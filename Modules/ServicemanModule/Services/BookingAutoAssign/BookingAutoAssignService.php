<?php

namespace Modules\ServicemanModule\Services\BookingAutoAssign;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServicemanModule\Entities\SupervisorTeam;

/**
 * Isolated auto-assign orchestrator.
 * Update picker / team rules here without changing booking create controllers.
 */
class BookingAutoAssignService
{
    public function __construct(
        private readonly SupervisorPickerInterface $picker
    ) {
    }

    public function enabled(): bool
    {
        if (!(bool) config('booking_auto_assign.enabled', true)) {
            return false;
        }

        if ((bool) config('booking_auto_assign.require_supervisor_mode', true) && !supervisorMode()) {
            return false;
        }

        return true;
    }

    /**
     * Assign a free supervisor (and optionally a team). Moves booking out of pending.
     *
     * @return array{assigned: bool, provider_id: ?string, team_id: ?string, reason: string}
     */
    public function assign(Booking $booking): array
    {
        if (!$this->enabled()) {
            return $this->result(false, $booking->provider_id, $booking->team_id, 'disabled');
        }

        $booking->refresh();

        // Already past pending with a supervisor — do not re-assign.
        if ($booking->booking_status !== 'pending' && !empty($booking->provider_id)) {
            return $this->result(false, $booking->provider_id, $booking->team_id, 'already_assigned');
        }

        $provider = $this->picker->pick($booking);
        if (!$provider) {
            Log::warning('booking_auto_assign.no_supervisor', [
                'booking_id' => $booking->id,
                'zone_id' => $booking->zone_id,
            ]);

            return $this->result(false, null, null, 'no_supervisor');
        }

        return DB::transaction(function () use ($booking, $provider) {
            /** @var Booking $locked */
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->first();
            if (!$locked) {
                return $this->result(false, null, null, 'booking_missing');
            }

            if ($locked->booking_status !== 'pending' && !empty($locked->provider_id)) {
                return $this->result(false, $locked->provider_id, $locked->team_id, 'already_assigned');
            }

            $locked->provider_id = $provider->id;
            $locked->booking_status = 'accepted';
            $locked->serviceman_id = null;
            $locked->assigned_by = (string) config('booking_auto_assign.assigned_by', 'auto');

            $teamId = null;
            if ((bool) config('booking_auto_assign.assign_available_team', true)) {
                $team = $this->pickAvailableTeam($provider->id);
                if ($team) {
                    $locked->team_id = $team->id;
                    $teamId = $team->id;
                    if (empty($locked->field_status)) {
                        $locked->field_status = 'assigned';
                        $locked->progress_percent = 0;
                    }
                    $locked->supervisor_seen_at = null;
                    $locked->supervisor_seen_by = null;
                }
            }

            $locked->save();
            $this->syncOpenRepeats($locked, $provider->id);
            $this->logAcceptedHistory($locked);

            Log::info('booking_auto_assign.assigned', [
                'booking_id' => $locked->id,
                'provider_id' => $provider->id,
                'team_id' => $teamId,
                'zone_id' => $locked->zone_id,
            ]);

            return $this->result(true, $provider->id, $teamId, 'assigned');
        });
    }

    private function pickAvailableTeam(string $providerId): ?SupervisorTeam
    {
        $busyStatuses = config('booking_auto_assign.busy_team_field_statuses', ['on_the_way', 'arrived', 'in_progress']);

        $teams = SupervisorTeam::query()
            ->withCount([
                'servicemen',
                'bookings as busy_jobs_count' => function ($query) use ($busyStatuses) {
                    $query->whereIn('field_status', $busyStatuses)
                        ->whereNotIn('booking_status', ['completed', 'canceled']);
                },
            ])
            ->where('provider_id', $providerId)
            ->where('is_active', true)
            ->orderBy('busy_jobs_count')
            ->orderBy('created_at')
            ->get()
            ->filter(fn (SupervisorTeam $team) => $team->servicemen_count > 0);

        return $teams->first();
    }

    private function syncOpenRepeats(Booking $booking, string $providerId): void
    {
        $repeats = $booking->repeat()->whereIn('booking_status', ['pending', 'accepted', 'ongoing'])->get();
        foreach ($repeats as $repeat) {
            $repeat->provider_id = $providerId;
            if ($repeat->booking_status === 'pending') {
                $repeat->booking_status = 'accepted';
            }
            $repeat->serviceman_id = null;
            $repeat->save();
        }
    }

    private function logAcceptedHistory(Booking $booking): void
    {
        try {
            $history = new BookingStatusHistory();
            $history->booking_id = $booking->id;
            $history->changed_by = $booking->customer_id;
            $history->booking_status = 'accepted';
            $history->save();
        } catch (\Throwable $e) {
            Log::warning('booking_auto_assign.history_failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function result(bool $assigned, ?string $providerId, ?string $teamId, string $reason): array
    {
        return [
            'assigned' => $assigned,
            'provider_id' => $providerId,
            'team_id' => $teamId,
            'reason' => $reason,
        ];
    }
}

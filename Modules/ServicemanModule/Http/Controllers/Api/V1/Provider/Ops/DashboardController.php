<?php

namespace Modules\ServicemanModule\Http\Controllers\Api\V1\Provider\Ops;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BookingModule\Entities\Booking;
use Modules\ServicemanModule\Entities\SupervisorTeam;
use Modules\ServicemanModule\Http\Traits\SupervisorOpsTrait;

class DashboardController extends Controller
{
    use SupervisorOpsTrait;

    private Booking $booking;
    private SupervisorTeam $team;

    public function __construct(Booking $booking, SupervisorTeam $team)
    {
        $this->booking = $booking;
        $this->team = $team;
    }

    public function index(Request $request): JsonResponse
    {
        $providerId = $request->user()->provider->id;
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $todayQuery = $this->booking
            ->where('provider_id', $providerId)
            ->whereNotNull('team_id')
            ->whereBetween('service_schedule', [$todayStart, $todayEnd])
            ->where('booking_status', '!=', 'canceled');

        $total = (clone $todayQuery)->count();
        $completed = (clone $todayQuery)->where(function ($q) {
            $q->where('field_status', 'completed')->orWhere('booking_status', 'completed');
        })->count();
        $inProgress = (clone $todayQuery)->whereIn('field_status', ['arrived', 'in_progress'])->count();
        $upcoming = (clone $todayQuery)->where(function ($q) {
            $q->whereNull('field_status')
                ->orWhereIn('field_status', ['assigned', 'on_the_way']);
        })->where('booking_status', '!=', 'completed')->count();

        $teams = $this->team->with(['servicemen.user'])
            ->where('provider_id', $providerId)
            ->where('is_active', true)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($team) => $this->transformTeamCard($team))
            ->values();

        $activeJobs = $this->booking
            ->with(['customer', 'team.servicemen.user', 'detail.service', 'subCategory', 'service_address', 'attendances'])
            ->where('provider_id', $providerId)
            ->whereIn('field_status', ['on_the_way', 'arrived', 'in_progress'])
            ->whereNotIn('booking_status', ['canceled', 'completed'])
            ->latest('service_schedule')
            ->take(5)
            ->get()
            ->map(fn ($booking) => $this->transformJobCard($booking))
            ->values();

        $upcomingJobs = $this->booking
            ->with(['customer', 'team.servicemen.user', 'detail.service', 'subCategory', 'service_address', 'attendances'])
            ->where('provider_id', $providerId)
            ->where(function ($q) {
                $q->whereNull('field_status')->orWhereIn('field_status', ['assigned', 'on_the_way']);
            })
            ->whereNotIn('booking_status', ['canceled', 'completed'])
            ->where('service_schedule', '>=', now())
            ->orderBy('service_schedule')
            ->take(5)
            ->get()
            ->map(fn ($booking) => $this->transformJobCard($booking))
            ->values();

        return response()->json(response_formatter(DEFAULT_200, [
            'today_summary' => [
                'total' => $total,
                'completed' => $completed,
                'upcoming' => $upcoming,
                'in_progress' => $inProgress,
                'progress_percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
                'date' => Carbon::today()->toDateString()],
            'teams_preview' => $teams,
            'active_jobs' => $activeJobs,
            'upcoming_jobs' => $upcomingJobs]), 200);
    }
}

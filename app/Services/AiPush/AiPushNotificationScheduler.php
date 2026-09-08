<?php

namespace App\Services\AiPush;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\BlogModule\Services\Gemini\GeminiVertexClient;

class AiPushNotificationScheduler
{
    /** Interval length in minutes (used for minute-based frequencies). */
    public const FREQUENCIES = [
        'every_1_minute' => 1,
        'every_5_minutes' => 5,
        'every_10_minutes' => 10,
        'daily' => 1440,
        'every_3_days' => 4320,
        'weekly' => 10080,
        'biweekly' => 20160,
        'monthly' => 43200,
    ];

    public const MINUTE_FREQUENCIES = [
        'every_1_minute',
        'every_5_minutes',
        'every_10_minutes',
    ];

    public const CLOCK_FREQUENCIES = [
        'daily',
        'every_3_days',
        'weekly',
        'biweekly',
        'monthly',
    ];

    public function isDue(?AiPushSettings $settings): bool
    {
        return $this->dueSlot($settings) !== null;
    }

    /**
     * Returns the HH:MM slot that should fire now, "interval" for minute frequencies, or null.
     */
    public function dueSlot(?AiPushSettings $settings): ?string
    {
        if (! $settings?->ai_push_enabled) {
            return null;
        }

        $hasGemini = app(GeminiVertexClient::class)->resolveTextApiKey() !== '';
        $hasOpenAi = (string) config('services.image_generation.openai.api_key') !== '';

        if (! $hasGemini && ! $hasOpenAi) {
            return null;
        }

        $frequency = (string) ($settings->ai_push_frequency ?? 'weekly');

        if (in_array($frequency, self::MINUTE_FREQUENCIES, true)) {
            return $this->dueMinuteSlot($settings, $frequency);
        }

        return $this->dueClockSlot($settings, $frequency);
    }

    public function nextRunAt(?AiPushSettings $settings): ?Carbon
    {
        if (! $settings?->ai_push_enabled) {
            return null;
        }

        $frequency = (string) ($settings->ai_push_frequency ?? 'weekly');

        if (in_array($frequency, self::MINUTE_FREQUENCIES, true)) {
            $minutes = $this->intervalMinutes($frequency);
            if (! $settings->ai_push_last_sent_at) {
                return now();
            }
            $next = $settings->ai_push_last_sent_at->copy()->addMinutes($minutes);

            return $next->gt(now()) ? $next : now();
        }

        return $this->nextClockSlotRun($settings, $frequency);
    }

    /**
     * @return list<string>
     */
    public function normalizeSendTimes(?AiPushSettings $settings): array
    {
        $stored = $settings?->ai_push_send_times;

        if (is_array($stored) && $stored !== []) {
            $times = array_values(array_unique(array_map(
                fn ($t) => $this->normalizeTime((string) $t),
                $stored
            )));
            sort($times);

            return $times !== [] ? $times : ['09:00'];
        }

        $legacy = $this->normalizeTime((string) ($settings?->ai_push_time ?? '09:00'));

        return [$legacy];
    }

    /**
     * @return list<string>
     */
    public function sentSlotsInCurrentCycle(AiPushSettings $settings): array
    {
        return $this->readCycleState($settings)['sent_slots'];
    }

    public function markSlotSent(AiPushSettings $settings, string $slotTime): void
    {
        $frequency = (string) ($settings->ai_push_frequency ?? 'weekly');
        $slotTime = $this->normalizeTime($slotTime);
        $sendTimes = $this->normalizeSendTimes($settings);

        $state = $this->readCycleState($settings);

        if ($state['cycle_started_at'] === null) {
            $state['cycle_started_at'] = now()->toDateTimeString();
        }

        $state['frequency'] = $frequency;
        $sent = $state['sent_slots'];
        if (! in_array($slotTime, $sent, true)) {
            $sent[] = $slotTime;
        }
        $state['sent_slots'] = array_values(array_unique($sent));

        $updates = [
            'ai_push_last_sent_at' => now(),
        ];

        $cycleComplete = count(array_intersect($sendTimes, $state['sent_slots'])) >= count($sendTimes);

        if ($cycleComplete) {
            $updates['ai_push_last_cycle_at'] = now();
            $updates['ai_push_daily_sent_slots'] = null;
        } else {
            $updates['ai_push_daily_sent_slots'] = $state;
        }

        $settings->update($updates);
    }

    public function usesClockTime(string $frequency): bool
    {
        return in_array($frequency, self::CLOCK_FREQUENCIES, true);
    }

    public function supportsMultipleSlots(string $frequency): bool
    {
        return $this->usesClockTime($frequency);
    }

    public function intervalMinutes(string $frequency): int
    {
        return self::FREQUENCIES[$frequency] ?? self::FREQUENCIES['weekly'];
    }

    public function cycleIntervalDays(string $frequency): int
    {
        return match ($frequency) {
            'daily' => 1,
            'every_3_days' => 3,
            'weekly' => 7,
            'biweekly' => 14,
            'monthly' => 30,
            default => 7,
        };
    }

    /**
     * @return array{sent_slots: list<string>, cycle_started_at: ?string, frequency: ?string}
     */
    private function readCycleState(AiPushSettings $settings): array
    {
        $stored = $settings->ai_push_daily_sent_slots;

        if (is_array($stored) && array_key_exists('sent_slots', $stored)) {
            return [
                'sent_slots' => array_values(array_filter((array) ($stored['sent_slots'] ?? []))),
                'cycle_started_at' => isset($stored['cycle_started_at']) ? (string) $stored['cycle_started_at'] : null,
                'frequency' => isset($stored['frequency']) ? (string) $stored['frequency'] : null,
            ];
        }

        if (is_array($stored)) {
            $today = now()->toDateString();
            $slots = $stored[$today] ?? [];

            if (is_array($slots) && $slots !== []) {
                return [
                    'sent_slots' => array_values(array_filter($slots)),
                    'cycle_started_at' => $today.' 00:00:00',
                    'frequency' => (string) ($settings->ai_push_frequency ?? 'daily'),
                ];
            }
        }

        return [
            'sent_slots' => [],
            'cycle_started_at' => null,
            'frequency' => null,
        ];
    }

    private function isCycleEligible(AiPushSettings $settings, string $frequency): bool
    {
        $state = $this->normalizeCycleForFrequency($settings, $frequency);

        if ($state['sent_slots'] !== []) {
            return true;
        }

        $lastCycle = $settings->ai_push_last_cycle_at;

        if (! $lastCycle) {
            return true;
        }

        if ($frequency === 'daily') {
            return $lastCycle->toDateString() < now()->toDateString();
        }

        return $lastCycle->diffInDays(now()) >= $this->cycleIntervalDays($frequency);
    }

    /**
     * @return array{sent_slots: list<string>, cycle_started_at: ?string, frequency: ?string}
     */
    private function normalizeCycleForFrequency(AiPushSettings $settings, string $frequency): array
    {
        $state = $this->readCycleState($settings);

        if ($frequency !== 'daily' || $state['cycle_started_at'] === null) {
            return $state;
        }

        $startedDate = Carbon::parse($state['cycle_started_at'])->toDateString();
        if ($startedDate !== now()->toDateString()) {
            return [
                'sent_slots' => [],
                'cycle_started_at' => null,
                'frequency' => null,
            ];
        }

        return $state;
    }

    private function dueMinuteSlot(AiPushSettings $settings, string $frequency): ?string
    {
        $minutes = $this->intervalMinutes($frequency);
        $lastSent = $settings->ai_push_last_sent_at;

        if (! $lastSent || $lastSent->diffInMinutes(now()) >= $minutes) {
            return 'interval';
        }

        return null;
    }

    private function dueClockSlot(AiPushSettings $settings, string $frequency): ?string
    {
        if (! $this->isCycleEligible($settings, $frequency)) {
            return null;
        }

        $settings->refresh();
        $sent = $this->normalizeCycleForFrequency($settings, $frequency)['sent_slots'];

        foreach ($this->normalizeSendTimes($settings) as $time) {
            if (in_array($time, $sent, true)) {
                continue;
            }

            if (! $this->isSlotTimeReached($time)) {
                continue;
            }

            if ($this->isSlotStale($time)) {
                $this->markSlotMissed($settings, $time);
                $settings->refresh();
                $sent = $this->normalizeCycleForFrequency($settings, $frequency)['sent_slots'];

                continue;
            }

            return $time;
        }

        return null;
    }

    public function markSlotMissed(AiPushSettings $settings, string $slotTime): void
    {
        $slotTime = $this->normalizeTime($slotTime);

        Log::info('ai_push.slot_skipped_stale', [
            'slot' => $slotTime,
            'frequency' => (string) ($settings->ai_push_frequency ?? 'weekly'),
            'grace_minutes' => $this->slotGraceMinutes(),
        ]);

        $this->markSlotSent($settings, $slotTime);
    }

    public function slotGraceMinutes(): int
    {
        return max(1, (int) config('ad_broadcast.ai_push_slot_grace_minutes', 30));
    }

    public function isSlotStale(string $time): bool
    {
        [$hour, $minute] = explode(':', $this->normalizeTime($time));
        $scheduledToday = now()->copy()->setTime((int) $hour, (int) $minute, 0);

        return $scheduledToday->diffInMinutes(now()) > $this->slotGraceMinutes();
    }

    private function isSlotTimeReached(string $time): bool
    {
        [$hour, $minute] = explode(':', $this->normalizeTime($time));

        $scheduledToday = now()->copy()->setTime((int) $hour, (int) $minute, 0);

        return now()->gte($scheduledToday);
    }

    private function nextClockSlotRun(AiPushSettings $settings, string $frequency): ?Carbon
    {
        $times = $this->normalizeSendTimes($settings);
        $state = $this->normalizeCycleForFrequency($settings, $frequency);
        $sent = $state['sent_slots'];

        foreach ($times as $time) {
            if (in_array($time, $sent, true)) {
                continue;
            }

            [$hour, $minute] = explode(':', $time);
            $candidate = now()->copy()->setTime((int) $hour, (int) $minute, 0);

            if ($candidate->gt(now())) {
                if (! $this->isCycleEligible($settings, $frequency) && $sent === []) {
                    $lastCycle = $settings->ai_push_last_cycle_at;
                    if ($lastCycle) {
                        $cycleStart = $lastCycle->copy()->addDays($this->cycleIntervalDays($frequency))->startOfDay();

                        return $cycleStart->setTime((int) $hour, (int) $minute, 0);
                    }
                }

                return $candidate;
            }
        }

        $lastCycle = $settings->ai_push_last_cycle_at ?? now();
        $nextCycle = $frequency === 'daily'
            ? now()->copy()->addDay()->startOfDay()
            : $lastCycle->copy()->addDays($this->cycleIntervalDays($frequency))->startOfDay();

        [$hour, $minute] = explode(':', $times[0]);

        return $nextCycle->setTime((int) $hour, (int) $minute, 0);
    }

    private function normalizeTime(string $time): string
    {
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            $parts = explode(':', $time);

            return sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
        }

        return '09:00';
    }
}

<?php

namespace App\Services\AiPush;

use Carbon\Carbon;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;

/**
 * Thin helper around BusinessSettings key_name=ai_push_settings, settings_type=notification_config.
 */
class AiPushSettings
{
    public const KEY_NAME = 'ai_push_settings';

    public const SETTINGS_TYPE = 'notification_config';

    /** @var list<string> */
    protected array $carbonKeys = [
        'ai_push_last_sent_at',
        'ai_push_last_cycle_at',
    ];

    /** @var array<string, mixed> */
    protected array $values = [];

    protected ?BusinessSettings $row = null;

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'ai_push_enabled' => false,
            'ai_push_prompt' => '',
            'ai_push_frequency' => 'weekly',
            'ai_push_time' => '09:00',
            'ai_push_send_times' => ['09:00'],
            'ai_push_audiences' => ['customers'],
            'ai_push_last_sent_at' => null,
            'ai_push_recent_topics' => [],
            'ai_push_last_error' => null,
            'ai_push_last_title' => null,
            'ai_push_service_id' => null,
            'ai_push_last_cycle_at' => null,
            'ai_push_daily_sent_slots' => null,
        ];
    }

    public static function load(): self
    {
        $instance = new self();
        $instance->refresh();

        return $instance;
    }

    public function refresh(): void
    {
        $this->row = BusinessSettings::query()
            ->where('key_name', self::KEY_NAME)
            ->where('settings_type', self::SETTINGS_TYPE)
            ->first();

        if (! $this->row) {
            $defaults = self::defaults();
            $this->row = BusinessSettings::query()->create([
                'key_name' => self::KEY_NAME,
                'settings_type' => self::SETTINGS_TYPE,
                'live_values' => $defaults,
                'test_values' => $defaults,
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        $stored = $this->row->live_values;
        if (! is_array($stored)) {
            $stored = [];
        }

        $this->values = array_merge(self::defaults(), $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function set(array $values): void
    {
        $this->update($values);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function update(array $values): void
    {
        foreach ($values as $key => $value) {
            if ($value instanceof Carbon) {
                $value = $value->toIso8601String();
            }
            $this->values[$key] = $value;
        }

        if (! $this->row) {
            $this->refresh();
        }

        $this->row->live_values = $this->values;
        $this->row->test_values = $this->values;
        $this->row->save();
    }

    public function updateCache(): void
    {
        // BusinessSettings is not cached the same way as Tashyik Settings.
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    public function __get(string $key): mixed
    {
        $value = $this->values[$key] ?? null;

        if (in_array($key, $this->carbonKeys, true)) {
            if ($value === null || $value === '') {
                return null;
            }

            return $value instanceof Carbon ? $value : Carbon::parse($value);
        }

        if ($key === 'ai_push_enabled') {
            return (bool) $value;
        }

        return $value;
    }

    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }
}

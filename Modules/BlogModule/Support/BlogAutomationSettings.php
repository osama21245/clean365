<?php

namespace Modules\BlogModule\Support;

use Modules\BusinessSettingsModule\Entities\BusinessSettings;

class BlogAutomationSettings
{
    public static function enabled(): bool
    {
        return (bool) (int) self::value('ai_blog_automation_enabled', 0);
    }

    public static function dailyLimit(): int
    {
        return max(1, (int) self::value('ai_blog_daily_limit', 5));
    }

    public static function monthlyLimit(): int
    {
        return max(1, (int) self::value('ai_blog_monthly_limit', 100));
    }

    public static function prompt(): string
    {
        return (string) self::value('ai_blog_prompt', '');
    }

    public static function all(): array
    {
        return [
            'ai_blog_automation_enabled' => self::enabled(),
            'ai_blog_daily_limit' => self::dailyLimit(),
            'ai_blog_monthly_limit' => self::monthlyLimit(),
            'ai_blog_prompt' => self::prompt(),
        ];
    }

    public static function update(array $data): void
    {
        $map = [
            'ai_blog_automation_enabled' => (int) (!empty($data['ai_blog_automation_enabled'])),
            'ai_blog_daily_limit' => max(1, min(100, (int) ($data['ai_blog_daily_limit'] ?? 5))),
            'ai_blog_monthly_limit' => max(1, min(1000, (int) ($data['ai_blog_monthly_limit'] ?? 100))),
            'ai_blog_prompt' => (string) ($data['ai_blog_prompt'] ?? ''),
        ];

        foreach ($map as $key => $value) {
            BusinessSettings::updateOrCreate(
                [
                    'key_name' => $key,
                    'settings_type' => 'blog_automation',
                ],
                [
                    'live_values' => $value,
                    'test_values' => $value,
                    'mode' => 'live',
                    'is_active' => 1,
                ]
            );
        }
    }

    private static function value(string $key, mixed $default = null): mixed
    {
        $row = business_config($key, 'blog_automation');

        if (!$row) {
            return $default;
        }

        return $row->live_values ?? $default;
    }
}

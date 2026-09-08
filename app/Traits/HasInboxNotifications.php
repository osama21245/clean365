<?php

namespace App\Traits;

use App\Models\UserInboxNotification;
use App\Services\Firebase\CloudMessaging;
use App\Support\NotificationLocale;
use Modules\UserManagement\Entities\User;

trait HasInboxNotifications
{
    /**
     * @param  array<string, string>  $title
     * @param  array<string, mixed>  $data
     * @param  array<string, string>|string|null  $description
     */
    protected function sendNotification(bool $pushNotification, User $recipient, array $title, string $type, array $data = [], $description = null)
    {
        $notification = new UserInboxNotification([
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'data' => ! empty($data) ? $data : null,
        ]);

        $recipient->inboxNotifications()->save($notification);

        $locale = NotificationLocale::pickTitleLocale($title);

        if ($pushNotification && array_key_exists($locale, $title) && $recipient->fcm_token) {
            $body = '';
            if (is_array($description) && array_key_exists($locale, $description)) {
                $body = (string) $description[$locale];
            } elseif (is_string($description)) {
                $body = $description;
            }

            $fcm = new CloudMessaging();
            $fcm->setNotification($title[$locale], $body);
            $fcm->setData(['notification_type' => $type]);
            $fcm->setTokens([$recipient->fcm_token]);
            $fcm->send();
        }
    }
}

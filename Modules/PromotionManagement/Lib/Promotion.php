<?php
use Illuminate\Support\Facades\Http;


function sendNotificationToHttp(array|null $data): bool
{
    $config = business_config('push_notification', 'third_party');
    $config = collect($config->live_values);

    $key = data_get($config, 'service_file_content', null);
    if (is_null($key))
        return false;
    $project_id = data_get(collect(json_decode($key, true)), 'project_id', null);
    $clientEmail = data_get(collect(json_decode($key, true)), 'client_email', null);
    $privateKey = data_get(collect(json_decode($key, true)), 'private_key', null);
    if (is_null($project_id) || is_null($clientEmail) || is_null($privateKey))
        return false;

    $url = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';
    $headers = [
        'Authorization' => 'Bearer ' . getAccessToken($clientEmail, $privateKey),
        'Content-Type' => 'application/json'
    ];
    try {
        Http::withHeaders($headers)->post($url, $data);
        return true;
    } catch (\Exception) {
        return false;
    }
}

function getAccessToken($clientEmail, $privateKey)
{
    $jwtToken = [
        'iss' => $clientEmail,
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600,
        'iat' => time()
    ];
    $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $jwtPayload = base64_encode(json_encode($jwtToken));
    $unsignedJwt = $jwtHeader . '.' . $jwtPayload;
    openssl_sign($unsignedJwt, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $jwt = $unsignedJwt . '.' . base64_encode($signature);

    $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]);
    return $response->json('access_token');
}

if (!function_exists('device_notification')) {
    function device_notification($fcm_token, $title, $description, $image, $booking_id, $type = 'status', $channel_id = null, $user_id = null, $data = null, $advertisement_id = null, $bookingType = null, $repeat_type = null)
    {
        $title = text_variable_data_format($title, $booking_id, $type, $data, $bookingType);
        $body = (string) ($description ?? '');

        // Persist to in-app inbox (GET /customer/inbox-notifications) when we know the user.
        if (!empty($user_id) && filled($title)) {
            try {
                $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
                \App\Models\UserInboxNotification::query()->create([
                    'user_id' => $user_id,
                    'type' => (string) $type,
                    'title' => [$locale => (string) $title],
                    'description' => $body !== '' ? [$locale => $body] : null,
                    'data' => array_filter([
                        'booking_id' => $booking_id,
                        'channel_id' => $channel_id,
                        'advertisement_id' => $advertisement_id,
                        'booking_type' => $bookingType,
                        'repeat_type' => $repeat_type,
                        'type' => $type,
                    ], fn ($v) => $v !== null && $v !== ''),
                ]);
            } catch (\Throwable $e) {
                info('inbox notification store failed: ' . $e->getMessage());
            }
        }

        $postData = [
            'message' => [
                "token" => $fcm_token,
                // Keep title/body only in `notification` to avoid Android double-display
                // when the client also renders a local notification from `data`.
                "data" => [
                    "booking_id" => (string) $booking_id,
                    "channel_id" => (string) $channel_id,
                    "user_id" => (string) $user_id,
                    "type" => (string) $type,
                    "image" => (string) $image,
                    "advertisement_id" => (string) $advertisement_id,
                    "booking_type" => (string) $bookingType,
                    "repeat_type" => (string) $repeat_type,
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                ],
                "notification" => [
                    'title' => (string) $title,
                    'body' => $body,
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "notification.wav"
                        ]
                    ]
                ],
                "android" => [
                    "notification" => [
                        "channelId" => "clean365"
                    ]
                ]
            ]
        ];

        return sendNotificationToHttp($postData);
    }
}

if (!function_exists('topic_notification')) {
    function topic_notification($topic, $title, $description, $image, $booking_id, $type = 'status')
    {
        $image = asset('storage/app/public/push-notification') . '/' . $image;

        $postData = [
            'message' => [
                "topic" => $topic,
                "data" => [
                    "title" => (string) $title,
                    "body" => (string) $description,
                    "booking_id" => (string) $booking_id,
                    "type" => (string) $type,
                    "image" => (string) $image
                ],
                "notification" => [
                    "title" => (string) $title,
                    "body" => (string) $description,
                    "image" => (string) $image
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "notification.wav"
                        ]
                    ]
                ],
                "android" => [
                    "notification" => [
                        "channelId" => "clean365"
                    ]
                ]
            ]
        ];

        return sendNotificationToHttp($postData);
    }
}

//bidding notification
if (!function_exists('device_notification_for_bidding')) {
    function device_notification_for_bidding($fcm_token, $title, $description, $image, $type = 'bidding', $booking_id = null, $post_id = null, $provider_id = null, $data = null)
    {
        $title = text_variable_data_format($title, $booking_id, $type, $data);
        $image = asset('storage/app/public/push-notification') . '/' . $image;

        $postData = [
            'message' => [
                "token" => $fcm_token,
                "data" => [
                    "title" => (string) $title,
                    "body" => (string) $description,
                    "booking_id" => (string) $booking_id,
                    "post_id" => (string) $post_id,
                    "provider_id" => (string) $provider_id,
                    "type" => (string) $type,
                    "image" => (string) $image
                ],
                "notification" => [
                    "title" => (string) $title,
                    "body" => (string) $description
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "notification.wav"
                        ]
                    ]
                ],
                "android" => [
                    "notification" => [
                        "channelId" => "clean365"
                    ]
                ]
            ]
        ];

        return sendNotificationToHttp($postData);
    }
}

//chatting notification

if (!function_exists('device_notification_for_chatting')) {
    function device_notification_for_chatting($fcm_token, $title, $description, $image, $channel_id, $user_name, $user_image, $user_phone, $user_type, $type = 'status')
    {
        $image = asset('storage/app/public/push-notification') . '/' . $image;

        $postData = [
            'message' => [
                "token" => $fcm_token,
                "data" => [
                    "title" => (string) $title,
                    "body" => (string) $description,
                    "image" => (string) $image,
                    "type" => (string) $type,
                    "channel_id" => (string) $channel_id,
                    "user_name" => (string) $user_name,
                    "user_image" => (string) $user_image,
                    "user_phone" => (string) $user_phone,
                    "user_type" => (string) $user_type
                ],
                "notification" => [
                    "title" => (string) $title,
                    "body" => (string) $description
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "notification.wav"
                        ]
                    ]
                ],
                "android" => [
                    "notification" => [
                        "channelId" => "clean365"
                    ]
                ]
            ]
        ];

        return sendNotificationToHttp($postData);
    }
}
if (!function_exists('basic_discount_calculation')) {
    function basic_discount_calculation($service, $total_purchase_amount): float
    {
        $keeper = null;
        if (!empty($service->service_discount) && $service->service_discount->count() > 0) {
            $keeper = $service->service_discount[0]->discount;
        } elseif (!empty($service->category?->category_discount) && $service->category->category_discount->count() > 0) {
            $keeper = $service->category->category_discount[0]->discount;
        }

        return booking_discount_calculator($keeper, $total_purchase_amount);
    }
}

if (!function_exists('campaign_discount_calculation')) {
    function campaign_discount_calculation($service, $total_purchase_amount): float
    {
        // Campaign discounts disabled — always return 0.
        return 0.0;
    }
}

if (!function_exists('booking_tax_amount')) {
    /**
     * VAT/tax disabled — always return 0.
     */
    function booking_tax_amount(float $taxableAmount, float|int|null $taxPercent = null): float
    {
        return 0.0;
    }
}

/**
 * @param string $url
 * @param string $postdata
 * @param array $header
 * @return bool|string
 */
function send_curl_request(string $url, string $postdata, array $header): string|bool
{
    $ch = curl_init();
    $timeout = 120;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    // Get URL content
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

/**
 * @param mixed $keeper
 * @param $total_purchase_amount
 * @return mixed
 */
function booking_discount_calculator(mixed $keeper, $total_purchase_amount): float
{
    $amount = 0;

    if ($keeper != null && $total_purchase_amount >= $keeper->min_purchase) {
        if ($keeper->discount_amount_type == 'percent') {
            $amount = ($total_purchase_amount / 100) * $keeper->discount_amount;

            if ($amount > $keeper->max_discount_amount) {
                $amount = $keeper->max_discount_amount;
            }

        } else {
            $amount = $keeper->discount_amount;
        }
    }

    $amount = min($amount, $total_purchase_amount);
    return $amount;
}

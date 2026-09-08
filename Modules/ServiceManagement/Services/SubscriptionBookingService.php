<?php

namespace Modules\ServiceManagement\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\BookingModule\Events\BookingRequested;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\ServicemanModule\Services\BookingAutoAssign\BookingAutoAssign;
use Modules\PaymentModule\Library\Payer;
use Modules\PaymentModule\Library\Payment as PaymentInfo;
use Modules\PaymentModule\Library\Receiver;
use Modules\PaymentModule\Traits\Payment as PaymentTrait;
use Modules\PromotionManagement\Entities\Coupon;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Illuminate\Support\Str;
use Modules\ServiceManagement\Entities\AdditionalService;
use Modules\ServiceManagement\Entities\CustomerServiceSubscription;
use Modules\ServiceManagement\Entities\Property;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;


class SubscriptionBookingService
{
    use BookingTrait;

    /**
     * Rebook from an existing booking — no cart.
     * Copies service/property/address/addons from the old booking unless overridden.
     *
     * @return array{flag:string,message?:string,booking?:Booking,subscription?:CustomerServiceSubscription}
     */
    public function rebook(string $userId, array $payload): array
    {
        $oldBooking = Booking::with(['detail', 'service_address'])
            ->where('id', $payload['booking_id'])
            ->where('customer_id', $userId)
            ->where('is_guest', 0)
            ->first();

        if (!$oldBooking) {
            return ['flag' => 'failed', 'message' => 'booking not found'];
        }

        $mainDetail = $oldBooking->detail->first();
        if (!$mainDetail) {
            return ['flag' => 'failed', 'message' => 'booking has no services'];
        }

        $propertyId = $payload['property_id']
            ?? $oldBooking->service_address?->property_id;

        if (!$propertyId) {
            return ['flag' => 'failed', 'message' => 'property_id required'];
        }

        $addonIds = $payload['addon_service_ids']
            ?? $oldBooking->detail
                ->slice(1)
                ->pluck('service_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

        $isAdditional = AdditionalService::where('id', $mainDetail->service_id)->exists();
        $serviceType = $isAdditional ? 'additional' : 'main';

        return $this->confirm($userId, [
            'service_id' => $mainDetail->service_id,
            'property_id' => $propertyId,
            'service_address_id' => $payload['service_address_id'] ?? $payload['address_id'] ?? $oldBooking->service_address_id,
            'service_schedule' => $payload['service_schedule'],
            'zone_id' => $payload['zone_id'] ?? $oldBooking->zone_id,
            'payment_method' => $payload['payment_method'] ?? $oldBooking->payment_method ?? 'cash_after_service',
            'variant_key' => $payload['variant_key'] ?? $mainDetail->variant_key,
            'addon_service_ids' => $addonIds,
            'coupon_code' => $payload['coupon_code'] ?? null,
            'customer_note' => $payload['customer_note'] ?? null,
            'service_location' => $payload['service_location'] ?? $oldBooking->service_location ?? 'customer',
            'is_partial' => $payload['is_partial'] ?? 0
        ], $serviceType);
    }

    /**
     * Reschedule a booking / subscription visit date & time.
     *
     * @return array{flag:string,message?:string,booking?:Booking}
     */
    public function reschedule(string $userId, string $bookingId, string $scheduleDate): array
    {
        $booking = Booking::where('id', $bookingId)
            ->where('customer_id', $userId)
            ->first();

        if (!$booking) {
            return ['flag' => 'failed', 'message' => 'booking not found'];
        }

        if (!in_array($booking->booking_status, ['pending', 'accepted'], true)) {
            return ['flag' => 'failed', 'message' => 'Only pending or accepted bookings can be rescheduled'];
        }

        $booking->service_schedule = date('Y-m-d H:i:s', strtotime($scheduleDate));

        $history = new BookingScheduleHistory();
        $history->booking_id = $booking->id;
        $history->changed_by = $userId;
        $history->is_guest = 0;
        $history->schedule = $booking->service_schedule;

        DB::transaction(function () use ($booking, $history) {
            $booking->save();
            $history->save();
        });

        return [
            'flag' => 'success',
            'booking' => $booking->fresh(['detail', 'service_address'])
        ];
    }

    /**
     * Price preview for Figma summary (no cart, no booking).
     * Use this when applying coupon / toggling addons before confirm.
     *
     * @return array{flag:string,message?:string,summary?:array}
     */
    public function preview(array $payload): array
    {
        $prepared = $this->prepareLineItems($payload);
        if (($prepared['flag'] ?? '') !== 'success') {
            return $prepared;
        }

        $lineItems = $prepared['line_items'];
        $extraFee = 0;
        if ((business_config('booking_additional_charge', 'booking_setup')->live_values ?? 0)) {
            $extraFee = (float) (business_config('additional_charge_fee_amount', 'booking_setup')->live_values ?? 0);
        }

        $main = $lineItems[0] ?? null;
        $addons = array_slice($lineItems, 1);

        $couponDiscount = round(collect($lineItems)->sum('coupon_discount'), 2);
        $couponCode = collect($lineItems)->pluck('coupon_code')->filter()->first();

        return [
            'flag' => 'success',
            'summary' => [
                'service_cost' => round((float) ($main['service_cost'] ?? 0) * (int) ($main['quantity'] ?? 1), 2),
                'discount_amount' => round(collect($lineItems)->sum('discount_amount'), 2),
                'campaign_discount_amount' => round(collect($lineItems)->sum('campaign_discount'), 2),
                'coupon_discount_amount' => $couponDiscount,
                'coupon_code' => $couponCode,
                'coupon_applied' => $couponDiscount > 0,
                'addons_amount' => round(collect($addons)->sum(fn($l) => $l['service_cost'] * $l['quantity']), 2),
                'tax_amount' => round(collect($lineItems)->sum('tax_amount'), 2),
                'extra_fee' => $extraFee,
                'total_amount' => round(collect($lineItems)->sum('total_cost') + $extraFee, 2),
                'items' => $lineItems
            ]
        ];
    }

    /**
     * Confirm subscription booking for a single service — no cart.
     *
     * @return array{flag:string,message?:string,booking?:Booking,subscription?:CustomerServiceSubscription}
     */
    public function confirm(string $userId, array $payload, string $serviceType = 'all'): array
    {
        $serviceAddressId = $payload['service_address_id'] ?? $payload['address_id'] ?? null;
        if (!$serviceAddressId) {
            return ['flag' => 'failed', 'message' => 'service_address_id or address_id required'];
        }

        $address = UserAddress::where('id', $serviceAddressId)
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return ['flag' => 'failed', 'message' => 'address not found or does not belong to customer'];
        }

        $zoneId = Config::get('zone_id') ?: ($payload['zone_id'] ?? $address->zone_id);
        if (!$zoneId) {
            return ['flag' => 'failed', 'message' => 'zone_id required'];
        }

        $service = $this->findServiceByType($payload['service_id'], $serviceType);

        if (!$service) {
            if ($serviceType === 'main') {
                $exists = Service::withTrashed()->where('id', $payload['service_id'])->exists();
                return ['flag' => 'failed', 'message' => $exists ? 'service inactive or deleted' : 'service not found'];
            } elseif ($serviceType === 'additional') {
                $exists = AdditionalService::where('id', $payload['service_id'])->exists();
                return ['flag' => 'failed', 'message' => $exists ? 'service inactive or deleted' : 'additional service not found'];
            } else {
                $existsInServices = Service::withTrashed()->where('id', $payload['service_id'])->exists();
                $existsInAdditional = AdditionalService::where('id', $payload['service_id'])->exists();
                if (!$existsInServices && !$existsInAdditional) {
                    return ['flag' => 'failed', 'message' => 'service not found'];
                }
                return ['flag' => 'failed', 'message' => 'service inactive or deleted'];
            }
        }

        $propertyId = null;
        if (!empty($payload['property_id'])) {
            if (Str::isUuid($payload['property_id'])) {
                $propertyId = Property::where('id', $payload['property_id'])->where('is_active', 1)->value('id');
            }
            if (!$propertyId) {
                $propertyId = Property::where('name', 'LIKE', '%' . $payload['property_id'] . '%')->where('is_active', 1)->value('id');
            }
        }
        if (!$propertyId && $address->property_id) {
            $propertyId = $address->property_id;
        }
        if (!$propertyId && $service->property_id) {
            $propertyId = $service->property_id;
        }
        if (!$propertyId) {
            $propertyId = Property::where('is_active', 1)->first()?->id;
        }

        $payload['service_address_id'] = $address->id;
        $payload['zone_id'] = $zoneId;
        $payload['property_id'] = $propertyId;

        if (!$address->property_id) {
            $address->property_id = $propertyId;
            $address->save();
        }

        $payload['service_type'] = $serviceType;
        $prepared = $this->prepareLineItems($payload);
        if (($prepared['flag'] ?? '') !== 'success') {
            return $prepared;
        }
        $lineItems = $prepared['line_items'];

        $paymentMethod = $payload['payment_method'] ?? 'cash_after_service';
        $digitalMethods = array_column(DIGITAL_PAYMENT_METHODS, 'key');
        $isDigitalPayment = $paymentMethod === 'edfapay'
            || in_array($paymentMethod, $digitalMethods, true);

        try {
            $result = DB::transaction(function () use ($userId, $payload, $zoneId, $service, $address, $lineItems, $paymentMethod, $isDigitalPayment) {
                $extraFee = 0;
                if ((business_config('booking_additional_charge', 'booking_setup')->live_values ?? 0)) {
                    $extraFee = (float) (business_config('additional_charge_fee_amount', 'booking_setup')->live_values ?? 0);
                }

                $subtotalCost = collect($lineItems)->sum('total_cost');
                $totalTax = collect($lineItems)->sum('tax_amount');
                $totalDiscount = collect($lineItems)->sum('discount_amount');
                $totalCampaign = collect($lineItems)->sum('campaign_discount');
                $totalCoupon = collect($lineItems)->sum('coupon_discount');
                $totalBookingAmount = round($subtotalCost + $extraFee, 2);

                $booking = new Booking();
                $booking->customer_id = $userId;
                $booking->provider_id = null;
                $booking->category_id = $service->category_id;
                $booking->sub_category_id = $service->sub_category_id;
                $booking->zone_id = $zoneId;
                $booking->booking_status = 'pending';
                // Unpaid status removed — all bookings are paid.
                $booking->is_paid = 1;
                $booking->payment_method = $paymentMethod;
                if ($paymentMethod === 'wallet_payment') {
                    $booking->transaction_id = 'wallet-payment';
                } elseif ($isDigitalPayment) {
                    $booking->transaction_id = null;
                } else {
                    $booking->transaction_id = 'cash-payment';
                }
                $booking->total_booking_amount = $totalBookingAmount;
                $booking->total_tax_amount = $totalTax;
                $booking->total_discount_amount = $totalDiscount;
                $booking->total_campaign_discount_amount = $totalCampaign;
                $booking->total_coupon_discount_amount = $totalCoupon;
                $booking->coupon_code = $lineItems[0]['coupon_code'] ?? null;
                $booking->service_schedule = date('Y-m-d H:i:s', strtotime($payload['service_schedule']));
                $booking->service_address_id = $address->id;
                                $booking->is_guest = 0;
                $booking->extra_fee = $extraFee;
                $booking->total_referral_discount_amount = 0;
                $booking->service_address_location = json_encode($address);
                $booking->service_location = $payload['service_location'] ?? 'customer';
                $booking->customer_note = $payload['customer_note'] ?? null;
                $booking->save();

                foreach ($lineItems as $line) {
                    $detail = new BookingDetail();
                    $detail->booking_id = $booking->id;
                    $detail->service_id = $line['service_id'];
                    $detail->service_name = $line['service_name'];
                    $detail->variant_key = $line['variant_key'];
                    $detail->quantity = $line['quantity'];
                    $detail->service_cost = $line['service_cost'];
                    $detail->discount_amount = $line['discount_amount'];
                    $detail->campaign_discount_amount = $line['campaign_discount'];
                    $detail->overall_coupon_discount_amount = $line['coupon_discount'];
                    $detail->tax_amount = $line['tax_amount'];
                    $detail->total_cost = $line['total_cost'];
                    $detail->save();

                    $amount = new BookingDetailsAmount();
                    $amount->booking_details_id = $detail->id;
                    $amount->booking_id = $booking->id;
                    $amount->service_unit_cost = $line['service_cost'];
                    $amount->service_quantity = $line['quantity'];
                    $amount->service_tax = $line['tax_amount'];
                    $amount->discount_by_admin = $this->calculate_discount_cost($line['discount_amount'])['admin'];
                    $amount->discount_by_provider = $this->calculate_discount_cost($line['discount_amount'])['provider'];
                    $amount->campaign_discount_by_admin = $this->calculate_campaign_cost($line['campaign_discount'])['admin'];
                    $amount->campaign_discount_by_provider = $this->calculate_campaign_cost($line['campaign_discount'])['provider'];
                    $amount->coupon_discount_by_admin = $this->calculate_coupon_cost($line['coupon_discount'])['admin'];
                    $amount->coupon_discount_by_provider = $this->calculate_coupon_cost($line['coupon_discount'])['provider'];
                    $amount->save();
                }

                $schedule = new BookingScheduleHistory();
                $schedule->booking_id = $booking->id;
                $schedule->changed_by = $userId;
                $schedule->is_guest = 0;
                $schedule->schedule = $booking->service_schedule;
                $schedule->save();

                $statusHistory = new BookingStatusHistory();
                $statusHistory->changed_by = $userId;
                $statusHistory->booking_id = $booking->id;
                $statusHistory->is_guest = 0;
                $statusHistory->booking_status = 'pending';
                $statusHistory->save();

                // Non-digital: assign supervisor immediately so booking never stays pending.
                // Digital: assign after payment via notifyAfterPaidBooking / BookingRequested.
                if (!$isDigitalPayment) {
                    BookingAutoAssign::handle($booking);
                    $booking->refresh();
                }

                if ($paymentMethod === 'wallet_payment') {
                    placeBookingTransactionForWalletPayment($booking);
                }

                $subscription = CustomerServiceSubscription::query()
                    ->where([
                        'user_id' => $userId,
                        'service_id' => $service->id,
                        'property_id' => $payload['property_id'],
                        'status' => 'active'
                    ])
                    ->lockForUpdate()
                    ->first();

                if (!$subscription) {
                    $visits = max(1, (int) $service->visits_count);
                    $subscription = CustomerServiceSubscription::create([
                        'user_id' => $userId,
                        'service_id' => $service->id,
                        'property_id' => $payload['property_id'],
                        'total_visits' => $visits,
                        'remaining_visits' => $visits,
                        'status' => 'active'
                    ]);
                }

                if ($subscription->remaining_visits < 1) {
                    throw new \RuntimeException('no remaining visits');
                }

                $booking->subscription_id = $subscription->id;
                $booking->save();

                // For digital payment, consume visit only after successful gateway callback.
                if (!$isDigitalPayment) {
                    $consumed = CustomerServiceSubscriptionService::consumeOneVisit(
                        userId: $userId,
                        serviceId: $service->id,
                        propertyId: $payload['property_id'],
                        bookingId: $booking->id
                    );

                    if (!$consumed) {
                        throw new \RuntimeException('no remaining visits');
                    }
                }

                return [
                    'flag' => 'success',
                    'booking' => $booking->fresh(['detail', 'service_address']),
                    'subscription' => CustomerServiceSubscriptionService::presentForApi(
                        $subscription->fresh(['service', 'property', 'additional_service'])
                    ),
                    'is_digital_payment' => $isDigitalPayment,
                    'payment_payload' => $payload
                ];
            });
        } catch (\RuntimeException $e) {
            return ['flag' => 'failed', 'message' => $e->getMessage()];
        }

        if (($result['flag'] ?? '') === 'success' && !empty($result['is_digital_payment'])) {
            try {
                $paymentUrl = $this->createSubscriptionDigitalPaymentLink(
                    userId: $userId,
                    booking: $result['booking'],
                    payload: $result['payment_payload'] ?? $payload
                );
            } catch (\Throwable $e) {
                \Log::error('Subscription digital payment link failed', [
                    'booking_id' => $result['booking']->id ?? null,
                    'error' => $e->getMessage()
                ]);
                $paymentUrl = false;
            }

            if (!$paymentUrl) {
                $result['booking']->booking_status = 'canceled';
                $result['booking']->save();
                return ['flag' => 'failed', 'message' => 'Unable to initiate digital payment'];
            }

            unset($result['is_digital_payment'], $result['payment_payload']);
            $result['payment_url'] = (string) $paymentUrl;
            $result['payment_required'] = true;
            // Also expose on booking so clients always see the URL in booking payload.
            $result['booking']->setAttribute('payment_url', $result['payment_url']);
            $result['booking']->setAttribute('payment_required', true);

            return $result;
        }

        unset($result['is_digital_payment'], $result['payment_payload']);

        // FCM + email are slow (network). Run after HTTP response so confirm stays fast.
        if (($result['flag'] ?? '') === 'success' && !empty($result['booking']?->id)) {
            $bookingId = $result['booking']->id;
            app()->terminating(function () use ($bookingId) {
                $booking = Booking::with('customer')->find($bookingId);
                if (!$booking) {
                    return;
                }
                // Assign first so notifications target the chosen supervisor.
                event(new BookingRequested($booking));
                $booking->refresh();
                $this->sendPlaceBookingNotifications($booking);
            });
        }

        return $result;
    }

    private function createSubscriptionDigitalPaymentLink(string $userId, Booking $booking, array $payload): string|false
    {
        $customer = User::find($userId);
        if (!$customer) {
            return false;
        }

        $payer = new Payer(
            trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')) ?: 'Customer',
            $customer->email ?? '',
            $customer->phone ?? '',
            ''
        );

        $additionalData = array_merge($payload, [
            'booking_id' => $booking->id,
            'subscription_id' => $booking->subscription_id,
            'service_id' => $payload['service_id'] ?? null,
            'property_id' => $payload['property_id'] ?? null,
            'access_token' => base64_encode($userId),
            'locale' => app()->getLocale() === 'ar' ? 'ar' : 'en'
        ]);

        $paymentInfo = new PaymentInfo(
            success_hook: 'service_subscription_payment_success',
            failure_hook: 'service_subscription_payment_fail',
            currency_code: currency_code(),
            payment_method: $payload['payment_method'],
            payment_platform: $payload['payment_platform'] ?? 'app',
            payer_id: $userId,
            receiver_id: null,
            additional_data: $additionalData,
            payment_amount: (float) $booking->total_booking_amount,
            external_redirect_link: $payload['callback'] ?? null,
            attribute: 'booking',
            attribute_id: $booking->id
        );

        $receiverInfo = new Receiver('receiver_name', 'example.png');

        $link = PaymentTrait::generate_link($payer, $paymentInfo, $receiverInfo);
        if (!$link) {
            \Log::error('EdfaPay generate_link returned false', [
                'booking_id' => $booking->id,
                'payment_method' => $payload['payment_method'] ?? null
            ]);
            return false;
        }

        return (string) $link;
    }

    public function notifyAfterPaidBooking(Booking $booking): void
    {
        // Assign supervisor before notifying (digital payment path).
        event(new BookingRequested($booking));
        $booking->refresh();
        $this->sendPlaceBookingNotifications($booking);
    }

    private function sendPlaceBookingNotifications(Booking $booking): void
    {
        $bookingNotificationStatus = business_config('booking', 'notification_settings')->live_values;

        // Supervisor already got "booking_accepted" from the Booking model observer
        // when auto-assign flipped pending → accepted. Only broadcast to the zone
        // when the booking is still unassigned (pending / no provider).
        if (!empty($booking->provider_id) && ($booking->booking_status ?? null) !== 'pending') {
            return;
        }

        $bookingNotification = (int) (business_config('booking_notification', 'business_information'))?->live_values;
        $bookingNotificationType = (business_config('booking_notification_type', 'business_information'))?->live_values;

        if ($bookingNotification && $bookingNotificationType == 'firebase') {
            try {
                $serviceAtProviderPlace = (int) (business_config('service_at_provider_place', 'provider_config')->live_values ?? 0);
                $serviceLocation = $booking->service_location;
                $zoneId = $booking->zone_id;

                if ($serviceAtProviderPlace) {
                    $topic = $serviceLocation === 'provider'
                        ? "clean365_provider_{$zoneId}_provider_booking_message"
                        : "clean365_provider_{$zoneId}_customer_booking_message";
                } else {
                    $topic = "clean365_provider_{$zoneId}_booking_message";
                }

                topic_notification($topic, 'new booking', '', 'def.png', null);
            } catch (\Exception $e) {
            }
        }

        $this->notifySubscribedProviders($booking, $bookingNotificationStatus);
    }

    private function notifySubscribedProviders(Booking $booking, $bookingNotificationStatus): void
    {
        // Prefer category (sub_category is optional / unused)
        $providerQuery = SubscribedService::ofSubscription(1);
        if (!empty($booking->sub_category_id)) {
            $providerQuery->where('sub_category_id', $booking->sub_category_id);
        } elseif (!empty($booking->category_id)) {
            $providerQuery->where('category_id', $booking->category_id);
        } else {
            return;
        }

        $providerIds = $providerQuery->pluck('provider_id')->toArray();
        if (business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values) {
            $providers = Provider::with('owner')->whereIn('id', $providerIds)->where('zone_id', $booking?->zone_id)->where('is_suspended', 0)->get();
        } else {
            $providers = Provider::with('owner')->whereIn('id', $providerIds)->where('zone_id', $booking?->zone_id)->get();
        }

        foreach ($providers as $provider) {
            $fcmToken = $provider->owner->fcm_token ?? null;
            $notification = isNotificationActive($provider?->id, 'booking', 'notification', 'provider');
            $title = get_push_notification_message('new_service_request_arrived', 'provider_notification', optional($provider->owner)->current_language_key);

            if (!is_null($fcmToken) && $provider->service_availability && $title && $notification && isset($bookingNotificationStatus) && $bookingNotificationStatus['push_notification_booking'] && sendDeviceNotificationPermission($provider->id)) {
                $serviceAtProviderPlace = (int) ((business_config('service_at_provider_place', 'provider_config'))->live_values ?? 0);
                $serviceLocations = getProviderSettings(providerId: $provider->id, key: 'service_location', type: 'provider_config') ?? ['customer'];

                if ($serviceAtProviderPlace == 1) {
                    if (in_array($booking->service_location, $serviceLocations)) {
                        device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                    }
                } else {
                    device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                }
            }
        }
    }

    private function prepareLineItems(array $payload): array
    {
        $zoneId = Config::get('zone_id') ?: ($payload['zone_id'] ?? \Modules\ZoneManagement\Entities\Zone::ofStatus(1)->first()?->id);
        if (!$zoneId) {
            return ['flag' => 'failed', 'message' => 'zone_id required'];
        }

        $serviceType = $payload['service_type'] ?? 'all';
        $service = $this->findServiceByType($payload['service_id'], $serviceType);

        if (!$service) {
            return ['flag' => 'failed', 'message' => 'service not found'];
        }

        if (!empty($payload['property_id']) && $service->property_id && $service->property_id !== $payload['property_id']) {
            return ['flag' => 'failed', 'message' => 'Service does not belong to this property'];
        }

        $addonIds = array_values(array_unique(array_filter($payload['addon_service_ids'] ?? [])));
        $lineItems = [];

        $mainLine = $this->buildLineItem($service, $zoneId, $payload['variant_key'] ?? null);
        if (!$mainLine) {
            return [
                'flag' => 'failed',
                'message' => "variation not found for service {$service->id} in zone {$zoneId}"
            ];
        }
        $lineItems[] = $mainLine;

        foreach ($addonIds as $addonId) {
            if ($addonId === $service->id) {
                continue;
            }
            $addonService = Service::where('id', $addonId)->where('is_active', 1)->first();
            if (!$addonService) {
                $addAddon = AdditionalService::where('id', $addonId)->where('is_active', 1)->first();
                if ($addAddon) {
                    $addonService = new Service();
                    $addonService->id = $addAddon->id;
                    $addonService->name = $addAddon->name;
                    $addonService->category_id = null;
                    $addonService->sub_category_id = null;
                    $addonService->property_id = null;
                    $addonService->min_bidding_price = $addAddon->price ?? 0;
                    $addonService->visits_count = 1;
                    $addonService->tax = 0;
                    $addonService->is_active = 1;
                    $addonService->exists = true;
                }
            }
            if (!$addonService) {
                return ['flag' => 'failed', 'message' => "addon service not found: {$addonId}"];
            }
            $addonLine = $this->buildLineItem($addonService, $zoneId, null);
            if (!$addonLine) {
                return [
                    'flag' => 'failed',
                    'message' => "variation not found for service {$addonId} in zone {$zoneId}"
                ];
            }
            $lineItems[] = $addonLine;
        }

        if (!empty($payload['coupon_code'])) {
            $lineItems = $this->applyCouponToLines($lineItems, $payload['coupon_code'], $zoneId);
        }

        return [
            'flag' => 'success',
            'service' => $service,
            'line_items' => $lineItems
        ];
    }

    private function buildLineItem(Service $service, string $zoneId, ?string $variantKey): ?array
    {
        $variationQuery = Variation::where(['zone_id' => $zoneId, 'service_id' => $service->id]);
        if ($variantKey) {
            $variationQuery->where('variant_key', $variantKey);
        }
        $variation = $variationQuery->first();

        if (!$variation) {
            $variation = Variation::where(['zone_id' => $zoneId, 'service_id' => $service->id])->first();
        }

        if (!$variation) {
            $variation = Variation::where('service_id', $service->id)->first();
        }

        $price = $variation?->price ?? $service->min_bidding_price ?? 0;
        $vKey = $variation?->variant_key ?? 'default';

        $quantity = 1;
        $basicDiscount = basic_discount_calculation($service, $price * $quantity);
        $campaignDiscount = campaign_discount_calculation($service, $price * $quantity);
        $subtotal = round($price * $quantity, 2);
        $applicableDiscount = ($campaignDiscount >= $basicDiscount) ? $campaignDiscount : $basicDiscount;
        $tax = round((($price * $quantity - $applicableDiscount) * 0) / 100, 2);
        $basicDiscount = $basicDiscount > $campaignDiscount ? $basicDiscount : 0;
        $campaignDiscount = $campaignDiscount >= $basicDiscount ? $campaignDiscount : 0;

        return [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'category_id' => $service->category_id,
            'variant_key' => $vKey,
            'quantity' => $quantity,
            'service_cost' => $price,
            'discount_amount' => $basicDiscount,
            'campaign_discount' => $campaignDiscount,
            'coupon_discount' => 0,
            'coupon_code' => null,
            'tax_amount' => round($tax, 2),
            'total_cost' => round($subtotal - $basicDiscount - $campaignDiscount + $tax, 2)
        ];
    }

    private function applyCouponToLines(array $lineItems, string $couponCode, ?string $zoneId = null): array
    {
        $zoneId = $zoneId ?: Config::get('zone_id');

        $coupon = Coupon::withoutGlobalScope('zone_wise_data')
            ->with(['discount.discount_types'])
            ->where('coupon_code', $couponCode)
            ->whereHas('discount', function ($query) {
                $query->where('promotion_type', 'coupon')
                    ->where('is_active', 1)
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })
            ->when($zoneId, function ($query) use ($zoneId) {
                $query->whereHas('discount.discount_types', function ($q) use ($zoneId) {
                    $q->where(['discount_type' => 'zone', 'type_wise_id' => $zoneId]);
                });
            })
            ->latest()
            ->first();

        if (!$coupon || !$coupon->discount) {
            return $lineItems;
        }

        $discountedIds = $coupon->discount->discount_types->pluck('type_wise_id')->toArray();

        foreach ($lineItems as &$line) {
            if (!in_array($line['service_id'], $discountedIds) && !in_array($line['category_id'], $discountedIds)) {
                continue;
            }

            $service = Service::find($line['service_id']);
            if (!$service) {
                continue;
            }

            $applicableDiscount = max($line['discount_amount'], $line['campaign_discount']);
            $couponDiscountAmount = booking_discount_calculator(
                $coupon->discount,
                (($line['service_cost'] * $line['quantity']) - $applicableDiscount)
            );
            $subtotal = round($line['service_cost'] * $line['quantity'], 2);
            $tax = round((((($line['service_cost'] * $line['quantity']) - $applicableDiscount - $couponDiscountAmount) * 0) / 100), 2);

            $line['coupon_discount'] = $couponDiscountAmount;
            $line['coupon_code'] = $coupon->coupon_code;
            $line['tax_amount'] = $tax;
            $line['total_cost'] = round($subtotal - $applicableDiscount - $couponDiscountAmount + $tax, 2);
        }
        unset($line);

        return $lineItems;
    }

    private function findServiceByType(string $serviceId, string $serviceType = 'all'): ?Service
    {
        if ($serviceType === 'main') {
            return Service::withoutGlobalScopes()
                ->with(['category.category_discount', 'category.campaign_discount', 'service_discount', 'campaign_discount'])
                ->where('id', $serviceId)
                ->where('is_active', 1)
                ->first();
        }

        if ($serviceType === 'additional') {
            $addService = AdditionalService::where('id', $serviceId)->where('is_active', 1)->first();
            if ($addService) {
                $service = new Service();
                $service->id = $addService->id;
                $service->name = $addService->name;
                $service->category_id = null;
                $service->sub_category_id = null;
                $service->property_id = null;
                $service->min_bidding_price = $addService->price ?? 0;
                $service->visits_count = 1;
                $service->tax = 0;
                $service->is_active = 1;
                $service->exists = true;
                return $service;
            }
            return null;
        }

        $service = Service::withoutGlobalScopes()
            ->with(['category.category_discount', 'category.campaign_discount', 'service_discount', 'campaign_discount'])
            ->where('id', $serviceId)
            ->where('is_active', 1)
            ->first();

        if (!$service) {
            $addService = AdditionalService::where('id', $serviceId)->where('is_active', 1)->first();
            if ($addService) {
                $service = new Service();
                $service->id = $addService->id;
                $service->name = $addService->name;
                $service->category_id = null;
                $service->sub_category_id = null;
                $service->property_id = null;
                $service->min_bidding_price = $addService->price ?? 0;
                $service->visits_count = 1;
                $service->tax = 0;
                $service->is_active = 1;
                $service->exists = true;
            }
        }

        return $service;
    }
}

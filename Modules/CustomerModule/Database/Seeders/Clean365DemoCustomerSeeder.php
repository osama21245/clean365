<?php

namespace Modules\CustomerModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\PromotionManagement\Entities\Coupon;
use Modules\PromotionManagement\Entities\CouponCustomer;
use Modules\PromotionManagement\Entities\Discount;
use Modules\PromotionManagement\Entities\DiscountType;
use Modules\ReviewModule\Entities\Review;
use Modules\ServiceManagement\Entities\CustomerServiceSubscription;
use Modules\ServiceManagement\Entities\CustomerServiceSubscriptionVisit;
use Modules\ServiceManagement\Entities\Property;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;
use Modules\ZoneManagement\Entities\Zone;

/**
 * Seeds a fully testable demo customer aligned with Clean365 Postman flow:
 * profile → addresses → coupons → package confirm/rebook → bookings/subscriptions.
 *
 * Target customer (from production/demo DB):
 *   id:    6906ae23-9c84-4dc1-8c0b-3f06d26204c6
 *   email: customer@demo.com
 *   phone: +966500000009
 *   zone:  a1614dbe-4732-11ee-9702-dee6e8d77be4
 *
 * Lookup is by email first (id may change between environments).
 *
 * Idempotent — tagged with customer_note / coupon codes / address labels.
 *
 * Run:
 *   php artisan db:seed --class="Modules\\CustomerModule\\Database\\Seeders\\Clean365DemoCustomerSeeder" --force
 */
class Clean365DemoCustomerSeeder extends Seeder
{
    private const CUSTOMER_ID = '6906ae23-9c84-4dc1-8c0b-3f06d26204c6';
    private const CUSTOMER_EMAIL = 'customer@demo.com';
    private const CUSTOMER_PHONE = '+966500000009';
    private const ZONE_ID = 'a1614dbe-4732-11ee-9702-dee6e8d77be4';
    private const TAG = '[demo-seed]';

    private const PROPERTY_APARTMENT = '14b36665-1c8a-4ecc-bd2d-d2af0aef318f';
    private const PROPERTY_VILLA = '850d157d-8567-425e-a3fa-1269850d3106';
    private const PROPERTY_STUDIO = 'ec7351c3-ec53-4038-a979-61b3e25dffe8';

    public function run(): void
    {
        $zone = Zone::query()->where('id', self::ZONE_ID)->first()
            ?? Zone::query()->where('is_active', 1)->first();

        if (!$zone) {
            $this->command?->error('Clean365DemoCustomerSeeder: no zone found.');
            return;
        }



        $servicesReady = Service::withoutGlobalScopes()
            ->where('is_active', 1)
            ->exists();

        if (!$servicesReady) {
            $this->command?->error('Clean365DemoCustomerSeeder: catalog packages missing. Run Clean365CatalogSeeder first.');
            return;
        }

        $customer = null;
        DB::transaction(function () use ($zone, &$customer) {
            $customer = $this->ensureCustomer();
            $addresses = $this->seedAddresses($customer, $zone);
            $coupons = $this->seedCoupons($zone, $customer);
            $this->seedPackageJourney($customer, $zone, $addresses, $coupons);
        });

        $this->command?->info('Clean365DemoCustomerSeeder done.');
        $this->command?->info('Login: ' . self::CUSTOMER_EMAIL . ' / 12345678');
        $this->command?->info('Customer id: ' . ($customer?->id ?? self::CUSTOMER_ID));
        $this->command?->info('zoneId header: ' . $zone->id);
        $this->command?->info('Coupons: CLEAN36510, WELCOME50, DEMOONLY, ABDO12');
    }

    private function ensureCustomer(): User
    {
        // Prefer email — IDs change between envs / re-registers
        $customer = User::query()->where('email', self::CUSTOMER_EMAIL)->first()
            ?? User::query()->where('id', self::CUSTOMER_ID)->first()
            ?? User::query()->where('phone', self::CUSTOMER_PHONE)->first();

        if (!$customer) {
            User::withoutEvents(function () use (&$customer) {
                $customer = new User();
                $customer->id = self::CUSTOMER_ID;
                $customer->first_name = 'Abdo';
                $customer->last_name = 'Customer';
                $customer->email = self::CUSTOMER_EMAIL;
                $customer->phone = self::CUSTOMER_PHONE;
                $customer->password = Hash::make('12345678');
                $customer->user_type = 'customer';
                $customer->is_active = 1;
                $customer->is_phone_verified = 1;
                $customer->is_email_verified = 1;
                $customer->phone_verified_at = now();
                $customer->email_verified_at = now();
                $customer->current_language_key = 'ar';
                $customer->wallet_balance = 250;
                $customer->loyalty_point = 120;
                $customer->fcm_token = 'demo-fcm-customer-token';
                $customer->ref_code = 'C365DEMO';
                $customer->save();
            });
            return $customer->fresh();
        }

        $customer->first_name = 'Abdo';
        $customer->last_name = 'Customer';
        $customer->email = self::CUSTOMER_EMAIL;
        $customer->phone = self::CUSTOMER_PHONE;
        $customer->password = Hash::make('12345678');
        $customer->user_type = 'customer';
        $customer->is_active = 1;
        $customer->is_phone_verified = 1;
        $customer->is_email_verified = 1;
        $customer->phone_verified_at = $customer->phone_verified_at ?: now();
        $customer->email_verified_at = $customer->email_verified_at ?: now();
        $customer->current_language_key = 'ar';
        $customer->wallet_balance = max((float) ($customer->wallet_balance ?? 0), 250);
        $customer->loyalty_point = max((int) ($customer->loyalty_point ?? 0), 120);
        $customer->fcm_token = $customer->fcm_token ?: 'demo-fcm-customer-token';
        if (empty($customer->ref_code)) {
            $customer->ref_code = 'C365DEMO';
        }
        $customer->save();

        return $customer->fresh();
    }

    /**
     * @return array<string, UserAddress> keyed by apartment|villa|studio
     */
    private function seedAddresses(User $customer, Zone $zone): array
    {
        $defs = [
            'apartment' => [
                'property_id' => self::PROPERTY_APARTMENT,
                'label' => 'Home Apartment',
                'address' => 'حي النرجس، الرياض',
                'street' => 'شارع الأمير سعود',
                'house' => '12A',
                'floor' => '3',
                'notes' => self::TAG . ' البوابة يمين، الاتصال قبل الوصول',
                'lat' => '24.774265',
                'lon' => '46.738586'
            ],
            'villa' => [
                'property_id' => self::PROPERTY_VILLA,
                'label' => 'Family Villa',
                'address' => 'حي الياسمين، الرياض',
                'street' => 'طريق أنس بن مالك',
                'house' => 'Villa 7',
                'floor' => '1',
                'notes' => self::TAG . ' فيلا ركنية — جرس الباب الأيسر',
                'lat' => '24.813450',
                'lon' => '46.622180'
            ],
            'studio' => [
                'property_id' => self::PROPERTY_STUDIO,
                'label' => 'Work Studio',
                'address' => 'حي العليا، الرياض',
                'street' => 'شارع التسعين',
                'house' => 'B-204',
                'floor' => '2',
                'notes' => self::TAG . ' استوديو للمكتب — كود المصعد 204',
                'lat' => '24.714612',
                'lon' => '46.676565'
            ]
        ];

        $map = [];
        foreach ($defs as $key => $def) {
            $property = Property::query()->where('id', $def['property_id'])->where('is_active', 1)->first();
            if (!$property) {
                $nameMap = [
                    'apartment' => 'شقة',
                    'villa' => 'فيلا',
                    'studio' => 'استوديو'
                ];
                $property = Property::query()
                    ->where('is_active', 1)
                    ->when(isset($nameMap[$key]), fn($q) => $q->where('name', $nameMap[$key]))
                    ->first()
                    ?? Property::query()->where('is_active', 1)->first();
            }

            if (!$property) {
                continue;
            }

            $address = UserAddress::query()
                ->where('user_id', $customer->id)
                ->where('address_label', $def['label'])
                ->first();

            if (!$address) {
                $address = new UserAddress();
                $address->user_id = $customer->id;
            }

            $address->property_id = $property->id;
            $address->lat = $def['lat'];
            $address->lon = $def['lon'];
            $address->city = 'الرياض';
            $address->street = $def['street'];
            $address->zip_code = '11564';
            $address->country = 'SA';
            $address->address = $def['address'];
            $address->zone_id = $zone->id;
            $address->address_type = 'service';
            $address->contact_person_name = trim(($customer->first_name ?? 'Abdo') . ' ' . ($customer->last_name ?? 'Customer'));
            $address->contact_person_number = $customer->phone ?: self::CUSTOMER_PHONE;
            $address->address_label = $def['label'];
            $address->house = $def['house'];
            $address->floor = $def['floor'];
            if (Schema::hasColumn('user_addresses', 'notes')) {
                $address->notes = $def['notes'];
            }
            $address->is_guest = 0;
            $address->save();

            $map[$key] = $address;
        }

        return $map;
    }

    /**
     * @return array<string, string> coupon_code => coupon_code
     */
    private function seedCoupons(Zone $zone, User $customer): array
    {
        $defs = [
            [
                'code' => 'CLEAN36510',
                'title' => 'خصم 10٪ Clean365',
                'type' => 'default',
                'amount' => 10,
                'amount_type' => 'percent',
                'min_purchase' => 100,
                'max_discount' => 80,
                'limit' => 5
            ],
            [
                'code' => 'WELCOME50',
                'title' => 'ترحيب 50 ريال',
                'type' => 'default',
                'amount' => 50,
                'amount_type' => 'amount',
                'min_purchase' => 150,
                'max_discount' => 50,
                'limit' => 3
            ],
            [
                'code' => 'ABDO12',
                'title' => 'كوبون تجريبي ABDO12',
                'type' => 'default',
                'amount' => 20,
                'amount_type' => 'percent',
                'min_purchase' => 100,
                'max_discount' => 100,
                'limit' => 10
            ],
            [
                'code' => 'DEMOONLY',
                'title' => 'كوبون خاص بالعميل التجريبي',
                'type' => 'customer_wise',
                'amount' => 15,
                'amount_type' => 'percent',
                'min_purchase' => 80,
                'max_discount' => 60,
                'limit' => 5
            ]
        ];

        $codes = [];
        foreach ($defs as $def) {
            $coupon = Coupon::withoutGlobalScopes()->where('coupon_code', $def['code'])->first();

            if ($coupon) {
                $discount = Discount::withoutGlobalScopes()->find($coupon->discount_id);
            } else {
                $discount = new Discount();
                $coupon = new Coupon();
            }

            if (!$discount) {
                $discount = new Discount();
            }

            $discount->discount_title = $def['title'];
            $discount->discount_type = 'zone';
            $discount->discount_amount = $def['amount'];
            $discount->discount_amount_type = $def['amount_type'];
            $discount->min_purchase = $def['min_purchase'];
            $discount->max_discount_amount = $def['max_discount'];
            $discount->limit_per_user = $def['limit'];
            $discount->promotion_type = 'coupon';
            $discount->start_date = now()->subDays(7)->toDateString();
            $discount->end_date = now()->addMonths(6)->toDateString();
            $discount->is_active = 1;
            $discount->save();

            $coupon->coupon_code = $def['code'];
            $coupon->coupon_type = $def['type'];
            $coupon->discount_id = $discount->id;
            $coupon->is_active = 1;
            $coupon->save();

            DiscountType::query()->updateOrCreate(
                [
                    'discount_id' => $discount->id,
                    'discount_type' => 'zone',
                    'type_wise_id' => $zone->id
                ],
                []
            );

            if ($def['type'] === 'customer_wise') {
                CouponCustomer::query()->firstOrCreate([
                    'coupon_id' => $coupon->id,
                    'customer_user_id' => $customer->id
                ]);
            }

            $codes[$def['code']] = $def['code'];
        }

        return $codes;
    }

    private function seedPackageJourney(User $customer, Zone $zone, array $addresses, array $coupons): void
    {
        // Wipe previous demo bookings/subscriptions for this customer (tagged only).
        $this->cleanupPreviousDemo($customer);

        $cases = [
            // Active multi-visit package with rebook history (core Postman flow)
            [
                'key' => 'active-rebookable',
                'service_slug' => 'regular-cleaning-apartments-5-visits',
                'fallback_slugs' => ['regular-cleaning-apartments-3-visits', 'monthly-subscriptions-apartments-8-visits'],
                'address_key' => 'apartment',
                'property_id' => self::PROPERTY_APARTMENT,
                'coupon' => $coupons['CLEAN36510'] ?? 'CLEAN36510',
                'visits_used' => 2, // first confirm + 1 rebook → remaining = total-2
                'booking_statuses' => ['completed', 'pending'], // visit1 completed, visit2 pending (rebookable next)
                'note' => self::TAG . ' active package — ready for rebook'
            ],
            // Accepted booking (can reschedule)
            [
                'key' => 'accepted-reschedule',
                'service_slug' => 'deep-cleaning-villas-1-visit',
                'fallback_slugs' => ['deep-cleaning-apartments-1-visit', 'deep-cleaning-villas-2-visits'],
                'address_key' => 'villa',
                'property_id' => self::PROPERTY_VILLA,
                'coupon' => null,
                'visits_used' => 1,
                'booking_statuses' => ['accepted'],
                'note' => self::TAG . ' accepted — test reschedule'
            ],
            // Ongoing
            [
                'key' => 'ongoing',
                'service_slug' => 'hotel-setup-studios-1-setup',
                'fallback_slugs' => ['sanitization-studios-1-visit', 'regular-cleaning-studios-1-visit'],
                'address_key' => 'studio',
                'property_id' => self::PROPERTY_STUDIO,
                'coupon' => $coupons['WELCOME50'] ?? null,
                'visits_used' => 1,
                'booking_statuses' => ['ongoing'],
                'note' => self::TAG . ' ongoing visit'
            ],
            // Canceled
            [
                'key' => 'canceled',
                'service_slug' => 'sanitization-apartments-1-visit',
                'fallback_slugs' => ['books-cleaning-apartments-1-visit', 'linen-laundry-apartments-1-wash'],
                'address_key' => 'apartment',
                'property_id' => self::PROPERTY_APARTMENT,
                'coupon' => null,
                'visits_used' => 1,
                'booking_statuses' => ['canceled'],
                'note' => self::TAG . ' canceled booking'
            ],
            // Finished subscription (no remaining visits) — rebook should fail
            [
                'key' => 'finished-subscription',
                'service_slug' => 'regular-cleaning-studios-1-visit',
                'fallback_slugs' => ['linen-laundry-studios-1-wash'],
                'address_key' => 'studio',
                'property_id' => self::PROPERTY_STUDIO,
                'coupon' => $coupons['ABDO12'] ?? 'ABDO12',
                'visits_used' => null, // use full visits_count
                'booking_statuses' => ['completed'],
                'force_finished' => true,
                'note' => self::TAG . ' finished package — rebook must fail'
            ],
            // Monthly subscription with remaining visits + accepted next visit
            [
                'key' => 'monthly-active',
                'service_slug' => 'monthly-subscriptions-apartments-4-visits',
                'fallback_slugs' => ['monthly-subscriptions-apartments-8-visits'],
                'address_key' => 'apartment',
                'property_id' => self::PROPERTY_APARTMENT,
                'coupon' => $coupons['DEMOONLY'] ?? null,
                'visits_used' => 1,
                'booking_statuses' => ['accepted'],
                'note' => self::TAG . ' monthly subscription active'
            ]
        ];

        foreach ($cases as $index => $case) {
            $service = $this->resolveService($case);
            if (!$service) {
                $this->command?->warn("Skip {$case['key']}: service not found.");
                continue;
            }

            $address = $addresses[$case['address_key']] ?? reset($addresses);
            if (!$address) {
                $this->command?->warn("Skip {$case['key']}: address missing.");
                continue;
            }

            // Ensure address property matches service property when possible
            $propertyId = $service->property_id ?: ($case['property_id'] ?? $address->property_id);
            if ($service->property_id && $address->property_id !== $service->property_id) {
                // Prefer address that matches service property
                foreach ($addresses as $addr) {
                    if ($addr->property_id === $service->property_id) {
                        $address = $addr;
                        $propertyId = $addr->property_id;
                        break;
                    }
                }
            }

            $variation = Variation::withoutGlobalScopes()
                ->where('service_id', $service->id)
                ->where('zone_id', $zone->id)
                ->first()
                ?? Variation::withoutGlobalScopes()->where('service_id', $service->id)->first();

            $unitPrice = (float) ($variation->price ?? $service->min_bidding_price ?? 200);
            $variantKey = $variation->variant_key ?? 'default';
            $totalVisits = max(1, (int) $service->visits_count);
            $used = $case['force_finished'] ?? false
                ? $totalVisits
                : min($totalVisits, (int) ($case['visits_used'] ?? 1));
            $remaining = max(0, $totalVisits - $used);
            $status = ($case['force_finished'] ?? false) || $remaining === 0 ? 'finished' : 'active';

            $subscription = CustomerServiceSubscription::create([
                'user_id' => $customer->id,
                'service_id' => $service->id,
                'property_id' => $propertyId,
                'total_visits' => $totalVisits,
                'remaining_visits' => $remaining,
                'status' => $status
            ]);

            $statuses = $case['booking_statuses'];
            // Pad statuses to match used visit count
            while (count($statuses) < $used) {
                $statuses[] = 'completed';
            }
            $statuses = array_slice($statuses, 0, $used);

            $firstBookingId = null;
            foreach ($statuses as $visitIndex => $bookingStatus) {
                $couponCode = $visitIndex === 0 ? ($case['coupon'] ?? null) : null;
                $booking = $this->createBooking(
                    customer: $customer,
                    zone: $zone,
                    service: $service,
                    address: $address,
                    subscription: $subscription,
                    status: $bookingStatus,
                    unitPrice: $unitPrice,
                    variantKey: $variantKey,
                    couponCode: $couponCode,
                    scheduleAt: now()->addDays($visitIndex + $index)->setTime(10 + $visitIndex, 30),
                    note: $case['note'] . " · visit " . ($visitIndex + 1),
                );

                CustomerServiceSubscriptionVisit::create([
                    'subscription_id' => $subscription->id,
                    'booking_id' => $booking->id,
                    'service_id' => $service->id
                ]);

                if ($visitIndex === 0) {
                    $firstBookingId = $booking->id;
                }

                if ($bookingStatus === 'completed') {
                    $this->seedReview($customer, $booking, $service);
                }
            }

            if ($firstBookingId) {
                $this->command?->line("  ✓ {$case['key']}: service={$service->slug} sub={$subscription->id} remaining={$remaining}");
            }
        }
    }

    private function cleanupPreviousDemo(User $customer): void
    {
        $demoBookingIds = Booking::query()
            ->where('customer_id', $customer->id)
            ->where('customer_note', 'like', self::TAG . '%')
            ->pluck('id');

        if ($demoBookingIds->isNotEmpty()) {
            CustomerServiceSubscriptionVisit::query()->whereIn('booking_id', $demoBookingIds)->delete();
            BookingDetailsAmount::query()->whereIn('booking_id', $demoBookingIds)->delete();
            BookingDetail::query()->whereIn('booking_id', $demoBookingIds)->delete();
            BookingStatusHistory::query()->whereIn('booking_id', $demoBookingIds)->delete();
            BookingScheduleHistory::query()->whereIn('booking_id', $demoBookingIds)->delete();
            Review::query()->whereIn('booking_id', $demoBookingIds)->delete();
            Booking::withoutEvents(function () use ($demoBookingIds) {
                Booking::query()->whereIn('id', $demoBookingIds)->delete();
            });
        }

        // Remove orphan demo subscriptions that no longer have visits
        $subs = CustomerServiceSubscription::query()->where('user_id', $customer->id)->get();
        foreach ($subs as $sub) {
            $hasVisits = CustomerServiceSubscriptionVisit::query()->where('subscription_id', $sub->id)->exists();
            if (!$hasVisits) {
                $sub->delete();
            }
        }
    }

    private function resolveService(array $case): ?Service
    {
        $slugs = array_merge([$case['service_slug']], $case['fallback_slugs'] ?? []);
        foreach ($slugs as $slug) {
            $service = Service::withoutGlobalScopes()
                ->where('slug', $slug)
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->first();
            if ($service) {
                return $service;
            }
        }

        // Last resort: any multi-visit package for the target property
        $propertyId = $case['property_id'] ?? null;
        return Service::withoutGlobalScopes()
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->where('visits_count', '>=', 1)
            ->orderByDesc('visits_count')
            ->first();
    }

    private function createBooking(
        User $customer,
        Zone $zone,
        Service $service,
        UserAddress $address,
        CustomerServiceSubscription $subscription,
        string $status,
        float $unitPrice,
        string $variantKey,
        ?string $couponCode,
        $scheduleAt,
        string $note,
    ): Booking {
        $taxRate = (float) ($service->tax ?? 15);
        $couponDiscount = 0.0;
        if ($couponCode) {
            $couponDiscount = round(min($unitPrice * 0.10, 80), 2);
        }
        $taxable = max(0, $unitPrice - $couponDiscount);
        $tax = round($taxable * $taxRate / 100, 2);
        $total = round($taxable + $tax, 2);

        $booking = null;
        Booking::withoutEvents(function () use (&$booking, $customer, $zone, $service, $address, $subscription, $status, $unitPrice, $variantKey, $couponCode, $scheduleAt, $note, $couponDiscount, $tax, $total) {
            $booking = new Booking();
            $booking->id = (string) Str::uuid();
            $booking->readable_id = (int) (Booking::query()->count() + 100000 + random_int(1, 999));
            $booking->customer_id = $customer->id;
            $booking->provider_id = null;
            $booking->category_id = $service->category_id;
            $booking->sub_category_id = $service->sub_category_id;
            $booking->zone_id = $zone->id;
            $booking->booking_status = $status;
            $booking->is_paid = in_array($status, ['completed', 'ongoing'], true) ? 1 : 0;
            $booking->payment_method = 'cash_after_service';
            $booking->transaction_id = 'cash-payment';
            $booking->total_booking_amount = $total;
            $booking->total_tax_amount = $tax;
            $booking->total_discount_amount = 0;
            $booking->total_campaign_discount_amount = 0;
            $booking->total_coupon_discount_amount = $couponDiscount;
            $booking->coupon_code = $couponCode;
            $booking->service_schedule = $scheduleAt;
            $booking->service_address_id = $address->id;
            $booking->is_guest = 0;
            $booking->extra_fee = 0;
            $booking->total_referral_discount_amount = 0;
            $booking->service_address_location = json_encode($address->toArray());
            $booking->service_location = 'customer';
            $booking->customer_note = $note;
            $booking->subscription_id = $subscription->id;
            $booking->is_checked = 0;
            $booking->is_verified = in_array($status, ['completed', 'ongoing'], true) ? 1 : 0;
            $booking->save();

            $detail = new BookingDetail();
            $detail->booking_id = $booking->id;
            $detail->service_id = $service->id;
            $detail->service_name = $service->name;
            $detail->variant_key = $variantKey;
            $detail->quantity = 1;
            $detail->service_cost = $unitPrice;
            $detail->discount_amount = 0;
            $detail->campaign_discount_amount = 0;
            $detail->overall_coupon_discount_amount = $couponDiscount;
            $detail->tax_amount = $tax;
            $detail->total_cost = $total;
            $detail->save();

            $amount = new BookingDetailsAmount();
            $amount->id = (string) Str::uuid();
            $amount->booking_details_id = $detail->id;
            $amount->booking_id = $booking->id;
            $amount->service_unit_cost = $unitPrice;
            $amount->service_quantity = 1;
            $amount->service_tax = $tax;
            $amount->discount_by_admin = 0;
            $amount->discount_by_provider = 0;
            $amount->campaign_discount_by_admin = 0;
            $amount->campaign_discount_by_provider = 0;
            $amount->coupon_discount_by_admin = $couponDiscount;
            $amount->coupon_discount_by_provider = 0;
            BookingDetailsAmount::withoutEvents(fn() => $amount->save());

            $schedule = new BookingScheduleHistory();
            $schedule->booking_id = $booking->id;
            $schedule->changed_by = $customer->id;
            $schedule->is_guest = 0;
            $schedule->schedule = $booking->service_schedule;
            $schedule->save();

            $history = new BookingStatusHistory();
            $history->changed_by = $customer->id;
            $history->booking_id = $booking->id;
            $history->is_guest = 0;
            $history->booking_status = $status;
            $history->save();
        });

        return $booking;
    }

    private function seedReview(User $customer, Booking $booking, Service $service): void
    {
        $exists = Review::query()
            ->where('booking_id', $booking->id)
            ->where('service_id', $service->id)
            ->where('customer_id', $customer->id)
            ->exists();

        if ($exists) {
            return;
        }

        $review = new Review();
        $review->id = (string) Str::uuid();
        $review->booking_id = $booking->id;
        $review->service_id = $service->id;
        $review->customer_id = $customer->id;
        $review->provider_id = $booking->provider_id;
        $review->review_rating = 5;
        $review->review_comment = self::TAG . ' خدمة ممتازة — بيانات تجريبية';
        $review->review_images = [];
        $review->booking_date = $booking->created_at ?? now();
        $review->is_active = 1;
        if (Schema::hasColumn('reviews', 'readable_id')) {
            $review->readable_id = (int) (($booking->readable_id ?? 100000) . '01');
        }
        $review->save();
    }
}

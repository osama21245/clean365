<?php

namespace Modules\CustomerModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;
use Modules\ZoneManagement\Entities\Zone;

/**
 * Seeds home-feed data for the customer app:
 * - Push notifications (GET /customer/notification)
 * - Popular services  (GET /customer/service/popular)  → needs booking_details rows
 * - Recommended       (GET /customer/service/recommended) → uses customer's booked category_ids
 *
 * Conditions (from ServiceController):
 * - popular: orderBy bookings_count DESC; if any booking exists system-wide → only services that HAS booking_details
 * - recommended (auth): services in categories the customer already booked; else random
 *
 * Run:
 *   php artisan db:seed --class="Modules\\CustomerModule\\Database\\Seeders\\Clean365HomeFeedSeeder" --force
 */
class Clean365HomeFeedSeeder extends Seeder
{
    private const CUSTOMER_ID = '6906ae23-9c84-4dc1-8c0b-3f06d26204c6';
    private const CUSTOMER_EMAIL = 'customer@demo.com';
    private const ZONE_ID = 'a1614dbe-4732-11ee-9702-dee6e8d77be4';
    private const TAG = '[demo-home-feed]';

    public function run(): void
    {
        $zone = Zone::query()->where('id', self::ZONE_ID)->first()
            ?? Zone::query()->where('is_active', 1)->first();

        if (!$zone) {
            $this->command?->error('Clean365HomeFeedSeeder: no zone found.');
            return;
        }

        $customer = User::query()->where('email', self::CUSTOMER_EMAIL)->where('user_type', 'customer')->first()
            ?? User::query()->where('id', self::CUSTOMER_ID)->first();

        if (!$customer) {
            $this->command?->error('Clean365HomeFeedSeeder: demo customer missing. Run Clean365DemoCustomerSeeder first.');
            return;
        }

        DB::transaction(function () use ($zone, $customer) {
            $this->seedNotifications($zone, $customer);
            $this->seedPopularBookingCounts($zone, $customer);
        });

        $this->command?->info('Clean365HomeFeedSeeder done.');
        $this->command?->info('Test with zoneId=' . $zone->id . ' as logged-in customer@demo.com');
    }

    private function seedNotifications(Zone $zone, User $customer): void
    {
        // Remove previous demo notifications (by title prefix)
        PushNotification::query()
            ->where(function ($q) {
                $q->where('title', 'like', 'Clean365%')
                    ->orWhere('description', 'like', self::TAG . '%');
            })
            ->get()
            ->each(fn(PushNotification $n) => $n->delete());

        $items = [
            [
                'title' => 'Clean365 — مرحبًا بك',
                'description' => self::TAG . ' اكتشف باقات التنظيف واحجز زيارتك الأولى بسهولة.'
            ],
            [
                'title' => 'Clean365 — خصم خاص',
                'description' => self::TAG . ' استخدم الكوبون CLEAN36510 واحصل على خصم 10٪ على باقتك.'
            ],
            [
                'title' => 'Clean365 — تذكير الزيارة',
                'description' => self::TAG . ' لديك زيارة قادمة — يمكنك إعادة الجدولة أو طلب زيارة جديدة من اشتراكك.'
            ],
            [
                'title' => 'Clean365 — باقات شهرية',
                'description' => self::TAG . ' وفّر أكثر مع الاشتراكات الشهرية (٤ / ٨ / ١٢ زيارة).'
            ],
            [
                'title' => 'Clean365 — تقييم الخدمة',
                'description' => self::TAG . ' قيّم زياراتك المكتملة لمساعدتنا على تحسين الخدمة.'
            ]
        ];

        // Notifications must be created_at >= customer.created_at
        $baseTime = ($customer->created_at ?? now())->copy()->addMinute();

        foreach ($items as $i => $item) {
            PushNotification::withoutEvents(function () use ($item, $zone, $baseTime, $i) {
                $n = new PushNotification();
                $n->id = (string) Str::uuid();
                $n->title = $item['title'];
                $n->description = $item['description'];
                $n->cover_image = null;
                $n->zone_ids = [$zone->id];
                $n->to_users = ['customer'];
                $n->is_active = 1;
                $n->created_at = $baseTime->copy()->addMinutes($i + 1);
                $n->updated_at = now();
                $n->save();
            });
        }

        $this->command?->info('  ✓ seeded ' . count($items) . ' customer push notifications');
    }

    /**
     * Popular endpoint ranks by booking_details count (relation name: bookings).
     * Create lightweight completed bookings + details for top packages.
     */
    private function seedPopularBookingCounts(Zone $zone, User $customer): void
    {
        $address = UserAddress::query()->where('user_id', $customer->id)->latest()->first();
        if (!$address) {
            $this->command?->warn('  ! no address for customer — skip popular boost');
            return;
        }

        // Preferred popular packages (highest synthetic booking volume first)
        $popularSlugs = [
            'one-bedroom-package' => 15,
            'studio-package' => 12,
            'two-bedroom-package' => 9,
            'three-bedroom-package' => 6
        ];

        // Clean previous popularity boost bookings
        $oldIds = Booking::query()
            ->where('customer_id', $customer->id)
            ->where('customer_note', 'like', self::TAG . '%')
            ->pluck('id');

        if ($oldIds->isNotEmpty()) {
            BookingDetail::query()->whereIn('booking_id', $oldIds)->delete();
            Booking::withoutEvents(fn() => Booking::query()->whereIn('id', $oldIds)->delete());
        }

        $boosted = 0;
        foreach ($popularSlugs as $slug => $hits) {
            $service = Service::withoutGlobalScopes()
                ->where('slug', $slug)
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->first();

            if (!$service) {
                // fallback: any active package under same prefix family
                $service = Service::withoutGlobalScopes()
                    ->where('is_active', 1)
                    ->whereNull('deleted_at')
                    ->where('name', 'like', '%شقق%')
                    ->orderByDesc('visits_count')
                    ->skip($boosted)
                    ->first();
            }

            if (!$service) {
                continue;
            }

            $variation = Variation::withoutGlobalScopes()
                ->where('service_id', $service->id)
                ->where('zone_id', $zone->id)
                ->first()
                ?? Variation::withoutGlobalScopes()->where('service_id', $service->id)->first();

            $price = (float) ($variation->price ?? $service->min_bidding_price ?? 200);
            $variantKey = $variation->variant_key ?? 'default';

            for ($i = 0; $i < $hits; $i++) {
                $this->createPopularityBooking(
                    customer: $customer,
                    zone: $zone,
                    service: $service,
                    address: $address,
                    unitPrice: $price,
                    variantKey: $variantKey,
                    index: $i,
                );
            }

            // Also mirror into order_count (used by most_loved sort)
            $service->order_count = max((int) $service->order_count, $hits);
            $service->save();
            $boosted++;
        }

        $this->command?->info("  ✓ boosted popular services ({$boosted} packages with booking_details)");
    }

    private function createPopularityBooking(
        User $customer,
        Zone $zone,
        Service $service,
        UserAddress $address,
        float $unitPrice,
        string $variantKey,
        int $index,
    ): void {
        $tax = round($unitPrice * ((float) ($service->tax ?? 15)) / 100, 2);
        $total = round($unitPrice + $tax, 2);

        Booking::withoutEvents(function () use ($customer, $zone, $service, $address, $unitPrice, $variantKey, $index, $tax, $total) {
            $booking = new Booking();
            $booking->id = (string) Str::uuid();
            $booking->readable_id = (int) (Booking::query()->count() + 100000 + random_int(1, 9999));
            $booking->customer_id = $customer->id;
            $booking->provider_id = null;
            $booking->category_id = $service->category_id;
            $booking->sub_category_id = $service->sub_category_id;
            $booking->zone_id = $zone->id;
            $booking->booking_status = 'completed';
            $booking->is_paid = 1;
            $booking->payment_method = 'cash_after_service';
            $booking->transaction_id = 'cash-payment';
            $booking->total_booking_amount = $total;
            $booking->total_tax_amount = $tax;
            $booking->total_discount_amount = 0;
            $booking->total_campaign_discount_amount = 0;
            $booking->total_coupon_discount_amount = 0;
            $booking->service_schedule = now()->subDays($index + 1)->setTime(10, 0);
            $booking->service_address_id = $address->id;
            $booking->is_guest = 0;
            $booking->extra_fee = 0;
            $booking->total_referral_discount_amount = 0;
            $booking->service_address_location = json_encode($address->toArray());
            $booking->service_location = 'customer';
            $booking->customer_note = self::TAG . ' popularity boost';
            $booking->is_checked = 1;
            $booking->is_verified = 1;
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
            $detail->overall_coupon_discount_amount = 0;
            $detail->tax_amount = $tax;
            $detail->total_cost = $total;
            $detail->save();
        });
    }
}

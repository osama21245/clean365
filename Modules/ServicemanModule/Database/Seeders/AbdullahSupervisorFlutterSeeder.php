<?php

namespace Modules\ServicemanModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ServicemanModule\Entities\BookingAttendance;
use Modules\ServicemanModule\Entities\BookingExecutionNote;
use Modules\ServicemanModule\Entities\BookingSupportRequest;
use Modules\ServicemanModule\Entities\SupervisorTeam;
use Modules\ServicemanModule\Entities\SupervisorTeamServiceman;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;

/**
 * Flutter QA data for Abdullah Khalaf:
 * - pending bookings (admin can assign supervisor + team)
 * - many assigned jobs at different times
 * - team / client / location / progress like the supervisor app screens
 *
 * php artisan db:seed --class="Modules\\ServicemanModule\\Database\\Seeders\\AbdullahSupervisorFlutterSeeder" --force
 */
class AbdullahSupervisorFlutterSeeder extends Seeder
{
    private const TAG = '[flutter-seed-abdullah]';
    private const PROVIDER_ID = 'e54e7a25-854d-42a1-8e00-e087c66f497c';
    private const SUPERVISOR_USER_ID = '71a724b4-3d9b-4e3a-ad9c-c3b870b655de';
    private const TEAM_RAWDAH_ID = 'bdc8afda-fd24-4f3d-9723-6d68045118ec';
    private const TEAM_ONE_ID = 'c1e2f3a4-b5c6-4789-8d0e-1f2a3b4c5d6e';
    private const ZONE_ID = 'a1614dbe-4732-11ee-9702-dee6e8d77be4';
    private const CUSTOMER_ID = '6906ae23-9c84-4dc1-8c0b-3f06d26204c6';
    private const ADDRESS_ID = 18;
    private const SERVICEMEN = [
        '0c87c261-b21a-4c44-b774-d0a33b6b6e1b',
        '40669922-c1f1-47fa-9089-4aba7df0ed32',
        '5947a190-5d9b-4595-acf5-0c9ab248d3a0',
    ];

    public function run(): void
    {
        $provider = Provider::query()->find(self::PROVIDER_ID);
        $supervisor = User::query()->find(self::SUPERVISOR_USER_ID);
        $teamRawdah = SupervisorTeam::query()->find(self::TEAM_RAWDAH_ID);
        $baseCustomer = User::query()->find(self::CUSTOMER_ID);
        $baseAddress = UserAddress::query()->find(self::ADDRESS_ID);

        if (!$provider || !$supervisor || !$teamRawdah || !$baseCustomer || !$baseAddress) {
            $this->command?->error('Missing Abdullah provider/team/customer/address.');
            return;
        }

        $service = Service::withoutGlobalScopes()
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->whereNotNull('sub_category_id')
            ->orderBy('created_at')
            ->first();

        if (!$service) {
            $this->command?->error('No active service found. Seed the catalog first.');
            return;
        }

        $this->subscribeProvider($provider, $service);
        $teamOne = $this->ensureSecondTeam($provider);
        $clients = $this->ensureClients($baseCustomer, $baseAddress);

        $variation = Variation::withoutGlobalScopes()
            ->where('service_id', $service->id)
            ->where('zone_id', self::ZONE_ID)
            ->first()
            ?? Variation::withoutGlobalScopes()->where('service_id', $service->id)->first();

        $unitPrice = (float) ($variation->price ?? 350);
        $variantKey = $variation->variant_key ?? 'default';

        $this->cleanupPrevious();

        $cases = [
            // Admin can assign these (no supervisor / no team yet)
            ['key' => 'pending-morning', 'booking_status' => 'pending', 'field_status' => null, 'progress' => 0, 'schedule' => Carbon::tomorrow()->setTime(9, 0), 'provider' => false, 'team' => null, 'client' => 0, 'attendance' => false, 'note' => 'يرجى التركيز على المطبخ والحمامات.'],
            ['key' => 'pending-noon', 'booking_status' => 'pending', 'field_status' => null, 'progress' => 0, 'schedule' => Carbon::tomorrow()->setTime(12, 30), 'provider' => false, 'team' => null, 'client' => 1, 'attendance' => false, 'note' => 'فيلا دورين — المدخل من الخلف.'],
            ['key' => 'pending-evening', 'booking_status' => 'pending', 'field_status' => null, 'progress' => 0, 'schedule' => Carbon::tomorrow()->setTime(16, 0), 'provider' => false, 'team' => null, 'client' => 2, 'attendance' => false, 'note' => 'استوديو مكتب — كود المصعد 204.'],

            // Supervisor app — assigned jobs at different times
            ['key' => 'assigned-09', 'booking_status' => 'accepted', 'field_status' => 'assigned', 'progress' => 0, 'schedule' => Carbon::today()->setTime(9, 0), 'provider' => true, 'team' => 'one', 'client' => 0, 'attendance' => false, 'note' => 'شقة — الدور الثالث.'],
            ['key' => 'assigned-13', 'booking_status' => 'accepted', 'field_status' => 'assigned', 'progress' => 0, 'schedule' => Carbon::today()->setTime(13, 0), 'provider' => true, 'team' => 'rawdah', 'client' => 1, 'attendance' => false, 'note' => 'يرجى تعقيم الأسطح.'],
            ['key' => 'on-the-way-11', 'booking_status' => 'accepted', 'field_status' => 'on_the_way', 'progress' => 20, 'schedule' => Carbon::today()->setTime(11, 0), 'provider' => true, 'team' => 'one', 'client' => 2, 'attendance' => false, 'note' => 'الوصول من بوابة النرجس.'],
            ['key' => 'arrived-15', 'booking_status' => 'accepted', 'field_status' => 'arrived', 'progress' => 40, 'schedule' => Carbon::today()->setTime(15, 0), 'provider' => true, 'team' => 'rawdah', 'client' => 0, 'attendance' => true, 'note' => 'العميل في المنزل.'],
            ['key' => 'in-progress-16', 'booking_status' => 'ongoing', 'field_status' => 'in_progress', 'progress' => 70, 'schedule' => Carbon::today()->setTime(16, 0), 'provider' => true, 'team' => 'rawdah', 'client' => 0, 'attendance' => true, 'note' => 'يرجى التركيز على تنظيف المطبخ والحمامات، والتأكد من تعقيم الأسطح.'],
            ['key' => 'in-progress-18', 'booking_status' => 'ongoing', 'field_status' => 'in_progress', 'progress' => 70, 'schedule' => Carbon::today()->setTime(18, 30), 'provider' => true, 'team' => 'one', 'client' => 1, 'attendance' => true, 'note' => 'تنظيف عام للفيلا.'],
            ['key' => 'completed-yesterday', 'booking_status' => 'completed', 'field_status' => 'completed', 'progress' => 100, 'schedule' => Carbon::yesterday()->setTime(16, 0), 'provider' => true, 'team' => 'rawdah', 'client' => 2, 'attendance' => true, 'note' => 'تمت المهمة بالكامل.'],
        ];

        $created = [];
        foreach ($cases as $case) {
            $client = $clients[$case['client']] ?? $clients[0];
            $team = match ($case['team']) {
                'one' => $teamOne,
                'rawdah' => $teamRawdah,
                default => null,
            };

            $booking = $this->createJob(
                $case['provider'] ? $provider : null,
                $supervisor,
                $team,
                $client['user'],
                $client['address'],
                $service,
                $unitPrice,
                $variantKey,
                $case
            );

            $created[] = [
                'key' => $case['key'],
                'booking_id' => $booking->id,
                'readable_id' => $booking->readable_id,
                'booking_status' => $booking->booking_status,
                'field_status' => $booking->field_status,
                'schedule' => optional($booking->service_schedule)->toDateTimeString(),
                'team' => $team?->name,
                'client' => trim($client['user']->first_name . ' ' . $client['user']->last_name),
                'location' => $client['address']->address,
            ];
        }

        $this->command?->info('Flutter seed ready. Pending jobs can be assigned from admin booking details.');
        $this->command?->info('Supervisor login: v_j@hotmail.com');
        $this->command?->line(json_encode($created, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function ensureSecondTeam(Provider $provider): SupervisorTeam
    {
        $team = SupervisorTeam::query()->find(self::TEAM_ONE_ID);
        if (!$team) {
            $team = new SupervisorTeam();
            $team->id = self::TEAM_ONE_ID;
            $team->provider_id = $provider->id;
            $team->name = 'فريق 1';
            $team->is_active = true;
            $team->save();
        }

        foreach (array_slice(self::SERVICEMEN, 0, 2) as $servicemanId) {
            SupervisorTeamServiceman::query()->firstOrCreate([
                'team_id' => $team->id,
                'serviceman_id' => $servicemanId,
            ]);
        }

        return $team;
    }

    /**
     * @return array<int, array{user: User, address: UserAddress}>
     */
    private function ensureClients(User $baseCustomer, UserAddress $baseAddress): array
    {
        $defs = [
            [
                'email' => 'flutter-client-narjis@clean365.test',
                'first_name' => 'عبدالرحمن',
                'last_name' => 'جميل',
                'phone' => '+966551234567',
                'address' => 'حي النرجس، الرياض',
                'street' => 'حي النرجس',
                'lat' => '24.830100',
                'lon' => '46.705600',
                'type' => 'شقة',
                'house' => 'الدور الثالث',
                'notes' => self::TAG . ' شقة — الدور الثالث',
            ],
            [
                'email' => 'flutter-client-yasmin@clean365.test',
                'first_name' => 'محمد',
                'last_name' => 'سالم',
                'phone' => '+966550001122',
                'address' => 'حي الياسمين، الرياض',
                'street' => 'حي الياسمين',
                'lat' => '24.822400',
                'lon' => '46.640800',
                'type' => 'فيلا',
                'house' => 'فيلا',
                'notes' => self::TAG . ' فيلا',
            ],
            [
                'email' => $baseCustomer->email,
                'first_name' => $baseCustomer->first_name,
                'last_name' => $baseCustomer->last_name,
                'phone' => $baseCustomer->phone,
                'existing_user' => $baseCustomer,
                'existing_address' => $baseAddress,
            ],
        ];

        $clients = [];
        foreach ($defs as $def) {
            if (!empty($def['existing_user'])) {
                $clients[] = ['user' => $def['existing_user'], 'address' => $def['existing_address']];
                continue;
            }

            $user = User::query()->where('email', $def['email'])->first();
            if (!$user) {
                $user = new User();
                $user->first_name = $def['first_name'];
                $user->last_name = $def['last_name'];
                $user->email = $def['email'];
                $user->phone = $def['phone'];
                $user->password = Hash::make('password123');
                $user->user_type = 'customer';
                $user->is_active = 1;
                $user->is_phone_verified = 1;
                $user->save();
            }

            $address = UserAddress::query()
                ->where('user_id', $user->id)
                ->where('address', $def['address'])
                ->first();

            if (!$address) {
                $address = UserAddress::unguarded(function () use ($user, $def) {
                    return UserAddress::create([
                        'user_id' => $user->id,
                        'lat' => $def['lat'],
                        'lon' => $def['lon'],
                        'city' => 'الرياض',
                        'street' => $def['street'],
                        'zip_code' => '13322',
                        'country' => 'SA',
                        'address' => $def['address'],
                        'address_type' => $def['type'],
                        'contact_person_name' => trim($def['first_name'] . ' ' . $def['last_name']),
                        'contact_person_number' => $def['phone'],
                        'zone_id' => self::ZONE_ID,
                        'house' => $def['house'],
                        'floor' => $def['type'] === 'شقة' ? '3' : '1',
                        'notes' => $def['notes'],
                    ]);
                });
            }

            $clients[] = ['user' => $user, 'address' => $address];
        }

        return $clients;
    }

    private function subscribeProvider(Provider $provider, Service $service): void
    {
        $subCategory = Category::query()->find($service->sub_category_id);
        $categoryId = $service->category_id ?: $subCategory?->parent_id;
        if (!$categoryId || !$service->sub_category_id) {
            return;
        }

        SubscribedService::query()->updateOrCreate(
            ['provider_id' => $provider->id, 'sub_category_id' => $service->sub_category_id],
            ['category_id' => $categoryId, 'is_subscribed' => 1]
        );
    }

    private function cleanupPrevious(): void
    {
        $ids = Booking::query()
            ->where('customer_note', 'like', self::TAG . '%')
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        BookingAttendance::query()->whereIn('booking_id', $ids)->delete();
        BookingExecutionNote::query()->whereIn('booking_id', $ids)->delete();
        BookingSupportRequest::query()->whereIn('booking_id', $ids)->delete();
        BookingDetailsAmount::query()->whereIn('booking_id', $ids)->delete();
        BookingDetail::query()->whereIn('booking_id', $ids)->delete();
        BookingStatusHistory::query()->whereIn('booking_id', $ids)->delete();
        BookingScheduleHistory::query()->whereIn('booking_id', $ids)->delete();
        Booking::withoutEvents(fn () => Booking::query()->whereIn('id', $ids)->delete());
    }

    private function createJob(
        ?Provider $provider,
        User $supervisor,
        ?SupervisorTeam $team,
        User $customer,
        UserAddress $address,
        Service $service,
        float $unitPrice,
        string $variantKey,
        array $case
    ): Booking {
        $tax = round($unitPrice * ((float) ($service->tax ?? 15)) / 100, 2);
        $total = round($unitPrice + $tax, 2);
        $booking = null;

        Booking::withoutEvents(function () use (
            &$booking, $provider, $supervisor, $team, $customer, $address, $service,
            $unitPrice, $variantKey, $case, $tax, $total
        ) {
            $booking = new Booking();
            $booking->id = (string) Str::uuid();
            $booking->readable_id = (int) (Booking::query()->max('readable_id') + random_int(2, 9));
            $booking->customer_id = $customer->id;
            $booking->provider_id = $provider?->id;
            $booking->zone_id = self::ZONE_ID;
            $booking->category_id = $service->category_id;
            $booking->sub_category_id = $service->sub_category_id;
            $booking->team_id = $team?->id;
            $booking->booking_status = $case['booking_status'];
            $booking->field_status = $case['field_status'];
            $booking->progress_percent = $case['progress'];
            $booking->is_paid = $case['field_status'] === 'completed' ? 1 : 0;
            $booking->payment_method = 'cash_after_service';
            $booking->transaction_id = $case['booking_status'] === 'pending' ? null : 'cash-payment';
            $booking->total_booking_amount = $total;
            $booking->total_tax_amount = $tax;
            $booking->total_discount_amount = 0;
            $booking->total_campaign_discount_amount = 0;
            $booking->total_coupon_discount_amount = 0;
            $booking->service_schedule = $case['schedule'];
            $booking->service_address_id = $address->id;
            $booking->service_address_location = json_encode($address->toArray());
            $booking->service_location = 'customer';
            $booking->customer_note = self::TAG . ' ' . $case['key'] . ' · ' . ($case['note'] ?? '');
            $booking->extra_fee = 0;
            $booking->is_checked = 0;
            $booking->is_verified = $case['field_status'] === 'completed' ? 1 : 0;
            $booking->before_images = $case['attendance'] ? [['image' => 'flutter-seed-before.jpg', 'storage' => 'public']] : null;
            $booking->during_images = $case['field_status'] === 'in_progress' ? [['image' => 'flutter-seed-during.jpg', 'storage' => 'public']] : null;
            $booking->after_images = $case['field_status'] === 'completed' ? [['image' => 'flutter-seed-after.jpg', 'storage' => 'public']] : null;
            $booking->save();

            $detail = new BookingDetail();
            $detail->booking_id = $booking->id;
            $detail->service_id = $service->id;
            if (Schema::hasColumn($detail->getTable(), 'service_name')) {
                $detail->service_name = $service->name;
            }
            $detail->variant_key = $variantKey;
            $detail->quantity = 1;
            $detail->service_cost = $unitPrice;
            $detail->discount_amount = 0;
            $detail->tax_amount = $tax;
            $detail->total_cost = $total;
            $detail->campaign_discount_amount = 0;
            $detail->overall_coupon_discount_amount = 0;
            $detail->save();

            $amount = new BookingDetailsAmount();
            $amount->id = (string) Str::uuid();
            $amount->booking_details_id = $detail->id;
            $amount->booking_id = $booking->id;
            $amount->service_unit_cost = $unitPrice;
            if (Schema::hasColumn($amount->getTable(), 'service_quantity')) {
                $amount->service_quantity = 1;
            }
            if (Schema::hasColumn($amount->getTable(), 'service_tax')) {
                $amount->service_tax = $tax;
            }
            $amount->discount_by_admin = 0;
            $amount->discount_by_provider = 0;
            $amount->campaign_discount_by_admin = 0;
            $amount->campaign_discount_by_provider = 0;
            $amount->coupon_discount_by_admin = 0;
            $amount->coupon_discount_by_provider = 0;
            BookingDetailsAmount::withoutEvents(fn () => $amount->save());

            $schedule = new BookingScheduleHistory();
            $schedule->booking_id = $booking->id;
            $schedule->changed_by = $customer->id;
            if (Schema::hasColumn($schedule->getTable(), 'is_guest')) {
                $schedule->is_guest = 0;
            }
            $schedule->schedule = $booking->service_schedule;
            $schedule->save();

            $history = new BookingStatusHistory();
            $history->booking_id = $booking->id;
            $history->changed_by = $provider ? $supervisor->id : $customer->id;
            $history->booking_status = $case['booking_status'];
            $history->is_guest = 0;
            $history->save();
        });

        if ($case['attendance'] && $provider && $team) {
            $this->seedAttendance($booking, $team);
            $this->seedNotesAndSupport($booking, $provider, $supervisor);
        }

        return $booking->fresh();
    }

    private function seedAttendance(Booking $booking, SupervisorTeam $team): void
    {
        $memberIds = $team->servicemen()->pluck('servicemen.id')->all();
        if (empty($memberIds)) {
            $memberIds = self::SERVICEMEN;
        }

        foreach ($memberIds as $i => $servicemanId) {
            BookingAttendance::create([
                'booking_id' => $booking->id,
                'serviceman_id' => $servicemanId,
                'is_attended' => true,
                'attended_at' => now()->subMinutes(40 - ($i * 8)),
            ]);
        }
    }

    private function seedNotesAndSupport(Booking $booking, Provider $provider, User $supervisor): void
    {
        BookingExecutionNote::create([
            'booking_id' => $booking->id,
            'user_id' => $supervisor->id,
            'note' => 'تم بدء تنظيف المطبخ والحمامات، والعمل يسير حسب الخطة.',
        ]);
        BookingExecutionNote::create([
            'booking_id' => $booking->id,
            'user_id' => $supervisor->id,
            'note' => 'الفريق كامل في الموقع.',
        ]);
        BookingSupportRequest::create([
            'booking_id' => $booking->id,
            'provider_id' => $provider->id,
            'type' => 'support',
            'message' => 'نحتاج عدد إضافي من أدوات الممسحة.',
            'status' => 'open',
        ]);
    }
}

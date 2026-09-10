<?php

namespace Modules\ServiceManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ServiceManagement\Entities\AdditionalService;
use Modules\ServiceManagement\Entities\CustomerServiceSubscription;
use Modules\ServiceManagement\Entities\Property;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Services\CustomerServiceSubscriptionService;
use Modules\ServiceManagement\Services\SubscriptionBookingService;
use Modules\UserManagement\Entities\UserAddress;

class ServiceSubscriptionController extends Controller
{
    public function __construct(
        private CustomerServiceSubscription $subscription,
        private Service $service,
        private Property $property,
        private UserAddress $address
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'status' => 'nullable|in:active,finished,canceled,all',
            'property_id' => 'nullable|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $userId = auth('api')->id();
        $status = $request->get('status', 'all');

        $mainServiceIds = \Illuminate\Support\Facades\DB::table('services')->pluck('id')->toArray();
        $additionalServiceIds = \Illuminate\Support\Facades\DB::table('additional_services')->pluck('id')->toArray();

        $subscriptions = $this->subscription
            ->with([
                'service' => fn($q) => $q->withoutGlobalScopes()->withTrashed(),
                'property',
                'additional_service' => fn($q) => $q->withoutGlobalScopes(),
            ])
            ->where('user_id', $userId)
            ->whereIn('service_id', $mainServiceIds)
            ->whereNotIn('service_id', $additionalServiceIds)
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($request->filled('property_id'), fn($q) => $q->where('property_id', $request->property_id))
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        $subscriptions->setCollection(
            CustomerServiceSubscriptionService::presentForApi($subscriptions->getCollection())
        );

        return response()->json(response_formatter(DEFAULT_200, $subscriptions), 200);
    }

    public function indexAdditional(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'status' => 'nullable|in:active,finished,canceled,all',
            'property_id' => 'nullable|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $userId = auth('api')->id();
        $status = $request->get('status', 'all');

        $additionalServiceIds = \Illuminate\Support\Facades\DB::table('additional_services')->pluck('id')->toArray();

        $subscriptions = $this->subscription
            ->where('user_id', $userId)
            ->whereIn('service_id', $additionalServiceIds)
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($request->filled('property_id'), fn($q) => $q->where('property_id', $request->property_id))
            ->with([
                'property',
                'additional_service' => fn($q) => $q->withoutGlobalScopes(),
            ])
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        $subscriptions->setCollection(
            CustomerServiceSubscriptionService::presentForApi($subscriptions->getCollection())
                ->map(function (CustomerServiceSubscription $item) {
                    $arr = $item->toArray();
                    unset($arr['total_visits'], $arr['remaining_visits'], $arr['used_visits']);
                    return $arr;
                })
        );

        return response()->json(response_formatter(DEFAULT_200, $subscriptions), 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => [
                'required',
                'uuid',
                function ($attribute, $value, $fail) {
                    $existsInServices = Service::withoutGlobalScopes()->where('id', $value)->exists();
                    $existsInAdditional = AdditionalService::withoutGlobalScopes()->where('id', $value)->exists();
                    if (!$existsInServices && !$existsInAdditional) {
                        $fail(translate('The selected service id is invalid.'));
                    }
                }
            ],
            'property_id' => 'required|uuid|exists:properties,id,is_active,1'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $userId = auth('api')->id();

        $service = $this->service->where('id', $request->service_id)->where('is_active', 1)->first();
        if (!$service) {
            $addService = AdditionalService::where('id', $request->service_id)->where('is_active', 1)->first();
            if ($addService) {
                $service = new Service();
                $service->id = $addService->id;
                $service->name = $addService->name;
                $service->visits_count = null;
                $service->is_active = 1;
                $service->exists = true;
            }
        }
        if (!$service) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        if ($service->property_id && $service->property_id !== $request->property_id) {
            return response()->json(response_formatter(DEFAULT_400, null, [
                [
                    'code' => 'property_id',
                    'message' => translate('Service does not belong to this property')
                ]
            ]), 400);
        }

        $ownsPropertyAddress = $this->address
            ->where('user_id', $userId)
            ->where('property_id', $request->property_id)
            ->exists();

        if (!$ownsPropertyAddress) {
            return response()->json(response_formatter(DEFAULT_400, null, [
                [
                    'code' => 'property_id',
                    'message' => translate('Please add an address for this property first')
                ]
            ]), 400);
        }

        $existing = $this->subscription
            ->where([
                'user_id' => $userId,
                'service_id' => $request->service_id,
                'property_id' => $request->property_id,
                'status' => 'active'
            ])
            ->first();

        if ($existing) {
            return response()->json(response_formatter(DEFAULT_200, CustomerServiceSubscriptionService::presentForApi(
                $existing->load(['service', 'property', 'additional_service'])
            )), 200);
        }

        $visits = !is_null($service->visits_count) ? max(1, (int) $service->visits_count) : null;

        $subscription = $this->subscription->create([
            'user_id' => $userId,
            'service_id' => $service->id,
            'property_id' => $request->property_id,
            'total_visits' => $visits,
            'remaining_visits' => $visits,
            'status' => 'active'
        ]);

        return response()->json(response_formatter(DEFAULT_STORE_200, CustomerServiceSubscriptionService::presentForApi(
            $subscription->load(['service', 'property', 'additional_service'])
        )), 200);
    }

    /**
     * Confirm booking for main services only.
     */
    public function confirm(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => [
                'required',
                'uuid',
                function ($attribute, $value, $fail) {
                    $existsInServices = Service::withoutGlobalScopes()->where('id', $value)->exists();
                    if (!$existsInServices) {
                        $fail(translate('The selected service id is invalid.'));
                    }
                }
            ],
            'property_id' => 'nullable|string',
            'service_address_id' => 'required_without:address_id|integer|exists:user_addresses,id',
            'address_id' => 'required_without:service_address_id|integer|exists:user_addresses,id',
            'service_schedule' => 'required|date',
            'payment_method' => 'nullable|in:' . implode(',', array_column(PAYMENT_METHODS, 'key')),
            'zone_id' => 'nullable|uuid',
            'variant_key' => 'nullable|string',
            'addon_service_ids' => 'nullable|array',
            'addon_service_ids.*' => 'uuid|exists:services,id',
            'coupon_code' => 'nullable|string',
            'customer_note' => 'nullable|string|max:2000',
            'service_location' => 'nullable|in:customer,provider',
            'is_partial' => 'nullable|in:0,1',
            'callback' => 'nullable|url',
            'payment_platform' => 'nullable|in:web,app'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $result = (new SubscriptionBookingService())->confirm(auth('api')->id(), $request->all(), 'main');

        if (($result['flag'] ?? '') !== 'success') {
            return response()->json(response_formatter(BOOKING_PLACE_FAIL_200, null, [
                [
                    'code' => 'booking',
                    'message' => translate($result['message'] ?? 'booking failed')
                ]
            ]), 200);
        }

        $booking = $result['booking'] ?? null;
        if ($booking) {
            $booking->loadMissing('service_address');
        }
        $address = $booking?->service_address
            ?? (is_string($booking?->service_address_location) ? json_decode($booking->service_address_location) : $booking?->service_address_location);

        $content = [
            'booking' => $booking,
            'subscription' => $result['subscription'],
            'address' => $address,
            'service_address' => $address
        ];

        if (!empty($result['payment_url'])) {
            $content['payment_required'] = true;
            $content['payment_url'] = $result['payment_url'];
        } elseif (
            ($request->payment_method ?? '') === 'edfapay'
            || in_array($request->payment_method, array_column(DIGITAL_PAYMENT_METHODS, 'key'), true)
        ) {
            return response()->json(response_formatter(BOOKING_PLACE_FAIL_200, $content, [
                [
                    'code' => 'payment_url',
                    'message' => translate('Unable to initiate digital payment')
                ]
            ]), 200);
        }

        return response()->json(response_formatter(BOOKING_PLACE_SUCCESS_200, $content), 200);
    }

    /**
     * Confirm booking for additional services only.
     */
    public function confirmAdditional(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => [
                'required',
                'uuid',
                function ($attribute, $value, $fail) {
                    $existsInAdditional = AdditionalService::withoutGlobalScopes()->where('id', $value)->exists();
                    if (!$existsInAdditional) {
                        $fail(translate('The selected additional service id is invalid.'));
                    }
                }
            ],
            'property_id' => 'nullable|string',
            'service_address_id' => 'required_without:address_id|integer|exists:user_addresses,id',
            'address_id' => 'required_without:service_address_id|integer|exists:user_addresses,id',
            'service_schedule' => 'required|date',
            'payment_method' => 'nullable|in:' . implode(',', array_column(PAYMENT_METHODS, 'key')),
            'zone_id' => 'nullable|uuid',
            'variant_key' => 'nullable|string',
            'addon_service_ids' => 'nullable|array',
            'addon_service_ids.*' => 'uuid|exists:services,id',
            'coupon_code' => 'nullable|string',
            'customer_note' => 'nullable|string|max:2000',
            'service_location' => 'nullable|in:customer,provider',
            'is_partial' => 'nullable|in:0,1',
            'callback' => 'nullable|url',
            'payment_platform' => 'nullable|in:web,app'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $result = (new SubscriptionBookingService())->confirm(auth('api')->id(), $request->all(), 'additional');

        if (($result['flag'] ?? '') !== 'success') {
            return response()->json(response_formatter(BOOKING_PLACE_FAIL_200, null, [
                [
                    'code' => 'booking',
                    'message' => translate($result['message'] ?? 'booking failed')
                ]
            ]), 200);
        }

        $booking = $result['booking'] ?? null;
        if ($booking) {
            $booking->loadMissing('service_address');
        }
        $address = $booking?->service_address
            ?? (is_string($booking?->service_address_location) ? json_decode($booking->service_address_location) : $booking?->service_address_location);

        $content = [
            'booking' => $booking,
            'subscription' => $result['subscription'],
            'address' => $address,
            'service_address' => $address
        ];

        if (!empty($result['payment_url'])) {
            $content['payment_required'] = true;
            $content['payment_url'] = $result['payment_url'];
        } elseif (
            ($request->payment_method ?? '') === 'edfapay'
            || in_array($request->payment_method, array_column(DIGITAL_PAYMENT_METHODS, 'key'), true)
        ) {
            return response()->json(response_formatter(BOOKING_PLACE_FAIL_200, $content, [
                [
                    'code' => 'payment_url',
                    'message' => translate('Unable to initiate digital payment')
                ]
            ]), 200);
        }

        return response()->json(response_formatter(BOOKING_PLACE_SUCCESS_200, $content), 200);
    }

    /**
     * Preview price/coupon for Figma summary — no cart, no booking.
     * Call this on "تطبيق" coupon and when addons change.
     */
    public function pricePreview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => [
                'required',
                'uuid',
                function ($attribute, $value, $fail) {
                    $existsInServices = Service::withoutGlobalScopes()->where('id', $value)->exists();
                    $existsInAdditional = AdditionalService::withoutGlobalScopes()->where('id', $value)->exists();
                    if (!$existsInServices && !$existsInAdditional) {
                        $fail(translate('The selected service id is invalid.'));
                    }
                }
            ],
            'zone_id' => 'nullable|uuid',
            'property_id' => 'nullable|string',
            'variant_key' => 'nullable|string',
            'addon_service_ids' => 'nullable|array',
            'addon_service_ids.*' => 'uuid',
            'coupon_code' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $result = (new SubscriptionBookingService())->preview($request->all());

        if (($result['flag'] ?? '') !== 'success') {
            return response()->json(response_formatter(DEFAULT_400, null, [
                [
                    'code' => 'preview',
                    'message' => translate($result['message'] ?? 'preview failed')
                ]
            ]), 400);
        }

        $summary = $result['summary'];
        if (!empty($request->coupon_code) && empty($summary['coupon_applied'])) {
            return response()->json(response_formatter(COUPON_NOT_VALID_FOR_CART, $summary), 200);
        }

        if (!empty($request->coupon_code) && !empty($summary['coupon_applied'])) {
            return response()->json(response_formatter(COUPON_APPLIED_200, $summary), 200);
        }

        return response()->json(response_formatter(DEFAULT_200, $summary), 200);
    }

    /**
     * Rebook previous booking without cart.
     */
    public function rebook(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|uuid|exists:bookings,id',
            'service_schedule' => 'required|date',
            'service_address_id' => 'nullable|integer|exists:user_addresses,id',
            'property_id' => 'nullable|uuid|exists:properties,id,is_active,1',
            'payment_method' => 'nullable|in:' . implode(',', array_column(PAYMENT_METHODS, 'key')),
            'zone_id' => 'nullable|uuid',
            'variant_key' => 'nullable|string',
            'addon_service_ids' => 'nullable|array',
            'addon_service_ids.*' => 'uuid|exists:services,id',
            'coupon_code' => 'nullable|string',
            'customer_note' => 'nullable|string|max:2000',
            'service_location' => 'nullable|in:customer,provider',
            'is_partial' => 'nullable|in:0,1',
            'callback' => 'nullable|url',
            'payment_platform' => 'nullable|in:web,app'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $result = (new SubscriptionBookingService())->rebook(auth('api')->id(), $request->all());

        if (($result['flag'] ?? '') !== 'success') {
            return response()->json(response_formatter(BOOKING_PLACE_FAIL_200, null, [
                [
                    'code' => 'booking',
                    'message' => translate($result['message'] ?? 'booking failed')
                ]
            ]), 200);
        }

        $booking = $result['booking'] ?? null;
        if ($booking) {
            $booking->loadMissing('service_address');
        }
        $address = $booking?->service_address
            ?? (is_string($booking?->service_address_location) ? json_decode($booking->service_address_location) : $booking?->service_address_location);

        $content = [
            'booking' => $booking,
            'subscription' => $result['subscription'],
            'address' => $address,
            'service_address' => $address
        ];

        if (!empty($result['payment_url'])) {
            $content['payment_required'] = true;
            $content['payment_url'] = $result['payment_url'];
        } elseif (
            ($request->payment_method ?? '') === 'edfapay'
            || in_array($request->payment_method, array_column(DIGITAL_PAYMENT_METHODS, 'key'), true)
        ) {
            return response()->json(response_formatter(BOOKING_PLACE_FAIL_200, $content, [
                [
                    'code' => 'payment_url',
                    'message' => translate('Unable to initiate digital payment')
                ]
            ]), 200);
        }

        return response()->json(response_formatter(BOOKING_PLACE_SUCCESS_200, $content), 200);
    }

    /**
     * Reschedule subscription booking date/time.
     */
    public function reschedule(Request $request, string $booking_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_schedule' => 'required_without:schedule|date',
            'schedule' => 'required_without:service_schedule|date'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $scheduleDate = $request->input('service_schedule', $request->input('schedule'));
        $result = (new SubscriptionBookingService())->reschedule(auth('api')->id(), $booking_id, $scheduleDate);

        if (($result['flag'] ?? '') !== 'success') {
            return response()->json(response_formatter(DEFAULT_400, null, [
                [
                    'code' => 'reschedule',
                    'message' => translate($result['message'] ?? 'reschedule failed')
                ]
            ]), 400);
        }

        return response()->json(response_formatter(SERVICE_SCHEDULE_UPDATE_200, $result['booking']), 200);
    }

    public function show(string $id): JsonResponse
    {
        $userId = auth('api')->id();
        $subscription = $this->subscription
            ->with(['service', 'property', 'visits'])
            ->where('user_id', $userId)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                    ->orWhereHas('visits', fn($v) => $v->where('booking_id', $id));
            })
            ->first();

        if (!$subscription) {
            $booking = \Modules\BookingModule\Entities\Booking::where('id', $id)
                ->where('customer_id', $userId)
                ->first();

            if ($booking && $booking->subscription_id) {
                $subscription = $this->subscription
                    ->with(['service', 'property', 'visits'])
                    ->where('user_id', $userId)
                    ->where('id', $booking->subscription_id)
                    ->first();
            }
        }




        if (!$subscription) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $subscription = CustomerServiceSubscriptionService::presentForApi($subscription);

        return response()->json(response_formatter(DEFAULT_200, $subscription), 200);
    }
}

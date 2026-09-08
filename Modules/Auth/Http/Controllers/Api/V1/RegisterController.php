<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use App\Traits\UploadSizeHelperTrait;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\PaymentModule\Entities\Setting;
use Modules\PaymentModule\Traits\SubscriptionTrait;
use Modules\PromotionManagement\Entities\PushNotification;
use Modules\PromotionManagement\Entities\PushNotificationUser;
use Modules\ProviderManagement\Emails\NewJoiningRequestMail;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\ProviderSetting;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;

class RegisterController extends Controller
{
    use UploadSizeHelperTrait;

    protected Provider $provider;
    protected User $owner;
    protected User $user;
    protected Serviceman $serviceman;
    private SubscriptionPackage $subscriptionPackage;

    use SubscriptionTrait;
    use UploadSizeHelperTrait;

    public function __construct(Provider $provider, User $owner, User $user, Serviceman $serviceman, SubscriptionPackage $subscriptionPackage)
    {
        $this->provider = $provider;
        $this->owner = $owner;
        $this->user = $user;
        $this->serviceman = $serviceman;
        $this->subscriptionPackage = $subscriptionPackage;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function customerRegister(Request $request): JsonResponse
    {
        $check = $this->validateUploadedFile($request, ['profile_image']);
        if ($check !== true) {
            return $check;
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required_without:first_name|nullable|string',
            'first_name' => 'required_without:name|nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'required',
            'password' => 'nullable|min:8',
            'gender' => 'in:male,female,others',
            'confirm_password' => 'nullable|same:password',
            'profile_image' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 403);
        }

        if ($request->filled('email') && User::where('email', $request['email'])->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "email", "message" => translate('Email already taken')]]), 400);
        }
        if (User::where('phone', $request['phone'])->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "phone", "message" => translate('Phone already taken')]]), 400);
        }

        if ($request->filled('name')) {
            $nameParts = explode(' ', trim($request->name), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';
        } else {
            $firstName = $request->first_name ?? '';
            $lastName = $request->last_name ?? '';
        }

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            User::create([
                'first_name' => $firstName ?: 'Customer',
                'last_name' => $lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password ?? rand(11111111, 99999999)),
                'user_type' => 'customer',
                'is_phone_verified' => 0,
                'is_active' => 1
            ]);
        } else {
            if ($firstName) {
                $user->first_name = $firstName;
            }
            if ($lastName !== null) {
                $user->last_name = $lastName;
            }
            $user->save();
        }

        DB::table('user_verifications')->updateOrInsert(
            ['identity' => $request->phone, 'identity_type' => 'phone'],
            [
                'otp' => '111111',
                'expires_at' => now()->addHour(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return response()->json(response_formatter(DEFAULT_SENT_OTP_200, [
            'phone' => $request->phone,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => trim($firstName . ' ' . $lastName),
            'otp' => '111111'
        ]), 200);
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function providerRegister(Request $request): JsonResponse
    {
        $check = $this->validateUploadedFile($request, ['logo', 'cover_image']);
        if ($check !== true) {
            return $check;
        }
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'contact_person_phone' => 'required|phone:country_code',
            'contact_person_email' => 'required',

            'account_first_name' => 'nullable|max:191',
            'account_last_name' => 'nullable|max:191',
            'zone_id' => 'required|uuid',
            'account_email' => 'required|email',
            'account_phone' => 'required|phone:country_code',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',

            'company_name' => 'required',
            'company_phone' => 'required|phone:country_code',
            'company_address' => 'required',
            'company_email' => 'required|email',
            'logo' => 'required|image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'cover_image' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),

            'identity_type' => 'required|in:passport,driving_license,nid,trade_license,company_id',
            'identity_number' => 'required',
            'identity_images' => 'required|array',
            'identity_images.*' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),

            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        if (User::where('email', $request['account_email'])->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "account_email", "message" => translate('Email already taken')]]), 400);
        }
        if (User::where('phone', $request['account_phone'])->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "account_phone", "message" => translate('Phone already taken')]]), 400);
        }

        if ($request->choose_business_plan == 'subscription_base') {
            $package = $this->subscriptionPackage->where('id', $request->selected_package_id)->ofStatus(1)->first();
            $vatPercentage = (int) ((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
            if (!$package) {
                return response()->json(response_formatter(DEFAULT_400, null, [["error_code" => "package", "message" => translate('Please Select valid plan')]]), 400);
            }

            $id = $package->id;
            $price = $package->price;
            $name = $package->name;
            $vatAmount = $package->price * ($vatPercentage / 100);
            $vatWithPrice = $price + $vatAmount;
        }

        $identityImages = [];
        foreach ($request->identity_images as $image) {
            $imageName = file_uploader('provider/identity/', APPLICATION_IMAGE_FORMAT, $image);
            $identityImages[] = ['image' => $imageName, 'storage' => getDisk()];
        }

        $provider = $this->provider;
        $provider->company_name = $request->company_name;
        $provider->company_phone = $request->company_phone;
        $provider->company_email = $request->company_email;
        $provider->logo = file_uploader('provider/logo/', APPLICATION_IMAGE_FORMAT, $request->file('logo'));

        if ($request->has('cover_image')) {
            $provider->cover_image = file_uploader('provider/logo/', APPLICATION_IMAGE_FORMAT, $request->file('cover_image'));
        }

        $provider->company_address = $request->company_address;

        $provider->contact_person_name = $request->contact_person_name;
        $provider->contact_person_phone = $request->contact_person_phone;
        $provider->contact_person_email = $request->contact_person_email;
        $provider->is_approved = 2;
        $provider->is_active = 0;
        $provider->zone_id = $request['zone_id'];
        $provider->coordinates = ['latitude' => $request['latitude'], 'longitude' => $request['longitude']];

        $owner = $this->owner;
        $owner->first_name = $request->account_first_name;
        $owner->last_name = $request->account_last_name;
        $owner->email = $request->account_email;
        $owner->phone = $request->account_phone;
        $owner->identification_number = $request->identity_number;
        $owner->identification_type = $request->identity_type;
        $owner->identification_image = $identityImages;
        $owner->password = bcrypt($request->password);
        $owner->user_type = 'provider-admin';
        $owner->is_active = 0;

        DB::transaction(function () use ($provider, $owner, $request) {
            $owner->save();
            $provider->user_id = $owner->id;
            $provider->save();

            $serviceLocation = ['customer'];
            ProviderSetting::create([
                'provider_id' => $provider->id,
                'key_name' => 'service_location',
                'live_values' => json_encode($serviceLocation),
                'test_values' => json_encode($serviceLocation),
                'settings_type' => 'provider_config',
                'mode' => 'live',
                'is_active' => 1
            ]);
        });

        $emailStatus = business_config('email_config_status', 'email_config')->live_values;

        if ($emailStatus) {
            try {
                Mail::to(User::where('user_type', 'super-admin')->value('email'))->send(new NewJoiningRequestMail($provider));
            } catch (\Exception $exception) {
                info($exception);
            }
        }

        if ($request->choose_business_plan == 'subscription_base') {
            $provider_id = $provider->id;
            if ($request->free_trial_or_payment == 'free_trial') {
                $result = $this->handleFreeTrialPackageSubscription($id, $provider_id, $price, $name);
                if (!$result) {
                    return response()->json(response_formatter(DEFAULT_FAIL_200), 400);
                }
            } elseif ($request->free_trial_or_payment == 'payment') {
                $paymentUrl = url('payment/subscription') . '?' .
                    'provider_id=' . $provider_id . '&' .
                    'access_token=' . base64_encode($owner->id) . '&' .
                    'package_id=' . $id . '&' .
                    'amount=' . $vatWithPrice . '&' .
                    'name=' . $name . '&' .
                    'package_status=' . 'subscription_purchase' . '&' .
                    http_build_query($request->all());
                return response()->json(response_formatter(PROVIDER_STORE_200, $paymentUrl), 200);
            }
        }

        return response()->json(response_formatter(PROVIDER_STORE_200), 200);
    }


    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user_verification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $data = DB::table('user_verifications')
            ->where('identity', $request['identity'])
            ->where(['otp' => $request['otp']])->first();

        if (isset($data)) {
            $this->user->whereIn('user_type', CUSTOMER_USER_TYPES)
                ->where('phone', $request['identity'])
                ->update([
                    'is_phone_verified' => 1
                ]);

            DB::table('user_verifications')
                ->where('identity', $request['identity'])
                ->where(['otp' => $request['otp']])->delete();

            return response()->json(response_formatter(DEFAULT_VERIFIED_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }

}

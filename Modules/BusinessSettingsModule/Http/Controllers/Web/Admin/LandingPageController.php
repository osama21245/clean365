<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use App\Traits\UploadSizeHelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\DataSetting;
use Modules\BusinessSettingsModule\Entities\LandingPageFeature;
use Modules\BusinessSettingsModule\Entities\LandingPageSpeciality;
use Modules\BusinessSettingsModule\Entities\LandingPageTestimonial;
use Modules\BusinessSettingsModule\Entities\Translation;
use Ramsey\Uuid\Uuid;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LandingPageController extends Controller
{
    use UploadSizeHelperTrait;

    private BusinessSettings $business_setting;
    private LandingPageFeature $feature;
    private LandingPageSpeciality $speciality;
    private LandingPageTestimonial $testimonial;
    private DataSetting $dataSetting;

    use AuthorizesRequests;

    public function __construct(BusinessSettings $business_setting, LandingPageFeature $feature, LandingPageSpeciality $speciality, LandingPageTestimonial $testimonial, DataSetting $dataSetting)
    {
        $this->business_setting = $business_setting;
        $this->feature = $feature;
        $this->speciality = $speciality;
        $this->testimonial = $testimonial;
        $this->dataSetting = $dataSetting;
    }

    /**
     * Display a listing of the resource.
     */
    public function getLandingInformation(Request $request): RedirectResponse
    {
        return redirect()->route('admin.business-page-setup.list');
    }

    /**
     * Display a listing of the resource.
     */
    public function setLandingInformation(Request $request): JsonResponse|RedirectResponse
    {
        return redirect()->route('admin.business-page-setup.list');
    }

    public function deleteLandingInformation($page, $id): RedirectResponse
    {
        $this->authorize('landing_delete');
        $array = [];
        $data = $this->business_setting->where('settings_type', 'landing_social_media')->first();
        foreach ($data->live_values as $value) {
            if ($value['id'] != $id) {
                $array[] = $value;
            }
        }

        $this->business_setting->updateOrCreate(['key_name' => $page], [
            'key_name' => $page,
            'live_values' => $array,
            'test_values' => $array,
            'settings_type' => 'landing_' . $page,
            'mode' => 'live',
            'is_active' => 1]);

        Toastr::success(translate(DEFAULT_DELETE_200['key']));
        return back();
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setServiceSetup(Request $request): JsonResponse
    {
        $request[$request['key']] = $request['value'];

        $validator = Validator::make($request->all(), [
            'schedule_booking' => 'in:1,0',
            'provider_can_cancel_booking' => 'in:1,0',
            'serviceman_can_cancel_booking' => 'in:1,0',
            'admin_order_notification' => 'in:1,0',
            'sms_verification' => 'in:1,0',
            'email_verification' => 'in:1,0',
            'provider_self_registration' => 'in:1,0'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            $this->business_setting->updateOrCreate(['key_name' => $key, 'settings_type' => 'service_setup'], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'is_active' => $value,
                'settings_type' => 'service_setup',
                'mode' => 'live']);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     */
    public function getPagesSetup(Request $request): View|Factory|Application
    {
        $webPage = $request->has('web_page') ? $request['web_page'] : 'about_us';
        return view('businesssettingsmodule::admin.page-settings', compact('webPage'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setPagesSetup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page_name' => 'required|in:about_us,privacy_policy,terms_and_conditions,refund_policy,cancellation_policy',
            'page_content' => ''
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->business_setting->updateOrCreate(['key_name' => $request['page_name'], 'settings_type' => 'pages_setup'], [
            'key_name' => $request['page_name'],
            'live_values' => $request['page_content'],
            'test_values' => null,
            'settings_type' => 'pages_setup',
            'mode' => 'live',
            'is_active' => $request['is_active'] ?? 0]);

        if (in_array($request['page_name'], ['privacy_policy', 'terms_and_conditions'])) {
            $message = translate('page_information_has_been_updated') . '!';
            topic_notification('customer', $request['page_name'], $message, 'def.png', null, $request['page_name']);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    public function setLandingSpeciality(Request $request): RedirectResponse
    {
        $this->authorize('landing_update');

        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'title.0' => 'required',
            'description.0' => 'required',
            'image' => 'image|max:'. uploadMaxFileSizeInKB('image') .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))],
            [
                'title.0.required' => translate('default_title_is_required'),
                'description.0.required' => translate('default_description_is_required')]
        );

        $speciality = $this->speciality;
        $speciality->title = $request->title[array_search('default', $request->lang)];
        $speciality->description = $request->description[array_search('default', $request->lang)];
        $speciality->image = file_uploader('landing-page/', APPLICATION_IMAGE_FORMAT, $request->file('image'));
        $speciality->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageSpeciality',
                            'translationable_id' => $speciality->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $speciality->title]
                    );
                }
            } else {

                if ($request->title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageSpeciality',
                            'translationable_id' => $speciality->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageSpeciality',
                            'translationable_id' => $speciality->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $speciality->description]
                    );
                }
            } else {

                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageSpeciality',
                            'translationable_id' => $speciality->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_STORE_200['key']));
        return back();
    }

    public function deleteLandingSpeciality($id): RedirectResponse
    {
        $this->authorize('landing_delete');
        $speciality = $this->speciality->where('id', $id)->first();
        if (isset($speciality)) {
            file_remover('landing-page/', $speciality->image);
            $speciality->translations()->delete();
            $speciality->delete();
            Toastr::success(translate(DEFAULT_DELETE_200['key']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['key']));
        return back();
    }

    public function setLandingTestimonial(Request $request): RedirectResponse
    {
        $this->authorize('landing_update');

        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'name.0' => 'required',
            'designation.0' => 'required',
            'review.0' => 'required',
            'image' => 'image|max:'. uploadMaxFileSizeInKB('image') .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))],
            [
                'name.0.required' => translate('default_name_is_required'),
                'designation.0.required' => translate('default_designation_is_required'),
                'review.0.required' => translate('default_review_is_required')]
        );

        $testimonial = $this->testimonial;
        $testimonial->name = $request->name[array_search('default', $request->lang)];
        $testimonial->designation = $request->designation[array_search('default', $request->lang)];
        $testimonial->review = $request->review[array_search('default', $request->lang)];
        $testimonial->image = file_uploader('landing-page/', APPLICATION_IMAGE_FORMAT, $request->file('image'));
        $testimonial->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $testimonial->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->designation[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'designation'],
                        ['value' => $testimonial->designation]
                    );
                }
            } else {

                if ($request->designation[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'designation'],
                        ['value' => $request->designation[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->review[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'review'],
                        ['value' => $testimonial->review]
                    );
                }
            } else {

                if ($request->review[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageTestimonial',
                            'translationable_id' => $testimonial->id,
                            'locale' => $key,
                            'key' => 'review'],
                        ['value' => $request->review[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_STORE_200['key']));
        return back();
    }

    public function deleteLandingTestimonial($id): RedirectResponse
    {
        $this->authorize('landing_delete');
        $testimonial = $this->testimonial->where('id', $id)->first();
        if (isset($testimonial)) {
            file_remover('landing-page/', $testimonial->image);
            $testimonial->translations()->delete();
            $testimonial->delete();
            Toastr::success(translate(DEFAULT_DELETE_200['key']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['key']));
        return back();
    }

    public function setLandingFeature(Request $request): RedirectResponse
    {
        $this->authorize('landing_update');

        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'title.0' => 'required',
            'sub_title.0' => 'required',
            'image_1' => 'image|max:'. uploadMaxFileSizeInKB('image') .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'image_2' => 'image|max:'. uploadMaxFileSizeInKB('image') .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key'))],
            [
                'title.0.required' => translate('default_title_is_required'),
                'sub_title.0.required' => translate('default_sub_title_is_required')]
        );

        $feature = $this->feature;
        $feature->title = $request->title[array_search('default', $request->lang)];
        $feature->sub_title = $request->sub_title[array_search('default', $request->lang)];
        $feature->image_1 = file_uploader('landing-page/', APPLICATION_IMAGE_FORMAT, $request->file('image_1'));
        $feature->image_2 = file_uploader('landing-page/', APPLICATION_IMAGE_FORMAT, $request->file('image_2'));
        $feature->save();

        $defaultLanguage = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLanguage == $key && !($request->title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $feature->title]
                    );
                }
            } else {

                if ($request->title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'title'],
                        ['value' => $request->title[$index]]
                    );
                }
            }

            if ($defaultLanguage == $key && !($request->sub_title[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'sub_title'],
                        ['value' => $feature->sub_title]
                    );
                }
            } else {

                if ($request->sub_title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\BusinessSettingsModule\Entities\LandingPageFeature',
                            'translationable_id' => $feature->id,
                            'locale' => $key,
                            'key' => 'sub_title'],
                        ['value' => $request->sub_title[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_STORE_200['key']));
        return back();
    }

    public function deleteLandingFeature($id): RedirectResponse
    {
        $this->authorize('landing_delete');
        $feature = $this->feature->where('id', $id)->first();
        if (isset($feature)) {
            file_remover('landing-page/', $feature->image_1);
            file_remover('landing-page/', $feature->image_2);
            $feature->translations()->delete();
            $feature->delete();
            Toastr::success(translate(DEFAULT_DELETE_200['key']));
            return back();
        }
        Toastr::success(translate(DEFAULT_204['key']));
        return back();
    }

}

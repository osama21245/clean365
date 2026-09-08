<?php

namespace Modules\CustomerModule\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\DataSetting;

class PagesController extends Controller
{
    public function aboutUs(): Renderable
    {
        $settings = BusinessSettings::whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $settingss = DataSetting::whereIn('type', ['landing_web_app', 'landing_text_setup'])->get();
        $dataSettings = DataSetting::whereIn('type', ['pages_setup', 'landing_text_setup'])->get();
        return view('about-us', compact('settings', 'dataSettings', 'settingss'));
    }

    public function privacyPolicy(): Renderable
    {
        $settings = BusinessSettings::whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $settingss = DataSetting::whereIn('type', ['landing_web_app', 'landing_text_setup'])->get();
        $dataSettings = DataSetting::where('type', 'pages_setup')->get();
        return view('privacy-policy', compact('settings', 'dataSettings', 'settingss'));
    }

    public function termsAndConditions(): Renderable
    {
        $settings = BusinessSettings::whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $settingss = DataSetting::whereIn('type', ['landing_web_app', 'landing_text_setup'])->get();
        $dataSettings = DataSetting::where('type', 'pages_setup')->get();
        return view('terms-and-conditions', compact('settings', 'dataSettings', 'settingss'));
    }

    public function refundPolicy(): Renderable
    {
        $settings = BusinessSettings::whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $settingss = DataSetting::whereIn('type', ['landing_web_app', 'landing_text_setup'])->get();
        $dataSettings = DataSetting::where('type', 'pages_setup')->get();
        return view('refund-policy', compact('settings', 'dataSettings', 'settingss'));
    }

    public function returnPolicy(): Renderable
    {
        $settings = BusinessSettings::whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $settingss = DataSetting::whereIn('type', ['landing_web_app', 'landing_text_setup'])->get();
        $dataSettings = DataSetting::where('type', 'pages_setup')->get();
        return view('refund-policy', compact('settings', 'dataSettings', 'settingss'));
    }

    public function cancellationPolicy(): Renderable
    {
        $settings = BusinessSettings::whereNotIn('settings_type', ['payment_config', 'third_party'])->get();
        $settingss = DataSetting::whereIn('type', ['landing_web_app', 'landing_text_setup'])->get();
        $dataSettings = DataSetting::where('type', 'pages_setup')->get();
        return view('cancellation-policy', compact('settings', 'dataSettings', 'settingss'));
    }
}

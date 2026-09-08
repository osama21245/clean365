<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\LoginSetup;

class LoginSetupController extends Controller
{

    use AuthorizesRequests;
    private BusinessSettings $businessSetting;
    private LoginSetup $loginSetup;

    public function __construct(BusinessSettings $businessSetting, LoginSetup $loginSetup)
    {
        $this->businessSetting = $businessSetting;
        $this->loginSetup = $loginSetup;
    }

    public function loginSetup(Request $request): RedirectResponse
    {
        return redirect()->route('admin.business-settings.get-business-information');
    }

    public function loginSetupUpdate(Request $request): RedirectResponse
    {
        return redirect()->route('admin.business-settings.get-business-information');
    }

    public function otpLoginInformationSet(Request $request): JsonResponse|RedirectResponse
    {
        return redirect()->route('admin.business-settings.get-business-information');
    }
}

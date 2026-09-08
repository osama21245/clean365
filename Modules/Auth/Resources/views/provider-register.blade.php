@php use Modules\BusinessSettingsModule\Entities\BusinessSettings; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ translate('Provider Registration') }}</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <?php
    $favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/admin-module/img/placeholder.png')
    ?>
    <link rel="shortcut icon" href="{{ $favIcon }}"/>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet"/>

    <link href="{{asset('public/assets/provider-module')}}/css/material-icons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/bootstrap.min.css"/>
    <link rel="stylesheet"
          href="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/toastr.css">
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">

    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/style.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/common')}}/css/form-unified.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/common')}}/css/ff-field-date-fix.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/common')}}/css/provider-register-fixes.css"/>
    <style>
        .no-business-plan-box {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
        }

        .wizard-finish-disabled {
            pointer-events: none;
            opacity: .55;
            cursor: not-allowed;
        }

        .ratio-1-1 {
            aspect-ratio: 1/1 !important;
        }
    </style>
</head>

<body>
<div class="preloader"></div>

<?php
$logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/admin-module/img/placeholder.png');
?>

<div class="dark-support">
    <div class="register-wrap style__two">
        <div class="register-left d-flex justify-content-center align-items-center bg-center"
             data-bg-img="{{asset('public/assets/provider-module')}}/img/media/login-bg.png">
            <div class="tf-box d-flex flex-column gap-3 align-items-center justify-content-center p-5 mx-5 h-75">
                <div class="px-xl-5 text-center">
                    <img class="login-img login-logo mb-2"
                         src="{{ $logo  }}"
                         alt="{{ translate('logo') }}">
                    <h2 class="text-center text-dark mt-2">Your <strong class="c1">Right <br> Choice</strong> for On
                        <br> Demand Business</h2>
                </div>
            </div>
        </div>

        <div class="register-right-wrap bg-white">
            <div class="register-right mx-auto p-3">
                <div class="text-center mb-5 d-flex flex-column gap-2 mt-3">
                    <h2 class="c1 fw-medium">{{ translate('Self Registration') }}</h2>
                    <p>{{translate('Sign up to provide service broadly')}}</p>
                </div>

                <form action="{{route('provider.auth.sign-up-submit')}}" method="POST" enctype="multipart/form-data"
                      id="register-vertical-steps">
                    @csrf

                    <h3>{{translate('Step 1')}}</h3>
                    <section>
                        <div class="" id="register-form-p-0">
                            <h3 class="border-bottom mb-4 pb-2">{{translate('Basic Information')}}</h3>
                            <h5 class="border-bottom mb-4 pb-2">{{ translate('General Information') }}</h5>
                            <div class="mb-4">
                                <div class="mb-30">
                                    <div class="ff-field ff-field--text ff-field--has-icon form-error-wrap" data-ff-field>
                                        <label for="company_name" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Company Name') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">apartment</span>
                                            <input type="text"
                                                   name="company_name"
                                                   id="company_name"
                                                   value="{{ old('company_name') }}"
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('Enter Company Name') }}"
                                                   maxlength="100"
                                                   data-char-count data-char-count-target="#company_name_char_count">
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                            <small class="ff-field__counter">
                                                <span id="company_name_char_count">{{ mb_strlen((string) old('company_name', '')) }}</span>/100
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-30">
                                    <div class="ff-field ff-field--email ff-field--has-icon form-error-wrap" data-ff-field>
                                        <label for="company_email" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Email Address') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">mail</span>
                                            <input type="email"
                                                   name="company_email"
                                                   id="company_email"
                                                   value="{{ old('company_email') }}"
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('ex: company@email.com') }}"
                                                   autocomplete="email"
                                                   maxlength="100"
                                                   data-char-count data-char-count-target="#company_email_char_count">
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                            <small class="ff-field__counter">
                                                <span id="company_email_char_count">{{ mb_strlen((string) old('company_email', '')) }}</span>/100
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-30">
                                    <div class="ff-field ff-field--tel form-error-wrap" data-ff-field>
                                        <label for="company_phone" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Phone Number') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="country-picker-1">
                                            <input type="tel"
                                                   name="company_phone"
                                                   id="company_phone"
                                                   value="{{ old('company_phone') }}"
                                                   class="form-control"
                                                   placeholder="{{ translate('123 456 789') }}"
                                                   maxlength="255">
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-30">
                                    <div class="ff-field ff-field--text ff-field--has-icon form-error-wrap" data-ff-field>
                                        <label for="company_address" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Address') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">location_on</span>
                                            <input type="text"
                                                   name="company_address"
                                                   id="company_address"
                                                   value="{{ old('company_address') }}"
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('Enter Address') }}"
                                                   maxlength="255"
                                                   data-char-count data-char-count-target="#company_address_char_count">
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                            <small class="ff-field__counter">
                                                <span id="company_address_char_count">{{ mb_strlen((string) old('company_address', '')) }}</span>/255
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-30">
                                    <div class="form-error-wrap" id="logo_upload_wrapper">
                                        @include('adminmodule::admin.partials._single-image-upload', [
                                            'name'             => 'logo',
                                            'id'               => 'logo_upload',
                                            'title'            => translate('Company Logo'),
                                            'required'         => true,
                                            'image'            => null,
                                            'instructionRatio' => '1:1',
                                            'showView'         => false,
                                            'showEdit'         => true,
                                            'showDelete'       => true])
                                    </div>
                                </div>

                                <div
                                    class="border-bottom d-flex align-items-center justify-content-between gap-2 pb-2 mb-4">
                                    <h5>{{translate('Contact Person Information')}}</h5>
                                    <div class="d-flex gap-2 align-items-center">
                                        <label for="sameAsGI">{{translate('Same as general info')}}</label>
                                        <input type="checkbox" value="" id="sameAsGI">
                                    </div>
                                </div>

                                <div class="sameAsGI_div">
                                    <div class="mb-30">
                                        <div class="ff-field ff-field--text ff-field--has-icon form-error-wrap" data-ff-field>
                                            <label for="contact_person_name" class="ff-field__label">
                                                <span class="ff-field__label-text">{{ translate('Contact Person Name') }}<span class="ff-field__required">*</span></span>
                                            </label>
                                            <div class="ff-field__control">
                                                <span class="material-icons ff-field__icon">person</span>
                                                <input type="text"
                                                       name="contact_person_name"
                                                       id="contact_person_name"
                                                       value="{{ old('contact_person_name') }}"
                                                       class="form-control ff-field__input"
                                                       placeholder="{{ translate('Enter Contact Person Name') }}"
                                                       maxlength="100"
                                                       data-char-count data-char-count-target="#contact_person_name_char_count">
                                            </div>
                                            <div class="ff-field__footer">
                                                <div class="ff-field__messages"></div>
                                                <small class="ff-field__counter">
                                                    <span id="contact_person_name_char_count">{{ mb_strlen((string) old('contact_person_name', '')) }}</span>/100
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="ff-field ff-field--tel form-error-wrap" data-ff-field>
                                            <label for="contact_person_phone" class="ff-field__label">
                                                <span class="ff-field__label-text">{{ translate('Contact Person Phone') }}<span class="ff-field__required">*</span></span>
                                            </label>
                                            <div class="country-picker-2">
                                                <input type="tel"
                                                       name="contact_person_phone"
                                                       id="contact_person_phone"
                                                       value="{{ old('contact_person_phone') }}"
                                                       class="form-control"
                                                       placeholder="{{ translate('123 456 789') }}">
                                            </div>
                                            <div class="ff-field__footer">
                                                <div class="ff-field__messages"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="ff-field ff-field--email ff-field--has-icon form-error-wrap" data-ff-field>
                                            <label for="contact_person_email" class="ff-field__label">
                                                <span class="ff-field__label-text">{{ translate('Contact Person Email') }}<span class="ff-field__required">*</span></span>
                                            </label>
                                            <div class="ff-field__control">
                                                <span class="material-icons ff-field__icon">mail</span>
                                                <input type="email"
                                                       name="contact_person_email"
                                                       id="contact_person_email"
                                                       value="{{ old('contact_person_email') }}"
                                                       class="form-control ff-field__input"
                                                       placeholder="{{ translate('ex: person@email.com') }}"
                                                       autocomplete="email"
                                                       maxlength="100"
                                                       data-char-count data-char-count-target="#contact_person_email_char_count">
                                            </div>
                                            <div class="ff-field__footer">
                                                <div class="ff-field__messages"></div>
                                                <small class="ff-field__counter">
                                                    <span id="contact_person_email_char_count">{{ mb_strlen((string) old('contact_person_email', '')) }}</span>/100
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <h3>{{translate('Step 2')}}</h3>
                    <section>
                        <div class="">
                            <h5 class="border-bottom mb-4 pb-2">{{ translate('Business Information') }}</h5>
                            <div class="mb-4">
                                <div class="mb-30">
                                    <div class="ff-field ff-field--select form-error-wrap" data-ff-field>
                                        <label for="zone_id" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Zone') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <select name="zone_id" id="zone_id" class="form-control ff-field__input ff-field__select">
                                                <option value="0" selected disabled>{{ translate('Select Zone') }}</option>
                                                @foreach($zones as $zone)
                                                    <option value="{{ $zone->id }}" {{ old('zone_id')==$zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-30">
                                    <div id="location_map_div">
                                        <input id="pac-input" class="form-control w-auto"
                                               data-toggle="tooltip"
                                               data-placement="right"
                                               data-original-title="{{ translate('Search your location here') }}"
                                               type="text"
                                               placeholder="{{ translate('Search Here') }}"/>
                                        <div id="location_map_canvas"
                                             class="overflow-hidden rounded h-100"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-30">
                                            <div class="ff-field ff-field--text form-error-wrap" data-ff-field>
                                                <label for="latitude" class="ff-field__label">
                                                    <span class="ff-field__label-text">{{ translate('Latitude') }}<span class="ff-field__required">*</span></span>
                                                </label>
                                                <div class="ff-field__control">
                                                    <input type="text"
                                                           name="latitude"
                                                           id="latitude"
                                                           value="{{ old('latitude') }}"
                                                           class="form-control ff-field__input"
                                                           placeholder="{{ translate('Ex:') }} 23.8118428"
                                                           data-bs-toggle="tooltip" data-bs-placement="top"
                                                           title="{{ translate('Select from map') }}"
                                                           readonly required>
                                                </div>
                                                <div class="ff-field__footer">
                                                    <div class="ff-field__messages"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-30">
                                            <div class="ff-field ff-field--text form-error-wrap" data-ff-field>
                                                <label for="longitude" class="ff-field__label">
                                                    <span class="ff-field__label-text">{{ translate('Longitude') }}<span class="ff-field__required">*</span></span>
                                                </label>
                                                <div class="ff-field__control">
                                                    <input type="text"
                                                           step="0.1"
                                                           name="longitude"
                                                           id="longitude"
                                                           value="{{ old('longitude') }}"
                                                           class="form-control ff-field__input"
                                                           placeholder="{{ translate('Ex:') }} 90.356331"
                                                           data-bs-toggle="tooltip" data-bs-placement="top"
                                                           title="{{ translate('Select from map') }}"
                                                           readonly required>
                                                </div>
                                                <div class="ff-field__footer">
                                                    <div class="ff-field__messages"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-30">
                                    <div class="ff-field ff-field--select form-error-wrap" data-ff-field>
                                        <label for="identity_type" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Identity Type') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <select name="identity_type" id="identity_type" class="form-control ff-field__input ff-field__select">
                                                <option value="0" selected disabled>{{ translate('Select Identity Type') }}</option>
                                                <option value="passport" {{ old('identity_type')=='passport' ? 'selected' : '' }}>{{ translate('Passport') }}</option>
                                                <option value="nid" {{ old('identity_type')=='nid' ? 'selected' : '' }}>{{ translate('NID') }}</option>
                                                <option value="driving_license" {{ old('identity_type')=='driving_license' ? 'selected' : '' }}>{{ translate('Driving License') }}</option>
                                                <option value="trade_license" {{ old('identity_type')=='trade_license' ? 'selected' : '' }}>{{ translate('Trade License') }}</option>
                                            </select>
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-30">
                                    <div class="ff-field ff-field--text ff-field--has-icon form-error-wrap" data-ff-field>
                                        <label for="identity_number" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Identity Number') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">badge</span>
                                            <input type="text"
                                                   name="identity_number"
                                                   id="identity_number"
                                                   value="{{ old('identity_number') }}"
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('Enter Identity Number') }}"
                                                   maxlength="50"
                                                   data-char-count data-char-count-target="#identity_number_char_count">
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                            <small class="ff-field__counter">
                                                <span id="identity_number_char_count">{{ mb_strlen((string) old('identity_number', '')) }}</span>/50
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="form-error-wrap">
                                        @include('adminmodule::admin.partials._multiple-image-upload', [
                                            'name'             => 'identity_images[]',
                                            'id'               => 'identity_images',
                                            'title'            => translate('Identity Image'),
                                            'subtitle'         => null,
                                            'instructionRatio' => '2:1',
                                            'required'         => true,
                                            'images'           => [],
                                            'maxCount'         => 2,
                                            'ratio'            => 'ratio-2-1',
                                            'showView'         => false,
                                            'showDelete'       => true])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <h3>{{translate('Step 3')}}</h3>
                    <section>
                        <div class="">
                            <h5 class="border-bottom mb-4 pb-2">{{ translate('Account Information') }}</h5>
                            <div class="mb-4">
                                <div class="mb-30" data-bs-toggle="tooltip" data-bs-title="{{ translate('If you want to change the email, go back to step 1') }}">
                                    <div class="ff-field ff-field--email ff-field--has-icon form-error-wrap" data-ff-field>
                                        <label for="account_email" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Email Address') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">mail</span>
                                            <input type="email"
                                                   name="account_email"
                                                   id="account_email"
                                                   value="{{ old('account_email') }}"
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('Email Address') }}"
                                                   autocomplete="email"
                                                   readonly>
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-30">
                                    <div class="ff-field ff-field--password ff-field--has-icon form-error-wrap" data-ff-field>
                                        <label for="password" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Password') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">lock</span>
                                            <input type="password"
                                                   name="password"
                                                   id="password"
                                                   value=""
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('Enter Password') }}"
                                                   minlength="8"
                                                   autocomplete="new-password">
                                            <span class="material-icons ff-field__toggle-password togglePassword"
                                                  data-ff-toggle-password="#password">visibility_off</span>
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages">
                                                <small class="ff-field__hint">{{ translate('Minimum 8 characters') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-30">
                                    <div class="ff-field ff-field--password ff-field--has-icon form-error-wrap" data-ff-field>
                                        <label for="confirm_password" class="ff-field__label">
                                            <span class="ff-field__label-text">{{ translate('Confirm Password') }}<span class="ff-field__required">*</span></span>
                                        </label>
                                        <div class="ff-field__control">
                                            <span class="material-icons ff-field__icon">lock</span>
                                            <input type="password"
                                                   name="confirm_password"
                                                   id="confirm_password"
                                                   value=""
                                                   class="form-control ff-field__input"
                                                   placeholder="{{ translate('Re-Enter Password') }}"
                                                   minlength="8"
                                                   autocomplete="new-password">
                                            <span class="material-icons ff-field__toggle-password togglePassword"
                                                  data-ff-toggle-password="#confirm_password">visibility_off</span>
                                        </div>
                                        <div class="ff-field__footer">
                                            <div class="ff-field__messages"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    @php
                        $hasCommissionBusinessPlan = (bool) $commission;
                        $hasSubscriptionBusinessPlan = (bool) $subscription && $formattedPackages->isNotEmpty();
                        $hasAnyBusinessPlan = $hasCommissionBusinessPlan || $hasSubscriptionBusinessPlan;
                        $defaultBusinessPlan = $hasCommissionBusinessPlan ? 'commision_base' : ($hasSubscriptionBusinessPlan ? 'subscription_base' : null);
                    @endphp

                    <h3>{{translate('Step 4')}}</h3>
                    <section>
                        <div class="">
                            <h5 class="border-bottom mb-4 pb-2">{{ translate('Choose Business Plan') }}</h5>
                            @if($hasAnyBusinessPlan)
                                <div class="choose_business_plan_wrap">
                                    <div class="d-flex flex-column gap-3">
                                        @if($hasCommissionBusinessPlan)
                                            <label
                                                class="business-plan-option border {{ $defaultBusinessPlan === 'commision_base' ? 'active' : '' }} rounded p-3 d-flex justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4>{{translate('Commision Base')}}</h4>
                                                    <div>{{ translate('You have to give a certain percentage of commission to admin for every booking request') }}</div>
                                                </div>

                                                <input value="commision_base" type="radio" name="choose_business_plan"
                                                       class="position-static w-26" {{ $defaultBusinessPlan === 'commision_base' ? 'checked' : '' }}>
                                            </label>
                                        @endif
                                        @if($hasSubscriptionBusinessPlan)
                                            <label
                                                class="business-plan-option border {{ $defaultBusinessPlan === 'subscription_base' ? 'active' : '' }} rounded p-3 d-flex justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4>{{translate('Subscription Base')}}</h4>
                                                    <div>{{ translate('You have to pay a certain amount every month or year to admin as subscription fee') }}</div>
                                                </div>

                                                <input value="subscription_base" type="radio" name="choose_business_plan"
                                                       class="position-static w-26" {{ $defaultBusinessPlan === 'subscription_base' ? 'checked' : '' }}>
                                            </label>
                                        @endif
                                    </div>
                                </div>

                            @if($hasSubscriptionBusinessPlan)
                                <div class="priceBoxSwiper-wrap">
                                    <h5 class="mt-4 mb-3 text-center">{{translate('Select Plan')}}</h5>
                                    <div class="w-100 mw-440">
                                        <div dir="ltr" class="swiper priceBoxSwiper">
                                            <div class="swiper-wrapper">
                                                <input type="hidden" name="selected_package_id"
                                                       id="selected-package-input" value="">

                                                @foreach($formattedPackages as $index => $package)
                                                    <div class="swiper-slide h-auto">
                                                        <div
                                                            class="price-box {{ $index == 1 ? 'active' : '' }} d-flex flex-column rounded-3 border h-100 package-option"
                                                            data-id="{{ $package->id }}">
                                                            <div
                                                                class="price-box__top d-flex gap-2 align-items-center justify-content-center px-2 py-4 text-center mb-3">
                                                                <span class="material-symbols-outlined uncheck-icon">radio_button_unchecked</span>
                                                                <span class="material-icons text-warning check-icon">check_circle</span>
                                                                <h5 class="line-clamp-1">{{ $package->name }}</h5>
                                                            </div>

                                                            <div
                                                                class="text-center min-h-62 d-flex flex-column justify-content-center">
                                                                <div class="h3 mb-0">
                                                                    {{ with_currency_symbol($package->price) }}
                                                                </div>
                                                                @if($vat = business_config('subscription_vat', 'subscription_Setting')?->live_values ?? 0)
                                                                    <small>+ {{ $vat }}% {{ translate('vat') }}</small>
                                                                @endif
                                                                <div class="mt-2">{{ translate(':count Days', ['count' => $package->duration]) }}</div>
                                                            </div>

                                                            <div class="px-2">
                                                                <hr>
                                                            </div>

                                                            <div class="p-3 flex-grow-1 d-flex flex-column">
                                                                <ul class="d-flex flex-column align-items-center gap-2 p-0 fs-12 mb-30">
                                                                    @foreach($package->feature_list as $feature)
                                                                        <li>{{ $feature }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="free_trial_or_payment_wrap">
                                    <div class="d-flex flex-column gap-3 mt-4">
                                        @if($freeTrialStatus)
                                            <label
                                                class="business-plan-option border active rounded p-3 d-flex justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4>{{ translate('Continue with :count Days Free Trial', ['count' => $duration]) }}</h4>
                                                    <div>{{ translate('Use the system free for :count days. After that you have to complete payments to continue', ['count' => $duration]) }}</div>
                                                </div>

                                                <input value="free_trial" type="radio" name="free_trial_or_payment"
                                                       class="position-static w-26" checked>
                                            </label>
                                        @endif

                                        @if($digitalPayment && $paymentGateways->isNotEmpty())
                                            <label
                                                class="business-plan-option payment_methods_list_container border rounded p-3">
                                                <div class="d-flex justify-content-between gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <h4>{{translate('Select Payment Method')}}</h4>
                                                        <div>{{translate('You can use any of our secure payment method & get selected subscription plan features')}}</div>
                                                    </div>

                                                    <input value="payment" type="radio" name="free_trial_or_payment"
                                                           class="position-static w-26">
                                                </div>
                                                <div class="payment_methods_list d-flex flex-column gap-3">
                                                    <input type="hidden" name="payment_platform" value="web">
                                                    <input type="hidden" name="callback"
                                                           value="{{route('provider.auth.login')}}">
                                                    @foreach($paymentGateways ?? [] as $gateway)
                                                        <label
                                                            class="payment-method-option border active rounded p-3 d-flex justify-content-between">
                                                            <div class="d-flex gap-2 align-items-center">
                                                                <img width="70" src="{{onErrorImage(
                                                                    $gateway['gateway_image'],
                                                                    asset('storage/app/public/payment_modules/gateway_image').'/' . $gateway['gateway_image'],
                                                                    asset('public/assets/admin-module/img/placeholder.png') ,
                                                                    'payment_modules/gateway_image/')}}"
                                                                     alt="{{translate('gateway image')}}">
                                                                <div>{{ $gateway['label'] }}</div>
                                                            </div>

                                                            <input value="{{ $gateway['gateway'] }}" type="radio"
                                                                   name="payment_method"
                                                                   class="position-static" {{ $loop->first ? 'checked' : '' }}>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @else
                                <div class="no-business-plan-box rounded p-4 text-center">
                                    <h4 class="mb-2">{{ translate('Currently no plan available') }}</h4>
                                    <p class="mb-0 text-muted">{{ translate('There is no business plan available right now') }}</p>
                                </div>
                            @endif
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </div>
    <?php
    $addressLat = BusinessSettings::where('key_name', 'address_latitude')->first()?->live_values ?? 23.811842872190;
    $addressLong = BusinessSettings::where('key_name', 'address_longitude')->first()?->live_values ?? 23.811842872190;
    ?>
</div>

<script src="{{asset('public/assets/provider-module')}}/js/jquery-3.6.0.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/bootstrap.bundle.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/main.js"></script>
<script src="{{asset('public/assets/admin-module')}}/plugins/swiper/swiper-bundle.min.js"></script>

<script src="{{asset('public/assets/provider-module')}}/js/sweet_alert.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/toastr.js"></script>
{!! Toastr::message() !!}

<script src="{{asset('public/assets/provider-module')}}/plugins/jquery-steps/jquery.steps.min.js"></script>
<script src="{{asset('public/assets/common/plugins/jquery-validation/jquery.validate.min.js')}}"></script>
<script src="{{asset('public/assets/common')}}/js/form-character-count.js"></script>
<script src="{{asset('public/assets/admin-module')}}/js/helper.js"></script>

@php($defaultCountryCode = strtolower(BusinessSettings::where('key_name', 'country_code')->first()?->live_values ?? 'us'))
<span class="system-default-country-code" data-value="{{ $defaultCountryCode }}"
      data-initial-country="{{ $defaultCountryCode }}"></span>
<link rel="stylesheet" href="{{asset('public/assets/libs/intl-tel-input/css/intlTelInput.css')}}"/>
<script src="{{ asset('public/assets/libs/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ asset('public/assets/libs/intl-tel-input/js/utils.js') }}"></script>
<script src="{{ asset('public/assets/libs/intl-tel-input/js/intlTelInout-validation.js') }}"></script>

<script src="{{ asset('public/assets/common/js/file-size-type-validation.js') }}"></script>

<script>
    "use strict";
    const hasAvailableBusinessPlan = @json($hasAnyBusinessPlan);

    document.addEventListener('DOMContentLoaded', function () {
        const packageOptions = document.querySelectorAll('.package-option');
        const selectedPackageInput = document.getElementById('selected-package-input');
        const initialSelectedPackage = document.querySelector('.package-option.active') || document.querySelector('.package-option');
        if (initialSelectedPackage && selectedPackageInput) {
            initialSelectedPackage.classList.add('active');
            selectedPackageInput.value = initialSelectedPackage.dataset.id;
        }

        packageOptions.forEach(option => {
            option.addEventListener('click', function () {
                packageOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                selectedPackageInput.value = this.dataset.id;
            });
        });
    });

    $(document).ready(function () {
        function syncCharCount(selector) {
            var input = document.querySelector(selector);
            if (!input) {
                return;
            }

            if (window.FormCharCount && typeof window.FormCharCount.update === 'function') {
                window.FormCharCount.update(input);
            } else {
                $(input).trigger('input');
            }
        }

        $("#company_email").on("change keyup paste", function () {
            $('#account_email').val($(this).val());
        });
        $("#company_phone").on("change keyup paste", function () {
            const countryCode = $('#register-vertical-steps-p-0').find('.iti__selected-dial-code').text();
            $('#account_phone').val(`${countryCode} ${$(this).val()}`);
        });
        $('#register-vertical-steps-p-0').find('.iti__flag-container').on("click", function () {
            const countryCode = $('#register-vertical-steps-p-0').find('.iti__selected-dial-code').text();
            $('#account_phone').val(`${countryCode} ${$("#company_phone").val()}`);
        });

        setInterval(() => {
            const countryCode = $('#register-vertical-steps-p-0').find('.iti__selected-dial-code').text();
            $('#account_phone').val(`${countryCode} ${$("#company_phone").val()}`);
            $('#account_email').val($('#company_email').val());
        }, 2000);


        $('#sameAsGI').on('change', function () {
            if ($(this).is(':checked')) {
                $('[name="contact_person_name"]').val($('[name="company_name"]').val());
                $('[name="contact_person_email"]').val($('#company_email').val());

                let companyPhone = $('#company_phone').val();
                let dialCode = $('.country-picker-1 .iti__selected-dial-code').text();
                let fullPhoneNumber = dialCode + companyPhone.replace(dialCode, '');

                $('#contact_person_phone').val(fullPhoneNumber);

                $('[name="contact_person_phone"]').val(companyPhone.replace(dialCode, ''));
                $('.country-picker-phone-number2').val(fullPhoneNumber);

                $('.country-picker-2').find('.iti__selected-dial-code').text(dialCode);
                $('.country-picker-2').find('.iti__selected-flag .iti__flag').attr('class', $('.country-picker-1').find('.iti__flag').attr('class'));

                syncCharCount('#contact_person_name');
                syncCharCount('#contact_person_email');

            } else {
                $('[name="contact_person_name"]').val('');
                $('[name="contact_person_email"]').val('');
                $('#contact_person_phone').val('');
                $('.country-picker-phone-number2').val('');

                syncCharCount('#contact_person_name');
                syncCharCount('#contact_person_email');
            }
        });


        let swiper = new Swiper(".priceBoxSwiper", {
            slidesPerView: 1.8,
            spaceBetween: 10,
            centeredSlides: true,
            initialSlide: 1,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });

        $('input[name="choose_business_plan"]').on('change', function() {
            var selectedPlan = $(this).val();
            if (selectedPlan === 'subscription_base') {
                $(".priceBoxSwiper-wrap").slideDown();
                $("body").addClass("subscription_base");
            } else {
                $(".priceBoxSwiper-wrap").slideUp();
                $("body").removeClass("subscription_base");
            }
        });
        var initialSelectedPlan = $('input[name="choose_business_plan"]:checked').val();
        if (initialSelectedPlan === 'subscription_base') {
            $(".priceBoxSwiper-wrap").slideDown();
            $("body").addClass("subscription_base");
        }

    });

</script>

<script>
    (function ($) {
        "use strict";

        let formWizard = $("#register-vertical-steps");
        let firstClick = true;

        function getRegistrationScrollContainer(target) {
            var panel = $(target).closest('.register-right-wrap')[0];
            return panel || document.scrollingElement || document.documentElement;
        }

        function scrollRegistrationTargetIntoView(target, behavior) {
            var container = getRegistrationScrollContainer(target);
            var offset = 120;
            var top;

            if (container === document.scrollingElement || container === document.documentElement || container === document.body) {
                var rect = target.getBoundingClientRect();
                top = Math.max(0, rect.top + window.pageYOffset - offset);
                window.scrollTo({ top: top, behavior: behavior || 'smooth' });
                return;
            }

            var targetRect = target.getBoundingClientRect();
            var containerRect = container.getBoundingClientRect();
            top = Math.max(0, targetRect.top - containerRect.top + container.scrollTop - offset);

            if (typeof container.scrollTo === 'function') {
                container.scrollTo({ top: top, behavior: behavior || 'smooth' });
            } else {
                container.scrollTop = top;
            }
        }

        $(document).on('click', '#register-vertical-steps .actions a[href^="#"]', function (e) {
            e.preventDefault();
        });

        $(document).on('focus', '.global-image-upload input[type="file"]', function (e) {
            e.preventDefault();
            var $wrap = $(this).closest('.global-image-upload');
            if ($wrap.length) {
                scrollRegistrationTargetIntoView($wrap[0], 'auto');
            }
        });

        function updateFinishButtonState(currentIndex) {
            let finishButton = $("a[href='#finish']");
            let shouldDisable = currentIndex === 3 && !hasAvailableBusinessPlan;

            finishButton.toggleClass('wizard-finish-disabled disabled', shouldDisable);
            finishButton.attr('aria-disabled', shouldDisable ? 'true' : 'false');

            if (shouldDisable) {
                finishButton.attr('tabindex', '-1');
            } else {
                finishButton.removeAttr('tabindex');
            }
        }

        formWizard.validate({
            focusInvalid: false,
            errorPlacement: function (error, element) {
                if (element.hasClass('multi-image-validation-proxy')) {
                    var wrapperSelector = element.data('upload-wrapper');
                    var $warningBox = $(wrapperSelector).find('.global-multi-image-warning');
                    $warningBox.html('');
                    $warningBox.append(error);
                    return;
                }
                if (element.is('input[type="file"]')) {
                    var $gu = element.closest('.global-image-upload');
                    if ($gu.length) {
                        $gu.after(error);
                        return;
                    }
                }
                var $ff = element.closest('[data-ff-field]');
                if ($ff.length) {
                    var $messages = $ff.find('.ff-field__messages').first();
                    if (!$messages.length) {
                        $messages = $('<div class="ff-field__messages"></div>');
                        $ff.append($messages);
                    }
                    $messages.append(error);
                    return;
                }
                element.parents('.form-floating, .form-error-wrap').after(error);
            },
        });

        function setValidationRulesAndMessages(rules, messages) {
            formWizard.validate().settings.rules = rules;
            formWizard.validate().settings.messages = messages;
        }

        function countIdentityImages() {
            return $('#identity_images_picker .spartan_image_input').filter(function () {
                return this.files && this.files.length > 0;
            }).length;
        }

        function syncIdentityImageProxy() {
            var total = countIdentityImages();
            var $proxy = $('#identity_images_wrapper .multi-image-validation-proxy');
            $proxy.val(total > 0 ? '1' : '');
            if ($proxy.closest('form').data('validator')) {
                $proxy.valid();
            }
            return total;
        }

        function handleImageUploadValidation() {
            var total = syncIdentityImageProxy();
            var $warning = $('#identity_images_wrapper .global-multi-image-warning');
            if (total < 1) {
                $warning.html('<label class="error">{{ translate('Please upload your identity image') }}</label>');
                return false;
            }
            $warning.html('');
            return true;
        }

        function resolveValidationErrorTarget(element) {
            var $el = $(element);

            if ($el.hasClass('multi-image-validation-proxy')) {
                var wrapSel = $el.data('upload-wrapper');
                var uploadWrapper = wrapSel ? document.querySelector(wrapSel) : null;
                if (uploadWrapper) {
                    return uploadWrapper;
                }
            }

            var $guWrap = $el.closest('.global-image-upload');
            if ($guWrap.length) {
                return $guWrap[0];
            }

            var $ff = $el.closest('[data-ff-field]');
            if ($ff.length) {
                return $ff[0];
            }

            var $wrap = $el.closest('.form-error-wrap');
            if ($wrap.length) {
                return $wrap[0];
            }

            return $el[0];
        }

        function getFirstValidationErrorInfo() {
            var validator = formWizard.data('validator');
            var errorElements = validator && validator.errorList ? validator.errorList : [];

            for (var i = 0; i < errorElements.length; i++) {
                var firstInvalidEl = errorElements[i] && errorElements[i].element;
                if (!firstInvalidEl) {
                    continue;
                }

                var target = resolveValidationErrorTarget(firstInvalidEl);
                if (target) {
                    var $section = $(target).closest('section');
                    return {
                        target: target,
                        sectionIndex: formWizard.find('section').index($section)
                    };
                }
            }

            var $fallback = formWizard.find('label.error:visible, .ff-field__messages .error:visible, .global-multi-image-warning .error:visible').first();
            if (!$fallback.length) {
                $fallback = formWizard.find('label.error, .ff-field__messages .error, .global-multi-image-warning .error').first();
            }

            if (!$fallback.length) {
                return null;
            }

            var $fallbackSection = $fallback.closest('section');
            return {
                target: $fallback[0],
                sectionIndex: formWizard.find('section').index($fallbackSection)
            };
        }

        function moveWizardToStep(stepIndex, callback) {
            var currentIndex = formWizard.steps('getCurrentIndex');

            if (stepIndex < 0 || stepIndex === currentIndex) {
                callback();
                return;
            }

            if (currentIndex === 3 && stepIndex < currentIndex && !firstClick) {
                var selectedPlan = $('input[name="choose_business_plan"]:checked').val();

                $(".choose_business_plan_wrap").slideDown();
                $(".free_trial_or_payment_wrap").slideUp();

                if (selectedPlan === 'subscription_base') {
                    $(".priceBoxSwiper-wrap").slideDown();
                    $("body").addClass("subscription_base");
                } else {
                    $(".priceBoxSwiper-wrap").slideUp();
                    $("body").removeClass("subscription_base");
                }

                $("a[href='#finish']").text('{{ translate("Complete") }}');
                firstClick = true;
            }

            var direction = stepIndex > currentIndex ? 'next' : 'previous';
            var maxAttempts = Math.abs(stepIndex - currentIndex) + 4;
            var attempts = 0;

            function move() {
                var current = formWizard.steps('getCurrentIndex');
                if (current === stepIndex || attempts >= maxAttempts) {
                    setTimeout(callback, 100);
                    return;
                }

                attempts++;
                formWizard.steps(direction);
                setTimeout(move, 80);
            }

            move();
        }

        function scrollToFirstError(allowStepChange) {
            var errorInfo = getFirstValidationErrorInfo();
            if (!errorInfo || !errorInfo.target) return;

            var currentIndex = formWizard.steps('getCurrentIndex');
            if (allowStepChange !== false && errorInfo.sectionIndex >= 0 && errorInfo.sectionIndex !== currentIndex) {
                moveWizardToStep(errorInfo.sectionIndex, function () {
                    scrollToFirstError(false);
                });
                return;
            }

            var target = errorInfo.target;

            function doScroll() {
                if (!$(target).is(':visible')) {
                    var $visibleFallback = formWizard.find('label.error:visible, .ff-field__messages .error:visible, .global-multi-image-warning .error:visible').first();
                    if (!$visibleFallback.length) {
                        return;
                    }
                    target = $visibleFallback[0];
                }

                $('html, body').stop(true, false);
                scrollRegistrationTargetIntoView(target, 'smooth');
            }

            doScroll();
            requestAnimationFrame(doScroll);
            setTimeout(doScroll, 50);
            setTimeout(doScroll, 200);
        }

        formWizard.steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "fade",
            stepsOrientation: "vertical",
            autoFocus: false,
            labels: {
                finish: "{{ translate('Complete') }}",
                next: "{{ translate('Next') }}",
                previous: "{{ translate('Previous') }}"
            },
            onStepChanged: function (event, currentIndex, priorIndex) {
                if (currentIndex === 3) {
                    $(".choose_business_plan_wrap").slideDown();
                    $(".free_trial_or_payment_wrap").slideUp();

                    var selectedPlan = $('input[name="choose_business_plan"]:checked').val();
                    if (selectedPlan === 'subscription_base') {
                        $(".priceBoxSwiper-wrap").slideDown();
                        $("body").addClass("subscription_base");
                    } else {
                        $(".priceBoxSwiper-wrap").slideUp();
                        $("body").removeClass("subscription_base");
                    }

                    firstClick = true;
                    $("a[href='#finish']").text('{{ translate("Complete") }}');
                }

                updateFinishButtonState(currentIndex);
            },
            onStepChanging: function (event, currentIndex, newIndex) {
                if (currentIndex === 3 && newIndex < currentIndex && !firstClick) {
                    var selectedPlan = $('input[name="choose_business_plan"]:checked').val();

                    $(".choose_business_plan_wrap").slideDown();
                    $(".free_trial_or_payment_wrap").slideUp();

                    if (selectedPlan === 'subscription_base') {
                        $(".priceBoxSwiper-wrap").slideDown();
                        $("body").addClass("subscription_base");
                    } else {
                        $(".priceBoxSwiper-wrap").slideUp();
                        $("body").removeClass("subscription_base");
                    }

                    $("a[href='#finish']").text('{{ translate("Complete") }}');
                    firstClick = true;
                    return false;
                }

                if (newIndex < currentIndex) {
                    return true;
                }

                switch (currentIndex) {
                    case 0:
                        setValidationRulesAndMessages({
                            company_name: "required",
                            company_email: {
                                required: true,
                                email: true
                            },
                            company_phone: "required",
                            company_address: "required",
                            logo: "required",
                            contact_person_name: "required",
                            contact_person_phone: "required",
                            contact_person_email: "required",
                        }, {
                            company_name: "Please enter your name",
                            company_phone: "Please enter your phone",
                            company_phone_2: "Please enter your phone",
                            company_email: "Please enter a valid email address",
                            company_address: "Please enter your address",
                            logo: "Please upload logo",
                            contact_person_name: "Please enter your name",
                            contact_person_phone: "Please enter your phone",
                            contact_person_email: "Please enter a valid email address",
                        });

                        formWizard.validate().settings.ignore = ":disabled,:hidden";
                        if (!formWizard.valid()) {
                            scrollToFirstError();
                            return false;
                        }

                        let email = $("#company_email").val();
                        let companyPhone = $('#company_phone').val();
                        let dialCode = $('.country-picker-1 .iti__selected-dial-code').text();
                        let phone = dialCode + companyPhone.replace(dialCode, '');

                        let isValid = false;

                        $.ajax({
                            url: "{{ route('check-unique-user') }}",
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                email: email,
                                phone: phone,
                            },
                            async: false,
                            success: function (response) {
                                if (response.success) {
                                    isValid = true;
                                } else {
                                    if (response.email_exists) {
                                        toastr.error('Email already exists')
                                    }
                                    if (response.phone_exists) {
                                        toastr.error('Phone already exists')
                                    }
                                    isValid = false;
                                }
                            },
                            error: function () {
                                isValid = false;
                            }
                        });
                        return isValid;

                    case 1:
                        setValidationRulesAndMessages({
                            zone_id: "required",
                            latitude: "required",
                            longitude: "required",
                            identity_type: "required",
                            identity_number: "required",
                        }, {
                            zone_id: "Please enter your Zone",
                            latitude: "Please enter latitude",
                            longitude: "Please enter longitude",
                            identity_type: "Please enter identity type",
                            identity_number: "Please enter identity number",
                        });

                        if (!handleImageUploadValidation()) {
                            scrollToFirstError();
                            return false;
                        }
                        break;
                    case 2:
                        setValidationRulesAndMessages({
                            account_email: "required",
                            password: {
                                required: true,
                                minlength: 8
                            },
                            confirm_password: {
                                required: true,
                                minlength: 8,
                                equalTo: "#password"
                            },
                        }, {
                            account_email: "Please enter email",
                            password: {
                                required: "Please provide a password",
                                minlength: "Your password must be at least 8 characters long"
                            },
                            confirm_password: {
                                required: "Please provide confirm password",
                                minlength: "Your password must be at least 8 characters long",
                                equalTo: "Please enter the same password as above",
                            },
                        });
                        break;
                }

                formWizard.validate().settings.ignore = ":disabled,:hidden";

                var stepValid = formWizard.valid();
                if (!stepValid) {
                    scrollToFirstError();
                }
                return stepValid;
            },
            onFinished: function (event, currentIndex) {
                event.preventDefault();

                if (!hasAvailableBusinessPlan) {
                    toastr.error('{{ translate('Currently no plan available') }}');
                    return false;
                }

                setValidationRulesAndMessages({
                    company_name: "required",
                    company_email: { required: true, email: true },
                    company_phone: "required",
                    company_address: "required",
                    logo: "required",
                    contact_person_name: "required",
                    contact_person_phone: "required",
                    contact_person_email: { required: true, email: true },
                    zone_id: "required",
                    latitude: "required",
                    longitude: "required",
                    identity_type: "required",
                    identity_number: "required",
                    identity_images_validation: { required: true },
                    account_email: "required",
                    password: { required: true, minlength: 8 },
                    confirm_password: { required: true, minlength: 8, equalTo: "#password" },
                }, {
                    identity_images_validation: {
                        required: "{{ translate('Please upload both sides of your identity image') }}"
                    }
                });
                formWizard.validate().settings.ignore = ":disabled,:hidden:not(.multi-image-validation-proxy)";
                syncIdentityImageProxy();
                if (!formWizard.valid() || !handleImageUploadValidation()) {
                    toastr.error('{{ translate('Please complete all required fields before submitting') }}');
                    scrollToFirstError();
                    return false;
                }

                const selectedPlan = $('input[name="choose_business_plan"]:checked').val();
                if (!selectedPlan) {
                    toastr.error('{{ translate('Please select a business plan') }}');
                    return false;
                }

                firstClick = $('body').hasClass('subscription_base');

                if (firstClick) {
                    $(".choose_business_plan_wrap").slideUp();
                    $(".priceBoxSwiper-wrap").slideUp();
                    $(".free_trial_or_payment_wrap").slideDown();
                    $("a[href='#finish']").text('{{ translate('Register') }}');
                    $('body').removeClass('subscription_base');
                    firstClick = false;
                } else {
                    if (selectedPlan === 'subscription_base') {
                        const mode = $('input[name="free_trial_or_payment"]:checked').val();
                        if (!mode) {
                            toastr.error('{{ translate('Please choose free trial or payment') }}');
                            return false;
                        }
                        if (mode === 'payment') {
                            const method = $('input[name="payment_method"]:checked').val();
                            if (!method) {
                                toastr.error('{{ translate('Please select a payment method') }}');
                                return false;
                            }
                        }
                        const packageId = $('#selected-package-input').val();
                        if (!packageId) {
                            toastr.error('{{ translate('Please select a subscription package') }}');
                            return false;
                        }
                    }

                    formWizard.submit();
                }
            }
        });

        updateFinishButtonState(formWizard.steps("getCurrentIndex"));

        (function patchStepHeaderFocus() {
            formWizard.find('.steps > ul a').each(function () {
                var el = this;
                if (el.__ffPatchedFocus) return;
                el.__ffPatchedFocus = true;
                var original = el.focus;
                el.focus = function (opts) {
                    opts = opts || {};
                    if (!('preventScroll' in opts)) opts.preventScroll = true;
                    try { return original.call(this, opts); }
                    catch (e) { return original.call(this); }
                };
            });
        })();

        var _origJqFocus = $.fn.focus;
        $.fn.focus = function () {
            if (this.length && this[0] && this[0].closest && this[0].closest('#register-vertical-steps .steps > ul')) {
                for (var i = 0; i < this.length; i++) {
                    var el = this[i];
                    if (el && el.focus) {
                        try { el.focus({ preventScroll: true }); }
                        catch (e) { el.focus(); }
                    }
                }
                return this;
            }
            return _origJqFocus.apply(this, arguments);
        };

    })(jQuery);
</script>


<script src="{{asset('public/assets/provider-module')}}/js//tags-input.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/spartan-multi-image-picker.js"></script>
<script
    src="https://maps.googleapis.com/maps/api/js?key={{business_config('google_map', 'third_party')?->live_values['map_api_key_client']}}&libraries=places,geometry&v=3.45.8"></script>
<script>
    "use strict";

    let maxSizeReadable = "{{ readableUploadMaxFileSize('image') }}";
    let maxFileSize = 2 * 1024 * 1024;

    if (maxSizeReadable.toLowerCase().includes('mb')) {
        maxFileSize = parseFloat(maxSizeReadable) * 1024 * 1024;
    } else if (maxSizeReadable.toLowerCase().includes('kb')) {
        maxFileSize = parseFloat(maxSizeReadable) * 1024;
    }

    function setAcceptForAllInputs() {
        const allowedExtensions = ".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }},"

        $('#identity_images_picker input[type=file]').each(function () {
            $(this).attr('accept', allowedExtensions);
        });
    }

    setAcceptForAllInputs();

    $("#identity_images_picker").spartanMultiImagePicker({
        fieldName: 'identity_images[]',
        maxCount: 2,
        allowedExt: 'png|jpg|jpeg|webp|gif',
        rowHeight: '120px',
        groupClassName: 'item',
        maxFileSize: maxFileSize,
        dropFileLabel: "{{ translate('Drop Here') }}",
        placeholderImage: {
            image: '{{asset('public/assets/admin-module')}}/img/media/upload-placeholder2.png',
            width: '100%',
        },

        onAddRow() {
            setAcceptForAllInputs()
        },
        onRenderedPreview: function (index) {
            syncIdentityImageProxy();
            toastr.success('{{ translate('Image Added') }}', {
                CloseButton: true,
                ProgressBar: true
            });
        },
        onRemoveRow: function (index) {
            syncIdentityImageProxy();
        },
        onExtensionErr: function (index, file) {
            toastr.error('{{ translate("Please only input png|jpg|jpeg|gif|webp type file") }}', {
                CloseButton: true,
                ProgressBar: true
            });
        },
        onSizeErr: function () {
            toastr.error('File size must be less than ' + maxSizeReadable);
        }
    });

    $(document).on('change', '#identity_images_picker .spartan_image_input', function () {
        syncIdentityImageProxy();
    });

    @if ($errors->any())
    @foreach($errors->all() as $error)
    toastr.error('{{$error}}', Error, {
        CloseButton: true,
        ProgressBar: true
    });
    @endforeach
    @endif

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#viewer').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#customFileEg1").change(function () {
        readURL(this);
    });


    $(document).ready(function () {
        function initAutocomplete() {
            var zonePolygons = {
                @foreach($zones as $zone)
                "{{ $zone->id }}": [
                        @if($zone->coordinates)
                        @foreach(json_decode($zone->coordinates[0]->toJson(), true)['coordinates'] as $coord)
                    {
                        lat: {{ $coord[1] }}, lng: {{ $coord[0] }}
                    },
                    @endforeach
                    @endif
                ],
                @endforeach
            };

            let currentPolygon = null;

            var myLatLng = {

                lat: {{$provider->coordinates['latitude'] ?? 23.811842872190343}},
                lng: {{$provider->coordinates['longitude'] ?? 90.356331}}
            };
            const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                center: {
                    lat: {{$provider->coordinates['latitude'] ?? 23.811842872190343}},
                    lng: {{$provider->coordinates['longitude'] ?? 90.356331}}
                },
                zoom: 13,
                mapTypeId: "roadmap",
            });

            var marker = new google.maps.Marker({
                position: myLatLng,
                map: map,
            });

            marker.setMap(map);

            function drawPolygon(zoneId, shouldFitBounds = true) {
                if (currentPolygon) {
                    currentPolygon.setMap(null);
                }
                if (zonePolygons[zoneId] && zonePolygons[zoneId].length > 0) {
                    currentPolygon = new google.maps.Polygon({
                        paths: zonePolygons[zoneId],
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: "#FF0000",
                        fillOpacity: 0.1,
                        clickable: false
                    });
                    currentPolygon.setMap(map);

                    var bounds = new google.maps.LatLngBounds();
                    for (var i = 0; i < currentPolygon.getPath().getLength(); i++) {
                        bounds.extend(currentPolygon.getPath().getAt(i));
                    }

                    if (shouldFitBounds) {
                        map.setCenter(bounds.getCenter());
                    }
                }
            }

            let selectedZone = $('select[name="zone_id"]').val();
            if (selectedZone) {
                drawPolygon(selectedZone, false);
            }

            $('select[name="zone_id"]').on('change', function () {
                drawPolygon($(this).val(), true);
            });

            var geocoder = geocoder = new google.maps.Geocoder();
            google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
                var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                var coordinates = JSON.parse(coordinates);
                var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);

                if (currentPolygon && !google.maps.geometry.poly.containsLocation(latlng, currentPolygon)) {
                    toastr.error('{{ translate('You cannot pin outside of the selected zone polygon') }}');
                    return;
                }

                marker.setPosition(latlng);
                map.panTo(latlng);

                document.getElementById('latitude').value = coordinates['lat'];
                document.getElementById('longitude').value = coordinates['lng'];


                if (document.getElementById('address')) {
                    geocoder.geocode({
                        'latLng': latlng
                    }, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                document.getElementById('address').value = results[1].formatted_address;
                            }
                        }
                    });
                }
            });

            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });
            let markers = [];
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }
                markers.forEach((marker) => {
                    marker.setMap(null);
                });
                markers = [];
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        return;
                    }

                    var latlng = place.geometry.location;
                    if (currentPolygon && !google.maps.geometry.poly.containsLocation(latlng, currentPolygon)) {
                        toastr.error('{{ translate('You cannot pin outside of the selected zone polygon') }}');
                        return;
                    }

                    document.getElementById('latitude').value = latlng.lat();
                    document.getElementById('longitude').value = latlng.lng();

                    marker.setPosition(latlng);

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(latlng);
                    }
                });
                map.fitBounds(bounds);
            });
        };
        initAutocomplete();
    });


    $('.__right-eye').on('click', function () {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active')
            $(this).find('i').removeClass('tio-invisible')
            $(this).find('i').addClass('tio-hidden-outlined')
            $(this).siblings('input').attr('type', 'password')
        } else {
            $(this).addClass('active')
            $(this).siblings('input').attr('type', 'text')


            $(this).find('i').addClass('tio-invisible')
            $(this).find('i').removeClass('tio-hidden-outlined')
        }
    })
</script>
</body>
</html>

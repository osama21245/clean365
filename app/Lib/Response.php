<?php

//default responses
const DEFAULT_200 = [
    'response_code' => 'default_200',
    'key' => 'response_default_200',
    'message' => 'successfully data fetched'
];

const DEFAULT_SENT_OTP_200 = [
    'response_code' => 'default_200',
    'key' => 'response_default_sent_otp_200',
    'message' => 'successfully sent OTP'
];

const DEFAULT_SENT_OTP_FAILED_200 = [
    'response_code' => 'default_200',
    'key' => 'response_default_sent_otp_failed_200',
    'message' => 'Failed to sent OTP'
];

const OTP_VERIFICATION_SUCCESS_200 = [
    'response_code' => 'default_200',
    'key' => 'response_otp_verification_success_200',
    'message' => 'Successfully verified'
];
const OTP_VERIFICATION_FAIL_403 = [
    'response_code' => 'default_403',
    'key' => 'response_otp_verification_fail_403',
    'message' => 'Verification failed'
];

const DEFAULT_VERIFIED_200 = [
    'response_code' => 'default_verified_200',
    'key' => 'response_default_verified_200',
    'message' => 'successfully verified'
];

const DEFAULT_PASSWORD_RESET_200 = [
    'response_code' => 'default_password_reset_200',
    'key' => 'response_default_password_reset_200',
    'message' => 'password reset successful'
];

const NO_CHANGES_FOUND = [
    'response_code' => 'no_changes_found_200',
    'key' => 'response_no_changes_found',
    'message' => 'no changes found'
];

const DEFAULT_204 = [
    'response_code' => 'default_204',
    'key' => 'response_default_204',
    'message' => 'information not found'
];

const DEFAULT_400 = [
    'response_code' => 'default_400',
    'key' => 'response_default_400',
    'message' => 'invalid or missing information'
];

const DEFAULT_401 = [
    'response_code' => 'default_401',
    'key' => 'response_default_401',
    'message' => 'credential does not match'
];

const DEFAULT_USER_REMOVED_401 = [
    'response_code' => 'default_user_removed_401',
    'key' => 'response_default_user_removed_401',
    'message' => 'user has been removed, please talk to the authority'
];

const DEFAULT_USER_DISABLED_401 = [
    'response_code' => 'default_user_disabled_401',
    'key' => 'response_default_user_disabled_401',
    'message' => 'user has been disabled, please talk to the authority'
];

const DEFAULT_403 = [
    'response_code' => 'default_403',
    'key' => 'response_default_403',
    'message' => 'your access has been denied'
];
const DEFAULT_404 = [
    'response_code' => 'default_404',
    'key' => 'response_default_404',
    'message' => 'resource not found'
];

const DEFAULT_DELETE_200 = [
    'response_code' => 'default_delete_200',
    'key' => 'response_default_delete_200',
    'message' => 'successfully deleted information'
];

const DEFAULT_FAIL_200 = [
    'response_code' => 'default_fail_200',
    'key' => 'response_default_fail_200',
    'message' => 'action failed'
];

const DEFAULT_PAID_200 = [
    'response_code' => 'default_paid_200',
    'key' => 'response_default_paid_200',
    'message' => 'already paid'
];


const DEFAULT_STORE_200 = [
    'response_code' => 'default_store_200',
    'key' => 'response_default_store_200',
    'message' => 'successfully added'
];

const DEFAULT_CART_STORE_200 = [
    'response_code' => 'default_cart_store_200',
    'key' => 'response_default_cart_store_200',
    'message' => 'Successfully added to the cart'
];

const DEFAULT_CART_ALREADY_ADDED_200 = [
    'response_code' => 'default_cart_already_added_store_200',
    'key' => 'response_default_cart_already_added_200',
    'message' => 'Already Added'
];

const DEFAULT_UPDATE_200 = [
    'response_code' => 'default_update_200',
    'key' => 'response_default_update_200',
    'message' => 'successfully updated'
];

const DEFAULT_STATUS_UPDATE_200 = [
    'response_code' => 'default_status_update_200',
    'key' => 'response_default_status_update_200',
    'message' => 'successfully status updated'
];

const CRONJOB_SETUP_MANUALLY = [
    'response_code' => 'cron_job_setup_manually',
    'key' => 'response_cronjob_setup_manually',
    'message' => 'Servers PHP exec function is disabled check dependencies & start cron job manually in server'
];

const DEFAULT_SUSPEND_UPDATE_200 = [
    'response_code' => 'default_suspend_update_200',
    'key' => 'response_default_suspend_update_200',
    'message' => 'successfully suspend status updated'
];

const DEFAULT_SUSPEND_200 = [
    'response_code' => 'default_suspend_update_200',
    'key' => 'response_default_suspend_200',
    'message' => 'Your account has been supended'
];

const TOO_MANY_ATTEMPT_403 = [
    'response_code' => 'too_many_attempt_403',
    'key' => 'response_too_many_attempt_403',
    'message' => 'your api hit limit exceeded, try after a minute.'
];


const REGISTRATION_200 = [
    'response_code' => 'registration_200',
    'key' => 'response_registration_200',
    'message' => 'successfully registered'
];

//auth module
const AUTH_LOGIN_200 = [
    'response_code' => 'auth_login_200',
    'key' => 'response_auth_login_200',
    'message' => 'successfully logged in'
];

const AUTH_LOGOUT_200 = [
    'response_code' => 'auth_logout_200',
    'key' => 'response_auth_logout_200',
    'message' => 'successfully logged out'
];

const AUTH_LOGIN_401 = [
    'response_code' => 'auth_login_401',
    'key' => 'response_auth_login_401',
    'message' => 'user credential does not match'
];

const ACCOUNT_UNDER_REVIEW = [
    'response_code' => 'account_under_review_401',
    'key' => 'response_account_under_review',
    'message' => 'Your account registration is currently under review by our admin. Thank you for your patience.'
];

const ACCOUNT_REJECTED = [
    'response_code' => 'account_rejected_401',
    'key' => 'response_account_rejected',
    'message' => 'Sorry, your registration has been denied. Please contact admin for further assistance.'
];

const ACCOUNT_DISABLED = [
    'response_code' => 'account_disabled_401',
    'key' => 'response_account_disabled',
    'message' => 'user account has been disabled, please talk to the admin.'
];
const ACCOUNT_DISABLED_SERVICEMAN = [
    'response_code' => 'account_disabled_401',
    'key' => 'response_account_disabled_serviceman',
    'message' => 'user account has been disabled, please talk to the provider.'
];

const PROVIDER_ACCOUNT_NOT_APPROVED = [
    'response_code' => 'provider_account_not_approved_401',
    'key' => 'response_provider_account_not_approved',
    'message' => 'Your account is currently under review. Contact with admin for any kind of query'
];

const AUTH_LOGIN_403 = [
    'response_code' => 'auth_login_403',
    'key' => 'response_auth_login_403',
    'message' => 'wrong login credentials'
];

const AUTH_LOGIN_404 = [
    'response_code' => 'auth_login_404',
    'key' => 'response_auth_login_404',
    'message' => 'User does not exist'
];

const ACCESS_DENIED = [
    'response_code' => 'access_denied_403',
    'key' => 'response_access_denied',
    'message' => 'access denied'
];

const ALREADY_USE_NUMBER_ANOTHER_ACCOUNT = [
    'response_code' => 'use_another_account_403',
    'key' => 'response_already_use_number_another_account',
    'message' => 'This phone has already been used in another account!'
];

const ALREADY_USE_EMAIL_ANOTHER_ACCOUNT = [
    'response_code' => 'use_another_account_403',
    'key' => 'response_already_use_email_another_account',
    'message' => 'This email has already been used in another account!'
];

const UNVERIFIED_EMAIL = [
    'response_code' => 'unverified_email_401',
    'key' => 'response_unverified_email',
    'message' => 'Verify your email'
];

const UNVERIFIED_PHONE = [
    'response_code' => 'unverified_phone_401',
    'key' => 'response_unverified_phone',
    'message' => 'Verify your phone'
];

const REFERRAL_CODE_INVALID_400 = [
    'response_code' => 'referral_code_400',
    'key' => 'response_referral_code_invalid_400',
    'message' => 'referral code is invalid'
];


//user management module
const USER_ROLE_CREATE_400 = [
    'response_code' => 'user_role_create_400',
    'key' => 'response_user_role_create_400',
    'message' => 'invalid or missing information'
];

const USER_ROLE_CREATE_200 = [
    'response_code' => 'user_role_create_200',
    'key' => 'response_user_role_create_200',
    'message' => 'successfully added'
];

const USER_ROLE_UPDATE_200 = [
    'response_code' => 'user_role_update_200',
    'key' => 'response_user_role_update_200',
    'message' => 'successfully updated'
];

const USER_ROLE_UPDATE_400 = [
    'response_code' => 'user_role_update_400',
    'key' => 'response_user_role_update_400',
    'message' => 'invalid or missing data'
];
const USER_INACTIVE_400 = [
    'response_code' => 'user_inactive_400',
    'key' => 'response_user_inactive_400',
    'message' => 'This user is not active!'
];

//zone management module
const ZONE_STORE_200 = [
    'response_code' => 'zone_store_200',
    'key' => 'response_zone_store_200',
    'message' => 'successfully added'
];

const ZONE_UPDATE_200 = [
    'response_code' => 'zone_update_200',
    'key' => 'response_zone_update_200',
    'message' => 'successfully updated'
];

const ZONE_DESTROY_200 = [
    'response_code' => 'zone_destroy_200',
    'key' => 'response_zone_destroy_200',
    'message' => 'successfully deleted'
];

const ZONE_404 = [
    'response_code' => 'zone_404',
    'key' => 'response_zone_404',
    'message' => 'resource not found'
];

const ZONE_RESOURCE_404 = [
    'response_code' => 'zone_404',
    'key' => 'response_zone_resource_404',
    'message' => 'No provider or service is available within this zone'
];

//category management module
const CATEGORY_STORE_200 = [
    'response_code' => 'category_store_200',
    'key' => 'response_category_store_200',
    'message' => 'successfully added'
];

const CATEGORY_UPDATE_200 = [
    'response_code' => 'category_update_200',
    'key' => 'response_category_update_200',
    'message' => 'successfully updated'
];

const CATEGORY_DESTROY_200 = [
    'response_code' => 'category_destroy_200',
    'key' => 'response_category_destroy_200',
    'message' => 'successfully deleted'
];

const CATEGORY_204 = [
    'response_code' => 'category_404',
    'key' => 'response_category_204',
    'message' => 'resource not found'
];

//discount section
const DISCOUNT_CREATE_200 = [
    'response_code' => 'discount_create_200',
    'key' => 'response_discount_create_200',
    'message' => 'successfully added discount'
];

const DISCOUNT_UPDATE_200 = [
    'response_code' => 'discount_update_200',
    'key' => 'response_discount_update_200',
    'message' => 'successfully updated discount'
];

//service management module

const SERVICE_STORE_200 = [
    'response_code' => 'service_store_200',
    'key' => 'response_service_store_200',
    'message' => 'successfully added'
];

const SERVICE_REQUEST_STORE_200 = [
    'response_code' => 'service_request_store_200',
    'key' => 'response_service_request_store_200',
    'message' => 'your request has been successfully added. thank you for the request.'
];

const SERVICE_ADD_TO_FAVORITE_200 = [
    'response_code' => 'service_favorite_store_200',
    'key' => 'response_service_add_to_favorite_200',
    'message' => 'service added as favorite successfully'
];

const SERVICE_REMOVE_FAVORITE_200 = [
    'response_code' => 'service_remove_favorite_200',
    'key' => 'response_service_remove_favorite_200',
    'message' => 'service removed as favorite successfully'
];

//coupon section
const COUPON_UPDATE_200 = [
    'response_code' => 'coupon_update_200',
    'key' => 'response_coupon_update_200',
    'message' => 'successfully updated'
];
const COUPON_APPLIED_200 = [
    'response_code' => 'coupon_applied_200',
    'key' => 'response_coupon_applied_200',
    'message' => 'coupon applied successfully'
];
const COUPON_NOT_VALID_FOR_ZONE = [
    'response_code' => 'coupon_not_valid_for_zone',
    'key' => 'response_coupon_not_valid_for_zone',
    'message' => 'only applicable for chosen zone'
];
const COUPON_NOT_VALID_FOR_CATEGORY = [
    'response_code' => 'coupon_not_valid_for_category',
    'key' => 'response_coupon_not_valid_for_category',
    'message' => 'only applicable for chosen category'
];
const COUPON_NOT_VALID_FOR_SERVICE = [
    'response_code' => 'coupon_not_valid_for_service',
    'key' => 'response_coupon_not_valid_for_service',
    'message' => 'only applicable for chosen service'
];

const CAMPAIGN_UPDATE_200 = [
    'response_code' => 'coupon_update_200',
    'key' => 'response_campaign_update_200',
    'message' => 'successfully updated'
];

//banner section
const BANNER_CREATE_200 = [
    'response_code' => 'banner_create_200',
    'key' => 'response_banner_create_200',
    'message' => 'successfully added'
];

const BANNER_UPDATE_200 = [
    'response_code' => 'banner_update_200',
    'key' => 'response_banner_update_200',
    'message' => 'successfully updated'
];

const COUPON_NOT_VALID_FOR_CART=[
    'response_code' => 'coupon_not_valid_for_your_cart',
    'key' => 'response_coupon_not_valid_for_cart',
    'message' => 'you have exceeded this coupon usage limit.'
];

const COUPON_IS_VALID_FOR_FIRST_TIME=[
    'response_code' => 'coupon_is_valid_for_first_time',
    'key' => 'response_coupon_is_valid_for_first_time',
    'message' => 'this coupon is valid for first-time bookings only.'
];

//provider management module
const PROVIDER_STORE_200 = [
    'response_code' => 'provider_store_200',
    'key' => 'response_provider_store_200',
    'message' => 'successfully added'
];
const PROVIDER_REGISTERED_200 = [
    'response_code' => 'provider_store_200',
    'key' => 'response_provider_registered_200',
    'message' => 'successfully registered. Thanks for joining us! Your registration is under review. Hang tight, we will notify you once approved!'
];

const PROVIDER_400 = [
    'response_code' => 'provider_store_400',
    'key' => 'response_provider_400',
    'message' => 'invalid or missing information'
];

const PROVIDER_ADD_TO_FAVORITE_200 = [
    'response_code' => 'provider_favorite_store_200',
    'key' => 'response_provider_add_to_favorite_200',
    'message' => 'provider added as favorite successfully'
];

const PROVIDER_REMOVE_FAVORITE_200 = [
    'response_code' => 'provider_remove_favorite_200',
    'key' => 'response_provider_remove_favorite_200',
    'message' => 'provider removed as favorite successfully'
];


//transaction
const COLLECT_CASH_SUCCESS_200 = [
    'response_code' => 'collect_cash_success_200',
    'key' => 'response_collect_cash_success_200',
    'message' => 'cash collected successfully'
];

const COLLECT_CASH_FAIL_200 = [
    'response_code' => 'collect_cash_fail_200',
    'key' => 'response_collect_cash_fail_200',
    'message' => 'failed to collect the cash'
];

//booking
const BOOKING_PLACE_SUCCESS_200 = [
    'response_code' => 'booking_place_success_200',
    'key' => 'response_booking_place_success_200',
    'message' => 'Booking Placed successfully'
];
const BOOKING_PLACE_FAIL_200 = [
    'response_code' => 'booking_place_fail_200',
    'key' => 'response_booking_place_fail_200',
    'message' => 'Booking Place failed'
];
const BOOKING_STATUS_UPDATE_SUCCESS_200 = [
    'response_code' => 'status_update_success_200',
    'key' => 'response_booking_status_update_success_200',
    'message' => 'booking status updated successfully'
];
const BOOKING_IGNORE_SUCCESS_200 = [
    'response_code' => 'booking_ignore_success_200',
    'key' => 'response_booking_ignore_success_200',
    'message' => 'booking ignore successfully'
];
const BOOKING_ALREADY_IGNORED_200 = [
    'response_code' => 'booking_already_ignore_200',
    'key' => 'response_booking_already_ignored_200',
    'message' => 'booking already ignored'
];
const BOOKING_ALREADY_CANCELED_200 = [
    'response_code' => 'booking_already_canceled_200',
    'key' => 'response_booking_already_canceled_200',
    'message' => 'booking already canceled'
];
const PAYMENT_STATUS_UPDATE_SUCCESS_200 = [
    'response_code' => 'payment_status_update_success_200',
    'key' => 'response_payment_status_update_success_200',
    'message' => 'payment status updated successfully'
];
const BOOKING_STATUS_UPDATE_FAIL_200 = [
    'response_code' => 'status_update_fail_200',
    'key' => 'response_booking_status_update_fail_200',
    'message' => 'failed to change the status'
];

const DELIVERYMAN_ASSIGN_200 = [
    'response_code' => 'deliveryman_assign_200',
    'key' => 'response_deliveryman_assign_200',
    'message' => 'Deliveryman must assign first'
];


const SERVICEMAN_ASSIGN_SUCCESS_200 = [
    'response_code' => 'serviceman_assign_success_200',
    'key' => 'response_serviceman_assign_success_200',
    'message' => 'Serviceman assigned successfully'
];

const SERVICE_SCHEDULE_UPDATE_200 = [
    'response_code' => 'service_schedule_update_200',
    'key' => 'response_service_schedule_update_200',
    'message' => 'Service schedule updated successfully'
];

const MINIMUM_BOOKING_AMOUNT_200 = [
    'response_code' => 'minimum_booking_amount_200',
    'key' => 'response_minimum_booking_amount_200',
    'message' => 'Booking amount must be greater than minimum booking amount'
];

const PROVIDER_EXCEED_CASH_IN_HAND = [
    'response_code' => 'provider_exceed_cash_in_hand_200',
    'key' => 'response_provider_exceed_cash_in_hand',
    'message' => 'You exceeded the cash in hand limit'
];



const UPDATE_FAILED_FOR_OFFLINE_PAYMENT_VERIFICATION_200 = [
    'response_code' => 'update_failed_for_offline_payment_200',
    'key' => 'response_update_failed_for_offline_payment_verification_200',
    'message' => 'Admin must verify the offline payment'
];
const CHECK_OFFLINE_PAYMENT_AND_VERIFIED_200 = [
    'response_code' => 'minimum_booking_amount_200',
    'key' => 'response_check_offline_payment_and_verified_200',
    'message' => 'Admin must verify the offline payment'
];

const BOOKING_ALREADY_ACCEPTED = [
    'response_code' => 'booking_already_accepted_200',
    'key' => 'response_booking_already_accepted',
    'message' => 'Booking is already accepted, you can not cancel this booking'
];

const BOOKING_ALREADY_ONGOING = [
    'response_code' => 'booking_already_ongoing_200',
    'key' => 'response_booking_already_ongoing',
    'message' => 'Booking is already ongoing, you can not cancel this booking'
];

const BOOKING_ALREADY_COMPLETED = [
    'response_code' => 'booking_already_completed_200',
    'key' => 'response_booking_already_completed',
    'message' => 'Booking is already completed, you can not cancel this booking'
];

const BOOKING_ALREADY_EDITED = [
    'response_code' => 'booking_already_edited_200',
    'key' => 'response_booking_already_edited',
    'message' => 'You can not cancel this booking. Please contact with admin'
];


//Random
const DEFAULT_STATUS_FAILED_200 = [
    'response_code' => 'default_status_change_failed_200',
    'key' => 'response_default_status_failed_200',
    'message' => 'Minimum one method must be selected as default'
];
const INSUFFICIENT_WALLET_BALANCE_400 = [
    'response_code' => 'insufficient_wallet_balance_400',
    'key' => 'response_insufficient_wallet_balance_400',
    'message' => 'Wallet balance is insufficient'
];

const NOTIFICATION_SEND_SUCCESSFULLY_200 = [
    'response_code' => 'notification_send_successfully_200',
    'key' => 'response_notification_send_successfully_200',
    'message' => 'Notification has been sent successfully'
];

const NOTIFICATION_SEND_FAILED_200 = [
    'response_code' => 'notification_send_failed_200',
    'key' => 'response_notification_send_failed_200',
    'message' => 'Notification has been failed to send'
];

const ADJUST_AMOUNT_SUCCESS_200 = [
    'response_code' => 'adjusted_successfully_200',
    'key' => 'response_adjust_amount_success_200',
    'message' => 'Amount adjusted successfully'
];

const RENEW_SUBSCRIPTION_PACKAGE = [
    'response_code' => 'renew_200',
    'key' => 'response_renew_subscription_package',
    'message' => 'Renew subscription packaged successfully'
];

const SHIFT_SUBSCRIPTION_PACKAGE = [
    'response_code' => 'shift_200',
    'key' => 'response_shift_subscription_package',
    'message' => 'The subscription packaged shift was completed successfully. And all of your subscribed services have been unsubscribed. You can manually re-subscribe to the service.'
];

const PURCHASE_SUBSCRIPTION_PACKAGE = [
    'response_code' => 'purchase_200',
    'key' => 'response_purchase_subscription_package',
    'message' => 'Purchase subscription packaged successfully'
];

const PAYMENT_FAILED_SHIFT_FREE_TRIAL = [
    'response_code' => 'payment_failed_free_trial_200',
    'key' => 'response_payment_failed_shift_free_trial',
    'message' => 'Transaction failed !! Due to a transaction failure, your registration has been shifted to the trial process. Thanks for joining with us! Your registration is under review. Hang tight, we will notify you once approved'
];

const PAYMENT_FAILED = [
    'response_code' => 'payment_failed_400',
    'key' => 'response_payment_failed',
    'message' => 'Transaction failed!! You can pay the due amount later to continue using our services. Thanks for joining with us! Your registration is under review. Hang tight, we will notify you once approved'
];

const ALREADY_COMMISSION_BASE = [
    'response_code' => 'commission_400',
    'key' => 'response_already_commission_base',
    'message' => 'Provider already commission based'
];

const SECTION_NOT_INCLUDE = [
    'response_code' => 'section_not_include_400',
    'key' => 'response_section_not_include',
    'message' => 'your_subscription_package_does_not_include_mobile_app'
];

const CATEGORY_LIMIT_END = [
    'response_code' => 'category_limit_end_400',
    'key' => 'response_category_limit_end',
    'message' => 'your_subscription_package_category_limit_has_ended.'
];
const BOOKING_LIMIT_END = [
    'response_code' => 'booking_limit_end_400',
    'key' => 'response_booking_limit_end',
    'message' => 'your_subscription_package_booking_limit_has_ended.'
];
const BOOKING_ELIGIBILITY_FOR_BOOKING = [
    'response_code' => 'booking_limit_end_400',
    'key' => 'response_booking_eligibility_for_booking',
    'message' => 'This provider is not eligible for this booking.'
];
const MAINTENANCE_MODE = [
    'response_code' => 'maintenance_mode_400',
    'key' => 'response_maintenance_mode',
    'message' => 'Sorry for the inconvenience! We are currently undergoing scheduled maintenance to improve our services. We will be back shortly. Thank you for your patience'
];

const USER_EXIST_400 = [
    'response_code' => 'user_exist_400',
    'key' => 'response_user_exist_400',
    'message' => 'invalid or missing information'
];

const OFFLINE_PAYMENT_SUCCESS_200 = [
    'response_code' => 'offline_payment_success_200',
    'key' => 'response_offline_payment_success_200',
    'message' => 'payment confirm successfully'
];

const PAYMENT_METHOD_UPDATE_200 = [
    'response_code' => 'payment_method_update_200',
    'key' => 'response_payment_method_update_200',
    'message' => 'payment method updated successfully'
];

const SUBSCRIBE_NEWSLETTER_200 = [
    'response_code' => 'subscribe_newsletter_200',
    'key' => 'response_subscribe_newsletter_200',
    'message' => 'subscribed newsletter successfully'
];

const SERVICE_LOCATION_400 = [
    'response_code' => 'service_location_400',
    'key' => 'response_service_location_400',
    'message' => 'Can not change the setting while service location at provider place from admin panel is off'
];

const SMS_GATEWAY_NOT_ACTIVE_400 = [
    'response_code' => 'sms_gateway_not_active_400',
    'key' => 'response_sms_gateway_not_active_400',
    'message' => 'SMS Gateway not configured'
];

const STATUS_CANNOT_BE_CHANGED_AS_BOOKING_EXISTS_400 = [
    'response_code' => 'status_cannot_be_changed_as_booking_exists_400',
    'key' => 'response_status_cannot_be_changed_as_booking_exists_400',
    'message' => 'Status cannot be changed as this customer has ongoing booking'
];

const ACCOUNT_CANNOT_BE_DELETED_400 = [
    'response_code' => 'account_cannot_be_deleted_400',
    'key' => 'response_account_cannot_be_deleted_400',
    'message' => 'You cannot delete this account because you have active bookings.'
];

const TEAM_ASSIGNED_TO_BOOKING_200 = [
    'response_code' => 'team_assigned_to_booking_200',
    'key' => 'response_team_assigned_to_booking_200',
    'message' => 'Team assigned to booking successfully'
];

const BOOKING_SUPERVISOR_SEEN_200 = [
    'response_code' => 'booking_supervisor_seen_200',
    'key' => 'response_booking_supervisor_seen_200',
    'message' => 'Booking marked as seen by supervisor'
];

const BOOKING_STATUS_SUPERVISOR_ONLY_403 = [
    'response_code' => 'booking_status_supervisor_only_403',
    'key' => 'response_booking_status_supervisor_only_403',
    'message' => 'Only the supervisor can update booking status for team orders'
];

const SERVICEMAN_ADMIN_ONLY_403 = [
    'response_code' => 'serviceman_admin_only_403',
    'key' => 'response_serviceman_admin_only_403',
    'message' => 'Servicemen can only be created, updated, or deleted by admin'
];

const ADMIN_ASSIGNMENT_ONLY_403 = [
    'response_code' => 'admin_assignment_only_403',
    'key' => 'response_admin_assignment_only_403',
    'message' => 'Admin assigns the supervisor, team, and tasks. Supervisors can only update task status, notes, and photos'
];

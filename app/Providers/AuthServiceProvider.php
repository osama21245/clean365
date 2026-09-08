<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('dashboard', fn () => $this->checkAccess('dashboard', 'can_view'));

        Gate::define('booking_view', fn () => $this->checkAccess('booking', 'can_view'));
        Gate::define('booking_edit', fn () => $this->checkAccess('booking', 'can_update'));
        Gate::define('booking_delete', fn () => $this->checkAccess('booking', 'can_delete'));
        Gate::define('booking_export', fn () => $this->checkAccess('booking', 'can_export'));
        Gate::define('booking_can_manage_status', fn () => $this->checkAccess('booking', 'can_manage_status'));
        Gate::define('booking_can_approve_or_deny', fn () => $this->checkAccess('booking', 'can_approve_or_deny'));


        Gate::define('discount_view', fn () => $this->checkAccess('discount', 'can_view'));
        Gate::define('discount_add', fn () => $this->checkAccess('discount', 'can_add'));
        Gate::define('discount_update', fn () => $this->checkAccess('discount', 'can_update'));
        Gate::define('discount_delete', fn () => $this->checkAccess('discount', 'can_delete'));
        Gate::define('discount_export', fn () => $this->checkAccess('discount', 'can_export'));
        Gate::define('discount_manage_status', fn () => $this->checkAccess('discount', 'can_manage_status'));
        Gate::define('discount_approve_or_deny', fn () => $this->checkAccess('discount', 'can_approve_or_deny'));

        Gate::define('coupon_view', fn () => $this->checkAccess('coupon', 'can_view'));
        Gate::define('coupon_add', fn () => $this->checkAccess('coupon', 'can_add'));
        Gate::define('coupon_update', fn () => $this->checkAccess('coupon', 'can_update'));
        Gate::define('coupon_delete', fn () => $this->checkAccess('coupon', 'can_delete'));
        Gate::define('coupon_export', fn () => $this->checkAccess('coupon', 'can_export'));
        Gate::define('coupon_manage_status', fn () => $this->checkAccess('coupon', 'can_manage_status'));
        Gate::define('coupon_approve_or_deny', fn () => $this->checkAccess('coupon', 'can_approve_or_deny'));

        Gate::define('banner_view', fn () => $this->checkAccess('banner', 'can_view'));
        Gate::define('banner_add', fn () => $this->checkAccess('banner', 'can_add'));
        Gate::define('banner_update', fn () => $this->checkAccess('banner', 'can_update'));
        Gate::define('banner_delete', fn () => $this->checkAccess('banner', 'can_delete'));
        Gate::define('banner_export', fn () => $this->checkAccess('banner', 'can_export'));
        Gate::define('banner_manage_status', fn () => $this->checkAccess('banner', 'can_manage_status'));
        Gate::define('banner_approve_or_deny', fn () => $this->checkAccess('banner', 'can_approve_or_deny'));

        Gate::define('blog_view', fn () => $this->checkAccess('blog', 'can_view'));
        Gate::define('blog_add', fn () => $this->checkAccess('blog', 'can_add'));
        Gate::define('blog_update', fn () => $this->checkAccess('blog', 'can_update'));
        Gate::define('blog_delete', fn () => $this->checkAccess('blog', 'can_delete'));
        Gate::define('blog_export', fn () => $this->checkAccess('blog', 'can_export'));
        Gate::define('blog_manage_status', fn () => $this->checkAccess('blog', 'can_manage_status'));
        Gate::define('blog_approve_or_deny', fn () => $this->checkAccess('blog', 'can_approve_or_deny'));

        Gate::define('push_notification_view', fn () => $this->checkAccess('push_notification', 'can_view'));
        Gate::define('push_notification_add', fn () => $this->checkAccess('push_notification', 'can_add'));
        Gate::define('push_notification_update', fn () => $this->checkAccess('push_notification', 'can_update'));
        Gate::define('push_notification_delete', fn () => $this->checkAccess('push_notification', 'can_delete'));
        Gate::define('push_notification_export', fn () => $this->checkAccess('push_notification', 'can_export'));
        Gate::define('push_notification_manage_status', fn () => $this->checkAccess('push_notification', 'can_manage_status'));
        Gate::define('push_notification_approve_or_deny', fn () => $this->checkAccess('push_notification', 'can_approve_or_deny'));

        Gate::define('notification_message_view', fn () => $this->checkAccess('notification_message', 'can_view'));
        Gate::define('notification_message_add', fn () => $this->checkAccess('notification_message', 'can_add'));
        Gate::define('notification_message_update', fn () => $this->checkAccess('notification_message', 'can_update'));
        Gate::define('notification_message_delete', fn () => $this->checkAccess('notification_message', 'can_delete'));
        Gate::define('notification_message_export', fn () => $this->checkAccess('notification_message', 'can_export'));
        Gate::define('notification_message_manage_status', fn () => $this->checkAccess('notification_message', 'can_manage_status'));
        Gate::define('notification_message_approve_or_deny', fn () => $this->checkAccess('notification_message', 'can_approve_or_deny'));

        Gate::define('notification_channel_view', fn () => $this->checkAccess('notification_channel', 'can_view'));
        Gate::define('notification_channel_add', fn () => $this->checkAccess('notification_channel', 'can_add'));
        Gate::define('notification_channel_update', fn () => $this->checkAccess('notification_channel', 'can_update'));
        Gate::define('notification_channel_delete', fn () => $this->checkAccess('notification_channel', 'can_delete'));
        Gate::define('notification_channel_export', fn () => $this->checkAccess('notification_channel', 'can_export'));
        Gate::define('notification_channel_manage_status', fn () => $this->checkAccess('notification_channel', 'can_manage_status'));
        Gate::define('notification_channel_approve_or_deny', fn () => $this->checkAccess('notification_channel', 'can_approve_or_deny'));

        Gate::define('provider_view', fn () => $this->checkAccess('provider', 'can_view'));
        Gate::define('provider_add', fn () => $this->checkAccess('provider', 'can_add'));
        Gate::define('provider_update', fn () => $this->checkAccess('provider', 'can_update'));
        Gate::define('provider_delete', fn () => $this->checkAccess('provider', 'can_delete'));
        Gate::define('provider_export', fn () => $this->checkAccess('provider', 'can_export'));
        Gate::define('provider_manage_status', fn () => $this->checkAccess('provider', 'can_manage_status'));
        Gate::define('provider_approve_or_deny', fn () => $this->checkAccess('provider', 'can_approve_or_deny'));

        Gate::define('serviceman_view', fn () => $this->checkAccess('serviceman', 'can_view'));
        Gate::define('serviceman_add', fn () => $this->checkAccess('serviceman', 'can_add'));
        Gate::define('serviceman_update', fn () => $this->checkAccess('serviceman', 'can_update'));
        Gate::define('serviceman_delete', fn () => $this->checkAccess('serviceman', 'can_delete'));
        Gate::define('serviceman_export', fn () => $this->checkAccess('serviceman', 'can_export'));
        Gate::define('serviceman_manage_status', fn () => $this->checkAccess('serviceman', 'can_manage_status'));

        Gate::define('team_view', fn () => $this->checkAccess('team', 'can_view'));
        Gate::define('team_add', fn () => $this->checkAccess('team', 'can_add'));
        Gate::define('team_update', fn () => $this->checkAccess('team', 'can_update'));
        Gate::define('team_delete', fn () => $this->checkAccess('team', 'can_delete'));
        Gate::define('team_manage_status', fn () => $this->checkAccess('team', 'can_manage_status'));

        Gate::define('task_view', fn () => $this->checkAccess('task', 'can_view'));
        Gate::define('task_add', fn () => $this->checkAccess('task', 'can_add'));
        Gate::define('task_update', fn () => $this->checkAccess('task', 'can_update'));
        Gate::define('task_delete', fn () => $this->checkAccess('task', 'can_delete'));

        Gate::define('zone_view', fn () => $this->checkAccess('zone', 'can_view'));
        Gate::define('zone_add', fn () => $this->checkAccess('zone', 'can_add'));
        Gate::define('zone_update', fn () => $this->checkAccess('zone', 'can_update'));
        Gate::define('zone_delete', fn () => $this->checkAccess('zone', 'can_delete'));
        Gate::define('zone_export', fn () => $this->checkAccess('zone', 'can_export'));
        Gate::define('zone_manage_status', fn () => $this->checkAccess('zone', 'can_manage_status'));
        Gate::define('zone_approve_or_deny', fn () => $this->checkAccess('zone', 'can_approve_or_deny'));

        Gate::define('category_view', fn () => $this->checkAccess('category', 'can_view'));
        Gate::define('category_add', fn () => $this->checkAccess('category', 'can_add'));
        Gate::define('category_update', fn () => $this->checkAccess('category', 'can_update'));
        Gate::define('category_delete', fn () => $this->checkAccess('category', 'can_delete'));
        Gate::define('category_export', fn () => $this->checkAccess('category', 'can_export'));
        Gate::define('category_manage_status', fn () => $this->checkAccess('category', 'can_manage_status'));
        Gate::define('category_approve_or_deny', fn () => $this->checkAccess('category', 'can_approve_or_deny'));

        Gate::define('service_view', fn () => $this->checkAccess('service', 'can_view'));
        Gate::define('service_add', fn () => $this->checkAccess('service', 'can_add'));
        Gate::define('service_update', fn () => $this->checkAccess('service', 'can_update'));
        Gate::define('service_delete', fn () => $this->checkAccess('service', 'can_delete'));
        Gate::define('service_export', fn () => $this->checkAccess('service', 'can_export'));
        Gate::define('service_manage_status', fn () => $this->checkAccess('service', 'can_manage_status'));
        Gate::define('service_approve_or_deny', fn () => $this->checkAccess('service', 'can_approve_or_deny'));

        Gate::define('product_view', fn () => $this->checkAccess('product', 'can_view'));
        Gate::define('product_add', fn () => $this->checkAccess('product', 'can_add'));
        Gate::define('product_update', fn () => $this->checkAccess('product', 'can_update'));
        Gate::define('product_delete', fn () => $this->checkAccess('product', 'can_delete'));
        Gate::define('product_export', fn () => $this->checkAccess('product', 'can_export'));
        Gate::define('product_manage_status', fn () => $this->checkAccess('product', 'can_manage_status'));
        Gate::define('product_approve_or_deny', fn () => $this->checkAccess('product', 'can_approve_or_deny'));
      
        Gate::define('customer_view', fn () => $this->checkAccess('customer', 'can_view'));
        Gate::define('customer_add', fn () => $this->checkAccess('customer', 'can_add'));
        Gate::define('customer_update', fn () => $this->checkAccess('customer', 'can_update'));
        Gate::define('customer_delete', fn () => $this->checkAccess('customer', 'can_delete'));
        Gate::define('customer_export', fn () => $this->checkAccess('customer', 'can_export'));
        Gate::define('customer_manage_status', fn () => $this->checkAccess('customer', 'can_manage_status'));
        Gate::define('customer_approve_or_deny', fn () => $this->checkAccess('customer', 'can_approve_or_deny'));

        Gate::define('role_view', fn () => $this->checkAccess('role', 'can_view'));
        Gate::define('role_add', fn () => $this->checkAccess('role', 'can_add'));
        Gate::define('role_update', fn () => $this->checkAccess('role', 'can_update'));
        Gate::define('role_delete', fn () => $this->checkAccess('role', 'can_delete'));
        Gate::define('role_export', fn () => $this->checkAccess('role', 'can_export'));
        Gate::define('role_manage_status', fn () => $this->checkAccess('role', 'can_manage_status'));
        Gate::define('role_approve_or_deny', fn () => $this->checkAccess('role', 'can_approve_or_deny'));

        Gate::define('employee_view', fn () => $this->checkAccess('employee', 'can_view'));
        Gate::define('employee_add', fn () => $this->checkAccess('employee', 'can_add'));
        Gate::define('employee_update', fn () => $this->checkAccess('employee', 'can_update'));
        Gate::define('employee_delete', fn () => $this->checkAccess('employee', 'can_delete'));
        Gate::define('employee_export', fn () => $this->checkAccess('employee', 'can_export'));
        Gate::define('employee_manage_status', fn () => $this->checkAccess('employee', 'can_manage_status'));
        Gate::define('employee_approve_or_deny', fn () => $this->checkAccess('employee', 'can_approve_or_deny'));

        Gate::define('transaction_view', fn () => $this->checkAccess('transaction', 'can_view'));
        Gate::define('transaction_add', fn () => $this->checkAccess('transaction', 'can_add'));
        Gate::define('transaction_update', fn () => $this->checkAccess('transaction', 'can_update'));
        Gate::define('transaction_delete', fn () => $this->checkAccess('transaction', 'can_delete'));
        Gate::define('transaction_export', fn () => $this->checkAccess('transaction', 'can_export'));
        Gate::define('transaction_manage_status', fn () => $this->checkAccess('transaction', 'can_manage_status'));
        Gate::define('transaction_approve_or_deny', fn () => $this->checkAccess('transaction', 'can_approve_or_deny'));

        Gate::define('report_view', fn () => $this->checkAccess('report', 'can_view'));
        Gate::define('report_add', fn () => $this->checkAccess('report', 'can_add'));
        Gate::define('report_update', fn () => $this->checkAccess('report', 'can_update'));
        Gate::define('report_delete', fn () => $this->checkAccess('report', 'can_delete'));
        Gate::define('report_export', fn () => $this->checkAccess('report', 'can_export'));
        Gate::define('report_manage_status', fn () => $this->checkAccess('report', 'can_manage_status'));
        Gate::define('report_approve_or_deny', fn () => $this->checkAccess('report', 'can_approve_or_deny'));

        Gate::define('analytics_view', fn () => $this->checkAccess('analytics', 'can_view'));
        Gate::define('analytics_add', fn () => $this->checkAccess('analytics', 'can_add'));
        Gate::define('analytics_update', fn () => $this->checkAccess('analytics', 'can_update'));
        Gate::define('analytics_delete', fn () => $this->checkAccess('analytics', 'can_delete'));
        Gate::define('analytics_export', fn () => $this->checkAccess('analytics', 'can_export'));
        Gate::define('analytics_manage_status', fn () => $this->checkAccess('analytics', 'can_manage_status'));
        Gate::define('analytics_approve_or_deny', fn () => $this->checkAccess('analytics', 'can_approve_or_deny'));

        Gate::define('business_add', fn () => $this->checkAccess('business', 'can_add'));
        Gate::define('business_view', fn () => $this->checkAccess('business', 'can_view'));
        Gate::define('business_update', fn () => $this->checkAccess('business', 'can_update'));
        Gate::define('business_delete', fn () => $this->checkAccess('business', 'can_delete'));
        Gate::define('business_export', fn () => $this->checkAccess('business', 'can_export'));
        Gate::define('business_manage_status', fn () => $this->checkAccess('business', 'can_manage_status'));
        Gate::define('business_approve_or_deny', fn () => $this->checkAccess('business', 'can_approve_or_deny'));

        Gate::define('page_view', fn () => $this->checkAccess('page', 'can_view'));
        Gate::define('page_add', fn () => $this->checkAccess('page', 'can_add'));
        Gate::define('page_update', fn () => $this->checkAccess('page', 'can_update'));
        Gate::define('page_delete', fn () => $this->checkAccess('page', 'can_delete'));
        Gate::define('page_export', fn () => $this->checkAccess('page', 'can_export'));
        Gate::define('page_manage_status', fn () => $this->checkAccess('page', 'can_manage_status'));
        Gate::define('page_approve_or_deny', fn () => $this->checkAccess('page', 'can_approve_or_deny'));

        Gate::define('landing_add', fn () => $this->checkAccess('landing', 'can_add'));
        Gate::define('landing_view', fn () => $this->checkAccess('landing', 'can_view'));
        Gate::define('landing_update', fn () => $this->checkAccess('landing', 'can_update'));
        Gate::define('landing_delete', fn () => $this->checkAccess('landing', 'can_delete'));
        Gate::define('landing_export', fn () => $this->checkAccess('landing', 'can_export'));
        Gate::define('landing_manage_status', fn () => $this->checkAccess('landing', 'can_manage_status'));
        Gate::define('landing_approve_or_deny', fn () => $this->checkAccess('landing', 'can_approve_or_deny'));

        Gate::define('login_setup_view', fn () => $this->checkAccess('login_setup', 'can_view'));
        Gate::define('login_setup_add', fn () => $this->checkAccess('login_setup', 'can_add'));
        Gate::define('login_setup_update', fn () => $this->checkAccess('login_setup', 'can_update'));
        Gate::define('login_setup_delete', fn () => $this->checkAccess('login_setup', 'can_delete'));
        Gate::define('login_setup_export', fn () => $this->checkAccess('login_setup', 'can_export'));
        Gate::define('login_setup_manage_status', fn () => $this->checkAccess('login_setup', 'can_manage_status'));
        Gate::define('login_setup_approve_or_deny', fn () => $this->checkAccess('login_setup', 'can_approve_or_deny'));

        Gate::define('language_view', fn () => $this->checkAccess('language', 'can_view'));
        Gate::define('language_add', fn () => $this->checkAccess('language', 'can_add'));
        Gate::define('language_update', fn () => $this->checkAccess('language', 'can_update'));
        Gate::define('language_delete', fn () => $this->checkAccess('language', 'can_delete'));
        Gate::define('language_export', fn () => $this->checkAccess('language', 'can_export'));
        Gate::define('language_manage_status', fn () => $this->checkAccess('language', 'can_manage_status'));
        Gate::define('language_approve_or_deny', fn () => $this->checkAccess('language', 'can_approve_or_deny'));

        Gate::define('gallery_view', fn () => $this->checkAccess('gallery', 'can_view'));
        Gate::define('gallery_add', fn () => $this->checkAccess('gallery', 'can_add'));
        Gate::define('gallery_update', fn () => $this->checkAccess('gallery', 'can_update'));
        Gate::define('gallery_delete', fn () => $this->checkAccess('gallery', 'can_delete'));
        Gate::define('gallery_export', fn () => $this->checkAccess('gallery', 'can_export'));
        Gate::define('gallery_manage_status', fn () => $this->checkAccess('gallery', 'can_manage_status'));
        Gate::define('gallery_approve_or_deny', fn () => $this->checkAccess('gallery', 'can_approve_or_deny'));

        Gate::define('backup_view', fn () => $this->checkAccess('backup', 'can_view'));
        Gate::define('backup_add', fn () => $this->checkAccess('backup', 'can_add'));
        Gate::define('backup_update', fn () => $this->checkAccess('backup', 'can_update'));
        Gate::define('backup_delete', fn () => $this->checkAccess('backup', 'can_delete'));
        Gate::define('backup_export', fn () => $this->checkAccess('backup', 'can_export'));
        Gate::define('backup_manage_status', fn () => $this->checkAccess('backup', 'can_manage_status'));
        Gate::define('backup_approve_or_deny', fn () => $this->checkAccess('backup', 'can_approve_or_deny'));

        Gate::define('firebase_view', fn () => $this->checkAccess('firebase', 'can_view'));
        Gate::define('firebase_add', fn () => $this->checkAccess('firebase', 'can_add'));
        Gate::define('firebase_update', fn () => $this->checkAccess('firebase', 'can_update'));
        Gate::define('firebase_delete', fn () => $this->checkAccess('firebase', 'can_delete'));
        Gate::define('firebase_export', fn () => $this->checkAccess('firebase', 'can_export'));
        Gate::define('firebase_manage_status', fn () => $this->checkAccess('firebase', 'can_manage_status'));
        Gate::define('firebase_approve_or_deny', fn () => $this->checkAccess('firebase', 'can_approve_or_deny'));

        Gate::define('payment_method_view', fn () => $this->checkAccess('payment_method', 'can_view'));
        Gate::define('payment_method_add', fn () => $this->checkAccess('payment_method', 'can_add'));
        Gate::define('payment_method_update', fn () => $this->checkAccess('payment_method', 'can_update'));
        Gate::define('payment_method_delete', fn () => $this->checkAccess('payment_method', 'can_delete'));
        Gate::define('payment_method_export', fn () => $this->checkAccess('payment_method', 'can_export'));
        Gate::define('payment_method_manage_status', fn () => $this->checkAccess('payment_method', 'can_manage_status'));
        Gate::define('payment_method_approve_or_deny', fn () => $this->checkAccess('payment_method', 'can_approve_or_deny'));

        Gate::define('ai_configuration_view', fn () => $this->checkAccess('ai_configuration', 'can_view'));
        Gate::define('ai_configuration_add', fn () => $this->checkAccess('ai_configuration', 'can_add'));
        Gate::define('ai_configuration_update', fn () => $this->checkAccess('ai_configuration', 'can_update'));
        Gate::define('ai_configuration_delete', fn () => $this->checkAccess('ai_configuration', 'can_delete'));
        Gate::define('ai_configuration_export', fn () => $this->checkAccess('ai_configuration', 'can_export'));
        Gate::define('ai_configuration_manage_status', fn () => $this->checkAccess('ai_configuration', 'can_manage_status'));
        Gate::define('ai_configuration_approve_or_deny', fn () => $this->checkAccess('ai_configuration', 'can_approve_or_deny'));

        Gate::define('configuration_view', fn () => $this->checkAccess('configuration', 'can_view'));
        Gate::define('configuration_add', fn () => $this->checkAccess('configuration', 'can_add'));
        Gate::define('configuration_update', fn () => $this->checkAccess('configuration', 'can_update'));
        Gate::define('configuration_delete', fn () => $this->checkAccess('configuration', 'can_delete'));
        Gate::define('configuration_export', fn () => $this->checkAccess('configuration', 'can_export'));
        Gate::define('configuration_manage_status', fn () => $this->checkAccess('configuration', 'can_manage_status'));
        Gate::define('configuration_approve_or_deny', fn () => $this->checkAccess('configuration', 'can_approve_or_deny'));
    }

    private function checkAccess($sectionName, $action): bool
    {
        $user = auth()->user();

        if ($user->user_type === 'super-admin') {
            return true;
        }

        $role = $user->roles->first();
        $roleId = $role->pivot->role_id ?? null;
        if (!$roleId) {
            return false;
        }

        // check module access
        return (bool) $user->module_access
            ->where('role_id', $roleId)
            ->where('section_name', $sectionName)
            ->first()?->$action;
    }
}

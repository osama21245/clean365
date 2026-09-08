<?php

namespace Modules\AdminModule\Traits;

trait AdminMenuWithRoutes
{
    public function adminMenuWithRoutes()
    {
        $result = [
            [
                'route_name' => 'Business_settings',
                'uri' => 'admin/business-settings/get-business-information',
                'full_route' => url('admin/business-settings/get-business-information'),
                "page_title" => 'Business_settings',
                "keywords" => 'Business Settings, Settings management',
                "type" => 'menu'],
            [
                'route_name' => 'Notification_Channel',
                'uri' => 'admin/business-settings/notification-channel?notification_type=user',
                'full_route' => url('admin/business-settings/notification-channel?notification_type=user'),
                "page_title" => 'Notification_Channel',
                "keywords" => 'Notification Channel, Settings management',
                "type" => 'menu'],
            [
                'route_name' => 'Push_notification',
                'uri' => 'admin/configuration/get-notification-setting?type=customers',
                'full_route' => url('admin/configuration/get-notification-setting?type=customers'),
                "page_title" => 'Push_notification',
                "keywords" => 'Push notification, Configuration',
                "type" => 'menu'],
            [
                'route_name' => 'Providers',
                'uri' => 'admin/configuration/get-notification-setting?type=providers',
                'full_route' => url('admin/configuration/get-notification-setting?type=providers'),
                "page_title" => 'Providers',
                "keywords" => 'Providers',
                "type" => 'menu'],
            [
                'route_name' => 'other_configuration',
                'uri' => 'admin/configuration/third-party/map-api',
                'full_route' => url('admin/configuration/third-party/map-api'),
                "page_title" => 'other_configuration',
                "keywords" => 'other configuration, Configuration',
                "type" => 'menu'],
            [
                'route_name' => 'Other_configuration',
                'uri' => 'admin/configuration/third-party/map-api',
                'full_route' => url('admin/configuration/third-party/map-api'),
                "page_title" => 'Other_configuration',
                "keywords" => 'Other configuration, Configuration',
                "type" => 'menu'],
            [
                'route_name' => 'Firebase',
                'uri' => 'admin/configuration/third-party/firebase-configuration',
                'full_route' => url('admin/configuration/third-party/firebase-configuration'),
                "page_title" => 'Firebase',
                "keywords" => 'Firebase',
                "type" => 'menu'],
            [
                'route_name' => 'Payment_methods',
                'uri' => 'admin/configuration/third-party/payment_config?type=digital_payment',
                'full_route' => url('admin/configuration/third-party/payment_config?type=digital_payment'),
                "page_title" => 'Payment_methods',
                "keywords" => 'Payment methods,Payment config',
                "type" => 'menu'],
            [
                'route_name' => 'Storage_connection',
                'uri' => 'admin/configuration/third-party/storage_connection',
                'full_route' => url('admin/configuration/third-party/storage_connection'),
                "page_title" => 'Storage_connection',
                "keywords" => 'Storage connection',
                "type" => 'menu'],
            [
                'route_name' => 'App_settings',
                'uri' => 'admin/configuration/third-party/app_settings',
                'full_route' => url('admin/configuration/third-party/app_settings'),
                "page_title" => 'App_settings',
                "keywords" => 'App Settings',
                "type" => 'menu'],
            [
                'route_name' => 'Language_setup',
                'uri' => 'admin/configuration/language-setup',
                'full_route' => url('admin/configuration/language-setup'),
                "page_title" => 'Language_setup',
                "keywords" => 'Language setup, Configuration',
                "type" => 'menu'],
            [
                'route_name' => 'Business_pages',
                'uri' => 'admin/business-page-setup/list',
                'full_route' => url('admin/business-page-setup/list'),
                "page_title" => 'Business_pages',
                "keywords" => 'Page & media',
                "type" => 'menu'],
            [
                'route_name' => 'Social_media',
                'uri' => 'admin/social-media/index',
                'full_route' => url('admin/social-media/index'),
                "page_title" => 'Social_media',
                "keywords" => 'Page & media',
                "type" => 'menu'],
            [
                'route_name' => 'Gallery',
                'uri' => 'admin/business-settings/get-gallery-setup',
                'full_route' => url('admin/business-settings/get-gallery-setup'),
                "page_title" => 'Gallery',
                "keywords" => 'Gallery',
                "type" => 'menu'],
            [
               'route_name' => 'Ai_configuration',
                'uri' => 'admin/configuration/ai-configuration',
                'full_route' => url('admin/configuration/ai-configuration'),
                "page_title" => 'AI_Configuration',
                "keywords" => 'AI , AI configuration',
                "type" => 'menu']];

        if(count(config('external_admin_routes'))>0) {
            if (count(config('external_admin_routes')) > 0) {
                $result[] = [
                    'route_name' => 'Payment_setup',
                    'uri' => 'admin/payment/configuration/addon-payment-get',
                    'full_route' => url('admin/payment/configuration/addon-payment-get'),
                    "page_title" => 'Payment_setup',
                    "keywords" => 'Addon Menus',
                    "type" => 'menu'];
            }

        }

        return collect($result)->map(function ($item) {
         return [
                "page_title" => $item['route_name'],
                'page_title_value' => $item['route_name'] ?? null,
                'key' => base64_encode($item['uri']),
                'uri' => $item['uri'],
                'uri_count' => count(explode('/', $item['uri'])),
                'full_route' => $item['full_route'] ?? '',
                'type' => $item['type'],
                'priority' => $item['priority'] ?? 1,
                'sorting' => $item['sorting'] ?? '',
                "keywords" => $item['keywords']];
        });
    }
}

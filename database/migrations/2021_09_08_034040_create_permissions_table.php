<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attribute');
            $table->mediumtext('keywords');
            $table->timestamps();
        });

        $now = now();
        $permissions = [
            [
                'attribute' => 'order',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "order_read",
                    'create' => "order_create",
                    'update' => "order_update",
                    'view' => "order_view",
                    'invoice' => "order_invoice",
                    'approve_offline_payment' => "order_approve_offline_payment"
                ])
            ],
            [
                'attribute' => 'pickup_hub',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "pickup_hub_read",
                    'create' => "pickup_hub_create",
                    'update' => "pickup_hub_update",
                    'delete' => "pickup_hub_delete"
                ])
            ],
            [
                'attribute' => 'product',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "product_read",
                    'create' => "product_create",
                    'update' => "product_update",
                    'delete' => "product_delete",
                    'restore' => "product_restore",
                    'clone' => "product_clone"
                ])
            ],
            [
                'attribute' => 'color',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "color_read",
                    'create' => "color_create",
                    'update' => "color_update",
                    'delete' => "color_delete"
                ])
            ],
            [
                'attribute' => 'attribute_set',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "attribute_set_read",
                    'create' => "attribute_set_create",
                    'update' => "attribute_set_update",
                    'delete' => "attribute_set_delete"
                ])
            ],
            [
                'attribute' => 'attribute_value',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "attribute_value_read",
                    'create' => "attribute_value_create",
                    'update' => "attribute_value_update",
                    'delete' => "attribute_value_delete"
                ])
            ],
            [
                'attribute' => 'brand',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "brand_read",
                    'create' => "brand_create",
                    'update' => "brand_update",
                    'delete' => "brand_delete"
                ])
            ],
            [
                'attribute' => 'category',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "category_read",
                    'create' => "category_create",
                    'update' => "category_update",
                    'delete' => "category_delete"
                ])
            ],
            [
                'attribute' => 'wholesale_product',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "wholesale_product_read",
                    'create' => "wholesale_product_create",
                    'update' => "wholesale_product_update",
                    'delete' => "wholesale_product_delete",
                    'clone' => "wholesale_product_clone",
                    'restore' => "wholesale_product_restore",
                    'setting' => "wholesale_product_setting"
                ])
            ],
            [
                'attribute' => 'customer',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "customer_read",
                    'create' => "customer_create",
                    'update' => "customer_update",
                    'ban' => "customer_ban",
                    'user_reward_read' => "user_reward_read",
                    'user_reward_update' => "user_reward_update"
                ])
            ],
            [
                'attribute' => 'seller',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "seller_read",
                    'create' => "seller_create",
                    'update' => "seller_update",
                    'verify' => "seller_verify",
                    'ban' => "seller_ban",
                    'seller_commission_read' => "seller_commission_read",
                    'seller_commission_update' => "seller_commission_update",
                    'seller_payout_read' => "seller_payout_read",
                    'seller_payout_reject' => "seller_payout_reject",
                    'seller_payout_accept' => "seller_payout_accept",
                    'login' => "seller_as_login"
                ])
            ],
            [
                'attribute' => 'delivery_hero',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "delivery_hero_read",
                    'create' => "delivery_hero_create",
                    'update' => "delivery_hero_update",
                    'Ban Delivery Hero' => "delivery_hero_ban",
                    'Account Deposit' => "Delivery_hero_account_deposit",
                    'Email Activation' => "delivery_hero_email_activation",
                    'Commission History' => "delivery_hero_commission_history",
                    'Deposit History' => "delivery_hero_deposit_history",
                    'Collection History' => "delivery_hero_collection_history",
                    'Cancel Request' => "delivery_hero_cancel_request",
                    'Configuration Read' => "delivery_hero_configuration_read",
                    'Configuration Update' => "delivery_hero_configuration_update"
                ])
            ],
            [
                'attribute' => 'media',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "media_read",
                    'create' => "media_create",
                    'update' => "media_update",
                    'delete' => "media_delete"
                ])
            ],
            [
                'attribute' => 'report',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'admin_product_sale' => "admin_product_sale_read",
                    'seller_product_sale' => "seller_product_sale_read",
                    'product_stock' => "product_stock_read",
                    'product_wishlist' => "product_wishlist_read",
                    'user_searches' => "user_searches_read",
                    'commission_history' => "commission_history_read",
                    'wallet_recharge_history' => "wallet_recharge_history_read"
                ])
            ],
            [
                'attribute' => 'refund',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "refund_read",
                    'approve' => "refund_approve",
                    'process' => "refund_process",
                    'reject' => "refund_reject",
                    'refund_setting_read' => "refund_setting_read",
                    'refund_setting_update' => "refund_setting_update"
                ])
            ],
            [
                'attribute' => 'bulk_sms',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "bulk_sms_read",
                    'send_sms' => "send_bulk_sms",
                    'otp_setting_read' => "otp_setting_read",
                    'otp_setting_update' => "otp_setting_update",
                    'sms_template_read' => "sms_template_read",
                    'sms_template_update' => "sms_template_update"
                ])
            ],
            [
                'attribute' => 'campaign',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "campaign_read",
                    'create' => "campaign_create",
                    'update' => "campaign_update",
                    'delete' => "campaign_delete",
                    'campaign_request_read' => "campaign_request_read",
                    'campaign_request_approved' => "campaign_request_approved"
                ])
            ],
            [
                'attribute' => 'campaign_product',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "campaign_product_read",
                    'create' => "campaign_product_create",
                    'update' => "campaign_product_update",
                    'delete' => "campaign_product_delete"
                ])
            ],
            [
                'attribute' => 'subscriber',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "subscriber_read",
                    'delete' => "subscriber_delete"
                ])
            ],
            [
                'attribute' => 'coupon',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "coupon_read",
                    'create' => "coupon_create",
                    'update' => "coupon_update",
                    'delete' => "coupon_delete"
                ])
            ],
            [
                'attribute' => 'blog',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "blog_read",
                    'create' => "blog_create",
                    'update' => "blog_update",
                    'delete' => "blog_delete",
                    'restore' => "blog_restore"
                ])
            ],
            [
                'attribute' => 'blog_category',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "blog_category_read",
                    'create' => "blog_category_create",
                    'update' => "blog_category_update",
                    'delete' => "blog_category_delete"
                ])
            ],
            [
                'attribute' => 'support',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "support_read",
                    'create' => "support_create",
                    'update' => "support_update",
                    'delete' => "support_delete"
                ])
            ],
            [
                'attribute' => 'support_department',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "support_department_read",
                    'create' => "support_department_create",
                    'update' => "support_department_update",
                    'delete' => "support_department_delete"
                ])
            ],
            [
                'attribute' => 'offline_payment',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "offline_payment_read",
                    'create' => "offline_payment_create",
                    'update' => "offline_payment_update",
                    'delete' => "offline_payment_delete",
                    'wallet_recharge_read' => "wallet_recharge_read",
                    'wallet_recharge_update' => "wallet_recharge_update"
                ])
            ],
            [
                'attribute' => 'reward_configuration',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "reward_configuration_read",
                    'update' => "reward_configuration_update",
                    'reward_setting_read' => "reward_setting_read",
                    'reward_setting_create' => "reward_setting_create",
                    'reward_setting_update' => "reward_setting_update"
                ])
            ],
            [
                'attribute' => 'payment_gateway',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "payment_gateway_read",
                    'update' => "payment_gateway_update"
                ])
            ],
            [
                'attribute' => 'shipping_configuration',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "shipping_configuration_read",
                    'update' => "shipping_configuration_update"
                ])
            ],
            [
                'attribute' => 'shipping_country',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "country_read",
                    'update' => "country_update"
                ])
            ],
            [
                'attribute' => 'shipping_state',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "state_read",
                    'create' => "state_create",
                    'update' => "state_update",
                    'delete' => "state_delete"
                ])
            ],
            [
                'attribute' => 'shipping_city',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "city_read",
                    'create' => "city_create",
                    'update' => "city_update",
                    'delete' => "city_delete"
                ])
            ],
            [
                'attribute' => 'store_front',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'theme_option' => "theme_option_update",
                    'header_content' => "header_content_update",
                    'footer_content' => "footer_content_update",
                    'home_page' => "home_page_update",
                    'website_seo' => "website_seo_update",
                    'website_popup' => "website_popup_update",
                    'custom_css' => "custom_css_update",
                    'custom_js' => "custom_js_update",
                    'gdpr' => "gdpr_update",
                    'all_page_read' => "page_read",
                    'all_page_create' => "page_create",
                    'all_page_update' => "page_update",
                    'all_page_delete' => "page_delete"
                ])
            ],
            [
                'attribute' => 'service',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "service_read",
                    'create' => "service_create",
                    'update' => "service_update",
                    'delete' => "service_delete"
                ])
            ],
            [
                'attribute' => 'slider',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "slider_read",
                    'create' => "slider_create",
                    'update' => "slider_update",
                    'delete' => "slider_delete"
                ])
            ],
            [
                'attribute' => 'wallet',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'recharge_request_read' => "recharge_request_read",
                    'recharge_request_status_update' => "recharge_request_status_update"
                ])
            ],
            [
                'attribute' => 'setting',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'general_setting' => "general_setting_update",
                    'preference' => "preference_setting_update",
                    'Social Login' => "social_login_setting_update",
                    'email_setting' => "email_setting_update",
                    'currency' => "currency_setting_update",
                    'vat_tax' => "vat_tax_setting_update",
                    'storage' => "storage_setting_update",
                    'cache' => "cache_update",
                    'miscellaneous' => "miscellaneous_setting_update",
                    'Admin Panel Setting Update' => "admin_panel_setting_update",
                    'Facebook Service' => "facebook_service_update",
                    'Google Service' => "google_service_update",
                    'Pusher Notification' => "pusher_notification_update"
                ])
            ],
            [
                'attribute' => 'chat_messenger',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "chat_messenger_read",
                    'update' => "chat_messenger_update"
                ])
            ],
            [
                'attribute' => 'language',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "language_read",
                    'create' => "language_create",
                    'update' => "language_update",
                    'delete' => "language_delete"
                ])
            ],
            [
                'attribute' => 'staff',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "staff_read",
                    'create' => "staff_create",
                    'update' => "staff_update",
                    'ban' => "staff_ban"
                ])
            ],
            [
                'attribute' => 'role',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "role_read",
                    'create' => "role_create",
                    'update' => "role_update",
                    'delete' => "role_delete"
                ])
            ],
            [
                'attribute' => 'mobile_apps',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'setting_update' => "api_setting_update",
                    'android_setting' => "android_setting_update",
                    'ios_setting' => "ios_setting_update",
                    'app_config' => "app_config_update",
                    'ads_config' => "ads_config_update",
                    'download_link' => "download_link_update"
                ])
            ],
            [
                'attribute' => 'mobile_app_intro',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "mobile_app_intro_read",
                    'create' => "mobile_app_intro_create",
                    'update' => "mobile_app_intro_update",
                    'delete' => "mobile_app_intro_delete"
                ])
            ],
            [
                'attribute' => 'pos_system',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "pos_order",
                    'update' => "pos_config_update"
                ])
            ],
            [
                'attribute' => 'api_key',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'create'  => "api_key_create",
                    'read' => "api_key_read",
                    'update' => "api_key_update",
                    'delete' => "api_key_delete",
                    'read_all' => "api_key_read_all",
                ])
            ],
            [
                'attribute' => 'state_import',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'create'  => "state_import_create",
                ])
            ],
            [
                'attribute' => 'city_import',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'create'  => "city_import_create",
                ])
            ],
            [
                'attribute' => 'firebase',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "firebase_read",
                    'update' => "firebase_update"
                ])
            ],
            [
                'attribute' => 'addon',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read'  => "addon_read",
                    'update' => "addon_update"
                ])
            ],
            [
                'attribute' => 'font',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'update' => "font_update"
                ])
            ],
            [
                'attribute' => 'package',
                'created_at' => $now,
                'updated_at' => $now,
                "keywords" => json_encode([
                    'read' => "package_read",
                    'create' => 'package_create',
                    'update' => 'package_update',
                    'destroy' => 'package_destroy',
                    'status' => 'package_status_change',
                    'settings' => 'subscription_setting_read',
                    'online_payment' => 'online_payment_read',
                    'offline_payment' => 'offline_payment_read'
                ])
            ]
        ];
        \App\Models\Permission::insert($permissions);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}

<?php

/*
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    5.1.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2020, Cartalyst LLC
 * @link       https://cartalyst.com
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrationCartalystSentinel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('name');
            $table->text('permissions')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->unique('slug');
        });

        $now = date('Y-m-d H:i:s');

        $roles = [
            [
                'slug' => 'superadmin',
                'name' => 'Superadmin',
                'permissions' => '["customer_create","customer_read","customer_update","customer_delete","staff_create","staff_read","staff_update","staff_delete","staff_ban","role_create","role_read",
                "role_update","role_delete","seller_create","seller_read","seller_update","seller_delete","seller_verify","language_create","language_read","language_update","language_delete",
                "translation_message_update","media_create","media_read","media_update","media_delete","media_download","brand_create","brand_read","brand_update","brand_delete","color_create",
                "color_read","color_update","color_delete","attribute_set_create","attribute_set_read","attribute_set_update","attribute_set_delete","attribute_value_create","attribute_value_read",
                "attribute_value_update","attribute_value_delete","category_create","category_read","category_update","category_delete","product_create","product_read","product_update","product_delete",
                "product_restore","product_clone","blog_create","blog_read","blog_update","blog_delete","blog_restore","blog_category_create","blog_category_read","blog_category_update","blog_category_delete",
                "support_create","support_read","support_update","support_delete","support_department_create","support_department_read","support_department_update","support_department_delete","seller_payout_read",
                "seller_payout_accept","seller_payout_reject","seller_commission_read","seller_commission_update","order_create","order_read","order_update","order_view","order_delete","order_invoice",
                "pickup_hub_create","pickup_hub_read","pickup_hub_update","pickup_hub_delete","recharge_request_read","recharge_request_status_update","general_setting_update","preference_setting_update",
                "email_setting_update","currency_setting_update","vat_tax_setting_update","storage_setting_update","cache_update","miscellaneous_setting_update","admin_panel_setting_update","facebook_service_update",
                "google_service_update","pusher_notification_update","otp_setting_read","otp_setting_update","sms_template_read","sms_template_update","payment_gateway_read","payment_gateway_update",
                "theme_option_update","header_content_update","footer_content_update","home_page_update","website_seo_update","website_popup_update","custom_css_update","custom_js_update","gdpr_update","page_read",
                "page_create","page_update","page_delete","campaign_create","campaign_read","campaign_update","campaign_delete","campaign_request_read","campaign_request_approved","campaign_product_create",
                "campaign_product_read","campaign_product_update","campaign_product_delete","bulk_sms_read","send_bulk_sms","subscriber_read","subscriber_delete","coupon_read","coupon_create","coupon_update",
                "coupon_delete","shipping_configuration_read","shipping_configuration_update","country_read","country_update","state_read","state_create","state_update","state_delete","city_read","city_create",
                "city_update","city_delete","admin_product_sale_read","seller_product_sale_read","product_stock_read","product_wishlist_read","user_searches_read","commission_history_read","wallet_recharge_history_read",
                "api_setting_update","android_setting_update","ios_setting_update","app_config_update","ads_config_update","download_link_update","mobile_app_intro_read","mobile_app_intro_create","mobile_app_intro_update",
                "mobile_app_intro_delete","delivery_hero_read","delivery_hero_create","delivery_hero_update","delivery_hero_delete","delivery_hero_ban","Delivery_hero_account_deposit","delivery_hero_email_activation",
                "delivery_hero_commission_history","delivery_hero_deposit_history","delivery_hero_collection_history","delivery_hero_cancel_request","delivery_hero_configuration_read","delivery_hero_configuration_update",
                "wholesale_product_read","wholesale_product_create","wholesale_product_update","wholesale_product_delete","wholesale_product_clone","wholesale_product_restore","wholesale_product_setting","refund_read",
                "refund_approve","refund_process","refund_reject","refund_setting_read","refund_setting_update","reward_configuration_read","reward_configuration_update","reward_setting_read","reward_setting_create",
                "reward_setting_update","user_reward_read","user_reward_update","offline_payment_read","offline_payment_create","offline_payment_update","offline_payment_delete","service_read","service_create","service_update",
                "service_delete","slider_read","slider_create","slider_update","slider_delete","wallet_recharge_read","wallet_recharge_update","login_singup_read","login_singup_update"]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'staff',
                'name' => 'Staff',
                'permissions' => '["customer_create","customer_read","customer_update","customer_delete","customer_ban","staff_create","staff_read","staff_update","staff_delete","role_create","role_read",
                "role_update","role_delete","seller_create","seller_read","seller_update","seller_delete","seller_ban","language_create",
                "language_read","language_update","language_delete","translation_message_update","media_create","media_read","media_update",
                "media_delete","media_download","brand_create","brand_read","brand_update","brand_delete","color_create","color_read","color_update","color_delete"]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        \App\Models\Role::insert($roles);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email',50)->nullable();
            $table->string('phone',20)->nullable();
            $table->string('password')->nullable();
            $table->text('permissions')->nullable();
            $table->string('first_name',50)->nullable();
            $table->string('last_name',50)->nullable();
            $table->enum('user_type', ['admin','staff','seller','customer','delivery_hero'])->default('customer');
            $table->string('gender')->default('male')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 inactive, 1 active');
            $table->tinyInteger('is_user_banned')->default(0)->comment('0 unban, 1 ban');
            $table->tinyInteger('newsletter_enable')->default(0)->comment('0 unable, 1 enable');
            $table->integer('otp')->nullable()->comment('used for reset password');
            $table->string('firebase_auth_id', 100)->nullable()->comment('this is for mobile app.');
            $table->tinyInteger('is_password_set')->default(1)->comment('0 for social login');
            $table->text('images')->nullable();
            $table->longText('socials')->nullable()->comment('it will be array data');
            $table->timestamp('last_login')->nullable();
            $table->string('last_ip',30)->nullable();
            $table->timestamp('last_password_change')->nullable();
            $table->bigInteger('image_id')->unsigned()->nullable();
            $table->integer('role_id')->unsigned()->nullable();
            $table->bigInteger('pickup_hub_id')->unsigned()->nullable();
            $table->double('balance', 20,3)->default(0.000);
            $table->timestamps();

            $table->engine = 'InnoDB';
        });

        $now = now();
        $users = [
            [
                'email' => 'admin@spagreen.net',
                'phone' => NULL,
                'password' => "$2y$10$64MnZYWDF/dtA3iBZ3K0be77.4qNy9Wi3lzjRO.drw26ADAXwRQQq",
                'permissions' => '["customer_create","customer_read","customer_update","customer_delete","customer_ban","staff_create","staff_read","staff_update","staff_delete","staff_ban","role_create",
                "role_read","role_update","role_delete","seller_create","seller_read","seller_update","seller_delete","seller_verify","seller_ban","seller_payout_read","seller_payout_accept","seller_payout_reject",
                "seller_commission_read","seller_commission_update","language_create","language_read","language_update","language_delete","translation_message_update","media_create","media_read","media_update","media_delete",
                "media_download","brand_create","brand_read","brand_update","brand_delete","color_create","color_read","color_update","color_delete","attribute_set_create","attribute_set_read","attribute_set_update",
                "attribute_set_delete","attribute_value_create","attribute_value_read","attribute_value_update","attribute_value_delete","category_create","category_read","category_update","category_delete","product_create",
                "product_read","product_update","product_delete","product_restore","product_clone","blog_create","blog_read","blog_update","blog_delete","blog_restore","blog_category_create","blog_category_read",
                "blog_category_update","blog_category_delete","support_create","support_read","support_update","support_delete","support_department_create","support_department_read","support_department_update",
                "support_department_delete","order_create","order_read","order_update","order_view","order_delete","order_invoice","order_approve_offline_payment","pickup_hub_create","pickup_hub_read","pickup_hub_update",
                "pickup_hub_delete","recharge_request_read","recharge_request_status_update","general_setting_update","preference_setting_update","email_setting_update","currency_setting_update","vat_tax_setting_update",
                "storage_setting_update","cache_update","miscellaneous_setting_update","admin_panel_setting_update","facebook_service_update","google_service_update","pusher_notification_update","otp_setting_read",
                "otp_setting_update","sms_template_read","sms_template_update","payment_gateway_read","payment_gateway_update","theme_option_update","header_content_update","footer_content_update","home_page_update",
                "website_seo_update","website_popup_update","custom_css_update","custom_js_update","gdpr_update","page_read","page_create","page_update","page_delete","campaign_create","campaign_read","campaign_update",
                "campaign_delete","campaign_request_read","campaign_request_approved","bulk_sms_read","send_bulk_sms","subscriber_read","subscriber_delete","campaign_product_create","campaign_product_read",
                "campaign_product_update","campaign_product_delete","coupon_read","coupon_create","coupon_update","coupon_delete","shipping_configuration_read","shipping_configuration_update","country_read",
                "country_update","state_read","state_create","state_update","state_delete","city_read","city_create","city_update","city_delete","admin_product_sale_read","seller_product_sale_read","product_stock_read",
                "product_wishlist_read","user_searches_read","commission_history_read","wallet_recharge_history_read","api_setting_update","android_setting_update","ios_setting_update","app_config_update",
                "ads_config_update","download_link_update","mobile_app_intro_read","mobile_app_intro_create","mobile_app_intro_update","mobile_app_intro_delete","delivery_hero_read","delivery_hero_create",
                "delivery_hero_update","delivery_hero_delete","delivery_hero_ban","Delivery_hero_account_deposit","delivery_hero_email_activation","delivery_hero_commission_history","delivery_hero_deposit_history",
                "delivery_hero_collection_history","delivery_hero_cancel_request","delivery_hero_configuration_read","delivery_hero_configuration_update","wholesale_product_read","wholesale_product_create",
                "wholesale_product_update","wholesale_product_delete","wholesale_product_clone","wholesale_product_restore","wholesale_product_setting","refund_read","refund_approve","refund_process","refund_reject",
                "refund_setting_read","refund_setting_update","reward_configuration_read","reward_configuration_update","reward_setting_read","reward_setting_create","reward_setting_update","user_reward_read",
                "user_reward_update","offline_payment_read","offline_payment_create","offline_payment_update","offline_payment_delete","service_read","service_create","service_update","service_delete","slider_read",
                "slider_create","slider_update","slider_delete","wallet_recharge_read","wallet_recharge_update","login_singup_read","login_singup_update","chat_messenger_read","chat_messenger_update",
                "social_login_setting_update","video_shopping_read","video_shopping_create","video_shopping_update","video_shopping_delete","pos_config_update","pos_order","api_key_create","api_key_update",
                "api_key_delete","api_key_read","api_key_read_all","state_import_create","city_import_create","firebase_read","firebase_update","addon_read","addon_update","font_update","seller_as_login","package_read",
                "package_create","package_update","package_destroy","package_status_change","subscription_setting_read","online_payment_read","offline_payment_read"]',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'user_type' => 'admin',
                'gender' => 'male',
                'date_of_birth' => NULL,
                'status' => 1,
                'is_user_banned' => 0,
                'newsletter_enable' => 0,
                'otp' => NULL,
                'firebase_auth_id' => NULL,
                'is_password_set' => 1,
                'images' => "[]",
                'socials' => "[]",
                'last_login' => $now,
                'last_ip' => NULL,
                'last_password_change' => NULL,
                'image_id' => NULL,
                'role_id' => NULL,
                'pickup_hub_id' => NULL,
                'balance' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'email' => NULL,
                'phone' => NULL,
                'password' => "$2y$10$4G7oN2kI8zJAx3WBlaDUoujmIahD8LisITdXchaOd2C4MbYzOAjqS",
                'permissions' => "[]",
                'first_name' => 'Walk-In',
                'last_name' => 'Customer',
                'user_type' => 'walk_in',
                'gender' => 'male',
                'date_of_birth' => NULL,
                'status' => 1,
                'is_user_banned' => 0,
                'newsletter_enable' => 0,
                'otp' => NULL,
                'firebase_auth_id' => NULL,
                'is_password_set' => 1,
                'images' => "[]",
                'socials' => "[]",
                'last_login' => $now,
                'last_ip' => NULL,
                'last_password_change' => NULL,
                'image_id' => NULL,
                'role_id' => NULL,
                'pickup_hub_id' => NULL,
                'balance' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];
        \App\Models\User::insert($users);

        Schema::create('activations', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('code');
            $table->boolean('completed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
        });
        $now = date('Y-m-d H:i:s');

        $activations = [
            [
                'user_id' => 1,
                'code' => 'jtrgT5HKaZhXox6VmOIpHnZrCqBHp2QH',
                'completed' => 1,
                'completed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 2,
                'code' => 'b8lmkVx7aiJquAXqcfghMtHSJC3j6XJ0',
                'completed' => 1,
                'completed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        \App\Models\Activation::insert($activations);


        Schema::create('persistences', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('code');
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->unique('code');
        });

        Schema::create('reminders', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('code');
            $table->boolean('completed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
        });

        Schema::create('role_users', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->nullableTimestamps();

            $table->engine = 'InnoDB';
            $table->primary(['user_id', 'role_id']);
        });

        \Illuminate\Support\Facades\DB::table('role_users')->insert([
            'user_id' => 1,
            'role_id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Schema::create('throttle', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('type');
            $table->string('ip')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('throttle');
        Schema::drop('role_users');
        Schema::drop('persistences');
        Schema::drop('activations');
        Schema::drop('reminders');
        Schema::drop('users');
        Schema::drop('roles');
    }
}

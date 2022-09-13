<?php

require_once 'Views.php';
require_once 'Apis.php';

/**
 * @author  winter_986@qq.com
 * @name Winter WP后端插件
 *
 */
class Winter
{


    function __construct()
    {

        add_action('init', function () {
            Hooks::booking_record_fun();
        });

        add_action('rest_api_init', function () {
            self::register_api();
        });
        add_action('admin_init', function () {
            Hooks::booking_view_ext();
        });
        add_action('save_post', function ($post_id, $view) {
            Hooks::booking_cus_save($post_id, $view);
        }, 10, 2);

        add_action('manage_macro_booking_record_posts_custom_column', function ($column, $post_id) {
            Hooks::booking_admin_column($column, $post_id);
        }, 10, 2);
        add_filter('manage_macro_booking_record_posts_columns', function ($columns) {
            return Hooks::booking_admin_columns($columns);
        });

        add_action( 'admin_enqueue_scripts', function (){
            Hooks::loadJs();
        } );
        add_action('wp_ajax_change_booking_status', function () {
            Hooks::change_booking_status();
        });
        add_action('wp_ajax_nopriv_change_booking_status',function (){
            Hooks::change_booking_status();
        });
    }


    private static function register_api()
    {

        register_rest_route('macro', '/booking_signIn', array(
            'methods' => 'GET',
            'callback' => function ($request) {
                return (new Apis($request))->bookingSignIn();
            },
        ));

        register_rest_route('macro', '/booking_list', array(
            'methods' => 'GET',
            'callback' => function ($request) {
                return (new Apis($request))->bookingListQuery();
            },
        ));

        register_rest_route('macro', '/booking_signUp', array(
            'methods' => 'POST',
            'callback' => function ($request) {
                return (new Apis($request))->bookingSignUp();
            },
        ));

        register_rest_route('macro', '/booking_create', array(
            'methods' => 'POST',
            'callback' => function ($request) {
                return (new Apis($request))->bookingCreate();
            },
        ));

    }


}



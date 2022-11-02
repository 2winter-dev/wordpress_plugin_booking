<?php

require_once 'Views.php';
require_once 'Apis.php';
require_once 'omiHooks.php';

/**
 * @author  omibeaver
 * @name BookingInit WP init hooks
 *
 */
class BookingInit
{


    function __construct()
    {

        //create booking post type.
      //  add_action('init', function () {
        //    omiHooks::booking_record_fun();
        //});

        //init routes.
        add_action('rest_api_init', function () {
            self::register_api();
        });

        //custom admin booking pane  btn column.
       // add_action('admin_init', function () {
         //   omiHooks::booking_view_ext();
        //});

        //custom admin booking pane  save post.
       // add_action('save_post', function ($post_id, $view) {
         //   omiHooks::booking_cus_save($post_id, $view);
        //}, 10, 2);


        //custom admin booking pane column.
       // add_action('manage_macro_booking_record_posts_custom_column', function ($column, $post_id) {
         //   omiHooks::booking_admin_column($column, $post_id);
        //}, 10, 2);

        //custom admin booking pane columns.
       // add_filter('manage_macro_booking_record_posts_columns', function ($columns) {
         //   return omiHooks::booking_admin_columns($columns);
        //});

        //load custom JS.
       // add_action( 'admin_enqueue_scripts', function (){
         //   omiHooks::loadJs();
        //} );

        //hook admin pane booking statue change.
        //add_action('wp_ajax_change_booking_status', function () {
          //  omiHooks::change_booking_status();
        //});

        //hook admin pane booking statue change.
       // add_action('wp_ajax_nopriv_change_booking_status',function (){
         //   omiHooks::change_booking_status();
        //});

        //custom admin booking pane row
      //  add_action( 'post_row_actions',function ($actions,$post ){
        //   return omiHooks::removeRowBtn($actions,$post );
        //} ,10,2);

        //custom woocommerce product
       // add_filter( 'woocommerce_rest_prepare_shop_order_object', function ($data, $post, $context){
       //     return omiHooks::customOrderQuery($data, $post, $context);
      //  }, 12, 3 );

        //close auto-update tips.
        //omiHooks::closeUpdate();

    }


    //start register all hooks.
    private static function register_api()
    {

        //rest loginIn
        register_rest_route('sunflower', '/signIn', array(
            'methods' => 'GET',
            'callback' => function ($request) {
                return (new Apis($request))->bookingSignIn();
            },
            'permission_callback' => '__return_true'
        ));


         register_rest_route('sunflower', '/signUp', array(
                    'methods' => 'POST',
                    'callback' => function ($request) {
                        return (new Apis($request))->bookingSignUp();
                    },
                    'permission_callback' => '__return_true'
                ));


        //rest query booking list by user
       // register_rest_route('sunflower', '/booking_list', array(
          //  'methods' => 'GET',
       //     'callback' => function ($request) {
       //         return (new Apis($request))->bookingListQuery();
        //   },
        //    'permission_callback' => '__return_true'
     //   ));


        //rest create new booking
       // register_rest_route('macro', '/booking_create', array(
          //  'methods' => 'POST',
         //   'callback' => function ($request) {
          //      return (new Apis($request))->bookingCreate();
          //  },
          //  'permission_callback' => '__return_true'
      //  ));


        //rest change  booking status
       // register_rest_route('macro', '/booking_update', array(
       //     'methods' => 'PUT',
        //    'callback' => function ($request) {
        //        return (new Apis($request))->bookingStatusUpdate();
        //    },
         //   'permission_callback' => '__return_true'
       // ));


       // register_rest_route('macro', '/create_order', array(
        //    'methods' => 'POST',
        //    'callback' => function ($request) {
        //        return (new Apis($request))->createOrder();
        //    },
         //   'permission_callback' => '__return_true'
        //));


    }


}







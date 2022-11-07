<?php
/**
 * Plugin Name:       小葵公文插件
 * Plugin URI:        https://codecanyon.net/user/omibeaver/portfolio
 * Description:       自定义WP-REST接口
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            omiBeaver
 * Author URI:        https://codecanyon.net/user/omibeaver/portfolio
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://codecanyon.net/user/omibeaver/portfolio
 * Text Domain:       OmiBeaverBooking
 * Domain Path:       /languages
 */

require_once 'Apis.php';
require_once 'omiHooks.php';

/**
 * @author  omibeaver
 * @name Sunflower WP init hooks
 *
 */
class Sunflower
{


    function __construct()
    {

        //创建支付记录帖子类型
        add_action('init', function () {omiHooks::pay_record_fun();});
        //初始化自定义路由
        add_action('rest_api_init', function () {self::register_api();});
        omiHooks::closeUpdate();

    }

    private static function register_api()
    {

        //登录
        register_rest_route('sunflower', '/signIn', array(
            'methods' => 'POST',
            'callback' => function ($request) {
                return (new Apis($request))->signIn();
            },
            'permission_callback' => '__return_true'
        ));
        //注册
        register_rest_route('sunflower', '/signUp', array(
            'methods' => 'POST',
            'callback' => function ($request) {
                return (new Apis($request))->signUp();
            },
            'permission_callback' => '__return_true'
        ));
        //订阅
         register_rest_route('sunflower', '/payRecordCreate', array(
          'methods' => 'POST',
           'callback' => function ($request) {
              return (new Apis($request))->PayRecordCreate();
          },
          'permission_callback' => '__return_true'
          ));

         //添加收藏
        register_rest_route('sunflower', '/likeAdd', array(
            'methods' => ['POST'],
            'callback' => function ($request) {
                return (new Apis($request))->addUserLike();
            },
            'permission_callback' => '__return_true'
        ));
    }
}

new Sunflower;






<?php
/**
 * @author  omibeaver
 * Booking Hoos list
 */
class omiHooks
{


    public static function pay_record_fun()
    {
        register_post_type('sun_pay_record',
            array(
                'label' => '订阅管理',
                'labels' => array(
                    'name' => '订阅支付记录',
                    'singular_name' => '订阅支付记录',
                    'add_new' => '添加',
                    'add_new_item' => '添加',
                    'edit' => '更新',
                    'edit_item' => '更新',
                    'new_item' => '创建',
                    'view' => '详情',
                    'view_item' => '详情',
                    'search_items' => '查询',
                    'not_found' => '无',
                    'not_found_in_trash' => '无'
                ),
                'show_ui' => true,
                'show_in_menu' => true,
                'public' => true,
                'description' => '订阅支付记录',
                'has_archive' => false,
                'show_in_rest' => false,
                'supports' => [
                    'title',
                    'author'
                ]
            )
        );
    }






    public static function closeUpdate(){
        add_filter('pre_site_transient_update_core', function ($a){return null;});
        add_filter('pre_site_transient_update_plugins',  function ($a){return null;});
        add_filter('pre_site_transient_update_themes',  function ($a){return null;});
        remove_action('admin_init', '_maybe_update_core');
        remove_action('admin_init', '_maybe_update_plugins');
        remove_action('admin_init', '_maybe_update_themes');
    }





}
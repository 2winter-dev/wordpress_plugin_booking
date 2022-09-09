<?php

require_once 'BasicAuth.php';
require_once 'Views.php';
class Macro_api
{


    function __construct()
    {

        add_action( 'admin_init', function (){self::booking_view_ext();} );
        add_action( 'save_post',function ($post_id,$view){self::booking_cus_save($post_id,$view);}, 10, 2 );
        add_filter('rest_authentication_errors', 'json_basic_auth_error');
        add_filter('determine_current_user', 'json_basic_auth_handler', 20);
        add_action('init', function () {self::booking_record_fun();});
        add_action('manage_macro_booking_record_posts_custom_column',function ($column,$post_id){self::booking_admin_column($column,$post_id);},10,2);
        add_filter('manage_macro_booking_record_posts_columns', function ($columns){return self::booking_admin_columns($columns);});
    }


    private static function booking_admin_columns($columns){
        $columns['title'] = '预约课程|教练';
        $columns['author'] ='顾客';
        unset( $columns['date']);
        $columns['booking_time'] = '预约时间';
        $columns['booking_status'] = '预约状态';
        return $columns;
    }

    private static function booking_admin_column($column, $post_id){
        if($column == 'booking_status'){
            $bg = get_post_meta( $post_id, 'booking_status', true ) == '0' ? 'tomato':'#578fff';
            echo "<span style='background:$bg;padding:4px 10px;box-shadow: 0 1px 4px 2px rgba(0,0,0,0.1) ;cursor: pointer;border-radius: 15px;color: #fff'>" .(get_post_meta( $post_id, 'booking_status', true ) == '0' ? '未使用':'已使用').'</span>';
        }
        if($column == 'booking_time'){
           echo str_replace('T',' ',get_post_meta( $post_id, 'booking_time', true ));
        }

    }

    private static function booking_record_fun()
    {
        register_post_type('macro_booking_record',
            array(
                'label' => '预约记录',
                'map_meta_cap'=>true,
                'labels' => array(
                    'name' => '课程预约',
                    'singular_name' => '课程预约清单',
                    'add_new' => '新增预约',
                    'add_new_item' => '新增预约',
                    'edit' => '更新',
                    'edit_item' => '更新',
                    'new_item' => '创建预约',
                    'view' => '详情',
                    'view_item' => '查看预约详情',
                    'search_items' => '查询预约',
                    'not_found' => '没有找到',
                    'not_found_in_trash' => '没有发现'
                ),
                'public' => true,
                'show_in_rest'=>true,
                'description' => '预约管理',
                'has_archive' => false,
                'supports'=>[
                    'title',
                    'author'
                ]
            )
        );


    }


    private static function booking_view_ext(){
        add_meta_box( 'macro_review_meta_box',
            '预约', function ($view){Macro_views::booking_cus_view($view);},
            'macro_booking_record', 'normal', 'high'
        );

    }

    private static function booking_cus_save($post_id, $view ){
        if ( $view->post_type == 'macro_booking_record' ) {
            // Store data in post meta table if present in post data
            if ( isset( $_POST['booking_time'] ) && $_POST['booking_time'] != '' ) {
                update_post_meta( $post_id, 'booking_time', $_POST['booking_time'] );
            }
            if ( isset( $_POST['booking_status'] ) && $_POST['booking_status'] != '' ) {
                update_post_meta( $post_id, 'booking_status', $_POST['booking_status'] );
            }
        }
    }





    function macro_booking_query(): Macro_api
    {
        return $this;
    }

    function macro_booking_update(): Macro_api
    {
        return $this;

    }


}


<?php

class Hooks
{

    public static function booking_view_ext()
    {
        add_meta_box('macro_review_meta_box', '预约',
            function ($view) {
                Macro_views::booking_cus_view($view);
            },
            'macro_booking_record', 'normal', 'high'
        );

    }

    public static function booking_cus_save($post_id, $view)
    {
        if ($view->post_type == 'macro_booking_record') {
            if (isset($_POST['booking_time']) && $_POST['booking_time'] != '') {
                update_post_meta($post_id, 'booking_time', $_POST['booking_time']);
            }
            if (isset($_POST['booking_status']) && $_POST['booking_status'] != '') {
                update_post_meta($post_id, 'booking_status', $_POST['booking_status']);
            }


        }
    }

    public static function booking_admin_column($column, $post_id)
    {
        if ($column == 'booking_status') {
            Macro_views::booking_status_change($post_id);
        }
        if ($column == 'booking_time') {
            echo str_replace('T', ' ', get_post_meta($post_id, 'booking_time', true));
        }

    }


    public static function booking_admin_columns($columns)
    {
        $columns['title'] = '预约课程|教练';
        $columns['author'] = '顾客';
        unset($columns['date']);
        $columns['booking_time'] = '预约时间';
        $columns['booking_status'] = '预约状态';
        return $columns;
    }

    public static function booking_record_fun()
    {
        register_post_type('macro_booking_record',
            array(
                'label' => '预约记录',
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
                'show_ui' => true,
                'show_in_menu' => true,
                'public' => true,
                'description' => '预约管理',
                'has_archive' => false,
                'show_in_rest' => false,
                'supports' => [
                    'title',
                    'author'
                ]
            )
        );
    }


    public static function change_booking_status()
    {
        check_ajax_referer('winter_986@qq.com');
        $post_id = $_POST['post_id'];
        $args = array(
            'post_type' => 'macro_booking_record',
            'posts_per_page' => 10,
            'p' =>$post_id
        );
        $data = (new WP_Query($args))->posts;
        if(count($data)  != 1){
            wp_send_json(['code'=>-1,'msg'=>'操作失败:内容不存在！','data'=>null]);
        }
        if(get_post_meta($post_id,'booking_status',true)['booking_status'] == '0'){
            update_post_meta($post_id, 'booking_status', 1);
            wp_send_json(['code'=>1,'msg'=>'操作成功','data'=>null]);
        }else{
            wp_send_json(['code'=>1,'msg'=>'操作失败,已经更改过！']);
        }
    }

    public static function loadJs()
    {

        wp_enqueue_script('ajax-script', plugins_url('/assets/utils.js', __FILE__));
        wp_localize_script(
            'ajax-script',
            'winter_ajax_obj',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('winter_986@qq.com'),
            )
        );
    }


}
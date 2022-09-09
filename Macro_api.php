<?php

require_once 'Views.php';

/**
 * @author  winter_986@qq.com
 * @name Macro_api WP后端插件
 *
 */
class Macro_api
{


    function __construct()
    {

        add_action('admin_init', function () {
            self::booking_view_ext();
        });
        add_action('save_post', function ($post_id, $view) {
            self::booking_cus_save($post_id, $view);
        }, 10, 2);
        add_action('rest_api_init', function () {
            self::register_api();
        });
        add_action('init', function () {
            self::booking_record_fun();
        });
        add_action('manage_macro_booking_record_posts_custom_column', function ($column, $post_id) {
            self::booking_admin_column($column, $post_id);
        }, 10, 2);
        add_filter('manage_macro_booking_record_posts_columns', function ($columns) {
            return self::booking_admin_columns($columns);
        });
    }


    private static function booking_admin_columns($columns)
    {
        $columns['title'] = '预约课程|教练';
        $columns['author'] = '顾客';
        unset($columns['date']);
        $columns['booking_time'] = '预约时间';
        $columns['booking_status'] = '预约状态';
        return $columns;
    }

    private static function booking_admin_column($column, $post_id)
    {
        if ($column == 'booking_status') {
            $bg = get_post_meta($post_id, 'booking_status', true) == '0' ? 'tomato' : '#578fff';
            echo "<span style='background:$bg;padding:4px 10px;box-shadow: 0 1px 4px 2px rgba(0,0,0,0.1) ;cursor: pointer;border-radius: 15px;color: #fff'>" . (get_post_meta($post_id, 'booking_status', true) == '0' ? '未使用' : '已使用') . '</span>';
        }
        if ($column == 'booking_time') {
            echo str_replace('T', ' ', get_post_meta($post_id, 'booking_time', true));
        }

    }

    private static function booking_record_fun()
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


    private static function booking_view_ext()
    {
        add_meta_box('macro_review_meta_box', '预约',
            function ($view) {
                Macro_views::booking_cus_view($view);
            },
            'macro_booking_record', 'normal', 'high'
        );

    }

    private static function booking_cus_save($post_id, $view)
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


    private static function bookingListQuery($request): array
    {
        $cookie = $request->get_param('token');
        $is_login = wp_validate_auth_cookie($cookie, 'macro');
        if ($is_login) {
            $args = array(
                'post_type' => 'macro_booking_record',
                'posts_per_page' => 10,
            );
            $data = (new WP_Query($args))->posts;
            foreach ($data as $key => $post) {
                $post->booking_status = get_post_meta($post->ID, 'booking_status', true);
                $post->booking_time = get_post_meta($post->ID, 'booking_time', true);

            }

            return ['code' => 1, 'msg' => 'SUCCESS', 'data' => $data];
        } else {
            return ['code' => -1, 'msg' => '请先登录', 'data' => null];
        }


    }


    private static function bookingSignIn($request): array
    {

        $username = $request->get_param('username');
        $password = $request->get_param('password');
        $user = wp_authenticate($username, $password);
        return ['code' => 1, 'msg' => 'success', 'user' => $user, 'token' => wp_generate_auth_cookie($user->ID, time() + 7200, 'macro')];

    }

    private static function register_api()
    {


        register_rest_route('macro', '/booking_signIn', array(
            'methods' => 'GET',
            'callback' => function ($request) {
                return self::bookingSignIn($request);
            },
        ));
        register_rest_route('macro', '/booking_list', array(
            'methods' => 'GET',
            'callback' => function ($request) {
                return self::bookingListQuery($request);
            },
        ));
    }


}



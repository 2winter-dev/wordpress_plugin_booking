<?php

/**
 * @author  omibeaver
 * Booking Hoos list
 */
class omiHooks
{
    public static function booking_view_ext()
    {
        add_meta_box(
            'macro_review_meta_box',
            'Booking',
            function ($view) {
                Macro_views::booking_cus_view($view);
            },
            'macro_booking_record',
            'normal',
            'high'
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
        if ($column == 'ID') {
            echo $post_id;
        }
    }


    public static function booking_admin_columns($columns)
    {

        $columns['title'] = 'booking';
        $columns['author'] = 'customer';
        unset($columns['date']);

        $columns['booking_time'] = 'booking time';
        $columns['booking_status'] = 'booking status';
        $columns['ID'] = 'Record ID';

        return $columns;
    }

    public static function booking_record_fun()
    {
        register_post_type(
            'macro_booking_record',
            array(
                'label' => 'booking record',
                'labels' => array(
                    'name' => 'Booking',
                    'singular_name' => 'Booking list',
                    'add_new' => 'add booking record',
                    'add_new_item' => 'add booking record',
                    'edit' => 'update booking record',
                    'edit_item' => 'update',
                    'new_item' => 'create',
                    'view' => 'detail',
                    'view_item' => 'to detail',
                    'search_items' => 'query',
                    'not_found' => 'not found',
                    'not_found_in_trash' => 'not found'
                ),
                'show_ui' => true,
                'show_in_menu' => true,
                'public' => true,
                'description' => 'Booking Manage',
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
        check_ajax_referer('omiBeaver');
        $post_id = $_POST['post_id'];
        $args = array(
            'post_type' => 'macro_booking_record',
            'posts_per_page' => 10,
            'p' => $post_id
        );
        $data = (new WP_Query($args))->posts;
        if (count($data)  != 1) {
            wp_send_json(['code' => -1, 'msg' => 'action failed:content not found！', 'data' => null]);
        }
        if (get_post_meta($post_id, 'booking_status', true)['booking_status'] == '0') {
            update_post_meta($post_id, 'booking_status', 1);
            wp_send_json(['code' => 1, 'msg' => 'success', 'data' => null]);
        } else {
            wp_send_json(['code' => 1, 'msg' => 'action failed']);
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
                'nonce' => wp_create_nonce('omiBeaver'),
            )
        );
    }

    public static function closeUpdate()
    {
        add_filter('pre_site_transient_update_core', function ($a) {
            return null;
        });
        add_filter('pre_site_transient_update_plugins',  function ($a) {
            return null;
        });
        add_filter('pre_site_transient_update_themes',  function ($a) {
            return null;
        });
        remove_action('admin_init', '_maybe_update_core');
        remove_action('admin_init', '_maybe_update_plugins');
        remove_action('admin_init', '_maybe_update_themes');
    }


    public static function removeRowBtn($actions, $post)
    {

        if ($post->post_type == "macro_booking_record") {
            unset($actions['view']);
            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }




    public static function customOrderQuery($data, $post, $context): array
    {

        $data->data['booking_left'] = 2;

        return $data;
    }
}

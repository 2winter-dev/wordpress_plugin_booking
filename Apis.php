<?php
require_once 'omiHooks.php';

/**
 * @author  omibeaver
 * Rest APIs
 */
class Apis
{

    private $request;
    private $user;
    private const WOO_SECRET = 'xxxx';//本地测试可写secret
    private const WOO_KEY = 'xxx';//本地测试可写key
    private const REMOTE_URL = 'xxxx';


    function __construct($request)
    {

        $this->request = $request;
        $is_login = wp_validate_auth_cookie($request->get_param('token'), 'macro');
        if ($is_login) {
            $userAuthInfo = wp_parse_auth_cookie($request->get_param('token'), 'macro');
            $this->user = get_user_by('login', $userAuthInfo['username'])->data;
        }
    }


    public function bookingStatusUpdate(): array
    {
        if (!$this->user) return ['code' => -1, 'msg' => 'token invalid', 'data' => null];
        $post_id = $this->request->get_param('post_id');
        $user_id = self::getUserByCookie($this->request->get_param('token'))->ID;
        if (!$post_id) {
            return ['code' => -1, 'msg' => 'params error', 'data' => null];
        }
        $args = array(
            'post_type' => 'macro_booking_record',
            'posts_per_page' => 10,
            'p' => $post_id
        );
        $data = (new WP_Query($args))->posts;
        if (count($data) != 1) {
            return ['code' => -1, 'msg' => 'content not found', 'data' => null];
        }
        if ($data[0]->post_author != $user_id) {
            return ['code' => -1, 'msg' => 'not permission', 'data' => null];
        }
        if (get_post_meta($post_id, 'booking_status', true)['booking_status'] == '0') {
            update_post_meta($post_id, 'booking_status', 1);
            return ['code' => 1, 'msg' => 'success', 'data' => null];
        } else {
            return ['code' => -1, 'msg' => 'had changed', 'data' => null];

        }

    }


    public function bookingCreate(): array
    {


        if (!$this->user) return ['code' => -1, 'msg' => 'login invalid', 'data' => null];
        $post_name = $this->request->get_param('booking_name');
        $booking_time = $this->request->get_param('booking_time');
        if (empty($booking_time) || empty($post_name)) return ['code' => -1, 'msg' => 'params  invalid', 'data' => null];
        if (!date_create($booking_time)) return ['code' => -1, 'msg' => 'date invalid', 'data' => null];
        if (strtotime($booking_time) < time()) return ['code' => -1, 'msg' => 'Can\'t make an appointment before', 'data' => null];
        if (strlen($post_name) > 50) return ['code' => -1, 'msg' => 'params error', 'data' => null];
        $booking_course_title = explode(':', $post_name);
        if (count($booking_course_title) != 2) {
            return ['code' => -1, 'msg' => 'title format invalid', 'data' => null];
        }

        try {

            $booking_course_title = $booking_course_title[0];
            $booking_course_id = $this->request->get_param('course_id');

            $user_orders = (new WC_Order($booking_course_id))->get_items();

            if (count($user_orders) != 1) {
                return ['code' => -1, 'msg' => 'course not found', 'data' => null];
            }


            $user_orders = current($user_orders);
            $meta_data = current($user_orders->get_meta_data());
            $user_all_booking_count = (int)$meta_data->value;
            //Check the available schedule of the course


        } catch (Exception $exception) {
            return ['code' => -1, 'msg' => $exception->getMessage(), 'data' => null];
        }

        $args = array(
            'post_type' => 'macro_booking_record',
            'posts_per_page' => 10,
            'post_status' => 'publish',
            'author' => $this->user->ID,
            'meta_query' => [
                'booking_id' => $booking_course_id
            ]
        );
        $user_booking_count = (new WP_Query($args))->post_count;
        if ($user_booking_count > $user_all_booking_count+10) return ['code' => -1, 'msg' => 'The number of appointments has been used up', 'data' => null];
        $res = wp_insert_post([
            'post_author' => $this->user->ID,
            'post_title' => $post_name,
            'post_status' => 'publish',
            'post_name' => $post_name,
            'post_type' => 'macro_booking_record'

        ]);

        update_post_meta($res, 'booking_status', 0);
        update_post_meta($res, 'booking_time', $booking_time);
        update_post_meta($res, 'booking_course_id', $booking_course_id);
        update_post_meta($res, 'booking_course_title', $booking_course_title);
        return ['code' => 1, 'data' => ['booking_id' => $res, 'left' => $user_all_booking_count - $user_booking_count], 'msg' => 'SUCCESS'];
    }

    public function bookingSignUp(): array
    {

        $user_name = sanitize_user($this->request->get_body_params()['user_name'] ?? '');
        $password = trim($this->request->get_body_params()['password'] ?? '');
        $user_email = trim($this->request->get_body_params()['user_email'] ?? '');

        if (!$user_name || !$password || !$user_email) {
            return ['code' => -1, 'msg' => 'params error', 'data' => null];
        }

        if (strlen($user_name) > 20) {
            return ['code' => -1, 'msg' => 'params error', 'data' => null];
        }

        if (!is_email($user_email)) {
            return ['code' => -1, 'msg' => 'email error', 'data' => null];
        }

        $user_id = username_exists($user_name);
        if (!$user_id && !email_exists($user_email)) {
            $user_id = wp_create_user($user_name, $password, $user_email);
            return ['code' => 1, 'msg' => 'SUCCESS', 'data' => ['user_id' => $user_id]];
        } else {

            return ['code' => -1, 'msg' => 'account exist', 'data' => null];
        }

    }

    private static function getUserByCookie($cookie)
    {
        $userAuthInfo = wp_parse_auth_cookie($cookie, 'macro');
        return get_user_by('login', $userAuthInfo['username']);


    }


    public function bookingListQuery(): array
    {
        if (!$this->user) return ['code' => -1, 'msg' => 'login invalid', 'data' => null];
        if (empty($this->request->get_param('start_booking_time')) || empty($this->request->get_param('end_booking_time'))) return ['code' => -1, 'msg' => 'params booking_time  invalid', 'data' => null];
        if (!date_create($this->request->get_param('start_booking_time')) || !date_create($this->request->get_param('end_booking_time'))) return ['code' => -1, 'msg' => 'params booking_time  invalid', 'data' => null];
        $start_booking_time = date_create($this->request->get_param('start_booking_time'));
        $end_booking_time = date_create($this->request->get_param('end_booking_time'));
        $args = array(
            'post_type' => 'macro_booking_record',
            'posts_per_page' => 10,
            'author' => $this->user->ID,
            'meta_query' => [
                'booking_time' =>
                    array(
                        array('key' => 'booking_time', 'value' => $start_booking_time->format('Y/m/d'), 'compare' => '>=', 'type' => 'DATE'),
                        array('key' => 'booking_time', 'value' => $end_booking_time->format('Y/m/d'), 'compare' => '<=', 'type' => 'DATE'),
                    )

            ]
        );

        $data = (new WP_Query($args))->posts;
        foreach ($data as $post) {
            $post->booking_status = get_post_meta($post->ID, 'booking_status', true);
            $post->booking_time = get_post_meta($post->ID, 'booking_time', true);

        }
        return ['code' => 1, 'msg' => 'SUCCESS', 'data' => $data];


    }

    public function bookingSignIn(): array
    {

        $username = sanitize_user($this->request->get_param('username'));
        $password = trim($this->request->get_param('password'));
        $user = wp_authenticate($username, $password);
        return ['code' => 1, 'msg' => 'success', 'user' => $user, 'token' => wp_generate_auth_cookie($user->ID, time() + 720000, 'macro')];

    }


    public function createOrder(): array
    {
        //默认订单数据
        $data = [
            'meta_data' => array(array(
                'key' => 'pay_status',
                'value' => '50%'
            )),

            'payment_method' => 'bacs',
            'payment_method_title' => 'Direct Bank Transfer',
            'set_paid' => true,
            'billing' => [
                'first_name' => 'testUser',
                'last_name' => 'testUser',
                'address_1' => '969 Market',
                'address_2' => '',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postcode' => '94103',
                'country' => 'US',
                'email' => 'testUser@test.com',
                'phone' => '(555) 555-5555'
            ],
            'shipping' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_1' => '969 Market',
                'address_2' => '',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postcode' => '94103',
                'country' => 'US'
            ],
            'line_items' => [

                [
                    'product_id' => 65,
                    'variation_id' => 70,
                    'quantity' => 1
                ]
            ],
            'shipping_lines' => [
                [
                    'method_id' => 'flat_rate',
                    'method_title' => 'Flat Rate',
                    'total' => '0'
                ]
            ]
        ];

        try {

            $data = wp_remote_post(self::REMOTE_URL ."/wp-json/wc/v3/orders?consumer_key=" . self::WOO_KEY . "&consumer_secret=" . self::WOO_SECRET,
                array(
                    'headers' => array('Content-Type' => 'application/json'),
                    'timeout' => 30,
                    'body' => json_encode($data),
                )
            );

        } catch (Exception $exception) {
            return ['code' => -1, 'msg' => 'SUCCESS', 'data' => $exception->getMessage()];
        }

        return ['code' => 1, 'msg' => 'SUCCESS', 'data' => $data];

    }


}
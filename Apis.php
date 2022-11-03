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
    function __construct($request)
    {

        $this->request = $request;
        $is_login = wp_validate_auth_cookie($request->get_param('token'), 'macro');
        if ($is_login) {
            $userAuthInfo = wp_parse_auth_cookie($request->get_param('token'), 'macro');
            $this->user = get_user_by('login', $userAuthInfo['username'])->data;
        }
    }


//    public function bookingStatusUpdate(): array
//    {
//        if (!$this->user) return ['code' => -1, 'msg' => 'token invalid', 'data' => null];
//        $post_id = $this->request->get_param('post_id');
//        $user_id = self::getUserByCookie($this->request->get_param('token'))->ID;
//        if (!$post_id) {
//            return ['code' => -1, 'msg' => 'params error', 'data' => null];
//        }
//        $args = array(
//            'post_type' => 'macro_booking_record',
//            'posts_per_page' => 10,
//            'p' => $post_id
//        );
//        $data = (new WP_Query($args))->posts;
//        if (count($data) != 1) {
//            return ['code' => -1, 'msg' => 'content not found', 'data' => null];
//        }
//        if ($data[0]->post_author != $user_id) {
//            return ['code' => -1, 'msg' => 'not permission', 'data' => null];
//        }
//        if (get_post_meta($post_id, 'booking_status', true)['booking_status'] == '0') {
//            update_post_meta($post_id, 'booking_status', 1);
//            return ['code' => 1, 'msg' => 'success', 'data' => null];
//        } else {
//            return ['code' => -1, 'msg' => 'had changed', 'data' => null];
//
//        }
//
//    }


    /**
     * 创建订阅订单
     * @return array
     */
    public function PayRecordCreate(): array
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


    //用户注册
    public function signUp(): array
    {

        $user_name = sanitize_user($this->request->get_json_params()['user_name'] ?? '');
        $password = trim($this->request->get_json_params()['password'] ?? '');
        $user_email = trim($this->request->get_json_params()['user_email'] ?? '');

        if (!$user_name || !$password || !$user_email) {
            return ['code' => -1, 'msg' => '用户名或者密码不完整', 'data' => null];
        }

        if (strlen($user_name) > 80) {
            return ['code' => -1, 'msg' => '用户名过长', 'data' => null];
        }

        if (!is_email($user_email)) {
            return ['code' => -1, 'msg' => '邮箱格式有误', 'data' => null];
        }

        $user_id = username_exists($user_name);
        if (!$user_id && !email_exists($user_email)) {
            $user_id = wp_create_user($user_name, $password, $user_email);
            return ['code' => 200, 'msg' => '注册成功', 'data' => ['user_id' => $user_id]];
        } else {

            return ['code' => -1, 'msg' => '账号存在', 'data' => null];
        }

    }

    private static function getUserByCookie($cookie)
    {
        $userAuthInfo = wp_parse_auth_cookie($cookie, 'macro');
        return get_user_by('login', $userAuthInfo['username']);


    }


   //用户登录
    public function signIn(): array
    {

       $username = sanitize_user($this->request->get_json_params()['username']);
        $password = trim($this->request->get_json_params()['password']);
         if (!$username || !$password) {
           return ['code' => -1, 'msg' => '用户名或者密码不完整', 'data' =>''];

          }

        $user = wp_authenticate($username, $password);

       if(is_wp_error($user)){

		     return ['code' => -1, 'msg' => $user->get_error_message(), 'data' =>$_GET];
	   }
        return ['code' => 200, 'msg' => 'success', 'data' => ['user'=>$user,'token'=>wp_generate_auth_cookie($user->ID, time() + 72000, 'sunflwoer')] ];

    }




}
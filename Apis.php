<?php
require_once 'Hooks.php';
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

    public function bookingCreate(): array
    {

        if (!$this->user) return ['code' => -1, 'msg' => '登录失效', 'data' => null];

        $post_name = $this->request->get_param('booking_name');
        if(!$post_name) return ['code' => -1, 'msg' => '预约信息缺失', 'data' => null];
        if(strlen($post_name) > 50) return ['code' => -1, 'msg' => '名称过长', 'data' => null];
        $booking_course_id = $this->request->get_param('course_id');
        //TODO 课程id获取课程信息

        //TODO 查询用户是否有购买课程

        //TODO 检查是否超出课时

        //TODO 查询日期是否过去

        $res = wp_insert_post([
            'post_author' => $this->user->ID,
            'post_title'=>$post_name,
            'post_status'=>'publish',
            'post_name'=>$post_name,
            'post_type'=>'macro_booking_record',
            'meta_input'=>[['booking_time'=>'2022'],['booking_status'=>0]]

        ]);
        update_post_meta($res, 'booking_status', 0);
        update_post_meta($res, 'booking_time', '2022/09/02 18:00:00');


        return ['code' => 1, 'data' =>['booking_id'=>$res],'msg'=>'SUCCESS'];
    }

    public function bookingSignUp(): array
    {

        $user_name = sanitize_user($this->request->get_body_params()['user_name'] ?? '');
        $password = trim($this->request->get_body_params()['password'] ?? '');
        $user_email = trim($this->request->get_body_params()['user_email'] ?? '');

        if (!$user_name || !$password || !$user_email) {
            return ['code' => -1, 'msg' => '参数不可以为空！', 'data' => null];
        }

        if (strlen($user_name) > 20) {
            return ['code' => -1, 'msg' => '名称过长！', 'data' => null];
        }

        if (!is_email($user_email)) {
            return ['code' => -1, 'msg' => '邮箱格式有误！', 'data' => null];
        }

        $user_id = username_exists($user_name);
        if (!$user_id && !email_exists($user_email)) {
            $user_id = wp_create_user($user_name, $password, $user_email);
            return ['code' => 1, 'msg' => 'SUCCESS', 'data' => ['user_id' => $user_id]];
        } else {

            return ['code' => -1, 'msg' => '注册失败,账号已存在', 'data' => null];
        }

    }

    private static function getUserByCookie($cookie)
    {
        $userAuthInfo = wp_parse_auth_cookie($cookie, 'macro');
        return get_user_by('login', $userAuthInfo['username']);


    }


    public function bookingListQuery(): array
    {
        $cookie = $this->request->get_param('token');

        $user = self::getUserByCookie($cookie)->data;
        $args = array(
            'post_type' => 'macro_booking_record',
            'posts_per_page' => 10,
            'author' => $user->ID
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


}
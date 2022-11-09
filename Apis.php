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
        $is_login = wp_validate_auth_cookie($request->get_param('token'), 'sunflower');
        if ($is_login) {
            $userAuthInfo = wp_parse_auth_cookie($request->get_param('token'), 'sunflower');
            $this->user = get_user_by('login', $userAuthInfo['username'])->data;
        }
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
            return ['code' => -1, 'msg' => '用户名或者密码不完整', 'data' => ''];

        }

        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {

            return ['code' => -1, 'msg' => $user->get_error_message(), 'data' => $_GET];
        }
        return ['code' => 200, 'msg' => 'success', 'data' => ['user' => $user, 'token' => wp_generate_auth_cookie($user->ID, time() + 72000, 'sunflower')]];

    }

    //添加用户收藏
    public function addUserLike(): array
    {






        if (!$this->user) return ['code' => -1, 'msg' => '登录失效，请重新登录', 'data' => null];
        if($_SERVER['REQUEST_METHOD'] === 'GET') {

            return ['code'=>200,'msg'=>'success','data'=> get_user_meta($this->user->ID,'likes')];

        }
        $params = $this->request->get_json_params();
        if (empty($params) || empty($params['id']) || empty($params['title'])) return ['code' => -1, 'msg' => '参数有误', 'data' => null];
        $user_likes = get_user_meta($this->user->ID,'likes');
        $item =  ['id' => $params['id'], 'title' => $params['title']];
        $data = ['code' => 200];

        if (empty($user_likes)) {
            add_user_meta($this->user->ID, 'likes',$item);
            $data['msg'] = '收藏完成';
        }else{
            $isNew= false;
            $exist_index = -1;
            foreach ($user_likes as $k=> $user_like) {
                if($user_like['id'] === $item['id']){
                    $exist_index = $k;
                    break;
                }
            }
            if($exist_index !== -1){
                $data['code'] = -1;
                $data['msg'] = '取消完成!';
                delete_user_meta($this->user->ID,'likes',$item);

            }else{
                $data['msg'] = '收藏完成!';
                !$isNew && add_user_meta($this->user->ID, 'likes',$item);

            }
        }

        return $data;



    }





    public function addPosts(): array
    {
        if (!$this->user) return ['code' => -1, 'msg' => '登录失效，请重新登录', 'data' => null];

        if($_SERVER['REQUEST_METHOD'] === 'GET') {


            return ['code'=>200,'msg'=>'success','data'=>get_posts(array('post_author'=>$this->user->ID,'post_status'=>'private'))];

        }

        if($_SERVER['REQUEST_METHOD'] === 'DELETE') {

            //return ['data'=>get_posts(array('p'=>2626,'post_status'=>'private','post_author'=>5))];

            $params = $this->request->get_params();
            //查询该文章
            $posts_id = $params['id'];
            if(!isset($params['id'])){

                return ['code' => -1, 'msg' => '参数有误', 'data' => null];
            }

            $ps = get_posts(array('p'=>$posts_id,'post_status'=>'private','post_author'=>$this->user->ID));



            if(is_wp_error($ps)){
                return ['code' => -1, 'msg' => '删除失败', 'data' => null];
            }
            if(count($ps) !== 1){
                return ['code' => -1, 'msg' => '删除失败：内容不存在！'];
            }





            if($ps[0]->post_author !== $this->user->ID){
                return ['code' => -1, 'msg' => '删除失败：无权限！', 'data' => null];
            }
            $r = wp_delete_post($posts_id,true);
            return ['code'=>200,'msg'=>'删除成功','data'=>$r];

        }



        //用户超过50篇禁止保存
        if( count_user_posts($this->user->ID) >50){
            return ['code' => -1, 'msg' => '最多备份50篇！', 'data' => null];
        }

        $params = $this->request->get_json_params();
        $my_post = array(
            'post_title'    => wp_strip_all_tags( $params['title'] ),
            'post_content'  => $params['content'],
            'post_status'   => 'private',
            'post_author'   => $this->user->ID
        );
        $res = wp_insert_post( $my_post );
        if(is_wp_error($res)){
            return ['code'=>-1,'msg'=>$res->get_error_message()];
        }
        return ['code' =>200,'msg'=>'保存成功'];
    }


}
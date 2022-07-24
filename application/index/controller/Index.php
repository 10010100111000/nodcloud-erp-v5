<?php
namespace app\index\controller;
use think\Controller;
use	app\index\model\user;
class Index extends Controller{
    //登录
    public function index(){
        if(checklogin()){
            header("Location: http://".$_SERVER['HTTP_HOST']."/index/main"); 
            exit;
        }else{
            return $this->fetch();
        }
    }
    //验证用户账号密码
    public function check_user(){
        $info=input('post.');
        $sql['user']=$info['user'];
        $sql['pwd']=md5($info['pwd']);
        $user = user::get($sql);
        if($user){
            $token=user_token();
            $user->token=$token;
            $user->save();
            //设置登录
            cookie('Ape_User_Id',$user['id'],604800);
            cookie('Ape_User_Token',$token,604800);
            Session('is_user_id',$user['id']);
            //日志
            push_log('登录系统成功');
            return json('success');
        }else{
            return json('error');
        }
    }
}

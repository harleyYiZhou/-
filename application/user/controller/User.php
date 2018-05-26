<?php
/**
 * User: cboy
 * Date: 2018/5/8
 * Time: 19:34
 */

namespace app\user\controller;

use think\Db;
use think\Validate;
use Utils\HttpUtil;

class User
{

    /**
     * 用户登录
     * @param string $phone
     * @param string $code
     */
    public function login($phone='',$code=''){

        if($phone=='' || $code==''){
            return json(['code'=>201,'msg'=>'参数不能为空']);
        }

        $record = Db::name('send_checkcode')
            ->where('phone',$phone)
            ->order('id desc')
            ->find();
        if($record==null){
            return json(['code'=>202,'msg'=>'手机号不正确']);
        }
        if($record['is_valid']==1){
            return json(['code'=>204,'msg'=>'验证码已过期']);
        }
        if($code != $record['checkcode']){
            Db::name('send_checkcode')->where('id',$record['id'])->setField('is_valid',1);
            return json(['code'=>203,'msg'=>'验证码不正确']);
        }
        $time = time()-strtotime($record['create_time']);
        if($time>300){
            return json(['code'=>204,'msg'=>'验证码已过期']);
        }

        $user = Db::name('user')->where('telphone',$phone)->find();
        if($user==null){
            $id = Db::name('user')->insertGetId(['telphone'=>$phone]);
            session('user_id',$id);
        }else{
            if($user['ustatus']==1){
                return json(['code'=>205,'msg'=>'用户已被禁用']);
            }
            session('user_id',$user['uid']);
        }
        return json(['code'=>200,'msg'=>'ok']);
    }


    /**
     * 发送短信验证码
     * @return mixed
     */
    public function sendCheckCode($phone=''){
        $pattern = '/^1[34578]{1}\d{9}$/';

        if(!preg_match($pattern,$phone)){
            return json(['code'=>201,'msg'=>'手机号码不正确']);
        }

        $appkey = config('jisu.appkey');
        $code = rand(100000,999999);

        Db::name('send_checkcode')->insert(['phone'=>$phone,'checkcode'=>$code]);
        return json(['code'=>200,'msg'=>'ok','code'=>$code]);
        $content = config('jisu.content');
        $content = str_replace('@',$code,$content);
        
        $url = 'http://api.jisuapi.com/sms/send?appkey='.$appkey.'&mobile='.$phone.'&content='.$content;

        $res = HttpUtil::http_request($url);
        
        $json_res = json_decode($res,true);
    
        if($json_res['status']==0){
            Db::name('send_checkcode')->insert(['phone'=>$phone,'checkcode'=>$code]);
            return json(['code'=>200,'msg'=>'ok']);
        }else{
            return json(['code'=>202,'msg'=>'发送失败']);
        }

    }

    /**
     * 获取用户信息
     */
    public function getUserInfo(){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'请先登录']);

        $user = Db::name('user')->where('uid',$userId)->find();
        return json(['code'=>200,'msg'=>'ok','user'=>$user]);
    }

    /**
     * 修改用户昵称
     * @return mixed
     */
    public function editUserNickname(){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'name'=>'require|max:32',
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }

        Db::name('user')->where('uid',$userId)->setField('nickname',$data['name']);

        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 管理员获取/搜索用户列表
     * @param int $pn
     * @param int $size
     * @param string $key
     */
    public function getUserList($pn=1,$size=10,$key=''){
        if(!is_numeric($pn) || $pn<1) $pn=1;
        if(!is_numeric($size) || $size<1) $size=10;

        if($key==''){
            $user = Db::name('user')->page($pn,$size)->order('uid desc')->select();
            $count = Db::name('user')->count();
        }else{
            $user = Db::name('user')
                ->where('telphone','like','%'.$key.'%')
                ->page($pn,$size)->order('uid desc')->select();
            $count = Db::name('user')
                ->where('telphone','like','%'.$key.'%')
                ->count();
        }
        $data = ['code'=>200,'msg'=>'ok','data'=>['user'=>$user,'count'=>$count,'pn'=>$pn,'size'=>$size]];
        return json($data);
    }

    /**
     * 管理员新增用户
     * @return mixed
     */
    public function addUser(){
        $validate = new Validate([
            'name'=>'require|max:32',
            'telphone'=>['require','regex'=>'/^[1][3,4,5,7,8][0-9]{9}$/'],
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        $user = Db::name('user')->where('telphone',$data['telphone'])->find();
        if($user!=null) return json(['code'=>202,'msg'=>'该手机号已被注册']);
        Db::name('user')->insert(['telphone'=>$data['telphone'],'nickname'=>$data['name']]);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 管理员修改用户
     * @return mixed
     */
    public function updateUser(){
        $validate = new Validate([
            'uid'=>'require|number',
            'name'=>'require|max:32',
            'telphone'=>['require','regex'=>'/^[1][3,4,5,7,8][0-9]{9}$/'],
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        $user = Db::name('user')->where('telphone',$data['telphone'])->find();
        if($user!=null && $user['uid'] != $data['uid'])
            return json(['code'=>202,'msg'=>'该手机号已被注册']);

        Db::name('user')
            ->where('uid',$data['uid'])
            ->update(['telphone'=>$data['telphone'],'nickname'=>$data['name']]);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 管理员禁用用户
     * @param null $uid
     */
    public function disableUser($uid=null){
        if($uid==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        Db::name('user')->where('uid',$uid)->setField('ustatus',1);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 管理员解除用户禁用状态
     * @param null $uid
     * @return mixed
     */
    public function enableUser($uid=null){
        if($uid==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        Db::name('user')->where('uid',$uid)->setField('ustatus',0);
        return json(['code'=>200,'msg'=>'ok']);
    }


    /**
     * 用户注销
     */
    public function logout(){
        session('user_id',null);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 管理员通过id获取用户信息（用于修改回显）
     * @param null $uid
     */
    public function getUserById($uid=null){
        if($uid==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        $user = Db::name('user')->where('uid',$uid)->where('ustatus',0)->find();
        return json(['code'=>200,'msg'=>'ok','user'=>$user]);
    }


}
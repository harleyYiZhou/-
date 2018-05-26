<?php
namespace app\admin\controller;

use think\Db;
use think\Validate;

class Admin
{
    /**
     * 管理员登录
     * @return mixed
     */
    public function login(){
        $validate = new Validate([
            'admin_name'=>'require|length:1,32',
            'admin_pwd'=>'require|length:32',//md5加密后传送
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }

        $admin = Db::name('admin')->where('admin_name',$data['admin_name'])->find();
        if($admin==null){
            return json(['code'=>202,'msg'=>'账号或密码不正确']);
        }
        if($admin['admin_status']==1){
            return json(['code'=>203,'msg'=>'账户已被禁用']);
        }

        if($admin['admin_pwd']!=md5($data['admin_pwd'])){
            return json(['code'=>202,'msg'=>'账号或密码不正确']);
        }

        session('admin_id',$admin['admin_id']);
        return json(['code'=>200,'msg'=>'ok']);
        
    }

    /**
     * 管理员注销登录
     * @return mixed
     */
    public function logout(){
        session('admin_id',null);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 获取/搜索管理员列表
     * @param int $pn
     * @param int $size
     * @param string $key
     */
    public function getAdminList($pn=1,$size=10,$key=''){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if(!is_numeric($pn) || $pn<1) $pn=1;
        if(!is_numeric($size) || $size<1) $size=10;

        if($key==''){
            $admin = Db::name('admin')
                ->field('admin_id,admin_name,last_login_ip,last_login_time,login_count,admin_status')
                ->order('admin_id desc')
                ->page($pn,$size)
                ->select();
            $count = Db::name('admin')->count();
        }else{
            $admin = Db::name('admin')
                ->field('admin_id,admin_name,last_login_ip,last_login_time,login_count,admin_status')
                ->where('admin_name','like','%'.$key.'%')
                ->order('admin_id desc')
                ->page($pn,$size)
                ->select();
            $count = Db::name('admin')
                ->count();
        }
        $data = ['code'=>200,'msg'=>'ok','data'=>[
            'admin'=>$admin,'count'=>$count,'pn'=>$pn,'size'=>$size]
        ];
        return json($data);
    }

    /**
     * 禁用admin
     * @param null $id
     */
    public function disableAdmin($id=null){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($id==null) return json(['code'=>202,'msg'=>'参数不能为空']);

        Db::name('admin')->where('admin_id',$id)->setField('admin_status',1);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 解除admin禁用状态
     * @param null $id
     * @return mixed
     */
    public function enableAdmin($id=null){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($id==null) return json(['code'=>202,'msg'=>'参数不能为空']);

        Db::name('admin')->where('admin_id',$id)->setField('admin_status',0);
        return json(['code'=>200,'msg'=>'ok']);
    }


    /**
     * 添加管理员
     * @return mixed
     */
    public function addAdmin(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'admin_name'=>'require|length:1,32',
            'admin_pwd'=>'require|length:32|confirm',//admin_pwd_confirm
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        //判断admin_name是否已被使用
        $admin = Db::name('admin')
            ->where('admin_name',$data['admin_name'])
            ->where('admin_status',0)
            ->find();
        if($admin != null){
            return json(['code'=>202,'msg'=>'名称已被使用']);
        }

        $dbData = [
            'admin_name'=>$data['admin_name'],
            'admin_pwd'=>md5($data['admin_pwd']),
            'last_login_time'=>date('Y-m-d H:i:s')
        ];
        Db::name('admin')->insert($dbData);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 修改管理员密码
     * @return mixed
     */
    public function editAdminPwd(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'admin_id'=>'require|number',
            'working_pwd'=>'require|length:32',//修改时的管理员密码
            'new_pwd'=>'require|length:32|confirm',//new_pwd_confirm
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }

        Db::name('admin')
            ->where('admin_id',$data['admin_id'])
            ->setField('admin_pwd',md5($data['new_pwd']));

        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 管理员修改自身密码
     * @return mixed
     */
    public function adminEditPwd(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'working_pwd'=>'require|length:32',//修改时的管理员密码
            'new_pwd'=>'require|length:32|confirm',//new_pwd_confirm
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }

        Db::name('admin')
            ->where('admin_id',$adminId)
            ->setField('admin_pwd',md5($data['new_pwd']));

        return json(['code'=>200,'msg'=>'ok']);
    }
    
    
}

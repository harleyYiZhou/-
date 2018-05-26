<?php
/**
 * User: cboy
 * Date: 2018/5/10
 * Time: 17:52
 */

namespace app\index\controller;


use think\Db;
use think\Validate;

class Address
{

    /**
     * 获取用户配送地址
     */
    public function getUserAddr(){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'未登录']);
        $address = Db::name('address')->where('user_id',$userId)->select();
        return json(['code'=>200,'msg'=>'ok','address'=>$address]);
    }

    /**
     * 添加用户配送地址
     * @return mixed
     */
    public function addAddr(){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'未登录']);

        $validate = new Validate([
            'address'=>'require|max:255',
            'username'=>'require|max:32',
            'telphone'=>['require','regex'=>'/^[1][3,4,5,7,8][0-9]{9}$/'],
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        $dbData = ['user_id'=>$userId,'address'=>$data['address'],
            'username'=>$data['username'],'telphone'=>$data['telphone']
        ];
        Db::name('address')->insert($dbData);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 获取用户地址信息(用于回显)
     * @param null $id
     * @return mixed
     */
    public function getAddrInfo($id=null){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'未登录']);
        if($id==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        $address = Db::name('address')->where('id',$id)->where('user_id',$userId)->find();
        return json(['code'=>200,'msg'=>'ok','addr'=>$address]);

    }

    /**
     * 更新用户地址
     * @return mixed
     */
    public function updateAddr(){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'未登录']);

        $validate = new Validate([
            'id'=>'require|number',
            'address'=>'require|max:255',
            'username'=>'require|max:32',
            'telphone'=>['require','regex'=>'/^[1][3,4,5,7,8][0-9]{9}$/'],
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        $dbData = ['user_id'=>$userId,'address'=>$data['address'],
            'username'=>$data['username'],'telphone'=>$data['telphone']
        ];
        Db::name('address')->where('id',$data['id'])->update($dbData);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 删除用户地址
     * @param null $id
     */
    public function deleteAddr($id=null){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'未登录']);
        if($id==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        Db::name('address')->where('id',$id)->where('user_id',$userId)->delete();
        return json(['code'=>200,'msg'=>'ok']);
    }
}
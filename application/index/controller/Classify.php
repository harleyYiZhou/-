<?php
/**
 * User: cboy
 * Date: 2018/5/9
 * Time: 15:11
 */

namespace app\index\controller;


use think\Db;
use think\Validate;

class Classify
{

    /**
     * 获取所有分类
     * @return mixed
     */
    public function getAllList(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $classify = Db::name('classify')->where('cstatus',0)->select();
        return json(['code'=>200,'msg'=>'ok','classify'=>$classify]);
    }

    /**
     * 添加分类
     * @return mixed
     */
    public function addClassify(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'name'=>'require|max:64',
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }

        $classify = Db::name('classify')
            ->where('cname',$data['name'])
            ->where('cstatus',0)
            ->find();
        if($classify!=null){
            return json(['code'=>202,'msg'=>'名称已被使用']);
        }
        Db::name('classify')->insert(['cname'=>$data['name']]);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 修改分类
     * @return mixed
     */
    public function updateClassify(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'id'=>'require|number',
            'name'=>'require|max:64',
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }

        $classify = Db::name('classify')
            ->where('cname',$data['name'])
            ->where('cstatus',0)
            ->find();
        if($classify!=null && $classify['cid'] != $data['id']){
            return json(['code'=>202,'msg'=>'名称已被使用']);
        }
        Db::name('classify')->update(['cid'=>$data['id'],'cname'=>$data['name']]);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 删除分类
     * @param null $id
     */
    public function deleteClassify($id=null){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        Db::name('classify')->where('cid',$id)->setField('cstatus',1);
        return json(['code'=>200,'msg'=>'ok']);
    }
}
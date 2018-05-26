<?php
/**
 * User: cboy
 * Date: 2018/5/9
 * Time: 14:36
 */

namespace app\index\controller;


use think\Db;
use think\Validate;

class Notice
{

    /**
     * 获取/搜索公告列表
     * @param int $pn
     * @param int $size
     * @param string $start_time
     * @param string $end_time
     * @return mixed
     */
    public function getList($pn=1,$size=10,$start_time='',$end_time=''){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if(!is_numeric($pn) || $pn<1) $pn=1;
        if(!is_numeric($size) || $size<1) $size=10;

        if($start_time=='' || $end_time==''){
            $notice = Db::name('notice')->page($pn,$size)->order('id desc')->select();
            $count = Db::name('notice')->count();
        }else{
            $notice = Db::name('notice')
                ->where('start_time','>= time',$start_time)
                ->where('end_time','<= time',$end_time)
                ->page($pn,$size)->order('id desc')->select();
            $count = Db::name('notice')
                ->where('start_time','>= time',$start_time)
                ->where('end_time','<= time',$end_time)
                ->count();
        }

        $data = ['code'=>200,'msg'=>'ok','data'=>[
            'notice'=>$notice,'count'=>$count,'pn'=>$pn,'size'=>$size]
        ];
        return json($data);
    }

    /**
     * 首页显示公告
     */
    public function getNotice(){
        $notice = Db::name('notice')
            ->where('start_time','<= time',date('Y-m-d H:i:s'))
            ->where('end_time','>= time',date('Y-m-d H:i:s'))
            ->select();
        return json(['code'=>200,'msg'=>'ok','notice'=>$notice]);
    }

    /**
     * 根据id获取公告信息（回显）
     */
    public function getNoticeById($id=null){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($id==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        $notice = Db::name('notice')->where('id',$id)->find();
        return json(['code'=>200,'msg'=>'ok','notice'=>$notice]);
    }

    /**
     * 添加公告
     */
    public function addNotice(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'content'=>'require|max:255',
            'start_time'=>'require|dateFormat:Y-m-d H:i:s',
            'end_time'=>'require|dateFormat:Y-m-d H:i:s',
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        if(strtotime($data['start_time'])>=strtotime($data['end_time'])){
            return json(['code'=>202,'msg'=>'结束时间不能小于等于开始时间']);
        }
        $dbData = ['content'=>$data['content'],'start_time'=>$data['start_time'],'end_time'=>$data['end_time']];
        Db::name('notice')->insert($dbData);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 更新公告
     */
    public function updateNotice(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'id'=>'require|number',
            'content'=>'require|max:255',
            'start_time'=>'require|dateFormat:Y-m-d H:i:s',
            'end_time'=>'require|dateFormat:Y-m-d H:i:s',
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        if(strtotime($data['start_time'])>=strtotime($data['end_time'])){
            return json(['code'=>202,'msg'=>'结束时间不能小于等于开始时间']);
        }
        $dbData = ['content'=>$data['content'],'start_time'=>$data['start_time'],'end_time'=>$data['end_time']];
        Db::name('notice')->where('id',$data['id'])->update($dbData);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 删除公告
     * @param null $id
     */
    public function deleteNotice($id=null){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($id==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        Db::name('notice')->where('id',$id)->delete();
        return json(['code'=>200,'msg'=>'ok']);
    }


}
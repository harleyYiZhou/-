<?php
/**
 * User: cboy
 * Date: 2018/5/9
 * Time: 15:29
 */

namespace app\index\controller;


use app\common\MyException;
use think\Db;
use think\Log;
use think\Validate;

class Goods
{
    /**
     * 获取/搜索商品列表
     * @param int $pn
     * @param int $size
     * @param string $key 商品名称
     */
    public function getList($pn=1,$size=10,$key=''){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if(!is_numeric($pn) || $pn<1) $pn=1;
        if(!is_numeric($size) || $size<1) $size=10;

        if($key==''){
            $goods = Db::name('goods')
                ->where('goods_status',0)
                ->page($pn,$size)
                ->order('goods_id desc')
                ->select();
            $count = Db::name('goods')->count();
        }else{
            $goods = Db::name('goods')
                ->where('goods_name','like','%'.$key.'%')
                ->where('goods_status',0)
                ->page($pn,$size)
                ->order('goods_id desc')
                ->select();
            $count = Db::name('goods')
                ->where('goods_name','like','%'.$key.'%')
                ->where('goods_status',0)
                ->count();
        }

        $data = ['code'=>200,'msg'=>'ok','data'=>[
            'goods'=>$goods,'count'=>$count,'pn'=>$pn,'size'=>$size]
        ];
        return json($data);
    }

    /**
     * 删除商品
     * @param null $id
     */
    public function deleteGoods($id=null){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($id==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        Db::name('goods')->where('goods_id',$id)->setField('goods_status',1);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 添加商品
     */
    public function add(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'name'=>'require|length:1,64',
            'img_ids'=>'require',
            'desc'=>'require|max:255',
            'price'=>'require|float',
            'classify_id'=>'require|number',
            'detail_ids'=>'require',
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }

        $arr = explode(',',$data['img_ids']);
        $arr2 = explode(',',$data['detail_ids']);
        if($arr==null || $arr2==null){
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        //判断是否存在分类
        $classify = Db::name('classify')
            ->where('cid',$data['classify_id'])
            ->where('cstatus',0)
            ->find();
        if($classify==null){
            return json(['code'=>202,'msg'=>'分类不存在']);
        }

        Db::startTrans();
        try{
            $dbData = [
                'goods_name'=>$data['name'],'classify_id'=>$data['classify_id'],
                'goods_price'=>$data['price'],'goods_desc'=>$data['desc'],
            ];
            //插入商品，并返回id
            $id = Db::name('goods')->insertGetId($dbData);
            //更改商品图片所属
            $res = Db::name('goods_image')
                ->where('id','in',$arr)
                ->where('img_type',0)
                ->update(['goods_id'=>$id]);
            if($res==0) throw new MyException(203,'商品图片至少需要一张');
            $res1 = Db::name('goods_image')
                ->where('id','in',$arr2)
                ->where('img_type',1)
                ->update(['goods_id'=>$id]);
            if($res1==0) throw new MyException(204,'商品详情图片还少需要一张');

            //提交事务
            Db::commit();
            return json(['code'=>200,'msg'=>'ok']);
        }catch (\Exception $e){
            //回滚事务
            Db::rollback();
            return json(['code'=>$e->getCode(),'msg'=>'添加失败,'.$e->getMessage()]);
        }
    }

    /**
     * 修改商品
     */
    public function update(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'id'=>'require|number',
            'name'=>'require|length:1,64',
            'del_ids'=>'require',
            'add_ids'=>'require',
            'desc'=>'require|max:255',
            'price'=>'require|float',
            'classify_id'=>'require|number',
            'det_del_ids'=>'require',
            'det_add_ids'=>'require',
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        //判断是否存在分类
        $classify = Db::name('classify')
            ->where('cid',$data['classify_id'])
            ->where('cstatus',0)
            ->find();
        if($classify==null){
            return json(['code'=>202,'msg'=>'分类不存在']);
        }

        Db::startTrans();
        try{
            $dbData = [
                'goods_name'=>$data['name'],'classify_id'=>$data['classify_id'],
                'goods_price'=>$data['price'],'goods_desc'=>$data['desc'],
            ];
            //更新商品
            Db::name('goods')->where('goods_id',$data['id'])->update($dbData);

            $flag = false;
            $delIds = explode(',',$data['del_ids']);
            if($delIds != null){
                //获取商品所属图片id
                $imgIds = Db::name('goods_image')
                    ->where('goods_id',$data['id'])
                    ->where('img_type',0)
                    ->column('id');
                foreach ($imgIds as $id){
                    if(!in_array($id,$delIds)){
                        $flag=true;
                    }
                }
            }else{
                $flag = true;
            }
            if($delIds!=null){
                //删除商品图片
                if($data['del_ids']!=null){
                    $res1 = Db::name('goods_image')
                        ->where('id','in',$delIds)
                        ->where('img_type',0)
                        ->delete();
                    if($res1==0) $flag=true;
                }
            }

            $addIds = explode(',',$data['add_ids']);
            //添加商品图片
            if($addIds!=null){
                $res2 = Db::name('goods_image')
                    ->where('id','in',$addIds)
                    ->where('img_type',0)
                    ->update(['goods_id'=>$data['id']]);
                if($res2>0) $flag=true;
            }

            $flag2 = false;

            $detDelIds = explode(',',$data['det_del_ids']);

            if($detDelIds != null){
                //获取商品所属图片id
                $imgIds = Db::name('goods_image')
                    ->where('img_type',1)
                    ->where('goods_id',$data['id'])->column('id');
                foreach ($imgIds as $id){
                    if(!in_array($id,$detDelIds)){
                        $flag2=true;
                    }
                }
            }else{
                $flag2=true;
            }
            //删除商品详情图片
            if($detDelIds!=null){
                $res3 = Db::name('goods_image')
                    ->where('id','in',$detDelIds)
                    ->where('img_type',1)
                    ->delete();
                if($res3==0) $flag2=true;
            }

            $delAddIds = explode(',',$data['det_add_ids']);
            //添加商品详情图片
            if($delAddIds!=null){
                $res4 = Db::name('goods_image')
                    ->where('id','in',$delAddIds)
                    ->where('img_type',1)
                    ->update(['goods_id'=>$data['id']]);
                if($res4>0) $flag2=true;
            }

            if($flag==true && $flag2==true){
                //提交事务
                Db::commit();
                return json(['code'=>200,'msg'=>'ok']);
            }else{
                Db::rollback();
                return json(['code'=>203,'msg'=>'商品图片与详情至少需要一张图片']);
            }

        }catch (\Exception $e){
            //回滚事务
            Db::rollback();
            return json(['code'=>204,'msg'=>'添加失败'.$e->getMessage()]);
        }
    }

    /**
     * 上传图片
     * @param int $type，图片类型，默认为0表示商品图片，其他表示详情图片
     * @return mixed
     */
    public function upload($type=0){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $file = request()->file('file');
        if($file==null){
            return json(['code'=>202,'msg'=>'上传图片不能为空!']);
        }
        $rule = array('size'=>2048000,'type'=>'image/gif,image/jpeg,image/png,image/bmp','ext'=>'gif,jpg,jpeg,png,bmp');
        if(!$file->check($rule)){
            return json(['code'=>203,'msg'=>$file->getError()]);
        }

        $info = $file->move(ROOT_PATH.'public/static/uploads/goods');
        if($info){
            $saveName = '/goods/'.$info->getSaveName();

            $id = Db::name('goods_image')->insertGetId(['url'=>$saveName,'img_type'=>$type==0?0:1]);
            return json(['code'=>200,'msg'=>'ok','id'=>$id,'url'=>$saveName]);
        }
        return json(['code'=>204,'msg'=>'上传失败！']);
    }

    /**
     * 获取商品详情
     * @param null $id 连锁店库存id
     */
    public function getGoodsDetail($id=null){
        if($id==null) return json(['code'=>201,'msg'=>'参数不能为空']);

        $info = Db::name('stock')->alias('s')
            ->field('sid,stock,g.goods_id,goods_name,goods_price,goods_desc,goods_create_time,telphone,address')
            ->join('goods g','g.goods_id=s.goods_id')
            ->join('chain_shop cs','cs.id=s.shop_id')
            ->where('goods_status',0)
            ->where('s.sid',$id)
            ->find();
        if($info==null){
            return json(['code'=>202,'msg'=>'连锁店商品不存在']);
        }

        $img = Db::name('goods_image')
            ->field('id,url')
            ->where('goods_id',$info['goods_id'])
            ->where('img_type',0)
            ->select();
        $detail = Db::name('goods_image')
            ->field('id,url')
            ->where('goods_id',$info['goods_id'])
            ->where('img_type',1)
            ->select();

        $info['img']=$img;
        $info['detail']=$detail;

        return json(['code'=>200,'msg'=>'ok','info'=>$info]);

    }

    /**
     * 获取购物车商品信息
     * @param string $ids 连锁店表id，用逗号分隔,
     */
    public function getBuyCarGoods($ids=''){
        if($ids=='') return json(['code'=>200,'msg'=>'ok','data'=>[]]);
        $arr = explode(',',$ids);
        if($arr==null) return json(['code'=>200,'msg'=>'ok','data'=>[]]);
        $ids = join(',',$arr);
        $sql = 'select sid,stock,goods_name,goods_price,url '
            .'from tx_stock s join tx_goods g on g.goods_id=s.goods_id '
            .'join (select url,goods_id from tx_goods_image where img_type=0 group by goods_id) gi '
            .'on gi.goods_id=g.goods_id where sid in ('.$ids.') and goods_status=0';
        $goods = Db::query($sql);
        return json(['code'=>200,'msg'=>'ok','goods'=>$goods]);
    }
}
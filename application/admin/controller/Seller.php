<?php
/**
 * User: cboy
 * Date: 2018/5/10
 * Time: 11:31
 */

namespace app\admin\controller;


use think\Db;
use think\Validate;

class Seller
{

    /**
     * 连锁店管理员登录
     * @param string $account
     * @param string $pwd
     * @return mixed
     */
    public function login($account='',$pwd=''){
        $seller = Db::name('chain_shop')->where('account',$account)->find();
        if($seller==null) return json(['code'=>201,'msg'=>'账号或密码不正确']);

        if($seller['csstatus']==1){
            return json(['code'=>202,'msg'=>'账号已被冻结']);
        }
        if($seller['pwd']!=md5($pwd)){
            return json(['code'=>201,'msg'=>'账号或密码不正确']);
        }
        session('mid',$seller['id']);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 连锁店管理员注销登录
     */
    public function logout(){
        session('mid',null);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 获取/搜索连锁店管理员列表
     * @param int $pn
     * @param int $size
     * @param string $key
     * @return mixed
     */
    public function getList($pn=1,$size=10,$key=''){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if(!is_numeric($pn) || $pn<1) $pn=1;
        if(!is_numeric($size) || $size<1) $size=10;

        if($key==''){
            $seller = Db::name('chain_shop')
                ->field('id,account,telphone,shop_name,province,city,address,csstatus,create_time')
                ->where('csstatus','<',2)
                ->page($pn,$size)
                ->order('id desc')
                ->select();
            $count = Db::name('chain_shop')
                ->where('csstatus','<',2)
                ->count();
        }else{
            $seller = Db::name('chain_shop')
                ->field('id,account,telphone,shop_name,province,city,address,csstatus,create_time')
                ->where('account','like','%'.$key.'%')
                ->where('csstatus','<',2)
                ->page($pn,$size)
                ->order('id desc')
                ->select();
            $count = Db::name('chain_shop')
                ->where('account','like','%'.$key.'%')
                ->where('csstatus','<',2)
                ->count();
        }
        $data = ['code'=>200,'msg'=>'ok','data'=>[
            'seller'=>$seller,'count'=>$count,'pn'=>$pn,'size'=>$size]
        ];
        return json($data);
    }

    /**
     * 添加连锁店管理员
     * @return mixed
     */
    public function add(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'account'=>'require|length:4,16',
            'pwd'=>'require|length:32|confirm',
            'shop_name'=>'require|max:32',
            'province'=>'require|max:32',
            'city'=>'require|max:32',
            'address'=>'require|max:128',
            'telphone'=>['require','regex'=>'/^[1][3,4,5,7,8][0-9]{9}$/'],
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        $shop = Db::name('chain_shop')->where('account',$data['account'])->find();
        if($shop!=null) return json(['code'=>202,'msg'=>'账号已被注册']);
        if($shop['shop_name']==$data['shop_name']) return json(['code'=>203,'msg'=>'商店名称已被注册']);

        $dbData = ['account'=>$data['account'],'pwd'=>md5($data['pwd']),
            'address'=>$data['address'],'telphone'=>$data['telphone'],'province'=>$data['province'],
            'city'=>$data['city'],'shop_name'=>$data['shop_name'],
        ];
        Db::name('chain_shop')->insert($dbData);

        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 获取连锁店地址和手机号码(用于回显)
     */
    public function getAddrAndPhone($id=null){
        if($id==null)  return json(['code'=>201,'msg'=>'参数不能为空']);
        $seller = Db::name('chain_shop')->field('id,address,telphone')->where('id',$id)->find();
        return json(['code'=>200,'msg'=>'ok','info'=>$seller]);
    }

    /**
     * 修改连锁店管理员
     * @return mixed
     */
    public function update(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'id'=>'require|number',
            'shop_name'=>'require|max:32',
            'address'=>'require|max:128',
            'province'=>$data['province'],
            'city'=>$data['city'],
            'telphone'=>['require','regex'=>'/^[1][3,4,5,7,8][0-9]{9}$/'],
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }

        $shop = Db::name('chain_shop')->where('id',$data['id'])->find();
        if($shop==null) return json(['code'=>202,'msg'=>'商店不存在']);
        if($shop['shop_name']==$data['shop_name'] && $shop['id']!=$data['id']){
            return json(['code'=>203,'msg'=>'商店名称已被注册']);
        }

        $dbData = ['address'=>$data['address'],'telphone'=>$data['telphone'],
            'province'=>$data['province'],'city'=>$data['city'],'shop_name'=>$data['shop_name'],
        ];
        Db::name('chain_shop')->where('id',$data['id'])->update($dbData);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 冻结/解禁连锁店管理员
     * @param null $id
     * @param int $type 0解禁，1冻结
     * @return mixed
     */
    public function disableSeller($id=null,$type=0){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($id==null) return json(['code'=>201,'msg'=>'参数格式不正确']);
        if(!in_array($type,[0,1])){
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        Db::name('chain_shop')
            ->where('id',$id)
            ->setField('csstatus',$type==0?0:1);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 管理员修改连锁店管理员密码
     * @return mixed
     */
    public function adminEditPwd(){
        $adminId = session('admin_id');
        if($adminId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'id'=>'require|number',
            'working_pwd'=>'require|length:32',//修改时的管理员密码
            'new_pwd'=>'require|length:32|confirm',//new_pwd_confirm
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        $adminId = session('admin_id');
        $admin = Db::name('admin')->where('admin_id',$adminId)->find();
        if($admin['admin_pwd']!=md5($data['working_pwd'])){
            return json(['code'=>202,'msg'=>'工作密码不正确']);
        }

        Db::name('chain_shop')->where('id',$data['id'])->setField('pwd',md5($data['new_pwd']));
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 连锁店管理员修改自身密码
     * @return mixed
     */
    public function editPwd(){
        $sellerId = session('mid');
        if($sellerId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'old_pwd'=>'require|length:32',//修改时的管理员密码
            'new_pwd'=>'require|length:32|confirm',//new_pwd_confirm
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }

        $seller = Db::name('chain_shop')->where('id',$sellerId)->find();

        if($seller['pwd']!=md5($data['old_pwd'])){
            return json(['code'=>202,'msg'=>'旧密码不正确']);
        }

        Db::name('chain_shop')->where('id',$sellerId)->setField('pwd',md5($data['new_pwd']));
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 获取连锁店管理员信息
     */
    public function getSellerInfo(){
        $sellerId = session('mid');
        if($sellerId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $seller = Db::name('chain_shop')
            ->field('id,account,telphone,address,csstatus,create_time')
            ->where('id',$sellerId)->find();
        return json(['code'=>200,'msg'=>'ok','seller'=>$seller]);
    }

    /**
     * 首页获取连锁店商品
     * @param int $id
     */
    public function search($pn=1,$size=8,$id=null,$classify_id=null){

        if(!is_numeric($pn) || $pn<1) $pn=1;
        if(!is_numeric($size) || $size<1) $size=8;


        if($id==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        $seller = Db::name('chain_shop')
            ->where('id',$id)
            ->where('csstatus',0)
            ->find();
        if($seller==null){
            return json(['code'=>202,'msg'=>'连锁店不存在']);
        }

        if($classify_id==null){
            $data = Db::name('stock')->alias('s')
                ->field('sid,stock,goods_name,goods_price,url')
                ->join('goods g','g.goods_id=s.goods_id')
                ->join('(SELECT url,goods_id FROM tx_goods_image WHERE img_type=0 GROUP BY goods_id) gi','gi.goods_id=g.goods_id')
                ->where('g.goods_status',0)
                ->where('s.shop_id',$id)
                ->page($pn,$size)
                ->select();
            $count = Db::name('stock')->alias('s')
                ->join('goods g','g.goods_id=s.goods_id')
                ->join('(SELECT url,goods_id FROM tx_goods_image WHERE img_type=0 GROUP BY goods_id) gi','gi.goods_id=g.goods_id')
                ->where('g.goods_status',0)
                ->where('s.shop_id',$id)
                ->count();
        }else{
            $data = Db::name('stock')->alias('s')
                ->field('sid,stock,goods_name,goods_price,url')
                ->join('goods g','g.goods_id=s.goods_id')
                ->join('(SELECT url,goods_id FROM tx_goods_image WHERE img_type=0 GROUP BY goods_id) gi','gi.goods_id=g.goods_id')
                ->where('g.goods_status',0)
                ->where('s.shop_id',$id)
                ->where('g.classify_id',$classify_id)
                ->page($pn,$size)
                ->select();
            $count = Db::name('stock')->alias('s')
                ->join('goods g','g.goods_id=s.goods_id')
                ->join('(SELECT url,goods_id FROM tx_goods_image WHERE img_type=0 GROUP BY goods_id) gi','gi.goods_id=g.goods_id')
                ->where('g.goods_status',0)
                ->where('s.shop_id',$id)
                ->where('g.classify_id',$classify_id)
                ->count();
        }
        
        return json(['code'=>200,'msg'=>'ok','data'=>[
            'data'=>$data,'count'=>$count,'pn'=>$pn,'size'=>$size,
        ]]);
    }

    /**
     * 通过城市地址获取商店
     */
    public function getShopByAddress($city=''){
        $shop = Db::name('chain_shop')->field('id,shop_name,province,city,address,telphone')
            ->where('city',$city)
            ->select();
        return json(['code'=>200,'msg'=>'ok','data'=>$shop]); 
    }

    /**
     * 修改连锁店商品库存
     * @param null $gid
     * @param int $stock
     */
    public function updateStock($gid=null,$stock=0){
        $sellerId = session('mid');
        if($sellerId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($gid==null || !is_numeric($stock) || $stock<1){
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        $goods = Db::name('goods')->where('goods_id',$gid)->where('goods_status',0)->find();
        if($goods==null) return json(['code'=>202,'msg'=>'商品不存在']);

        $goodsStock = Db::name('stock')
            ->where('goods_id',$gid)->where('shop_id',$sellerId)
            ->find();
        if($goodsStock==null){
            Db::name('stock')->insert(['goods_id'=>$gid,'shop_id'=>$sellerId,'stock'=>$stock]);
        }else{
            Db::name('stock')->where('sid',$goodsStock['sid'])->setField('stock',$stock);
        }

        return json(['code'=>200,'msg'=>'ok']);;
    }

    /**
     * 获取/搜索连锁店商品列表
     * @param int $pn
     * @param int $size
     * @param string $key
     */
    public function getGoodsStock($pn=1,$size=10,$key=''){
        $mid = session('mid');
        if($mid==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if(!is_numeric($pn) || $pn<1) $pn=1;
        if(!is_numeric($size) || $size<1) $size=10;

        if($key==''){
            $goods = Db::name('goods')->alias('g')
                ->field('g.goods_id,goods_name,goods_price,goods_desc,goods_create_time,IFNULL(stock,0) stock,url')
                ->join('tx_stock s',"(g.goods_id=s.goods_id and shop_id=$mid)",'left')
                ->join('(select url,goods_id from tx_goods_image where img_type=0 group by goods_id) gi','gi.goods_id=g.goods_id','left')
                ->where('goods_status',0)
                ->page($pn,$size)
                ->order('goods_id desc')
                ->select();
            $count = Db::name('goods')->alias('g')
                ->join('tx_stock s',"(g.goods_id=s.goods_id and shop_id=$mid)",'left')
                ->where('goods_status',0)
                ->count();
            
        }else{
            $goods = Db::name('goods')->alias('g')
                ->field('g.goods_id,goods_name,goods_price,goods_desc,goods_create_time,IFNULL(stock,0) stock,url')
                ->join('tx_stock s',"(g.goods_id=s.goods_id and shop_id=$mid)",'left')
                ->join('(select url,goods_id from tx_goods_image where img_type=0 group by goods_id) gi','gi.goods_id=g.goods_id','left')
                ->where('goods_name','like','%'.$key.'%')
                ->where('goods_status',0)
                ->page($pn,$size)
                ->order('goods_id desc')
                ->select();
            $count = Db::name('goods')->alias('g')
                ->join('tx_stock s',"(g.goods_id=s.goods_id and shop_id=$mid)",'left')
                ->where('goods_name','like','%'.$key.'%')
                ->where('goods_status',0)
                ->count();
           
        }
        $data = ['code'=>200,'msg'=>'ok','data'=>[
            'goods'=>$goods,'count'=>$count,'pn'=>$pn,'size'=>$size]
        ];
        return json($data);

    }
}
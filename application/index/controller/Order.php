<?php
/**
 * User: cboy
 * Date: 2018/5/10
 * Time: 17:52
 */

namespace app\index\controller;


use alipay\NotifyCallBack;
use alipay\Pay;
use app\common\MyException;
use Payment\Client\Notify;
use Payment\Common\PayException;
use Payment\Config;
use think\Db;
use think\Log;
use think\Validate;

class Order
{
    /**
     * 获取用户订单
     */
    public function getUserOrder($type=-1){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($type==0){//未付款
            $orders = Db::name('order')
            ->where('user_id',$userId)
            ->where('pay_status',0)
            ->select();
        }else if($type==1){//未发货
            $orders = Db::name('order')
            ->where('user_id',$userId)
            ->where('order_status','<',2)
            ->select();
        }else if($type==2){//未收货
            $orders = Db::name('order')
            ->where('user_id',$userId)
            ->where('order_status',2)
            ->select();
        }else if($type==3){//已完成
            $orders = Db::name('order')
            ->where('user_id',$userId)
            ->where('order_status',3)
            ->select();
        }else if($type==4){//已评价
            $orders = Db::name('order')
            ->where('user_id',$userId)
            ->where('order_status',6)
            ->select();
        }else{//所有
            $orders = Db::name('order')
            ->where('user_id',$userId)
            ->select();
        }
        
        return json(['code'=>200,'msg'=>'ok','orders'=>$orders]);
    }

    /**
     * 连锁店管理员获取/搜索已支付订单列表
     * @param int $pn
     * @param int $size
     * @param string $key
     * @return mixed
     */
    public function getOrderList($pn=1,$size=10,$key='',$type=-1){
        $mid = session('mid');
        if($mid==null) return json(['code'=>-1,'msg'=>'未登录']);
        $sid = Db::name('stock')->where('shop_id',$mid)->column('sid');
        if(!is_numeric($pn) || $pn<1) $pn=1;
        if(!is_numeric($size) || $size<1) $size=10;
        if(!in_array($type,[0,1,2,3,4,5,6])){
            if($key==''){
                $orders = Db::name('order')
                ->where('mid','in',$sid)
                ->where('pay_status',1)
                ->page($pn,$size)
                ->order('oid desc')
                ->select();
            $count = Db::name('order')
                ->where('mid','in',$sid)
                ->where('pay_status',1)
                ->count();
            return json(['code'=>200,'msg'=>'ok','data'=>[
                    'orders'=>$orders,'pn'=>$pn,'size'=>$size,'count'=>$count
                    ]
                ]);
            }else{
                $orders = Db::name('order')
                ->where('mid','in',$sid)
                ->where('trade_no','like','%'.$key.'%')
                ->where('pay_status',1)
                ->page($pn,$size)
                ->order('oid desc')
                ->select();
            $count = Db::name('order')
                ->where('mid','in',$sid)
                ->where('trade_no','like','%'.$key.'%')
                ->where('pay_status',1)
                ->count();
            return json(['code'=>200,'msg'=>'ok','data'=>[
                    'orders'=>$orders,'pn'=>$pn,'size'=>$size,'count'=>$count
                    ]
                ]);
            }
        }else{
            if($key==''){
                $orders = Db::name('order')
                    ->where('mid','in',$sid)
                    ->where('pay_status',1)
                    ->where('order_status',$type)
                    ->page($pn,$size)
                    ->order('oid desc')
                    ->select();
                $count = Db::name('order')
                    ->where('mid','in',$sid)
                    ->where('pay_status',1)
                    ->where('order_status',$type)
                    ->count();
                
            }else{
                $orders = Db::name('order')
                    ->where('mid','in',$sid)
                    ->where('trade_no','like','%'.$key.'%')
                    ->where('pay_status',1)
                    ->where('order_status',$type)
                    ->page($pn,$size)
                    ->order('oid desc')
                    ->select();
                $count = Db::name('order')
                    ->where('mid','in',$sid)
                    ->where('trade_no','like','%'.$key.'%')
                    ->where('pay_status',1)
                    ->where('order_status',$type)
                    ->count();
            }
            return json(['code'=>200,'msg'=>'ok','data'=>[
                'orders'=>$orders,'pn'=>$pn,'size'=>$size,'count'=>$count
                ]
            ]);
        }
        
    }

    /**
     * 根据订单id获取订单商品列表
     */
    public function getOrderGoodsList($oid=null){
        $mid = session('mid');
        if($mid==null) return json(['code'=>-1,'msg'=>'未登录']);

        if($oid==null){
            return json(['code'=>201,'msg'=>'参数不能为空']);
        }

        $oglList = Db::name('order_goods_list')
            ->where('order_id',$oid)
            ->select();

        return json(['code'=>200,'msg'=>'ok','list'=>$oglList]);
        
    }

    /**
     * {
	{
	"aid": 1,
	"goods": {
            "1": 1,
            "2": 2
        }
    }
     */
    
    public function placeOrder($data=null){
        // phpinfo();return;
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($data==null) return json(['code'=>201,'msg'=>'参数不正确']);
        $data = json_decode($data,true);
        if(!isset($data['goods']) || $data['goods']==null){
            return json(['code'=>201,'msg'=>'参数不正确']);
        }

        Db::startTrans();
        try{

            $orderGoodsList = [];//订单商品清单
            $totalMoney = 0;
            foreach($data['goods'] as $key=>$value){
                $goods = Db::name('stock')->alias('s')
                    ->field('sid,stock,shop_id,g.goods_id,goods_name,goods_price')
                    ->join('goods g','g.goods_id=s.goods_id')
                    ->where('s.sid',$key)
                    ->where('goods_status',0)
                    ->find();

                if($goods==null) return json(['code'=>202,'msg'=>'商品不存在']);
                if($goods['stock']<$value) return json(['code'=>203,'msg'=>'商品库存不足']);

                $mid = $goods['shop_id'];
                
                $money = bcmul($goods['goods_price'],$value,2);
                $totalMoney = bcadd($totalMoney,$money,2);
                $orderGoodsList[] = [
                    'goods_id'=>$goods['sid'],'goods_price'=>$goods['goods_price'],
                    'goods_name'=>$goods['goods_name'],'goods_count'=>$value,'total_money'=>$money,
                
                ];

                //修改库存
                $res = Db::name('stock')
                    ->where('sid',$key)
                    ->where("(stock-$value)>=0")
                    ->setDec('stock',$value);
                if($res==0){
                    throw new MyException(203,'商品库存不足');
                }
            }
            if($orderGoodsList==null) return json(['code'=>204,'msg'=>'商品至少需要一个']);
            
            $addr = null;
            if(!empty($data['aid'])){
                $addr = Db::name('address')
                    ->where('id',$data['aid'])
                    ->where('user_id',$userId)
                    ->find();
            }
            //type=0表示上门取，1表示配送
            if($addr==null){
                $shop = Db::name('chain_shop')->where('id',$goods['shop_id'])->find();
                $address = json_encode(['type'=>0,'info'=>['phone'=>$shop['telphone'],'addr'=>$shop['address']]]);
            }else{
                $address = json_encode(['type'=>1,'info'=>[
                    'phone'=>$addr['telphone'],'addr'=>$addr['address'],'name'=>$addr['username']
                    ]
                ]);
            }

            $orderNo = create_id();
            //保存订单数据
            $orderData = [
                'trade_no'=>$orderNo,'total_price'=>$totalMoney,'mid'=>$mid,
                'user_id'=>$userId,'address'=>$address,
            ];
            //如果商品价格为0则直接支付成功
            if($goods['goods_price']==0){
                $orderData['pay_status']=1;
            }
            $oid = Db::name('order')->insertGetId($orderData);

            $dbdata  = [];
            foreach($orderGoodsList as $ogl){
            
                $ogl['order_id']=$oid;
                $dbdata[]=$ogl;
            }
            
            Db::name('order_goods_list')->insertAll($dbdata);

            if($goods['goods_price']!=0){
                //发起支付
                $payData = [
                    'body'    => '商品购买',
                    'subject'    => '商品购买支付',
                    'order_no'    => $orderNo,
                    'timeout_express' => time() + 600,// 表示必须 600s 内付款
                    'amount'    => $totalMoney,// 单位为元 ,最小为0.01
                    'return_param' => '100',
                    // 'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',// 客户地址
                    'goods_type' => '1',// 0—虚拟类商品，1—实物类商品
                ];

                $pay = new Pay();
                $ret = $pay->webPay($payData);
                Db::commit();
                return json(['code'=>200,'msg'=>'ok','data'=>$ret]);
            }
            Db::commit();
            return json(['code'=>200,'msg'=>'ok']);
        }catch (\Exception $e){
            Db::rollback();
            return json(['code'=>205,'msg'=>'下单失败'.$e->getMessage()]);
        }
    }

    /**
     * 支付异步通知
     */
    public function payNotify(){
        $callback = new NotifyCallBack();
        try {
            // 处理回调，内部进行了签名检查
            $ret = Notify::run(Config::WX_CHARGE, config('ali'), $callback);
            echo $ret;
        } catch (PayException $e) {
            Log::error($e->errorMessage());
        }
        return;
    }


    public function notify(){
        $order = Db::name('order')->order('oid desc')->find();
        if($order != null){
            Db::name('order')->where('oid',$order['oid'])->setField('pay_status',1);
            return json(['code'=>200,'msg'=>'ok']);
        }
        return json(['code'=>201,'msg'=>'失败']);
    }

    /**
     * 退款
     * @param null $oid
     */
    public function refund($oid=null){
        $mid = session('mid');
        if($mid==null) return json(['code'=>-1,'msg'=>'未登录']);
        if($oid==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        $order = Db::name('order')->where('oid',$oid)->find();
        //订单状态，默认为0表示订单未确定，1表示已接单，2表示已发货，
        //3表示用户已收货，4表示取消订单，5同意退款,6已评论
        if($order==null) return json(['code'=>202,'msg'=>'订单不存在']);
        if($order['order_status']!=4){
            return json(['code'=>203,'msg'=>'订单状态不正确']);//只有用户取消订单时才可以退款
        }
        try{
            //修改库存
            Db::name('stock')->where('sid',$order['stock_id'])->setInc('stock',$order['buy_count']);
            //修改订单状态
            Db::name('order')->where('oid',$oid)->setField('order_status',5);

            //更改账户金额
            Db::name('account')->where('id',1)
                ->where('money-'.$order['goods_price'],'>',0)
            ->setDec('money',$order['goods_price']);

            //插入流水账单
            $dbData = ['money'=>$order['goods_price'],'type'=>1,'trade_no'=>$order['trade_no']];
            Db::name('account_bill')->insert($dbData);

            $pay = new Pay();
            $data = [
                'trade_no' => $order['trade_no'],// 支付宝交易号， 与 out_trade_no 必须二选一
                'refund_fee' => $order['goods_price'],
                'reason' => '我要退款',
                'refund_no' => create_id(),
            ];
            $ret = $pay->aliRefund($data);
            //退款失败抛出异常
            //throw new MyException(204,'退款失败');
            Db::commit();
            return json(['code'=>200,'msg'=>'ok','info'=>$ret]);
        }catch (\Exception $e){
            Db::rollback();
            return json(['code'=>204,'msg'=>'退款失败'.$e->getMessage()]);
        }

    }

    /**
     * 商家确认订单
     * @param null $oid
     * @return mixed
     */
    public function confirmOrder($oid=null){
        $mid = session('mid');
        if($mid==null) return json(['code'=>-1,'msg'=>'未登录']);
        if($oid==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        $order = Db::name('order')->where('oid',$oid)->where('pay_status',1)->find();
        //订单状态，默认为0表示订单未确定，1表示已接单，2表示已发货，
        //3表示用户已收货，4表示取消订单，5同意退款,6已评论
        if($order==null) return json(['code'=>202,'msg'=>'订单不存在']);
        if($order['order_status']!=0){
            return json(['code'=>203,'msg'=>'订单状态不正确']);//只有未确定订单才可以确认订单
        }

        Db::name('order')->where('oid',$oid)->setField('order_status',1);

        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 商家发货
     * @param null $oid
     * @return mixed
     */
    public function delivery($oid=null){
        $mid = session('mid');
        if($mid==null) return json(['code'=>-1,'msg'=>'未登录']);
        if($oid==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        $order = Db::name('order')->where('oid',$oid)->where('pay_status',1)->find();
        //订单状态，默认为0表示订单未确定，1表示已接单，2表示已发货，
        //3表示用户已收货，4表示取消订单，5同意退款,6已评论
        if($order==null) return json(['code'=>202,'msg'=>'订单不存在']);
        if($order['order_status']!=1){
            return json(['code'=>203,'msg'=>'订单状态不正确']);//只有确定订单才可以发货
        }

        Db::name('order')->where('oid',$oid)->setField('order_status',2);

        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 用户收到商品
     * @param null $oid
     * @return mixed
     */
    public function receipt($oid=null){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($oid==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        $order = Db::name('order')
            ->where('oid',$oid)
            ->where('user_id',$userId)
            ->where('pay_status',1)
            ->find();
        //订单状态，默认为0表示订单未确定，1表示已接单，2表示已发货，
        //3表示用户已收货，4表示取消订单，5同意退款,6已评论
        if($order==null) return json(['code'=>202,'msg'=>'订单不存在']);
        if($order['order_status']!=2){
            return json(['code'=>203,'msg'=>'订单状态不正确']);//只有订单发货才可以收到商品
        }
        Db::name('order')
            ->where('oid',$oid)
            ->where('user_id',$userId)
            ->setField('order_status',3);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 用户取消订单
     * @param null $oid
     * @return mixed
     */
    public function cancelOrder($oid=null){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        if($oid==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        $order = Db::name('order')->where('oid',$oid)->where('pay_status',1)->find();
        //订单状态，默认为0表示订单未确定，1表示已接单，2表示已发货，
        //3表示用户已收货，4表示取消订单，5同意退款,6已评论
        if($order==null) return json(['code'=>202,'msg'=>'订单不存在']);
        if($order['order_status']>3){
            return json(['code'=>203,'msg'=>'订单状态不正确']);
        }
        Db::name('order')
            ->where('oid',$oid)
            ->where('user_id',$userId)
            ->setField('order_status',4);
        return json(['code'=>200,'msg'=>'ok']);
    }

    /**
     * 评论订单
     * @return mixed
     */
    public function commentOrder(){
        $userId = session('user_id');
        if($userId==null) return json(['code'=>-1,'msg'=>'请先登录']);
        $validate = new Validate([
            'oid'=>'require|number',
            'content'=>'require|max:255',
        ]);
        $data = input('param.');
        if (!$validate->check($data)) {
            return json(['code'=>201,'msg'=>'参数格式不正确']);
        }
        $order = Db::name('order')->where('oid',$data['oid'])->where('pay_status',1)->find();
        //订单状态，默认为0表示订单未确定，1表示已接单，2表示已发货，
        //3表示用户已收货，4表示取消订单，5同意退款,6已评论
        if($order==null) return json(['code'=>203,'msg'=>'订单不存在']);
        if($order['order_status']!=3){
            return json(['code'=>203,'msg'=>'订单状态不正确']);//用户收货后才可以评论订单
        }
        
        Db::startTrans();
        try{
            $dbData = ['content'=>$data['content'],'user_id'=>$userId,'shop_id'=>$order['stock_id']];
            Db::name('comment')->insert($dbData);
            Db::name('order')
                ->where('oid',$data['oid'])
                ->where('user_id',$userId)
                ->setField('order_status',6);
            Db::commit();
            return json(['code'=>200,'msg'=>'ok']);
        }catch (\Exception $e){
            Db::rollback();
            return json(['code'=>204,'msg'=>'评论失败'.$e->getMessage()]);
        }
        
    }

    /**
     * 获取连锁店商品评论
     * @param int $pn
     * @param int $size
     * @param null $sid
     * @return mixed
     */
    public function getAllComment($pn=1,$size=10,$sid=null){
        if($sid==null) return json(['code'=>201,'msg'=>'参数不能为空']);
        if(!is_numeric($pn) || $pn<1) $pn=1;
        if(!is_numeric($size) || $size<1) $size=10;
        
        $comment = Db::name('comment')
            ->field('id,content,create_time,uid,telphone,nickname')
            ->join('user','uid=user_id')
            ->where('shop_id',$sid)
            ->order('id desc')
            ->page($pn,$sid)
            ->select();
        $count = Db::name('comment')
            ->where('shop_id',$sid)
            ->count();
        return json(['code'=>200,'msg'=>'ok','data'=>[
            'comment'=>$comment,'count'=>$count,'pn'=>$pn,'size'=>$size]
        ]);
    }

}
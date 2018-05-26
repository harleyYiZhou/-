<?php
/**
 * User: cboy
 * Date: 2018/1/25
 * Time: 16:45
 */

namespace app\common;

use think\exception\Handle;
use think\Log;

class ExceptionHandle extends Handle
{

    public function render(\Exception $e){
        if($e instanceof MyException){
            return json(['code'=>$e->getCode(),'msg'=>$e->getMessage()]);
        }
        Log::error($e->getMessage());
        return json(['code'=>-3,'msg'=>'系统错误！']);
    }
}
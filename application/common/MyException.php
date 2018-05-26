<?php
/**
 * User: cboy
 * Date: 2018/1/25
 * Time: 17:26
 */

namespace app\common;


class MyException extends \RuntimeException
{
    
    public function __construct($code, $message)
    {
        parent::__construct($message, $code);
    }
    
}
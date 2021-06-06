<?php

namespace Weirin\Wechat\Pay;

use Exception;

/**
 *
 * 微信支付API异常类
 * @author widyhu
 *
 */
class ErrorException extends Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}

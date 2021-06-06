<?php

namespace Weirin\Wechat\Pay\Data;

use Weirin\Wechat\Pay\Config;
use Weirin\Wechat\Pay\Data\Base;

/**
 *
 * 回调基础类
 * @author widyhu
 *
 */
class NotifyReply extends  Base
{
    /**
     *
     * 设置返回状态码 FAIL 或者 SUCCESS
     * @param string
     */
    public function SetReturn_code($return_code)
    {
        $this->values['return_code'] = $return_code;
    }

    /**
     *
     * 获取返回状态码 FAIL 或者 SUCCESS
     * @return string $return_code
     */
    public function GetReturn_code()
    {
        return $this->values['return_code'];
    }

    /**
     *
     * 设置返回信息, 如非空，为错误原因, 如: 签名失败/参数格式校验错误
     * @param string $return_code
     */
    public function SetReturn_msg($return_msg)
    {
        $this->values['return_msg'] = $return_msg;
    }

    /**
     *
     * 获取返回信息, 如非空，为错误原因, 如: 签名失败/参数格式校验错误
     * @return string
     */
    public function GetReturn_msg()
    {
        return $this->values['return_msg'];
    }

    /**
     *
     * 设置返回参数
     * @param string $key
     * @param string $value
     */
    public function SetData($key, $value)
    {
        $this->values[$key] = $value;
    }
}

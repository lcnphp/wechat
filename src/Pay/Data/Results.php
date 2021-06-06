<?php

namespace Weirin\Wechat\Pay\Data;

use Weirin\Wechat\Pay\Config;
use Weirin\Wechat\Pay\Data\Base;
use Weirin\Wechat\Pay\ErrorException;

/**
 *
 * 接口调用结果类
 * @author widyhu
 *
 */
class Results extends Base
{
    /**
     *
     * 检测签名
     */
    public function CheckSign()
    {
        //fix异常
        if(!$this->IsSignSet()){
            throw new ErrorException("签名错误，签名数据不存在！");
        }

        $sign = $this->MakeSign();
        if($this->GetSign() == $sign){
            return true;
        }
        throw new ErrorException("签名错误！");
    }

    /**
     *
     * 使用数组初始化
     * @param array $array
     */
    public function FromArray($array)
    {
        $this->values = $array;
    }

    /**
     *
     * 使用数组初始化对象
     * @param array $array
     * @param 是否检测签名 $noCheckSign
     */
    public static function InitFromArray($array, $noCheckSign = false)
    {
        $obj = new self();
        $obj->FromArray($array);
        if($noCheckSign == false){
            $obj->CheckSign();
        }
        return $obj;
    }

    /**
     *
     * 设置参数
     * @param string $key
     * @param string $value
     */
    public function SetData($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws ErrorException
     */
    public static function Init($xml, $initCallBack = null)
    {
        $obj = new self();
        $obj->FromXml($xml);
        //fix bug 2015-06-29
        if($obj->values['return_code'] != 'SUCCESS'){
            return $obj->GetValues();
        }

        // 根据appid初始化支付配置数据
        if ($initCallBack !== null && !empty($obj->values['appid'])) {
            call_user_func($initCallBack, $obj->values['appid']);
        }

        $obj->CheckSign();
        return $obj->GetValues();
    }
}
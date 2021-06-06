<?php

namespace Weirin\Wechat\Pay;

use Weirin\Wechat\Pay\Api;
use Weirin\Wechat\Pay\Notify;
use Weirin\Wechat\Pay\Log;
use Weirin\Wechat\Pay\Data\OrderQuery as OrderQueryData;

/**
 * 支付通知回调类
 * Class PayNotifyCallBack
 * @package Wechat\Pay
 */
class PayNotifyCallBack extends Notify
{
    /**
     * @var
     */
    public $data;


    /**
     * 查询订单
     * @param $transaction_id
     * @return bool
     */
    public function Queryorder($transaction_id)
    {

        $input = new OrderQueryData();
        $input->SetTransaction_id($transaction_id);
        $result = Api::orderQuery($input);

        Log::debug("查询订单:" . json_encode($result));

        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    /**
     * 重写回调处理函数
     * @param array $data
     * @param string $msg
     * @return bool
     */
    public function NotifyProcess($data, &$msg)
    {
        Log::debug("通知数据:" . json_encode($data));

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }

        $this->data = $data;
        return true;
    }
}

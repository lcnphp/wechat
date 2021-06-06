<?php

namespace Weirin\Wechat\Pay;

use Weirin\Wechat\Pay\Api as PayApi;
use Weirin\Wechat\Pay\Data\UnifiedOrder as PayUnifiedOrderData;

/**
 * 扫码支付通知回调类
 * Class NativeNotifyCallBack
 * @package Wechat\Pay
 */
class NativeNotifyCallBack extends Notify
{
    /**
     * @var
     */
    public $data;

    public function unifiedorder($openId, $product_id)
    {
        $config = Config::getInstance();

        $datetime = date('YmdHis');

        // 统一下单
        $input = new PayUnifiedOrderData();
        $input->SetBody("test");
        $input->SetAttach("test");
        $input->SetOut_trade_no($config->MCHID . $datetime);
        $input->SetTotal_fee("1");
        $input->SetTime_start($datetime);
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
        $input->SetTrade_type("NATIVE");
        $input->SetOpenid($openId);
        $input->SetProduct_id($product_id);
        $result = PayApi::unifiedOrder($input);
        Log::debug("unifiedorder:" . json_encode($result));
        return $result;
    }

    /**
     * @param array $data
     * @param string $msg
     * @return bool
     */
    public function NotifyProcess($data, &$msg)
    {
        //echo "处理回调";
        Log::debug("call back:" . json_encode($data));

        if(!array_key_exists("openid", $data) ||
            !array_key_exists("product_id", $data))
        {
            $msg = "回调数据异常";
            return false;
        }

        $openid = $data["openid"];
        $order_no = $data["product_id"];

        // 统一下单
        //$result = $this->unifiedorder($openid, $product_id);
        // 根据appid初始化支付配置数据
        $result = call_user_func([
            'app\wx\controllers\OrderController',
            'initWxpayConfigCallBack'],
            $data->values['appid']
        );
        if (
            !array_key_exists("appid", $result)
            || !array_key_exists("mch_id", $result)
            || !array_key_exists("prepay_id", $result)
        ) {
            $msg = "统一下单失败";
            return false;
        }

        $this->SetData("appid", $result["appid"]);
        $this->SetData("mch_id", $result["mch_id"]);
        $this->SetData("nonce_str", PayApi::getNonceStr());
        $this->SetData("prepay_id", $result["prepay_id"]);
        $this->SetData("result_code", "SUCCESS");
        $this->SetData("err_code_des", "OK");
        return true;
    }
}

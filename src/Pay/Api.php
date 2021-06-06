<?php

namespace Weirin\Wechat\Pay;

use Weirin\Wechat\Pay\Config;
use Weirin\Wechat\Pay\ErrorException;
use Weirin\Wechat\Pay\Data\BizPayUrl;
use Weirin\Wechat\Pay\Data\CloseOrder;
use Weirin\Wechat\Pay\Data\DownloadBill;
use Weirin\Wechat\Pay\Data\MicroPay;
use Weirin\Wechat\Pay\Data\OrderQuery;
use Weirin\Wechat\Pay\Data\Refund;
use Weirin\Wechat\Pay\Data\RefundQuery;
use Weirin\Wechat\Pay\Data\Report;
use Weirin\Wechat\Pay\Data\Results;
use Weirin\Wechat\Pay\Data\Reverse;
use Weirin\Wechat\Pay\Data\ShortUrl;
use Weirin\Wechat\Pay\Data\UnifiedOrder;
use Weirin\Wechat\Pay\Data\TransfersOrder;
use Weirin\Wechat\Pay\Data\RedPack;
use Weirin\Wechat\Pay\Data\GroupRedPack;
use Weirin\Wechat\Pay\Data\Coupon;
use Weirin\Wechat\Pay\Data\TransfersBank;
use Weirin\Wechat\Log as WechatLog;

/**
 * 接口访问类，包含所有微信支付API列表的封装，类中方法为static方法，
 * 每个接口有默认超时时间（除提交被扫支付为10s，上报超时时间为1s外，其他均为6s）
 *
 * @author widyhu
 * @author Lee <349238652@qq.com>
 *
 * Class Api
 * @package Wechat\Pay
 */
class Api
{
    /**
     *
     * 统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param UnifiedOrder $inputObj
     * @param int $timeOut
     * @throws ErrorException
     * @return array
     */
    public static function unifiedOrder(UnifiedOrder $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //检测必填参数
        if(!$inputObj->IsOut_trade_noSet()) {
            throw new ErrorException("缺少统一支付接口必填参数out_trade_no！");
        } else if(!$inputObj->IsBodySet()){
            throw new ErrorException("缺少统一支付接口必填参数body！");
        } else if(!$inputObj->IsTotal_feeSet()) {
            throw new ErrorException("缺少统一支付接口必填参数total_fee！");
        } else if(!$inputObj->IsTrade_typeSet()) {
            throw new ErrorException("缺少统一支付接口必填参数trade_type！");
        }

        //关联参数
        if($inputObj->GetTrade_type() == "JSAPI" && !$inputObj->IsOpenidSet()){
            throw new ErrorException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！");
        }
        if($inputObj->GetTrade_type() == "NATIVE" && !$inputObj->IsProduct_idSet()){
            throw new ErrorException("统一支付接口中，缺少必填参数product_id！trade_type为JSAPI时，product_id为必填参数！");
        }

        $config = Config::getInstance();

        //异步通知url未设置，则使用配置文件中的url
        if(!$inputObj->IsNotify_urlSet()){
            $inputObj->SetNotify_url($config->NOTIFY_URL);//异步通知url
        }

        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
        //$inputObj->SetSpbill_create_ip("1.1.1.1");
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        //签名
        $inputObj->SetSign();
        $xml = $inputObj->ToXml();

        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, false, $timeOut);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }

    /*
     * 企业转账订单
     * 必填参数: partner_trade_no、openid、check_name、amount、desc
     * 参数:
            <xml>
            <mch_appid>wxe062425f740c30d8</mch_appid>
            <mchid>10000098</mchid>
            <nonce_str>3PG2J4ILTKCH16CQ2502SI8ZNMTM67VS</nonce_str>
            <partner_trade_no>100000982014120919616</partner_trade_no>
            <openid>ohO4Gt7wVPxIT1A9GjFaMYMiZY1s</openid>
            <check_name>OPTION_CHECK</check_name>
            <re_user_name>张三</re_user_name>
            <amount>100</amount>
            <desc>节日快乐!</desc>
            <spbill_create_ip>10.2.3.10</spbill_create_ip>
            <sign>C97BDBACF37622775366F38B629F45E3</sign>
            </xml>
     */
    public static function transfersOrder(TransfersOrder $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        //检测必填参数
        if(!$inputObj->IsPartner_trade_noSet()) {
            throw new ErrorException("缺少企业转账接口必填参数: partner_trade_no！");
        } else if(!$inputObj->IsDescSet()){
            throw new ErrorException("缺少企业转账接口必填参数: desc！");
        } else if(!$inputObj->IsAmountSet()) {
            throw new ErrorException("缺少企业转账接口必填参数: amount！");
        }else if(!$inputObj->IsOpenidSet()) {
            throw new ErrorException("缺少企业转账接口必填参数: openid！");
        }else if(!$inputObj->IsCheck_nameSet()) {
            throw new ErrorException("缺少企业转账接口必填参数: check_name！");
        }


        $config = Config::getInstance();

        $inputObj->SetAppid($config->APPID);// 商户appid
        $inputObj->SetMch_id($config->MCHID);// 商户号
        $inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
        //$inputObj->SetSpbill_create_ip("1.1.1.1");
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        //签名
        $inputObj->SetSign();
        $xml = $inputObj->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, true, $timeOut);
        $obj = new Results();
        $result = $obj->FromXml($response);
        //$result = Results::Init($response); // 不需要再进去校验
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        return $result;
    }

    /**
     *
     * 查询订单，WxPayOrderQuery中out_trade_no、transaction_id至少填一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param OrderQuery $inputObj
     * @param int $timeOut
     * @throws ErrorException
     * @return 成功时返回，其他抛异常
     */
    public static function orderQuery(OrderQuery $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        //检测必填参数
        if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
            throw new ErrorException("订单查询接口中，out_trade_no、transaction_id至少填一个！");
        }

        $config = Config::getInstance();

        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();

        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, false, $timeOut);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }

    /**
     *
     * 关闭订单，WxPayCloseOrder中out_trade_no必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param CloseOrder $inputObj
     * @param int $timeOut
     * @throws ErrorException
     * @return 成功时返回，其他抛异常
     */
    public static function closeOrder(CloseOrder $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/pay/closeorder";
        //检测必填参数
        if(!$inputObj->IsOut_trade_noSet()) {
            throw new ErrorException("订单查询接口中，out_trade_no必填！");
        }
        $config = Config::getInstance();
        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();

        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, false, $timeOut);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }

    /**
     *
     * 申请退款，WxPayRefund中out_trade_no、transaction_id至少填一个且
     * out_refund_no、total_fee、refund_fee、op_user_id为必填参数
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param Refund $inputObj
     * @param int $timeOut
     * @throws ErrorException
     * @return 成功时返回，其他抛异常
     */
    public static function refund(Refund $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        //检测必填参数
        if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
            throw new ErrorException("退款申请接口中，out_trade_no、transaction_id至少填一个！");
        }else if(!$inputObj->IsOut_refund_noSet()){
            throw new ErrorException("退款申请接口中，缺少必填参数out_refund_no！");
        }else if(!$inputObj->IsTotal_feeSet()){
            throw new ErrorException("退款申请接口中，缺少必填参数total_fee！");
        }else if(!$inputObj->IsRefund_feeSet()){
            throw new ErrorException("退款申请接口中，缺少必填参数refund_fee！");
        }else if(!$inputObj->IsOp_user_idSet()){
            throw new ErrorException("退款申请接口中，缺少必填参数op_user_id！");
        }
        $config = Config::getInstance();
        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, true, $timeOut);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }

    /**
     *
     * 查询退款
     * 提交退款申请后，通过调用该接口查询退款状态。退款有一定延时，
     * 用零钱支付的退款20分钟内到账，银行卡支付的退款3个工作日后重新查询退款状态。
     * WxPayRefundQuery中out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param RefundQuery $inputObj
     * @param int $timeOut
     * @throws ErrorException
     * @return 成功时返回，其他抛异常
     */
    public static function refundQuery(RefundQuery $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/pay/refundquery";
        //检测必填参数
        if(!$inputObj->IsOut_refund_noSet() &&
            !$inputObj->IsOut_trade_noSet() &&
            !$inputObj->IsTransaction_idSet() &&
            !$inputObj->IsRefund_idSet()) {
            throw new ErrorException("退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！");
        }
        $config = Config::getInstance();
        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();

        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, false, $timeOut);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }

    /**
     * 下载对账单，WxPayDownloadBill中bill_date为必填参数
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param DownloadBill $inputObj
     * @param int $timeOut
     * @throws ErrorException
     * @return 成功时返回，其他抛异常
     */
    public static function downloadBill(DownloadBill $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/pay/downloadbill";
        //检测必填参数
        if(!$inputObj->IsBill_dateSet()) {
            throw new ErrorException("对账单接口中，缺少必填参数bill_date！");
        }
        $config = Config::getInstance();
        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();

        $response = self::postXmlCurl($xml, $url, false, $timeOut);
        if(substr($response, 0 , 5) == "<xml>"){
            return "";
        }
        return $response;
    }

    /**
     * 提交被扫支付API
     * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，
     * 由商户收银台或者商户后台调用该接口发起支付。
     * WxPayWxPayMicroPay中body、out_trade_no、total_fee、auth_code参数必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param MicroPay $inputObj
     * @param int $timeOut
     * @return mixed
     * @throws ErrorException
     */
    public static function micropay(MicroPay $inputObj, $timeOut = 10)
    {
        $url = "https://api.mch.weixin.qq.com/pay/micropay";
        //检测必填参数
        if(!$inputObj->IsBodySet()) {
            throw new ErrorException("提交被扫支付API接口中，缺少必填参数body！");
        } else if(!$inputObj->IsOut_trade_noSet()) {
            throw new ErrorException("提交被扫支付API接口中，缺少必填参数out_trade_no！");
        } else if(!$inputObj->IsTotal_feeSet()) {
            throw new ErrorException("提交被扫支付API接口中，缺少必填参数total_fee！");
        } else if(!$inputObj->IsAuth_codeSet()) {
            throw new ErrorException("提交被扫支付API接口中，缺少必填参数auth_code！");
        }

        $inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip

        $config = Config::getInstance();
        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();

        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, false, $timeOut);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }

    /**
     * 撤销订单API接口，WxPayReverse中参数out_trade_no和transaction_id必须填写一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param Reverse $inputObj
     * @param int $timeOut
     * @return array
     * @throws \Wechat\Pay\ErrorException
     */
    public static function reverse(Reverse $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/secapi/pay/reverse";
        //检测必填参数
        if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
            throw new ErrorException("撤销订单API接口中，参数out_trade_no和transaction_id必须填写一个！");
        }

        $config = Config::getInstance();
        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();

        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, true, $timeOut);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }

    /**
     *
     * 测速上报，该方法内部封装在report中，使用时请注意异常流程
     * Report中interface_url、return_code、result_code、user_ip、execute_time_必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param Report $inputObj
     * @param int $timeOut
     * @throws ErrorException
     * @return 成功时返回，其他抛异常
     */
    public static function report(Report $inputObj, $timeOut = 1)
    {
        $url = "https://api.mch.weixin.qq.com/payitil/report";
        //检测必填参数
        if(!$inputObj->IsInterface_urlSet()) {
            throw new ErrorException("接口URL，缺少必填参数interface_url！");
        } if(!$inputObj->IsReturn_codeSet()) {
            throw new ErrorException("返回状态码，缺少必填参数return_code！");
        } if(!$inputObj->IsResult_codeSet()) {
            throw new ErrorException("业务结果，缺少必填参数result_code！");
        } if(!$inputObj->IsUser_ipSet()) {
            throw new ErrorException("访问接口IP，缺少必填参数user_ip！");
        } if(!$inputObj->IsExecute_time_Set()) {
            throw new ErrorException("接口耗时，缺少必填参数execute_time_！");
        }

        $config = Config::getInstance();
        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetUser_ip($_SERVER['REMOTE_ADDR']);//终端ip
        $inputObj->SetTime(date("YmdHis"));//商户上报时间
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();

        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, false, $timeOut);
        return $response;
    }

    /**
     *
     * 生成二维码规则,模式一生成支付二维码
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param BizPayUrl $inputObj
     * @param int $timeOut
     * @throws ErrorException
     * @return 成功时返回，其他抛异常
     */
    public static function bizpayurl(BizPayUrl $inputObj, $timeOut = 6)
    {
        if(!$inputObj->IsProduct_idSet()){
            throw new ErrorException("生成二维码，缺少必填参数product_id！");
        }

        $config = Config::getInstance();
        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetTime_stamp(time());//时间戳
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名

        return $inputObj->GetValues();
    }

    /**
     *
     * 转换短链接
     * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，
     * 减小二维码数据量，提升扫描速度和精确度。
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param ShortUrl $inputObj
     * @param int $timeOut
     * @throws ErrorException
     * @return 成功时返回，其他抛异常
     */
    public static function shorturl(ShortUrl $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/tools/shorturl";
        //检测必填参数
        if(!$inputObj->IsLong_urlSet()) {
            throw new ErrorException("需要转换的URL，签名用原串，传输需URL encode！");
        }

        $config = Config::getInstance();
        $inputObj->SetAppid($config->APPID);//公众账号ID
        $inputObj->SetMch_id($config->MCHID);//商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToXml();

        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, false, $timeOut);
        $result = Results::Init($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }

    /**
     * * 支付结果通用通知
     * @param function $callback
     * 直接回调函数使用方法: notify(you_function);
     * 回调类成员函数方法:notify(array($this, you_function));
     * $callback  原型为：function function_name($data){}     *
     * @param $msg
     * @return bool|mixed
     */
    public static function notify($callback, $initCallBack, &$msg)
    {
        $xml = file_get_contents('php://input', 'r');

        //如果返回成功则验证签名
        try {
            $result = Results::Init($xml, $initCallBack);
        } catch (ErrorException $e){
            $msg = $e->errorMessage();
            return false;
        }

        return call_user_func($callback, $result);
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return string 产生的随机字符串
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * 直接输出xml
     * @param string $xml
     */
    public static function replyNotify($xml)
    {
        echo $xml;
    }

    /**
     *
     * 上报数据， 上报的时候将屏蔽所有异常流程
     * @param string $url
     * @param int $startTimeStamp
     * @param array $data
     */
    private static function reportCostTime($url, $startTimeStamp, $data)
    {
        $config = Config::getInstance();

        //如果不需要上报数据
        if($config->REPORT_LEVENL == 0){
            return;
        }
        //如果仅失败上报
        if($config->REPORT_LEVENL == 1 &&
            array_key_exists("return_code", $data) &&
            $data["return_code"] == "SUCCESS" &&
            array_key_exists("result_code", $data) &&
            $data["result_code"] == "SUCCESS")
        {
            return;
        }

        //上报逻辑
        $endTimeStamp = self::getMillisecond();
        $objInput = new Report();
        $objInput->SetInterface_url($url);
        $objInput->SetExecute_time_($endTimeStamp - $startTimeStamp);
        //返回状态码
        if(array_key_exists("return_code", $data)){
            $objInput->SetReturn_code($data["return_code"]);
        }
        //返回信息
        if(array_key_exists("return_msg", $data)){
            $objInput->SetReturn_msg($data["return_msg"]);
        }
        //业务结果
        if(array_key_exists("result_code", $data)){
            $objInput->SetResult_code($data["result_code"]);
        }
        //错误代码
        if(array_key_exists("err_code", $data)){
            $objInput->SetErr_code($data["err_code"]);
        }
        //错误代码描述
        if(array_key_exists("err_code_des", $data)){
            $objInput->SetErr_code_des($data["err_code_des"]);
        }
        //商户订单号
        if(array_key_exists("out_trade_no", $data)){
            $objInput->SetOut_trade_no($data["out_trade_no"]);
        }
        //设备号
        if(array_key_exists("device_info", $data)){
            $objInput->SetDevice_info($data["device_info"]);
        }

        try{
            self::report($objInput);
        } catch (ErrorException $e){
            //不做任何处理
        }
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @param bool|false $useCert
     * @return mixed
     * @throws \Wechat\Pay\ErrorException
     */
    private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        $config = Config::getInstance();

        //如果有配置代理这里就设置代理
        if($config->CURL_PROXY_HOST != "0.0.0.0"
            && $config->CURL_PROXY_PORT != 0){
            curl_setopt($ch,CURLOPT_PROXY, $config->CURL_PROXY_HOST);
            curl_setopt($ch,CURLOPT_PROXYPORT, $config->CURL_PROXY_PORT);
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if($useCert == true){
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $config->SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $config->SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);

            WechatLog::debug("postXmlCurl curl Exception:: " . $error);
            throw new ErrorException("postXmlCurl curl Exception: error_no=[$error]");
        }
    }

    /**
     * 获取毫秒级别的时间戳
     * @return array|string
     */
    private static function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }

    /*
     * 发放现金红包
     * 参数:
        <xml>
            <sign><![CDATA[E1EE61A91C8E90F299DE6AE075D60A2D]]></sign>
            <mch_billno><![CDATA[0010010404201411170000046545]]></mch_billno>
            <mch_id><![CDATA[888]]></mch_id>
            <wxappid><![CDATA[wxcbda96de0b165486]]></wxappid>
            <send_name><![CDATA[send_name]]></send_name>
            <re_openid><![CDATA[onqOjjmM1tad-3ROpncN-yUfa6uI]]></re_openid>
            <total_amount><![CDATA[200]]></total_amount>
            <total_num><![CDATA[1]]></total_num>
            <wishing><![CDATA[恭喜发财]]></wishing>
            <client_ip><![CDATA[127.0.0.1]]></client_ip>
            <act_name><![CDATA[新年红包]]></act_name>
            <remark><![CDATA[新年红包]]></remark>
            <nonce_str><![CDATA[50780e0cca98c8c8e814883e5caa672e]]></nonce_str>
        </xml>
     */
    public static function sendRedPack(RedPack $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";

        // 检测必填参数
        if(!$inputObj->IsMch_billnoSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: mch_billno！");
        } else if(!$inputObj->IsSend_nameSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: send_name！");
        }else if(!$inputObj->IsRe_openidSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: re_openid！");
        }else if(!$inputObj->IsTotal_amountSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: total_amount！");
        }else if(!$inputObj->IsTotal_numSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: total_num！");
        }else if(!$inputObj->IsWishingSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: wishing！");
        }else if(!$inputObj->IsAct_nameSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: act_name！");
        }else if(!$inputObj->IsRemarkSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: remark！");
        }


        $config = Config::getInstance();

        $inputObj->SetAppid($config->APPID);// 商户appid
        $inputObj->SetMch_id($config->MCHID);// 商户号
        $inputObj->SetClient_ip($_SERVER['REMOTE_ADDR']);//终端ip
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        //签名
        $inputObj->SetSign();
        $xml = $inputObj->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, true, $timeOut);
        $obj = new Results();
        $result = $obj->FromXml($response);

        // 上报请求花费时间
        self::reportCostTime($url, $startTimeStamp, $result);

        return $result;
    }

    /*
     * 发放裂变红包
     * 参数:
        <xml>
           <sign><![CDATA[E1EE61A91C8E90F299DE6AE075D60A2D]]></sign>
           <mch_billno><![CDATA[0010010404201411170000046545]]></mch_billno>
           <mch_id><![CDATA[1000888888]]></mch_id>
           <wxappid><![CDATA[wxcbda96de0b165486]]></wxappid>
           <send_name><![CDATA[send_name]]></send_name>
           <re_openid><![CDATA[onqOjjmM1tad-3ROpncN-yUfa6uI]]></re_openid>
           <total_amount><![CDATA[600]]></total_amount>
           <amt_type><![CDATA[ALL_RAND]]></amt_type>
           <total_num><![CDATA[3]]></total_num>
           <wishing><![CDATA[恭喜发财]]></wishing>
           <act_name><![CDATA[新年红包]]></act_name>
           <remark><![CDATA[新年红包]]></remark>
           <nonce_str><![CDATA[50780e0cca98c8c8e814883e5caa672e]]></nonce_str>
        </xml>
     */
    public static function sendGroupRedPack(GroupRedPack $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack";

        // 检测必填参数
        if(!$inputObj->IsMch_billnoSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: mch_billno！");
        } else if(!$inputObj->IsSend_nameSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: send_name！");
        }else if(!$inputObj->IsRe_openidSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: re_openid！");
        }else if(!$inputObj->IsTotal_amountSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: total_amount！");
        }else if(!$inputObj->IsAmt_typeSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: amt_type！");
        }else if(!$inputObj->IsTotal_numSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: total_num！");
        }else if(!$inputObj->IsWishingSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: wishing！");
        }else if(!$inputObj->IsAct_nameSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: act_name！");
        }else if(!$inputObj->IsRemarkSet()) {
            throw new ErrorException("缺少发放现金红包接口必填参数: remark！");
        }


        $config = Config::getInstance();

        $inputObj->SetAppid($config->APPID);// 商户appid
        $inputObj->SetMch_id($config->MCHID);// 商户号
        $inputObj->SetClient_ip($_SERVER['REMOTE_ADDR']);//终端ip
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        //签名
        $inputObj->SetSign();
        $xml = $inputObj->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, true, $timeOut);
        $obj = new Results();
        $result = $obj->FromXml($response);

        // 上报请求花费时间
        self::reportCostTime($url, $startTimeStamp, $result);

        return $result;
    }

    /*
    * 发放代金券
    * 参数:
       <xml>
            <appid> wx5edab3bdfba3dc1c</appid>
            <coupon_stock_id>699204</coupon_stock_id>
            <mch_id>10010405</mch_id>
            <nonce_str>1417574675</nonce_str>
            <openid>onqOjjrXT-776SpHnfexGm1_P7iE</openid>
            <openid_count>1</openid_count>
            <partner_trade_no>1000009820141203515766</partner_trade_no>
            <sign>841B3002FE2220C87A2D08ABD8A8F791</sign>
        </xml>
    */
    public static function sendCoupon(Coupon $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/send_coupon";

        // 检测必填参数
        if(!$inputObj->IsCoupon_stock_idSet()) {
            throw new ErrorException("缺少发放代金券接口必填参数: coupon_stock_id！");
        } else if(!$inputObj->IsOpenid_countSet()) {
            throw new ErrorException("缺少发放代金券接口必填参数: openid_count！");
        }else if(!$inputObj->IsOpenidSet()) {
            throw new ErrorException("缺少发放代金券接口必填参数: openid！");
        }else if(!$inputObj->IsPartner_trade_noSet()) {
            throw new ErrorException("缺少发放代金券接口必填参数: partner_trade_no！");
        }

        $config = Config::getInstance();

        $inputObj->SetAppid($config->APPID);// 商户appid
        $inputObj->SetMch_id($config->MCHID);// 商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        //签名
        $inputObj->SetSign();
        $xml = $inputObj->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, true, $timeOut);
        $obj = new Results();
        $result = $obj->FromXml($response);

        // 上报请求花费时间
        self::reportCostTime($url, $startTimeStamp, $result);

        return $result;
    }

    /*
     * 微信支付-企业付款到个人银行卡
     * 必填参数: partner_trade_no、enc_bank_no、enc_true_name、bank_code、amount、desc
     * 参数:
            <xml>
                <amount>500</amount>
                <bank_code>1002</bank_code>
                <desc>test</desc>
                <enc_bank_no>so40iz98I8P5DRdMpOqYK/SOWdDhW8fQhlCQEuxV//LLvRZs51B4z8yeIe3X7aYyRdJGdYy18RLpJAZEYrZ9y981pB55aU9ZqT1So7Ypc1URahkLAOggUk/nKur750Lei6D0QQ1Q/B1aiYHA+IPwZH1YEjsIra9tvY7LjYgBjUsEnWx51piaL/Bv4gLvK5lo+lT7iTT2eiLD95y7PcV9U5p5zAxRMPiy6dtJt1UYfwNnbHMZbP+hdTmUhBup2JpJbk+9xchWzrwrFUQPYpB4caTOx98xubwrKrOO/xM2lt9GbRsv1GA5vF04jIiWx/dtkjQvWuPlBOTmkSDl6J0ErQ==</enc_bank_no>
                <enc_true_name>WrmNNBewyx8KJGMtrsYUf3RAmMsaHByOIu/wSjFKy/ouMeg1msRxbwzksPDRjI7OA6pvb3Ty7RQKQTGAjFdaxa10c9Dn0BqLPapP1svj000TWRd1VRJriUqy0macXZu6Pxx9bZd9ngiUcXbrVpGA10BMMwOFJ5VEt7aFJjUJSw2CCZNgj1HOVskm3abNl1eMWyzDCHVjH6uXnT8of17g5GTELTNn2ccNMTmfkUrVJopHeXTA5Yd+uKx5Tgst4IonNiHb+dFWsiGG8aOY29nqHWHw3e+vVRk/0DwEAJzaJlWjb110/TtjYjkquZwFh9XL8GncrNfoBjUz2rtvmhb5Rg==</enc_true_name>
                <mch_id>2302758702</mch_id>
                <nonce_str>50780e0cca98c8c8e814883e5caa672e</nonce_str>
                <partner_trade_no>1212121221278</partner_trade_no>
                <sign>1b3375482ac61d952aab56b534608971</sign>
            </xml>
            <xml>
                <partner_trade_no>201808151616351335</partner_trade_no>
                <enc_bank_no><![CDATA[PTGzIjaDPSKLgqFjuWi01PsNNqzu/oacfZdisiZfwgpskNr8FaEdZRdww/MdrGfmFPjWO37yQ7fJiNP3G0lZQYcHsJoC6GjdGwjs2D/vmJWp27q1zBGn25VG/1x7an4/4PPsOWepKgcUXXjm0UGIRdFVW7cHHT4Gdkx+MTlYItEqZjm1ilbPO3mIsp7TmShMohHvxqVWZe33/dHshba+yG/yYZLDXK1RJuNQG3TtOXnhIfcyzF5GBhXRJKXyeHi6+tK8+q5QkDV8ru7j4ePrYBR5iFf4SmRZoQC5LJjIMyRdbi9sbDdjDMVWsz0DyrZW2oDTv7gYeli+mmjCb8BTiw==]]></enc_bank_no>
                <enc_true_name><![CDATA[RnE0C0Iz1Q5G26RLsbYTLm8Td2GuOTXRlzUOQ6H49M9RGoPqVa0OnkRntjLMskEvcuEq9NwBlrc2alIdcc2VgAswU/AWnnHq7nEgBh8BoPLXt/BNj8H8RPDAwkr9f/7Y4b3KsfMtySuowoyOqo1Qx2/F+AjbyCSoc44PTqPx0QteNg57QYON/Bk8HCSdrWdM49gzj220QncDfra24tO1hqk+kmfRmEWt4MYYhFzEwPCNy0decfirTjIpJWdib85D3qsDOmYcTjbnpcoV8VfsHN0rf6IXS4PKD65spr1Be8JhQIN88XfGzFC22RDoaaI1oi56hi14qZEERToWwNS/3w==]]></enc_true_name>
                <bank_code>1002</bank_code>
                <amount>3</amount>
                <desc><![CDATA[供应商提现No.:201808151616351335]]></desc>
                <mch_id>1496877302</mch_id>
                <nonce_str><![CDATA[isvrds1kpcrhm15nrmpadgxg8rim7974]]></nonce_str>
                <sign><![CDATA[BBAE482FE851A5C781CC59B4FAAE4EA9]]></sign>
            </xml>
            <xml>
                <partner_trade_no>201808151607376417</partner_trade_no>
                <enc_bank_no><![CDATA[TaeSdXa0t7fARUPMb78O/c+33paBKty8PGI7NCzEuV/oz23Bcx77QpHEjwHSqNPkHMrAY8B8T+72LeD7/hqgVPzOD8wr13+ZZM/KDqbj055Iwf6aRY2EIkkuq2wYjAQGtQf3xDCKUWJINZ82F+FjgT8kzF3bt3aDDUmC0UFyfpfiN0KaWq1ges8CXi0FG7pYUGFrBemVkqLmgj+w+84iDjRh4QPtGUVeIdt38tfUjtNyC5Oy2UsXLlhfju23RUjyo5BzEq8pdYny9cIwP/XqefNTVR7LU9jIDx4jUP2sOhBBtucFYdXNSLEYBl/w483jvoOuPmkEvpov2pdx/fkZmA==]]></enc_bank_no>
                <enc_true_name><![CDATA[TVA8mmyihmtXAUMiRVEszQABeZYp7UiJ4ZeamvafREI5cKEZFEWLF9GyngwkSNbWi1cZW7eZRdH3ARE80eRWO9L60kjWEN30QtOa3MS2iuqH87gaVyBCq8eUKCwEzwQK3r3yEY/SxwhYDPPqUeYQWhike0/pJPLuzYsMkYymTZ2i5YpaHHosaE5Fpm0dT0l/rDVbs9iiStNDkncjOAslpj5z5djo5za/9fTBgjDpn2fANubolSxRv2TsfMghhVlFNqMf1KY2jWAgUQ+Ob7NQUFqAC6E34dIQJMxDppLtCLUUQBrxWh0kdGrMw7Soc7Z6gtYGAoR7JbQz70zhwjoNNA==]]></enc_true_name>
                <bank_code>1002</bank_code>
                <amount>2</amount>
                <desc><![CDATA[供应商提现No.:201808151607376417]]></desc>
                <mch_id>1496877302</mch_id>
                <nonce_str><![CDATA[hqeiuou8a8p7pyqwzzjgjxphab87og7n]]></nonce_str>
                <sign><![CDATA[2F7C2A385422468B41C02BECCEDF5609]]></sign>
            </xml>
     */
    public static function transfersBank(TransfersBank $inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank";
        //检测必填参数
        if(!$inputObj->IsPartner_trade_noSet()) {
            throw new ErrorException("缺少企业转账接口必填参数: partner_trade_no！");
        } else if(!$inputObj->IsDescSet()){
            throw new ErrorException("缺少企业转账接口必填参数: desc！");
        } else if(!$inputObj->IsAmountSet()) {
            throw new ErrorException("缺少企业转账接口必填参数: amount！");
        }else if(!$inputObj->IsEncBankNoSet()) {
            throw new ErrorException("缺少企业转账接口必填参数: enc_bank_no！");
        }else if(!$inputObj->IsEncTrueNameSet()) {
            throw new ErrorException("缺少企业转账接口必填参数: enc_true_name！");
        }else if(!$inputObj->IsBankCodeSet()) {
            throw new ErrorException("缺少企业转账接口必填参数: bank_code！");
        }


        $config = Config::getInstance();

        $inputObj->SetMch_id($config->MCHID);// 商户号
        //$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//设置微信支付签名

        $xml = $inputObj->ToXml();
        $startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, true, $timeOut);
        $obj = new Results();
        $result = $obj->FromXml($response);
        self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
        return $result;
    }

    /**
     * @return bool
     * @throws ErrorException
     *
     * <xml>
     *  <mchid>1496877302</mchid>
     *  <nonce_str><![CDATA[hjgxvnb77b491uqhmtqj6qf6yc4jzzto]]></nonce_str>
     *  <sign_type><![CDATA[MD5]]></sign_type>
     *  <sign><![CDATA[9CD1FE2152964EC9CAD1073743DDAEE5]]></sign>
     * </xml>
     * <xml>
     *  <mchid>1496877302</mchid>
     *  <nonce_str><![CDATA[bv82u0kr5y4nyxouqy3twunkbwr2ehip]]></nonce_str>
     *  <sign><![CDATA[CB259AF07E4195DFB158231BF65A4451]]></sign>
     *  <sign_type><![CDATA[MD5]]></sign_type>
     * </xml>
     * <xml>
     *  <mch_id>1496877302</mch_id>
     *  <nonce_str><![CDATA[ne08ilytz2mxh2pvrah6lcdxf3g5evxs]]></nonce_str>
     *  <sign><![CDATA[7F8D06BD5FF42882D78BCCE28AFFD160]]></sign>
     *  <sign_type><![CDATA[MD5]]></sign_type>
     * </xml>
     * mch_id=1496877302&nonce_str=xgs4lxj3l3ucxly25xnegmh12xldx6nw&key=wxpaycoolmall93wjcomgzhtest11234
     */
    public static function makePublicKey()
    {
        $url = 'https://fraud.mch.weixin.qq.com/risk/getpublickey';
        $config = Config::getInstance();

        $inputObj = new TransfersBank();
        $inputObj->SetMch_id($config->MCHID);
        $inputObj->SetNonce_str(Api::getNonceStr());
        $inputObj->SetSignType('MD5');
        $inputObj->SetSign();

        $xml = $inputObj->ToXml();

        $response = self::postXmlCurl($xml, $url, true);
        $obj = new Results();
        $result = $obj->FromXml($response);

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return $result['pub_key'];
        }
        return false;
    }
}


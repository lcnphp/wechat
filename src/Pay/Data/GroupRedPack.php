<?php

namespace Weirin\Wechat\Pay\Data;

use Weirin\Wechat\Pay\Config;
use Weirin\Wechat\Pay\Data\RedPack;


/**
 * 裂变红包输入对象
 * 业务介绍:
 * 1、商户调用接口时，通过指定发送金额以及指定一位发送对象的方式发放一组裂变红包
 * 2、指定发送对象领取到红包后，资金直接进入微信零钱，带给用户微信支付原生的流畅体验
 * 3、指定发送对象能够将组合中的剩余红包分享给好友，好友可继续领取，形成传播效应，放大企业品牌价值
 * @author weigong
 *
 */
class GroupRedPack extends RedPack
{
    /**
     * 设置接收红包的种子用户（首个用户）, 用户在wxappid下的openid
     * @param string $value
     **/
    public function SetRe_openid($value)
    {
        $this->values['re_openid'] = $value;
    }
    /**
     * 获取接收红包的种子用户
     * @return 值
     **/
    public function GetRe_openid()
    {
        return $this->values['re_openid'];
    }
    /**
     * 判断接收红包的种子用户是否存在
     * @return true 或 false
     **/
    public function IsRe_openidSet()
    {
        return array_key_exists('re_openid', $this->values);
    }

    /**
     * 设置红包发放总金额，即一组红包金额总和，包括分享者的红包和裂变的红包，单位分
     * 注意: 每个红包的平均金额必须在1.00元到200.00元之间
     * @param string $value
     **/
    public function SetTotal_amount($value)
    {
        $this->values['total_amount'] = $value;
    }
    /**
     * 获取红包发放总金额
     * @return
     **/
    public function GetTotal_amount()
    {
        return $this->values['total_amount'];
    }
    /**
     * 判断红包发放总金额是否存在
     * @return true 或 false
     **/
    public function IsTotal_amountSet()
    {
        return array_key_exists('total_amount', $this->values);
    }

    /**
     * 设置红包发放总人数，即总共有多少人可以领到该组红包（包括分享者）
     * 注意: total_num 必须介于(包括)3到20之间
     * @param string $value
     **/
    public function SetTotal_num($value)
    {
        $this->values['total_num'] = $value;
    }
    /**
     * 获取红包发放总人数
     * @return
     **/
    public function GetTotal_num()
    {
        return $this->values['total_num'];
    }
    /**
     * 判断红包发放总人数是否存在
     * @return true 或 false
     **/
    public function IsTotal_numSet()
    {
        return array_key_exists('total_num', $this->values);
    }

    /**
     * 设置红包金额设置方式
     * 目前只有一种: ALL_RAND—全部随机,商户指定总金额和红包发放总人数，由微信支付随机计算出各红包金额
     * @param string $value
     **/
    public function SetAmt_type($value)
    {
        $this->values['amt_type'] = $value;
    }
    /**
     * 获取红包金额设置方式
     * @return
     **/
    public function GetAmt_type()
    {
        return $this->values['amt_type'];
    }
    /**
     * 判断红包金额设置方式是否存在
     * @return true 或 false
     **/
    public function IsAmt_typeSet()
    {
        return array_key_exists('amt_type', $this->values);
    }
}

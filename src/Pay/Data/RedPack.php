<?php

namespace Weirin\Wechat\Pay\Data;

use Weirin\Wechat\Pay\Config;
use Weirin\Wechat\Pay\Data\Base;

/**
 * 现金红包输入对象
 * 业务介绍:
 * 1、商户调用接口时，通过指定发送对象以及发送金额的方式发放红包，这样的方式，允许商户灵活的应用于各种各样丰富的活动场景
 * 2、领取到红包后，用户的资金直接进入微信零钱，避免繁复的领奖流程，带给用户微信支付原生的流畅体验
 * @author weigong
 *
 */
class RedPack extends Base
{
    /**
     * 设置微信支付分配的商户号
     * @param string $value
     **/
    public function SetMch_id($value)
    {
        $this->values['mch_id'] = $value;
    }
    /**
     * 获取微信支付分配的商户号的值
     * @return 值
     **/
    public function GetMch_id()
    {
        return $this->values['mch_id'];
    }
    /**
     * 判断微信支付分配的商户号是否存在
     * @return true 或 false
     **/
    public function IsMch_idSet()
    {
        return array_key_exists('mch_id', $this->values);
    }

    /**
     * 设置微信分配的公众账号ID
     * @param string $value
     **/
    public function SetAppid($value)
    {
        $this->values['wxappid'] = $value;
    }
    /**
     * 获取微信分配的公众账号ID的值
     * @return 值
     **/
    public function GetAppid()
    {
        return $this->values['wxappid'];
    }
    /**
     * 判断微信分配的公众账号ID是否存在
     * @return true 或 false
     **/
    public function IsAppidSet()
    {
        return array_key_exists('wxappid', $this->values);
    }

    /**
     * 设置商户订单号(每个订单号必须唯一)
     * 组成: mch_id + yyyymmdd + 10位一天内不能重复的数字
     * 备注: 接口根据商户订单号支持重入,如出现超时可再调用
     * @param string $value
     **/
    public function SetMch_billno($value)
    {
        $this->values['mch_billno'] = $value;
    }
    /**
     * 获取商户订单号
     * @return 值
     **/
    public function GetMch_billno()
    {
        return $this->values['mch_billno'];
    }
    /**
     * 判断商户订单号是否存在
     * @return true 或 false
     **/
    public function IsMch_billnoSet()
    {
        return array_key_exists('mch_billno', $this->values);
    }

    /**
     * 设置随机字符串，不长于32位。推荐随机数生成算法
     * @param string $value
     **/
    public function SetNonce_str($value)
    {
        $this->values['nonce_str'] = $value;
    }
    /**
     * 获取随机字符串
     * @return 值
     **/
    public function GetNonce_str()
    {
        return $this->values['nonce_str'];
    }
    /**
     * 判断随机字符串是否存在
     * @return true 或 false
     **/
    public function IsNonce_strSet()
    {
        return array_key_exists('nonce_str', $this->values);
    }

    /**
     * 设置红包发送者名称, 默认使用: 商户名称
     * @param string $value
     **/
    public function SetSend_name($value)
    {
        $this->values['send_name'] = $value;
    }
    /**
     * 获取红包发送者名称
     * @return 值
     **/
    public function GetSend_name()
    {
        return $this->values['send_name'];
    }
    /**
     * 判断红包发送者名称是否存在
     * @return true 或 false
     **/
    public function IsSend_nameSet()
    {
        return array_key_exists('send_name', $this->values);
    }

    /**
     * 设置接受红包的用户(用户在wxappid下的openid)
     * @param string $value
     **/
    public function SetRe_openid($value)
    {
        $this->values['re_openid'] = $value;
    }
    /**
     * 获取接受红包的用户
     * @return 值
     **/
    public function GetRe_openid()
    {
        return $this->values['re_openid'];
    }
    /**
     * 判断接受红包的用户是否存在
     * @return true 或 false
     **/
    public function IsRe_openidSet()
    {
        return array_key_exists('re_openid', $this->values);
    }

    /**
     * 设置付款金额, 单位为分
     * @param string $value
     **/
    public function SetTotal_amount($value)
    {
        $this->values['total_amount'] = $value;
    }
    /**
     * 获取付款金额
     * @return
     **/
    public function GetTotal_amount()
    {
        return $this->values['total_amount'];
    }
    /**
     * 判断付款金额是否存在
     * @return true 或 false
     **/
    public function IsTotal_amountSet()
    {
        return array_key_exists('total_amount', $this->values);
    }

    /**
     * 设置红包发放总人数
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
     * 设置红包祝福语
     * @param string $value
     **/
    public function SetWishing($value)
    {
        $this->values['wishing'] = $value;
    }
    /**
     * 获取红包祝福语
     * @return
     **/
    public function GetWishing()
    {
        return $this->values['wishing'];
    }
    /**
     * 判断红包祝福语是否存在
     * @return true 或 false
     **/
    public function IsWishingSet()
    {
        return array_key_exists('wishing', $this->values);
    }

    /**
     * 设置调用接口的机器Ip地址
     * @param string $value
     **/
    public function SetClient_ip($value)
    {
        $this->values['client_ip'] = $value;
    }
    /**
     * 获取调用接口的机器Ip地址
     * @return 值
     **/
    public function GetClient_ip()
    {
        return $this->values['client_ip'];
    }
    /**
     * 判断调用接口的机器Ip地址是否存在
     * @return true 或 false
     **/
    public function IsClient_ipSet()
    {
        return array_key_exists('client_ip', $this->values);
    }

    /**
     * 设置活动名称
     * @param string $value
     **/
    public function SetAct_name($value)
    {
        $this->values['act_name'] = $value;
    }
    /**
     * 获取活动名称
     * @return
     **/
    public function GetAct_name()
    {
        return $this->values['act_name'];
    }
    /**
     * 判断活动名称是否存在
     * @return true 或 false
     **/
    public function IsAct_nameSet()
    {
        return array_key_exists('act_name', $this->values);
    }

    /**
     * 设置备注信息
     * @param string $value
     **/
    public function SetRemark($value)
    {
        $this->values['remark'] = $value;
    }
    /**
     * 获取备注信息
     * @return
     **/
    public function GetRemark()
    {
        return $this->values['remark'];
    }
    /**
     * 判断备注信息是否存在
     * @return true 或 false
     **/
    public function IsRemarkSet()
    {
        return array_key_exists('remark', $this->values);
    }
}

<?php

namespace Weirin\Wechat\Pay\Data;

use Weirin\Wechat\Pay\Config;
use Weirin\Wechat\Pay\Data\Base;

/**
 *
 * 代金券输入对象
 * @author weigong
 *
 */
class Coupon extends Base
{
    /**
     * 设置微信分配的公众账号ID
     * @param string $value
     **/
    public function SetAppid($value)
    {
        $this->values['appid'] = $value;
    }
    /**
     * 获取微信分配的公众账号ID的值
     * @return 值
     **/
    public function GetAppid()
    {
        return $this->values['appid'];
    }
    /**
     * 判断微信分配的公众账号ID是否存在
     * @return true 或 false
     **/
    public function IsAppidSet()
    {
        return array_key_exists('appid', $this->values);
    }


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
     * 设置: 商户appid下，某用户的openid。下单前需要调用【网页授权获取用户信息】接口获取到用户的Openid。
     * @param string $value
     **/
    public function SetOpenid($value)
    {
        $this->values['openid'] = $value;
    }
    /**
     * 获取: 商户appid下，某用户的openid
     * @return 值
     **/
    public function GetOpenid()
    {
        return $this->values['openid'];
    }
    /**
     * 判断: 商户appid下，某用户的openid是否存在
     * @return true 或 false
     **/
    public function IsOpenidSet()
    {
        return array_key_exists('openid', $this->values);
    }


    /**
     * 设置代金券批次id
     * @param string $value
     **/
    public function SetCoupon_stock_id($value)
    {
        $this->values['coupon_stock_id'] = $value;
    }
    /**
     * 获取代金券批次id
     * @return 值
     **/
    public function GetCoupon_stock_id()
    {
        return $this->values['coupon_stock_id'];
    }
    /**
     * 判断代金券批次id是否存在
     * @return true 或 false
     **/
    public function IsCoupon_stock_idSet()
    {
        return array_key_exists('coupon_stock_id', $this->values);
    }

    /**
     * 设置openid记录数（目前支持num=1）
     * @param string $value
     **/
    public function SetOpenid_count($value)
    {
        $this->values['openid_count'] = $value;
    }
    /**
     * 获取openid记录数
     * @return 值
     **/
    public function GetOpenid_count()
    {
        return $this->values['openid_count'];
    }
    /**
     * 判断openid记录数是否存在
     * @return true 或 false
     **/
    public function IsOpenid_countSet()
    {
        return array_key_exists('openid_count', $this->values);
    }

    /**
     * 设置商户此次发放凭据号（格式：商户id+日期+流水号），商户侧需保持唯一性
     * @param string $value
     **/
    public function SetPartner_trade_no($value)
    {
        $this->values['partner_trade_no'] = $value;
    }
    /**
     * 获取商户此次发放凭据号
     * @return 值
     **/
    public function GetPartner_trade_no()
    {
        return $this->values['partner_trade_no'];
    }
    /**
     * 判断商户此次发放凭据号是否存在
     * @return true 或 false
     **/
    public function IsPartner_trade_noSet()
    {
        return array_key_exists('partner_trade_no', $this->values);
    }

    /**
     * 设置操作员帐号, 默认为商户号(可在商户平台配置操作员对应的api权限)
     * @param string $value
     **/
    public function SetOp_user_id($value)
    {
        $this->values['op_user_id'] = $value;
    }
    /**
     * 获取操作员帐号
     * @return 值
     **/
    public function GetOp_user_id()
    {
        return $this->values['op_user_id'];
    }
    /**
     * 判断操作员帐号是否存在
     * @return true 或 false
     **/
    public function IsOp_user_idSet()
    {
        return array_key_exists('op_user_id', $this->values);
    }

    /**
     * 设置微信支付分配的终端设备号，商户自定义
     * @param string $value
     **/
    public function SetDevice_info($value)
    {
        $this->values['device_info'] = $value;
    }
    /**
     * 获取微信支付分配的终端设备号
     * @return 值
     **/
    public function GetDevice_info()
    {
        return $this->values['device_info'];
    }
    /**
     * 判断微信支付分配的终端设备号是否存在
     * @return true 或 false
     **/
    public function IsDevice_infoSet()
    {
        return array_key_exists('device_info', $this->values);
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
     * 设置协议版本(默认1.0)
     * @param string $value
     **/
    public function SetVersion($value)
    {
        $this->values['version'] = $value;
    }
    /**
     * 获取协议版本
     * @return 值
     **/
    public function GetVersion()
    {
        return $this->values['version'];
    }
    /**
     * 判断协议版本是否存在
     * @return true 或 false
     **/
    public function IsVersionSet()
    {
        return array_key_exists('version', $this->values);
    }

    /**
     * 设置协议类型【目前仅支持默认XML】
     * @param string $value
     **/
    public function SetType($value)
    {
        $this->values['type'] = $value;
    }
    /**
     * 获取协议类型
     * @return 值
     **/
    public function GetType()
    {
        return $this->values['type'];
    }
    /**
     * 判断协议类型是否存在
     * @return true 或 false
     **/
    public function IsTypeSet()
    {
        return array_key_exists('type', $this->values);
    }

}

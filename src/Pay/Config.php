<?php

namespace Weirin\Wechat\Pay;

/**
 * 配置账号信息
 * Class Config
 * @package Wechat\Pay
 */
class Config
{
    /**
     * =======【基本信息设置】=====================================
     * TODO: 修改这里配置为您自己申请的商户信息
     * 微信公众号信息配置
     *
     * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
     *
     * MCHID：商户号（必须配置，开户邮件中可查看）
     *
     * KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
     * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
     *
     * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置），
     * 获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
     * @var string
     */
    public $APPID;
    public $MCHID;
    public $KEY;
    public $APPSECRET;

    /**
     * =======【证书路径设置】=====================================
     * TODO：设置商户证书路径
     * apiclient_cert.pem
     * apiclient_key.pem
     * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
     * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
     * @var path
     */
    public $SSLCERT_PATH;
    public $SSLKEY_PATH;
    public $PUBKEY_PATH;

    /**
     * =======【curl代理设置】===================================
     * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
     * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
     * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
     * @var unknown_type
     */
    public $CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
    public $CURL_PROXY_PORT = 0;//8080;

    /**
     * =======【上报信息配置】===================================
     * TODO：接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
     * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
     * 开启错误上报。
     * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
     * @var int
     */
    public $REPORT_LEVENL = 1;

    /**
     * @var string
     */
    public $NOTIFY_URL = '';

    //保存类实例的静态成员变量
    /*@inheritdoc*/
    private static $instance;

    /**
     * 单例方法,用于访问实例的公共的静态方法
     * @param array $options
     * @return Config
     */
    public static function getInstance($options = []){

        if(!(self::$instance instanceof self)){
            self::$instance = new self;

            // 使用商家自己的微信支付(根据商城ID从数据库获取相关配置数据)
            if (empty($options)) {
                return self::$instance;
            }
            self::$instance->APPID = $options['appid'] ?? '';
            self::$instance->APPSECRET = $options['appsecret'] ?? '';
            self::$instance->MCHID = $options['mchid'];
            self::$instance->KEY = $options['apikey'];
            self::$instance->SSLCERT_PATH = $options['ssl_cert_path'];
            self::$instance->SSLKEY_PATH = $options['ssl_key_path'];
            self::$instance->PUBKEY_PATH = $options['pub_key_path'] ?? '';
        }

        return self::$instance;

    }

    /**
     *
     * 注意：该方法会清除所有默认设置
     *
     * @param $options
     */
    public function set($options)
    {
        $this->APPID = null;
        $this->MCHID = null;
        $this->KEY = null;
        $this->APPSECRET = null;
        $this->SSLCERT_PATH = null;
        $this->SSLKEY_PATH = null;
        $this->PUBKEY_PATH = null;

        if(isset($options['app_id'])){
            $this->APPID = $options['app_id'];
        }
        if(isset($options['mch_id'])){
            $this->MCHID = $options['mch_id'];
        }
        if(isset($options['api_key'])){
            $this->KEY = $options['api_key'];
        }
        if(isset($options['app_secret'])){
            $this->APPSECRET = $options['app_secret'];
        }
        if(isset($options['sslcert_path'])){
            $this->SSLCERT_PATH = $options['app_secret'];
        }
        if(isset($options['sslkey_path'])){
            $this->SSLKEY_PATH = $options['sslkey_path'];
        }
    }

    /*@inheritdoc*/
    private function __construct()
    {}

    /*@inheritdoc*/
    private function __clone()
    {}
}

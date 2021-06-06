<?php

namespace Weirin\Wechat\Pay\Data;

use Weirin\Wechat\Pay\Config;
use Weirin\Wechat\Pay\Api;
use Weirin\Wechat\Pay\ErrorException;

/**
 *
 * 微信支付-提现付款到银行卡
 * @author widyhu
 *
 */
class TransfersBank extends TransfersOrder
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
     * @return
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
     * 设置收款方银行卡号
     */
    public function SetEncBankNo($value)
    {
        $config = Config::getInstance();
        $publicPem = $this->getPublicKey($config->PUBKEY_PATH);
        $pubkey = openssl_pkey_get_public($publicPem);
        $encryptedBlock = '';
        $encrypted = '';
        openssl_public_encrypt($value, $encryptedBlock, $pubkey, OPENSSL_PKCS1_OAEP_PADDING);
        $str = base64_encode($encrypted.$encryptedBlock);
        $this->values['enc_bank_no'] = $str;
    }
    /**
     * 获取收款方银行卡号
     */
    public function GetEncBankNo()
    {
        return $this->values['enc_bank_no'];
    }
    /**
     * 判断收款方银行卡号是否存在
     * @return true 或 false
     **/
    public function IsEncBankNoSet()
    {
        return array_key_exists('enc_bank_no', $this->values);
    }

    /**
     * 设置收款方用户名
     */
    public function SetEncTrueName($value)
    {
        $config = Config::getInstance();
        $publicPem = $this->getPublicKey($config->PUBKEY_PATH);
        $pubkey = openssl_pkey_get_public($publicPem);
        $encryptedBlock = '';
        $encrypted = '';
        openssl_public_encrypt($value, $encryptedBlock, $pubkey, OPENSSL_PKCS1_OAEP_PADDING);
        $str = base64_encode($encrypted.$encryptedBlock);
        $this->values['enc_true_name'] = $str;
    }
    /**
     * 获取收款方用户名
     */
    public function GetEncTrueName()
    {
        return $this->values['enc_true_name'];
    }
    /**
     * 判断收款方用户名是否存在
     * @return true 或 false
     **/
    public function IsEncTrueNameSet()
    {
        return array_key_exists('enc_true_name', $this->values);
    }
    /**
     * 设置收款方开户行
     */
    public function SetBankCode($value)
    {
        $this->values['bank_code'] = $value;
    }
    /**
     * 获取收款方开户行
     */
    public function GetBankCode()
    {
        return $this->values['bank_code'];
    }
    /**
     * 判断收款方开户行	是否存在
     * @return true 或 false
     **/
    public function IsBankCodeSet()
    {
        return array_key_exists('bank_code', $this->values);
    }

    /**
     * 设置签名类型
     */
    public function SetSignType($value)
    {
        $this->values['sign_type'] = $value;
    }
    /**
     * 获取签名类型
     */
    public function GetSignType()
    {
        return $this->values['sign_type'];
    }
    /**
     * 判断签名类型是否存在
     * @return true 或 false
     **/
    public function IsSignTypeSet()
    {
        return array_key_exists('sign_type', $this->values);
    }

    /**
     * 获取获取RSA公钥
     * @param $pubKeyPath
     * @return
     */
    public static function getPublicKey($pubKeyPath)
    {
        $certDir = dirname($pubKeyPath);
        if (!file_exists($certDir)) {
            mkdir($certDir, 0777, true);
        }

        if (!file_exists($pubKeyPath)) { // 证书文件不存在, 创建并写入文件
            $publicPem = Api::makePublicKey();
            if(empty($publicPem)){
                throw new ErrorException('获取企业RSA公钥失败');
            }
            file_put_contents($pubKeyPath, $publicPem);
            //将PKCS#1 转 PKCS#8:
            exec('openssl rsa -RSAPublicKey_in -in '.$pubKeyPath.' -pubout', $array);
            $publicPem = implode("\n",$array);
            file_put_contents($pubKeyPath, $publicPem);
            //$publicPem = file_get_contents($pubKeyPath);
        }else{
            $publicPem = file_get_contents($pubKeyPath);
        }

        return $publicPem;
    }
}

<?php

namespace Weirin\Wechat\MiniApp;

/**
 * Class BizData
 * @package Wechat\MiniApp
 */
class BizData
{
    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $sessionKey
     * @return bool
     * @throws ErrorException
     */
    public static function decrypt( $encryptedData, $iv, $sessionKey)
    {
        if (strlen($sessionKey) != 24) {
            throw new ErrorException("encodingAesKey 非法");
        }

        if (strlen($iv) != 24) {
            throw new ErrorException("iv 非法");
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $aesKey = base64_decode($sessionKey);
        $result = openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = (array)json_decode( $result, true);
        if( $dataObj  == null || $dataObj['watermark']['appid'] == null) {
            throw new ErrorException("aes 解密失败");
        }

        return $dataObj;
    }

}


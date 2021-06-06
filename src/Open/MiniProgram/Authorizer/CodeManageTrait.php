<?php

namespace Weirin\Wechat\Open\MiniProgram\Authorizer;

use Weirin\Wechat\Http;
use Weirin\Wechat\Urls;

/**
 * 代码管理
 * https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=6455a1d3996285caf5dd205ffe34d83230344055&lang=zh_CN
 * Trait CodeManageTrait
 * @package Weirin\Wechat\Open\MiniProgram\Authorizer
 */
trait CodeManageTrait
{
    /**
     * 为授权的小程序帐号上传小程序代码
     * @param $data
     * @return bool
     */
    public function commit(array $data)
    {
        $url = Urls::api('wxa/commit', [
            'access_token' => $this->getAccessToken()
        ]);

        if ($result = $this->httpPost($url, $data)) {
            return $result['errcode'] == 0;
        }
        return false;
    }

    /**
     * 获取体验小程序的体验二维码
     * @param string $path
     * @return bool|mixed
     */
    public function getQrcode(string $path = '')
    {
        $url = Urls::api('wxa/get_qrcode', [
            'access_token' => $this->getAccessToken(),
            'path' => $path,
        ]);

        return Http::get($url);
    }


    /**
     * 获取授权小程序帐号已设置的类目
     * @return bool|mixed
     */
    public function getCategory()
    {
        $url = Urls::api('wxa/get_category', [
            'access_token' => $this->getAccessToken(),
        ]);

        if($result = $this->httpGet($url)){
            return $result;
        }
    }

    /**
     * 获取小程序的第三方提交代码的页面配置
     * @return bool|mixed
     */
    public function getPage()
    {
        $url = Urls::api('wxa/get_page', [
            'access_token' => $this->getAccessToken(),
        ]);

        if($result = $this->httpGet($url)){
            return $result;
        }
    }

    /**
     * 将第三方提交的代码包提交审核（仅供第三方开发者代小程序调用）
     * @param $data
     * @return bool
     */
    public function submitAudit(array $data)
    {
        $url = Urls::api('wxa/submit_audit', [
            'access_token' => $this->getAccessToken()
        ]);

        if ($result = $this->httpPost($url, $data)) {
            return $result;
        }
    }

    /**
     * 查询某个指定版本的审核状态（仅供第三方代小程序调用）
     * @param array $data
     * @return bool|mixed
     */
    public function getAuditstatus(array $data)
    {
        $url = Urls::api('wxa/get_auditstatus', [
            'access_token' => $this->getAccessToken(),
        ]);

        if ($result = $this->httpPost($url, $data)) {
            return $result;
        }
    }

    /**
     * 查询最新一次提交的审核状态
     * @return bool|mixed
     */
    public function getLatestAuditstatus()
    {
        $url = Urls::api('wxa/get_latest_auditstatus', [
            'access_token' => $this->getAccessToken(),
        ]);

        if($result = $this->httpGet($url)){
            return $result;
        }
    }

    /**
     * 发布已通过审核的小程序（仅供第三方代小程序调用）
     * @return bool|mixed
     */
    public function release()
    {
        $url = Urls::api('wxa/release', [
            'access_token' => $this->getAccessToken(),
        ]);

        if ($result = $this->httpPost($url)) {
            return $result;
        }
    }

    /**
     * 小程序版本回退（仅供第三方代小程序调用）
     * @return bool|mixed
     */
    public function revertcoderelease()
    {
        $url = Urls::api('wxa/revertcoderelease', [
            'access_token' => $this->getAccessToken(),
        ]);

        if ($result = $this->httpGet($url)) {
            return $result;
        }
    }

    /**
     * 小程序审核撤回
     * @return bool|mixed
     */
    public function undocodeaudit()
    {
        $url = Urls::api('wxa/undocodeaudit', [
            'access_token' => $this->getAccessToken(),
        ]);

        if($result = $this->httpGet($url)){
            return $result;
        }
    }

    /**
     * 查询服务商的当月提审限额（quota）和加急次数
     * @return bool|mixed
     */
    public function queryquota()
    {
        $url = Urls::api('wxa/queryquota', [
            'access_token' => $this->getAccessToken(),
        ]);

        if($result = $this->httpGet($url)){
            return $result;
        }
    }
}
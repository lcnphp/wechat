<?php

namespace Weirin\Wechat\Open\MiniProgram\Authorizer;

use Weirin\Wechat\Urls;

/**
 * 代小程序实现业务-修改服务器地址
 * Trait DomainTrait
 * @package Weirin\Wechat\Open\MiniProgram\Authorizer
 */
trait DomainTrait
{
    /**
     * @param string $domain
     * @return bool
     */
    public function setWebviewDomain(string $domain)
    {
        return $this->modifyWebviewDomain('set', $domain);
    }

    /**
     * @param string $domain
     * @return bool
     */
    public function deleteWebviewDomain(string $domain)
    {
        return $this->modifyWebviewDomain('delete', $domain);
    }

    /**
     * @param string $domain
     * @return bool
     */
    public function addWebviewDomain(string $domain)
    {
        return $this->modifyWebviewDomain('add', $domain);
    }

    /**
     * https://api.weixin.qq.com/wxa/setwebviewdomain?access_token=ACCESS_TOKEN
     * @param $action
     * @param string $domain
     * @return bool
     */
    private function modifyWebviewDomain($action, string $domain)
    {
        $url = Urls::api('wxa/setwebviewdomain', [
            'access_token' => $this->getAccessToken()
        ]);
        $data = [
            'action' => $action,
            'webviewdomain' => $domain,
        ];
        if ($result = $this->httpPost($url, $data)) {
            return $result['errcode'] == 0;
        }
        return false;
    }

    /**
     * @param array $domains
     * @return bool
     */
    public function setDomains(array $domains)
    {
        return $this->modifyDomains('set', $domains);
    }

    /**
     * @param array $domains
     * @return bool
     */
    public function deleteDomains(array $domains)
    {
        return $this->modifyDomains('delete', $domains);
    }

    /**
     * @param array $domains
     * @return bool
     */
    public function addDomains(array $domains)
    {
        return $this->modifyDomains('add', $domains);
    }

    /**
     *
     * https://api.weixin.qq.com/wxa/modify_domain?access_token=ACCESS_TOKEN
     *
     * @param $action
     * @param array $domains
     * @return bool
     */
    private function modifyDomains(string $action, array $domains)
    {
        $url = Urls::api('wxa/modify_domain', [
            'access_token' => $this->getAccessToken()
        ]);
        $data = [
            'action' => $action,
            'requestdomain' => $domains['requestdomain'],
            'wsrequestdomain' => $domains['wsrequestdomain'],
            'uploaddomain' => $domains['uploaddomain'],
            'downloaddomain' => $domains['downloaddomain']
        ];
        if ($result = $this->httpPost($url, $data)) {
            return $result['errcode'] == 0;
        }
        return false;
    }
}
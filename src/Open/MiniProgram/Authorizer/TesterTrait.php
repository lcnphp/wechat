<?php

namespace Weirin\Wechat\Open\MiniProgram\Authorizer;

use Weirin\Wechat\Urls;

/**
 * 成员管理(体验者)
 * Trait TesterTrait
 * @package Weirin\Wechat\Open\MiniProgram\Authorizer
 */
trait TesterTrait
{
    /*
     * https://api.weixin.qq.com/wxa/bind_tester?access_token=ACCESS_TOKEN;
     * 绑定小程序的体验者
     * @param string $wechatId
     */
    public function bindTester(string $wechatId)
    {
        $url = Urls::api('wxa/bind_tester', [
            'access_token' => $this->getAccessToken()
        ]);
        $data = [
            'wechatid' => $wechatId,
        ];
        if ($result = $this->httpGet($url, $data)) {
            return $result['errmsg'] == 0;
        }
        return false;
    }

    /*
     * https://api.weixin.qq.com/wxa/unbind_tester?access_token=ACCESS_TOKEN
     * 解除绑定小程序的体验者
     * @param string $wechatId 微信号
     */
    public function unbindTester(string $wechatId)
    {
        $url = Urls::api('wxa/unbind_tester', [
            'access_token' => $this->getAccessToken()
        ]);
        $data = [
            'wechatid' => $wechatId,
        ];
        if ($result = $this->httpGet($url, $data)) {
            return $result['errmsg'] == 0;
        }
        return false;
    }

    /**
     * https://api.weixin.qq.com/wxa/memberauth?access_token=ACCESS_TOKEN
     * 获取小程序体验者列表
     * @return bool|mixed
     */
    public function getTesters()
    {
        $url = Urls::api('wxa/memberauth', [
            'access_token' => $this->getAccessToken()
        ]);
        $data = [
            'action' => 'get_experiencer',
        ];
        if ($result = $this->httpGet($url, $data)) {
            return $result;
        }
        return false;
    }
}
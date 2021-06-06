<?php

namespace Weirin\Wechat\Open\MiniProgram\Component;

use Weirin\Wechat\Log;
use Weirin\Wechat\Http;
use Weirin\Wechat\Urls;
use Weirin\Wechat\ErrorException;

/**
 * Class PHPSDK
 * @package Weirin\Wechat\Open\MiniProgram\Component
 */
class Facade
{
    //const AUTH_TYPE_MP = 1;//公众号
    //const AUTH_TYPE_MINIPROGRAM = 2;//小程序
    //const AUTH_TYPE_MP_MINIPROGRAM = 3;//公众号和小程序

    protected $appid;
    protected $appsecret;
    protected $verifyTicket;
    protected $authorizerAppid;
    protected $accessToken;

    /**
     * Facade constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->appid           = isset($options['component_appid']) ? $options['component_appid'] : '';
        $this->appsecret       = isset($options['component_appsecret']) ? $options['component_appsecret'] : '';
        $this->verifyTicket    = isset($options['component_verify_ticket']) ? $options['component_verify_ticket'] : '';
        $this->authorizerAppid = isset($options['authorizer_appid']) ? $options['authorizer_appid'] : '';
    }

    /**
     * @return string
     */
    public function getAppid()
    {
        return (string)$this->appid;
    }

    /**
     * @return string
     */
    public function getAuthorizerAppid()
    {
        return (string)$this->authorizerAppid;
    }

    /**
     *
     * 获取预授权码
     * 只有十分钟有效时间，不做缓存
     *
     * @return string|null
     * @throws ErrorException
     */
    public function getPreAuthCode()
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        $url = Urls::api('cgi-bin/component/api_create_preauthcode', [
            'component_access_token' => $this->getAccessToken()
        ]);
        $data = [
            "component_appid" => $this->getAppid(),
        ];
        if ($result = $this->httpPost($url, $data)) {
            return (string)$result['pre_auth_code'];
        }
    }

    /**
     *
     * 使用授权码换取公众号的接口调用凭据和授权信息
     *
     * @param string $authCode 授权跳转后获取到
     * @return array|null {authorizer_appid,authorizer_access_token,expires_in,authorizer_refresh_token,func_info[]}
     * @throws ErrorException
     */
    public function getAuthorizationInfo(string $authCode)
    {
        if (!$this->checkAccessToken()) {
            return null;
        }
        $url = Urls::api('cgi-bin/component/api_query_auth', [
            'component_access_token' => $this->getAccessToken()
        ]);
        $data = [
            "component_appid" => $this->appid,
            "authorization_code" => $authCode,
        ];
        if ($result = $this->httpPost($url, $data)) {
            return $result['authorization_info'];
        }
    }

    /**
     *
     * 公众号运营者授权页
     *
     * 要授权的帐号类型(auth type):
     *   1.则商户扫码后，手机端仅展示公众号,
     *   2.表示仅展示小程序，
     *   3.表示公众号和小程序都展示。
     * 如果为未指定，则默认小程序和公众号都展示。
     * 第三方平台开发者可以使用本字段来控制授权的帐号类型。
     *
     * @param string $callback 授权回调URI
     * @param int $authType 1.则商户扫码后，手机端仅展示公众号,2.表示仅展示小程序,3.表示公众号和小程序都展示。
     * @return string
     */
    public function getAuthorizerAuthUrl(string $callback, int $authType = 3)
    {
        $preAuthCode = $this->getPreAuthCode();
        Log::debug('授权方授权页，获取PreAuthCode：' . $preAuthCode);
        return (string)Urls::mp('cgi-bin/componentloginpage', [
            'component_appid' => $this->appid,
            'pre_auth_code' => $preAuthCode,
            'redirect_uri' => $callback,
            'auth_type' => $authType
        ]);
    }

    /**
     *
     * https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=xxxx
     * 获取授权应用的信息
     *
     * @param string $authorizerAppid
     * @return bool|mixed
     * @throws ErrorException
     */
    public function getAuthorizerInfo(string $authorizerAppid = '')
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        if ($authorizerAppid == '') {
            $authorizerAppid = $this->getAuthorizerAppid();
        }
        $url = Urls::api('cgi-bin/component/api_get_authorizer_info',  [
            'component_access_token' => $this->getAccessToken()
        ]);
        $data = [
            "component_appid" => $this->appid,
            "authorizer_appid" => $authorizerAppid,
        ];
        if ($result = $this->httpPost($url, $data)) {
            return $result["authorizer_info"];

        }
    }

    /**
     *
     * https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option?component_access_token=xxxx
     * 获取授权应用的选项设置
     *
     * @param string $optionName
     * @param string $authorizerAppid
     * @return bool|null
     * @throws ErrorException
     */
    public function getAuthorizerOption(string $optionName, string $authorizerAppid = '')
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        if ($authorizerAppid == '') {
            $authorizerAppid = $this->getAuthorizerAppid();
        }
        $url = Urls::api('cgi-bin/component/api_get_authorizer_option',  [
            'component_access_token' => $this->getAccessToken()
        ]);
        $data = [
            "component_appid" => $this->getAppid(),
            "authorizer_appid" => $authorizerAppid,
            "option_name" => $optionName,
        ];

        if ($result = $this->httpPost($url, $data)) {
            return $result["option_value"];

        }
    }

    /**
     *
     * https://api.weixin.qq.com/cgi-bin/component/ api_set_authorizer_option?component_access_token=xxxx
     * 设置授权应用的选项
     *
     * @param string $optionName
     * @param string $optionValue
     * @param string $authorizerAppid
     * @return bool
     * @throws ErrorException
     */
    public function setAuthorizerOption(string $optionName, string $optionValue, string $authorizerAppid = '')
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        if ($authorizerAppid == '') {
            $authorizerAppid = $this->getAuthorizerAppid();
        }
        $url = Urls::api('cgi-bin/component/api_set_authorizer_option',  [
            'component_access_token' => $this->getAccessToken()
        ]);
        $data = [
            "component_appid" => $this->getAppid(),
            "authorizer_appid" => $authorizerAppid,
            "option_name" => $optionName,
            "option_value" => $optionValue,
        ];
        if ($result = $this->httpPost($url, $data)) {
            return $result["errcode"] == 0;
        }
    }

    /**
     * 获取代码草稿列表
     * @return bool|mixed
     */
    public function getTemplateDraftList()
    {
        if (!$this->checkAccessToken()) {
            return false;
        }

        $url = Urls::api('wxa/gettemplatedraftlist', [
            'access_token' => $this->getAccessToken()
        ]);
        if ($result = $this->httpGet($url)) {
            return $result;
        }
    }

    /**
     * 将草稿添加到代码模板库
     * @param string $draftId
     * @return bool|mixed
     */
    public function addTemplate(string $draftId)
    {
        if (!$this->checkAccessToken()) {
            return false;
        }

        $url = Urls::api('wxa/addtotemplate', [
            'access_token' => $this->getAccessToken(),
            'draft_id' => $draftId
        ]);
        if ($result = $this->httpPost($url)) {
            return $result;
        }
    }

    /**
     * 获取代码模板列表
     * @return bool|mixed
     */
    public function getTemplateList()
    {
        if (!$this->checkAccessToken()) {
            return false;
        }

        $url = Urls::api('wxa/gettemplatelist', [
            'access_token' => $this->getAccessToken()
        ]);
        if ($result = $this->httpGet($url)) {
            return $result;
        }
    }

    /**
     * 删除指定代码模板
     * @param string $templateId
     * @return bool|mixed
     */
    public function deleteTemplate(string $templateId)
    {
        if (!$this->checkAccessToken()) {
            return false;
        }

        $url = Urls::api('wxa/deletetemplate', [
            'access_token' => $this->getAccessToken(),
            'template_id' => $templateId
        ]);
        if ($result = $this->httpPost($url)) {
            return $result;
        }
    }

    /**
     * 获取component_access_token
     * @param bool $forceRefresh
     * @return string
     */
    public function checkAccessToken($forceRefresh = false)
    {
        if ($forceRefresh || !$this->accessToken){
            if($result = $this->getAccessToken($forceRefresh)){
                $this->accessToken = $result;
                return (string)$this->accessToken;
            }
        }

        return $this->accessToken;
    }

    /**
     * 获取文件中的AccessToken
     * @return string
     */
    public function getAccessToken($forceRefresh = false)
    {
        if($forceRefresh || !$this->accessToken){
            $this->accessToken = AccessToken::get($this->appid, $this->appsecret, $this->verifyTicket, $forceRefresh);
        }
        return (string)$this->accessToken;
    }

    /**
     * @param string $url
     * @param array $data
     * @return array|null
     */
    public function httpPost(string $url, array $data)
    {
        $data = json_encode($data);
        $result = Http::post($url, $data);
        if ($result) {
            $array = json_decode($result, true);
            Log::debug("httpPost: result=>" . $result);
            if(isset($array['errcode']) && $array['errcode'] == 40001) {
                Log::warn("httpPost: 检测到component_access_token无效错误, 自动恢复!");
                if ($this->checkAccessToken(true)) {
                    $result = Http::post($url, $data);
                }
            }
        }
        if ($result) {
            $array = json_decode($result, true);
            if(isset($array['errcode']) && $array['errcode'] > 0) {
                throw new ErrorException($array['errmsg'], $array['errcode']);
            }
            return $array;
        }
    }

    /**
     * @param string $url
     * @return mixed
     * @throws ErrorException
     */
    public function httpGet(string $url)
    {
        $result = Http::get($url);
        if ($result) {
            $array = json_decode($result,true);
            Log::debug("httpGet: result=>" . $result);
            if(!empty($array['errcode']) && $array['errcode'] == 40001) {
                Log::debug("httpGet: 检测到access_token无效错误, 自动恢复!");
                if ($this->checkAccessToken(true)) {
                    $result = Http::get($url);
                }
            }
        }
        if ($result) {
            $array = json_decode($result, true);
            if(isset($array['errcode']) && $array['errcode'] > 0) {
                throw new ErrorException($array['errmsg'], $array['errcode']);
            }
            return $array;
        }
    }
}
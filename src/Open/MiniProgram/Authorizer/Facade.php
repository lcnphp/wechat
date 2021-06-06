<?php

namespace Weirin\Wechat\Open\MiniProgram\Authorizer;

use Weirin\Wechat\Log;
use Weirin\Wechat\Http;
use Weirin\Wechat\ErrorException;
use Weirin\Wechat\Open\MiniProgram\Component\Facade as Component;
use function json_encode;
use function json_decode;

/**
 * Class Facade
 * @package Weirin\Wechat\Open\MiniProgram\Authorizer
 */
class Facade
{
    /**
     * 域名设置
     */
    use DomainTrait;
    /**
     * 发送消息
     */
    use SendMessageTrait;
    /**
     * 体验者
     */
    use TesterTrait;
    /**
     * 用户
     */
    use UserTrait;
    /**
     * 代码管理
     */
    use CodeManageTrait;
    /**
     * 内容安全
     */
    use SecCheckTrait;

    /**
     * @var string
     */
    protected $authorizerAppid;
    /**
     * @var string
     */
    protected $accessToken;
    /**
     * @var Component
     */
    protected $component;

    /**
     * Facade constructor.
     * @param Component $component
     * @param array $options
     */
    public function __construct(Component $component, array $options)
    {
        $this->authorizerAppid = isset($options['authorizer_appid']) ? (string)$options['authorizer_appid'] : '';
        $this->component = $component;
    }

    /**
     * 保存authorizer_access_token信息到缓存
     * @param $authorizerAppid
     * @param $accessToken
     * @param $expiresIn
     * @param $authorizerRefreshToken
     */
    public function saveAccessToken($authorizerAppid, $accessToken, $expiresIn, $authorizerRefreshToken)
    {
        AccessToken::set($authorizerAppid, $accessToken, $expiresIn, $authorizerRefreshToken);
    }

    /**
     * 获取 Authorizer Access Token
     * @param bool $forceRefresh
     * @param string $authorizerAppid
     * @return string|null
     */
    public function getAccessToken($forceRefresh = false, $authorizerAppid = '')
    {
        if (!$this->component->checkAccessToken()) {
            return false;
        }
        if($forceRefresh || !$this->accessToken){
            if ($authorizerAppid == '') {
                $authorizerAppid = $this->authorizerAppid;
            }
            $this->accessToken = AccessToken::get(
                $this->component->getAppid(),
                $this->component->getAccessToken(),
                $authorizerAppid,
                $forceRefresh
            );
        }
        return $this->accessToken;
    }

    /**
     * 通用访问令牌检测方法
     * @return string|null
     */
    public function checkAccessToken($forceRefresh = false)
    {
        if($forceRefresh || !$this->accessToken) {
            if($result = $this->getAccessToken($forceRefresh)){
                return $result;
            }
        }
    }

    /**
     * 封装一个较为稳妥的Http post请求接口
     * 说明: 可以自动纠正一次40001错误(access_token无效)
     * @param string $url
     * @param array $data
     * @return mixed
     * @throws ErrorException
     */
    public function httpPost(string $url, array $data = [])
    {
        if (!empty($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $data = '{}';
        }

        $result = Http::post($url, $data);

        if ($result) {
            $array = json_decode($result,true);
            Log::debug("httpPost: result=>" . $result);
            if(!empty($array['errcode']) && $array['errcode'] == 40001) {
                Log::debug("httpPost: 检测到access_token无效错误, 自动恢复!");
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

    /**
     * 封装一个较为稳妥的Http post请求接口
     * 说明: 可以自动纠正一次40001错误(access_token无效)
     * @param string $url
     * @param array $data
     * @return mixed
     * @throws ErrorException
     */
    public function httpUpload(string $url, array $data = [])
    {
        $result = Http::upload($url, $data);
        if ($result) {
            $array = json_decode($result,true);
            Log::debug("httpPost: result=>" . $result);
            if(!empty($array['errcode']) && $array['errcode'] == 40001) {
                Log::debug("httpPost: 检测到access_token无效错误, 自动恢复!");
                if ($this->checkAccessToken(true)) {
                    $result = Http::upload($url, $data);
                }
            }
        }
        if ($result) {
            $array = json_decode($result, true);
            if(isset($array['errcode']) && $array['errcode'] > 0) {
                throw new \ErrorException($array['errmsg'], $array['errcode']);
            }
            return $array;
        }
    }
}
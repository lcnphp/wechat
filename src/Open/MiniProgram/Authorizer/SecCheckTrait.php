<?php

namespace Weirin\Wechat\Open\MiniProgram\Authorizer;

use Weirin\Wechat\Urls;

/**
 * 内容安全
 *
 * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/sec-check/security.imgSecCheck.html
 *
 * Trait SecCheckTrait
 * @package Weirin\Wechat\Open\MiniProgram\Authorizer
 */
trait SecCheckTrait
{
    /**
     *
     * @param string $filepath
     * @return bool|mixed
     */
    public function imgSecCheck(string $filepath)
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        $url = Urls::api('wxa/img_sec_check', [
            'access_token' => $this->getAccessToken(),
        ]);

        if (class_exists ( '\CURLFile' )) {//关键是判断curlfile,官网推荐php5.5或更高的版本使用curlfile来实例文件
            $data = [
                'media' => new \CURLFile (realpath($filepath), 'image/jpeg')
            ];
        } else {
            $data = [
                'media' => '@' . realpath($filepath)
            ];
        }

        if ($result = $this->httpUpload($url, $data)) {
            return $result;
        }
    }

    /**
     *
     * @param string $mediaUrl
     * @param int $mediaType 1:音频;2:图片
     * @return bool|mixed
     */
    public function mediaCheckAsync(string $mediaUrl, int $mediaType)
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        $url = Urls::api('wxa/media_check_async', [
            'access_token' => $this->getAccessToken(),
        ]);

        $data = [
            'media_url' => $mediaUrl,
            'media_type' => $mediaType
        ];

        if ($result = $this->httpPost($url, $data)) {
            return $result;
        }
    }

    /**
     *
     * @param string $content
     * @return bool|mixed
     */
    public function msgSecCheck(string $content)
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        $url = Urls::api('wxa/msg_sec_check', [
            'access_token' => $this->getAccessToken(),
        ]);

        $data = [
            'content' => $content,
        ];

        if ($result = $this->httpPost($url, $data)) {
            return $result;
        }
    }
}
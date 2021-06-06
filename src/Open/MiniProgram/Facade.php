<?php

namespace Weirin\Wechat\Open\MiniProgram;

use Weirin\Wechat\Open\MiniProgram\Authorizer\Facade as Authorizer;
use Weirin\Wechat\Open\MiniProgram\Component\Facade as Component;
use Weirin\Wechat\Open\MiniProgram\Message\WXBizMsgCrypt as MessageCrypt;
use function file_get_contents;
use function simplexml_load_string;

/**
 * Class PHPSDK
 * @package Weirin\Wechat\Open\MiniProgram
 */
class Facade
{
    const INFOTYPE_TICKET           = 'component_verify_ticket';
    const INFOTYPE_AUTHORIZED       = 'authorized';
    const INFOTYPE_UNAUTHORIZED     = 'unauthorized';
    const INFOTYPE_UPDATEAUTHORIZED = 'updateauthorized';

    public $authorizer;
    public $component;
    public $messageCrypt;

    protected $token;
    protected $encodingAesKey;
    protected $componentAppid;
    protected $componentAppsecret;
    protected $componentVerifyTicket;
    protected $authorizerAppid;

    private $receive;

    /**
     * PHPSDK constructor.
     * @param $options
     */
    public function __construct(array $options)
    {
        $this->token                 = isset($options['token']) ? $options['token'] : '';
        $this->encodingAesKey        = isset($options['encodingaeskey']) ? $options['encodingaeskey'] : '';
        $this->componentAppid        = isset($options['component_appid']) ? $options['component_appid'] : '';
        $this->componentAppsecret    = isset($options['component_appsecret']) ? $options['component_appsecret'] : '';
        $this->authorizerAppid       = isset($options['authorizer_appid']) ? $options['authorizer_appid'] : '';
        $this->componentVerifyTicket = isset($options['component_verify_ticket']) ? $options['component_verify_ticket'] : '';

        $this->messageCrypt = $this->createMessageCrypt($options);
        $this->component    = $this->createComponent($options);
        $this->authorizer   = $this->createAuthorizer($options);
    }

    /**
     * @param array $options
     * @return MessageCrypt
     */
    public function createMessageCrypt(array $options)
    {
        $token          = isset($options['token']) ? $options['token'] : '';
        $encodingAesKey = isset($options['encodingaeskey']) ? $options['encodingaeskey'] : '';
        $componentAppid = isset($options['component_appid']) ? $options['component_appid'] : '';
        return new MessageCrypt($token, $encodingAesKey,  $componentAppid);
    }

    /**
     * @param array $options
     * @return Component
     */
    public function createComponent(array $options)
    {
        return new Component($options);
    }

    /**
     * @param array $options
     * @return Authorizer
     */
    public function createAuthorizer(array $options)
    {
        if($this->component === null){
            $this->component = $this->createComponent($options);
        }
        return new Authorizer($this->component, $options);
    }

    /**
     * @return Authorizer
     */
    public function getAuthorizer()
    {
        return $this->authorizer;
    }

    /**
     * @return Component
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @return MessageCrypt
     */
    public function getMessageCrypt()
    {
        return $this->messageCrypt;
    }

    /**
     * 获取微信服务器发来的信息
     * @param string $postStr
     * @return $this
     */
    public function getRev(string $form = '')
    {
        if ($this->receive === null) {
            if ($form == '') {
                $form = file_get_contents("php://input");
            }
            if (!empty($form)) {
                $this->receive = (array)simplexml_load_string($form, 'SimpleXMLElement', LIBXML_NOCDATA);
            }
        }
        return $this;
    }

    /**
     * 获取接收消息的类型
     * 示例: $sdk->getRev()->getRevInfoType();
     * @return string
     */
    public function getRevInfoType()
    {
        if (isset($this->receive['InfoType'])){
            return (string)$this->receive['InfoType'];
        }
    }

    /**
     * 获取微信服务器发来的 component_verify_ticket
     * 示例: $sdk->getRev()->getRevComponentVerifyTicket();
     * @return string
     */
    public function getRevComponentVerifyTicket()
    {
        if (isset($this->receive['ComponentVerifyTicket'])) {
            $this->componentVerifyTicket = $this->receive['ComponentVerifyTicket'];
            return (string)$this->componentVerifyTicket;
        }
    }
}



<?php

namespace Weirin\Wechat;

/**
 *
 *  @注意： 这里移除了支付相关的接口
 *
 *	微信公众平台PHP-SDK, 官方API部分
 *  @author  dodge <dodgepudding@gmail.com>
 *  @link https://github.com/dodgepudding/wechat-php-sdk
 *  @version 1.2
 *  usage:
 *   $options = array(
 *			'token'=>'tokenaccesskey', //填写你设定的key
 *			'appid'=>'wxdk1234567890', //填写高级调用功能的app id
 *			'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
 *		);
 *	 $weObj = new Wechat($options);
 *   $weObj->valid();
 *   $type = $weObj->getRev()->getRevType();
 *   switch($type) {
 *   		case Wechat::MSGTYPE_TEXT:
 *   			$weObj->text("hello, I'm wechat")->reply();
 *   			exit;
 *   			break;
 *   		case Wechat::MSGTYPE_EVENT:
 *   			....
 *   			break;
 *   		case Wechat::MSGTYPE_IMAGE:
 *   			...
 *   			break;
 *   		default:
 *   			$weObj->text("help info")->reply();
 *   }
 *   //获取菜单操作:
 *   $menu = $weObj->getMenu();
 *   //设置菜单
 *   $newmenu =  array(
 *   		"button"=>
 *   			array(
 *   				array('type'=>'click','name'=>'最新消息','key'=>'MENU_KEY_NEWS'),
 *   				array('type'=>'view','name'=>'我要搜索','url'=>'http://www.baidu.com'),
 *   				)
 *  		);
 *   $result = $weObj->createMenu($newmenu);
 *
 * Class PHPSDK
 * @package Wechat
 */
class PHPSDK
{
    const MSGTYPE_TEXT = 'text';
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_LOCATION = 'location';
    const MSGTYPE_USER_LOCATION = 'LOCATION';
    const MSGTYPE_LINK = 'link';
    const MSGTYPE_EVENT = 'event';
    const MSGTYPE_MUSIC = 'music';
    const MSGTYPE_NEWS = 'news';
    const MSGTYPE_VOICE = 'voice';
    const MSGTYPE_VIDEO = 'video';
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    const API_URL_GET_USER_CUMULATE = 'https://api.weixin.qq.com/datacube/getusercumulate?';
    const AUTH_URL = '/token?grant_type=client_credential&';
    const MENU_CREATE_URL = '/menu/create?';
    const MENU_GET_URL = '/menu/get?';
    const MEDIA_FOREVER_UPLOAD_URL = '/material/add_material?';
    const MEDIA_DELETE_URL = '/material/del_material?';
    const MEDIA_FOREVER_GET_URL = '/material/get_material?';
    const MEDIA_FOREVER_BATCHGET_URL = '/material/batchget_material?';
    const MEDIA_UPLOAD_URL = '/media/upload?';
    const MEDIA_UPLOADIMG_URL = '/media/uploadimg?';//图片上传接口
    const MENU_DELETE_URL = '/menu/delete?';
    const MEDIA_GET_URL = '/media/get?';
    const MEDIA_GET_JSSDK_URL = '/media/get/jssdk?';
    const QRCODE_CREATE_URL='/qrcode/create?';
    const QR_SCENE = 0;
    const QR_LIMIT_SCENE = 1;
    const QRCODE_IMG_URL='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';
    const USER_GET_URL='/user/get?';
    const USER_INFO_URL='/user/info?';
    const GROUP_GET_URL='/groups/get?';
    const GROUP_CREATE_URL='/groups/create?';
    const GROUP_UPDATE_URL='/groups/update?';
    const GROUP_MEMBER_UPDATE_URL='/groups/members/update?';
    const CUSTOM_SEND_URL='/message/custom/send?';
    const TEMPLATE_SEND_URL='/message/template/send?';
    const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
    const OAUTH_AUTHORIZE_URL = '/authorize?';
    const OAUTH_TOKEN_PREFIX = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
    const OAUTH_REFRESH_URL = '/refresh_token?';
    const OAUTH_USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo?';
    const CUSTOM_SERVICE = '/customservice/getrecord?';
    const GET_TEMPLATE_URL = '/template/api_add_template?'; // 添加模板
    const GET_TEMPLATE_LIST_URL = '/template/get_all_private_template?'; // 获取已添加模板列表
    const DEL_TEMPLATE_URL = '/template/del_private_template?'; // 删除指定ID的模板
    const GET_SHORT_URL = '/shorturl?'; // 长链接转成短链接

    private $token;
    private $appid;
    private $appsecret;
    private $access_token;
    private $user_token;
    private $_msg;
    private $_funcflag = false;
    private $_receive;
    public  $debug =  false;
    private $_logcallback;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->token = isset($options['token'])?$options['token']:'';
        $this->appid = isset($options['appid'])?$options['appid']:'';
        $this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
        $this->debug = isset($options['debug'])?$options['debug']:false;
        $this->_logcallback = isset($options['logcallback'])?$options['logcallback']:false;
    }

    /**
     * For weixin server validation
     * @return bool
     */
    private function checkSignature()
    {
        $signature = isset($_GET["signature"]) ? $_GET["signature"] : '';
        $timestamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : '';
        $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * For weixin server validation
     *
     * @return boolean
     */
    public function validToken()
    {
        return $this->checkSignature();
    }

    /**
     *
     *
     * 设置发送消息
     * @param string|array $msg 消息数组
     * @param bool $append 是否在原消息数组追加
     * @return array
     */
    public function Message($msg = '', $append = false)
    {
        if (is_null($msg)) {
            $this->_msg =array();
        } elseif (is_array($msg)) {
            if ($append)
                $this->_msg = array_merge($this->_msg,$msg);
            else
                $this->_msg = $msg;
            return $this->_msg;
        } else {
            return $this->_msg;
        }
    }

    /**
     * @param $flag
     * @return $this
     */
    public function setFuncFlag($flag)
    {
        $this->_funcflag = $flag;
        return $this;
    }

    /**
     * @param $log
     * @return mixed
     */
    private function log($log)
    {
        if ($this->debug && function_exists($this->_logcallback)) {
            if (is_array($log))
                $log = print_r($log, true);
            return call_user_func($this->_logcallback,$log);
        }
    }

    /**
     * 获取微信服务器发来的信息
     */
    public function getRev()
    {
        if ($this->_receive) return $this;
        $postStr = file_get_contents("php://input");
        if (!empty($postStr)) {
            libxml_disable_entity_loader(true);
            $this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return $this;
    }

    /**
     * 获取微信服务器发来的信息
     */
    public function getRevData()
    {
        return $this->_receive;
    }

    /**
     * 获取消息发送者
     */
    public function getRevFrom()
    {
        if (isset($this->_receive['FromUserName']))
            return $this->_receive['FromUserName'];
        else
            return false;
    }

    /**
     * 获取消息接受者
     */
    public function getRevTo()
    {
        if (isset($this->_receive['ToUserName']))
            return $this->_receive['ToUserName'];
        else
            return false;
    }

    /**
     * 获取接收消息的类型
     */
    public function getRevType()
    {
        if (isset($this->_receive['MsgType']))
            return $this->_receive['MsgType'];
        else
            return false;
    }

    /**
     * 获取消息ID
     */
    public function getRevID()
    {
        if (isset($this->_receive['MsgId']))
            return $this->_receive['MsgId'];
        else
            return false;
    }

    /**
     * 获取模板消息ID
     */
    public function getRevTplID()
    {
        if (isset($this->_receive['MsgID']))
            return $this->_receive['MsgID'];
        else
            return false;
    }

    /**
     * 获取消息发送时间
     */
    public function getRevCtime()
    {
        if (isset($this->_receive['CreateTime']))
            return $this->_receive['CreateTime'];
        else
            return false;
    }

    /**
     * 获取接收消息内容正文
     */
    public function getRevContent()
    {
        if (isset($this->_receive['Content']))
            return $this->_receive['Content'];
        else if (isset($this->_receive['Recognition'])) //获取语音识别文字内容，需申请开通
            return $this->_receive['Recognition'];
        else
            return false;
    }

    /**
     * 获取接收消息图片
     */
    public function getRevPic()
    {
        if (isset($this->_receive['PicUrl']))
            return $this->_receive['PicUrl'];
        else
            return false;
    }

    /**
     * 获取接收消息链接
     */
    public function getRevLink()
    {
        if (isset($this->_receive['Url'])){
            return array(
                'url'=>$this->_receive['Url'],
                'title'=>$this->_receive['Title'],
                'description'=>$this->_receive['Description']
            );
        } else
            return false;
    }

    /**
     * 获取接收地理位置
     */
    public function getRevGeo()
    {
        if (isset($this->_receive['Location_X'])){
            return array(
                'x'=>$this->_receive['Location_X'],
                'y'=>$this->_receive['Location_Y'],
                'scale'=>$this->_receive['Scale'],
                'label'=>$this->_receive['Label']
            );
        } else
            return false;
    }

    /**
     * 获取上报地理位置事件
     */
    public function getRevEventGeo()
    {
        if (isset($this->_receive['Latitude'])){
            return array(
                'x'=>$this->_receive['Latitude'],
                'y'=>$this->_receive['Longitude'],
                'precision'=>$this->_receive['Precision'],
            );
        } else
            return false;
    }

    /**
     * 获取接收事件推送
     */
    public function getRevEvent()
    {
        if (isset($this->_receive['Event'])){
            return array(
                'event'=>$this->_receive['Event'],
                'key'=>isset($this->_receive['EventKey']) ? $this->_receive['EventKey'] : '#', // 判断是否存在
            );
        } else
            return false;
    }

    /**
     * 获取接收语言推送
     */
    public function getRevVoice()
    {
        if (isset($this->_receive['MediaId'])){
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'format'=>$this->_receive['Format'],
            );
        } else
            return false;
    }

    /**
     * 获取接收视频推送
     */
    public function getRevVideo()
    {
        if (isset($this->_receive['MediaId'])){
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'thumbmediaid'=>$this->_receive['ThumbMediaId']
            );
        } else
            return false;
    }

    /**
     * 获取接收TICKET
     */
    public function getRevTicket()
    {
        if (isset($this->_receive['Ticket'])){
            return $this->_receive['Ticket'];
        } else
            return false;
    }

    /**
     * 获取二维码的场景值
     */
    public function getRevSceneId ()
    {
        if (isset($this->_receive['EventKey'])){
            return str_replace('qrscene_','',$this->_receive['EventKey']);
        } else{
            return false;
        }
    }
    /**
     * 获取模板消息发送状态
     */
    public function getRevStatus()
    {
        if (isset($this->_receive['Status']))
            return $this->_receive['Status'];
        else
            return false;
    }


    /**
     * @param $str
     * @return string
     */
    public static function xmlSafeStr($str)
    {
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
    }

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    public static function dataToXml($data) {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($val) || is_object($val)) ? self::dataToXml($val)  : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public function xmlEncode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8')
    {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml   = "<{$root}{$attr}>";
        $xml   .= self::dataToXml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }

    /**
     * 设置回复消息
     * Examle: $obj->text('hello')->reply();
     * @param string $text
     * @return $this
     */
    public function text($text='')
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_TEXT,
            'Content'=>$text,
            'CreateTime'=>time(),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复消息
     * Example: $obj->image('media_id')->reply();
     * @param string $mediaid
     * @return $this
     */
    public function image($mediaid='')
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_IMAGE,
            'Image'=>array('MediaId'=>$mediaid),
            'CreateTime'=>time(),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     *
     * 设置回复音乐
     * @param $title
     * @param $desc
     * @param $musicurl
     * @param string $hgmusicurl
     * @return $this
     */
    public function music($title,$desc,$musicurl,$hgmusicurl='') {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'CreateTime'=>time(),
            'MsgType'=>self::MSGTYPE_MUSIC,
            'Music'=>array(
                'Title'=>$title,
                'Description'=>$desc,
                'MusicUrl'=>$musicurl,
                'HQMusicUrl'=>$hgmusicurl
            ),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复图文
     * @param array $newsData
     * 数组结构:
     *  array(
     *  	"0"=>array(
     *  		'Title'=>'msg title',
     *  		'Description'=>'summary text',
     *  		'PicUrl'=>'http://www.domain.com/1.jpg',
     *  		'Url'=>'http://www.domain.com/1.html'
     *  	),
     *  	"1"=>....
     *  )
     * @param array $newsData
     * @return $this
     */
    public function news($newsData=array())
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $count = count($newsData);

        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_NEWS,
            'CreateTime'=>time(),
            'ArticleCount'=>$count,
            'Articles'=>$newsData,
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     *
     * 回复微信服务器, 此函数支持链式操作
     * @example $this->text('msg tips')->reply();
     *
     * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
     * @param string|array $msg 要发送的信息, 默认取$this->_msg
     * @param bool|false $return
     * @return string
     */
    public function reply($msg=array(),$return = false)
    {
        if (empty($msg))
            $msg = $this->_msg;
        $xmldata=  $this->xmlEncode($msg);
        $this->log($xmldata);
        if ($return)
            return $xmldata;
        else
            echo $xmldata;
    }


    /**
     * 删除验证数据
     * @param string $appid
     * @return bool
     */
    public function resetAuth($appid=''){
        $this->access_token = '';
        //TODO: remove cache
        return true;
    }

    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     * @return string
     */
    static function jsonEncode($arr)
    {
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys[0] === 0)
            && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::jsonEncode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::jsonEncode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                    $str .= 'false'; //The booleans
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }

    /**
     * 创建菜单
     * @param array $data 菜单数组数据
     * example:
     * {
     * "button":[
     * {
     * "type":"click",
     * "name":"今日歌曲",
     * "key":"MENU_KEY_MUSIC"
     * },
     * {
     * "type":"view",
     * "name":"歌手简介",
     * "url":"http://www.qq.com/"
     * },
     * {
     * "name":"菜单",
     * "sub_button":[
     * {
     * "type":"click",
     * "name":"hello word",
     * "key":"MENU_KEY_MENU"
     * },
     * {
     * "type":"click",
     * "name":"赞一下我们",
     * "key":"MENU_KEY_GOOD"
     * }]
     * }]
     * }
     * @param $data
     * @return bool
     */
    public function createMenu($data)
    {
        if (!$this->access_token && !$this->checkAuth(true)) {
            return false;
        }

        $result = $this->safeHttpPost(self::API_URL_PREFIX.self::MENU_CREATE_URL, self::jsonEncode($data));

        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * 获取菜单
     * @return array|boolean
     */
    public function getMenu()
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $result = $this->safeHttpGet(self::API_URL_PREFIX.self::MENU_GET_URL);
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 获取最近七天内累计用户数据
     * @return array|boolean
     */
    public function getUserCumulate()
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $params['begin_date'] = date('Y-m-d', strtotime('-7 days'));
        $params['end_date'] = date('Y-m-d', strtotime('-1 days'));
        $params = json_encode($params);
        $result = $this->safeHttpPost(self::API_URL_GET_USER_CUMULATE, $params);

        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }


    /**
     * 删除菜单
     * @return boolean
     */
    public function deleteMenu()
    {
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->safeHttpGet(self::API_URL_PREFIX.self::MENU_DELETE_URL);
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 根据媒体文件ID获取媒体文件
     * @param string $mediaId 媒体文件id
     * @return array|boolean
     */
    public function getMedia($mediaId)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $result = Http::get(self::API_URL_PREFIX.self::MEDIA_GET_URL.'access_token='.$this->access_token.'&media_id='.$mediaId);

        return $result;
    }


    /**
     * 根据媒体文件ID获取媒体文件
     * @param string $mediaId 媒体文件id
     * @return array|boolean
     */
    public function getMediaJssdk($mediaId)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $result = Http::get(self::API_URL_PREFIX.self::MEDIA_GET_JSSDK_URL.'access_token='.$this->access_token.'&media_id='.$mediaId);

        return $result;
    }


    /**
     * 官方文档 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433542&token=&lang=zh_CN
     * 创建临时二维码ticket
     * @param int $sceneId 临时二维码时为32位非0整型，二进制
     * @param int $type   0 : 数字类型  1:  字符串类型
     * @param int $expire 临时二维码有效期，最大为2592000秒（即30天）
     * @return array|boolean ('ticket'=>'qrcode字串','expire_seconds'=>2592000)
     */
    public function getQRCode($sceneId, $type = 0, $expire = 2592000)
    {
        if (!$this->access_token && !$this->checkAuth()) return false;

        if ($type == 1) {
            $data = array(
                'action_name'=>"QR_STR_SCENE",
                'expire_seconds'=>$expire,
                'action_info'=>array('scene'=>array('scene_str'=>$sceneId))
            );
        } else {
            $data = array(
                'action_name'=>"QR_SCENE",
                'expire_seconds'=>$expire,
                'action_info'=>array('scene'=>array('scene_id'=>$sceneId))
            );
        }
        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::QRCODE_CREATE_URL, self::jsonEncode($data));
        if ($result)
        {
            Log::debug("getQRCode: result=>" . $result);
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 官方文档 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433542&token=&lang=zh_CN
     * 创建永久二维码ticket
     * @param $sceneId 场景值ID，永久二维码时最大值为100000（目前参数只支持1--100000）
     * @param int $type   0 : 数字类型  1:  字符串类型
     * @return array|boolean ('ticket'=>'qrcode字串','url'=>'二维码图片解析后的地址')
     */
    public function getLimitQRCode($sceneId, $type = 0)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }
        if ($type == 1) {
            $data = array(
                'action_name'=>"QR_LIMIT_STR_SCENE",
                'action_info'=>array('scene'=>array('scene_str'=>$sceneId))
            );
        }else {
            $data = array(
                'action_name'=>"QR_LIMIT_SCENE",
                'action_info'=>array('scene'=>array('scene_id'=>$sceneId))
            );
        }

        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::QRCODE_CREATE_URL, self::jsonEncode($data));
        if ($result) {
            Log::debug("getLimitQRCode: result=>" . $result);
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 获取二维码图片
     * @param string $ticket 传入由getQRCode方法生成的ticket参数
     * @return string url 返回http地址
     */
    public function getQRUrl($ticket)
    {
        return self::QRCODE_IMG_URL . $ticket;
    }

    /**
     * 批量获取关注用户列表
     * @param string $next_openid
     * @return bool|mixed
     */
    public function getUserList($next_openid=''){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = Http::get(self::API_URL_PREFIX.self::USER_GET_URL.'access_token='.$this->access_token.'&next_openid='.$next_openid);
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 通用auth验证方法，暂时仅用于菜单更新操作
     * @return bool|mixed
     */
    public function checkAuth($forceRefresh = false)
    {
        $result = $this->getAccessToken($forceRefresh);

        if($result){
            $this->access_token = $result;
            return  $this->access_token;
        }
        return false;
    }

    /**
     * 获取文件中的AccessToken
     * @return mixed
     */
    private function getAccessToken($forceRefresh = false)
    {
        $this->access_token = AccessToken::get($this->appid, $this->appsecret, $forceRefresh);
        return $this->access_token;
    }

    /**
     *
     * 获取关注者详细信息
     * 已关注：
     * {
     *     "subscribe":1
     *     "openid":"oicCewkZUxj7tv92z57TiX7ehN28",
     *     "nickname":"\u534e\u5347@\u6d41\u5149\u6620\u753b",
     *     "sex":1,"language":"zh_CN",
     *     "city":"\u5357\u5b81",
     *     "province":"\u5e7f\u897f",
     *     "country":"\u4e2d\u56fd",
     *     "headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/hiamn2bFGxrN76Kq21WD5Tm9Cr91p68h52bWr2rEr9iaUCIib63jsibxDQvNsjPBtxD9HKZwiaAvNfPTpibYQxmefNQw\/0",
     *     "subscribe_time":1462272994,
     *     "remark":"",
     *     "groupid":0,
     *     "tagid_list":[]
     * }
     *
     * 未关注：
     * {
     *     "subscribe":0,
     *     "openid":"oicCewi22xTYCclqDOVEP-g-uawY",
     *     "tagid_list":[]
     * }
     *
     * @param $openid
     * @return bool|mixed
     */
    public function getUserInfo($openid)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $result = Http::get(self::API_URL_PREFIX.self::USER_INFO_URL.'access_token='.$this->access_token.'&openid='.$openid);
        $json = json_decode($result);

        if(isset($json->errcode) && $json->errcode == 40001) {
            Log::WARN("getUserInfo: result=>" . $result . "  access_token无效错误, 自动恢复!");
            if ($this->checkAuth(true)) {
                $result = Http::get(self::API_URL_PREFIX.self::USER_INFO_URL.'access_token='.$this->access_token.'&openid='.$openid);
            }
        }

        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 获取用户分组列表
     * @return boolean|array
     */
    public function getGroup()
    {
        if (!$this->access_token && !$this->checkAuth())
            return false;

        $result = Http::get(self::API_URL_PREFIX.self::GROUP_GET_URL.'access_token='.$this->access_token);

        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 新增自定分组
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function createGroup($name)
    {
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'group'=>array('name'=>$name)
        );
        $result = Http::post(self::API_URL_PREFIX.self::GROUP_CREATE_URL.'access_token='.$this->access_token,self::jsonEncode($data));
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 更改分组名称
     * @param int $groupid 分组id
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function updateGroup($groupid, $name)
    {
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'group'=>array('id'=>$groupid,'name'=>$name)
        );
        $result = Http::post(self::API_URL_PREFIX.self::GROUP_UPDATE_URL.'access_token='.$this->access_token,self::jsonEncode($data));
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 移动用户分组
     * @param int $groupid 分组id
     * @param string $openid 用户openid
     * @return boolean|array
     */
    public function updateGroupMembers($groupid, $openid)
    {
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'openid'=>$openid,
            'to_groupid'=>$groupid
        );
        $result = Http::post(self::API_URL_PREFIX.self::GROUP_MEMBER_UPDATE_URL.'access_token='.$this->access_token,self::jsonEncode($data));
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return boolean|array
     */
    public function sendCustomService($data)
    {
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->safeHttpPost(self::API_URL_PREFIX.self::CUSTOM_SEND_URL, self::jsonEncode($data));
        Log::debug($this->appid);
        Log::debug(self::jsonEncode($data));
        Log::debug("{$result}");
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 发送模板消息
     * @param $data
     * @return bool|mixed
     */
    public function sendTemplate($data)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        Log::debug("sendTemplate: access_token=[" .  $this->access_token . "]");

        //$result = Http::post(self::API_URL_PREFIX . self::TEMPLATE_SEND_URL . 'access_token=' . $this->access_token, json_encode($data));
        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::TEMPLATE_SEND_URL, json_encode($data));

        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * 根据模板库中模板的编号获取模板ID
     * 备注: 由于不同微信公众号的模板ID不一样(即便是同一个行业模板), 所以需要根据统一编号做动态获取
     * @param $data
     * @return bool|mixed
     */
    public function getTemplateId($data)
    {
        if (!$this->access_token && !$this->checkAuth())
            return false;
        //$result = Http::post(self::API_URL_PREFIX . self::GET_TEMPLATE_URL . 'access_token=' . $this->access_token, json_encode($data));
        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::GET_TEMPLATE_URL, json_encode($data));

        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * 获取已添加的模板列表
     * @return boolean|array
     */
    public function getTemplateList()
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $result = $this->safeHttpGet(self::API_URL_PREFIX . self::GET_TEMPLATE_LIST_URL);
        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * 删除微信公众号后台指定的模板
     * @param $data
     * @return bool|mixed
     */
    public function delTemplateId($data)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }
        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::DEL_TEMPLATE_URL, json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * oauth 授权跳转接口
     * @param string $callback 回调URI
     * @return string
     */
    public function getOAuthRedirect($callback, $state='', $scope='snsapi_userinfo')
    {
        return self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
    }

    /*
     * 通过code获取Access Token(用户访问的)
     * @return array {access_token,expires_in,refresh_token,openid,scope}
     */
    public function getOAuthAccessToken($code)
    {
        if (!$code) {
            return false;
        }

        $url = self::OAUTH_TOKEN_PREFIX.'appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code';

        $result = Http::get($url);
        if ($result) {
            $json = json_decode($result, true);
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }

    /**
     * 刷新access token并续期
     * @param string $refresh_token
     * @return boolean|mixed
     */
    public function getOAuthRefreshToken($refresh_token)
    {
        $result = Http::get(self::OAUTH_TOKEN_PREFIX.self::OAUTH_REFRESH_URL.'appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token);
        if ($result) {
            $json = json_decode($result,true);
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }

    /**
     * 获取授权后的用户资料
     * @param string $access_token
     * @param string $openid
     * @return array|boolean {openid,nickname,sex,province,city,country,headimgurl,privilege}
     */
    public function getOAuthUserinfo($access_token, $openid)
    {
        $result = Http::get(self::OAUTH_USERINFO_URL.'access_token='.$access_token.'&openid='.$openid);
        if ($result)
        {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 获取签名
     * @param array $arrdata 签名数组
     * @param string $method 签名方法
     * @return boolean|string 签名值
     */
    public function getSignature($arrdata, $method = "sha1")
    {
        if (!function_exists($method)) return false;
        ksort($arrdata);
        $paramstring = "";
        foreach($arrdata as $key => $value)
        {
            if(strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }
        $paySign = $method($paramstring);
        return $paySign;
    }

    /**
     * 生成随机字串
     * $length 长度，默认为16，最长为32字节
     *
     * @param int $length
     * @return string
     */
    public function generateNonceStr($length=16){
        // 密码字符集，可任意添加你需要的字符
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for($i = 0; $i < $length; $i++)
        {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    /**
     * 获取收货地址JS的签名
     * @param string $url
     * @param int $timeStamp
     * @param string $nonceStr
     * @param string $user_token
     * @return Ambigous <boolean, string>
     */
    public function getAddrSign($url, $timeStamp, $nonceStr, $user_token = '')
    {
        if (!$user_token) $user_token = $this->user_token;
        if (!$user_token) {
            $this->errMsg = 'no user access token found!';
            return false;
        }
        $url = htmlspecialchars_decode($url);
        $arrdata = array(
            'appid'=>$this->appid,
            'url'=>$url,
            'timestamp'=>strval($timeStamp),
            'noncestr'=>$nonceStr,
            'accesstoken'=>$user_token
        );
        return $this->getSignature($arrdata);
    }

    /**
     * 获取多客服获取会话记录
     * @param array $data 数据结构{"starttime":123456789,"endtime":987654321,"openid":"OPENID","pagesize":10,"pageindex":1,}
     * @return boolean|array
     */
    public function getCustomServiceMessage($data)
    {
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = Http::post(self::API_URL_PREFIX.self::CUSTOM_SERVICE.'access_token='.$this->access_token,self::jsonEncode($data));
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 上传永久素材(认证后的订阅号可用)
     * 新增的永久素材也可以在公众平台官网素材管理模块中看到
     * 注意：上传大文件时可能需要先调用 set_time_limit(0) 避免超时
     * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
     * @param string $filepath
     * @param string $type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
     * @param boolean $isVideo 是否为视频文件，默认为否
     * @param array $videoInfo 视频信息数组，非视频素材不需要提供 array('title'=>'视频标题','introduction'=>'描述')
     * @return boolean|array
     */
    public function uploadForeverMedia($filepath, $type, $isVideo = false, $videoInfo = [])
    {
        if (!$this->access_token && !$this->checkAuth(true)) {
            return false;
        }

        // 组成数据
        $data = [
            'media' => new \CURLFile (realpath ($filepath), null, basename($filepath))
        ];

        if ($isVideo) {
            $data['description'] = self::jsonEncode($videoInfo);
        }

        $result = Http::upload(self::API_URL_PREFIX . self::MEDIA_FOREVER_UPLOAD_URL.'access_token=' . $this->access_token .'&type=' . $type, $data);
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }

        return false;
    }

    /**
     * 上传临时素材，有效期为3天(认证后的订阅号可用)
     * 注意：上传大文件时可能需要先调用 set_time_limit(0) 避免超时
     * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
     * 注意：临时素材的media_id是可复用的！
     * @param string $filepath
     * @param string $type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
     * @return boolean|array
     */
    public function uploadMedia($filepath, $type)
    {
        if (!$this->access_token && !$this->checkAuth())
            return false;

        // 组成数据
        if (class_exists ( '\CURLFile' )) {//关键是判断curlfile,官网推荐php5.5或更高的版本使用curlfile来实例文件
            $filedata = array (
                'media' => new \CURLFile ( realpath ( $filepath ), 'image/jpeg' )
            );
        } else {
            $filedata = array (
                'media' => '@' . realpath ( $filepath )
            );
        }

        //原先的上传多媒体文件接口使用 self::UPLOAD_MEDIA_URL 前缀
        $url = self::API_URL_PREFIX . self::MEDIA_UPLOAD_URL . 'access_token=' . $this->access_token . '&type=' . $type;
        $result = Http::upload($url, $filedata);
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     *  获取永久素材
     * @param $file_info
     * @return bool
     */
    public function getForeverMedia($mediaId)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $data = [
            'media_id' => $mediaId
        ];
        $result = Http::post(self::API_URL_PREFIX.self::MEDIA_FOREVER_GET_URL.'access_token='.$this->access_token, self::jsonEncode($data));
        if ($result) {
            return $result;
        }

        return false;
    }

    /**
     *  获获取素材列表
     * @param int $mediaType
     * @param int $offset
     * @param int $count 1~20之间
     * @return bool
     */
    public function batchgetForeverMedia($mediaType, $offset = 0, $count = 20)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $data = [
            'type'   => $mediaType,
            'offset' => $offset,
            'count'  => $count,
        ];
        $result = Http::post(self::API_URL_PREFIX.self::MEDIA_FOREVER_BATCHGET_URL.'access_token='.$this->access_token, self::jsonEncode($data));
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }

        return false;
    }

    /**
     *  删除永久素材
     * @param $file_info
     * @return bool
     */
    public function deleteForeverMedia($mediaId)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $data = [
            'media_id' => $mediaId
        ];
        $result = Http::post(self::API_URL_PREFIX.self::MEDIA_DELETE_URL.'access_token='.$this->access_token, self::jsonEncode($data));
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }

        return false;
    }

    /**
     * 返回对应微信公众号的唯一AppID
     * @return string
     */
    public function getAppID()
    {
        return $this->appid;
    }

    /**
     * 封装一个较为稳妥的Http get请求接口
     * 说明: 可以自动纠正一次40001错误(access_token无效)
     * @param $urlHeader
     * @return bool|mixed
     */
    private function safeHttpGet($urlHeader)
    {
        $result = Http::get($urlHeader . 'access_token=' . $this->access_token);
        if ($result) {
            $json = json_decode($result);
            Log::debug("safeHttpGet: result=>" . $result);
            if (isset($json->errcode) && $json->errcode == 40001) {
                Log::WARN("safeHttpGet: 检测到access_token无效错误, 自动恢复!");
                if ($this->checkAuth(true)) {
                    $result = Http::get($urlHeader . 'access_token=' . $this->access_token);
                }
            }
        }
        return $result;
    }

    /**
     * 封装一个较为稳妥的Http post请求接口
     * 说明: 可以自动纠正一次40001错误(access_token无效)
     * @param $urlHeader
     * @param $param
     * @return bool|mixed
     */
    public function safeHttpPost($urlHeader, $param)
    {
        $result = Http::post($urlHeader . 'access_token=' . $this->access_token, $param);

        if ($result) {
            $json = json_decode($result);
            Log::debug("safeHttpPost: result=>" . $result);
            if(isset($json->errcode) && $json->errcode == 40001) {
                Log::debug("safeHttpPost: 检测到access_token无效错误, 自动恢复!");
                if ($this->checkAuth(true)) {
                    $result = Http::post($urlHeader . 'access_token=' . $this->access_token, $param);
                }
            }
        }

        return $result;
    }

    /**
     * 长链接转成短链接
     * 主要使用场景： 开发者用于生成二维码的原链接（商品、支付二维码等）太长导致扫码速度和成功率下降，
     * 将原长链接通过此接口转成短链接再生成二维码将大大提升扫码速度和成功率。
     * @param string long_url 需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url
     * @return string
     */
    public function getShortUrl($long_url)
    {
        if (!$this->access_token && !$this->checkAuth())
            return false;

        $data = array(
            'action'=>'long2short',
            'long_url'=>$long_url
        );
        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::GET_SHORT_URL, json_encode($data));
        if ($result) {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                return false;
            }
            return $json['short_url'];
        }
        return false;
    }
}

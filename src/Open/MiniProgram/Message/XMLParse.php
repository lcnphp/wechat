<?php

namespace Weirin\Wechat\Open\MiniProgram\Message;

/**
 * 提供提取消息格式中的密文及生成回复消息格式的接口.
 * Class XMLParse
 * @package Weirin\Wechat\Open\MiniProgram\Message
 */
class XMLParse
{
	/**
	 * 提取出xml数据包中的加密消息
	 * @param string $xmltext 待提取的xml字符串
	 * @return Array
	 */
	public function extract($xmltext)
	{
		try {
			$array = (array)simplexml_load_string($xmltext, 'SimpleXMLElement', LIBXML_NOCDATA);
			return array(ErrorCode::$OK, $array['Encrypt']);
		} catch (Exception $e) {
			return array(ErrorCode::$ParseXmlError, null, null);
		}
	}

	/**
	 * 生成xml消息
	 * @param string $encrypt 加密后的消息密文
	 * @param string $signature 安全签名
	 * @param string $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 */
	public function generate($encrypt, $signature, $timestamp, $nonce)
	{
		$format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MessageSignature><![CDATA[%s]]></MessageSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
		return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
	}

}
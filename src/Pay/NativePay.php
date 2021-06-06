<?php

namespace Weirin\Wechat\Pay;

use Weirin\Wechat\Pay\Api as PayApi;
use Weirin\Wechat\Pay\Data\BizPayUrl as PayBizPayUrlData;
use Weirin\Wechat\Pay\Data\UnifiedOrderData;

/**
 * 扫码支付实现类
 * @author widyhu
 */
class NativePay
{
	/**
	 * 生成扫描支付URL
	 * @param BizPayUrlInput $bizUrlInfo
	 */
	public function GetPrePayUrl($productId)
	{
		$biz = new PayBizPayUrlData();
		$biz->SetProduct_id($productId);
		$values = PayApi::bizpayurl($biz);
		$url = "weixin://wxpay/bizpayurl?" . $this->ToUrlParams($values);
		return $url;
	}
	
	/**
	 * 参数数组转换为url参数
	 * @param array $urlObj
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			$buff .= $k . "=" . $v . "&";
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}
	
	/**
	 * 生成直接支付url，支付url有效期为2小时
	 * 代码范例: (跟JSAPI方式的差别点)
	 *  use Qrcode\QrcodeApi;
	 *
	 *  $input->SetTrade_type("NATIVE");
	 *  $input->SetProduct_id($orderModel->order_no);
	 *  $order = PayApi::UnifiedOrderData($input);
	 *  $codeUrl = $order["code_url"];
	 *  $qrcodeFile = QrcodeApi::png($codeUrl, $qrcodePath);
	 * @param UnifiedOrderData $input
	 */
	public function GetPayUrl(UnifiedOrderData $input)
	{
		if($input->GetTrade_type() == "NATIVE")
		{
			$result = PayApi::unifiedOrder($input);
			return $result;
		}
	}
}
<?php

namespace Weirin\Wechat\Pay;

/**
 * Class Log
 * @package Wechat
 */
class Log
{
	private static $adapter;

	/**
	 * @param LogAdapterInterface $logAdapter
	 */
	public static function setAdapter(LogAdapterInterface $adapter)
	{
		self::$adapter = $adapter;
	}

	/**
	 * @param $msg
	 * @return void
	 */
	public static function debug($msg)
	{
		if(self::$adapter instanceof LogAdapterInterface){
			self::$adapter->debug($msg);
		}
	}

	/**
	 * @param $msg
	 * @return void
	 */
	public static function warn($msg)
	{
		if(self::$adapter instanceof LogAdapterInterface){
			self::$adapter->warn($msg);
		}
	}

	/**
	 * @param $msg
	 * @return void
	 */
	public static function error($msg)
	{
		if(self::$adapter instanceof LogAdapterInterface){
			self::$adapter->error($msg);
		}
	}

	/**
	 * @param $msg
	 * @return void
	 */
	public static function info($msg)
	{
		if(self::$adapter instanceof LogAdapterInterface){
			self::$adapter->info($msg);
		}
	}
}
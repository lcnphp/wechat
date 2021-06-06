<?php

namespace Weirin\Wechat\Pay;

/**
 * Class Log
 * @package Wechat
 */
interface LogAdapterInterface
{
    /**
     * @param $msg
     */
    public function debug($msg);

    /**
     * @param $msg
     */
    public function warn($msg);

    /**
     * @param $msg
     */
    public function error($msg);

    /**
     * @param $msg
     */
    public function info($msg);
}

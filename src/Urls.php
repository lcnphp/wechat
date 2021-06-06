<?php

namespace Weirin\Wechat;

use function http_build_query;

/**
 * Class Urls
 * @package Wechat\Open\MiniProgram
 */
class Urls
{
    const API_PREFIX  = 'https://api.weixin.qq.com/';
    const MP_PREFIX   = 'https://mp.weixin.qq.com/';
    const OPEN_PREFIX = 'https://open.weixin.qq.com/';

    /**
     * @param $path
     * @param array $parts
     * @return string
     */
    public static function api($path, array $parts = [])
    {
        return static::build(static::API_PREFIX, $path, $parts);
    }

    /**
     * @param $path
     * @param array $parts
     * @return string
     */
    public static function open($path, array $parts = [])
    {
        return static::build(static::OPEN_PREFIX, $path, $parts);
    }

    /**
     * @param $path
     * @param array $parts
     * @return string
     */
    public static function mp($path, array $parts = [])
    {
        return static::build(static::MP_PREFIX, $path, $parts);
    }

    /**
     * @param $prefix
     * @param $path
     * @param array $parts
     * @return string
     */
    private static function build($prefix, $path, array $parts)
    {
        $uri = $prefix . $path;
        $query = http_build_query($parts);
        $query = $query ? '?'.$query : '';
        return $uri.$query;
    }
}
<?php

namespace Weirin\Wechat;

/**
 * 异步请求类
 * 实现效果是: 触发一个PHP脚本，然后立即返回，留它在服务器端慢慢执行
 * 注意: 确保php.ini allow_url_fopen=on
 * Class FSock
 * @package Wechat
 */
class FSock
{
    const DEFAULT_PORT = 80; // 默认端口号
    const DEFAULT_TIMEOUT = 30; // 默认请求超时(秒)

    /**
     * 非阻塞式的异步GET请求
     * @param string $url http://host/wechat/send 完整url地址(不要携带query参数)
     * @param array $param
     * sample:  $params = array('key1'=>$value1, 'key2'=>$value2);
     * @return bool|mixed
     */
    public static function get($url, $param = array())
    {
        //set_time_limit(0); // 暂时默认30秒足够了
        $url_array = parse_url($url); // 获取URL信息，以便拼凑HTTP HEADER
        $host = $url_array['host'];
        $port = isset($url_array['port'])? $url_array['port'] : self::DEFAULT_PORT;
        $path = $url_array['path'] ."?". http_build_query($param); // 注意: query参数从$param统一传入
        $useragent = 'chrome'; // 默认用户代理(浏览器信息)
        if(isset($_SERVER['HTTP_USER_AGENT'])) {
            $useragent = $_SERVER['HTTP_USER_AGENT'];
        }

        Log::debug('非阻塞式的异步GET请求: host=' . $host . ', path=' . $path . ', port=' . $port);

        $fp = @fsockopen($host, $port, $errno, $errstr, self::DEFAULT_TIMEOUT);
        if (!$fp) {
            Log::error("{$host}{$path}:{$port} {$errstr} ({$errno})<br />\r\n");
            return false;
        }

        stream_set_blocking($fp, 0);//非阻塞模式
        $header = "GET $path HTTP/1.1\r\n";
        $header.="Host: $host\r\n";
        $header .= "User-Agent: " . $useragent . "\r\n"; // 注意: apache服务器需要设置, nginx服务器不需要
        $header.="Connection: Close\r\n\r\n";//长连接关闭
        fwrite($fp, $header);
        fclose($fp);

        return true;
    }

    /**
     * 非阻塞式的异步POST请求
     * @param string $url http://host/wechat/send 完整url地址
     * @param array $param
     * sample:  $params = array('key1'=>$value1, 'key2'=>$value2);
     */
    public static function post($url, $param = array())
    {
        //set_time_limit(0); // 暂时默认30秒足够了
        $url_array = parse_url($url); // 获取URL信息，以便拼凑HTTP HEADER
        $host = $url_array['host'];
        $port = isset($url_array['port'])? $url_array['port'] : self::DEFAULT_PORT;
        $path = $url_array['path'];
        $data = http_build_query($param); // POST数据
        $useragent = 'chrome'; // 默认用户代理(浏览器信息)
        if(isset($_SERVER['HTTP_USER_AGENT'])) {
            $useragent = $_SERVER['HTTP_USER_AGENT'];
        }

        Log::debug('非阻塞式的异步POST请求: host=' . $host . ', path=' . $path . ', port=' . $port);

        $fp = fsockopen($host, $port, $errno, $errstr, self::DEFAULT_TIMEOUT);
        if (!$fp) {
            Log::error("$errstr ($errno)<br />\n");
            return false;
        }

        // 设置非阻塞模式
        stream_set_blocking($fp, 0);
        $out = "POST $path HTTP/1.1\r\n";
        $out .="Host: $host\r\n";
        $out .= "User-Agent: " . $useragent . "\r\n"; // 注意: apache服务器需要设置, nginx服务器不需要
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n"; // POST数据
        $out .= "Content-Length: ". strlen($data) ." \r\n"; // POST数据的长度
        $out .="Connection: Close\r\n\r\n";//长连接关闭
        $out .= $data; // 传递POST数据

        fwrite($fp, $out);
        fclose($fp);

        Log::debug('非阻塞式的异步POST请求: out=' . $out);
        return true;
    }
}
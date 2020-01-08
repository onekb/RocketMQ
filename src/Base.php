<?php

namespace onekb\RocketMQ;

use MQ\MQClient;

class Base
{
    private $client;

    private $host;
    private $accessKey;
    private $secretKey;


    //单例
    private static $_instance;
    private function __construct()
    {
        $this->host = getenv('mq_host');
        $this->accessKey = getenv('mq_AccessKey');
        $this->secretKey = getenv('mq_SecretKey');

        $this->client = new MQClient(
            // 设置HTTP接入域名（此处以公共云生产环境为例）
            $this->host,
            // AccessKey 阿里云身份验证，在阿里云服务器管理控制台创建
            $this->accessKey,
            // SecretKey 阿里云身份验证，在阿里云服务器管理控制台创建
            $this->secretKey
        );
    }
    private function __clone()
    {
    }
    public static function getInstance()//: self
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getClient()//: MQClient
    {
        return $this->client;
    }
}

<?php

namespace onekb\Test;

require_once __DIR__ . '/../vendor/autoload.php';

use onekb\RocketMQ\Consumer;
use onekb\RocketMQ\ConsumerMessage;

// 设置HTTP接入域名（此处以公共云生产环境为例）
putenv('mq_host=http://xxxxxxxxxxxx.mqrest.cn-shenzhen.aliyuncs.com');
// AccessKey 阿里云身份验证，在阿里云服务器管理控制台创建
putenv('mq_AccessKey=xxxxxxxxxxxxxxxxxxxx');
// SecretKey 阿里云身份验证，在阿里云服务器管理控制台创建
putenv('mq_SecretKey=xxxxxxxxxxxxxxxxxxx');
// Topic所属实例ID，默认实例为空NULL。键值对关系
putenv('mq_topic_XXXXXXX=MQ_INST_xxxxxxxxxxxxx');

$run = new run();
Consumer::Job(
    'XXXXXXX',
    'GID_XXXXXXX',
    $run,
    10,
    3
);

class run
{
    public function run($message, ConsumerMessage $consumer)
    {
        print_r('已消费');
        print_r($message);
        $consumer->release(500);
    }
}

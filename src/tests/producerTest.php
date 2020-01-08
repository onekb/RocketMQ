<?php

namespace onekb\Test;

require_once __DIR__ . '/../vendor/autoload.php';

use onekb\RocketMQ\Producer;

// 设置HTTP接入域名（此处以公共云生产环境为例）
putenv('mq_host=http://xxxxxxxxxxxxx.mqrest.cn-shenzhen.aliyuncs.com');
// AccessKey 阿里云身份验证，在阿里云服务器管理控制台创建
putenv('mq_AccessKey=xxxxxxxxxxxxxxxxx');
// SecretKey 阿里云身份验证，在阿里云服务器管理控制台创建
putenv('mq_SecretKey=xxxxxxxxxxxxxxxxxxxxxx');
// Topic所属实例ID，默认实例为空NULL。键值对关系
putenv('mq_topic_XXXXXXX=MQ_INST_xxxxxxxxxxxxxxxxx');


$topicMessage = Producer::push('XXXXXXX', [
    'aaa' => 111,
    'bbb' => 222,
    'ccc' => '恭喜发财'
]);
print_r($topicMessage);

# RocketMQ
阿里云RocketMQ队列 封装成工具方法

## 安装
> composer require 1kb/rocket-mq

## 配置
> 配置环境变量ENV
### 请在调用前配置

```php
// 设置HTTP接入域名（此处以公共云生产环境为例）
putenv('mq_host=http://xxxxxxxxxxxxx.mqrest.cn-shenzhen.aliyuncs.com');
// AccessKey 阿里云身份验证，在阿里云服务器管理控制台创建
putenv('mq_AccessKey=xxxxxxxxxxxxxxxxx');
// SecretKey 阿里云身份验证，在阿里云服务器管理控制台创建
putenv('mq_SecretKey=xxxxxxxxxxxxxxxxxxxxxx');
// Topic所属实例ID，默认实例为空NULL。键值对关系
putenv('mq_topic_XXXXXXX=MQ_INST_xxxxxxxxxxxxxxxxx');
```

## 创建任务
> 请参考/src/tests/producerTest.php

```php

use onekb\RocketMQ\Producer;

//第一个参数为topic 第二个参数，会自动json化
$topicMessage = Producer::push('XXXXXXX', [
    'aaa' => 111,
    'bbb' => 222,
    'ccc' => '恭喜发财'
]);
// $topicMessage->messageId 消息id
// $topicMessage->messageBodyMD5 内容md5
print_r($topicMessage);

```

## 消费任务
> 请参考cd/src/tests/consumerTest.php

```php

use onekb\RocketMQ\Consumer;
use onekb\RocketMQ\ConsumerMessage;

//准备一个类 用于被注入
class run
{
    public function run($message, ConsumerMessage $consumer)
    {
        //所有业务代码写在这
        print_r('已消费');
        print_r($message);
        print($message->getProperty('num')); //获取重试数量 第一次运行为0
        print_r($message->messageBodyArray); //获取body数组
        print_r($message->getMessageBody()); //获取原始body字符串

        $consumer->delete();//确认消费 发送ack

        //业务失败 发送->release($delay) 重新发起 $delay为延迟时间 单位毫秒
        $consumer->release(500);
    }
}
//实例化run类
$run = new run();

//第一个参数为topic 第二参数为group_id 第三个参数为实例化的注入类，运行function为run 第四个参数为每次获取多少条 第五个参数为空闲获取间隔时间
Consumer::Job(
    'XXXXXXX',
    'GID_XXXXXXXX',
    $run,//注入
    10,
    3
);

```

## 监听任务并执行

> php consumerTest.php

>可配合supervisor使用，保证进程常驻
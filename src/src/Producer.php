<?php

namespace onekb\RocketMQ;

use MQ\Model\TopicMessage;
use MQ\Model\Message;

//生产者
class Producer
{
    /**
     * 推送到阿里云
     *
     * @param integer $delay 延时 单位毫秒
     * @param string $topic 所属的 Topic
     * @param string $instanceId Topic所属实例ID，默认实例为空NULL
     * @param TopicMessage $topicMessage TopicMessage
     * @return void
     * @author 1kb<1@1kb.ren>
     * @since 2020-01-07 11:47
     */
    protected static function pushToRemote($delay = 0, $topic = '', $instanceId = null, TopicMessage $topicMessage)
    {
        //定时消息
        if ($delay)
            $topicMessage->setStartDeliverTime((time() * 1000) + $delay);

        $producer = Base::getInstance()->getClient()->getProducer($instanceId, $topic);

        $result = $producer->publishMessage($topicMessage); //发送消息
        return $result;
    }

    public static function push($topic, $body, $delay = 0, $key = null)
    {
        //消息内容
        $publishMessage = new TopicMessage(
            json_encode($body)
        );
        //设置属性
        $publishMessage->putProperty("num", 0); //重试次数
        //设置消息key
        if ($key)
            $publishMessage->setMessageKey($key);
        $instanceId = getenv('mq_topic_' . $topic) ?: null;
        return static::pushToRemote($delay, $topic, $instanceId, $publishMessage);
    }

    /**
     * 将消费message转变成生产message
     *
     * @param \MQ\Model\TopicMessage $message
     * @return \MQ\Model\TopicMessage
     * @author 1kb<1@1kb.ren>
     * @since 2020-01-07 17:28
     */
    protected static function message2Topic(Message $message)//: TopicMessage
    {
        //消息内容
        $publishMessage = new TopicMessage(
            $message->getMessageBody()
        );
        //设置属性
        $publishMessage->putProperty("num", intval($message->getProperty('num')) + 1); //重试次数

        //设置消息key
        if ($key = $message->getMessageKey())
            $publishMessage->setMessageKey($key);
        return $publishMessage;
    }

    public static function messageRelease($delay, $message, $topic, $instanceId)
    {
        $publishMessage = self::message2Topic($message);
        return static::pushToRemote($delay, $topic, $instanceId, $publishMessage);
    }
}

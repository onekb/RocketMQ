<?php

namespace onekb\RocketMQ;

use MQ\Exception\MessageNotExistException;
use MQ\Model\Message;
use MQ\MQConsumer;

/**
 * 消费者
 *
 * @author 1kb<1@1kb.ren>
 * @since 2020-01-07 19:56
 */
class Consumer
{
    protected static $consumer;
    protected static $numOfMessages;
    protected static $waitSeconds;
    public static function Job($topic, $groupId,  $loc, $numOfMessages = 3, $waitSeconds = 3)
    {
        self::$numOfMessages = $numOfMessages; //一次最多消费3条(最多可设置为16条)
        self::$waitSeconds = $waitSeconds; //长轮询时间3秒（最多可设置为30秒）

        $instanceId = getenv('mq_topic_' . $topic) ?: null;
        self::$consumer = base::getInstance()->getClient()->getConsumer($instanceId, $topic, $groupId);
        self::consumerJob($loc);
    }

    protected static function consumerJob($loc)
    {
        // 在当前线程循环消费消息，建议是多开个几个线程并发消费消息
        while (true) {
            try {
                // 长轮询消费消息
                // 长轮询表示如果topic没有消息则请求会在服务端挂住3s，3s内如果有消息可以消费则立即返回
                $messages = self::$consumer->consumeMessage(
                    self::$numOfMessages, // 一次最多消费3条(最多可设置为16条)
                    self::$waitSeconds // 长轮询时间3秒（最多可设置为30秒）
                );
            } catch (\Exception $e) {
                if ($e instanceof MessageNotExistException) {
                    // 没有消息可以消费，接着轮询
                    printf("仍然没有消息,继续长轮询! RequestId:%s\n", $e->getRequestId());
                    continue;
                }
                print_r($e->getMessage() . "\n");
                sleep(3);
                continue;
            }
            // 处理业务逻辑
            foreach ($messages as $message) {
                //body转换成array
                $message->messageBodyArray = json_decode($message->messageBody,true);
                $loc->run($message, new ConsumerMessage($message, self::$consumer));
            }
        }
    }
}


class ConsumerMessage
{
    protected $message;
    protected $consumer;
    public function __construct(Message $message, MQConsumer $consumer)
    {
        $this->message = $message;
        $this->consumer = $consumer;
    }

    protected function ackMessage()
    {
        print "ack finish:" . $this->message->getMessageId() . "\n";
        $this->consumer->ackMessage([$this->message->getReceiptHandle()]);
    }

    public function delete()
    {
        return $this->ackMessage();
    }

    /**
     * 重新发布
     *
     * @param integer $delay 延迟时间 单位毫秒
     * @return void
     * @author 1kb<1@1kb.ren>
     * @since 2020-01-07 17:06
     */
    public function release($delay)
    {
        $this->ackMessage();
        $message = $this->message;
        $topic =  $this->consumer->getTopicName();
        $instanceId = $this->consumer->getInstanceId();
        Producer::messageRelease($delay, $message, $topic, $instanceId);
    }
}

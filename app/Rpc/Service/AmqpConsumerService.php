<?php declare(strict_types=1);

namespace App\Rpc\Service;

use App\Rpc\Lib\AmqpConsumerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class AmqpConsumerService
 *
 * @package App\Rpc\Service
 *
 * @since 2.0
 *
 * @Service()
 *
 */
class AmqpConsumerService implements AmqpConsumerInterface
{

    /**
     * @param array $params
     * @return array
     */
    public function test(array $params): array
    {
        $params['service'] = 'service';
        return $params;
    }

    /**
     * 发布者(生产者)
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function publisher(array $params): array
    {
        //// 配置
        $HOST = env('AMQP_HOST');
        $PORT = env('AMQP_PORT');
        $USER = env('AMQP_USER');
        $PASS = env('AMQP_PASSWORD');
        $VHOST = env('AMQP_VHOST', '/');
//var_dump( $HOST, $PORT, $USER, $PASS, $VHOST );

        //// 参数
        // 交换器名称
        $exchange = empty( $params['exchange '] ) ? 'exchang_amqp' : $params['exchange '];
        // 队列名称
        $queue = empty( $params['queue'] ) ? 'queue_amqp' : $params['queue'];
        // 消息体
        $messageBody  = empty( $params['message'] ) ? 'i love rabbitmq(amqp)! ^2_3^' : $params['message'];

        //// 连接
        $connection  = new AMQPStreamConnection($HOST, $PORT, $USER, $PASS, $VHOST);

        //// 信道
        $channel = $connection->channel();

        //// 队列声明
        /*
            name: $queue
            passive: false
            durable: true //(是否持久化) the queue will survive server restarts
            exclusive: false //(是否排他性) the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
         */
        $channel->queue_declare($queue, false,true, false, false);

        //// 交换器声明
        /*
            name: $exchange
            type: direct
            passive: false
            durable: true //(是否持久化) the exchange will survive server restarts
            auto_delete: false //(是否自动删除)the exchange won't be deleted once the channel is closed.
         */
        $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

        //// 交换器-队列绑定
        $channel->queue_bind($queue, $exchange);

        //// 消息发布
        $message  = new AMQPMessage($messageBody, array( 'content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ));
        $channel->basic_publish($message, $exchange);

        //// 关闭资源
        $channel->close();
        $connection->close();

        return $params;
    }

    /**
     * php-amqplib 消费者
     * @param array $params
     * @return array
     * @throws \ErrorException
     */
    public function consumer(array $params): array
    {
        //// 参数
        // 交换器名称
        $exchange = empty( $params['exchange '] ) ? 'exchang_amqp' : $params['exchange '];
        // 队列名称
        $queue = empty( $params['queue'] ) ? 'queue_amqp' : $params['queue'];
        // 消费标识
        $consumerTag = empty( $params['consumer_tag'] ) ? 'consumer_tag' : $params['consumer_tag'];

        while (true) {

            $connection = null;
            try{

                //// 连接
                $connection = $this->getConnection();

                //// 信道
                $channel = $connection->channel();

                //// 队列声明
                /*
                    name: $queue
                    passive: false
                    durable: true //(是否持久化) the queue will survive server restarts
                    exclusive: false //(是否排他性) the queue can be accessed in other channels
                    auto_delete: false //(是否自动删除)the queue won't be deleted once the channel is closed.
                */
                $channel->queue_declare($queue, false, true, false, false);

                //// 交换器声明
                /*
                    name: $exchange
                    type: direct
                    passive: false
                    durable: true //(是否持久化) the exchange will survive server restarts
                    auto_delete: false //(是否自动删除)the exchange won't be deleted once the channel is closed.
                 */
                $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

                //// 交换器-队列绑定
                $channel->queue_bind($queue, $exchange);

                //// 消费消息
                /*
                    queue: (队列)Queue from where to get the messages
                    consumer_tag: (消费标识)Consumer identifier
                    no_local: Don't receive messages published by this consumer.
                    no_ack: (是否自动答复)If set to true, automatic acknowledgement mode will be used by this consumer. See https://www.rabbitmq.com/confirms.html for details.
                    exclusive: (是否排他性)Request exclusive consumer access, meaning only this consumer can access the queue
                    nowait:
                    callback: A PHP Callback
                */
                $channel->basic_consume($queue, $consumerTag, false, false, false, false, array($this, 'consumerProcessMessage'));

                //// 注册中止函数：注册一个会在php中止时执行的函数；
                register_shutdown_function(array($this, 'consumerShutdown'), $channel, $connection);

                // Loop as long as the channel has callbacks registered
                while ($channel ->is_consuming()) {
                    $channel->wait();
                }

            }catch (AMQPRuntimeException $e) {
                var_dump( 'AMQPRuntimeException' );
                $connection->close();
                usleep(1000000);

            }catch (\RuntimeException $e) {
                var_dump('RuntimeException');
                $connection->close();
                usleep(1000000);

            }catch (\ErrorException $e) {
                var_dump('ErrorException');
                $connection->close();
                usleep(1000000);
            }

        }

        return $params;
    }

    /**
     * 获取连接
     * @return AMQPStreamConnection
     */
    private function getConnection(): AMQPStreamConnection
    {
        //// 配置
        $HOST = env('AMQP_HOST');
        $PORT = env('AMQP_PORT');
        $USER = env('AMQP_USER');
        $PASS = env('AMQP_PASSWORD');
        $VHOST = env('AMQP_VHOST', '/');

        //// 连接
        $connection = new AMQPStreamConnection($HOST, $PORT, $USER, $PASS, $VHOST);

        return $connection;
    }

    /**
     * php-amqplib 消费者处理消息
     * @param AMQPMessage $message
     */
    public function consumerProcessMessage( AMQPMessage $message ): void
    {
        //// 消息处理
        echo "\n--------\n";
        echo $message->body;
        echo "\n--------\n";

        //// 答复响应
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']); //消息答复;

        //// 停止消费
        // Send a message with the string "quit" to cancel the consumer.
        if ($message->body === 'quit') {
            $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
        }

    }

    /**
     * 消费者处理关闭
     * @param AMQPChannel $channel
     * @param AbstractConnection $connection
     * @throws \Exception
     */
    public function consumerShutdown(AMQPChannel $channel, AbstractConnection $connection): void
    {
        $channel->close();
        $connection->close();
var_dump('consumerShutdown');
    }

}


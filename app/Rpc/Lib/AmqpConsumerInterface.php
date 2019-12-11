<?php declare(strict_types=1);

namespace App\Rpc\Lib;

/**
 * ^23^AmqpConsumerInterface
 * Class UserInterface
 *
 * @since 2.0
 */
interface AmqpConsumerInterface
{
    /**
     * 测试
     * @param array $params
     * @return array
     */
    public function test(array $params): array;

    /**
     * php-amqplib 生产者(发布者)
     * @param array $params
     * @return array
     */
    public function publisher(array $params): array;

    /**
     * php-amqplib 消费者
     * @param array $params
     * @return array
     */
    public function consumer(array $params): array;
}

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
}

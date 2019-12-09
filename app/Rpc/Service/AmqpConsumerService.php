<?php declare(strict_types=1);

namespace App\Rpc\Service;

use App\Rpc\Lib\AmqpConsumerInterface;
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
}


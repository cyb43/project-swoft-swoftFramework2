<?php declare(strict_types=1);

namespace App\Http\Controller;

use App\Rpc\Lib\AmqpConsumerInterface;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * Class AmqpConsumerController
 * @package App\Http\Controller
 *
 * @Controller(prefix="/amqp/consumer")
 */
class AmqpConsumerController
{

    /**
     * (RPC客户端请求)amqp消费者
     * @Reference(pool="user.pool")
     * @var AmqpConsumerInterface
     */
    private $amqpConsumerService;

    /**
     * 测试
     *
     * @RequestMapping(route="test", method={RequestMethod::GET})
     *
     * @param Request $request
     * @return array
     */
    public function test(Request $request): array
    {
        $params = $request->input();
        $params['controller'] = 'controller';

        return $this->amqpConsumerService->test( $params );
    }
}
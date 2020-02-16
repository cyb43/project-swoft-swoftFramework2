<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: ^2_3^王尔贝
 * Date: 2020/2/8
 * Time: 17:43
 */

namespace App\Http\Controller;

use App\Rpc\Lib\WrDocInterface;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;

/**
 * ^2_3^开发文档示例
 * Class WrDocController
 *
 * @since 2.0
 * @Controller(prefix="/wrdoc")
 *
 * @package App\Http\Controller
 * @author ^2_3^王尔贝
 */
class WrDocController
{

    /**
     * [rpc服务]开发文档示例
     * @Reference(pool="rpcClient.pool")
     *
     * app/bean.php
     * //// [RPC服务] RPC Client 配置
        'rpcClient'              => [
            'class'   => ServiceClient::class,
            'host'    => '127.0.0.1',
            'port'    => 18307,
            'setting' => [
                'timeout'         => 0.5,
                'connect_timeout' => 1.0,
                'write_timeout'   => 10.0,
                'read_timeout'    => 0.5,
            ],
            'packet'  => bean('rpcClientPacket')
        ],
        'rpcClient.pool'         => [
            'class'  => ServicePool::class,
            'client' => bean('rpcClient'),
        ],
     *
     * @var WrDocInterface
     * @author ^2_3^王尔贝
     */
    private $wrDocService;


    /**
     * 信息
     * @RequestMapping(route="info")
     *
     * @author ^2_3^王尔贝
     */
    public function info(): Response
    {
        $info = $this->wrDocService->info();
        $info .= '^2_3^';
        return context()->getResponse()->withContent( $info );
    }

}
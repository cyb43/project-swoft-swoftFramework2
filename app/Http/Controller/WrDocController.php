<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: ^2_3^王尔贝
 * Date: 2020/2/8
 * Time: 17:43
 */

namespace App\Http\Controller;

use App\Rpc\Lib\WrDocInterface;
use Swoft\Co;
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
    //private $wrDocService;

    /**
     * [rpc服务]开发文档示例
     * @Reference(pool="swoft2-rpc-srv.pool")
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
        $info .= '(^2_3^)';
        return context()->getResponse()->withContent( $info );
    }

    /**
     * 协程函数
     * @RequestMapping(route="co")
     *
     * @return Response
     * @author ^2_3^王尔贝
     */
    public function co(): Response
    {
        //// 协程函数(协同处理，单独执行)
        /// 创建协程_方式一
        Co::create(function() {
            // 顶级 ID
            $coTid = Co::tid();
            // 协程 ID
            $coId = Co::id();

            var_dump( "协程({$coTid}-{$coId}) start ".date('Y-m-d H:i:s') );
            sleep(6);
            var_dump( "协程({$coTid}-{$coId}) end ".date('Y-m-d H:i:s') );
        });
        Co::create(function() {
            // 顶级 ID
            $coTid = Co::tid();
            // 协程 ID
            $coId = Co::id();

            var_dump( "协程({$coTid}-{$coId}) start ".date('Y-m-d H:i:s') );
            sleep(6);
            var_dump( "协程({$coTid}-{$coId}) end ".date('Y-m-d H:i:s') );
        });
        //
        /// 创建协程_方式二
        sgo(function() {
            // 顶级 ID
            $coTid = Co::tid();
            // 协程 ID
            $coId = Co::id();

            var_dump( "协程({$coTid}-{$coId}) start ".date('Y-m-d H:i:s') );
            sleep(3);
            var_dump( "协程({$coTid}-{$coId}) end ".date('Y-m-d H:i:s') );
        });
        sgo(function() {
            // 顶级 ID
            $coTid = Co::tid();
            // 协程 ID
            $coId = Co::id();

            var_dump( "协程({$coTid}-{$coId}) start ".date('Y-m-d H:i:s') );
            sleep(3);
            var_dump( "协程({$coTid}-{$coId}) end ".date('Y-m-d H:i:s') );
        });
        //
        /// 控制台日志输出
//        string(37) "协程(4-5) start 2020-06-27 23:39:19"
//        string(37) "协程(4-6) start 2020-06-27 23:39:19"
//        string(37) "协程(4-7) start 2020-06-27 23:39:19"
//        string(37) "协程(4-8) start 2020-06-27 23:39:19"
//        string(35) "协程(4-7) end 2020-06-27 23:39:22"
//        string(35) "协程(4-8) end 2020-06-27 23:39:22"
//        string(35) "协程(4-5) end 2020-06-27 23:39:25"
//        string(35) "协程(4-6) end 2020-06-27 23:39:25"

        return context()->getResponse()->withContent('协程函数');
    }

}
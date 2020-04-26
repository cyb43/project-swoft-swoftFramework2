<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
use App\Common\DbSelector;
use App\Process\MonitorProcess;
use Swoft\Crontab\Process\CrontabProcess;
use Swoft\Db\Pool;
use Swoft\Http\Server\HttpServer;
use Swoft\Task\Swoole\SyncTaskListener;
use Swoft\Task\Swoole\TaskListener;
use Swoft\Task\Swoole\FinishListener;
use Swoft\Rpc\Client\Client as ServiceClient;
use Swoft\Rpc\Client\Pool as ServicePool;
use Swoft\Rpc\Server\ServiceServer;
use Swoft\Http\Server\Swoole\RequestListener;
use Swoft\WebSocket\Server\WebSocketServer;
use Swoft\Server\SwooleEvent;
use Swoft\Db\Database;
use Swoft\Redis\RedisDb;


/**
 * ^2_3^
 */
return [
    'noticeHandler'      => [
        'logFile' => '@runtime/logs/notice-%d{Y-m-d-H}.log',
    ],

    'applicationHandler' => [
        'logFile' => '@runtime/logs/error-%d{Y-m-d}.log',
    ],

    'logger'            => [
        'flushRequest' => false,
        'enable'       => false,
        'json'         => false,
    ],


    //// ^2_3^ http服务
    'httpServer'        => [
        'class'    => HttpServer::class,
        'port'     => 18306,
        'listener' => [
            'rpc' => bean('rpcServer'),
            //'ws' => bean('wsServer') //?为什么跟随http服务启动ws会报错？(2020-02-15)
        ],
        //// 进程
        'process'  => [
//            'monitor' => bean(MonitorProcess::class)

            //// 定时任务
            //'crontab' => bean(CrontabProcess::class)
        ],
        'on'       => [
//            SwooleEvent::TASK   => bean(SyncTaskListener::class),  // Enable sync task
            SwooleEvent::TASK   => bean(TaskListener::class),  // Enable task must task and finish event
            SwooleEvent::FINISH => bean(FinishListener::class)
        ],
        /* @see HttpServer::$setting */
        'setting' => [
            'task_worker_num'       => 12,
            'task_enable_coroutine' => true,
            'worker_num'            => 6
        ]
    ],
    // 中间件配置
    'httpDispatcher'    => [
        // Add global http middleware
        'middlewares'      => [
            \App\Http\Middleware\FavIconMiddleware::class,
            \Swoft\Http\Session\SessionMiddleware::class,
            // \Swoft\Whoops\WhoopsMiddleware::class, //异常错误信息处理中间件;
            // Allow use @View tag
            \Swoft\View\Middleware\ViewMiddleware::class,
        ],
        'afterMiddlewares' => [
            //// 验证器中间件
            \Swoft\Http\Server\Middleware\ValidatorMiddleware::class
        ]
    ],


    ////// [示例]数据库配置
    //
    //// db配置，使用默认db.pool连接池；
    'db'                => [
        'class'    => Database::class,

        //// 容器宿主mysql(MacBookPro)
        // (1)、容器宿主地址查询：通过ifconfig命令查看mac网络设置获取192.168.0.102；
        // (2)、地址绑定注释：注释mysql配置文件中 bind-address = 127.0.0.1，允许其他地址访问；
        // (3)、数据库授权：数据库运行 "mysql> ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY '123456';"，允许root从其他地址登录；
        // (4)、数据库创建：CREATE DATABASE `wr_swoft2` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        // (5)、数据迁移：php bin/swoft migrate:up；
//        'dsn'      => 'mysql:dbname=wr_swoft2;host=192.168.0.102:3306',
//        'username' => 'root',
//        'password' => '123456',

        //// mysql容器
        // docker inspect mysql-srv 获取 172.18.0.2；
        'dsn'      => 'mysql:dbname=wr_swoft2;host=172.18.0.2:3306',
        'username' => 'root',
        'password' => '123456',


        //// 腾讯云_mysql
//        'dsn'      => 'mysql:dbname=wr_swoft2;host=gz-cdb-5opdrysn.sql.tencentcdb.com:60312',
//        'username' => 'root',
//        'password' => 'mysql_1357924680_@',

        'charset' => 'utf8mb4',
    ],
    //
    //// db2配置
    'db2'               => [
        'class'      => Database::class,
        'dsn'        => 'mysql:dbname=test2;host=127.0.0.1',
        'username'   => 'root',
        'password'   => 'swoft123456',
//        'dbSelector' => bean(DbSelector::class)
    ],
    'db2.pool' => [
        'class'    => Pool::class,
        'database' => bean('db2'),
    ],
    //
    //// db3配置
    'db3'               => [
        'class'    => Database::class,
        'dsn'      => 'mysql:dbname=test3;host=127.0.0.1',
        'username' => 'root',
        'password' => 'swoft123456'
    ],
    'db3.pool'          => [
        'class'    => Pool::class,
        'database' => bean('db3')
    ],
    //
    //// 数据迁移
    'migrationManager'  => [
        'migrationPath' => '@database/Migration',
    ],

    //// redis配置
    'redis'             => [
        'class'    => RedisDb::class,
        'host'     => '127.0.0.1',
        'port'     => 6379,
        'database' => 0,
        'option'   => [
            'prefix' => 'swoft:'
        ]
    ],

    //// amqp rabbitmq
//    'amqp'        => [
//        'class'    => Swoft\Amqp\Client::class,
//        'auths'    => [
//            [
//                'host'     => env('AMQP_HOST', 'localhost'),
//                'port'     => env('AMQP_PORT', '5672'),
//                'user'     => env('AMQP_USER', 'admin'),
//                'password' => env('AMQP_PASSWORD', 'admin'),
//                'vhost'    => '/',
//            ],
//        ],
//
//        'settings' => [
//            //// 默认
//            'default' => [
//                'exchange' => ['name' => 'exchange_swoft_amqp', 'type' => \PhpAmqpLib\Exchange\AMQPExchangeType::TOPIC],
//                'queue'    => ['name' => 'queue_swoft_amqp'],
//                'route'    => ['key' => ''],
//            ],
//            'stats_social' => [
//                'exchange' => ['name' => 'exchange_swoft_amqp_stats_social', 'type' => \PhpAmqpLib\Exchange\AMQPExchangeType::TOPIC],
//                'queue'    => ['name' => 'queue_swoft_amqp_stats_social'],
//                'route'    => ['key' => ''],
//            ]
//        ]
//    ],
//
//    'amqp.pool'   => [
//        'class'  => Swoft\Amqp\Pool::class,
//        'client' => bean('amqp'),
//    ],

    //// [RPC服务] RPC Client 配置
    'user'              => [
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
    'user.pool'         => [
        'class'  => ServicePool::class,
        'client' => bean('user'),
    ],

    //// [RPC服务] RPC Client 配置
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

    //// [RPC服务(swoft2-rpc-srv)] RPC Client 配置
    'swoft2-rpc-srv'              => [
        'class'   => ServiceClient::class,
        //'host'    => '127.0.0.1',
        'host'    => env('SWOFT2_RPC_SRV_HOST'),
        'port'    => 18407,
        'setting' => [
            'timeout'         => 0.5,
            'connect_timeout' => 1.0,
            'write_timeout'   => 10.0,
            'read_timeout'    => 0.5,
        ],
        'packet'  => bean('rpcClientPacket')
    ],
    'swoft2-rpc-srv.pool'         => [
        'class'  => ServicePool::class,
        'client' => bean('swoft2-rpc-srv'),
    ],


    //// ^2_3^ RPC服务
    'rpcServer'         => [
        'class' => ServiceServer::class,
    ],

    //// ^2_3^ WS服务
    'wsServer'          => [
        'class'   => WebSocketServer::class,
        'port'    => 18308,
        'listener' => [
            // 'rpc' => bean('rpcServer'),
            // 'tcp' => bean('tcpServer'),
        ],
        'on'      => [
            // Enable http handle
            SwooleEvent::REQUEST => bean(RequestListener::class),
        ],
        'debug'   => 1,
        // 'debug'   => env('SWOFT_DEBUG', 0),

        /* @see WebSocketServer::$setting */
        'setting' => [
            'log_file' => alias('@runtime/swoole.log'),
        ],
    ],
    /** @see \Swoft\WebSocket\Server\WsMessageDispatcher */
    'wsMsgDispatcher' => [
        'middlewares' => [
            \App\WebSocket\Middleware\GlobalWsMiddleware::class
        ],
    ],

    /** @see \Swoft\Tcp\Server\TcpServer */
    'tcpServer'         => [
        'port'  => 18309,
        'debug' => 1,
    ],
    /** @see \Swoft\Tcp\Protocol */
    'tcpServerProtocol' => [
        // 'type' => \Swoft\Tcp\Packer\JsonPacker::TYPE,
        'type' => \Swoft\Tcp\Packer\SimpleTokenPacker::TYPE,
        // 'openLengthCheck' => true,
    ],
    /** @see \Swoft\Tcp\Server\TcpDispatcher */
    'tcpDispatcher' => [
        'middlewares' => [
            \App\Tcp\Middleware\GlobalTcpMiddleware::class
        ],
    ],

    'cliRouter'         => [
        // 'disabledGroups' => ['demo', 'test'],
    ]
];

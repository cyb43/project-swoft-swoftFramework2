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
        'process'  => [
//            'monitor' => bean(MonitorProcess::class)
//            'crontab' => bean(CrontabProcess::class)
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
    'httpDispatcher'    => [
        // Add global http middleware
        'middlewares'      => [
            \App\Http\Middleware\FavIconMiddleware::class,
            \Swoft\Http\Session\SessionMiddleware::class,
            // \Swoft\Whoops\WhoopsMiddleware::class,
            // Allow use @View tag
            \Swoft\View\Middleware\ViewMiddleware::class,
        ],
        'afterMiddlewares' => [
            \Swoft\Http\Server\Middleware\ValidatorMiddleware::class
        ]
    ],


    //// [示例]数据库配置
    'db'                => [
        'class'    => Database::class,
        'dsn'      => 'mysql:dbname=test;host=127.0.0.1',
        'username' => 'root',
        'password' => 'swoft123456',
        'charset' => 'utf8mb4',
    ],
    //
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
    'db3'               => [
        'class'    => Database::class,
        'dsn'      => 'mysql:dbname=test2;host=127.0.0.1',
        'username' => 'root',
        'password' => 'swoft123456'
    ],
    'db3.pool'          => [
        'class'    => Pool::class,
        'database' => bean('db3')
    ],
    //
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

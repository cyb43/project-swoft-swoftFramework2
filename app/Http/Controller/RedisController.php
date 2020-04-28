<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Http\Controller;

use RuntimeException;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Redis\Exception\RedisException;
use Swoft\Redis\Pool;
use Swoft\Redis\Redis;
use function sgo;

/**
 * Class RedisController
 * ^2_3^ redis控制器
 *
 * @since 2.0
 * @Controller("redis")
 */
class RedisController
{
    /**
     * redis连接池
     * @Inject()
     *
     * @var Pool
     */
    private $redis;

    /**
     * ^2_3^ poolSet()
     * @RequestMapping("poolSet")
     */
    public function poolSet(): array
    {
        $key   = 'key';
        $value = uniqid();

        $this->redis->set($key, $value);

        $get = $this->redis->get($key);

        $isError = $this->redis->call(function (\Redis $redis) {
            $redis->eval('returnxxxx 1');

            return $redis->getLastError();
        });

        return [$get, $value, $isError];
    }

    /**
     * ^2_3^ set()
     * @RequestMapping()
     */
    public function set(): array
    {
        $key = 'key1';

        $data = [
            'add'    => 11.1,
            'score2' => 11.1,
            'score3' => 11.21
        ];
        $this->redis->zAdd($key, $data);

        $res = Redis::zRangeByScore($key, '11.1', '11.21', ['withscores' => true]);

        return [$res, $res === $data];
    }

    /**
     * ^2_3^ str()
     * @RequestMapping("str")
     */
    public function str(): array
    {
        $key    = 'key';
        $result = Redis::set($key, 'key');

        $keyVal = Redis::get($key);

        $isError = Redis::call(function (\Redis $redis) {
            $redis->eval('return 1');

            return $redis->getLastError();
        });

        $data = [
            $result,
            $keyVal,
            $isError
        ];

        return $data;
    }

    /**
     * ^2_3^ 错误方式
     * Only to use test. The wrong way to use it
     *
     * @RequestMapping("release")
     *
     * @return array
     * @throws RedisException
     */
    public function release(): array
    {
        sgo(function () {
            Redis::connection();
        });

        Redis::connection();

        return ['release'];
    }

    /**
     * ^2_3^ 错误方式
     * Only to use test. The wrong way to use it
     *
     * @RequestMapping("ep")
     *
     * @return array
     */
    public function exPipeline(): array
    {
        sgo(function () {
            Redis::pipeline(function () {
                throw new RuntimeException('');
            });
        });

        Redis::pipeline(function () {
            throw new RuntimeException('');
        });

        return ['exPipeline'];
    }

    /**
     * ^2_3^ 错误方式
     * Only to use test. The wrong way to use it
     *
     * @RequestMapping("et")
     *
     * @return array
     */
    public function exTransaction(): array
    {
        sgo(function () {
            Redis::transaction(function () {
                throw new RuntimeException('');
            });
        });

        Redis::transaction(function () {
            throw new RuntimeException('');
        });

        return ['exPipeline'];
    }
}

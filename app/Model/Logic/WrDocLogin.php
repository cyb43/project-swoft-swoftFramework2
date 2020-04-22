<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: ^2_3^王尔贝
 * Date: 2020/3/23
 * Time: 23:33
 */

namespace App\Model\Logic;


use App\Model\Entity\User;
use Swoft\Bean\Annotation\Mapping\Bean;


/**
 * Class WrDocLogin
 *
 * @since 2.0
 *
 * @Bean()
 *
 * @package App\Model\Logic
 * @author ^2_3^王尔贝
 */
class WrDocLogin
{

    /**
     * 信息
     * @return string
     * @author ^2_3^王尔贝
     */
    public function info(): string
    {
        $user = User::find(1);
        var_dump( $user );

        return 'WrDocService(开发文档示例)_Login';
    }

}
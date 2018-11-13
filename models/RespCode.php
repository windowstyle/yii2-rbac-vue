<?php
/**
 * Created by PhpStorm.
 * User: yan.wang5
 * Date: 2018/11/8
 * Time: 17:25
 */

namespace wyvue\models;


class RespCode
{
    const SUCCESS = 1000;

    const ERROR_FAILED      = 1001;
    const ERROR_PARAM       = 1002;
    const ERROR_EXCEPTION   = 1003;

    const ERROR_TOKEN       = 1010;
    const ERROR_AUTH        = 1011;

}
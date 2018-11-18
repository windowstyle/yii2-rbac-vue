<?php
/**
 * Created by PhpStorm.
 * User: yan.wang5
 * Date: 2018/10/14
 * Time: 13:31
 */

namespace wyrbac\components;

use Yii;
use wyrbac\models\RespCode;
use yii\filters\auth\HttpHeaderAuth;

class AuthToken extends HttpHeaderAuth
{

    public $header = 'X-Token';

    public function handleFailure($response){
        Yii::$app->getResponse()->data = ['code' => RespCode::ERROR_TOKEN,'data' => 'Auth Token Failed.'];
    }
}
<?php
/**
 * Top Secret
 * Created by PhpStorm.
 * User: yan.wang
 * Date: 2018/9/28
 * Time: 13:42
 */

namespace wyrbac\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\RateLimiter;
use wyrbac\components\AuthToken;
use wyrbac\components\AccessControl;

class BaseController extends Controller
{
    public function init()
    {
        Yii::$app->getResponse()->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 登录的权限校验
        $behaviors['authenticator'] = [
            'class'  => AuthToken::className()
        ];

        // 防止请求次数过多
        $behaviors['rateLimiter'] = [
            'class'  => RateLimiter::className()
        ];

        // 登录的权限校验
        $behaviors['auth_access'] = [
            'class'  => AccessControl::className()
        ];

        return $behaviors;
    }

    /**
     * 获取get参数
     * @param      $param
     * @param null $default
     *
     * @return array|mixed
     */
    protected function get_query($param,$default = null){

        return Yii::$app->getRequest()->get($param,$default);
    }

    /**
     * 获取post参数
     * @param      $param
     * @param null $default
     *
     * @return array|mixed
     */
    protected function get_body($param,$default = null){

        return Yii::$app->getRequest()->post($param,$default);
    }
}
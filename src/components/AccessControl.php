<?php
/**
 * Created by PhpStorm.
 * User: yan.wang
 * Date: 2018/10/4
 * Time: 23:20
 */

namespace wyrbac\components;

use Yii;
use yii\base\ActionFilter;
use wyrbac\models\RespCode;

class AccessControl extends ActionFilter
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if(Yii::$app->user->identity->username == 'admin') return true;

        if(!Yii::$app->user->can('/'.Yii::$app->requestedRoute)){
            $content = json_encode(['code' => RespCode::ERROR_AUTH,'data' => 'No operation authority.']);
            Yii::$app->getResponse()->content = $content;
            return false;
        }

        return true;
    }
}
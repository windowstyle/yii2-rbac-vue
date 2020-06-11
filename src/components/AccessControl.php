<?php
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
            Yii::$app->getResponse()->data = ['code' => RespCode::ERROR_AUTH,'data' => 'No operation authority.'];
            return false;
        }

        return true;
    }
}

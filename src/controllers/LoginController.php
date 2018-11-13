<?php

namespace wyvue\controllers;

use wyvue\models\AuthItem;
use wyvue\models\RespCode;
use Yii;
use common\models\LoginForm;
use wyvue\models\UserModel;
use yii\rbac\Item;

class LoginController extends BaseController
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = ['login','register-default']; // 登录无需校验
        $behaviors['auth_access']['except']   = ['login', 'user-info','logout','register-default']; // 权限无需校验

        return $behaviors;
    }

    public function actionLogin(){

        $loginForm = new LoginForm();

        $loginForm->load(['LoginForm' => Yii::$app->getRequest()->post()]);

        if(!$loginForm->login()){
            return ['code' => RespCode::ERROR_FAILED,'data' => current($loginForm->getFirstErrors())];
        }

        // 修改token
        $loginUser = Yii::$app->user->identity; /* @var $loginUser UserModel */

        $loginUser->generateAuthKey();

        $loginUser->save();

        return ['code' => 1000,'data' => ['token' => $loginUser->getAuthKey()]];
    }

    public function actionRegisterDefault($username,$password,$email){
        if($this->get_query('key') != 123) die('Error Request!');

        $user = new UserModel();
        $user->username = $username;
        $user->generateAuthKey();
        $user->setPassword($password);
        $user->email = $email;
        $user->save();

        // 创建superAdmin角色，并赋给admin账号
        if($username == 'admin'){
            $authManager = Yii::$app->authManager;

            $authItem = new AuthItem();
            $authItem->name = 'superadmin';
            $authItem->type = Item::TYPE_ROLE;
            $authItem->description = '超级管理员';

            $authItem->save();

            $authManager->assign($authManager->getRole('superadmin'),$user->id);
        }

        die('添加成功');
    }

    public function actionUserInfo(){
        // 角色查询
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->getId());

        return ['code' => 1000,'data' => [
            'name' => Yii::$app->user->identity->username,
            'roles' => array_keys($roles)
        ]];
    }

    public function actionLogout(){
        $loginUser = Yii::$app->user->identity; /* @var $loginUser UserModel */

        $loginUser->generateAuthKey();

        $loginUser->save();

        return ['code' => 1000,'data' => []];
    }
}

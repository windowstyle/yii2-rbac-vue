<?php

namespace wyvue\controllers;

use wyvue\models\RespCode;
use wyvue\models\UserModel;
use Yii;

class UserController extends BaseController
{

    public function actionIndex()
    {
        $query = UserModel::find();

        $query->filterWhere([
            'id'     => $this->get_query('id'),
            'status' => $this->get_query('status')
        ]);

        $query->andFilterWhere(['like','username',$this->get_query('username')]);

        $total = (clone $query)->count();

        // 分页与排序
        $sort  = $this->get_query('sort', '+id');
        $query->orderBy([substr($sort, 1) => ($sort[0] == '+' ? SORT_ASC : SORT_DESC)]);

        $page  = max(intval($this->get_query('page', 1)), 1);
        $limit = max(intval($this->get_query('limit', 1)), 1);
        $query->offset(($page - 1) * $limit)->limit($limit);

        $items = $query->asArray()->all();

        $authManager = Yii::$app->authManager;
        if($items){
            foreach($items as $key => $item){
                $roles = $authManager->getRolesByUser($item['id']);

                $items[$key]['role'] = $roles ? current(array_keys($roles)) : '';
            }
        }

        // 获取一共有多少角色
        $roles = array_keys($authManager->getRoles());

        return ['code' => RespCode::SUCCESS,'data' => compact('items','roles','total')];
    }

    public function actionSave(){
        $id = $this->get_body('id');

        $user = $id ? UserModel::findOne($id) : new UserModel();

        if(!$user) return ['code' => RespCode::ERROR_FAILED,'data' => 'User not found!'];

        $user->load(['UserModel' => Yii::$app->getRequest()->post()]);

        if($user->validate() && $user->save()){
            return ['code' => RespCode::SUCCESS,'data' => 'Save Success!'];
        }

        return ['code' => RespCode::ERROR_FAILED,'data' => current($user->getFirstErrors())];
    }

    public function actionDelete(){
        $ids = $this->get_body('ids');

        if(!$ids) return ['code' => RespCode::ERROR_FAILED,'data' => 'Please select your delete row!'];

        UserModel::deleteAll(['id' => $ids]);

        return ['code' => RespCode::SUCCESS,'data' => 'Delete Success!'];
    }

    public function actionSaveRole(){
        $userId = $this->get_body('id');
        $role   = $this->get_body('role');

        if(!$userId) return ['code' => RespCode::ERROR_PARAM,'data' => 'The params user id error!'];

        $authManager = Yii::$app->authManager;

        $userRoles = $authManager->getRolesByUser($userId);

        if(!$role && !$userRoles) return ['code' => RespCode::ERROR_PARAM,'data' => 'Do nothing!'];

        // 删除所有的角色
        foreach($userRoles as $userRole) $authManager->revoke($userRole,$userId);

        // 获取新添加的角色
        $roleObj = $authManager->getRole($role);

        if(!$roleObj) return ['code' => RespCode::ERROR_PARAM,'data' => 'The role is invalid!'];

        $authManager->assign($roleObj,$userId);

        return ['code' => RespCode::SUCCESS,'data' => 'success'];
    }
}

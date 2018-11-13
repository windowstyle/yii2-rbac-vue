<?php

namespace wyvue\controllers;

use wyvue\models\RespCode;
use Yii;
use yii\db\Query;
use yii\rbac\DbManager;
use yii\rbac\Item;
use wyvue\models\AuthItem;
use yii\rbac\Permission;

/**
 * AuthController implements the CRUD actions for AuthItemModel model.
 */
class AuthController extends BaseController
{

    public function actionIndex()
    {
        $authManager = Yii::$app->authManager;/* @var $authManager DbManager */

        $query = new Query();

        $query->from($authManager->itemTable);

        $query->where(['type' => Item::TYPE_ROLE]);

        $query->filterWhere(['like','name',$this->get_query('name')]);

        $total = (clone $query)->count();

        // 分页与排序
        $sort  = $this->get_query('sort', '+id');
        $query->orderBy([substr($sort, 1) => ($sort[0] == '+' ? SORT_ASC : SORT_DESC)]);

        $page  = max(intval($this->get_query('page', 1)), 1);
        $limit = max(intval($this->get_query('limit', 1)), 1);
        $query->offset(($page - 1) * $limit)->limit($limit);

        $items = $query->all();

        if($items){
            foreach($items as $key => $item){
                $items[$key]['permissions'] = array_keys($authManager->getPermissionsByRole($item['name']));
            }
        }

        $routes = array_keys($authManager->getPermissions());

        return ['code' => RespCode::SUCCESS,'data' => compact('items','routes','total')];
    }

    public function actionSave(){
        $authItem = AuthItem::find($this->get_body('name',''),Item::TYPE_ROLE);

        if(!$authItem) {
            $authItem = new AuthItem();
            $authItem->type = Item::TYPE_ROLE;
        }

        $authItem->load(['AuthItem' => Yii::$app->getRequest()->post()]);

        if(!$authItem->save()) return ['code' => RespCode::ERROR_FAILED,'data' => 'Db Error!'];

        return ['code' => RespCode::SUCCESS,'data' => 'Success!'];
    }

    public function actionDelete()
    {
        $names = $this->get_body('names');

        if(!$names) return ['code' => RespCode::ERROR_FAILED,'data' => 'Please select your delete row!'];

        foreach($names as $name){
            $authItem = AuthItem::find($name,Item::TYPE_ROLE);

            if($authItem) Yii::$app->authManager->remove($authItem->getItem());
        }

        return ['code' => RespCode::SUCCESS,'data' =>'Success!'];
    }

    public function actionRefreshRoutes(){
        $authManager = Yii::$app->authManager;

        $permissions  = $authManager->getPermissions();

        $newRoutes = AuthItem::getAllRoutes();

        // 添加没有的路由
        foreach($newRoutes as $route){
            if(!isset($permissions[$route])){
                $permission = new Permission();
                $permission->name = $route;
                $authManager->add($permission);
            }else{
                unset($permissions[$route]);
            }
        }

        // 删除不存在的路由
        if($permissions){
            foreach ($permissions as $permission) {
                $authManager->remove($permission);
            }
        }

        return ['code' => RespCode::SUCCESS,'data' =>'Success!'];
    }

    public function actionSavePermissions(){
        $authManager = Yii::$app->authManager;

        $role = $authManager->getRole($this->get_body('name'));

        if(!$role) return ['code' => RespCode::ERROR_FAILED,'data' => 'The role not found!'];

        $rolePermissions = $authManager->getPermissionsByRole($this->get_body('name'));

        $newPermissions = $this->get_body('permissions');

        if($newPermissions){
            foreach($newPermissions as $item){
                if(!isset($rolePermissions[$item])){
                    $permission = $authManager->getPermission($item);
                    $authManager->addChild($role,$permission);
                }else{
                    unset($rolePermissions[$item]);
                }
            }
        }

        if($rolePermissions){
            foreach($rolePermissions as $item){
                $authManager->removeChild($role,$item);
            }
        }

        return ['code' => RespCode::SUCCESS,'data' => 'success'];
    }
}

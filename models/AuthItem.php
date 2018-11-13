<?php

namespace wyvue\models;

use Yii;
use yii\rbac\Item;
use yii\rbac\Role;
use yii\rbac\Permission;
use yii\helpers\Json;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "{{%auth_item}}".
 *
 * @property string $name
 * @property int $type
 * @property string $description
 * @property string $ruleName
 * @property string $data
 * @property Item $item
 */
class AuthItem extends \yii\base\Model
{
    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;

    /* @var $_item Item */
    private $_item = null;

    /**
     * Initialize object.
     *
     * @param Item  $item
     * @param array $config
     */
    public function __construct($item = null, $config = [])
    {
        $this->_item = $item;
        if ($item !== null) {
            $this->name        = $item->name;
            $this->type        = $item->type;
            $this->description = $item->description;
            $this->ruleName    = $item->ruleName;
            $this->data        = $item->data === null ? null : Json::encode($item->data);
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = Yii::$app->authManager->getRules();
        return [
            [['name', 'description'], 'required'],
            ['description', 'string'],
            ['name', 'string', 'max' => 64],
            ['name', 'validateName'],
            ['type', 'integer'],
            [['description', 'data', 'ruleName'], 'default'],
            ['ruleName', 'in', 'range' => array_keys($rules), 'message' => 'Rule not exists',],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name'        => '名称',
            'type'        => '类型',
            'description' => '描述',
            'ruleName'    => '规则名',
            'data'        => 'Data'
        ];
    }

    /**
     * 校验名称是否合法
     * @return bool
     */
    public function validateName()
    {
        // 判断名称是否正确
        if(StringHelper::startsWith($this->name,'/')){
            $this->addError('name','名称不得以/开头！');
            return false;
        }

        // 判断是否重复
        if($this->_item !== null && ($this->_item->name == $this->name)) return true;

        $authManager = Yii::$app->authManager;
        $value = $this->name;
        if ($authManager->getRole($value) !== null || $authManager->getPermission($value) !== null) {
            $message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
            $params = [
                'attribute' => $this->getAttributeLabel('name'),
                'value' => $value,
            ];
            $this->addError('name', Yii::$app->getI18n()->format($message, $params, Yii::$app->language));
            return false;
        }

        return true;
    }

    public static function find($name,$type){
        if(empty($name)) return null;

        $manager = Yii::$app->authManager;

        $item = $type == Item::TYPE_ROLE ? $manager->getRole($name) : $manager->getPermission($name);

        return $item === null ? null : new static($item);
    }

    /**
     * @return Role | Permission | Item
     */
    public function getItem(){
        return $this->_item;
    }

    public function save(){
        if(!$this->validate()) return false;

        $manager = Yii::$app->authManager;

        if ($this->_item === null) {
            $this->_item = $this->type == Item::TYPE_ROLE ? $manager->createRole($this->name) : $manager->createPermission($this->name);
        } else {
            $oldName = $this->_item->name;
        }

        $this->_item->name        = $this->name;
        $this->_item->description = $this->description;
        $this->_item->ruleName    = $this->ruleName;
        $this->_item->data        = $this->data === null || $this->data === '' ? null : Json::decode($this->data);

        isset($oldName) ? $manager->update($oldName, $this->_item) : $manager->add($this->_item);

        return true;
    }

    // 关于权限路由的获取


    /**
     * 获取所有的可用路由
     * @return array
     */
    public static function getAllRoutes(){
        $result = [];

        (new static)->getRouteRecrusive(Yii::$app,$result);

        $result = array_reverse($result);

        return $result;
    }

    /**
     * Get route(s) recrusive.
     *
     * @param \yii\base\Module $module
     * @param array            $result
     */
    private function getRouteRecrusive($module, &$result)
    {
        foreach ($module->getModules() as $id => $child) {
            if (($child = $module->getModule($id)) !== null) $this->getRouteRecrusive($child, $result);
        }

        foreach ($module->controllerMap as $id => $type) {
            $this->getControllerActions($type, $id, $module, $result);
        }

        $this->getControllerFiles($module, trim($module->controllerNamespace, '\\').'\\', '', $result);
    }

    private function getControllerFiles($module, $namespace, $prefix, &$result)
    {
        $path = @Yii::getAlias('@'.str_replace('\\', '/', $namespace));
        if (!is_dir($path)) return;

        foreach (scandir($path) as $file) {
            if ($file == '.' || $file == '..') continue;

            if (is_dir($path.'/'.$file)) {
                $this->getControllerFiles($module, $namespace.$file.'\\', $prefix.$file.'/', $result);
            } elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
                $id = Inflector::camel2id(substr(basename($file), 0, -14));
                $className = $namespace.Inflector::id2camel($id).'Controller';
                if (strpos($className, '-') === false && class_exists($className) && is_subclass_of($className, 'yii\base\Controller')) {
                    $this->getControllerActions($className, $prefix.$id, $module, $result);
                }
            }
        }
    }

    private function getControllerActions($type, $id, $module, &$result)
    {
        /* @var $controller \yii\base\Controller */
        $controller = Yii::createObject($type, [$id, $module]);
        $prefix = '/'.$controller->uniqueId.'/';

        foreach ($controller->actions() as $id => $value) $result[] = $prefix.$id;

        $class = new \ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
                $result[] = $prefix.Inflector::camel2id(substr($name, 6));
            }
        }
    }

}

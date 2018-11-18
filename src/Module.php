<?php
/**
 * Top Secret
 * Created by PhpStorm.
 * User: yan.wang
 * Date: 2018/9/25
 * Time: 17:54
 */

namespace wyrbac;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'wyrbac\controllers';

    public function init()
    {
        // 设置路径
        $this->setAliases(['@'.$this->id => __DIR__]);

        parent::init();
    }
}

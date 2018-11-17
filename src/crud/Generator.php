<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace wyvue\crud;

use Yii;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\gii\generators\crud\Generator AS YiiGenerator;

class Generator extends YiiGenerator
{
    public $baseControllerClass = 'wyvue\controllers\BaseController';
    /**
     * {@inheritdoc}
     */
    public function generateSearchConditions()
    {
        $columns = [];
        if (($table = $this->getTableSchema()) === false) {
            $class = $this->modelClass;
            /* @var $model \yii\base\Model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_TINYINT:
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $hashConditions[] = "'{$column}' => \$this->get_query('{$column}'),";
                    break;
                default:
                    $likeKeyword = $this->getClassDbDriverName() === 'pgsql' ? 'ilike' : 'like';
                    $likeConditions[] = "->andFilterWhere(['{$likeKeyword}', '{$column}', \$this->get_query('{$column}')])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function generateSearchField($attribute)
    {
        return "<el-input :placeholder=\"\$t('".$this->controllerID.".".$attribute."')\" v-model=\"searchModel.".$attribute."\" style=\"width: 100px;\" class=\"filter-item\"/>";
    }


    /**
     * Generates code for table colume
     * @param string $attribute
     * @return string
     */
    public function generateListField($attribute)
    {
        return "<el-table-column :label=\"\$t('".$this->controllerID.".".$attribute."')\" prop=\"".$attribute."\" sortable=\"custom\" align=\"center\">
                <template slot-scope=\"props\">
                    <span>{{ props.row.".$attribute." }}</span>
                </template>
            </el-table-column>";
    }

    /**
     * Generates code for table colume
     * @param string $attribute
     * @return string
     */
    public function generateDialogField($attribute)
    {
        if($attribute == 'id'){
            return "<el-form-item label=\"ID\" label-width=\"100px\" v-show=\"false\" prop=\"id\">
                    <el-input v-model=\"formDialogModel.id\" autocomplete=\"off\" readonly=\"readonly\"></el-input>
                </el-form-item>";
        }else{
            return "<el-form-item :label=\"\$t('".$this->controllerID.".".$attribute."')\" label-width=\"100px\" prop=\"".$attribute."\">
                    <el-input v-model=\"formDialogModel.".$attribute."\" autocomplete=\"off\"></el-input>
                </el-form-item>";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

        $viewPath = dirname(Yii::getAlias('@backend')) . '/vueview/src/views';

        $files = [
            new CodeFile($controllerFile, $this->render('controller.php')),
            new CodeFile("$viewPath/".$this->controllerID.".vue", $this->render("views/index.php"))
        ];

        return $files;
    }
}

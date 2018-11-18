<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\helpers\StringHelper;

/* @var $generator wyrbac\crud\Generator */

$controllerClass  = StringHelper::basename($generator->controllerClass);
$modelClass       = StringHelper::basename($generator->modelClass);
$searchConditions = $generator->generateSearchConditions();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
use wyrbac\models\RespCode;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    /**
     * Lists all <?= $modelClass ?> models.
     * @return array
     */
    public function actionIndex()
    {
        $query = <?= $modelClass ?>::find();

        <?= implode("\n        ", $searchConditions) ?>

        $total = (clone $query)->count();

        // 分页与排序
        $sort  = $this->get_query('sort', '+id');
        $query->orderBy([substr($sort, 1) => ($sort[0] == '+' ? SORT_ASC : SORT_DESC)]);

        $page  = max(intval($this->get_query('page', 1)), 1);
        $limit = max(intval($this->get_query('limit', 1)), 1);
        $query->offset(($page - 1) * $limit)->limit($limit);

        $items = $query->asArray()->all();

        return ['code' => RespCode::SUCCESS,'data' => compact('items','total')];
    }

    /**
     * Save <?= $modelClass ?> model.
     * If save is successful, the response will return 1000,otherwise return 1001.
     * @return array
     */
    public function actionSave()
    {
        $id = $this->get_body('id');

        $model = $id ? <?= $modelClass ?>::findOne($id) : new <?= $modelClass ?>();

        if(!$model) return ['code' => RespCode::ERROR_FAILED,'data' => '<?= $modelClass ?> not found!'];

        $model->load(['<?= $modelClass ?>' => Yii::$app->getRequest()->post()]);

        if($model->validate() && $model->save()){
            return ['code' => RespCode::SUCCESS,'data' => 'Save Success!'];
        }

        return ['code' => RespCode::ERROR_FAILED,'data' => current($model->getFirstErrors())];
    }

    /**
     * Deletes some existing <?= $modelClass ?> model.
     * If deletion is successful, the response will return 1000,otherwise return 1001.
     * @return array
     */
    public function actionDelete()
    {
        $ids = $this->get_body('ids');

        if(!$ids) return ['code' => RespCode::ERROR_FAILED,'data' => 'Please select your delete row!'];

        <?= $modelClass ?>::deleteAll(['id' => $ids]);

        return ['code' => RespCode::SUCCESS,'data' => 'Delete Success!'];
    }
}

<?php

namespace wyrbac\models;

use Yii;
use yii\base\Event;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use common\models\User;

/**
 * This is the model class for table "%user".
 */
class UserModel extends User
{

    public $permissions;

    public $password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username','email'], 'required'],
            [['password'],'string','min' => 6],
            ['status', 'default', 'value' => User::STATUS_ACTIVE],
            ['status', 'in', 'range' => [User::STATUS_ACTIVE, User::STATUS_DELETED]],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                   => 'ID',
            'username'             => '用户名',
            'auth_key'             => '权限Key',
            'password_hash'        => '密码Hash值',
            'password_reset_token' => '重置密码token',
            'email'                => '邮箱',
            'status'               => '状态',
            'created_at'           => '添加时间',
            'updated_at'           => '更新时间',
        ];
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'password_hash',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'password_hash',
                ],
                'value' => function(Event $event){
                    $model = $event->sender;/* @var $model UserModel */

                    if(!$model->password) return $model->password_hash;

                    return Yii::$app->security->generatePasswordHash($model->password);
                }
            ],
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'auth_key'
                ],
                'value' => function(Event $event){
                    return Yii::$app->security->generateRandomString();
                }
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @param $data
     * @param $formName
     *
     * @return mixed
     */
    public function load($data, $formName = null){
        // 解决修改密码失败的bug
        if(!empty($data['UserModel']['password'])) $data['UserModel']['password_hash'] = '';

        return parent::load($data, $formName);
    }
}

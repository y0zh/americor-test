<?php

namespace app\models\user;

use Yii;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'created_at', 'updated_at'], 'required'],
            [[
                'status',
                'created_at',
                'updated_at',
            ], 'integer'],
            [['username'], 'string', 'max' => 255],

            [['username'], 'unique'],
            [['email'], 'email'],

            ['status', 'default', 'value' => UserStatusEnum::STATUS_ACTIVE],
            ['status', 'in', 'range' => [UserStatusEnum::STATUS_ACTIVE, UserStatusEnum::STATUS_DELETED, UserStatusEnum::STATUS_HIDDEN]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username (login)'),
            'statusText' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return array
     */
    public static function getStatusTexts()
    {
        return [
            UserStatusEnum::STATUS_ACTIVE => Yii::t('app', 'Active'),
            UserStatusEnum::STATUS_DELETED => Yii::t('app', 'Deleted'),
            UserStatusEnum::STATUS_HIDDEN => Yii::t('app', 'Hidden'),
        ];
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        $a = self::getStatusTexts();
        return isset($a[$this->status]) ? $a[$this->status] : $this->status;
    }
}

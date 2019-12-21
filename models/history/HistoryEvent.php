<?php

namespace app\models\history;

use Yii;

/**
 * This is the model class for table "{{%event}}"
 *
 * @property integer $id
 * @property string $name
 * @property string $text
 * @property string $template
 */
class HistoryEvent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%event}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'text', 'template'], 'string'],
            [['name'], 'required'],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'text' => Yii::t('app', 'Text'),
            'template' => Yii::t('app', 'Template'),
        ];
    }
}
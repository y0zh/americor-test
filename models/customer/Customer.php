<?php

namespace app\models\customer;


use Yii;


/**
 * This is the model class for table "{{%customer}}".
 *
 * @property integer $id
 * @property string $name
  */
class Customer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return array
     */
    public static function getQualityTexts()
    {
        return [
            CustomerQualityEnum::QUALITY_ACTIVE => Yii::t('app', 'Active'),
            CustomerQualityEnum::QUALITY_REJECTED => Yii::t('app', 'Rejected'),
            CustomerQualityEnum::QUALITY_COMMUNITY => Yii::t('app', 'Community'),
            CustomerQualityEnum::QUALITY_UNASSIGNED => Yii::t('app', 'Unassigned'),
            CustomerQualityEnum::QUALITY_TRICKLE => Yii::t('app', 'Trickle'),
        ];
    }

    /**
     * @param $quality
     * @return mixed|null
     */
    public static function getQualityTextByQuality($quality)
    {
        return self::getQualityTexts()[$quality] ?? $quality;
    }

    /**
     * @return array
     */
    public static function getTypeTexts()
    {
        return [
            CustomerTypeEnum::TYPE_LEAD => Yii::t('app', 'Lead'),
            CustomerTypeEnum::TYPE_DEAL => Yii::t('app', 'Deal'),
            CustomerTypeEnum::TYPE_LOAN => Yii::t('app', 'Loan'),
        ];
    }

    /**
     * @param $type
     * @return mixed
     */
    public static function getTypeTextByType($type)
    {
        return self::getTypeTexts()[$type] ?? $type;
    }
}
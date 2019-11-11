<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fax".
 *
 * @property integer $id
 * @property string $ins_ts
 * @property integer $user_id
 * @property integer $document_id
 * @property string $from
 * @property string $to
 * @property integer $status
 * @property integer $direction
 * @property string $error_message
 * @property string $twilio_sid
 * @property string $twilio_account_sid
 * @property string $twilio_direction
 * @property string $twilio_status
 * @property string $twilio_error_code
 * @property string $twilio_error_message
 * @property string $twilio_document_request_date
 * @property string $last_send_ts
 * @property integer $count_attempt_send
 * @property integer $type
 * @property string $typeText
 *
 * @property User $user
 */
class Fax extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_SENT = 1;

    const DIRECTION_INCOMING = 0;
    const DIRECTION_OUTGOING = 1;

    const TWILIO_STATUS_QUEUED = 'queued';
    const TWILIO_STATUS_DELIVERED = 'delivered';

    const TYPE_POA_ATC = 'poa_atc';
    const TYPE_REVOCATION_NOTICE = 'revocation_notice';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fax';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['ins_ts', 'twilio_document_request_date', 'last_send_ts'], 'safe'],
            [['user_id', 'document_id', 'status', 'direction', 'count_attempt_send'], 'integer'],
            [['from', 'to', 'twilio_sid', 'twilio_account_sid', 'twilio_status', 'twilio_error_code', 'twilio_error_message', 'error_message'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ins_ts' => Yii::t('app', 'Created Time'),
            'user_id' => Yii::t('app', 'User ID'),
            'document_id' => Yii::t('app', 'Document ID'),
            'from' => Yii::t('app', 'From'),
            'to' => Yii::t('app', 'To'),
            'twilio_sid' => Yii::t('app', 'Twilio Sid'),
            'twilio_account_sid' => Yii::t('app', 'Twilio Account Sid'),
            'twilio_direction' => Yii::t('app', 'Direction'),
            'twilio_status' => Yii::t('app', 'Status'),
            'twilio_error_code' => Yii::t('app', 'Error Code'),
            'twilio_error_message' => Yii::t('app', 'Error Message'),
            'twilio_document_request_date' => Yii::t('app', 'Twilio Document Request Ts'),
            'document.name' => Yii::t('app', 'Document'),
            'document.customer.name' => Yii::t('app', 'Client'),
            'creditor.name' => Yii::t('app', 'Creditor'),
            'user.fullname' => Yii::t('app', 'User'),
            'count_attempt_send' => Yii::t('app', 'Attempts'),
            'last_send_ts' => Yii::t('app', 'Sent Time'),
            'typeText' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return array
     */
    public static function getTypeTexts()
    {
        return [
            self::TYPE_POA_ATC => Yii::t('app', 'POA/ATC'),
            self::TYPE_REVOCATION_NOTICE => Yii::t('app', 'Revocation'),
        ];
    }

    /**
     * @return mixed|string
     */
    public function getTypeText()
    {
        return self::getTypeTexts()[$this->type] ?? $this->type;
    }

}

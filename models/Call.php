<?php

namespace app\models;

use common\models\interfaces\CallInterface;
use Yii;

/**
 * This is the model class for table "{{%call}}".
 *
 * @property integer $id
 * @property string $ins_ts
 * @property integer $direction
 * @property integer $user_id
 * @property integer $customer_id
 * @property integer $status
 * @property integer $viewed
 * @property string $phone_from
 * @property string $phone_to
 * @property string $info
 * @property string $comment
 * @property integer $applicant_id
 * @property integer $disposition_id
 * @property string $switchvox_job_id
 * @property string $type
 * @property string $hangup_ts
 * @property integer $pbx
 * @property string $answered_ts
 * @property string $outcome
 * @property string $records
 * @property integer $duration
 * @property string $extension_type
 * @property string $created_ts
 * @property string $extension
 * @property boolean $is_warning
 * @property string $client_phone
 *
 * -- magic properties
 * @property string $statusText
 * @property string $directionText
 * @property string $totalStatusText
 * @property string $totalDisposition
 * @property string $outcomeText
 * @property string $durationText
 * @property string $start_time
 *
 * @property Customer $customer
 * @property User $user
 */
class Call extends \yii\db\ActiveRecord
{
    const TYPE_NEW = 0;
    const TYPE_EXISTING = 1;

    const VIEWED_NO = 0;
    const VIEWED_YES = 1;

    const STATUS_NO_ANSWERED = 0;
    const STATUS_ANSWERED = 1;

    const DIRECTION_INCOMING = 0;
    const DIRECTION_OUTGOING = 1;

    const OUTCOME_DISPOSITION = 'disposition';
    const OUTCOME_WRONG_NUMBER = 'wrong_number';
    const OUTCOME_NO_DISPOSITION = 'no_disposition';
    const OUTCOME_APPOINTMENT_SCHEDULED = 'appointment_scheduled';

    public $duration = 720;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%call}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ins_ts', 'hangup_ts', 'answered_ts', 'created_ts'], 'safe'],
            [['direction', 'phone_from', 'phone_to', 'type', 'status', 'viewed'], 'required'],
            [['direction', 'user_id', 'customer_id', 'type', 'status', 'applicant_id', 'viewed', 'pbx', 'duration', 'disposition_id'], 'integer'],
            [['comment', 'info', 'records', 'extension_type', 'extension'], 'string'],
            [['switchvox_job_id', 'phone_from', 'phone_to', 'outcome'], 'string', 'max' => 255],
            [['is_warning'], 'boolean'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            //[['outcome'], 'required', 'on' => self::SCENARIO_DISPOSITION]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ins_ts' => Yii::t('app', 'Date'),
            'direction' => Yii::t('app', 'Direction'),
            'directionText' => Yii::t('app', 'Direction'),
            'user_id' => Yii::t('app', 'User ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'status' => Yii::t('app', 'Status'),
            'statusText' => Yii::t('app', 'Status'),
            'phone_from' => Yii::t('app', 'Caller Phone'),
            'phone_to' => Yii::t('app', 'Dialed Phone'),
            'comment' => Yii::t('app', 'Comment'),
            'totalDisposition' => Yii::t('app', 'Comment'),
            'applicant_id' => Yii::t('app', 'Applicant ID'),
            'user.fullname' => Yii::t('app', 'User'),
            'customer.name' => Yii::t('app', 'Client'),
            'totalStatusText' => Yii::t('app', 'Status'),
            'outcome' => Yii::t('app', 'Call Outcome'),
            'records' => Yii::t('app', 'Records'),
            'duration' => Yii::t('app', 'Duration'),
            'durationText' => Yii::t('app', 'Duration'),
            'disposition_id' => Yii::t('app', 'Disposition'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getClient_phone()
    {
        return $this->direction == self::DIRECTION_INCOMING ? $this->phone_from : $this->phone_to;
    }

    /**
     * @return mixed|string
     */
    public function getTotalStatusText()
    {
        if (
            $this->status == self::STATUS_NO_ANSWERED
            && $this->direction == self::DIRECTION_INCOMING
        ) {
            return Yii::t('app', 'Missed Call');
        }

        if (
            $this->status == self::STATUS_NO_ANSWERED
            && $this->direction == self::DIRECTION_OUTGOING
        ) {
            return Yii::t('app', 'Client No Answer');
        }

        $msg = $this->getFullDirectionText();

        if ($this->duration) {
            $msg .= ' (' . $this->getDurationText() . ')';
        }

        return $msg;
    }

    /**
     * @param bool $hasComment
     * @return string
     */
    public function getTotalDisposition($hasComment = true)
    {
        $t = [];
        if (isset($this->outcomeText) && $this->outcomeText) {
            $t[] = $this->outcomeText;
        }
        if ($hasComment && $this->comment) {
            $t[] = $this->comment;
        }
        return implode(': ', $t);
    }

    /**
     * @return array
     */
    public static function getFullDirectionTexts()
    {
        return [
            self::DIRECTION_INCOMING => Yii::t('app', 'Incoming Call'),
            self::DIRECTION_OUTGOING => Yii::t('app', 'Outgoing Call'),
        ];
    }

    /**
     * @return mixed|string
     */
    public function getFullDirectionText()
    {
        $a = self::getFullDirectionTexts();
        return $a[$this->direction] ?? $this->direction;
    }

    /**
     * @return string
     */
    public function getDurationText()
    {
        if (!is_null($this->duration)) {
            return $this->duration >= 3600 ? gmdate("H:i:s", $this->duration) : gmdate("i:s", $this->duration);
        }
        return '00:00';
    }
}

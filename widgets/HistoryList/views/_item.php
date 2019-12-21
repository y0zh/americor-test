<?php

use app\models\call\CallStatusEnum;
use app\models\customer\Customer;
use app\widgets\HistoryList\helpers\HistoryListHelper;
use yii\helpers\Html;
use app\models\DirectionEnum;

/** @var $model \app\models\history\HistorySearch */

$object = $model->obj;

switch ($model->event->template) {
    case 'task':
        echo $this->render('_item_common', [
            'user' => $model->user,
            'body' => HistoryListHelper::getBodyByModel($model),
            'iconClass' => 'fa-check-square bg-yellow',
            'footerDatetime' => $model->ins_ts,
            'footer' => isset($object->customerCreditor->name) ? "Creditor: " . $object->customerCreditor->name : ''
        ]);
        break;
    case 'sms':
        echo $this->render('_item_common', [
            'user' => $model->user,
            'body' => HistoryListHelper::getBodyByModel($model),
            'footer' => $object->direction == DirectionEnum::DIRECTION_INCOMING ?
                Yii::t('app', 'Incoming message from {number}', [
                    'number' => $object->phone_from ?? ''
                ]) : Yii::t('app', 'Sent message to {number}', [
                    'number' => $object->phone_to ?? ''
                ]),
            'iconIncome' => $object->direction == DirectionEnum::DIRECTION_INCOMING,
            'footerDatetime' => $model->ins_ts,
            'iconClass' => 'icon-sms bg-dark-blue'
        ]);
        break;
    case 'fax':
        echo $this->render('_item_common', [
            'user' => $model->user,
            'body' => HistoryListHelper::getBodyByModel($model).
                ' - ' .
                (isset($object->document) ? Html::a(
                    Yii::t('app', 'view document'),
                    $object->document->getViewUrl(),
                    [
                        'target' => '_blank',
                        'data-pjax' => 0
                    ]
                ) : ''),
            'footer' => Yii::t('app', '{type} was sent to {group}', [
                'type' => $object ? $object->getTypeText() : 'Fax',
                'group' => isset($object->creditorGroup) ? Html::a($object->creditorGroup->name, ['creditors/groups'], ['data-pjax' => 0]) : ''
            ]),
            'footerDatetime' => $model->ins_ts,
            'iconClass' => 'fa-fax bg-green'
        ]);
        break;
    case 'change_type':
        echo $this->render('_item_statuses_change', [
            'model' => $model,
            'oldValue' => Customer::getTypeTextByType($model->getDetailOldValue('type')),
            'newValue' => Customer::getTypeTextByType($model->getDetailNewValue('type'))
        ]);
        break;
    case 'change_quality':
        echo $this->render('_item_statuses_change', [
            'model' => $model,
            'oldValue' => Customer::getQualityTextByQuality($model->getDetailOldValue('quality')),
            'newValue' => Customer::getQualityTextByQuality($model->getDetailNewValue('quality')),
        ]);
        break;

    case 'call':
        $answered = $object && $object->status == CallStatusEnum::STATUS_ANSWERED;

        echo $this->render('_item_common', [
            'user' => $model->user,
            'content' => $object->comment ?? '',
            'body' => HistoryListHelper::getBodyByModel($model),
            'footerDatetime' => $model->ins_ts,
            'footer' => isset($object->applicant) ? "Called <span>{$object->applicant->name}</span>" : null,
            'iconClass' => $answered ? 'md-phone bg-green' : 'md-phone-missed bg-red',
            'iconIncome' => $answered && $object->direction == DirectionEnum::DIRECTION_INCOMING
        ]);

        break;

    default:
        echo $this->render('_item_common', [
            'user' => $model->user,
            'body' => HistoryListHelper::getBodyByModel($model),
            'bodyDatetime' => $model->ins_ts,
            'iconClass' => 'fa-gear bg-purple-light'
        ]);
        break;
}

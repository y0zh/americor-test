<?php

namespace app\widgets\HistoryList\helpers;

use app\models\customer\Customer;
use app\models\history\History;

class HistoryListHelper
{
    public static function getBodyByModel(History $model)
    {
        $object = $model->obj;

        switch ($model->event->template) {
            case 'task':
                return $model->event->text.": " . ($object->title ?? '');
            case 'sms':
                return $object->message ? $object->message : '';
            case 'fax':
                return 'Fax '.$model->event->text;
            case 'change_type':
                return $model->event->text." " .
                    (Customer::getTypeTextByType($model->getDetailOldValue('type')) ?? "not set") . ' to ' .
                    (Customer::getTypeTextByType($model->getDetailNewValue('type')) ?? "not set");
            case 'change_quality':
                return $model->event->text." " .
                    (Customer::getQualityTextByQuality($model->getDetailOldValue('quality')) ?? "not set") . ' to ' .
                    (Customer::getQualityTextByQuality($model->getDetailNewValue('quality')) ?? "not set");
            case 'call':
                return ($object ? $object->totalStatusText . ($object->getTotalDisposition(false) ? " <span class='text-grey'>" . $object->getTotalDisposition(false) . "</span>" : "") : '<i>Deleted</i> ');
            default:
                return $model->event->text;
        }
    }
}
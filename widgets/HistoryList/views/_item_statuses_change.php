<?php
/* @var $model \app\models\History */
/* @var $oldValue string */
/* @var $newValue string */
/* @var $content string */

echo $this->render('_item_common', [
    'user' => $model->user,
    'body' => "$model->eventText " .
        "<span class='tag'>" . ($oldValue ?? "<i>not set</i>") . "</span>" .
        "<span class='arrow'></span>" .
        "<span class='tag'>" . ($newValue ?? "<i>not set</i>") . "</span>",
    'content' => $content ?? null,
    'bodyDatetime' => $model->ins_ts,
    'iconClass' => 'fa-gear bg-purple-light'
]);
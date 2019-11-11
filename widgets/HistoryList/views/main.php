<?php
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $model \app\models\search\HistorySearch */
/* @var $linkExport string */

?>

<?php Pjax::begin(['id' => 'grid-pjax', 'formSelector' => false]); ?>

<?php echo \yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_item',
    'options' => [
        'tag' => 'ul',
        'class' => 'list-group list-group-history'
    ],
    'itemOptions' => [
        'tag' => 'li',
        'class' => 'list-group-item'
    ],
    'emptyTextOptions' => ['class' => 'empty p-20'],
    'layout' => '{items}{pager}',
]); ?>

<?php Pjax::end(); ?>

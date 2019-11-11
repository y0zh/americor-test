<?php
use yii\helpers\Html;

/* @var $user \app\models\User */
/* @var $body string */
/* @var $footer string */
/* @var $footerDatetime string */
/* @var $bodyDatetime string */
/* @var $iconClass string */
?>
<?php if (isset($iconIncome) && $iconIncome): ?>
    <div class="icon-group position-relative pull-xs-left">
        <?php echo Html::tag('i', '', ['class' => "icon icon-circle icon-main white $iconClass"]); ?>
        <span class="tag tag-pill tag-danger up"><i class="icon md-long-arrow-down" aria-hidden="true"></i></span>
    </div>
<?php else: ?>
    <?php echo Html::tag('i', '', ['class' => "icon icon-circle icon-main white $iconClass"]); ?>
<?php endif; ?>

<div class="list-group-content">

    <div class="list-group-inner">
        <div class="list-group-body">

            <div class="list-group-message">
                <?php echo $body ?>
                <?php if (isset($bodyDatetime)): ?>
                    <span class="list-group-datetime">
                        <?= \app\widgets\DateTime\DateTime::widget(['dateTime' => $bodyDatetime]) ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (isset($user)): ?>
                <div class="list-group-side">
                    <?= $user->username; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php if (isset($content) && $content): ?>
        <div class="list-group-footer">
            <?php echo $content ?>
        </div>
    <?php endif; ?>

    <?php if (isset($footer) || isset($footerDatetime)): ?>
        <div class="list-group-footer">
            <?php echo isset($footer) ? $footer : '' ?>
            <?php if (isset($footerDatetime)): ?>
                <span class="list-group-datetime">
                    <?= \app\widgets\DateTime\DateTime::widget(['dateTime' => $footerDatetime]) ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
<?php

use common\models\AchievementSeason;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новый сезон'
    : 'Сезон: ' . $model->title;
?>

<div class="admin-section-head">
    <div>
        <a class="small text-decoration-none" href="<?= Url::to(['index']) ?>">
            ← К сезонам
        </a>
    </div>
</div>

<section class="admin-panel">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row g-3">
        <div class="col-md-7">
            <?= $form->field($model, 'title')->textInput() ?>
        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'code')->textInput([
                'placeholder' => 'season_1',
            ]) ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'status')->dropDownList([
                AchievementSeason::STATUS_PLANNED => 'Запланирован',
                AchievementSeason::STATUS_ACTIVE => 'Активен',
                AchievementSeason::STATUS_CLOSED => 'Закрыт',
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'starts_at')->textInput([
                'placeholder' => '2026-10-09 18:00:00',
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'ends_at')->textInput([
                'placeholder' => '2027-01-31 23:59:59',
            ]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'sort')->input('number') ?>
        </div>
    </div>

    <button class="btn btn-primary" type="submit">
        Сохранить
    </button>

    <?php ActiveForm::end(); ?>
</section>

<?php

use yii\helpers\Html;

$this->title = Yii::t('app', 'Ошибка');
?>
<div class="container ht-section">
    <div class="ht-card">
        <h1><?= Html::encode($this->title) ?></h1>
        <p><?= Html::encode($exception?->getMessage() ?: Yii::t('app', 'Произошла ошибка')) ?></p>
        <a href="/" class="btn btn-primary"><?= Yii::t('app', 'На главную') ?></a>
    </div>
</div>

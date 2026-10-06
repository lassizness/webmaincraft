<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('ui', 'Промокод');

?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('../account/_nav') ?>

        <section>
            <div class="ht-card">

                <h2>
                    <?= Yii::t(
                        'ui',
                        'Активировать промокод'
                    ) ?>
                </h2>

                <?php $form = ActiveForm::begin(); ?>

                <?= $form
                    ->field($model, 'code')
                    ->textInput([
                        'class' =>
                            'form-control text-uppercase',
                    ]) ?>

                <?= Html::submitButton(
                    Yii::t('ui', 'Активировать'),
                    ['class' => 'btn btn-primary']
                ) ?>

                <?php ActiveForm::end(); ?>

            </div>
        </section>

    </div>
</div>

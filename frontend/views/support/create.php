<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('ui', 'Новый тикет');

?>

<div class="container ht-section">
    <div class="account-grid">

        <?= $this->render('../account/_nav') ?>

        <section>
            <div class="ht-card">

                <h2>
                    <?= Yii::t('ui', 'Новое обращение') ?>
                </h2>

                <?php $form = ActiveForm::begin([
                    'options' => [
                        'enctype' => 'multipart/form-data',
                    ],
                ]); ?>

                <?= $form
                    ->field($model, 'subject')
                    ->textInput()
                    ->label(Yii::t('ui', 'Тема')) ?>

                <?= $form
                    ->field($model, 'message')
                    ->textarea(['rows' => 8])
                    ->label(Yii::t('ui', 'Сообщение')) ?>

                <?= $form
                    ->field($model, 'attachments[]')
                    ->fileInput(['multiple' => true])
                    ->label(
                        Yii::t(
                            'ui',
                            'Вложения (до 3 файлов, 5 МБ)'
                        )
                    ) ?>

                <?= Html::submitButton(
                    Yii::t('ui', 'Отправить'),
                    ['class' => 'btn btn-primary']
                ) ?>

                <?php ActiveForm::end(); ?>

            </div>
        </section>

    </div>
</div>

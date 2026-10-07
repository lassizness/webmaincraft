<?php

use yii\helpers\Html;

?>
<h2><?= Yii::t('app', 'Сброс пароля HiveTech') ?></h2>
<p><?= Yii::t('app', 'Для аккаунта {username} запрошен сброс пароля.', [
    'username' => Html::encode($user->username),
]) ?></p>
<p><?= Html::a(Yii::t('app', 'Установить новый пароль'), $url, ['class' => 'mail-button']) ?></p>
<p><?= Yii::t('app', 'Ссылка действует 1 час. После смены пароля старые web- и launcher-сессии будут сброшены.') ?></p>

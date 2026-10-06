<?php

use yii\helpers\Html;

?>
<h2><?= Yii::t('app', 'Добро пожаловать в HiveTech, {username}!', [
    'username' => Html::encode($user->username),
]) ?></h2>
<p><?= Yii::t('app', 'Подтвердите email. До подтверждения аккаунт имеет статус pending и не может войти в HiveTech Launcher.') ?></p>
<p><?= Html::a(Yii::t('app', 'Подтвердить email'), $url) ?></p>
<p><?= Yii::t('app', 'Ссылка действует 24 часа.') ?></p>

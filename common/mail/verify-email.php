<?php

use yii\helpers\Html;

?>
<h2><?= Yii::t('app', 'Добро пожаловать в HiveTech, {username}!', [
    'username' => Html::encode($user->username),
]) ?></h2>
<p><?= Yii::t('app', 'Подтвердите email, чтобы мы знали, что адрес действительно принадлежит вам. Аккаунт уже активен, подтверждение не блокирует вход.') ?></p>
<p><?= Html::a(Yii::t('app', 'Подтвердить email'), $url, ['class' => 'mail-button']) ?></p>
<p><?= Yii::t('app', 'Ссылка действует 24 часа.') ?></p>

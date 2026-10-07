<?php

use yii\helpers\Html;

?>
<h2><?= Yii::t('app', 'Подтвердите новый email HiveTech') ?></h2>
<p><?= Yii::t('app', 'Вы изменили email для аккаунта {username}. Подтвердите новый адрес по ссылке ниже. Вход в аккаунт при этом остаётся доступен.', [
    'username' => Html::encode($user->username),
]) ?></p>
<p><?= Html::a(Yii::t('app', 'Подтвердить новый email'), $url) ?></p>
<p><?= Yii::t('app', 'Ссылка действует 24 часа.') ?></p>

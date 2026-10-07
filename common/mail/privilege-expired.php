<?php

use yii\helpers\Html;

/** @var common\models\User $user */
/** @var common\models\UserPrivilege $privilege */

$url = rtrim((string)(Yii::$app->params['appUrl'] ?? ''), '/') . '/privilege';
?>
<h2><?= Yii::t('ui', 'Статус закончился') ?></h2>

<p>
    <?= Yii::t(
        'ui',
        'Привет, {username}. Срок статуса {status} на сервере {server} закончился.',
        [
            'username' => Html::encode((string)$user->username),
            'status' => Html::encode((string)($privilege->group?->name ?? '')),
            'server' => Html::encode((string)($privilege->server?->name ?? '')),
        ]
    ) ?>
</p>

<p>
    <?= Yii::t('ui', 'Если статус ещё нужен, его можно снова активировать из личного кабинета.') ?>
</p>

<p>
    <a class="mail-button" href="<?= Html::encode($url) ?>">
        <?= Yii::t('ui', 'Открыть игровой статус') ?>
    </a>
</p>

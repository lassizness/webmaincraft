<?php

use yii\helpers\Html;

/** @var common\models\User $user */
/** @var common\models\UserPrivilege $privilege */
/** @var string $noticeType */

$days = match($noticeType) {
    '1d' => 1,
    '3d' => 3,
    default => 7,
};

$url = rtrim((string)(Yii::$app->params['appUrl'] ?? ''), '/') . '/privilege';
?>
<h2><?= Yii::t('ui', 'Статус скоро закончится') ?></h2>

<p>
    <?= Yii::t(
        'ui',
        'Привет, {username}. Статус {status} на сервере {server} закончится примерно через {days} дн.',
        [
            'username' => Html::encode((string)$user->username),
            'status' => Html::encode((string)($privilege->group?->name ?? '')),
            'server' => Html::encode((string)($privilege->server?->name ?? '')),
            'days' => $days,
        ]
    ) ?>
</p>

<p>
    <?= Yii::t('ui', 'Продлить статус можно прямо в личном кабинете, оставшийся срок не сгорит.') ?>
</p>

<p>
    <a class="mail-button" href="<?= Html::encode($url) ?>">
        <?= Yii::t('ui', 'Открыть игровой статус') ?>
    </a>
</p>

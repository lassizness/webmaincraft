<?php

use yii\helpers\Html;

?>
<h2><?= Yii::t('app', 'Ответ поддержки HiveTech #{id}', [
    'id' => $ticket->id,
]) ?></h2>
<p><?= nl2br(Html::encode($message->body)) ?></p>
<p>
    <a href="<?= Yii::$app->params['appUrl'] ?>/support/<?= $ticket->id ?>">
        <?= Yii::t('app', 'Открыть тикет') ?>
    </a>
</p>

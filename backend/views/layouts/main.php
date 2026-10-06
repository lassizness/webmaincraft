<?php
use backend\assets\AppAsset;
use yii\helpers\{Html,Url};
AppAsset::register($this);
?><?php $this->beginPage() ?><!doctype html><html lang="ru"><head><meta charset="<?= Yii::$app->charset ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php $this->registerCsrfMetaTags() ?><title><?= Html::encode($this->title ?: 'HiveTech Admin') ?></title><?php $this->head() ?></head><body><?php $this->beginBody() ?>
<?php if (Yii::$app->user->isGuest): ?>
    <main class="admin-main"><?= $content ?></main>
<?php else: ?>
<div class="admin-shell">
    <aside class="admin-side">
        <a class="admin-brand" href="<?= Url::to(['/dashboard/index']) ?>">HiveTech <small>ADMIN</small></a>
        <nav>
            <?php foreach ([
                ['dashboard/index','Обзор','adminPanel'],['user/index','Пользователи','manageUsers'],['news/index','Новости','manageContent'],
                ['page/index','Страницы','manageContent'],['slide/index','Слайдер','manageContent'],['support/index','Поддержка','manageSupport'],
                ['shop/index','Магазин','manageShop'],['voting/index','Голосования','manageShop'],['privilege/index','Привилегии','managePrivileges'],['promo/index','Промокоды','manageShop'],['payment/index','Платежи','managePayments'],
                ['server/index','Серверы','manageServers'],['counter/index','Счётчики','manageSettings'],['setting/index','Настройки','manageSettings'],['audit/index','Аудит','viewAudit'],
            ] as [$r,$n,$perm]): if (Yii::$app->user->can($perm)): ?>
                <a href="<?= Url::to(['/'.$r]) ?>"><?= $n ?></a>
            <?php endif; endforeach; ?>
        </nav>
        <?= Html::beginForm(['/site/logout'],'post') ?><?= Html::submitButton('Выйти',['class'=>'btn btn-outline-light w-100']) ?><?= Html::endForm() ?>
    </aside>
    <section class="admin-main">
        <header><h1><?= Html::encode($this->title ?: 'HiveTech Admin') ?></h1><span><?= Html::encode(Yii::$app->user->identity->username) ?></span></header>
        <?php foreach (['success','error','warning'] as $type) if (Yii::$app->session->hasFlash($type)): ?><div class="alert alert-<?= $type==='error'?'danger':$type ?>"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div><?php endif; ?>
        <?= $content ?>
    </section>
</div>
<?php endif; ?>
<?php $this->endBody() ?></body></html><?php $this->endPage() ?>

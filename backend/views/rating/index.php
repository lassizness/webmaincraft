<?php

use yii\helpers\{Html, Url};

$this->title = 'Рейтинги';
?>

<div class="admin-section-head">
    <div>
        <div class="admin-panel-subtitle">
            Внутренний рейтинг доступен администрации всегда.
            Публичная страница управляется отдельным feature flag.
        </div>
    </div>

    <div class="admin-actions">
        <span class="badge <?= $publicEnabled ? 'bg-success' : 'bg-secondary' ?>">
            Публичный рейтинг: <?= $publicEnabled ? 'включён' : 'выключен' ?>
        </span>
        <a class="btn btn-outline-primary" href="<?= Url::to(['/setting/index']) ?>">
            Настройки
        </a>
    </div>
</div>

<section class="admin-panel">
    <?php if(!$rows): ?>
        <div class="admin-muted">Рейтинг пока пуст.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Игрок</th>
                    <th class="text-end">Score</th>
                    <th class="text-end">Достижений</th>
                    <th>Последнее</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($rows as $row): ?>
                    <tr>
                        <td>#<?= (int)$row['rank'] ?></td>
                        <td>
                            <?= Html::a(
                                Html::encode((string)$row['username']),
                                ['/user/view', 'id' => $row['user_id']],
                                ['class' => 'fw-bold text-decoration-none']
                            ) ?>
                        </td>
                        <td class="text-end">
                            <?= number_format((int)$row['score'], 0, ',', ' ') ?>
                        </td>
                        <td class="text-end">
                            <?= (int)$row['achievements'] ?>
                        </td>
                        <td><?= Html::encode((string)$row['last_unlock']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

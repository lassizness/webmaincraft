<?php

use yii\helpers\Html;

$this->title = 'Роли и права';
?>

<div class="admin-section-head">
    <div>
        <div class="admin-panel-subtitle">
            Роли сотрудников поверх Yii RBAC. Уровень используется для
            защиты от управления сотрудниками равного или более высокого уровня.
        </div>
    </div>
    <div class="admin-actions">
        <?= Html::a(
            'Создать роль',
            ['edit'],
            ['class' => 'btn btn-primary']
        ) ?>
    </div>
</div>

<div class="row g-3">
    <?php foreach($rows as $row): ?>
        <div class="col-xl-6">
            <section class="admin-panel h-100">
                <div class="d-flex justify-content-between gap-3 align-items-start">
                    <div>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <h2 class="admin-panel-title mb-0">
                                <?= Html::encode($row->label) ?>
                            </h2>
                            <?php if((int)$row->is_system === 1): ?>
                                <span class="badge bg-secondary">system</span>
                            <?php endif; ?>
                        </div>
                        <div class="admin-muted small mt-1 font-monospace">
                            <?= Html::encode($row->role_name) ?>
                        </div>
                    </div>

                    <span class="badge bg-info">
                        level <?= (int)$row->level ?>
                    </span>
                </div>

                <div class="mt-3 small">
                    Пользователей:
                    <strong><?= (int)($assignments[$row->role_name] ?? 0) ?></strong>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <?php foreach(($permissions[$row->role_name] ?? []) as $permission): ?>
                        <span class="badge text-bg-secondary">
                            <?= Html::encode($permission) ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="admin-actions mt-4">
                    <?php if($row->role_name !== 'admin'): ?>
                        <?= Html::a(
                            'Настроить',
                            ['edit', 'name' => $row->role_name],
                            ['class' => 'btn btn-sm btn-outline-primary']
                        ) ?>
                    <?php endif; ?>

                    <?php if((int)$row->is_system !== 1): ?>
                        <?= Html::beginForm(
                            ['delete', 'name' => $row->role_name],
                            'post',
                            ['class' => 'd-inline']
                        ) ?>
                        <?= Html::submitButton(
                            'Удалить',
                            [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data-confirm' => 'Удалить роль? Пользователей на ней быть не должно.',
                            ]
                        ) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    <?php endforeach; ?>
</div>

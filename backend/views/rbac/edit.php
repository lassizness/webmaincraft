<?php

use yii\helpers\Html;

$this->title = $isNew
    ? 'Новая роль'
    : 'Роль ' . $meta->label;

$code = $role?->name ?? '';
$label = $meta?->label ?? '';
$level = $meta?->level ?? 10;
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К ролям',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="admin-panel-subtitle mt-2">
            adminPanel и базовая роль user добавляются автоматически.
        </div>
    </div>
</div>

<?= Html::beginForm() ?>

<section class="admin-panel">
    <div class="row g-3">
        <div class="col-lg-5">
            <label class="form-label">Код роли</label>
            <input
                class="form-control font-monospace"
                name="code"
                value="<?= Html::encode($code) ?>"
                <?= $isNew ? '' : 'readonly' ?>
                placeholder="game_master"
                maxlength="64"
            >
        </div>

        <div class="col-lg-5">
            <label class="form-label">Название</label>
            <input
                class="form-control"
                name="label"
                value="<?= Html::encode($label) ?>"
                required
                maxlength="128"
            >
        </div>

        <div class="col-lg-2">
            <label class="form-label">Уровень</label>
            <input
                class="form-control"
                name="level"
                type="number"
                min="1"
                max="<?= (int)$maxLevel ?>"
                value="<?= (int)$level ?>"
                required
            >
        </div>
    </div>
</section>

<?php foreach($permissionGroups as $group => $permissions): ?>
    <section class="admin-panel mt-3">
        <h2 class="admin-panel-title mb-3">
            <?= Html::encode($group) ?>
        </h2>

        <div class="admin-setting-grid">
            <?php foreach($permissions as $name => $permissionLabel): ?>
                <label class="admin-toggle-card">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="permissions[]"
                            value="<?= Html::encode($name) ?>"
                            <?= in_array(
                                $name,
                                $selectedPermissions,
                                true
                            ) ? 'checked' : '' ?>
                        >
                        <span class="form-check-label ms-2">
                            <?= Html::encode($permissionLabel) ?>
                        </span>
                    </div>
                    <div class="admin-toggle-description">
                        <code><?= Html::encode($name) ?></code>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<div class="admin-save-bar">
    <div class="admin-save-copy">
        Роль не сможет управлять сотрудниками равного или более высокого уровня.
    </div>
    <?= Html::submitButton(
        'Сохранить роль',
        ['class' => 'btn btn-primary']
    ) ?>
</div>

<?= Html::endForm() ?>

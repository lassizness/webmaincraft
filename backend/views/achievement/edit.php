<?php

use common\models\Achievement;
use common\services\AchievementIconService;
use yii\helpers\{Html, Url};
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord
    ? 'Новое достижение'
    : 'Достижение: ' . $model->title;

$seasonOptions = ['' => 'Постоянное'];
foreach($seasons as $season)
{
    $seasonOptions[(int)$season->id] =
        $season->title . ' [' . $season->status . ']';
}

$conditionRows = [];
foreach($conditions as $condition)
{
    $conditionRows[] = [
        'group_no' => (int)$condition->group_no,
        'stat_key' => (string)$condition->stat_key,
        'operator' => (string)$condition->operator,
        'target_value' => (int)$condition->target_value,
    ];
}

if(!$conditionRows)
{
    $conditionRows[] = [
        'group_no' => 1,
        'stat_key' => '',
        'operator' => '>=',
        'target_value' => 1,
    ];
}

$rewardRows = [];
foreach($rewards as $reward)
{
    $rewardRows[] = [
        'reward_type' => (string)$reward->reward_type,
        'payload_json' => (string)$reward->payload_json,
    ];
}
?>

<div class="admin-section-head">
    <div>
        <a class="small text-decoration-none" href="<?= Url::to(['index']) ?>">
            ← К каталогу
        </a>
        <div class="admin-panel-subtitle mt-2">
            Статистика хранится в stat_key. Группы с одинаковым номером предназначены
            для объединения условий в будущем evaluator.
        </div>
    </div>
</div>

<?php $form = ActiveForm::begin(); ?>

<div class="row g-3">
    <div class="col-xl-7">
        <section class="admin-panel h-100">
            <h2 class="admin-panel-title mb-3">Основное</h2>

            <div class="row g-3">
                <div class="col-md-7">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => 191]) ?>
                </div>
                <div class="col-md-5">
                    <?= $form->field($model, 'code')->textInput([
                        'maxlength' => 96,
                        'placeholder' => 'ae2_first_controller',
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'hint')->textarea([
                        'rows' => 2,
                        'placeholder' => 'Подсказка для скрытого достижения',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'category')->textInput([
                        'placeholder' => 'general / ic2 / ae2',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'rarity')->dropDownList(
                        array_combine(
                            Achievement::RARITIES,
                            Achievement::RARITIES
                        )
                    ) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'score')->input('number', [
                        'min' => 0,
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'season_id')->dropDownList($seasonOptions) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'sort')->input('number') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'icon')->dropDownList(
                        AchievementIconService::options(),
                        [
                            'prompt' => 'Автоматически по коду',
                        ]
                    ) ?>
                    <div class="form-text">
                        Векторный тематический значок. Если не выбран,
                        значок определяется автоматически по code.
                    </div>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'hidden')->checkbox() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'enabled')->checkbox() ?>
                </div>
            </div>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="admin-panel h-100">
            <h2 class="admin-panel-title mb-2">Логика</h2>
            <div class="admin-panel-subtitle mb-3">
                Примеры stat_key:
                <code>playtime_seconds</code>,
                <code>block.break.minecraft:diamond_ore</code>,
                <code>item.craft.appliedenergistics2:controller</code>.
            </div>

            <div id="achievement-conditions" class="d-grid gap-2">
                <?php foreach($conditionRows as $index => $row): ?>
                    <div class="border rounded-3 p-3 condition-row">
                        <div class="row g-2">
                            <div class="col-2">
                                <label class="form-label">Группа</label>
                                <input
                                    class="form-control"
                                    type="number"
                                    min="1"
                                    name="conditions[<?= $index ?>][group_no]"
                                    value="<?= (int)$row['group_no'] ?>"
                                >
                            </div>
                            <div class="col-10">
                                <label class="form-label">stat_key</label>
                                <input
                                    class="form-control font-monospace"
                                    name="conditions[<?= $index ?>][stat_key]"
                                    value="<?= Html::encode($row['stat_key']) ?>"
                                >
                            </div>
                            <div class="col-4">
                                <label class="form-label">Оператор</label>
                                <select
                                    class="form-select"
                                    name="conditions[<?= $index ?>][operator]"
                                >
                                    <?php foreach(['>=','<=','=','>','<'] as $operator): ?>
                                        <option
                                            value="<?= Html::encode($operator) ?>"
                                            <?= $row['operator'] === $operator ? 'selected' : '' ?>
                                        >
                                            <?= Html::encode($operator) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-8">
                                <label class="form-label">Значение</label>
                                <input
                                    class="form-control"
                                    type="number"
                                    name="conditions[<?= $index ?>][target_value]"
                                    value="<?= (int)$row['target_value'] ?>"
                                >
                            </div>
                        </div>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger mt-2"
                            data-remove-condition
                        >
                            Удалить
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button
                type="button"
                class="btn btn-sm btn-outline-primary mt-3"
                id="add-condition"
            >
                + Условие
            </button>
        </section>
    </div>
</div>

<section class="admin-panel mt-3">
    <div class="admin-section-head">
        <div>
            <h2 class="admin-panel-title">Награды</h2>
            <div class="admin-panel-subtitle">
                Награды выполняются отдельным worker после unlock, поэтому ошибка
                RCON или привилегии не откатывает принятое игровое событие.
                Payload задаётся JSON: wallet {"amount":500},
                rcon {"server_id":1,"command":"give {username} ..."},
                permission {"name":"permission.name"},
                privilege {"server_id":1,"group_id":1,"days":30}.
            </div>
        </div>
    </div>

    <div id="achievement-rewards" class="d-grid gap-2">
        <?php foreach($rewardRows as $index => $row): ?>
            <div class="border rounded-3 p-3 reward-row">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select class="form-select" name="rewards[<?= $index ?>][reward_type]">
                            <?php foreach(['wallet','rcon','permission','privilege'] as $type): ?>
                                <option
                                    value="<?= $type ?>"
                                    <?= $row['reward_type'] === $type ? 'selected' : '' ?>
                                >
                                    <?= $type ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <input
                            class="form-control font-monospace"
                            name="rewards[<?= $index ?>][payload_json]"
                            value="<?= Html::encode($row['payload_json']) ?>"
                            placeholder='{"amount":500}'
                        >
                    </div>
                </div>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger mt-2"
                    data-remove-reward
                >
                    Удалить
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <button
        type="button"
        class="btn btn-sm btn-outline-primary mt-3"
        id="add-reward"
    >
        + Награда
    </button>
</section>

<div class="admin-save-bar">
    <div class="admin-save-copy">
        Изменения каталога начинают действовать после подключения evaluator.
    </div>
    <button class="btn btn-primary" type="submit">
        Сохранить достижение
    </button>
</div>

<?php ActiveForm::end(); ?>

<?php
$conditionIndex = count($conditionRows);
$rewardIndex = count($rewardRows);

$this->registerJs(<<<JS
(function () {
    let conditionIndex = {$conditionIndex};
    let rewardIndex = {$rewardIndex};

    const conditions = document.getElementById('achievement-conditions');
    const rewards = document.getElementById('achievement-rewards');

    document.getElementById('add-condition').addEventListener('click', function () {
        const i = conditionIndex++;
        const row = document.createElement('div');
        row.className = 'border rounded-3 p-3 condition-row';
        row.innerHTML =
            '<div class="row g-2">' +
                '<div class="col-2"><label class="form-label">Группа</label><input class="form-control" type="number" min="1" name="conditions[' + i + '][group_no]" value="1"></div>' +
                '<div class="col-10"><label class="form-label">stat_key</label><input class="form-control font-monospace" name="conditions[' + i + '][stat_key]"></div>' +
                '<div class="col-4"><label class="form-label">Оператор</label><select class="form-select" name="conditions[' + i + '][operator]"><option>&gt;=</option><option>&lt;=</option><option>=</option><option>&gt;</option><option>&lt;</option></select></div>' +
                '<div class="col-8"><label class="form-label">Значение</label><input class="form-control" type="number" name="conditions[' + i + '][target_value]" value="1"></div>' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger mt-2" data-remove-condition>Удалить</button>';
        conditions.appendChild(row);
    });

    document.getElementById('add-reward').addEventListener('click', function () {
        const i = rewardIndex++;
        const row = document.createElement('div');
        row.className = 'border rounded-3 p-3 reward-row';
        row.innerHTML =
            '<div class="row g-2">' +
                '<div class="col-md-3"><select class="form-select" name="rewards[' + i + '][reward_type]"><option>wallet</option><option>rcon</option><option>permission</option><option>privilege</option></select></div>' +
                '<div class="col-md-9"><input class="form-control font-monospace" name="rewards[' + i + '][payload_json]" placeholder=\'{"amount":500}\'></div>' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger mt-2" data-remove-reward>Удалить</button>';
        rewards.appendChild(row);
    });

    document.addEventListener('click', function (event) {
        if(event.target.matches('[data-remove-condition]'))
        {
            event.target.closest('.condition-row').remove();
        }

        if(event.target.matches('[data-remove-reward]'))
        {
            event.target.closest('.reward-row').remove();
        }
    });
})();
JS);
?>

<?php

use common\models\ModerationAction;
use yii\helpers\Html;

$this->title = 'Новое наказание';

$selectedUserId = $user?->id ?? 0;
$selectedUsername = $user?->username ?? '';

$types = [];
if(Yii::$app->user->can('createWarn'))
{
    $types[ModerationAction::TYPE_WARN] = 'Предупреждение';
}
if(Yii::$app->user->can('createMute'))
{
    $types[ModerationAction::TYPE_MUTE] = 'Мут';
}
if(Yii::$app->user->can('createBan'))
{
    $types[ModerationAction::TYPE_BAN] = 'Бан';
}
?>

<div class="admin-section-head">
    <div>
        <?= Html::a(
            '← К модерации',
            ['index'],
            ['class' => 'small text-decoration-none']
        ) ?>
        <div class="admin-panel-subtitle mt-2">
            Для mute/unmute RCON-команды должны быть настроены в карточке сервера.
            Ban/unban имеют Vanilla-совместимые шаблоны по умолчанию.
        </div>
    </div>
</div>

<?= Html::beginForm() ?>

<section class="admin-panel">
    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label">Игрок</label>

            <?php if($selectedUserId): ?>
                <input type="hidden" name="user_id" value="<?= (int)$selectedUserId ?>">
                <input
                    class="form-control"
                    value="<?= Html::encode($selectedUsername) ?>"
                    readonly
                >
            <?php else: ?>
                <input
                    class="form-control"
                    name="username"
                    required
                    placeholder="Minecraft-ник"
                    autocomplete="off"
                >
            <?php endif; ?>
        </div>

        <div class="col-lg-3">
            <label class="form-label">Тип</label>
            <select class="form-select" name="type" id="moderation-type" required>
                <?php foreach($types as $value => $label): ?>
                    <option
                        value="<?= Html::encode($value) ?>"
                        <?= $type === $value ? 'selected' : '' ?>
                    >
                        <?= Html::encode($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-lg-3">
            <label class="form-label">Область</label>
            <select class="form-select" name="scope" id="moderation-scope">
                <option value="server">Один сервер</option>
                <option value="global">Все серверы</option>
            </select>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6" id="moderation-server-wrap">
            <label class="form-label">Сервер</label>
            <select class="form-select" name="server_id">
                <option value="">Выберите сервер</option>
                <?php foreach($servers as $server): ?>
                    <option value="<?= (int)$server->id ?>">
                        <?= Html::encode($server->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-lg-3" id="moderation-duration-wrap">
            <label class="form-label">Срок</label>
            <select class="form-select" name="duration" id="moderation-duration">
                <option value="1h">1 час</option>
                <option value="6h">6 часов</option>
                <option value="1d" selected>1 день</option>
                <option value="3d">3 дня</option>
                <option value="7d">7 дней</option>
                <option value="30d">30 дней</option>
                <option value="permanent">Навсегда</option>
                <option value="custom">Своя дата</option>
            </select>
        </div>

        <div class="col-lg-3 d-none" id="moderation-custom-wrap">
            <label class="form-label">До</label>
            <input
                class="form-control"
                type="datetime-local"
                name="expires_at"
            >
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Причина</label>
        <textarea
            class="form-control"
            name="reason"
            rows="3"
            required
            maxlength="2000"
            placeholder="Причина, которая будет сохранена в истории"
        ></textarea>
    </div>

    <div class="mt-3">
        <label class="form-label">Внутренний комментарий</label>
        <textarea
            class="form-control"
            name="internal_comment"
            rows="3"
            maxlength="5000"
            placeholder="Не предназначен для игрока"
        ></textarea>
    </div>

    <div class="alert alert-warning mt-3 mb-0 d-none" id="moderation-global-warning">
        Глобальный ban/mute требует отдельного RBAC-права.
        Сервер проверит его повторно при сохранении.
    </div>
</section>

<div class="admin-save-bar">
    <div class="admin-save-copy">
        Наказание попадёт в историю и аудит до выполнения RCON-команды.
    </div>
    <?= Html::submitButton(
        'Применить наказание',
        [
            'class' => 'btn btn-primary',
            'data-confirm' => 'Применить наказание к игроку?',
        ]
    ) ?>
</div>

<?= Html::endForm() ?>

<?php
$this->registerJs(<<<'JS'
(function () {
    const type = document.getElementById('moderation-type');
    const scope = document.getElementById('moderation-scope');
    const serverWrap = document.getElementById('moderation-server-wrap');
    const durationWrap = document.getElementById('moderation-duration-wrap');
    const duration = document.getElementById('moderation-duration');
    const customWrap = document.getElementById('moderation-custom-wrap');
    const globalWarning = document.getElementById('moderation-global-warning');

    const refresh = function () {
        const isWarn = type && type.value === 'warn';
        const isGlobal = scope && scope.value === 'global';

        if(serverWrap)
        {
            serverWrap.classList.toggle('d-none', isGlobal);
        }

        if(durationWrap)
        {
            durationWrap.classList.toggle('d-none', isWarn);
        }

        if(customWrap)
        {
            customWrap.classList.toggle(
                'd-none',
                isWarn || !duration || duration.value !== 'custom'
            );
        }

        if(globalWarning)
        {
            globalWarning.classList.toggle('d-none', !isGlobal);
        }
    };

    type?.addEventListener('change', refresh);
    scope?.addEventListener('change', refresh);
    duration?.addEventListener('change', refresh);
    refresh();
})();
JS);
?>

<?php

use yii\helpers\Html;

$this->title = 'Настройки';

$secretConfigured = trim((string)($settings['recaptcha.secret_key'] ?? '')) !== '';
?>
<div class="card p-4">
    <?= Html::beginForm() ?>

    <h4 class="mb-3">Основные настройки</h4>

    <div class="mb-3">
        <label class="form-label">Название сайта</label>
        <input
            class="form-control"
            name="settings[site.title]"
            value="<?= Html::encode((string)$settings['site.title']) ?>"
        >
    </div>

    <div class="mb-4">
        <label class="form-label">Подзаголовок</label>
        <input
            class="form-control"
            name="settings[site.tagline]"
            value="<?= Html::encode((string)$settings['site.tagline']) ?>"
        >
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <?= Html::checkbox(
                'settings[maintenance.enabled]',
                (string)$settings['maintenance.enabled'] === '1',
                ['value' => '1', 'label' => 'Режим обслуживания']
            ) ?>
        </div>

        <div class="col-lg-4 mb-3">
            <?= Html::checkbox(
                'settings[registration.enabled]',
                (string)$settings['registration.enabled'] === '1',
                ['value' => '1', 'label' => 'Регистрация включена']
            ) ?>
        </div>

        <div class="col-lg-4 mb-3">
            <?= Html::checkbox(
                'settings[shop.enabled]',
                (string)$settings['shop.enabled'] === '1',
                ['value' => '1', 'label' => 'Магазин включён']
            ) ?>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-1">Google reCAPTCHA</h4>
            <div class="text-muted small">reCAPTCHA v3 · невидимая проверка по score</div>
        </div>

        <?php if($secretConfigured): ?>
            <span class="badge bg-success">Secret key сохранён</span>
        <?php else: ?>
            <span class="badge bg-secondary">Secret key не задан</span>
        <?php endif; ?>
    </div>

    <div class="alert alert-info">
        reCAPTCHA v3 работает без чекбокса и не прерывает пользователя.
        Google возвращает score от 0.0 до 1.0: чем выше значение, тем больше вероятность,
        что действие выполнил человек. Используйте ключи именно reCAPTCHA v3.
        Начните с режима наблюдения, посмотрите реальные score в
        <code>frontend/runtime/logs/recaptcha.log</code>, затем включите блокировку по выбранным порогам.
        При сетевой недоступности Google можно оставить fail-open, чтобы внешний сбой не отключал авторизацию.
    </div>

    <div class="mb-3">
        <?= Html::checkbox(
            'settings[recaptcha.enabled]',
            (string)$settings['recaptcha.enabled'] === '1',
            ['value' => '1', 'label' => 'Включить Google reCAPTCHA']
        ) ?>
    </div>

    <div class="row mb-3">
        <div class="col-lg-6">
            <?= Html::checkbox(
                'settings[recaptcha.login.enabled]',
                (string)$settings['recaptcha.login.enabled'] === '1',
                ['value' => '1', 'label' => 'Проверять при авторизации']
            ) ?>
        </div>

        <div class="col-lg-6">
            <?= Html::checkbox(
                'settings[recaptcha.register.enabled]',
                (string)$settings['recaptcha.register.enabled'] === '1',
                ['value' => '1', 'label' => 'Проверять при регистрации']
            ) ?>
        </div>
    </div>

    <div class="mb-3">
        <?= Html::checkbox(
            'settings[recaptcha.monitoring_only]',
            (string)$settings['recaptcha.monitoring_only'] === '1',
            [
                'value' => '1',
                'label' => 'Режим наблюдения: считать score и писать его в лог, но не блокировать пользователя',
            ]
        ) ?>
        <div class="form-text">
            Рекомендуется оставить включённым после первого запуска, чтобы подобрать пороги на реальном трафике.
        </div>
    </div>

    <div class="mb-3">
        <?= Html::checkbox(
            'settings[recaptcha.fail_open_on_service_error]',
            (string)$settings['recaptcha.fail_open_on_service_error'] === '1',
            [
                'value' => '1',
                'label' => 'Пропускать пользователя, если Google reCAPTCHA временно недоступна',
            ]
        ) ?>
        <div class="form-text">
            Рекомендуется держать включённым, чтобы сбой DNS или Google не отключал вход и регистрацию.
            Невалидный токен, неверный action и проваленная проверка при этом по-прежнему блокируются.
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-6">
            <label class="form-label">Минимальный score для авторизации</label>
            <input
                type="number"
                min="0"
                max="1"
                step="0.1"
                class="form-control"
                name="settings[recaptcha.login.threshold]"
                value="<?= Html::encode((string)$settings['recaptcha.login.threshold']) ?>"
            >
            <div class="form-text">Стартовое значение: 0.5.</div>
        </div>

        <div class="col-lg-6">
            <label class="form-label">Минимальный score для регистрации</label>
            <input
                type="number"
                min="0"
                max="1"
                step="0.1"
                class="form-control"
                name="settings[recaptcha.register.threshold]"
                value="<?= Html::encode((string)$settings['recaptcha.register.threshold']) ?>"
            >
            <div class="form-text">Стартовое значение: 0.5.</div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Site key</label>
        <input
            class="form-control"
            name="settings[recaptcha.site_key]"
            value="<?= Html::encode((string)$settings['recaptcha.site_key']) ?>"
            autocomplete="off"
        >
    </div>

    <div class="mb-2">
        <label class="form-label">Secret key</label>
        <input
            type="password"
            class="form-control"
            name="settings[recaptcha.secret_key]"
            value=""
            autocomplete="new-password"
            placeholder="<?= $secretConfigured
                ? 'Secret key уже сохранён. Оставьте пустым, чтобы не менять.'
                : 'Введите Secret key'
            ?>"
        >
        <div class="form-text">Значение намеренно не выводится обратно в HTML.</div>
    </div>

    <?php if($secretConfigured): ?>
        <div class="mb-4">
            <?= Html::checkbox(
                'recaptcha_secret_clear',
                false,
                ['value' => '1', 'label' => 'Удалить сохранённый Secret key']
            ) ?>
        </div>
    <?php endif; ?>

    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
    <?= Html::endForm() ?>
</div>

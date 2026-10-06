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
            <div class="text-muted small">reCAPTCHA v2 Checkbox</div>
        </div>

        <?php if($secretConfigured): ?>
            <span class="badge bg-success">Secret key сохранён</span>
        <?php else: ?>
            <span class="badge bg-secondary">Secret key не задан</span>
        <?php endif; ?>
    </div>

    <div class="alert alert-info">
        CAPTCHA проверяется только после включения и заполнения Site key + Secret key.
        Для локального стенда используйте тестовые ключи Google, перед production замените их реальными.
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

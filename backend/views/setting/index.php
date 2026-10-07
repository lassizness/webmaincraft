<?php

use yii\helpers\Html;

$this->title = 'Настройки';

$secretConfigured = trim(
    (string)($settings['recaptcha.secret_key'] ?? '')
) !== '';

$siteKeyConfigured = trim(
    (string)($settings['recaptcha.site_key'] ?? '')
) !== '';

$recaptchaReady = $secretConfigured && $siteKeyConfigured;

$payPalychTokenConfigured = trim((string)ht_env('PAYPALYCH_API_TOKEN', '')) !== '';
$payPalychSignatureConfigured = trim((string)ht_env('PAYPALYCH_SIGNATURE_TOKEN', '')) !== '';
$payPalychShopConfigured = trim((string)($settings['payments.paypalych.shop_id'] ?? '')) !== '';
$payPalychReady = $payPalychTokenConfigured && $payPalychShopConfigured;
?>

<?= Html::beginForm() ?>

<div class="row g-3">
    <div class="col-xl-5">
        <section class="admin-panel h-100">
            <div class="admin-section-head">
                <div>
                    <h2 class="admin-panel-title">
                        Проект
                    </h2>
                    <div class="admin-panel-subtitle">
                        Название и короткое описание публичного сайта.
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="site-title">
                    Название сайта
                </label>
                <input
                    id="site-title"
                    class="form-control"
                    name="settings[site.title]"
                    value="<?= Html::encode(
                        (string)$settings['site.title']
                    ) ?>"
                >
            </div>

            <div>
                <label class="form-label" for="site-tagline">
                    Подзаголовок
                </label>
                <input
                    id="site-tagline"
                    class="form-control"
                    name="settings[site.tagline]"
                    value="<?= Html::encode(
                        (string)$settings['site.tagline']
                    ) ?>"
                >
            </div>
        </section>
    </div>

    <div class="col-xl-7">
        <section class="admin-panel h-100">
            <div class="admin-section-head">
                <div>
                    <h2 class="admin-panel-title">
                        Доступность функций
                    </h2>
                    <div class="admin-panel-subtitle">
                        Операционные переключатели проекта.
                    </div>
                </div>
            </div>

            <div class="admin-setting-grid">
                <div class="admin-toggle-card">
                    <?= Html::checkbox(
                        'settings[maintenance.enabled]',
                        (string)$settings['maintenance.enabled'] === '1',
                        [
                            'value' => '1',
                            'class' => 'form-check-input me-2',
                            'label' => 'Режим обслуживания',
                        ]
                    ) ?>
                    <div class="admin-toggle-description">
                        Используй только для технических работ.
                    </div>
                </div>

                <div class="admin-toggle-card">
                    <?= Html::checkbox(
                        'settings[registration.enabled]',
                        (string)$settings['registration.enabled'] === '1',
                        [
                            'value' => '1',
                            'class' => 'form-check-input me-2',
                            'label' => 'Регистрация включена',
                        ]
                    ) ?>
                    <div class="admin-toggle-description">
                        Разрешает создание новых аккаунтов.
                    </div>
                </div>

                <div class="admin-toggle-card">
                    <?= Html::checkbox(
                        'settings[shop.enabled]',
                        (string)$settings['shop.enabled'] === '1',
                        [
                            'value' => '1',
                            'class' => 'form-check-input me-2',
                            'label' => 'Магазин включён',
                        ]
                    ) ?>
                    <div class="admin-toggle-description">
                        Управляет доступностью магазина для игроков.
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<section class="admin-panel mt-3">
    <div class="admin-section-head">
        <div>
            <h2 class="admin-panel-title">
                Google reCAPTCHA v3
            </h2>
            <div class="admin-panel-subtitle">
                Невидимая score-проверка входа и регистрации.
            </div>
        </div>

        <div class="admin-actions">
            <?php if($recaptchaReady): ?>
                <span class="badge bg-success">
                    Ключи настроены
                </span>
            <?php elseif($siteKeyConfigured || $secretConfigured): ?>
                <span class="badge bg-warning">
                    Настройка не завершена
                </span>
            <?php else: ?>
                <span class="badge bg-secondary">
                    Ключи не заданы
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="alert alert-info">
        reCAPTCHA v3 не показывает пользователю чекбокс.
        Сначала оставь режим наблюдения включённым и собери реальные score
        в <code>frontend/runtime/logs/recaptcha.log</code>.
        Fail-open защищает авторизацию от внешнего сбоя DNS или Google.
    </div>

    <div class="admin-setting-grid mb-4">
        <div class="admin-toggle-card">
            <?= Html::checkbox(
                'settings[recaptcha.enabled]',
                (string)$settings['recaptcha.enabled'] === '1',
                [
                    'value' => '1',
                    'class' => 'form-check-input me-2',
                    'label' => 'Включить reCAPTCHA',
                ]
            ) ?>
            <div class="admin-toggle-description">
                Глобальный переключатель всей защиты.
            </div>
        </div>

        <div class="admin-toggle-card">
            <?= Html::checkbox(
                'settings[recaptcha.monitoring_only]',
                (string)$settings['recaptcha.monitoring_only'] === '1',
                [
                    'value' => '1',
                    'class' => 'form-check-input me-2',
                    'label' => 'Режим наблюдения',
                ]
            ) ?>
            <div class="admin-toggle-description">
                Логировать score, но не блокировать из-за низкой оценки.
            </div>
        </div>

        <div class="admin-toggle-card">
            <?= Html::checkbox(
                'settings[recaptcha.login.enabled]',
                (string)$settings['recaptcha.login.enabled'] === '1',
                [
                    'value' => '1',
                    'class' => 'form-check-input me-2',
                    'label' => 'Защищать авторизацию',
                ]
            ) ?>
            <div class="admin-toggle-description">
                Проверять вход по логину и паролю.
            </div>
        </div>

        <div class="admin-toggle-card">
            <?= Html::checkbox(
                'settings[recaptcha.register.enabled]',
                (string)$settings['recaptcha.register.enabled'] === '1',
                [
                    'value' => '1',
                    'class' => 'form-check-input me-2',
                    'label' => 'Защищать регистрацию',
                ]
            ) ?>
            <div class="admin-toggle-description">
                Проверять создание новых аккаунтов.
            </div>
        </div>

        <div class="admin-toggle-card">
            <?= Html::checkbox(
                'settings[recaptcha.fail_open_on_service_error]',
                (string)$settings['recaptcha.fail_open_on_service_error'] === '1',
                [
                    'value' => '1',
                    'class' => 'form-check-input me-2',
                    'label' => 'Fail-open при сбое сервиса',
                ]
            ) ?>
            <div class="admin-toggle-description">
                Не блокировать игрока, если Google или DNS временно недоступны.
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <label class="form-label" for="recaptcha-login-threshold">
                Минимальный score для авторизации
            </label>
            <input
                id="recaptcha-login-threshold"
                type="number"
                min="0"
                max="1"
                step="0.1"
                class="form-control"
                name="settings[recaptcha.login.threshold]"
                value="<?= Html::encode(
                    (string)$settings['recaptcha.login.threshold']
                ) ?>"
            >
            <div class="form-text">
                Базовое значение: 0.5. Меняй только после анализа логов.
            </div>
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="recaptcha-register-threshold">
                Минимальный score для регистрации
            </label>
            <input
                id="recaptcha-register-threshold"
                type="number"
                min="0"
                max="1"
                step="0.1"
                class="form-control"
                name="settings[recaptcha.register.threshold]"
                value="<?= Html::encode(
                    (string)$settings['recaptcha.register.threshold']
                ) ?>"
            >
            <div class="form-text">
                Регистрацию можно сделать немного строже после накопления статистики.
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label" for="recaptcha-site-key">
                Site key
            </label>
            <input
                id="recaptcha-site-key"
                class="form-control font-monospace"
                name="settings[recaptcha.site_key]"
                value="<?= Html::encode(
                    (string)$settings['recaptcha.site_key']
                ) ?>"
                autocomplete="off"
            >
            <div class="form-text">
                Публичный ключ reCAPTCHA v3 для hivegrid.online.
            </div>
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="recaptcha-secret-key">
                Secret key
            </label>
            <input
                id="recaptcha-secret-key"
                type="password"
                class="form-control font-monospace"
                name="settings[recaptcha.secret_key]"
                value=""
                autocomplete="new-password"
                placeholder="<?= $secretConfigured
                    ? 'Сохранён. Оставь пустым, чтобы не менять.'
                    : 'Введите Secret key'
                ?>"
            >
            <div class="form-text">
                Секрет намеренно никогда не выводится обратно в HTML.
            </div>
        </div>
    </div>

    <?php if($secretConfigured): ?>
        <div class="admin-danger-zone admin-panel mt-3">
            <h3 class="admin-panel-title mb-1">
                Удаление Secret key
            </h3>
            <div class="admin-panel-subtitle mb-3">
                После удаления включённая reCAPTCHA перестанет быть настроенной.
            </div>

            <?= Html::checkbox(
                'recaptcha_secret_clear',
                false,
                [
                    'value' => '1',
                    'label' => 'Удалить сохранённый Secret key при сохранении',
                ]
            ) ?>
        </div>
    <?php endif; ?>
</section>


<section class="admin-panel mt-3">
    <div class="admin-section-head">
        <div>
            <h2 class="admin-panel-title">PayPalych / Pally</h2>
            <div class="admin-panel-subtitle">
                Каркас интернет-эквайринга. Боевой поток пополнения пока не включён.
            </div>
        </div>
        <div class="admin-actions">
            <?php if($payPalychReady): ?>
                <span class="badge bg-success">API готов к подключению</span>
            <?php elseif($payPalychTokenConfigured || $payPalychShopConfigured): ?>
                <span class="badge bg-warning">Настройка не завершена</span>
            <?php else: ?>
                <span class="badge bg-secondary">Не настроен</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="alert alert-info">
        API token и token подписи не сохраняются в базе и не выводятся в HTML.
        Они задаются в <code>/etc/hivetech/portal.env</code> как
        <code>PAYPALYCH_API_TOKEN</code> и <code>PAYPALYCH_SIGNATURE_TOKEN</code>.
        Result URL/webhook будет подключён отдельным шагом при включении реальных платежей.
    </div>

    <div class="admin-setting-grid mb-4">
        <div class="admin-toggle-card">
            <?= Html::checkbox(
                'settings[payments.paypalych.enabled]',
                (string)$settings['payments.paypalych.enabled'] === '1',
                [
                    'value' => '1',
                    'class' => 'form-check-input me-2',
                    'label' => 'Включить провайдер',
                ]
            ) ?>
            <div class="admin-toggle-description">
                Разрешает backend использовать PayPalych adapter. Само пополнение пока остаётся выключенным.
            </div>
        </div>

        <div class="admin-toggle-card">
            <div class="form-check-label fw-bold">Секреты окружения</div>
            <div class="admin-toggle-description ms-0 mt-2">
                API token: <strong><?= $payPalychTokenConfigured ? 'настроен' : 'не задан' ?></strong><br>
                Signature token: <strong><?= $payPalychSignatureConfigured ? 'отдельный' : 'используется API token' ?></strong>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label" for="paypalych-shop-id">Shop ID</label>
            <input
                id="paypalych-shop-id"
                class="form-control font-monospace"
                name="settings[payments.paypalych.shop_id]"
                value="<?= Html::encode((string)$settings['payments.paypalych.shop_id']) ?>"
                autocomplete="off"
            >
        </div>

        <div class="col-lg-6">
            <label class="form-label" for="paypalych-base-url">API base URL</label>
            <input
                id="paypalych-base-url"
                class="form-control font-monospace"
                name="settings[payments.paypalych.base_url]"
                value="<?= Html::encode((string)$settings['payments.paypalych.base_url']) ?>"
                autocomplete="off"
            >
            <div class="form-text">
                По умолчанию: https://pally.info/api/v1
            </div>
        </div>
    </div>
</section>

<div class="admin-save-bar">
    <div class="admin-save-copy">
        Изменения применяются после сохранения.
    </div>
    <?= Html::submitButton(
        'Сохранить настройки',
        ['class' => 'btn btn-primary']
    ) ?>
</div>

<?= Html::endForm() ?>

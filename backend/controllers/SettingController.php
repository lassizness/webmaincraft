<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\AdminController;
use common\models\Setting;
use Yii;

final class SettingController extends AdminController
{
    protected string $permission = 'manageSettings';

    private const BOOLEAN_KEYS = [
        'maintenance.enabled',
        'registration.enabled',
        'shop.enabled',
        'recaptcha.enabled',
        'recaptcha.login.enabled',
        'recaptcha.register.enabled',
        'recaptcha.monitoring_only',
        'recaptcha.fail_open_on_service_error',
        'payments.paypalych.enabled',
    ];

    private const TEXT_KEYS = [
        'site.title',
        'site.tagline',
        'recaptcha.site_key',
        'payments.paypalych.shop_id',
        'payments.paypalych.base_url',
    ];

    private const SCORE_KEYS = [
        'recaptcha.login.threshold',
        'recaptcha.register.threshold',
    ];

    private const ALL_KEYS = [
        'site.title',
        'site.tagline',
        'maintenance.enabled',
        'registration.enabled',
        'shop.enabled',
        'recaptcha.enabled',
        'recaptcha.login.enabled',
        'recaptcha.register.enabled',
        'recaptcha.monitoring_only',
        'recaptcha.fail_open_on_service_error',
        'recaptcha.login.threshold',
        'recaptcha.register.threshold',
        'recaptcha.site_key',
        'recaptcha.secret_key',
        'payments.paypalych.enabled',
        'payments.paypalych.shop_id',
        'payments.paypalych.base_url',
    ];

    public function actionIndex()
    {
        if(Yii::$app->request->isPost)
        {
            $settings = (array)Yii::$app->request->post('settings', []);

            foreach(self::BOOLEAN_KEYS as $key)
            {
                $this->saveSetting(
                    $key,
                    isset($settings[$key]) && (string)$settings[$key] === '1'
                        ? '1'
                        : '0'
                );
            }

            foreach(self::TEXT_KEYS as $key)
            {
                $this->saveSetting(
                    $key,
                    trim((string)($settings[$key] ?? ''))
                );
            }

            foreach(self::SCORE_KEYS as $key)
            {
                $raw = str_replace(
                    ',',
                    '.',
                    trim((string)($settings[$key] ?? '0.5'))
                );

                $score = is_numeric($raw) ? (float)$raw : 0.5;
                $score = max(0.0, min(1.0, $score));

                $this->saveSetting(
                    $key,
                    rtrim(rtrim(number_format($score, 2, '.', ''), '0'), '.')
                );
            }

            $newSecret = trim(
                (string)($settings['recaptcha.secret_key'] ?? '')
            );

            if(Yii::$app->request->post('recaptcha_secret_clear') === '1')
            {
                $this->saveSetting('recaptcha.secret_key', '');
            }
            elseif($newSecret !== '')
            {
                $this->saveSetting('recaptcha.secret_key', $newSecret);
            }

            Yii::$app->session->setFlash('success', 'Настройки сохранены.');

            return $this->refresh();
        }

        $settings = [];

        foreach(self::ALL_KEYS as $key)
        {
            $default = match($key)
            {
                'registration.enabled',
                'recaptcha.login.enabled',
                'recaptcha.register.enabled',
                'recaptcha.monitoring_only',
                'recaptcha.fail_open_on_service_error' => '1',
                'recaptcha.login.threshold',
                'recaptcha.register.threshold' => '0.5',
                'payments.paypalych.base_url' => 'https://pally.info/api/v1',
                default => '',
            };

            $settings[$key] = Setting::value($key, $default);
        }

        return $this->render('index', ['settings' => $settings]);
    }

    private function saveSetting(string $key, string $value): void
    {
        $model = Setting::findOne(['key' => $key]);

        if(!$model)
        {
            $model = new Setting(['key' => $key]);
        }

        $model->value = $value;
        $model->save(false);
    }
}

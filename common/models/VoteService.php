<?php

declare(strict_types=1);

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

final class VoteService extends ActiveRecord
{
    public const DRIVER_TOPG = 'topg';
    public const DRIVER_TOPCRAFT = 'topcraft';

    public static function tableName(): string
    {
        return '{{%vote_services}}';
    }

    public static function driverOptions(): array
    {
        return [
            self::DRIVER_TOPG => 'TopG Postback',
            self::DRIVER_TOPCRAFT => 'TopCraft Reward Script',
        ];
    }

    public function rules(): array
    {
        return [
            [['code', 'name', 'driver', 'vote_url_template', 'reward_amount'], 'required'],
            [['reward_amount'], 'number', 'min' => 0.01],
            [['reward_cooldown_seconds'], 'integer', 'min' => 60],
            [['enabled', 'show_on_home', 'sort'], 'integer'],
            [['config'], 'string'],
            [['code', 'driver'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 128],
            [['vote_url_template', 'banner_url'], 'string', 'max' => 512],
            [['callback_secret'], 'string', 'max' => 255],
            [['code'], 'match', 'pattern' => '/^[a-z0-9_-]+$/', 'message' => 'Только a-z, 0-9, _ и -.'],
            [['driver'], 'in', 'range' => array_keys(self::driverOptions())],
            [['code'], 'unique'],
            [['vote_url_template'], 'validateVoteUrlTemplate'],
            [['callback_secret'], 'validateCallbackSecret'],
            [['banner_url'], 'url', 'defaultScheme' => 'https', 'skipOnEmpty' => true],
            [['config'], 'validateConfig'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code' => 'Код сервиса',
            'name' => 'Название',
            'driver' => 'Драйвер',
            'vote_url_template' => 'Ссылка голосования',
            'reward_amount' => 'Награда, кварков',
            'reward_cooldown_seconds' => 'Повторная награда через, сек.',
            'callback_secret' => 'Секретный ключ callback',
            'banner_url' => 'URL баннера рейтинга',
            'show_on_home' => 'Показывать на главной',
            'enabled' => 'Включён',
            'sort' => 'Сортировка',
            'config' => 'Конфигурация JSON',
        ];
    }

    public function getVotes(): ActiveQuery
    {
        return $this->hasMany(Vote::class, ['service_id' => 'id']);
    }

    public function configArray(): array
    {
        if ($this->config === null || trim((string)$this->config) === '') {
            return [];
        }

        $decoded = json_decode((string)$this->config, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function validateVoteUrlTemplate(string $attribute): void
    {
        $value = trim((string)$this->{$attribute});
        if ($value === '') {
            return;
        }

        if (filter_var(str_replace('{username}', 'Steve', $value), FILTER_VALIDATE_URL) === false) {
            $this->addError($attribute, 'Укажи корректный URL голосования.');
            return;
        }

        if ($this->driver === self::DRIVER_TOPG && !str_contains($value, '{username}')) {
            $this->addError($attribute, 'Для TopG ссылка должна содержать {username}.');
        }
    }

    public function validateCallbackSecret(string $attribute): void
    {
        if (
            $this->driver === self::DRIVER_TOPCRAFT
            && (int)$this->enabled === 1
            && trim((string)$this->{$attribute}) === ''
        ) {
            $this->addError($attribute, 'Для включённого TopCraft укажи секретный ключ из раздела «Скрипт поощрений».');
        }
    }

    public function validateConfig(string $attribute): void
    {
        $value = trim((string)$this->{$attribute});
        if ($value === '') {
            return;
        }

        json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError($attribute, 'Некорректный JSON: ' . json_last_error_msg());
        }
    }
}

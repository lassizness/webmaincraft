<?php

declare(strict_types=1);

namespace common\services;

final class RbacCatalog
{
    public static function groups(): array
    {
        return [
            'Пользователи' => [
                'viewUsers' => 'Просмотр пользователей',
                'manageUserStatus' => 'Изменение статуса аккаунта',
                'manageUserRoles' => 'Назначение ролей',
                'manageLauncherPermissions' => 'Launcher permissions',
                'manageHwid' => 'Управление HWID',
            ],
            'Модерация' => [
                'viewModeration' => 'Просмотр наказаний',
                'createWarn' => 'Предупреждения',
                'createMute' => 'Мут',
                'createGlobalMute' => 'Глобальный мут',
                'createBan' => 'Бан отдельного сервера',
                'createGlobalBan' => 'Глобальный бан',
                'revokePunishment' => 'Снятие наказаний',
            ],
            'Поддержка и контент' => [
                'manageSupport' => 'Поддержка',
                'manageContent' => 'Контент',
            ],
            'Монетизация' => [
                'manageShop' => 'Магазин и промокоды',
                'managePayments' => 'Платежи',
                'managePrivileges' => 'Привилегии',
            ],
            'Инфраструктура' => [
                'manageServers' => 'Серверы',
                'manageDelivery' => 'Очередь выдачи',
                'manageSettings' => 'Настройки проекта',
                'viewAudit' => 'Аудит',
                'manageRbac' => 'Роли и права',
            ],
        ];
    }

    public static function labels(): array
    {
        $result = [];

        foreach(self::groups() as $permissions)
        {
            foreach($permissions as $name => $label)
            {
                $result[$name] = $label;
            }
        }

        return $result;
    }

    public static function names(): array
    {
        return array_keys(self::labels());
    }
}

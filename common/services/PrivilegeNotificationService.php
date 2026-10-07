<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    PrivilegeNotification,
    UserPrivilege
};
use Yii;

final class PrivilegeNotificationService
{
    public function sendDue(int $limit = 200): int
    {
        $sent = 0;
        $now = time();

        $active = UserPrivilege::find()
            ->with(['user', 'group', 'server'])
            ->where(['status' => UserPrivilege::STATUS_ACTIVE])
            ->andWhere(['<=', 'active_to', date('Y-m-d H:i:s', $now + 7 * 86400)])
            ->andWhere(['>', 'active_to', date('Y-m-d H:i:s', $now)])
            ->orderBy(['active_to' => SORT_ASC, 'id' => SORT_ASC])
            ->limit($limit)
            ->all();

        foreach($active as $row)
        {
            $remaining = strtotime((string)$row->active_to) - $now;

            $type = match(true) {
                $remaining <= 86400 => PrivilegeNotification::TYPE_1D,
                $remaining <= 3 * 86400 => PrivilegeNotification::TYPE_3D,
                default => PrivilegeNotification::TYPE_7D,
            };

            if($this->sendOnce($row, $type, false))
            {
                $sent++;
            }
        }

        if($sent < $limit)
        {
            $expired = UserPrivilege::find()
                ->with(['user', 'group', 'server'])
                ->where(['status' => UserPrivilege::STATUS_EXPIRED])
                ->andWhere(['>=', 'active_to', date('Y-m-d H:i:s', $now - 30 * 86400)])
                ->orderBy(['active_to' => SORT_DESC, 'id' => SORT_DESC])
                ->limit($limit - $sent)
                ->all();

            foreach($expired as $row)
            {
                if($this->sendOnce(
                    $row,
                    PrivilegeNotification::TYPE_EXPIRED,
                    true
                ))
                {
                    $sent++;
                }
            }
        }

        return $sent;
    }

    private function sendOnce(
        UserPrivilege $privilege,
        string $type,
        bool $expired
    ): bool
    {
        if(PrivilegeNotification::find()->where([
            'user_privilege_id' => $privilege->id,
            'notice_type' => $type,
            'active_to' => (string)$privilege->active_to,
        ])->exists())
        {
            return false;
        }

        $user = $privilege->user;

        if(!$user || trim((string)$user->email) === '')
        {
            return false;
        }

        $previousLanguage = Yii::$app->language;
        Yii::$app->language = in_array((string)$user->language, ['ru', 'en', 'de'], true)
            ? (string)$user->language
            : 'ru';

        try
        {
            $view = $expired
                ? 'privilege-expired'
                : 'privilege-expiring';

            $subject = $expired
                ? Yii::t('ui', 'Игровой статус HiveTech закончился')
                : Yii::t('ui', 'Игровой статус HiveTech скоро закончится');

            $ok = Yii::$app->mailer
                ->compose($view, [
                    'user' => $user,
                    'privilege' => $privilege,
                    'noticeType' => $type,
                ])
                ->setTo($user->email)
                ->setSubject($subject)
                ->send();

            if(!$ok)
            {
                return false;
            }

            $log = new PrivilegeNotification([
                'user_privilege_id' => $privilege->id,
                'notice_type' => $type,
                'active_to' => (string)$privilege->active_to,
                'sent_at' => date('Y-m-d H:i:s'),
            ]);

            return $log->save(false);
        }
        catch(\Throwable $e)
        {
            Yii::error([
                'message' => 'Privilege notification failed',
                'privilegeId' => (int)$privilege->id,
                'noticeType' => $type,
                'error' => $e->getMessage(),
            ], 'mail');

            return false;
        }
        finally
        {
            Yii::$app->language = $previousLanguage;
        }
    }
}

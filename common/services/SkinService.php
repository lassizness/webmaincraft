<?php

declare(strict_types=1);

namespace common\services;

use common\models\{
    Server,
    User
};
use Yii;
use yii\web\UploadedFile;

final class SkinService
{
    public function save(
        User $user,
        UploadedFile $file,
        string $type
    ): void
    {
        if(!in_array($type, ['skin', 'cloak'], true))
        {
            throw new \DomainException(
                'Неизвестный тип текстуры.'
            );
        }

        /*
         * Скин доступен всем.
         * Плащ — только при активном VIP или выше.
         */
        if(
            $type === 'cloak'
            && !$this->canUseCloak($user)
        )
        {
            throw new \DomainException(
                'Загрузка плаща доступна начиная со статуса VIP.'
            );
        }

        if($file->size > 2 * 1024 * 1024)
        {
            throw new \DomainException(
                'Файл больше 2 МБ'
            );
        }

        $info = @getimagesize(
            $file->tempName
        );

        if(
            !$info
            || $info[2] !== IMAGETYPE_PNG
        )
        {
            throw new \DomainException(
                'Нужен PNG'
            );
        }

        [$width, $height] = $info;

        if($type === 'skin')
        {
            $valid =
                $width === 64
                && in_array(
                    $height,
                    [32, 64],
                    true
                );

            if(!$valid)
            {
                throw new \DomainException(
                    'Размер скина: 64x64 или 64x32'
                );
            }
        }
        else
        {
            $valid =
                ($width === 64 && $height === 32)
                || ($width === 22 && $height === 17);

            if(!$valid)
            {
                throw new \DomainException(
                    'Размер плаща: 64x32 или 22x17'
                );
            }
        }

        $dir = Yii::getAlias(
            '@storage/textures/'
            . (
                $type === 'skin'
                    ? 'skins'
                    : 'cloaks'
            )
        );

        if(!is_dir($dir))
        {
            mkdir(
                $dir,
                0770,
                true
            );
        }

        $path =
            $dir
            . '/'
            . $user->uuid
            . '.png';

        if(
            !move_uploaded_file(
                $file->tempName,
                $path
            )
        )
        {
            if(!$file->saveAs($path))
            {
                throw new \RuntimeException(
                    'Не удалось сохранить файл'
                );
            }
        }
    }

    public function pathByUsername(
        string $username,
        string $type
    ): ?string
    {
        if(
            !in_array(
                $type,
                ['skin', 'cloak'],
                true
            )
        )
        {
            return null;
        }

        $user =
            User::findActiveByUsername(
                $username
            );

        if(!$user)
        {
            return null;
        }

        /*
         * Сам файл плаща НЕ удаляем.
         *
         * Как только VIP закончился,
         * cloak endpoint перестаёт его отдавать.
         */
        if(
            $type === 'cloak'
            && !$this->canUseCloak($user)
        )
        {
            return null;
        }

        $path = Yii::getAlias(
            '@storage/textures/'
            . (
                $type === 'skin'
                    ? 'skins'
                    : 'cloaks'
            )
            . '/'
            . $user->uuid
            . '.png'
        );

        return is_file($path)
            ? $path
            : null;
    }

    private function canUseCloak(
        User $user
    ): bool
    {
        $server =
            Server::find()
                ->where([
                    'enabled' => 1,
                ])
                ->orderBy([
                    'sort' => SORT_ASC,
                    'id' => SORT_ASC,
                ])
                ->one();

        if(!$server)
        {
            return false;
        }

        return (
            new PrivilegeAccessService()
        )->canUseCloak(
            (int)$user->id,
            (int)$server->id
        );
    }
}

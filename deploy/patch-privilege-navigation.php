<?php

declare(strict_types=1);

$root =
    dirname(
        __DIR__
    );

$files = [
    $root
        . '/backend/views/layouts/main.php',
    $root
        . '/frontend/views/account/_nav.php',
];

foreach($files as $file)
{
    if(!is_file($file))
    {
        fwrite(
            STDERR,
            "Not found: {$file}\n"
        );

        continue;
    }

    $contents =
        file_get_contents(
            $file
        );

    if($contents === false)
    {
        fwrite(
            STDERR,
            "Read failed: {$file}\n"
        );

        continue;
    }

    $original =
        $contents;

    if(
        str_ends_with(
            $file,
            '/backend/views/layouts/main.php'
        )
    )
    {
        if(
            !str_contains(
                $contents,
                "'privilege/index'"
            )
        )
        {
            $needle =
                "['shop/index','Магазин','manageShop'],";

            $replacement =
                $needle
                . "['privilege/index','Привилегии','managePrivileges'],";

            $contents =
                str_replace(
                    $needle,
                    $replacement,
                    $contents
                );
        }
    }
    else
    {
        if(
            !str_contains(
                $contents,
                "['/privilege/index']"
            )
        )
        {
            $needle =
                '<a href="<?= Url::to([\'/wallet/index\']) ?>">Баланс</a>';

            $replacement =
                $needle
                . '<a href="<?= Url::to([\'/privilege/index\']) ?>">Статус</a>';

            $contents =
                str_replace(
                    $needle,
                    $replacement,
                    $contents
                );
        }
    }

    if($contents === $original)
    {
        echo "No change: {$file}\n";

        continue;
    }

    $backup =
        $file
        . '.before-privileges';

    if(!is_file($backup))
    {
        copy(
            $file,
            $backup
        );
    }

    file_put_contents(
        $file,
        $contents
    );

    echo "Patched: {$file}\n";
}

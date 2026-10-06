<?php

declare(strict_types=1);

namespace console\controllers;

use common\models\{
    News,
    NewsCategory
};
use common\services\ContentAutoTranslationService;
use yii\console\Controller;
use yii\console\ExitCode;

final class ContentController extends Controller
{
    public function actionSeedTechnicalOpening(): int
    {
        $slug = 'technical-opening';

        $news = News::findOne([
            'slug' => $slug,
        ]);

        if(!$news)
        {
            $news = new News([
                'slug' => $slug,
            ]);
        }

        $category = NewsCategory::findOne([
            'slug' => 'general',
        ]);

        $news->category_id = $category?->id;
        $news->author_id = null;

        $news->title =
            'Техническое открытие HiveGrid';

        $news->excerpt =
            'HiveGrid открыт для первых игроков. Сервер, сайт, лаунчер и основные игровые системы уже доступны в режиме технического запуска.';

        $news->body = <<<'HTML'
<p>
    Мы открываем HiveGrid для первых игроков.
</p>

<p>
    Сейчас проект работает в режиме технического запуска.
    Основная инфраструктура уже развёрнута: сайт, личный кабинет,
    собственный лаунчер, игровой сервер, система аккаунтов,
    магазин, статусы, поддержка и базовые механики сборки.
</p>

<h2>Что уже доступно</h2>

<p>
    Можно зарегистрировать единый аккаунт HiveGrid,
    установить клиент через лаунчер и подключиться к игровому серверу.
</p>

<p>
    Основное направление первой сборки —
    классическая промышленность Minecraft 1.12.2:
    IndustrialCraft 2, Forestry, пчёлы и генетика,
    Applied Energistics 2, Thermal Expansion,
    Immersive Engineering и другие технические моды.
</p>

<h2>Почему это техническое открытие</h2>

<p>
    На этом этапе мы проверяем работу проекта уже в реальных условиях:
    производительность сервера, баланс сборки,
    рецепты, экономику, приваты, загрузку чанков,
    работу лаунчера и веб-сервисов.
</p>

<p>
    Возможны технические работы, перезапуски
    и точечные изменения настроек сборки.
    О важных изменениях мы будем сообщать в новостях.
</p>

<h2>Обратная связь</h2>

<p>
    Если вы нашли ошибку на сайте, в личном кабинете,
    лаунчере или непосредственно в игре,
    пожалуйста, создайте обращение через раздел поддержки.
</p>

<p>
    Спасибо всем, кто присоединяется к HiveGrid
    на самом раннем этапе развития проекта.
</p>
HTML;

        $news->published = 1;

        if(!$news->published_at)
        {
            $news->published_at =
                date('Y-m-d H:i:s');
        }

        if(!$news->save())
        {
            $this->stderr(
                json_encode(
                    $news->errors,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_UNICODE
                ) . PHP_EOL
            );

            return ExitCode::DATAERR;
        }

        $this->stdout(
            "RU news saved: {$news->slug}\n"
        );

        if(class_exists(ContentAutoTranslationService::class))
        {
            $errors = (
                new ContentAutoTranslationService()
            )->translateNews($news);

            if($errors)
            {
                foreach($errors as $error)
                {
                    $this->stderr(
                        "TRANSLATION WARNING: {$error}\n"
                    );
                }
            }
            else
            {
                $this->stdout(
                    "EN/DE translations saved\n"
                );
            }
        }

        return ExitCode::OK;
    }
}

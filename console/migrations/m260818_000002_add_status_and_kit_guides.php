<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260818_000002_add_status_and_kit_guides extends Migration
{
    public function safeUp(): void
    {
        $this->savePage(
            'Статусы и привилегии',
            'guide-statuses',
            'Статусы и привилегии HiveGrid',
            'Описание статусов VIP, Premium, Elite и Legend на сервере HiveGrid.',
            140,
            <<<'HTML'
<p>
    На HiveGrid доступны дополнительные статусы:
    <strong>VIP</strong>, <strong>Premium</strong>,
    <strong>Elite</strong> и <strong>Legend</strong>.
</p>

<p>
    Статусы расширяют удобства аккаунта: количество точек дома,
    лимиты привата и загруженных чанков, а также косметические возможности.
</p>

<p>
    Актуальную стоимость и доступные сроки всегда смотрите в разделе
    <a href="/shop">Магазин</a>.
</p>

<hr>

<h2>VIP</h2>

<ul>
    <li><strong>4</strong> точки дома.</li>
    <li>До <strong>75</strong> чанков привата.</li>
    <li><strong>1</strong> постоянно загруженный чанк.</li>
    <li>Загрузка собственного плаща.</li>
</ul>

<h3>Старт-кит VIP</h3>

<ul>
    <li>32 еды;</li>
    <li>64 факела;</li>
    <li>кровать;</li>
    <li>компас;</li>
    <li>IC2 Treetap;</li>
    <li>Forge Hammer;</li>
    <li>Cutter;</li>
    <li>набор строительных блоков.</li>
</ul>

<hr>

<h2>Premium</h2>

<ul>
    <li><strong>7</strong> точек дома.</li>
    <li>До <strong>100</strong> чанков привата.</li>
    <li><strong>2</strong> загруженных чанка.</li>
    <li>Загрузка собственного плаща.</li>
    <li>Выбор цвета ника.</li>
</ul>

<h3>Старт-кит Premium</h3>

<ul>
    <li>48 еды;</li>
    <li>128 факелов;</li>
    <li>кровать;</li>
    <li>IC2 Treetap;</li>
    <li>Forge Hammer;</li>
    <li>Cutter;</li>
    <li>IC2 Wrench;</li>
    <li>RE-Battery;</li>
    <li>рюкзак;</li>
    <li>набор строительных блоков.</li>
</ul>

<hr>

<h2>Elite</h2>

<ul>
    <li><strong>10</strong> точек дома.</li>
    <li>До <strong>150</strong> чанков привата.</li>
    <li><strong>3</strong> загруженных чанка.</li>
    <li>Загрузка собственного плаща.</li>
    <li>Выбор цвета ника.</li>
</ul>

<h3>Старт-кит Elite</h3>

<ul>
    <li>64 еды;</li>
    <li>192 факела;</li>
    <li>кровать;</li>
    <li>Forge Hammer;</li>
    <li>Cutter;</li>
    <li>IC2 Wrench;</li>
    <li>RE-Battery;</li>
    <li>Toolbox;</li>
    <li>улучшенный рюкзак;</li>
    <li>полезные расходники.</li>
</ul>

<hr>

<h2>Legend</h2>

<ul>
    <li><strong>15</strong> точек дома.</li>
    <li>До <strong>200</strong> чанков привата.</li>
    <li><strong>4</strong> загруженных чанка.</li>
    <li>Загрузка собственного плаща.</li>
    <li>Выбор цвета ника.</li>
</ul>

<h3>Старт-кит Legend</h3>

<ul>
    <li>64 еды;</li>
    <li>256 факелов;</li>
    <li>кровать;</li>
    <li>Toolbox;</li>
    <li>2 × RE-Battery;</li>
    <li>Electric Wrench;</li>
    <li>улучшенный рюкзак;</li>
    <li>расширенный набор расходников и QoL-инструментов.</li>
</ul>

<hr>

<h2>Что происходит после окончания статуса?</h2>

<p>
    Временные возможности статуса действуют только в течение оплаченного периода.
    Постройки, обычные предметы и прогресс персонажа при этом не являются отдельным
    статусом и сами по себе не удаляются.
</p>

<p>
    Следите за лимитами привата и загруженных чанков:
    после изменения статуса действуют лимиты текущего уровня аккаунта.
</p>

<p>
    <a href="/page/guide-kits"><strong>Подробнее о стартовых китах →</strong></a>
</p>
HTML
        );

        $this->savePage(
            'Стартовые киты',
            'guide-kits',
            'Стартовые киты HiveGrid',
            'Что входит в стартовые киты статусов HiveGrid и для чего нужны полученные предметы.',
            150,
            <<<'HTML'
<p>
    Стартовые киты HiveGrid предназначены не для того,
    чтобы пропустить развитие сборки, а чтобы убрать часть стартовой рутины.
</p>

<p>
    Чем выше статус, тем больше в комплекте расходников
    и полезных инструментов для начала технического развития.
</p>

<hr>

<h2>VIP</h2>

<p><strong>Состав:</strong></p>

<p>
    32 еды · 64 факела · кровать · компас · IC2 Treetap ·
    Forge Hammer · Cutter · набор строительных блоков.
</p>

<p>
    Это базовый комплект для быстрого начала:
    исследование мира, шахта, освещение и первые крафты IndustrialCraft 2.
</p>

<hr>

<h2>Premium</h2>

<p><strong>Состав:</strong></p>

<p>
    48 еды · 128 факелов · кровать · IC2 Treetap ·
    Forge Hammer · Cutter · IC2 Wrench · RE-Battery ·
    рюкзак · набор строительных блоков.
</p>

<p>
    Помимо стартовых инструментов здесь уже появляются
    предметы для первых энергетических механизмов IC2.
</p>

<hr>

<h2>Elite</h2>

<p><strong>Состав:</strong></p>

<p>
    64 еды · 192 факела · кровать · Forge Hammer · Cutter ·
    IC2 Wrench · RE-Battery · Toolbox · улучшенный рюкзак ·
    полезные расходники.
</p>

<p>
    Комплект рассчитан на более быстрое развёртывание
    первой технической базы и организацию инструментов.
</p>

<hr>

<h2>Legend</h2>

<p><strong>Состав:</strong></p>

<p>
    64 еды · 256 факелов · кровать · Toolbox ·
    2 × RE-Battery · Electric Wrench · улучшенный рюкзак ·
    расширенный набор расходников и QoL-инструментов.
</p>

<p>
    Это наиболее полный стартовый комплект,
    ориентированный на игрока, который сразу начинает
    техническое развитие базы.
</p>

<hr>

<h2>Для чего нужны основные предметы?</h2>

<h3>IC2 Treetap</h3>

<p>
    Используется при работе с каучуковыми деревьями
    и получении смолы, необходимой для раннего развития IndustrialCraft 2.
</p>

<h3>Forge Hammer и Cutter</h3>

<p>
    Базовые инструменты IC2, необходимые для изготовления
    пластин, проводов и других компонентов.
</p>

<h3>IC2 Wrench</h3>

<p>
    Инструмент для работы с механизмами IndustrialCraft 2.
</p>

<h3>RE-Battery</h3>

<p>
    Переносной накопитель EU.
    Полезен на раннем этапе развития энергетики.
</p>

<h3>Electric Wrench</h3>

<p>
    Электрическая версия инструмента для работы
    с механизмами IC2.
</p>

<h3>Toolbox</h3>

<p>
    Помогает организовать и переносить технические инструменты.
</p>

<hr>

<h2>Кит не заменяет развитие</h2>

<p>
    В стартовых комплектах нет готового промышленного завода,
    сложных генераторов или конечного оборудования.
</p>

<p>
    Основные машины, энергетика, автоматизация,
    селекция и генетика по-прежнему развиваются самим игроком.
</p>

<p>
    Все рецепты предметов проверяйте через JEI:
    серверная сборка может отличаться от стандартных Wiki.
</p>

<hr>

<p>
    <a href="/page/guide-statuses"><strong>Статусы и их возможности →</strong></a>
</p>

<p>
    <a href="/page/guide-start"><strong>Гайд: первые 30 минут →</strong></a>
</p>
HTML
        );

        $this->savePage(
            'Гайды',
            'guides',
            'Гайды по HiveGrid',
            'Гайды HiveGrid: начало игры, технологии, пчёлы, статусы и стартовые киты.',
            100,
            <<<'HTML'
<p>
    Здесь собраны основные инструкции по HiveGrid.
    Если вы впервые на сервере, рекомендуем идти по порядку.
</p>

<hr>

<h2>Начало игры</h2>

<h3>Первые 30 минут</h3>

<p>
    Первый вход, базовые ресурсы, хранение и подготовка к развитию.
</p>

<p>
    <a href="/page/guide-start"><strong>Читать →</strong></a>
</p>

<hr>

<h3>Энергия и первые машины</h3>

<p>
    IndustrialCraft 2, первая энергия,
    переработка ресурсов и начало промышленной базы.
</p>

<p>
    <a href="/page/guide-energy"><strong>Читать →</strong></a>
</p>

<hr>

<h3>Пчёлы и генетика</h3>

<p>
    Forestry, поколения пчёл, селекция,
    Gendustry и генетические механики.
</p>

<p>
    <a href="/page/guide-bees"><strong>Читать →</strong></a>
</p>

<hr>

<h2>Сервер и аккаунт</h2>

<h3>Статусы и привилегии</h3>

<p>
    Возможности VIP, Premium, Elite и Legend:
    точки дома, приваты, загруженные чанки и дополнительные функции.
</p>

<p>
    <a href="/page/guide-statuses"><strong>Читать →</strong></a>
</p>

<hr>

<h3>Стартовые киты</h3>

<p>
    Состав комплектов каждого статуса
    и назначение основных технических предметов.
</p>

<p>
    <a href="/page/guide-kits"><strong>Читать →</strong></a>
</p>

<hr>

<h2>Важно</h2>

<p>
    Для игровых рецептов используйте JEI.
    Именно игровой рецепт является актуальным для текущей версии сборки.
</p>
HTML
        );
    }

    private function savePage(
        string $title,
        string $slug,
        string $metaTitle,
        string $metaDescription,
        int $sort,
        string $body
    ): void {
        $id = $this->db->createCommand(
            'SELECT id FROM {{%pages}} WHERE slug = :slug',
            [':slug' => $slug]
        )->queryScalar();

        $data = [
            'title' => $title,
            'body' => $body,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'published' => 1,
            'sort' => $sort,
        ];

        if($id)
        {
            $this->update(
                '{{%pages}}',
                $data,
                ['id' => (int)$id]
            );

            return;
        }

        $data['slug'] = $slug;

        $this->insert('{{%pages}}', $data);
    }

    public function safeDown(): void
    {
        $this->delete('{{%pages}}', [
            'slug' => [
                'guide-statuses',
                'guide-kits',
            ],
        ]);
    }
}

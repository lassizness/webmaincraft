<?php

use common\models\UserPrivilege;
use yii\helpers\{
    Html,
    Url
};

$this->title = Yii::t('ui', 'Игровой статус');


// HIVEGRID_I18N_BUFFER_START
$__hgI18nKeys = [
    'Игровой статус',
    'Магазин',
    'Платных статусов пока нет.',
    'Сервер',
    'Статус',
    'Активен',
    'Начало:',
    'До:',
    'осталось',
    'дн.',
    'Старт-кит',
    'Стоимость',
    'Купить',
    'дней',
    'кварков',
    '4 точки дома · до 75 чанков привата · 1 загруженный чанк · загрузка собственного плаща',
    '7 точек дома · до 100 чанков привата · 2 загруженных чанка · загрузка собственного плаща · выбор цвета ника',
    '10 точек дома · до 150 чанков привата · 3 загруженных чанка · загрузка собственного плаща · выбор цвета ника',
    '15 точек дома · до 200 чанков привата · 4 загруженных чанка · загрузка собственного плаща · выбор цвета ника',
    '32 еды · 64 факела · кровать · компас · IC2 Treetap · Forge Hammer · Cutter · набор строительных блоков',
    '48 еды · 128 факелов · кровать · IC2 Treetap · Forge Hammer · Cutter · IC2 Wrench · RE-Battery · рюкзак · набор строительных блоков',
    '64 еды · 192 факела · кровать · Forge Hammer · Cutter · IC2 Wrench · RE-Battery · Toolbox · улучшенный рюкзак · полезные расходники',
    '64 еды · 256 факелов · кровать · Toolbox · 2× RE-Battery · Electric Wrench · улучшенный рюкзак · расширенный набор расходников и QoL-инструментов',
    ' на 30 дней',
    ' на 90 дней',
    ' на 180 дней',
    ' на 365 дней',
];

ob_start();
?>

<div class="container ht-section">

    <div class="account-grid">

        <?= $this->render(
            '../account/_nav'
        ) ?>

        <section>

            <div class="section-title">

                <h2>
                    Игровой статус
                </h2>

                <a
                    class="btn btn-primary"
                    href="<?= Url::to(
                        [
                            '/shop/index',
                        ]
                    ) ?>"
                >
                    Магазин
                </a>

            </div>

            <?php if(!$privileges): ?>

                <div class="ht-card">
                    <p class="muted mb-0">
                        Платных статусов пока нет.
                    </p>
                </div>

            <?php else: ?>

                <div class="row g-3">

                    <?php foreach($privileges as $row): ?>

                        <?php
                        $active =
                            $row->status
                                === UserPrivilege::STATUS_ACTIVE
                            && strtotime(
                                (string)$row->active_to
                            ) > time();
                        ?>

                        <div class="col-lg-6">

                            <div class="ht-card h-100">

                                <div class="d-flex justify-content-between align-items-start">

                                    <div>

                                        <div class="muted">
                                            <?= Html::encode(
                                                $row->server?->name
                                                ?: 'Сервер'
                                            ) ?>
                                        </div>

                                        <h3
                                            class="mt-2"
                                            style="color: <?= Html::encode(
                                                $row->group?->color
                                                ?: '#ffffff'
                                            ) ?>"
                                        >
                                            <?= Html::encode(
                                                $row->group?->name
                                                ?: 'Статус'
                                            ) ?>
                                        </h3>

                                    </div>

                                    <span class="badge <?= $active
                                        ? 'text-bg-success'
                                        : 'text-bg-secondary'
                                    ?>">
                                        <?= $active
                                            ? 'Активен'
                                            : Html::encode(
                                                $row->status
                                            )
                                        ?>
                                    </span>

                                </div>

                                <hr>

                                <div class="small">
                                    Начало:
                                    <strong><?= Html::encode(
                                        $row->active_from
                                    ) ?></strong>
                                </div>

                                <div class="small mt-2">
                                    До:
                                    <strong><?= Html::encode(
                                        $row->active_to
                                    ) ?></strong>
                                </div>

                                <?php if($active): ?>

                                    <?php
                                    $seconds =
                                        max(
                                            0,
                                            strtotime(
                                                (string)$row->active_to
                                            )
                                            - time()
                                        );

                                    $days =
                                        (int)ceil(
                                            $seconds
                                            / 86400
                                        );
                                    ?>

                                    <div class="metric mt-3">
                                        <?= $days ?>
                                        дн.
                                    </div>

                                    <div class="muted">
                                        осталось
                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    </div>

</div>


<?php
// HIVEGRID_I18N_BUFFER_END
$__hgHtml = ob_get_clean();

if(Yii::$app->language !== 'ru')
{
    $__hgMap = [];

    foreach($__hgI18nKeys as $__hgKey)
    {
        $__hgTranslated = Yii::t(
            'ui',
            $__hgKey
        );

        if($__hgTranslated !== $__hgKey)
        {
            $__hgMap[$__hgKey] =
                $__hgTranslated;
        }
    }

    if($__hgMap)
    {
        $__hgHtml = strtr(
            $__hgHtml,
            $__hgMap
        );
    }
}

echo $__hgHtml;

unset(
    $__hgHtml,
    $__hgMap,
    $__hgKey,
    $__hgTranslated,
    $__hgI18nKeys
);
?>


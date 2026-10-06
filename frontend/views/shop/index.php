<?php

use common\models\Setting;
use yii\helpers\{
    Html,
    Json,
    Url
};

$this->title = Yii::t('ui', 'Магазин');

$translateShopText = static function (?string $value): string {
    $value = trim((string)$value);

    if($value === '' || Yii::$app->language === 'ru')
    {
        return $value;
    }

    $normalized = preg_replace('/\s+/u', ' ', $value);

    if(!is_string($normalized) || $normalized === '')
    {
        return $value;
    }

    if(preg_match(
        '/^(VIP|Premium|Elite|Legend)\\s+на\\s+(\\d+)\\s+дн(?:я|ей)?\\.?$/u',
        $normalized,
        $matches
    ))
    {
        return Yii::t('ui', '{status} на {days} дней', [
            'status' => $matches[1],
            'days' => (int)$matches[2],
        ]);
    }

    $translated = Yii::t('ui', $normalized);

    return $translated !== $normalized
        ? $translated
        : $value;
};

$daysLabel = Yii::t('ui', 'дней');
$quarksLabel = Yii::t('ui', 'кварков');
$jsLocale = match(Yii::$app->language)
{
    'de' => 'de-DE',
    'en' => 'en-US',
    default => 'ru-RU',
};

?>

<div class="container ht-section">

    <div class="section-title">

        <h2><?= Yii::t('ui', 'Магазин') ?></h2>

        <?php if(!Yii::$app->user->isGuest): ?>

            <div>
                <a
                    href="<?= Url::to(['/wallet/index']) ?>"
                    class="btn btn-outline-light"
                >
                    <?= Yii::t('ui', 'Баланс') ?>
                </a>

                <a
                    href="<?= Url::to(['/shop/promo']) ?>"
                    class="btn btn-outline-light"
                >
                    <?= Yii::t('ui', 'Промокод') ?>
                </a>
            </div>

        <?php endif; ?>

    </div>


    <?php
    $shopDescription = $translateShopText(
        (string)Setting::value(
            'shop.description',
            ''
        )
    );
    ?>

    <?php if($shopDescription !== ''): ?>

        <div class="shop-intro ht-card mb-4">

            <div class="eyebrow">
                HiveTech Store
            </div>

            <p class="muted mb-0">
                <?= nl2br(
                    Html::encode($shopDescription)
                ) ?>
            </p>

        </div>

    <?php endif; ?>


    <?php foreach($categories as $category): ?>

        <?php

        $privilegeGroups = [];
        $regularProducts = [];
        $availableDurations = [];

        foreach($category->products as $product)
        {
            if(
                $product->product_type === 'privilege'
                && $product->privilege_group_id
            )
            {
                $groupId = (int)$product->privilege_group_id;

                if(!isset($privilegeGroups[$groupId]))
                {
                    $privilegeGroups[$groupId] = [
                        'group' => $product->privilegeGroup,
                        'products' => [],
                    ];
                }

                $days = (int)$product->duration_days;

                $privilegeGroups[$groupId]['products'][$days] = $product;

                $availableDurations[$days] = true;
            }
            else
            {
                $regularProducts[] = $product;
            }
        }

        ksort($availableDurations);

        ?>

        <h3 class="mt-4">
            <?= Html::encode($translateShopText((string)$category->name)) ?>
        </h3>

        <?php if(trim((string)$category->description) !== ''): ?>

            <p class="muted shop-category-description">
                <?= nl2br(
                    Html::encode($translateShopText((string)$category->description))
                ) ?>
            </p>

        <?php endif; ?>


        <?php if($privilegeGroups): ?>

            <?php

            $durations = array_keys($availableDurations);

            $defaultDuration = in_array(30, $durations, true)
                ? 30
                : (int)reset($durations);

            ?>

            <div
                class="privilege-duration-toggle mt-3"
                data-privilege-duration-toggle
            >

                <?php foreach($durations as $days): ?>

                    <button
                        type="button"
                        class="privilege-duration-btn <?= $days === $defaultDuration ? 'active' : '' ?>"
                        data-duration="<?= $days ?>"
                    >
                        <?= $days ?> <?= Html::encode($daysLabel) ?>
                    </button>

                <?php endforeach; ?>

            </div>


            <div class="shop-grid mt-3">

                <?php foreach($privilegeGroups as $entry): ?>

                    <?php

                    $group = $entry['group'];
                    $products = $entry['products'];

                    ksort($products);

                    $variants = [];

                    foreach($products as $days => $product)
                    {
                        $variants[(string)$days] = [
                            'id' => (int)$product->id,
                            'price' => (float)$product->price,
                            'url' => Url::to([
                                '/shop/buy',
                                'id' => $product->id,
                            ]),
                            'name' => $translateShopText(
                                (string)$product->name
                            ),
                        ];
                    }

                    $selectedProduct =
                        $products[$defaultDuration]
                        ?? reset($products);

                    $selectedDays =
                        isset($products[$defaultDuration])
                            ? $defaultDuration
                            : (int)array_key_first($products);

                    ?>

                    <div
                        class="product-card privilege-card"
                        data-privilege-card
                        data-variants="<?= Html::encode(
                            Json::encode($variants)
                        ) ?>"
                    >

                        <div class="privilege-card-head">

                            <div>

                                <div class="eyebrow">
                                    <?= Yii::t('ui', 'Статус') ?>
                                </div>

                                <h3
                                    <?php if($group?->color): ?>
                                        style="color: <?= Html::encode($group->color) ?>"
                                    <?php endif; ?>
                                >
                                    <?= Html::encode(
                                        $translateShopText(
                                            (string)(
                                                $group?->name
                                                ?: $selectedProduct->name
                                            )
                                        )
                                    ) ?>
                                </h3>

                            </div>

                            <span
                                class="privilege-days"
                                data-selected-days
                            >
                                <?= $selectedDays ?> <?= Html::encode($daysLabel) ?>
                            </span>

                        </div>


                        <p class="muted privilege-description">

                            <?= Html::encode(
                                $translateShopText(
                                    (string)(
                                        $group?->description
                                        ?: $selectedProduct->description
                                    )
                                )
                            ) ?>

                        </p>


                        <?php if(
                            $group
                            && trim(
                                (string)$group->kit_description
                            ) !== ''
                        ): ?>

                            <div class="privilege-kit-box">

                                <div class="privilege-kit-title">
                                    <?= Yii::t('ui', 'Кит · раз в 30 дней') ?>
                                </div>

                                <div class="privilege-kit-text">
                                    <?= Html::encode(
                                        $translateShopText(
                                            (string)$group->kit_description
                                        )
                                    ) ?>
                                </div>

                            </div>

                        <?php endif; ?>


                        <div class="privilege-card-bottom">

                            <div>

                                <div class="muted small">
                                    <?= Yii::t('ui', 'Стоимость') ?>
                                </div>

                                <div
                                    class="privilege-price"
                                    data-price
                                >
                                    <?= number_format(
                                        (float)$selectedProduct->price,
                                        0,
                                        ',',
                                        ' '
                                    ) ?> <?= Html::encode($quarksLabel) ?>
                                </div>

                            </div>


                            <?php if(Yii::$app->user->isGuest): ?>

                                <a
                                    class="btn btn-primary"
                                    href="<?= Url::to(['/auth/login']) ?>"
                                >
                                    <?= Yii::t('app', 'Войти') ?>
                                </a>

                            <?php else: ?>

                                <?= Html::beginForm(
                                    [
                                        '/shop/buy',
                                        'id' => $selectedProduct->id,
                                    ],
                                    'post',
                                    [
                                        'data-buy-form' => '1',
                                    ]
                                ) ?>

                                <?= Html::submitButton(
                                    Yii::t('ui', 'Купить'),
                                    [
                                        'class' => 'btn btn-primary',
                                        'data-buy-button' => '1',
                                    ]
                                ) ?>

                                <?= Html::endForm() ?>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <?php if($regularProducts): ?>

            <div class="shop-grid mt-3">

                <?php foreach($regularProducts as $product): ?>

                    <div class="product-card">

                        <h3>
                            <?= Html::encode(
                                $translateShopText((string)$product->name)
                            ) ?>
                        </h3>

                        <p class="muted">
                            <?= Html::encode(
                                $translateShopText((string)$product->description)
                            ) ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-center">

                            <strong>
                                <?= number_format(
                                    (float)$product->price,
                                    0,
                                    ',',
                                    ' '
                                ) ?> <?= Html::encode($quarksLabel) ?>
                            </strong>

                            <?php if(Yii::$app->user->isGuest): ?>

                                <a
                                    class="btn btn-primary"
                                    href="<?= Url::to(['/auth/login']) ?>"
                                >
                                    <?= Yii::t('app', 'Войти') ?>
                                </a>

                            <?php else: ?>

                                <?= Html::beginForm(
                                    [
                                        '/shop/buy',
                                        'id' => $product->id,
                                    ],
                                    'post'
                                ) ?>

                                <?= Html::submitButton(
                                    Yii::t('ui', 'Купить'),
                                    [
                                        'class' => 'btn btn-primary',
                                    ]
                                ) ?>

                                <?= Html::endForm() ?>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    <?php endforeach; ?>


    <?php if(!$categories): ?>

        <div class="ht-card">
            <p class="muted mb-0">
                <?= Yii::t('ui', 'Каталог пока пуст.') ?>
            </p>
        </div>

    <?php endif; ?>

</div>


<style>

.privilege-duration-toggle {
    display: inline-flex;
    gap: 4px;
    padding: 5px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 12px;
    background: rgba(255,255,255,.035);
}

.privilege-duration-btn {
    border: 0;
    border-radius: 8px;
    padding: 9px 16px;
    background: transparent;
    color: rgba(255,255,255,.65);
    font-weight: 600;
    transition: .15s ease;
}

.privilege-duration-btn:hover {
    color: #fff;
    background: rgba(255,255,255,.06);
}

.privilege-duration-btn.active {
    color: #fff;
    background: rgba(255,255,255,.13);
}

.privilege-card {
    display: flex;
    flex-direction: column;
    min-height: 250px;
}

.privilege-card-head {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    align-items: flex-start;
}

.privilege-card-head h3 {
    margin-bottom: 0;
}

.privilege-days {
    white-space: nowrap;
    padding: 5px 9px;
    border-radius: 8px;
    background: rgba(255,255,255,.07);
    color: rgba(255,255,255,.7);
    font-size: 13px;
}

.privilege-description {
    margin-top: 18px;
    flex: 1;
}

.privilege-card-bottom {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 20px;
    margin-top: 20px;
}

.privilege-price {
    font-size: 24px;
    line-height: 1.1;
    font-weight: 700;
    margin-top: 3px;
}

@media (max-width: 700px) {

    .privilege-duration-toggle {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        width: 100%;
    }

    .privilege-duration-btn {
        width: 100%;
    }
}



.privilege-kit-box {
    margin-top: 16px;
    padding: 12px 13px;
    border: 1px solid rgba(31,201,151,.16);
    border-radius: 10px;
    background: rgba(31,201,151,.045);
}

.privilege-kit-title {
    margin-bottom: 6px;
    color: #54e6a8;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.privilege-kit-text {
    color: rgba(220,235,245,.78);
    font-size: 13px;
    line-height: 1.55;
}


.privilege-kit-text {
    white-space: pre-line;
    line-height: 1.6;
}


.privilege-description {
    white-space: pre-line;
    line-height: 1.55;
}

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const locale = <?= Json::htmlEncode($jsLocale) ?>;
    const daysLabel = <?= Json::htmlEncode($daysLabel) ?>;
    const quarksLabel = <?= Json::htmlEncode($quarksLabel) ?>;
    const unavailableLabel = <?= Json::htmlEncode(Yii::t('ui', 'Недоступно')) ?>;
    const buyLabel = <?= Json::htmlEncode(Yii::t('ui', 'Купить')) ?>;

    const toggle = document.querySelector(
        '[data-privilege-duration-toggle]'
    );

    if (!toggle) {
        return;
    }

    const buttons = toggle.querySelectorAll(
        '[data-duration]'
    );

    const cards = document.querySelectorAll(
        '[data-privilege-card]'
    );

    function formatPrice(value) {

        return new Intl.NumberFormat(
            locale,
            {
                maximumFractionDigits: 2
            }
        ).format(value) + ' ' + quarksLabel;

    }


    function selectDuration(days) {

        buttons.forEach(function (button) {

            button.classList.toggle(
                'active',
                Number(button.dataset.duration) === days
            );

        });


        cards.forEach(function (card) {

            const variants = JSON.parse(
                card.dataset.variants
            );

            const variant = variants[String(days)];

            const price = card.querySelector(
                '[data-price]'
            );

            const selectedDays = card.querySelector(
                '[data-selected-days]'
            );

            const form = card.querySelector(
                '[data-buy-form]'
            );

            const buyButton = card.querySelector(
                '[data-buy-button]'
            );


            if (!variant) {

                card.classList.add(
                    'privilege-unavailable'
                );

                if (buyButton) {
                    buyButton.disabled = true;
                    buyButton.textContent = unavailableLabel;
                }

                return;
            }


            card.classList.remove(
                'privilege-unavailable'
            );


            if (price) {
                price.textContent = formatPrice(
                    variant.price
                );
            }


            if (selectedDays) {
                selectedDays.textContent =
                    days + ' ' + daysLabel;
            }


            if (form) {
                form.action = variant.url;
            }


            if (buyButton) {
                buyButton.disabled = false;
                buyButton.textContent = buyLabel;
            }

        });

    }


    buttons.forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                selectDuration(
                    Number(button.dataset.duration)
                );

            }
        );

    });

});

</script>

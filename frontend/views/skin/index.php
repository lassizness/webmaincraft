<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var common\models\User $user
 */

$this->title = Yii::t('ui', 'Персонализация персонажа');

$skinPath = Yii::getAlias(
    '@storage/textures/skins/' . $user->uuid . '.png'
);

$cloakPath = Yii::getAlias(
    '@storage/textures/cloaks/' . $user->uuid . '.png'
);

$hasSkin = is_file($skinPath);
$hasCloakFile = is_file($cloakPath);
$hasCloak = $hasCloakFile && $canCloak;

$skinUrl = $hasSkin
    ? Url::to([
        '/texture/skin',
        'username' => $user->username,
        'v' => filemtime($skinPath),
    ])
    : '';

$cloakUrl = $hasCloak
    ? Url::to([
        '/texture/cloak',
        'username' => $user->username,
        'v' => filemtime($cloakPath),
    ])
    : '';


?>

<div class="container ht-section skin-page">

    <div class="account-grid">

        <?= $this->render('../account/_nav') ?>

        <section class="skin-content">

            <div class="ht-card">

                <div class="mb-4">
                    <h2 class="mb-1">
                        <?= Yii::t('ui', 'Персонализация персонажа') ?>
                    </h2>

                    <div class="muted">
                        <?= Yii::t('ui', 'Настройте внешний вид персонажа: скин, плащ и цвет ника.') ?>
                    </div>
                </div>


                <?= Html::beginForm(
                    '',
                    'post',
                    [
                        'id' => 'texture-form',
                        'enctype' => 'multipart/form-data',
                    ]
                ) ?>


                <div class="texture-grid">

                    <!-- SKIN -->
                    <div class="texture-card">

                        <div class="texture-card-header">

                            <div>
                                <strong><?= Yii::t('ui', 'Скин') ?></strong>

                                <div class="texture-format">
                                    <?= Yii::t('ui', 'PNG · 64×64 или 64×32') ?>
                                </div>
                            </div>


                            <?php if($hasSkin): ?>

                                <?= Html::a(
                                    Yii::t('ui', 'Удалить'),
                                    [
                                        '/skin/delete',
                                        'type' => 'skin',
                                    ],
                                    [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'data-method' => 'post',
                                        'data-confirm' => Yii::t('ui', 'Удалить текущий скин?'),
                                        'data-drop-ignore' => '1',
                                    ]
                                ) ?>

                            <?php endif; ?>

                        </div>


                        <div
                            id="skin-drop"
                            class="texture-drop-zone"
                            data-input="skin-file"
                        >

                            <canvas
                                id="skin-character"
                                width="150"
                                height="300"
                            ></canvas>


                            <div
                                id="skin-empty"
                                class="texture-empty"
                                <?= $hasSkin ? 'style="display:none"' : '' ?>
                            >
                                <div class="drop-icon">
                                    ↓
                                </div>

                                <strong>
                                    <?= Yii::t('ui', 'Перетащите скин сюда') ?>
                                </strong>

                                <span>
                                    <?= Yii::t('ui', 'или нажмите для выбора файла') ?>
                                </span>
                            </div>


                            <div class="drop-overlay">
                                <?= Yii::t('ui', 'Отпустите файл') ?>
                            </div>

                        </div>


                        <input
                            id="skin-file"
                            type="file"
                            name="skin"
                            accept="image/png"
                            hidden
                        >

                    </div>


                    <!-- CLOAK -->
                    <div class="texture-card">

                        <div class="texture-card-header">

                            <div>
                                <strong><?= Yii::t('ui', 'Плащ') ?></strong>

                                <div class="texture-format">
                                    <?= Yii::t('ui', 'PNG · 64×32 или 22×17') ?>
                                </div>
                            </div>


                            <?php if($hasCloak): ?>

                                <?= Html::a(
                                    Yii::t('ui', 'Удалить'),
                                    [
                                        '/skin/delete',
                                        'type' => 'cloak',
                                    ],
                                    [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'data-method' => 'post',
                                        'data-confirm' => Yii::t('ui', 'Удалить текущий плащ?'),
                                        'data-drop-ignore' => '1',
                                    ]
                                ) ?>

                            <?php endif; ?>

                        </div>


                        <div
                            id="cloak-drop"
                            class="texture-drop-zone"
                            data-input="cloak-file"
                        >

                            <img
                                id="cloak-preview"
                                src="<?= Html::encode($cloakUrl) ?>"
                                alt="<?= Html::encode(Yii::t('ui', 'Плащ')) ?>"
                                <?= $hasCloak ? '' : 'style="display:none"' ?>
                            >


                            <div
                                id="cloak-empty"
                                class="texture-empty"
                                <?= $hasCloak ? 'style="display:none"' : '' ?>
                            >
                                <div class="drop-icon">
                                    ↓
                                </div>

                                <strong>
                                    <?= Yii::t('ui', 'Перетащите плащ сюда') ?>
                                </strong>

                                <span>
                                    <?= Yii::t('ui', 'или нажмите для выбора файла') ?>
                                </span>
                            </div>


                            <div class="drop-overlay">
                                <?= Yii::t('ui', 'Отпустите файл') ?>
                            </div>

                        </div>


                        <input
                            id="cloak-file"
                            type="file"
                            name="cloak"
                            accept="image/png"
                            hidden
                        >

                    </div>

                </div>


                <div
                    id="upload-status"
                    class="upload-status"
                >
                </div>


                <?= Html::endForm() ?>

            

<!-- NICK COLOR -->
            <div
                class="character-personalization-section"
                data-hivetech-nick-color
            >

                <div class="character-option-header">

                    <div>

                        <div class="eyebrow">
                            <?= Yii::t('ui', 'Персонализация') ?>
                        </div>

                        <h3>
                            <?= Yii::t('ui', 'Цвет ника') ?>
                        </h3>

                    </div>

                    <?php if($privilege?->group): ?>

                        <span
                            class="character-rank-badge"
                            style="border-color: <?= Html::encode(
                                $privilege->group->color ?: '#777'
                            ) ?>"
                        >
                            <?= Html::encode(
                                $privilege->group->name
                            ) ?>
                        </span>

                    <?php endif; ?>

                </div>


                <?php if(!$canNickColor): ?>

                    <p class="muted mb-3">
                        <?= Yii::t('ui', 'Выбор цвета ника доступен начиная со статуса Premium.') ?>
                    </p>

                    <a
                        href="<?= Url::to(['/shop/index']) ?>"
                        class="btn btn-outline-light"
                    >
                        <?= Yii::t('ui', 'Посмотреть привилегии') ?>
                    </a>

                <?php else: ?>

                    <?php
                    $currentNickColor =
                        $cosmetic?->nick_color;
                    ?>

                    <p class="muted">
                        <?= Yii::t('ui', 'Выберите цвет ника из стандартной палитры Minecraft. Цвет префикса статуса при этом не изменяется.') ?>
                    </p>


                    <div class="nick-game-preview">

                        <span class="muted">
                            <?= Yii::t('ui', 'Как будет выглядеть ник:') ?>
                        </span>

                        <div class="nick-game-preview-value">

                            <?php if($privilege?->group): ?>

                                <span>
                                    [<?= Html::encode(
                                        strtoupper(
                                            $privilege->group->name
                                        )
                                    ) ?>]
                                </span>

                            <?php endif; ?>

                            <strong
                                <?php if(
                                    $currentNickColor
                                    && isset(
                                        $colors[
                                            $currentNickColor
                                        ]
                                    )
                                ): ?>
                                    style="color: <?= Html::encode(
                                        $colors[
                                            $currentNickColor
                                        ]['hex']
                                    ) ?>"
                                <?php endif; ?>
                            >
                                <?= Html::encode(
                                    $user->username
                                ) ?>
                            </strong>

                        </div>

                    </div>


                    <div class="minecraft-color-palette">

                        <?php foreach(
                            $colors
                            as $colorKey => $color
                        ): ?>

                            <?= Html::beginForm(
                                ['/skin/nick-color'],
                                'post',
                                [
                                    'class' =>
                                        'minecraft-color-form',
                                ]
                            ) ?>

                            <input
                                type="hidden"
                                name="color"
                                value="<?= Html::encode(
                                    $colorKey
                                ) ?>"
                            >

                            <button
                                type="submit"
                                class="minecraft-color-option <?= (
                                    $currentNickColor
                                    === $colorKey
                                )
                                    ? 'active'
                                    : ''
                                ?>"
                            >

                                <span
                                    class="minecraft-color-dot"
                                    style="background: <?= Html::encode(
                                        $color['hex']
                                    ) ?>"
                                ></span>

                                <span>
                                    <?= Html::encode(
                                        Yii::t('ui', $color['label'])
                                    ) ?>
                                </span>

                            </button>

                            <?= Html::endForm() ?>

                        <?php endforeach; ?>

                    </div>


                    <?php if($currentNickColor): ?>

                        <div class="mt-4">

                            <?= Html::beginForm(
                                ['/skin/nick-color'],
                                'post'
                            ) ?>

                            <input
                                type="hidden"
                                name="color"
                                value="reset"
                            >

                            <?= Html::submitButton(
                                Yii::t('ui', 'Сбросить цвет'),
                                [
                                    'class' =>
                                        'btn btn-outline-light',
                                ]
                            ) ?>

                            <?= Html::endForm() ?>

                        </div>

                    <?php endif; ?>

                <?php endif; ?>

            </div>

</div>

        

            

</section>

    </div>

</div>


<style>

.skin-page {
    padding-bottom: 50px;
}

.skin-content {
    min-width: 0;
}

.texture-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.texture-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 12px;
    background: rgba(255,255,255,.025);
}

.texture-card-header {
    display: flex;
    min-height: 68px;
    padding: 12px 16px;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255,255,255,.08);
}

.texture-format {
    margin-top: 3px;
    color: rgba(226,238,247,.72);
    font-size: 12px;
}

.texture-drop-zone {
    position: relative;
    display: flex;
    height: 360px;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    background: rgba(0,0,0,.12);
    transition:
        background .15s ease,
        box-shadow .15s ease;
}

.texture-drop-zone:hover {
    background: rgba(255,255,255,.035);
}

.texture-drop-zone.dragging {
    background: rgba(31,201,151,.08);
    box-shadow:
        inset 0 0 0 2px rgba(31,201,151,.8);
}

.texture-drop-zone.uploading {
    pointer-events: none;
    opacity: .65;
}

#skin-character {
    width: 150px;
    height: 300px;
    image-rendering: pixelated;
    image-rendering: crisp-edges;
}

#cloak-preview {
    width: 240px;
    max-width: 75%;
    max-height: 300px;
    object-fit: contain;
    image-rendering: pixelated;
    image-rendering: crisp-edges;
}

.texture-empty {
    position: absolute;
    display: flex;
    flex-direction: column;
    gap: 7px;
    align-items: center;
    justify-content: center;
    color: rgba(235,243,249,.72);
    text-align: center;
    pointer-events: none;
}

.texture-empty strong {
    color: rgba(245,249,252,.9);
}

.texture-empty span {
    font-size: 13px;
}

.drop-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 4px;
    border: 1px dashed rgba(255,255,255,.3);
    border-radius: 12px;
    font-size: 28px;
    line-height: 44px;
}

.drop-overlay {
    position: absolute;
    inset: 0;
    z-index: 10;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(7,18,25,.88);
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    pointer-events: none;
}

.texture-drop-zone.dragging .drop-overlay {
    display: flex;
}

.upload-status {
    min-height: 24px;
    margin-top: 14px;
    color: rgba(226,238,247,.74);
    font-size: 13px;
}

@media (max-width: 900px) {

    .texture-grid {
        grid-template-columns: 1fr;
    }

    .texture-drop-zone {
        height: 320px;
    }

}

@media (max-width: 560px) {

    .texture-card-header,
    .character-option-header {
        flex-direction: column;
        align-items: stretch;
    }

    .texture-card-header .btn,
    .character-rank-badge {
        align-self: flex-start;
    }

    .texture-drop-zone {
        height: 280px;
    }

    #skin-character {
        width: 130px;
        height: 260px;
    }

    .nick-game-preview-value {
        flex-wrap: wrap;
        overflow-wrap: anywhere;
    }
}





.character-option-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 18px;
}

.character-option-header h3 {
    margin-bottom: 0;
}

.character-rank-badge {
    padding: 6px 10px;
    border: 1px solid;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
}

.nick-game-preview {
    margin: 18px 0 22px;
    padding: 15px 17px;
    background: rgba(255,255,255,.035);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 10px;
}

.nick-game-preview-value {
    margin-top: 6px;
    font-size: 18px;
    display: flex;
    gap: 6px;
    align-items: center;
}

.minecraft-color-palette {
    display: grid;
    grid-template-columns:
        repeat(
            auto-fit,
            minmax(165px, 1fr)
        );
    gap: 10px;
}

.minecraft-color-form {
    margin: 0;
}

.minecraft-color-option {
    width: 100%;
    min-height: 48px;
    padding: 10px 12px;

    display: flex;
    align-items: center;
    gap: 10px;

    border: 1px solid rgba(255,255,255,.10);
    border-radius: 10px;

    color: inherit;
    background: rgba(255,255,255,.025);

    cursor: pointer;
    transition: .15s ease;
}

.minecraft-color-option:hover {
    background: rgba(255,255,255,.07);
}

.minecraft-color-option.active {
    border-color: rgba(255,255,255,.55);
    background: rgba(255,255,255,.11);
}

.minecraft-color-dot {
    display: block;

    width: 21px;
    height: 21px;

    flex: 0 0 21px;

    border-radius: 5px;
    border: 1px solid rgba(255,255,255,.25);
}



.character-personalization-section {
    margin-top: 26px;
    padding-top: 24px;
    border-top: 1px solid rgba(255,255,255,.09);
}

.character-personalization-section .character-option-header {
    margin-bottom: 14px;
}

.character-personalization-section .nick-game-preview {
    margin: 16px 0 18px;
}

.character-personalization-section .minecraft-color-palette {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 9px;
}

.character-personalization-section .minecraft-color-option {
    min-width: 0;
}

@media (max-width: 1100px) {
    .character-personalization-section .minecraft-color-palette {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .character-personalization-section .minecraft-color-palette {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 520px) {
    .character-personalization-section .minecraft-color-palette {
        grid-template-columns: 1fr;
    }
}

</style>


<script>

(function()
{
    const form =
        document.getElementById(
            'texture-form'
        );

    const status =
        document.getElementById(
            'upload-status'
        );


    function setStatus(text)
    {
        status.textContent = text;
    }


    function validateFile(file)
    {
        if(!file)
        {
            return false;
        }

        if(
            file.type !== 'image/png'
            && !file.name
                .toLowerCase()
                .endsWith('.png')
        )
        {
            alert(
                <?= Json::htmlEncode(Yii::t('ui', 'Можно загрузить только PNG.')) ?>
            );

            return false;
        }

        return true;
    }


    function submitFile(
        input,
        file,
        zone
    )
    {
        if(!validateFile(file))
        {
            return;
        }

        const transfer =
            new DataTransfer();

        transfer.items.add(file);

        input.files =
            transfer.files;

        zone.classList.add(
            'uploading'
        );

        setStatus(
            <?= Json::htmlEncode(Yii::t('ui', 'Загрузка файла...')) ?>
        );

        form.submit();
    }


    function initDropZone(zone)
    {
        const input =
            document.getElementById(
                zone.dataset.input
            );


        zone.addEventListener(
            'click',
            function(event)
            {
                if(
                    event.target.closest(
                        '[data-drop-ignore]'
                    )
                )
                {
                    return;
                }

                input.click();
            }
        );


        input.addEventListener(
            'change',
            function()
            {
                const file =
                    input.files[0];

                if(file)
                {
                    submitFile(
                        input,
                        file,
                        zone
                    );
                }
            }
        );


        zone.addEventListener(
            'dragenter',
            function(event)
            {
                event.preventDefault();

                zone.classList.add(
                    'dragging'
                );
            }
        );


        zone.addEventListener(
            'dragover',
            function(event)
            {
                event.preventDefault();

                event.dataTransfer
                    .dropEffect =
                        'copy';

                zone.classList.add(
                    'dragging'
                );
            }
        );


        zone.addEventListener(
            'dragleave',
            function(event)
            {
                if(
                    !zone.contains(
                        event.relatedTarget
                    )
                )
                {
                    zone.classList.remove(
                        'dragging'
                    );
                }
            }
        );


        zone.addEventListener(
            'drop',
            function(event)
            {
                event.preventDefault();

                zone.classList.remove(
                    'dragging'
                );

                const file =
                    event
                        .dataTransfer
                        .files[0];

                if(file)
                {
                    submitFile(
                        input,
                        file,
                        zone
                    );
                }
            }
        );
    }


    initDropZone(
        document.getElementById(
            'skin-drop'
        )
    );

    initDropZone(
        document.getElementById(
            'cloak-drop'
        )
    );


    /*
     * Простое фронтальное preview скина.
     */

    const canvas =
        document.getElementById(
            'skin-character'
        );

    const ctx =
        canvas.getContext('2d');

    ctx.imageSmoothingEnabled =
        false;


    function part(
        image,
        sx,
        sy,
        sw,
        sh,
        dx,
        dy,
        dw,
        dh
    )
    {
        ctx.drawImage(
            image,
            sx,
            sy,
            sw,
            sh,
            dx,
            dy,
            dw,
            dh
        );
    }


    function renderSkin(image)
    {
        ctx.clearRect(
            0,
            0,
            canvas.width,
            canvas.height
        );

        const modern =
            image.naturalHeight >= 64;


        // Голова
        part(
            image,
            8,8,8,8,
            37,0,76,76
        );


        // Шапка / второй слой головы
        part(
            image,
            40,8,8,8,
            37,0,76,76
        );


        // Тело
        part(
            image,
            20,20,8,12,
            37,76,76,112
        );


        // Правая рука
        part(
            image,
            44,20,4,12,
            0,76,37,112
        );


        // Левая рука
        part(
            image,
            modern ? 36 : 44,
            modern ? 52 : 20,
            4,12,
            113,76,37,112
        );


        // Правая нога
        part(
            image,
            4,20,4,12,
            37,188,38,112
        );


        // Левая нога
        part(
            image,
            modern ? 20 : 4,
            modern ? 52 : 20,
            4,12,
            75,188,38,112
        );
    }


    <?php if($hasSkin): ?>

    const currentSkin =
        new Image();

    currentSkin.onload =
        function()
        {
            renderSkin(
                currentSkin
            );
        };

    currentSkin.src =
        <?= json_encode(
            $skinUrl,
            JSON_UNESCAPED_SLASHES
        ) ?>;

    <?php endif; ?>

})();

</script>



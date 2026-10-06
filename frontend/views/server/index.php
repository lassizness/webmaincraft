<?php

use yii\helpers\Html;

$this->title = Yii::t('app', 'Серверы');

$modNames = [
    'appliedenergistics2' => 'Applied Energistics 2',
    'bdlib' => 'BDLib',
    'binniecore' => "Binnie's Core",
    'binniedesign' => "Binnie's Design",
    'genetics' => "Binnie's Genetics",
    'botany' => "Binnie's Botany",
    'extrabees' => 'Extra Bees',
    'extratrees' => 'Extra Trees',
    'biomesoplenty' => "Biomes O' Plenty",
    'buildcraftlib' => 'BuildCraft Lib',
    'buildcraftcore' => 'BuildCraft',
    'buildcrafttransport' => 'BuildCraft Transport',
    'crafttweaker' => 'CraftTweaker',
    'crafttweakerjei' => 'CraftTweaker JEI',
    'forestry' => 'Forestry',
    'gendustry' => 'Gendustry',
    'ic2' => 'IndustrialCraft 2',
    'industrialforegoing' => 'Industrial Foregoing',
    'immersiveengineering' => 'Immersive Engineering',
    'ironchest' => 'Iron Chests',
    'jei' => 'Just Enough Items',
    'storagedrawers' => 'Storage Drawers',
    'theoneprobe' => 'The One Probe',
    'thermalfoundation' => 'Thermal Foundation',
    'thermalexpansion' => 'Thermal Expansion',
    'ftblib' => 'FTB Library',
    'ftbutilities' => 'FTB Utilities',
    'xaerominimap' => "Xaero's Minimap",
    'xaeroworldmap' => "Xaero's World Map",
    'aiimprovements' => 'AI Improvements',
    'chunkpregenerator' => 'Chunk Pregenerator',
    'clumps' => 'Clumps',
    'spark' => 'Spark',
    'vintagefix' => 'VintageFix',
    'hivetechkits' => 'HiveTech Kits',
];

$technicalMods = [
    'minecraft',
    'mcp',
    'FML',
    'forge',
    'cleanroom',
    'cleanmix',
    'mixinbooter',
    'configanytime',
    'kirino_engine',
    'kirino_ecs',
    'kirino_gl',
    'scalar',
    'fugue',
    'carbonconfig',
    'chameleon',
    'codechickenlib',
    'redstoneflux',
    'cofhcore',
    'cofhworld',
    'forgelin',
    'forgelin_continuous',
    'ic2patcher',
    'ic2patcher-core',
    'teslacorelib',
    'teslacorelib_registries',
    'xaerolib',
    'xaerolib_core',
    'xaerominimap_core',
    'xaeroworldmap_core',
];

?>

<div class="container ht-section">

    <div class="section-title">
        <h2><?= Yii::t('app', 'Серверы HiveTech') ?></h2>
    </div>

    <div class="row g-3">

        <?php foreach ($rows as $row): ?>

            <?php
            $server = $row['server'];
            $status = $row['status'];

            $online = !empty($status['online']);

            $mods = $status['modinfo']['modList'] ?? [];

            usort(
                $mods,
                static function (array $a, array $b) use ($modNames): int {
                    $aId = $a['modid'] ?? '';
                    $bId = $b['modid'] ?? '';

                    $aName = $modNames[$aId] ?? $aId;
                    $bName = $modNames[$bId] ?? $bId;

                    return strcasecmp($aName, $bName);
                }
            );

            $contentMods = array_values(array_filter(
                $mods,
                static fn(array $mod): bool =>
                    !in_array(
                        $mod['modid'] ?? '',
                        $technicalMods,
                        true
                    )
            ));

            $collapseId = 'mods-' . (int)$server->id;
            ?>

            <div class="col-12">

                <div class="ht-card">

                    <div class="d-flex justify-content-between align-items-start gap-3">

                        <div>
                            <div class="eyebrow">
                                <?= Html::encode($server->code) ?>
                            </div>

                            <h3 class="mb-1">
                                <?= Html::encode($server->name) ?>
                            </h3>

                            <?php if ($online && isset($status['description'])): ?>
                                <div class="muted">
                                    <?php
                                    $description = $status['description'];

                                    if (is_array($description)) {
                                        echo Html::encode(
                                            $description['text'] ?? ''
                                        );
                                    } else {
                                        echo Html::encode((string)$description);
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <span class="badge <?= $online ? 'text-bg-success' : 'text-bg-secondary' ?>">
                            <?= $online ? 'ONLINE' : 'OFFLINE' ?>
                        </span>

                    </div>


                    <div class="row g-3 mt-2">

                        <div class="col-md-4">

                            <div class="muted small">
                                <?= Yii::t('app', 'Адрес сервера') ?>
                            </div>

                            <div class="fw-semibold">
                                <?= Html::encode($server->host . ':' . $server->port) ?>
                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="muted small">
                                <?= Yii::t('app', 'Версия') ?>
                            </div>

                            <div class="fw-semibold">
                                <?php if ($online): ?>
                                    <?= Html::encode(
                                        $status['version']['name'] ?? '1.12.2'
                                    ) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="muted small">
                                <?= Yii::t('app', 'Игроки') ?>
                            </div>

                            <div class="fw-semibold">
                                <?php if ($online): ?>

                                    <?= (int)($status['players']['online'] ?? 0) ?>
                                    /
                                    <?= (int)($status['players']['max'] ?? 0) ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </div>

                        </div>

                    </div>


                    <?php if ($online && $contentMods): ?>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center gap-3">

                            <div>
                                <div class="fw-semibold">
                                    <?= Yii::t('app', 'Модификации') ?>
                                </div>

                                <div class="muted small">
                                    <?= Yii::t('app', '{count} игровых модов', ['count' => count($contentMods)]) ?>
                                    · <?= Yii::t('app', '{count} компонентов всего', ['count' => count($mods)]) ?>
                                </div>
                            </div>

                            <button
                                class="btn btn-outline-light btn-sm"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= Html::encode($collapseId) ?>"
                                aria-expanded="false"
                                aria-controls="<?= Html::encode($collapseId) ?>"
                            >
                                <?= Yii::t('app', 'Показать список') ?>
                            </button>

                        </div>


                        <div
                            class="collapse mt-3"
                            id="<?= Html::encode($collapseId) ?>"
                        >

                            <div class="row g-2">

                                <?php foreach ($contentMods as $mod): ?>

                                    <?php
                                    $modId = $mod['modid'] ?? 'unknown';
                                    $modName = $modNames[$modId] ?? $modId;
                                    $version = $mod['version'] ?? '';
                                    ?>

                                    <div class="col-md-6 col-lg-4">

                                        <div class="border rounded p-2 h-100">

                                            <div class="fw-semibold">
                                                <?= Html::encode($modName) ?>
                                            </div>

                                            <div class="muted small">
                                                <?= Html::encode($modId) ?>

                                                <?php if ($version !== ''): ?>
                                                    · <?= Html::encode($version) ?>
                                                <?php endif; ?>
                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

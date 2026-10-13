<?php

declare(strict_types=1);

namespace common\services;

use common\models\Achievement;
use Yii;
use yii\helpers\Html;

final class AchievementIconService
{
    public const ICONS = [
        'clock' => 'Часы',
        'pickaxe' => 'Кирка',
        'blocks' => 'Строительство',
        'craft' => 'Крафт',
        'furnace' => 'Печь',
        'sword' => 'Меч',
        'skull' => 'Смерть',
        'duel' => 'PvP',
        'advancement' => 'Достижение',
        'portal' => 'Портал',
        'chip' => 'Технологии',
        'controller' => 'Контроллер',
        'storage-cell' => 'Ячейка хранения',
        'network' => 'Сеть',
        'terminal' => 'Терминал',
        'assembler' => 'Сборщик',
        'machine' => 'Машина',
        'generator' => 'Генератор',
        'battery' => 'Энергия',
        'wrench' => 'Ключ',
        'apiary' => 'Пасека',
        'alveary' => 'Большая пасека',
        'bee' => 'Пчела',
        'dna' => 'Генетика',
        'lab' => 'Лаборатория',
        'dynamo' => 'Динамо',
        'augment' => 'Улучшение',
        'upgrade' => 'Апгрейд',
        'storage' => 'Хранилище',
        'capacitor' => 'Конденсатор',
        'connector' => 'Соединитель',
        'multiblock' => 'Мультиблок',
        'hammer' => 'Молот',
        'robot' => 'Автоматизация',
        'factory' => 'Завод',
        'universal' => 'Инженерия',
        'toolkit' => 'Набор инженера',
        'question' => 'Секрет',
    ];

    public static function key(Achievement $achievement): string
    {
        $explicit = trim((string)$achievement->icon);

        if(isset(self::ICONS[$explicit]))
        {
            return $explicit;
        }

        return self::infer((string)$achievement->code);
    }

    public static function infer(string $code): string
    {
        $code = strtolower($code);

        return match(true) {
            str_contains($code, 'first_hour'),
            str_contains($code, 'ten_hours'),
            str_contains($code, 'fifty_hours'),
            str_contains($code, 'hundred_hours'),
            str_contains($code, 'five_hundred_hours') => 'clock',

            str_contains($code, 'mining_break') => 'pickaxe',
            str_contains($code, 'building_place') => 'blocks',
            str_contains($code, 'general_craft') => 'craft',
            str_contains($code, 'general_smelt') => 'furnace',
            str_contains($code, 'combat_first_death') => 'skull',
            str_contains($code, 'combat_pvp') => 'duel',
            str_contains($code, 'combat_kill') => 'sword',
            str_contains($code, 'advancements') => 'advancement',
            str_contains($code, 'exploration_dimensions') => 'portal',

            str_contains($code, 'ae2_controller') => 'controller',
            str_contains($code, 'ae2_storage_cell') => 'storage-cell',
            str_contains($code, 'ae2_network') => 'network',
            str_contains($code, 'ae2_terminal') => 'terminal',
            str_contains($code, 'ae2_crafting_storage') => 'storage',
            str_contains($code, 'ae2_molecular_assembler') => 'assembler',
            str_contains($code, 'ae2_machine') => 'machine',
            str_contains($code, 'ae2_first_craft') => 'chip',

            str_contains($code, 'ic2_wrench') => 'wrench',
            str_contains($code, 'ic2_energy_tier') => 'battery',
            str_contains($code, 'ic2_generator') => 'generator',
            str_contains($code, 'ic2_machine') => 'machine',

            str_contains($code, 'forestry_apiary') => 'apiary',
            str_contains($code, 'forestry_alveary') => 'alveary',
            str_contains($code, 'forestry_species') => 'bee',
            str_contains($code, 'forestry_bee_acquire') => 'bee',
            str_contains($code, 'forestry_genetics_tool') => 'dna',
            str_contains($code, 'forestry_machine') => 'lab',

            str_contains($code, 'thermal_dynamo') => 'dynamo',
            str_contains($code, 'thermal_augment') => 'augment',
            str_contains($code, 'thermal_upgrade') => 'upgrade',
            str_contains($code, 'thermal_storage') => 'storage',
            str_contains($code, 'thermal_machine') => 'machine',

            str_contains($code, 'ie_capacitor') => 'capacitor',
            str_contains($code, 'ie_connector') => 'connector',
            str_contains($code, 'ie_multiblock') => 'multiblock',
            str_contains($code, 'ie_hammer') => 'hammer',
            str_contains($code, 'ie_machine') => 'factory',

            str_contains($code, 'if_addon') => 'augment',
            str_contains($code, 'if_machine') => 'robot',
            str_contains($code, 'if_craft') => 'craft',

            str_contains($code, 'engineering_toolkit') => 'toolkit',
            str_contains($code, 'engineering_') => 'universal',

            default => 'advancement',
        };
    }

    public static function render(
        Achievement $achievement,
        bool $unlocked = true,
        string $size = 'md',
        ?string $title = null,
        bool $secret = false
    ): string {
        $key = $secret
            ? 'question'
            : self::key($achievement);

        $rarity = in_array(
            (string)$achievement->rarity,
            Achievement::RARITIES,
            true
        )
            ? (string)$achievement->rarity
            : 'common';

        $category = preg_replace(
            '/[^a-z0-9_-]+/i',
            '-',
            (string)$achievement->category
        );

        $label = $title
            ?? (
                $secret
                    ? Yii::t('ui', 'Секретное достижение')
                    : $achievement->getDisplayTitle()
            );

        $classes = [
            'achievement-icon',
            'achievement-icon--' . $size,
            'achievement-icon--' . $rarity,
            'achievement-icon--category-' . $category,
            $unlocked
                ? 'is-unlocked'
                : 'is-locked',
        ];

        return Html::tag(
            'span',
            self::svg($key),
            [
                'class' => implode(' ', $classes),
                'title' => $label,
                'aria-label' => $label,
                'data-achievement-icon' => $key,
            ]
        );
    }

    public static function options(): array
    {
        return self::ICONS;
    }

    private static function svg(string $key): string
    {
        $inner = match($key) {
            'clock' => '<circle cx="24" cy="24" r="12"/><path d="M24 17v8l6 4"/>',
            'pickaxe' => '<path d="M12 14c8-8 19-9 27-3-9-1-16 2-22 8"/><path d="m20 17-8 19"/><path d="m9 36 6 3"/>',
            'blocks' => '<path d="m24 8 12 7-12 7-12-7 12-7Z"/><path d="m12 15v14l12 7 12-7V15"/><path d="M24 22v14"/>',
            'craft' => '<path d="M11 34 34 11"/><path d="m30 9 9 9"/><path d="M10 26 22 38"/><path d="m9 35 4 4"/>',
            'furnace' => '<rect x="12" y="9" width="24" height="30" rx="3"/><path d="M16 14h16v7H16z"/><path d="M19 33c0-5 5-6 5-11 5 4 7 7 5 11-2 4-8 4-10 0Z"/>',
            'sword' => '<path d="m35 9-17 17"/><path d="m31 9 8 0 0 8-4-4"/><path d="m15 24 9 9"/><path d="m12 30 6 6"/><path d="m10 38 4-4"/>',
            'skull' => '<path d="M14 24a10 10 0 1 1 20 0v6l-4 4H18l-4-4v-6Z"/><circle cx="20" cy="24" r="2"/><circle cx="28" cy="24" r="2"/><path d="M22 31h4M20 34v4M28 34v4"/>',
            'duel' => '<path d="m10 12 27 27M38 12 11 39"/><path d="m9 9 7 2-5 5M39 9l-7 2 5 5"/><circle cx="24" cy="24" r="5"/>',
            'advancement' => '<circle cx="24" cy="20" r="10"/><path d="m18 29-3 11 9-5 9 5-3-11"/><path d="m24 14 2 4 5 .7-3.5 3.4.8 4.9-4.3-2.3-4.3 2.3.8-4.9-3.5-3.4 5-.7 2-4Z"/>',
            'portal' => '<path d="M14 8h20v32H14z"/><path d="M18 12h12v24H18z"/><path d="m20 17 7 5-6 5 7 5"/>',
            'chip' => '<rect x="14" y="14" width="20" height="20" rx="3"/><path d="M20 20h8v8h-8zM18 8v6M24 8v6M30 8v6M18 34v6M24 34v6M30 34v6M8 18h6M8 24h6M8 30h6M34 18h6M34 24h6M34 30h6"/>',
            'controller' => '<path d="M12 15 24 8l12 7v18l-12 7-12-7V15Z"/><circle cx="24" cy="24" r="6"/><path d="M24 12v6M24 30v6M12 24h6M30 24h6"/>',
            'storage-cell' => '<rect x="13" y="8" width="22" height="32" rx="4"/><path d="M18 13h12v8H18z"/><circle cx="19" cy="32" r="2"/><path d="M25 30h5M25 34h5"/>',
            'network' => '<circle cx="12" cy="24" r="4"/><circle cx="24" cy="12" r="4"/><circle cx="36" cy="24" r="4"/><circle cx="24" cy="36" r="4"/><path d="m15 21 6-6M27 15l6 6M33 27l-6 6M21 33l-6-6"/>',
            'terminal' => '<rect x="9" y="10" width="30" height="24" rx="3"/><path d="M14 16h20v12H14zM19 39h10M24 34v5"/>',
            'assembler' => '<path d="M13 11h22v26H13z"/><circle cx="24" cy="24" r="7"/><path d="M24 13v4M24 31v4M13 24h4M31 24h4M18 18l3 3M27 27l3 3M30 18l-3 3M21 27l-3 3"/>',
            'machine' => '<rect x="10" y="10" width="28" height="28" rx="4"/><circle cx="24" cy="24" r="7"/><path d="M24 17v14M17 24h14"/><path d="M14 14h5M29 34h5"/>',
            'generator' => '<path d="M18 9h12l6 10v18H12V19l6-10Z"/><path d="m25 16-6 10h6l-2 8 7-12h-6l1-6Z"/>',
            'battery' => '<rect x="13" y="11" width="22" height="28" rx="3"/><path d="M20 7h8v4"/><path d="m26 17-6 10h6l-3 8 8-12h-6l1-6Z"/>',
            'wrench' => '<path d="M30 11a9 9 0 0 0-10 12L10 33a4 4 0 0 0 5 5l10-10a9 9 0 0 0 12-10l-6 6-6-2-2-6 7-5Z"/>',
            'apiary' => '<path d="M12 17h24v20H12z"/><path d="m10 17 14-9 14 9"/><path d="M18 23h12M18 28h12M18 33h12"/><circle cx="35" cy="12" r="3"/>',
            'alveary' => '<path d="M9 18h30v20H9z"/><path d="m8 18 16-10 16 10"/><path d="M14 23h20M14 29h20M14 35h20"/><path d="M24 18v20"/>',
            'bee' => '<ellipse cx="24" cy="25" rx="9" ry="7"/><path d="M19 20v10M24 18v14M29 20v10"/><path d="M18 20c-7-2-8-8-3-10 4-1 7 3 9 8M30 20c7-2 8-8 3-10-4-1-7 3-9 8"/><path d="M15 25h-5M38 25h-5"/>',
            'dna' => '<path d="M15 9c18 8 18 22 0 30M33 9c-18 8-18 22 0 30"/><path d="M17 14h14M14 20h20M14 28h20M17 34h14"/>',
            'lab' => '<path d="M19 8h10M21 8v10L13 34a4 4 0 0 0 4 6h14a4 4 0 0 0 4-6l-8-16V8"/><path d="M16 31h16"/><circle cx="22" cy="34" r="2"/>',
            'dynamo' => '<circle cx="24" cy="24" r="13"/><path d="m26 12-8 14h7l-3 10 9-15h-7l2-9Z"/><path d="M24 7v4M24 37v4M7 24h4M37 24h4"/>',
            'augment' => '<circle cx="24" cy="24" r="11"/><path d="M24 9v6M24 33v6M9 24h6M33 24h6"/><path d="m24 18 2 4 5 .7-3.5 3.4.8 4.9-4.3-2.3-4.3 2.3.8-4.9-3.5-3.4 5-.7 2-4Z"/>',
            'upgrade' => '<path d="m24 8 10 10h-6v12h-8V18h-6L24 8Z"/><path d="M12 35h24v5H12z"/>',
            'storage' => '<path d="M10 12h28v8H10zM12 20h24v19H12z"/><path d="M19 26h10M19 31h10"/>',
            'capacitor' => '<path d="M18 11v26M30 11v26M10 24h8M30 24h8"/><path d="M13 16h5M30 32h5"/>',
            'connector' => '<circle cx="15" cy="24" r="6"/><circle cx="33" cy="24" r="6"/><path d="M21 24h6M12 18V12M36 30v6"/>',
            'multiblock' => '<path d="M10 12h12v12H10zM26 12h12v12H26zM10 28h12v12H10zM26 28h12v12H26z"/><path d="M22 18h4M16 24v4M32 24v4M22 34h4"/>',
            'hammer' => '<path d="M13 13h19l5 5-8 8-5-5-12 12-5-5 12-12-6-3Z"/><path d="m10 31 7 7"/>',
            'robot' => '<rect x="11" y="14" width="26" height="22" rx="5"/><path d="M24 8v6M20 8h8"/><circle cx="19" cy="24" r="2"/><circle cx="29" cy="24" r="2"/><path d="M18 30h12M7 21h4M37 21h4"/>',
            'factory' => '<path d="M9 39V22l10-6v6l10-6v6l10-6v23H9Z"/><path d="M14 28h5M24 28h5M34 28h3M14 34h5M24 34h5M34 34h3"/><path d="M12 22V10h6v8"/>',
            'universal' => '<circle cx="24" cy="24" r="15"/><path d="M24 9v30M9 24h30"/><circle cx="24" cy="24" r="6"/><path d="m13 13 5 5M35 13l-5 5M13 35l5-5M35 35l-5-5"/>',
            'toolkit' => '<path d="M11 18h26v20H11z"/><path d="M18 18v-5a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v5"/><path d="M11 26h26"/><path d="M21 23h6v6h-6z"/>',
            default => '<circle cx="24" cy="24" r="15"/><path d="M19 19a5 5 0 1 1 7 4.6c-2 1-2 2.2-2 4.4M24 34h.01"/>',
        };

        return '<svg viewBox="0 0 48 48" aria-hidden="true" focusable="false">'
            . $inner
            . '</svg>';
    }
}

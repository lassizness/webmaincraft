<?php

declare(strict_types=1);

use yii\db\Migration;

final class m261012_000001_seed_achievement_catalog_v1 extends Migration
{
    public function safeUp(): void
    {
        $sort = 100;

        foreach($this->catalog() as $item)
        {
            $existingId = $this->db
                ->createCommand(
                    'SELECT id FROM {{%achievements}} WHERE code = :code',
                    [':code' => $item['code']]
                )
                ->queryScalar();

            if($existingId)
            {
                continue;
            }

            $now = date('Y-m-d H:i:s');

            $this->insert('{{%achievements}}', [
                'season_id' => null,
                'code' => $item['code'],
                'title' => $item['title'],
                'description' => $item['description'],
                'hint' => $item['hint'] ?? null,
                'category' => $item['category'],
                'rarity' => $item['rarity'],
                'score' => $item['score'],
                'icon' => null,
                'hidden' => !empty($item['hidden']) ? 1 : 0,
                'enabled' => 1,
                'sort' => $sort,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $achievementId = (int)$this->db
                ->getLastInsertID();

            $conditionSort = 10;

            foreach($item['conditions'] as $condition)
            {
                $this->insert('{{%achievement_conditions}}', [
                    'achievement_id' => $achievementId,
                    'group_no' => $condition[3] ?? 1,
                    'stat_key' => $condition[0],
                    'operator' => $condition[2] ?? '>=',
                    'target_value' => $condition[1],
                    'sort' => $conditionSort,
                ]);

                $conditionSort += 10;
            }

            $sort += 10;
        }
    }

    public function safeDown(): void
    {
        $codes = array_column(
            $this->catalog(),
            'code'
        );

        $this->delete(
            '{{%achievements}}',
            ['code' => $codes]
        );
    }

    private function catalog(): array
    {
        return [
            $this->a('general_first_hour', 'Первый час', 'Провести в игре один час.', 'general', 'common', 10, [['playtime_seconds', 3600]]),
            $this->a('general_ten_hours', 'Освоился', 'Провести в игре 10 часов.', 'general', 'uncommon', 25, [['playtime_seconds', 36000]]),
            $this->a('general_fifty_hours', 'Постоянный житель', 'Провести в игре 50 часов.', 'general', 'rare', 75, [['playtime_seconds', 180000]]),
            $this->a('general_hundred_hours', 'Здесь мой дом', 'Провести в игре 100 часов.', 'general', 'epic', 200, [['playtime_seconds', 360000]]),
            $this->a('general_five_hundred_hours', 'Вторая жизнь', 'Провести в игре 500 часов.', 'general', 'legendary', 500, [['playtime_seconds', 1800000]]),

            $this->a('mining_break_100', 'Кирка в дело', 'Сломать 100 блоков.', 'mining', 'common', 10, [['blocks.break.total', 100]]),
            $this->a('mining_break_1000', 'Шахтёрская смена', 'Сломать 1 000 блоков.', 'mining', 'uncommon', 25, [['blocks.break.total', 1000]]),
            $this->a('mining_break_10000', 'Гора стала меньше', 'Сломать 10 000 блоков.', 'mining', 'rare', 75, [['blocks.break.total', 10000]]),
            $this->a('mining_break_100000', 'Промышленная добыча', 'Сломать 100 000 блоков.', 'mining', 'epic', 200, [['blocks.break.total', 100000]]),

            $this->a('building_place_100', 'Первые стены', 'Установить 100 блоков.', 'building', 'common', 10, [['blocks.place.total', 100]]),
            $this->a('building_place_1000', 'Строитель', 'Установить 1 000 блоков.', 'building', 'uncommon', 25, [['blocks.place.total', 1000]]),
            $this->a('building_place_10000', 'Архитектор', 'Установить 10 000 блоков.', 'building', 'rare', 75, [['blocks.place.total', 10000]]),
            $this->a('building_place_50000', 'Мегастройка', 'Установить 50 000 блоков.', 'building', 'epic', 200, [['blocks.place.total', 50000]]),

            $this->a('general_craft_100', 'Руки помнят', 'Создать 100 предметов.', 'general', 'common', 10, [['item.craft.total', 100]]),
            $this->a('general_craft_1000', 'Серийное производство', 'Создать 1 000 предметов.', 'general', 'uncommon', 25, [['item.craft.total', 1000]]),
            $this->a('general_craft_10000', 'Верстак не остывает', 'Создать 10 000 предметов.', 'general', 'rare', 75, [['item.craft.total', 10000]]),
            $this->a('general_smelt_100', 'Горячий цех', 'Переплавить 100 предметов.', 'general', 'common', 10, [['item.smelt.total', 100]]),
            $this->a('general_smelt_1000', 'Металлург', 'Переплавить 1 000 предметов.', 'general', 'uncommon', 25, [['item.smelt.total', 1000]]),

            $this->a('combat_kill_10', 'Самооборона', 'Убить 10 существ.', 'combat', 'common', 10, [['entity.kill.total', 10]]),
            $this->a('combat_kill_100', 'Охотник', 'Убить 100 существ.', 'combat', 'uncommon', 25, [['entity.kill.total', 100]]),
            $this->a('combat_kill_1000', 'Истребитель', 'Убить 1 000 существ.', 'combat', 'rare', 75, [['entity.kill.total', 1000]]),
            $this->a('combat_first_death', 'Это было неизбежно', 'Погибнуть первый раз.', 'combat', 'common', 10, [['player.death.total', 1]]),
            $this->a('combat_pvp_first', 'Первый фраг', 'Победить другого игрока.', 'combat', 'uncommon', 25, [['player.kill.total', 1]]),
            $this->a('combat_pvp_25', 'Опасный сосед', 'Победить игроков 25 раз.', 'combat', 'rare', 75, [['player.kill.total', 25]]),
            $this->a('general_advancements_25', 'По плану', 'Получить 25 игровых advancement.', 'general', 'uncommon', 25, [['advancement.complete.total', 25]]),

            $this->a('exploration_dimensions_2', 'Портал открыт', 'Посетить 2 разных измерения.', 'exploration', 'common', 10, [['dimensions.visited', 2]]),
            $this->a('exploration_dimensions_3', 'Между мирами', 'Посетить 3 разных измерения.', 'exploration', 'uncommon', 25, [['dimensions.visited', 3]]),
            $this->a('exploration_dimensions_5', 'Путешественник', 'Посетить 5 разных измерений.', 'exploration', 'rare', 75, [['dimensions.visited', 5]]),
            $this->a('exploration_dimensions_10', 'Картограф измерений', 'Посетить 10 разных измерений.', 'exploration', 'epic', 200, [['dimensions.visited', 10]]),

            $this->a('ae2_first_craft', 'Кварцевый старт', 'Создать первый предмет Applied Energistics 2.', 'ae2', 'common', 10, [['ae2.item.craft.total', 1]]),
            $this->a('ae2_controller_craft', 'Теперь у меня есть система', 'Создать ME Controller.', 'ae2', 'uncommon', 25, [['ae2.controller.craft.total', 1]]),
            $this->a('ae2_controller_place', 'Сеть включена', 'Установить ME Controller.', 'ae2', 'uncommon', 25, [['ae2.controller.place.total', 1]]),
            $this->a('ae2_storage_cell_first', 'Всё в одной ячейке', 'Создать первую ME storage cell.', 'ae2', 'common', 10, [['ae2.storage_cell.craft.total', 1]]),
            $this->a('ae2_storage_cell_10', 'Цифровой склад', 'Создать 10 ME storage cell.', 'ae2', 'rare', 75, [['ae2.storage_cell.craft.total', 10]]),
            $this->a('ae2_machine_10', 'ME-цех', 'Установить 10 машин AE2.', 'ae2', 'uncommon', 25, [['ae2.machine.place.total', 10]]),
            $this->a('ae2_machine_50', 'Системный архитектор', 'Установить 50 машин AE2.', 'ae2', 'epic', 200, [['ae2.machine.place.total', 50]]),
            $this->a('ae2_network_50', 'Первые каналы', 'Установить 50 компонентов ME-сети.', 'ae2', 'uncommon', 25, [['ae2.network_component.place.total', 50]]),
            $this->a('ae2_network_500', 'Кабельный кошмар', 'Установить 500 компонентов ME-сети.', 'ae2', 'epic', 200, [['ae2.network_component.place.total', 500]]),
            $this->a('ae2_terminal_first', 'Доступ к системе', 'Создать терминал AE2.', 'ae2', 'uncommon', 25, [['ae2.terminal.craft.total', 1]]),
            $this->a('ae2_crafting_storage_first', 'Подготовка к автоматизации', 'Создать crafting storage AE2.', 'ae2', 'rare', 75, [['ae2.crafting_storage.craft.total', 1]]),
            $this->a('ae2_molecular_assembler_first', 'Молекулярная сборка', 'Установить Molecular Assembler.', 'ae2', 'rare', 75, [['ae2.molecular_assembler.place.total', 1]]),

            $this->a('ic2_machine_first', 'Индустриализация', 'Установить первую машину IC2.', 'ic2', 'common', 10, [['ic2.machine.place.total', 1]]),
            $this->a('ic2_machine_10', 'Машинный зал', 'Установить 10 машин IC2.', 'ic2', 'uncommon', 25, [['ic2.machine.place.total', 10]]),
            $this->a('ic2_machine_50', 'Завод', 'Установить 50 машин IC2.', 'ic2', 'epic', 200, [['ic2.machine.place.total', 50]]),
            $this->a('ic2_generator_first', 'Собственная генерация', 'Установить первый генератор IC2.', 'ic2', 'common', 10, [['ic2.generator.place.total', 1]]),
            $this->a('ic2_generator_10', 'Энергокомпания', 'Установить 10 генераторов IC2.', 'ic2', 'rare', 75, [['ic2.generator.place.total', 10]]),
            $this->a('ic2_energy_tier_1', 'Низкое напряжение', 'Установить хранилище энергии IC2 первого уровня.', 'ic2', 'common', 10, [['ic2.energy.tier_reached.tier:1', 1]]),
            $this->a('ic2_energy_tier_2', 'Среднее напряжение', 'Достигнуть второго уровня хранения энергии IC2.', 'ic2', 'uncommon', 25, [['ic2.energy.tier_reached.tier:2', 1]]),
            $this->a('ic2_energy_tier_3', 'Высокое напряжение', 'Достигнуть третьего уровня хранения энергии IC2.', 'ic2', 'rare', 75, [['ic2.energy.tier_reached.tier:3', 1]]),
            $this->a('ic2_energy_tier_4', 'MFSU', 'Достигнуть четвёртого уровня хранения энергии IC2.', 'ic2', 'epic', 200, [['ic2.energy.tier_reached.tier:4', 1]]),
            $this->a('ic2_wrench_25', 'Гаечный ключ', 'Использовать IC2 wrench 25 раз.', 'ic2', 'uncommon', 25, [['ic2.wrench.use.total', 25]]),
            $this->a('ic2_wrench_250', 'Сервисная служба', 'Использовать IC2 wrench 250 раз.', 'ic2', 'rare', 75, [['ic2.wrench.use.total', 250]]),

            $this->a('forestry_apiary_first', 'Первая пасека', 'Установить первый Apiary.', 'forestry', 'common', 10, [['forestry.apiary.place.total', 1]]),
            $this->a('forestry_apiary_10', 'Пчеловод', 'Установить 10 Apiary.', 'forestry', 'uncommon', 25, [['forestry.apiary.place.total', 10]]),
            $this->a('forestry_alveary_first', 'Большая пасека', 'Установить первый Alveary.', 'forestry', 'rare', 75, [['forestry.alveary.place.total', 1]]),
            $this->a('forestry_species_1', 'Первая порода', 'Обнаружить первый вид пчёл.', 'forestry', 'common', 10, [['forestry.bee.discovered.unique', 1]]),
            $this->a('forestry_species_10', 'Коллекционер', 'Обнаружить 10 видов пчёл.', 'forestry', 'uncommon', 25, [['forestry.bee.discovered.unique', 10]]),
            $this->a('forestry_species_25', 'Селекционер', 'Обнаружить 25 видов пчёл.', 'forestry', 'rare', 75, [['forestry.bee.discovered.unique', 25]]),
            $this->a('forestry_species_50', 'Генетический архив', 'Обнаружить 50 видов пчёл.', 'forestry', 'epic', 200, [['forestry.bee.discovered.unique', 50]]),
            $this->a('forestry_species_100', 'Энциклопедия пчёл', 'Обнаружить 100 видов пчёл.', 'forestry', 'legendary', 500, [['forestry.bee.discovered.unique', 100]]),
            $this->a('forestry_machine_10', 'Генетическая лаборатория', 'Установить 10 машин Forestry/Gendustry/Binnie.', 'forestry', 'uncommon', 25, [['forestry.machine.place.total', 10]]),
            $this->a('forestry_genetics_tool_25', 'Полевой генетик', 'Использовать генетические инструменты 25 раз.', 'forestry', 'rare', 75, [['forestry.genetics_tool.use.total', 25]]),
            $this->a('forestry_bee_acquire_100', 'Рой', 'Получить 100 пчёл через telemetry-события.', 'forestry', 'rare', 75, [['forestry.bee.acquire.total', 100]]),

            $this->a('thermal_machine_first', 'Тепловой старт', 'Установить первую машину Thermal Expansion.', 'thermal', 'common', 10, [['thermal.machine.place.total', 1]]),
            $this->a('thermal_machine_10', 'Красный цех', 'Установить 10 машин Thermal.', 'thermal', 'uncommon', 25, [['thermal.machine.place.total', 10]]),
            $this->a('thermal_machine_50', 'Тепловая промышленность', 'Установить 50 машин Thermal.', 'thermal', 'epic', 200, [['thermal.machine.place.total', 50]]),
            $this->a('thermal_dynamo_first', 'Первый динамо', 'Установить первый Dynamo.', 'thermal', 'common', 10, [['thermal.dynamo.place.total', 1]]),
            $this->a('thermal_dynamo_10', 'Генераторная', 'Установить 10 Dynamo.', 'thermal', 'rare', 75, [['thermal.dynamo.place.total', 10]]),
            $this->a('thermal_augment_first', 'Тонкая настройка', 'Создать первый Thermal augment.', 'thermal', 'common', 10, [['thermal.augment.craft.total', 1]]),
            $this->a('thermal_augment_25', 'Специалист по улучшениям', 'Создать 25 Thermal augment.', 'thermal', 'rare', 75, [['thermal.augment.craft.total', 25]]),
            $this->a('thermal_upgrade_10', 'Повышение класса', 'Создать 10 Thermal upgrade kit.', 'thermal', 'uncommon', 25, [['thermal.upgrade.craft.total', 10]]),
            $this->a('thermal_storage_25', 'Складской комплекс', 'Установить 25 хранилищ Thermal.', 'thermal', 'rare', 75, [['thermal.storage.place.total', 25]]),

            $this->a('ie_machine_first', 'Тяжёлая инженерия', 'Установить первую машину Immersive Engineering.', 'immersiveengineering', 'common', 10, [['immersiveengineering.machine.place.total', 1]]),
            $this->a('ie_machine_10', 'Промышленный район', 'Установить 10 машин Immersive Engineering.', 'immersiveengineering', 'rare', 75, [['immersiveengineering.machine.place.total', 10]]),
            $this->a('ie_capacitor_first', 'Буфер мощности', 'Установить первый capacitor Immersive Engineering.', 'immersiveengineering', 'common', 10, [['immersiveengineering.capacitor.place.total', 1]]),
            $this->a('ie_capacitor_20', 'Энергобанк', 'Установить 20 capacitor.', 'immersiveengineering', 'rare', 75, [['immersiveengineering.capacitor.place.total', 20]]),
            $this->a('ie_connector_10', 'Провода пошли', 'Установить 10 connector/relay.', 'immersiveengineering', 'uncommon', 25, [['immersiveengineering.connector.place.total', 10]]),
            $this->a('ie_connector_100', 'Подстанция', 'Установить 100 connector/relay.', 'immersiveengineering', 'epic', 200, [['immersiveengineering.connector.place.total', 100]]),
            $this->a('ie_multiblock_components_50', 'Большая машина начинается с блока', 'Установить 50 компонентов IE multiblock.', 'immersiveengineering', 'rare', 75, [['immersiveengineering.multiblock_component.place.total', 50]]),
            $this->a('ie_hammer_50', 'Инженерный молот', 'Использовать Engineer\'s Hammer 50 раз.', 'immersiveengineering', 'uncommon', 25, [['immersiveengineering.hammer.use.total', 50]]),
            $this->a('ie_hammer_500', 'Молот знает лучше', 'Использовать Engineer\'s Hammer 500 раз.', 'immersiveengineering', 'epic', 200, [['immersiveengineering.hammer.use.total', 500]]),

            $this->a('if_machine_first', 'Автоматизатор', 'Установить первую машину Industrial Foregoing.', 'industrialforegoing', 'common', 10, [['industrialforegoing.machine.place.total', 1]]),
            $this->a('if_machine_10', 'Конвейер без конвейера', 'Установить 10 машин Industrial Foregoing.', 'industrialforegoing', 'uncommon', 25, [['industrialforegoing.machine.place.total', 10]]),
            $this->a('if_machine_50', 'Автоматизированный комплекс', 'Установить 50 машин Industrial Foregoing.', 'industrialforegoing', 'epic', 200, [['industrialforegoing.machine.place.total', 50]]),
            $this->a('if_addon_first', 'Первое улучшение', 'Создать первый addon Industrial Foregoing.', 'industrialforegoing', 'common', 10, [['industrialforegoing.addon.craft.total', 1]]),
            $this->a('if_addon_10', 'Тюнинг производства', 'Создать 10 addon Industrial Foregoing.', 'industrialforegoing', 'uncommon', 25, [['industrialforegoing.addon.craft.total', 10]]),
            $this->a('if_addon_use_25', 'Настройщик автоматизации', 'Использовать addon Industrial Foregoing 25 раз.', 'industrialforegoing', 'rare', 75, [['industrialforegoing.addon.use.total', 25]]),
            $this->a('if_craft_100', 'Пластик и механизмы', 'Создать 100 предметов Industrial Foregoing.', 'industrialforegoing', 'rare', 75, [['industrialforegoing.item.craft.total', 100]]),

            $this->a(
                'engineering_six_technologies',
                'Шесть школ инженерии',
                'Установить хотя бы по одной машине AE2, IC2, Forestry, Thermal, Immersive Engineering и Industrial Foregoing.',
                'engineering',
                'legendary',
                500,
                [
                    ['ae2.machine.place.total', 1],
                    ['ic2.machine.place.total', 1],
                    ['forestry.machine.place.total', 1],
                    ['thermal.machine.place.total', 1],
                    ['immersiveengineering.machine.place.total', 1],
                    ['industrialforegoing.machine.place.total', 1],
                ]
            ),
            $this->a(
                'engineering_six_technologies_10',
                'Универсальный инженер',
                'Установить не менее 10 машин каждой из шести технологических веток.',
                'engineering',
                'legendary',
                500,
                [
                    ['ae2.machine.place.total', 10],
                    ['ic2.machine.place.total', 10],
                    ['forestry.machine.place.total', 10],
                    ['thermal.machine.place.total', 10],
                    ['immersiveengineering.machine.place.total', 10],
                    ['industrialforegoing.machine.place.total', 10],
                ]
            ),
            $this->a(
                'engineering_toolkit',
                'Инженер на все руки',
                'Освоить инструменты и улучшения всех технологических веток.',
                'engineering',
                'legendary',
                500,
                [
                    ['ae2.network_tool.use.total', 1],
                    ['ic2.wrench.use.total', 25],
                    ['forestry.genetics_tool.use.total', 1],
                    ['thermal.augment.craft.total', 1],
                    ['immersiveengineering.hammer.use.total', 25],
                    ['industrialforegoing.addon.use.total', 1],
                ],
                true,
                'Нужны инструменты и улучшения из всех основных технологических модов.'
            ),
        ];
    }

    private function a(
        string $code,
        string $title,
        string $description,
        string $category,
        string $rarity,
        int $score,
        array $conditions,
        bool $hidden = false,
        ?string $hint = null
    ): array {
        return [
            'code' => $code,
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'rarity' => $rarity,
            'score' => $score,
            'conditions' => $conditions,
            'hidden' => $hidden,
            'hint' => $hint,
        ];
    }
}

<?php

declare(strict_types=1);

use common\models\Achievement;
use common\services\AchievementIconService;
use yii\db\Migration;

final class m261013_000001_assign_achievement_icons extends Migration
{
    private const CODES = [
        'general_first_hour',
        'general_ten_hours',
        'general_fifty_hours',
        'general_hundred_hours',
        'general_five_hundred_hours',
        'mining_break_100',
        'mining_break_1000',
        'mining_break_10000',
        'mining_break_100000',
        'building_place_100',
        'building_place_1000',
        'building_place_10000',
        'building_place_50000',
        'general_craft_100',
        'general_craft_1000',
        'general_craft_10000',
        'general_smelt_100',
        'general_smelt_1000',
        'combat_kill_10',
        'combat_kill_100',
        'combat_kill_1000',
        'combat_first_death',
        'combat_pvp_first',
        'combat_pvp_25',
        'general_advancements_25',
        'exploration_dimensions_2',
        'exploration_dimensions_3',
        'exploration_dimensions_5',
        'exploration_dimensions_10',
        'ae2_first_craft',
        'ae2_controller_craft',
        'ae2_controller_place',
        'ae2_storage_cell_first',
        'ae2_storage_cell_10',
        'ae2_machine_10',
        'ae2_machine_50',
        'ae2_network_50',
        'ae2_network_500',
        'ae2_terminal_first',
        'ae2_crafting_storage_first',
        'ae2_molecular_assembler_first',
        'ic2_machine_first',
        'ic2_machine_10',
        'ic2_machine_50',
        'ic2_generator_first',
        'ic2_generator_10',
        'ic2_energy_tier_1',
        'ic2_energy_tier_2',
        'ic2_energy_tier_3',
        'ic2_energy_tier_4',
        'ic2_wrench_25',
        'ic2_wrench_250',
        'forestry_apiary_first',
        'forestry_apiary_10',
        'forestry_alveary_first',
        'forestry_species_1',
        'forestry_species_10',
        'forestry_species_25',
        'forestry_species_50',
        'forestry_species_100',
        'forestry_machine_10',
        'forestry_genetics_tool_25',
        'forestry_bee_acquire_100',
        'thermal_machine_first',
        'thermal_machine_10',
        'thermal_machine_50',
        'thermal_dynamo_first',
        'thermal_dynamo_10',
        'thermal_augment_first',
        'thermal_augment_25',
        'thermal_upgrade_10',
        'thermal_storage_25',
        'ie_machine_first',
        'ie_machine_10',
        'ie_capacitor_first',
        'ie_capacitor_20',
        'ie_connector_10',
        'ie_connector_100',
        'ie_multiblock_components_50',
        'ie_hammer_50',
        'ie_hammer_500',
        'if_machine_first',
        'if_machine_10',
        'if_machine_50',
        'if_addon_first',
        'if_addon_10',
        'if_addon_use_25',
        'if_craft_100',
        'engineering_six_technologies',
        'engineering_six_technologies_10',
        'engineering_toolkit',
    ];

    public function safeUp(): void
    {
        $achievements = Achievement::find()
            ->where(['code' => self::CODES])
            ->all();

        foreach($achievements as $achievement)
        {
            if(trim((string)$achievement->icon) !== '')
            {
                continue;
            }

            $achievement->icon =
                AchievementIconService::infer(
                    (string)$achievement->code
                );

            $achievement->updated_at =
                date('Y-m-d H:i:s');

            $achievement->save(
                false,
                ['icon', 'updated_at']
            );
        }
    }

    public function safeDown(): void
    {
        Achievement::updateAll(
            ['icon' => null],
            ['code' => self::CODES]
        );
    }
}

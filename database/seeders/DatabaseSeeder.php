<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            SkillsSeeder::class,
            CitySeeder::class,
            SettingSeeder::class,
            MessageTemplateSeeder::class,
            AdminSeeder::class,
        ]);
    }
}

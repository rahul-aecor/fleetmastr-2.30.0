<?php

use Illuminate\Database\Seeder;

class SettingsAccidentModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        setting([            
            'accident_insurance_detail' => '{"insurance_company":null,"telephone_number":null,"policy_number":null,"policy_name":null,"insurance_certificate_attachment":null}'
        ])->save();
    }
}

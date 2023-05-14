<?php

use Illuminate\Database\Seeder;

class MaintenanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $maintenanceHistoryEventTypes = [
            'annual_service_inspection' => 'Annual service',
            'compressor_inspection' => 'Compressor service',
            'invertor_inspection' => 'Invertor service',
            'loler_test' => 'LOLER test',
            'mot' => 'MOT',
            'maintenance_expiry' => 'Maintenance Expiry', // Used only for planner filter
            'next_service_inspection' => 'Service (time)',
            'next_service_inspection_distance' => 'Service (distance)',
            'preventative_maintenance_inspection' => 'PMI',
            'pto_service_inspection' => 'PTO service',
            'tachograph_calibration' => 'Tacho calibration',
            'vehicle_tax' => 'Tax',
        ];

        if (count($maintenanceHistoryEventTypes) > 0) {
            foreach ($maintenanceHistoryEventTypes as $slug => $type) {
                \App\Models\MaintenanceEvents::create([
                    'name' => $type,
                    'slug' => $slug,
                    'is_standard_event' => $slug == 'maintenance_expiry' ? 2 : 1
                ]);
            }
        }
    }
}

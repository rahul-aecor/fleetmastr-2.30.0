<?php

use Illuminate\Database\Seeder;

class CreateReportDataSetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('report_dataset')->truncate();
        DB::table('report_dataset')->insert(
        [
            [
                'field_name' => 'users.first_name',
                'title' => 'First Name',
                'description' => 'Display the user\'s first name',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.last_name',
                'title' => 'Last Name',
                'description' => 'Display the user\'s last name',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.company_id',
                'title' => 'Company',
                'description' => 'Display company associated with the user',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.email',
                'title' => 'Email',
                'description' => 'Display the user\'s email address',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.job_title',
                'title' => 'Job Title',
                'description' => 'Display the user\'s job title',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.mobile',
                'title' => 'Mobile',
                'description' => 'Display the user\'s mobile number',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.user_division_id',
                'title' => 'User Division',
                'description' => 'Display the user\'s division',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.user_region_id',
                'title' => 'User Region',
                'description' => 'Display the user\'s region',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.engineer_id',
                'title' => 'Engineer ID',
                'description' => 'Display the user\'s engineer ID',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.created_at',
                'title' => 'Created Date',
                'description' => 'Display the user\'s registration date',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.landline',
                'title' => 'Landline',
                'description' => 'Display the user\'s landline number',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'dallas_key',
                'title' => 'Dallas Key',
                'description' => 'Display the user\'s dallas key',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.imei',
                'title' => 'IMEI Number',
                'description' => 'Display the user\'s IMEI number',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.base_location',
                'title' => 'Base location',
                'description' => 'Display the user\'s base location',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.id',
                'title' => 'Vehicle ID',
                'description' => 'Display the ID',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.vehicle_type_id',
                'title' => 'Type',
                'description' => 'Display the vehicle type',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_types.vehicle_category',
                'title' => 'Category',
                'description' => 'Display the vehicle category',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_types.vehicle_subcategory',
                'title' => 'Sub Category',
                'description' => 'Display the vehicle sub category',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_types.manufacturer',
                'title' => 'Manufacturer',
                'description' => 'Display the vehicle manufacturer',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_types.model',
                'title' => 'Model',
                'description' => 'Display the vehicle model',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_types.fuel_type',
                'title' => 'Fuel Type',
                'description' => 'Display the fuel type',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_types.engine_type',
                'title' => 'Type Of Engine',
                'description' => 'Display the engine type',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.registration',
                'title' => 'Registration',
                'description' => 'Display the registration number',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.vehicle_division_id',
                'title' => 'Vehicle Division',
                'description' => 'Display the division',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.vehicle_region_id',
                'title' => 'Vehicle Region',
                'description' => 'Display the region',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.chassis_number',
                'title' => 'Chassis Number',
                'description' => 'Display the chassis number',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.contract_id',
                'title' => 'Contract ID',
                'description' => 'Display the contract ID',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'total_journeys',
                'title' => 'Total Journey',
                'description' => 'Display the total journey',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_start_date',
                'title' => 'Journey Start Date',
                'description' => 'Display the jounry start date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_start_time',
                'title' => 'Journey Start Time',
                'description' => 'Display the jounry start time',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journeys.end_time',
                'title' => 'Journey End Date',
                'description' => 'Display the jounry end date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_end_time',
                'title' => 'Journey End Time',
                'description' => 'Display the jounry end date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journeys.engine_duration',
                'title' => 'Journey Duration(HH:MM:SS)',
                'description' => 'Display the journey duration',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journeys.gps_distance',
                'title' => 'Journey Distance(Miles)',
                'description' => 'Display the journey distance',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journeys.co2',
                'title' => 'Journey CO2',
                'description' => 'Display the journey co2',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'incident_time',
                'title' => 'Incident Date',
                'description' => 'Display the incident date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'total_incidents',
                'title' => 'Total Incidents',
                'description' => 'Display the total incidents',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.acceleration_score',
                'title' => 'Acceleration',
                'description' => 'Display the acceleration score',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.braking_score',
                'title' => 'Braking',
                'description' => 'Display the braking score',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.cornering_score',
                'title' => 'Cornering',
                'description' => 'Display the cornering score',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.speeding_score',
                'title' => 'Speeding',
                'description' => 'Display the speeding score',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.rpm_score',
                'title' => 'RPM',
                'description' => 'Display the RPM score',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.idle_score',
                'title' => 'Idle',
                'description' => 'Display the idle score',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'field_name' => 'telematics_journeys.gps_idle_duration',
                'title' => 'Idle Time(HH:MM:SS)',
                'description' => 'Display the idle time',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'field_name' => 'vehicle_overall_score',
                'title' => 'Vehicle Overall Score',
                'description' => 'Display the vehicle overall score',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_safety_score',
                'title' => 'Vehicle Safety Score',
                'description' => 'Display the vehicle safety score',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_efficiency_score',
                'title' => 'Vehicle Efficiency Score',
                'description' => 'Display the vehicle efficiency score',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defects.report_datetime',
                'title' => 'Defect Date',
                'description' => 'Display the defect date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defect_type',
                'title' => 'Type Of Defects',
                'description' => 'Display the type of defects',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'checks.report_datetime',
                'title' => 'Last Check Date',
                'description' => 'Display the last check date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'checks.check_duration',
                'title' => 'Check Duration',
                'description' => 'Display the check duration',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'checks.type',
                'title' => 'Check Type',
                'description' => 'Display the check type',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.dt_annual_service_inspection',
                'title' => 'Annual Service',
                'description' => 'Display the annual service',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.next_compressor_service',
                'title' => 'Compressor Service',
                'description' => 'Display the compressor service',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.next_invertor_service_date',
                'title' => 'Invertor Service',
                'description' => 'Display the invertor service',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.dt_loler_test_due',
                'title' => 'LOLER Test',
                'description' => 'Display the LOLER test',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.maintenance_cost',
                'title' => 'Management Cost',
                'description' => 'Display the management cost',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.dt_mot_expiry',
                'title' => 'MOT',
                'description' => 'Display the MOT date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.first_pmi_date',
                'title' => 'First PMI',
                'description' => 'Display the first PMI date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.next_pmi_date',
                'title' => 'Next PMI',
                'description' => 'Display the next PMI date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.next_pto_service_date',
                'title' => 'PTO Service',
                'description' => 'Display the PTO service date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.dt_next_service_inspection',
                'title' => 'Service',
                'description' => 'Display the service inspection date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.dt_tacograch_calibration_due',
                'title' => 'Tacho Service',
                'description' => 'Display the tacho service date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.dt_tax_expiry',
                'title' => 'Tax',
                'description' => 'Display the tax expiry',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defect_master.page_title',
                'title' => 'Defect Category',
                'description' => 'Display the defect category',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defect_master.defect',
                'title' => 'Defect Name',
                'description' => 'Display the defect name',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'driver_overall_score',
                'title' => 'Overall Score',
                'description' => 'Display the driver overall score',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'driver_safety_score',
                'title' => 'Safety Score',
                'description' => 'Display the driver safety score',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'driver_efficiency_score',
                'title' => 'Efficiency Score',
                'description' => 'Display the driver efficiency score',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.vehicle_location_id',
                'title' => 'Vehicle Location',
                'description' => 'Display the location',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'manufacturer_type',
                'title' => 'Maintenance Event',
                'description' => 'Display the maintenance event',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'event_due_date',
                'title' => 'Due Date',
                'description' => 'Display the event due date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.repair_location_name',
                'title' => 'Repair/Maintenance Location',
                'description' => 'Display the vehicle repair location name',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'checks.status',
                'title' => 'Check Result',
                'description' => 'Display the check result',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.ns',
                'title' => 'Incident Type',
                'description' => 'Display the incident type',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.username',
                'title' => 'Username',
                'description' => 'Display the user username',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.is_disabled',
                'title' => 'Is Archived?',
                'description' => 'Display the user last archive',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.operator_license',
                'title' => 'Operator License',
                'description' => 'Display the vehicle operator license',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.staus_owned_leased',
                'title' => 'Ownership Status',
                'description' => 'Display the vehicle ownership staus',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.status',
                'title' => 'Vehicle Status',
                'description' => 'Display the vehicle status',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.dt_added_to_fleet',
                'title' => 'Date Added To Fleet',
                'description' => 'Display the vehicle date added to fleet',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'location_from',
                'title' => 'Location From',
                'description' => 'Display the vehicle location from',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'location_to',
                'title' => 'Location To',
                'description' => 'Display the vehicle location to',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.lease_cost',
                'title' => 'Hire Cost',
                'description' => 'Display the vehicle hire cost',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.monthly_depreciation_cost',
                'title' => 'Depreciation Cost',
                'description' => 'Display the vehicle depreciation cost',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_tax',
                'title' => 'Vehicle Tax',
                'description' => 'Display the vehicle tax',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'insurance_cost',
                'title' => 'Insurance Cost',
                'description' => 'Display the vehicle insurance cost',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_cost',
                'title' => 'Telematics Cost',
                'description' => 'Display the vehicle telematics cost',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'manual_cost_adj',
                'title' => 'Manual Cost Adj',
                'description' => 'Display the vehicle manual cost adj',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'oil',
                'title' => 'Oil',
                'description' => 'Display the vehicle oil',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'adBlue',
                'title' => 'AdBlue',
                'description' => 'Display the vehicle adBlue',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'screen_wash',
                'title' => 'Screen Wash',
                'description' => 'Display the vehicle screen wash',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'fleet_livery',
                'title' => 'Fleet Livery',
                'description' => 'Display the vehicle fleet livery',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defects',
                'title' => 'Defects',
                'description' => 'Display the vehicle defects',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'total',
                'title' => 'Total',
                'description' => 'Display the vehicle total',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'transfer',
                'title' => 'Transfer',
                'description' => 'Display the vehicle transfer',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_take_out',
                'title' => 'Vehicle Take Out',
                'description' => 'Display the vehicle take out',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicle_return',
                'title' => 'Vehicle Return',
                'description' => 'Display the vehicle return',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'hgv_non_hgv',
                'title' => 'HGV/Non-HGV',
                'description' => 'Display the vehicle profile category',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'dated_vor_d',
                'title' => "Dated VOR'd",
                'description' => 'Display the vehicle dated vor\d',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vor_duration_days',
                'title' => 'VOR Duration(days)',
                'description' => 'Display the vehicle VOR duration(days)',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defect_master.page_title',
                'title' => 'Defect Category',
                'description' => 'Display the vehicle defect category',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defect_master.defect',
                'title' => 'Defect',
                'description' => 'Display the vehicle defect',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defects.id',
                'title' => 'Defect Number',
                'description' => 'Display the vehicle defect number',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defects.est_completion_date',
                'title' => 'Estimated Completion Date',
                'description' => 'Display the vehicle estimated completion date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'last_comment_date',
                'title' => 'Last Comment Date',
                'description' => 'Display the vehicle last_comment_date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'last_comment',
                'title' => 'Last Comment',
                'description' => 'Display the vehicle last comment',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'vehicles.last_odometer_reading',
                'title' => 'Odometer',
                'description' => 'Display the vehicle odometer',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.time',
                'title' => 'Date',
                'description' => 'Display the date (Y-m-d)',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.time',
                'title' => 'Time',
                'description' => 'Display the time (H:i)',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.ns',
                'title' => 'Incident',
                'description' => 'Display the incident',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'street',
                'title' => 'Location',
                'description' => 'Display the location',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.speed',
                'title' => 'Vehicle Speed(MPH)',
                'description' => 'Display the speed(MPH)',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journey_details.street_speed',
                'title' => 'Speed Limit(MPH)',
                'description' => 'Display the speed limit(MPH)',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'start_location',
                'title' => 'Start Location',
                'description' => 'Display the start location',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'end_location',
                'title' => 'End Location',
                'description' => 'Display the end location',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journeys.incident_count',
                'title' => 'Number of Incidents',
                'description' => 'Display the number of incidents',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'mpg_actual',
                'title' => 'MPG(Actual)',
                'description' => 'Display the mpg (actual)',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'mpg_expected',
                'title' => 'MPG(Expected)',
                'description' => 'Display the mpg (expected)',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'actual_driving_time',
                'title' => 'Actual Driving Time(HH:MM:SS)',
                'description' => 'Display the actual driving time',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'idling_time',
                'title' => 'Idling Time(HH:MM:SS)',
                'description' => 'Display the idling time',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'telematics_journeys.fuel',
                'title' => 'Fuel Consumption(in litre)',
                'description' => 'Display the fuel',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'roles',
                'title' => 'Roles',
                'description' => 'Display the roles',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'users.last_login',
                'title' => 'Last Login',
                'description' => 'Display the last login',
                'model_type' => 'App\Models\User',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'defect_status',
                'title' => 'Defect Status',
                'description' => 'Display the vehicle defect status',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'service_type',
                'title' => 'Service Type',
                'description' => 'Display the type of service',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'service_date',
                'title' => 'Service Date',
                'description' => 'Display the date of service',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'nominated_driver',
                'title' => 'Nominated Driver',
                'description' => 'Display the nominated driver',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'journey_start_location',
                'title' => 'Journey Start Location',
                'description' => 'Display the journey start location',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'pmi_planned_date',
                'title' => 'PMI Planned Date',
                'description' => 'Display the PMI planned date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'journey_start_map_link',
                'title' => 'Journey Start Map Link',
                'description' => 'Display the journey start map link',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'journey_end_location',
                'title' => 'Journey End Location',
                'description' => 'Display the journey end location',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'journey_end_map_link',
                'title' => 'Journey End Map Link',
                'description' => 'Display the journey end map link',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'pmi_actual_date',
                'title' => 'PMI Actual Date',
                'description' => 'Display the PMI actual date',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'field_name' => 'event_status',
                'title' => 'Event Status',
                'description' => 'Display the maintenance event status',
                'model_type' => 'App\Models\Vehicle',
                'created_at' => date('Y-m-d H:i:s')
            ],

        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

    }
}

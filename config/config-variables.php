<?php

return [

    'displayTimezone' => 'Europe/London',

    'displayTimeFormat' => 'H:i:s d M Y',
    'apiTimeFormat' => 'H:i j M Y',

    'vehicleCategories' => [
        '' => '',
        'hgv' => 'HGV',
        'non-hgv' => 'Non-HGV'
    ],
    'engineSizeMandatoryFlag' => env('ENGINE_SIZE_MANDATORY') == 1 ?'on':'off',

    'fuelTypeList' => ['' => '','Diesel' => 'Diesel', 'EV'=>'EV', 'Hybrid/Diesel'=>'Hybrid/Diesel', 'Hybrid/Petrol'=>'Hybrid/Petrol', 'Hybrid/Petrol PHEV'=>'Hybrid/Petrol PHEV', 'Unleaded petrol' => 'Unleaded petrol'
    ],

    'engineTypeList' => ['' => '', 'Petrol' => 'Petrol', 'Hybrid petrol/EV' => 'Hybrid petrol/EV', 'PHEV petrol/EV' => 'PHEV petrol/EV', 'Hybrid diesel/EV' => 'Hybrid diesel/EV', 'Euro V diesel'=>'Euro V diesel','Euro VI diesel (Adblue)' => 'Euro VI diesel (Adblue)', 'EV' => 'EV'
    ],

/*
    'fuelTypeList' => ['' => 'Select','Diesel' => 'Diesel', 'EV'=>'EV', 'Hybrid/Diesel'=>'Hybrid/Diesel', 'Hybrid/Petrol'=>'Hybrid/Petrol', 'Hybrid/Petrol PHEV'=>'Hybrid/Petrol PHEV', 'Unleaded petrol' => 'Unleaded petrol'
    ],

    'engineTypeList' => ['' => 'Select', 'Electric' => 'Electric', 'Diesel Electric' => 'Diesel Electric', 'Petrol Electric' => 'Petrol Electric', 'Petrol' => 'Petrol'
    ],
*/
    'vehicleSubCategoriesNonHGV' => [
        '' => '',
        'car' => 'Car',
        'none' => 'None',
        'van' => 'Van'
    ],

    'usageType' => [
        '' => '',
        'Commercial' => 'Commercial',
        'Non-commercial' => 'Non-commercial'
    ],
    'vehicleStatus' => [
        '' => '',
        // 'Archived' => 'Archived',
        // 'Archived - De-commissioned' => 'Archived - De-commissioned',
        // 'Archived - Written off' => 'Archived - Written off',
        'Awaiting kit' => 'Awaiting kit',
        'Re-positioning' => 'Re-positioning',
        'Roadworthy' => 'Roadworthy',
        'Roadworthy (with defects)' => 'Roadworthy (with defects)',
        'VOR' => 'VOR',
        'VOR - Accident damage' => 'VOR - Accident damage',
        'VOR - Bodybuilder' => 'VOR - Bodybuilder',
        'VOR - Bodyshop' => 'VOR - Bodyshop',
        'VOR - MOT' => 'VOR - MOT',
        'VOR - Service' => 'VOR - Service',
        'VOR - Quarantined' => 'VOR - Quarantined',
        'Other' => 'Other'
    ],

    'ownershipStatus' => [
        '' => '',
        'Contract' => 'Contract',
        'Hired' => 'Hired',
        'Hire purchase' => 'Hire purchase',
        'Leased' => 'Leased',
        'Owned' => 'Owned'  
    ],

    'odometerReadingUnit' => [
        '' => '',
        'km' => 'KM',
        'miles' => 'Miles'
    ],

    'vehicleRegions' => [
        '' => '',
        'North' => 'North',
        'South' => 'South',
        'Central' => 'Central',
        'Scotland' => 'Scotland',
        'Head Office' => 'Head Office'
    ],

    'userAccessibleRegions' => [
        '' => '',
    ],

    'userAccessibleRegionsForQuery' => [],

    'flashMessages' => [
        'dataSaved' => 'Data has been saved successfully.',
        'dataNotSaved' => 'Data could not be saved at this moment. Please try later.',
        'dataDeleted' => 'Data have been deleted successfully.',
        'documentDeleted' => 'The document has been deleted successfully.',
        'dataNotDeleted' => 'Data could not be deleted at this moment. Please try later.',
        'userDisabled' => 'This user has now been deactivated.',
        'userNotDisabled' => 'The user could not be deleted at this moment. Please try later.',
        'loginNotSuccess' => 'You are not authorised to access this system.',
        'noAccess' => 'Sorry, you do not have access to this action',
        'unauthorized' => 'Sorry, your current user permissions do not allow access this information.',
        'noDeleteAccess' => 'You cannot delete your own profile. Please contact a Lanes administrator for further assistance.',
        'dataEnabled' => 'This user has now been reactivated.',
        'dataNotEnabled' => 'This user could not be reactivated at this moment. Please try later.',
        'vehicleOffRoad' => 'This registration belongs to a vehicle that is marked VOR (Vehicle Off Road). This vehicle is unsafe to use at present and MUST NOT be taken onto the highway. Please contact a member of the Transport Team for further advice.',
        'vehicleDeleteDocument' => 'You are not authorised to delete this document.',
        'zoneSaved' => 'Zone have been saved successfully.',
        'reportDeleted' => 'The report has been deleted successfully.',
        'locationSaved'=>'Location have been saved successfully.',
        'locationNotSaved' => 'Location could not be saved at this moment. Please try later.',
        'locationDeleted' => 'Location have been deleted successfully.',
        'locationNotDeleted' => 'Location could not be deleted at this moment. Please try later.',
    ],

    'vehicleRegionsForSelect' => [
        'all' => 'All',
        'et' => 'East',
        'ho' => 'Head Office',
        'no' => 'North',
        'sl' => 'Scotland',
        'st' => 'South',
        'wt' => 'West'
    ],

    'userAccessibleRegionsForSelect' => [
        'all' => 'All'
    ],

    'userAccessibleRegionsForSelectSample' => [
        'East' => 'et',
        'Head Office' => 'ho',
        'North' => 'no',
        'Scotland' => 'sl',
        'South'=> 'st',
        'West' => 'wt'
    ],

    'userRegionsForSelect' => [
        '' => '',
        'North' => 'North',
        'South' => 'South',
        'Central' => 'Central',
        'Scotland' => 'Scotland',
        'Head Office' => 'Head Office'
    ],

    'inspectionInterval' => [
        'hgv' => 'Every 8 weeks',
        'nonhgv' => 'Every 15,000 miles or when indicated'
    ],
    'pushNotification' => [
        'messages' => [
            'vehicle_added' => 'Vehicle Added',
            'vehicle_updated' => 'Vehicle Updated',
            'defect_updated' => 'Defect Updated',
        ]
    ],
    'dashboard' => [
        'periods' => [
            'other' => [
                'text' => 'Date passed/exceeded',
                'type' => 'interval4'
            ],
            'red' => [
                'text' => 'Next 7 days',
                'type' => 'interval1'
            ],
            'amber' => [
                'text' => '8-14 days time',
                'type' => 'interval2'
            ],
            'green' => [
                'text' => '15-30 days time',
                'type' => 'interval3'
            ],
        ],
        'inspection_fields' => [
            'adr-test' => [
                'text' => 'ADR test',
                'type' => 'adr-test',
            ],
            'annual-service' => [
                'text' => 'Annual service',
                'type' => 'annual-service',
            ],
            'compressor-service' => [
                'text' => 'Compressor service',
                'type' => 'compressor-services',
            ],
            'invertor-service' => [
                'text' => 'Invertor service',
                'type' => 'invertor-services',
            ],
            'loler-test' => [
                'text' => 'LOLER test',
                'type' => 'loler-test',
            ],
            'pmi' => [
                'text' => 'PMI',
                'type' => 'pmi',
            ],
            'pto-service' => [
                'text' => 'PTO service',
                'type' => 'pto-services',
            ],
            'next-service-distance' => [
                'text' => 'Service (distance)',
                'type' => 'services-distance',
            ],
            'next-service' => [
                'text' => 'Service (time)',
                'type' => 'services',
            ],
            'taco' => [
                'text' => 'Tachograph calibration',
                'type' => 'tachograph',
            ],        
        ],
        'expiry_fields' => [
            'maintenace-expiry' => [
                'text' => 'Maintenance',
                'type' => 'repair',
            ],
            'mot-expiry' => [
                'text' => 'MOT',
                'type' => 'mot',
            ],
            'tax-expiry' => [
                'text' => 'Tax',
                'type' => 'tax',
            ],
        ],
    ],
    'vehicleDivisions' => [
        'Assurance' => 'Assurance',
        'Finance' => 'Finance',
        'HR' => 'HR',
        'IT' => 'IT',
        'Maintenance' => 'Maintenance',
        'Operations' => 'Operations',
        'Pipeline' => 'Pipeline'
    ],
    'usersBaseLocations' => [
        '' => '',
        'Aldermaston' => 'Aldermaston',
        'Backford North' => 'Backford North',
        'Hallen' => 'Hallen',
        'Home (Remote)' => 'Home (Remote)',
        'Inverness PSD' => 'Inverness PSD',
        'Killingholme' => 'Killingholme',
        'London Office'=> 'London Office',
        'Maintenance Central' => 'Maintenance Central',
        'Maintenance East' => 'Maintenance East',
        'Maintenance North' => 'Maintenance North',
        'Maintenance South' => 'Maintenance South',
        'Maintenance West' => 'Maintenance West',
        'Misterton'=> 'Misterton',
        'Purton' => 'Purton',
        'Redmile' => 'Redmile',
        'Rawcliffe' => 'Rawcliffe',
        'Redcliffe Bay' => 'Redcliffe Bay',
        'Saffron Walden' => 'Saffron Walden',
        'Sandy' => 'Sandy',
        'Thames B'=> 'Thames B',
        'Thetford' => 'Thetford',
        'Walton' => 'Walton'
    ],
    'usersDivisions' => [
        '' => '',
        'Assurance' => 'Assurance',
        'Finance'=> 'Finance',
        'HR' => 'HR',
        'IT' => 'IT',
        'Maintenance' => 'Maintenance',
        'Operations' => 'Operations',
        'Pipeline' => 'Pipeline'
    ],
    'format' => [
        'showDateTime' => 'H:i:s d M Y',
        'jsShowDateTime' => 'hh:ii:ss d M yyyy',
        'displayTimezone' => 'Europe/London'
    ],
    'profile_status' => [
        '' => '',
        'Active' => 'Active',
        'Archived' => 'Archived'
    ],
    'planner_events' => [
        'All' => 'All',
        'ADR test' => 'ADR test',
        'Annual service' => 'Annual service',
        'Compressor service' => 'Compressor service',
        'Invertor service' => 'Invertor service',
        'LOLER test' => 'LOLER test',
        'Maintenance expiry' =>  'Maintenance expiry',
        'MOT expiry' => 'MOT expiry',
        'Next service' => 'Service',
        'PMI' => 'PMI',
        'PTO service' => 'PTO service',
        'Tacho calibration' => 'Tacho calibration',
        'Tax expiry' => 'Tax expiry',
    ],
    'planner_events_names' => [
    	'adrTest' => ['title' => 'ADR test', 'filter' => 'adr_test'],
        'annualServiceInspection' => ['title' => 'Annual service', 'filter' => 'dt_annual_service_inspection'],
    	'nextServiceInspection' => ['title' => 'Service', 'filter' => 'dt_next_service_inspection'],
    	'compressorService' => ['title' => 'Compressor service', 'filter' => 'next_compressor_service'],
    	'pmiDate' => ['title' => 'PMI', 'filter' => 'next_pmi_date','filter_first_pmi' => 'first_pmi_date'],
    	'invertorServiceDate' => ['title' => 'Invertor service', 'filter' => 'next_invertor_service_date'],
    	'ptoServiceDate' => ['title' => 'PTO service', 'filter' => 'next_pto_service_date'],
    	'lollerTestDueDates' => ['title' => 'LOLER test', 'filter' => 'dt_loler_test_due'],
    	'tachographCalibration' => ['title' => 'Tacho calibration', 'filter' => 'dt_tacograch_calibration_due'],
    	'repairExpiry' =>  ['title' => 'Maintenance expiry', 'filter' => 'dt_repair_expiry' ],
    	'taxExpiry' => ['title' => 'Tax expiry', 'filter' => 'dt_tax_expiry'],
    	'motExpiry' => ['title' => 'MOT expiry', 'filter' => 'dt_mot_expiry'],
    ],

    'co2Unit' => 'g/km',
    'weekReportOptionForSelect' => [
        'thisWeek' => 'This week',
        'prevWeek' => 'Previous week'
    ],
    'monthReportOptionForSelect' => [
        'thisMonth' => 'This month',
        'prevMonth' => 'Previous month'
    ],
    'google_map_key' => getenv('GOOGLE_MAP_KEY'),

    //Maintenance & planning
    'ptoServiceInterval' => [
        '' => '',
        'none' => 'None',
        '3 months' => '3 months',
        '6 months' => '6 months',
        '9 months' => '9 months',
        '12 months' => '12 months',
    ],
    'invertorServiceInterval' => [
        '' => '',
        'none' => 'None',
        '3 months' => '3 months',
        '6 months' => '6 months',
        '9 months' => '9 months',
        '12 months' => '12 months',
    ],
    'compressorServiceInterval' => [
        '' => '',
        'none' => 'None',
        '3 months' => '3 months',
        '6 months' => '6 months',
        '9 months' => '9 months',
        '12 months' => '12 months',
    ],
    'maintenanceEventTypes' => [
        '' => '',
        'adr_test' => 'ADR test',
        'annual_service_inspection' => 'Annual service',
        'compressor_inspection' => 'Compressor service',
        'invertor_inspection' => 'Invertor service',
        'loler_test' => 'LOLER test',
        'mot' => 'MOT',
        'next_service_inspection' => 'Service',
        'preventative_maintenance_inspection' => 'PMI',
        'pto_service_inspection' => 'PTO service',
        'tachograph_calibration' => 'Tacho calibration',
        'vehicle_tax' => 'Tax',
    ],
    'maintenanceHistoryEventTypes' => [
        '' => 'All events',
        'adr_test' => 'ADR test',
        'annual_service_inspection' => 'Annual service',
        'compressor_inspection' => 'Compressor service',
        'invertor_inspection' => 'Invertor service',
        'loler_test' => 'LOLER test',
        'mot' => 'MOT',
        'next_service_inspection' => 'Service',
        'preventative_maintenance_inspection' => 'PMI',
        'pto_service_inspection' => 'PTO service',
        'tachograph_calibration' => 'Tacho calibration',
        'vehicle_tax' => 'Tax',
    ],
    'pmiIntervalService' => [
        '' => '',
        '4 weeks' => '4 weeks',
        '6 weeks' => '6 weeks',
        '8 weeks' => '8 weeks',
        '10 weeks' => '10 weeks',
        '12 weeks' => '12 weeks',
        '13 weeks' => '13 weeks',
        '26 weeks' => '26 weeks'
    ],
    'serviceInspection' => [
        '' => '',
        '4 weeks' => '4 weeks',
        '6 weeks' => '6 weeks',
        '3 months' => '3 months',
        '6 months' => '6 months',
        '9 months' => '9 months',
        '12 months' => '12 months',
    ],
    'eventNotifications' => [
        'adr_test' => [
                    'interval' => '12 months',
                    'column' => 'adr_test',
                    'caption' => 'ADR test reminder',
                    'message' => 'ADR',
                    'maintenanceType' => 'ADR test',
                ],
        'mot' => [
                    'interval' => '4 weeks',
                    'column' => 'dt_mot_expiry',
                    'caption' => 'MOT expiry reminder',
                    'message' => 'MOT',
                    'maintenanceType' => 'MOT expiry',
                ],
        'annual_service_inspection' => [
                    'interval' => '4 weeks',
                    'column' => 'dt_annual_service_inspection',
                    'caption' => 'Annual service reminder',
                    'message' => 'annual service',
                    'maintenanceType' => 'Annual service',
                ],
        'next_service_inspection' => [
            'interval' => '1 weeks',
            'column' => 'dt_next_service_inspection',
            'caption' => 'Service reminder',
            'message' => 'next service',
            'maintenanceType' => 'Service',
        ],
        'vehicle_tax' => [
            'interval' => '1 weeks',
            'column' => 'dt_tax_expiry',
            'caption' => 'Tax expiry reminder',
            'message' => 'tax expiry',
            'maintenanceType' => 'Tax expiry',
        ],
        'preventative_maintenance_inspection' => [
                    'interval' => '1 weeks',
                    'column' => 'next_pmi_date',
                    'caption' => 'PMI reminder',
                    'message' => 'PMI',
                    'maintenanceType' => 'PMI',
                ],
        'pto_service_inspection' => [
                    'interval' => '1 weeks',
                    'column' => 'next_pto_service_date',
                    'caption' => 'PTO service reminder',
                    'message' => 'PTO service',
                    'maintenanceType' => 'PTO service',
                ],
        'invertor_inspection' => [
                    'interval' => '1 weeks',
                    'column' => 'next_invertor_service_date',
                    'caption' => 'Invertor service reminder',
                    'message' => 'invertor service',
                    'maintenanceType' => 'Invertor service',
                ],
        'compressor_inspection' => [
                    'interval' => '1 weeks',
                    'column' => 'next_compressor_service',
                    'caption' => 'Compressor service reminder',
                    'message' => 'compressor service',
                    'maintenanceType' => 'Compressor service',
                ],
        'loler_test' => [
                    'interval' => '1 weeks',
                    'column' => 'dt_loler_test_due',
                    'caption' => 'LOLER test reminder',
                    'message' => 'LOLER',
                    'maintenanceType' => 'LOLER test',
                ],
        'tachograph_calibration' => [
                    'interval' => '1 weeks',
                    'column' => 'dt_tacograch_calibration_due',
                    'caption' => 'Tacho calibration reminder',
                    'message' => 'tacho calibration',
                    'maintenanceType' => 'Tacho calibration',
                ],
    ],
    'service_column_mapping' => [
        'adr_test' => [
            'get_column' => 'adr_test_date',
            'set_column' => 'adr_test_date',
            'interval' => '12 months',
            'set_expiry_date' => true,
        ],
        'mot' => [
            'get_column' => '',
            'set_column' => 'dt_mot_expiry',
            'interval' => '1 year',
            'set_expiry_date' => true,
        ],
        'next_service_inspection' => [
            'get_column' => 'service_inspection_interval',
            'set_column' => 'dt_next_service_inspection',
            'interval' => '',
            'set_expiry_date' => true,
        ],
        'invertor_inspection' => [
            'get_column' => 'invertor_service_interval',
            'set_column' => 'next_invertor_service_date',
            'interval' => '',
            'set_expiry_date' => true,
        ],
        'compressor_inspection' => [
            'get_column' => 'compressor_service_interval',
            'set_column' => 'next_compressor_service',
            'interval' => '',
            'set_expiry_date' => true,
        ],
        'loler_test' => [
            'get_column' => 'loler_test_interval',
            'set_column' => 'dt_loler_test_due',
            'interval' => '1 year',
            'set_expiry_date' => true,
        ],
        'vehicle_tax' => [
            'get_column' => '',
            'set_column' => 'dt_tax_expiry',
            'interval' => '1 year',
            'set_expiry_date' => true,
        ],
        'pto_service_inspection' => [
            'get_column' => 'pto_service_interval',
            'set_column' => 'next_pto_service_date',
            'interval' => '',
            'set_expiry_date' => true,
        ],
        'preventative_maintenance_inspection' => [
            'get_column' => 'pmi_interval',
            'set_column' => 'next_pmi_date',
            'interval' => '',
            'set_expiry_date' => false,
        ],
        'annual_service_inspection' => [
            'get_column' => '',
            'set_column' => 'dt_annual_service_inspection',
            'interval' => '1 year',
            'set_expiry_date' => true,
        ],
        'tachograph_calibration' => [
            'get_column' => '',
            'set_column' => 'dt_tacograch_calibration_due',
            'interval' => '2 year',
            'set_expiry_date' => true,
        ],
        'tank_test' => [
            'get_column' => 'tank_test_interval',
            'set_column' => 'tank_test_date',
            'interval' => '2 year',
            'set_expiry_date' => true,
        ],
    ],

    'eventsList' => [
        'adr_test' => 'ADR test',
        'dt_annual_service_inspection' => 'Annual service',
        'next_compressor_service' => 'Compressor service',
        'next_invertor_service_date' => 'Invertor service',
        'dt_loler_test_due' => 'LOLER test',
        'dt_repair_expiry' => 'Maintenance expiry',
        'dt_mot_expiry' => 'MOT expiry',
        'dt_next_service_inspection' => 'Service',
        'next_pmi_date' => 'PMI',
        'next_pto_service_date' => 'PTO service',
        'dt_tacograch_calibration_due' => 'Tacho calibration',
        'dt_tax_expiry' => 'Tax expiry',
    ],
    'eventSlugWithVehicleFields' => [
        'adr_test' => 'adr_test_date',
        'annual_service_inspection' => 'dt_annual_service_inspection',
        'compressor_inspection' => 'next_compressor_service',
        'invertor_inspection' => 'next_invertor_service_date',
        'loler_test' => 'dt_loler_test_due',
        'maintenance_expiry' => 'dt_repair_expiry',
        'mot' => 'dt_mot_expiry',
        'preventative_maintenance_inspection' => 'next_pmi_date',
        'pto_service_inspection' => 'next_pto_service_date',
        'next_service_inspection_distance' => 'dt_next_service_inspection',
        'next_service_inspection' => 'dt_next_service_inspection',
        'tachograph_calibration' => 'dt_tacograch_calibration_due',
        'vehicle_tax' => 'dt_tax_expiry'
    ],
    'vehicleLocation' => [

    ],
    'userNotificationEventTypes' => [
        'maintenance_summary_(based on vehicle permissions):' => [
            'weekly_maintenance' => 'Weekly maintenance summary (sent every Thursday @ 09:00am)'
        ],
        "maintenance_and_planning_notifications_(nominated driver):" => [
            'adr_test' => 'ADR test reminder',
            'annual_service_inspection' => 'Annual service reminder',
            'compressor_inspection' => 'Compressor service reminder',
            'invertor_inspection' => 'Invertor service reminder',
            'loler_test' => 'LOLER test reminder',
            'mot' => 'MOT expiry reminder',
            'next_service_inspection_distance' => 'Service reminder (distance)',
            'next_service_inspection' => 'Service reminder (time)',
            'preventative_maintenance_inspection' => 'PMI reminder',
            'pto_service_inspection' => 'PTO service reminder',
            'tachograph_calibration' => 'Tacho calibration reminder',
            'vehicle_tax' => 'Tax expiry reminder',
        ],
        "telematics:" => [
            'zone_alerts' => 'Zone alerts'
        ]
    ],    
    'vehicle_type_odometer_setting' => [
        '' => '',
        'km' => 'KM',
        'miles' => 'Miles'
    ],
    'app_url'=> env('APP_URL'),

    'maintenance_history_status' => [
        '' => '',
        'Complete' => 'Complete',
        'Incomplete' => 'Incomplete'
    ],

    'automaticMaintenanceEvent' => [
        [
            'event' => 'mot',
            'date' => 'dt_mot_expiry',
        ],
        [
            'event' => 'annual_service_inspection',
            'date' => 'dt_annual_service_inspection',
        ],
        [
            'event' => 'next_service_inspection',
            'date' => 'dt_next_service_inspection',
        ],
        [
            'event' => 'vehicle_tax',
            'date' => 'dt_tax_expiry',
        ],
        [
            'event' => 'preventative_maintenance_inspection',
            'date' => 'next_pmi_date',
        ],
        [
            'event' => 'preventative_maintenance_inspection',
            'date' => 'first_pmi_date',
        ],
        [
            'event' => 'pto_service_inspection',
            'date' => 'next_pto_service_date',
        ],
        [
            'event' => 'invertor_inspection',
            'date' => 'next_invertor_service_date',
        ],
        [
            'event' => 'compressor_inspection',
            'date' => 'next_compressor_service',
        ],
        [
            'event' => 'loler_test',
            'date' => 'dt_loler_test_due',
        ],
        [
            'event' => 'tachograph_calibration',
            'date' => 'dt_tacograch_calibration_due',
        ],
        [
            'event' => 'adr_test',
            'date' => 'adr_test_date',
        ],
        [
            'event' => 'tank_test',
            'date' => 'tank_test_date',
        ],
    ],
    'eventRemindersNotifications' => [
        [
            'event' => 'adr_test',
            'interval' => '90 days',
            'column' => 'adr_test_date',
            'caption' => 'ADR test reminder',
            'message' => 'ADR test',
            'maintenanceType' => 'ADR test expiry',
        ],
        [
            'event' => 'mot',
            'interval' => '30 days',
            'column' => 'dt_mot_expiry',
            'caption' => 'MOT expiry reminder',
            'message' => 'MOT',
            'maintenanceType' => 'MOT expiry',
        ],
        [
            'event' => 'annual_service_inspection',
            'interval' => '30 days',
            'column' => 'dt_annual_service_inspection',
            'caption' => 'Annual service reminder',
            'message' => 'annual service',
            'maintenanceType' => 'Annual service',
        ],
        [
            'event' => 'next_service_inspection',
            'interval' => '30 days',
            'column' => 'dt_next_service_inspection',
            'caption' => 'Service reminder',
            'message' => 'service',
            'maintenanceType' => 'Service (time)',
        ],
        [
            'event' => 'vehicle_tax',
            'interval' => '30 days',
            'column' => 'dt_tax_expiry',
            'caption' => 'Tax expiry reminder',
            'message' => 'tax expiry',
            'maintenanceType' => 'Tax expiry',
        ],
        [
            'event' => 'preventative_maintenance_inspection',
            'interval' => '30 days',
            'column' => 'next_pmi_date',
            'caption' => 'PMI reminder',
            'message' => 'PMI',
            'maintenanceType' => 'PMI',
        ],
        [
            'event' => 'preventative_maintenance_inspection',
            'interval' => '30 days',
            'column' => 'first_pmi_date',
            'caption' => 'PMI reminder',
            'message' => 'PMI',
            'maintenanceType' => 'PMI',
        ],
        [
            'event' => 'pto_service_inspection',
            'interval' => '30 days',
            'column' => 'next_pto_service_date',
            'caption' => 'PTO service reminder',
            'message' => 'PTO service',
            'maintenanceType' => 'PTO service',
        ],
        [
            'event' => 'invertor_inspection',
            'interval' => '30 days',
            'column' => 'next_invertor_service_date',
            'caption' => 'Invertor service reminder',
            'message' => 'invertor service',
            'maintenanceType' => 'Invertor service',
        ],
        [
            'event' => 'compressor_inspection',
            'interval' => '30 days',
            'column' => 'next_compressor_service',
            'caption' => 'Compressor service reminder',
            'message' => 'compressor service',
            'maintenanceType' => 'Compressor service',
        ],
        [
            'event' => 'loler_test',
            'interval' => '30 days',
            'column' => 'dt_loler_test_due',
            'caption' => 'LOLER test reminder',
            'message' => 'LOLER test',
            'maintenanceType' => 'LOLER test',
        ],
        [
            'event' => 'tachograph_calibration',
            'interval' => '30 days',
            'column' => 'dt_tacograch_calibration_due',
            'caption' => 'Tacho calibration reminder',
            'message' => 'tacho calibration',
            'maintenanceType' => 'Tacho calibration',
        ],
        [
            'event' => 'next_service_inspection_distance',
            'interval' => '30 days',
            'column' => 'next_service_inspection_distance',
            'caption' => 'Service reminder',
            'message' => 'service',
            'maintenanceType' => 'Service',
        ],
    ],
    'vehicleStatusArchived' => [
            'Archived',
            'Archived - De-commissioned',
            'Archived - Written off',
            'Archived - Sold',
    ],
    'vahicleStatusUnArchived' => [
        'Re-positioning',
        'Awaiting kit',
        'Roadworthy',
        'Roadworthy (with defects)',
        'VOR',
        'VOR - Accident damage',
        'VOR - Bodybuilder',
        'VOR - Bodyshop',
        'VOR - MOT',
        'VOR - Quarantined',
        'VOR - Service',
    ],

    // 'to_show_fleet_cost' => env('TO_SHOW_FLEET_COST', true),

    'telematics_incidents' => [
        'tm8.dfb2.acc.l'=>'Harsh Acceleration v2 (Low Threshold)',
        'tm8.dfb2.dec.l'=>'Harsh Braking v2 (Low Threshold)',
        'tm8.dfb2.cnrl.l'=>'Harsh Left Cornering (Low Threshold)',
        'tm8.dfb2.cnrr.l'=>'Harsh Right Cornering (Low Threshold)',
        'tm8.gps.idle.end'=>'Idle End',
        'tm8.dfb2.rpm'=>'RPM Over Threshold v2',
        'tm8.dfb2.spd'=>'Speeding Over Threshold v2',
        'tm8.dfb2.spdinc'=>'Speeding Over Threshold v2',
        'tm8.fnol'=>'FNOL',
    ],
    /*'telematics_incidents_filter' => [
        'tm8.dfb2.acc.l'=>'Harsh Acceleration v2 (Low Threshold)',
        'tm8.dfb2.dec.l'=>'Harsh Braking v2 (Low Threshold)',
        'harsh.cornering'=>'Harsh Cornering (Low Threshold)',
        //'tm8.dfb2.cnrl.l'=>'Harsh Left Cornering (Low Threshold)',
        //'tm8.dfb2.cnrr.l'=>'Harsh Right Cornering (Low Threshold)',
        'tm8.gps.heartbeat'=>'Heartbeat',
        'tm8.gps.idle.start'=>'Idle Start',
        'tm8.dfb2.rpm'=>'RPM Over Threshold v2',
        'tm8.dfb2.spd'=>'Speeding Over Threshold v2'
    ],*/
    'telematics_incidents_filter' => [
        'tm8.dfb2.acc.l'=>'Harsh Acceleration',
        'tm8.dfb2.dec.l'=>'Harsh Braking',
        'harsh.cornering'=>'Harsh Cornering',
        'tm8.gps.idle.end'=>'Idling',
        'tm8.dfb2.rpm'=>'RPM',
        // 'tm8.dfb2.spd'=>'Speeding'
        'tm8.dfb2.spdinc'=>'Speeding',
        'tm8.fnol'=>'FNOL'
    ],
    'loler_test_interval' => [
        '' => '',
        'none' => 'None',
        '6 months' => '6 months',
        '12 months' => '12 months',
    ],
    'service_inspection_type' => [
        '' => '',
        'Distance' => 'Distance',
        'Time' => 'Time'
    ],
    'adr_test' => [
        '' => '',
        '12 months' => '12 months',
    ],
    'minimum_service_interval' => 3000,
    'dvsa_years' => [
        '2021' => 'Year 2021',
        '2020' => 'Year 2020',
        '2019' => 'Year 2019'
    ],
    'dvsa_codes' => [
        'M1' => 'M1 - Full Set',
        'M2' => 'M2 - Completed',
        'M3' => 'M3 - Frequency',
        'M4' => 'M4 - Driver Defects',
        'M5' => 'M5 - MOT'
    ],
    'dvsa_periods' => [
        'period_6' => 'Period 6 - 2021 (24/05/2021 to 20/06/2021)',
        'period_5' => 'Period 5 - 2021 (26/04/2021 to 23/05/2021)',
        'period_4' => 'Period 4 - 2021 (29/03/2021 to 25/04/2021)',
        'period_3' => 'Period 3 - 2021 (01/03/2021 to 28/03/2021)',
        'period_2' => 'Period 2 - 2021 (01/02/2021 to 28/02/2021)',
        'period_1' => 'Period 1 - 2021 (04/01/2021 to 31/01/2021)',
    ],

    'alert_type' => [
        '' => '',
        'dtc' => 'DTC',
        'fnol' => 'FNOL',
        'trigger' => 'Trigger',
        'other' => 'Other',
    ],

    'alert_source' => [
        '' => '',
        'telematics' => 'Telematics',
        'system' => 'System',
        'other' => 'Other',
    ],

    'alert_severity' => [
        '' => '',
        'critical' => 'Critical',
        'high' => 'High',
        'medium' => 'Medium',
        'low' => 'Low',
        'lowest' => 'Lowest'
    ],

    'alert_status' => [
        '' => '',
        'active' => 'Active',
        'disabled' => 'Disabled',
    ],

    'alert_notification' => [
        '' => '',
        'open' => 'Open',
        'resolved' => 'Resolved',
    ],
    'webfleet' => [
        'api_key' => env('WEBFLEET_API_KEY',''),
        'account' => env('WEBFLEET_ACCOUNT',''),
        'username' => env('WEBFLEET_USERNAME',''),
        'password' => env('WEBFLEET_PASSWORD',''),
        'api_url' => env('WEBFLEET_API_URL','https://csv.webfleet.com/extern'),
        'datetime_format' => 'd/m/Y H:i:s',
    ],
    'teletracnavman' => [
        'api_key' => env('TELETRACNAVMAN_API_KEY',''),
        'api_url' => 'https://api-uk.nextgen.teletracnavman.net/v1',
        'datetime_format' => 'd/m/Y H:i:s',
    ],
    'googleMap' => [
        'api_key' => env('GOOGLE_MAP_KEY',''),
        'api_url' => 'https://maps.googleapis.com/maps/api/geocode/json',
    ],

    'message_attachments' => [
        'single_file_size' => 10, //in MB
        'total_file_size' => 50, //in MB
        'default_message' => 'The total of all attachments can be up to 50MB and a single attachment can be up to 10MB.'
    ],

    'standard_reports' => [
        'standard_last_login_report' => [
            'First Name|User', 'Last Name|User', 'Company|User', 'User Division|User', 'User Region|User', 'Username|User', 'Email|User', 'Mobile|User', 'Roles|User', 'Last Login|User', 'Is Archived?|User'
        ],
        'standard_fleet_cost_report' => [
            'Registration|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Location|Vehicle', 'Nominated Driver|Vehicle', 'Type|Vehicle', 'Operator License|Vehicle', 'Ownership Status|Vehicle', 'Vehicle Status|Vehicle', 'Date Added To Fleet|Vehicle', 'Location From|Vehicle', 'Location To|Vehicle', 'Hire Cost|Vehicle', 'Management Cost|Vehicle', 'Depreciation Cost|Vehicle', 'Vehicle Tax|Vehicle', 'Insurance Cost|Vehicle', 'Telematics Cost|Vehicle', 'Manual Cost Adj|Vehicle', 'Fuel|Vehicle', 'Oil|Vehicle', 'AdBlue|Vehicle', 'Screen Wash|Vehicle', 'Fleet Livery|Vehicle', 'Defects|Vehicle', 'Total|Vehicle', 'Transfer|Vehicle'
        ],
        'standard_p11d_benefits_report' => [
            'Full Name|User', 'Type of Vehicle|Vehicle', 'Type of Fuel used|Vehicle', 'Vehicle Index|Vehicle', 'Make|Vehicle', 'Model|Vehicle', 'Date first registered|Vehicle', 'CO2|Vehicle', 'C02 %|Vehicle', 'Engine Size (Cubic Capacity)|Vehicle', 'Date vehicle was available from|Vehicle', 'Date vehicle was no longer available|Vehicle', 'Vehicle List Price (Non-commercial) / Benefit charge (Commercial)|Vehicle', 'FULL Benefit / Cash Eqiv|Vehicle', 'No. Days in tax year|Vehicle', 'Prorat\'d BIK Based on no. of days (£)|Vehicle', 'Private use days in tax year|Vehicle', 'Fuel card used during tax year|Vehicle', 'Fuel Benefit Charge (£)|Vehicle', 'Prorat\'d Fuel Benefit Based on no. of days (£)|Vehicle'
        ],
        'standard_activity_report' => [
            'First Name|User', 'Last Name|User', 'Username/Email|User', 'User Region|User', 'Vehicle Take Out|Vehicle', 'Vehicle Return|Vehicle'
        ],
        'standard_vor_report' => [
            'Registration|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'HGV/Non-HGV|Vehicle', 'Type|Vehicle', 'Manufacturer|Vehicle', 'Model|Vehicle', 'Vehicle Location|Vehicle', 'Repair/Maintenance Location|Vehicle', "Dated VOR'd|Vehicle", 'VOR Duration(days)|Vehicle', 'Vehicle Status|Vehicle', 'Defect Category|Vehicle', 'Defect|Vehicle', 'Defect Number|Vehicle', 'Estimated Completion Date|Vehicle', 'Last Comment Date|Vehicle', 'Last Comment|Vehicle', 
        ],
        'standard_vor_defect_report' => [
            'Defect Category|Vehicle', 'Defect|Vehicle'
        ],
        'standard_defect_report' => [
            'Registration|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'HGV/Non-HGV|Vehicle', 'Type|Vehicle', 'Manufacturer|Vehicle', 'Model|Vehicle', 'Vehicle Location|Vehicle', 'Repair/Maintenance Location|Vehicle', 'Defect Date|Vehicle', 'Defect Number|Vehicle', 'Odometer|Vehicle', 'Defect Category|Vehicle', 'Defect|Vehicle', 'Vehicle Status|Vehicle', 'Defect Status|Vehicle', 'Last Comment Date|Vehicle', 'Last Comment|Vehicle'
        ],
        'standard_driving_events_report' => [
            'Registration|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'First Name|User', 'Last Name|User', 'Date|Vehicle', 'Time|Vehicle', 'Incident|Vehicle', 'Location|Vehicle'
        ],
        'standard_speeding_report' => [
            'Registration|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Vehicle Status|Vehicle', 'First Name|User', 'Last Name|User', 'Speed(MPH)|Vehicle', 'Speed Limit(MPH)|Vehicle', 'Date|Vehicle', 'Time|Vehicle', 'Incident|Vehicle', 'Location|Vehicle'
        ],
        'standard_journey_report' => [
            'Registration|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'First Name|User', 'Last Name|User', 'Journey Start Time|Vehicle', 'Journey End Time|Vehicle', 'Journey Duration(HH:MM:SS)|Vehicle', 'Journey Distance(Miles)|Vehicle', 'Start Location|Vehicle', 'End Location|Vehicle', 'Number of Incidents|Vehicle', 'Fuel|Vehicle', 'MPG(Actual)|Vehicle', 'MPG(Expected)|Vehicle', 'Journey CO2|Vehicle'
        ],
        'standard_fuel_usage_and_emission_report' => [
            'Registration|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Journey Duration(HH:MM:SS)|Vehicle', 'Journey Distance(Miles)|Vehicle', 'Actual Driving Time(HH:MM:SS)|Vehicle', 'Idling Time(HH:MM:SS)|Vehicle', 'Fuel Consumption(in litre)|Vehicle', 'MPG(Actual)|Vehicle', 'MPG(Expected)|Vehicle', 'Journey CO2|Vehicle'
        ],
        'standard_driver_behaviour_report' => [
            'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User', 'Safety Score|User', 'Efficiency Score|User', 'Overall Score|User'/* , 'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle' */
        ],
        'standard_vehicle_behaviour_report' => [
            'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Safety Score|User', 'Efficiency Score|User', 'Overall Score|User', 'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User'
        ],
        'standard_vehicle_profile_report' => [
            'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Manufacturer|Vehicle', 'Model|Vehicle', 'Fuel Type|Vehicle', 'Type Of Engine|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Chassis Number|Vehicle', 'Contract ID|Vehicle', 'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User'
        ],
        'standard_vehicle_incident_report' => [
            'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Incident Date|Vehicle', 'Incident Type|Vehicle', 'Speed Limit(MPH)|Vehicle', 'Vehicle Speed(MPH)|Vehicle', 'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User'
        ],
        'standard_vehicle_defects_report' => [
            'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User', 'Defect Date|Vehicle', 'Defect Category|Vehicle', 'Defect Name|Vehicle'
        ],
        'standard_vehicle_checks_report' => [
            'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User', 'Check Type|Vehicle', 'Check Result|Vehicle', 'Last Check Date|Vehicle','Check Duration|Vehicle'
        ],
        // 'standard_vehicle_planning_report' => [
        //     'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'Annual Service|Vehicle', 'Compressor Service|Vehicle', 'Invertor Service|Vehicle', 'LOLER Test|Vehicle', 'MOT|Vehicle', 'First PMI|Vehicle', 'Next PMI|Vehicle', 'PTO Service|Vehicle', 'Service|Vehicle', 'Tacho Service|Vehicle', 'Tax|Vehicle' 
        // ],
        'standard_vehicle_planning_report' => [
            'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Service Type|Vehicle', 'Service Date|Vehicle', 'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User'
        ],
        'standard_user_details_report' => [
            'Email|User', 'First Name|User', 'Last Name|User', 'Created Date|User', 'Engineer ID|User', 'Mobile|User', 'Landline|User', 'Company|User', 'Dallas Key|User', 'IMEI Number|User', 'Base location|User', 'User Division|User', 'User Region|User', 'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle'
        ],
        'standard_user_incident_report' => [
            'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User', 'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Incident Date|Vehicle', 'Incident Type|Vehicle', 'Speed Limit(MPH)|Vehicle', 'Vehicle Speed(MPH)|Vehicle'
        ],
        'standard_user_defects_report' => [
            'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User', 'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Defect Date|Vehicle', 'Defect Category|Vehicle', 'Defect Name|Vehicle'
        ],
        'standard_user_checks_report' => [
            'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User', 'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Check Type|Vehicle', 'Check Result|Vehicle', 'Last Check Date|Vehicle'
        ],
        'standard_user_journey_report' => [
            'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User', 'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Journey Start Date|Vehicle', 'Journey Start Time|Vehicle', 'Journey Duration(HH:MM:SS)|Vehicle', 'Journey Distance(Miles)|Vehicle'
        ],
        'standard_vehicle_journey_report' => [
            'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Journey Start Date|Vehicle', 'Journey Start Time|Vehicle', 'Journey Duration(HH:MM:SS)|Vehicle', 'Journey Distance(Miles)|Vehicle', 'Email|User', 'First Name|User', 'Last Name|User', 'Engineer ID|User', 'Mobile|User', 'Company|User', 'User Division|User', 'User Region|User'
        ],
        'standard_weekly_maintanance_report' => [
            'Registration|Vehicle', 'Type|Vehicle', 'Manufacturer|Vehicle', 'Model|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Vehicle Location|Vehicle', 'Maintenance Event|Vehicle', 'Due Date|Vehicle', 'Repair/Maintenance Location|Vehicle'
        ],
        'standard_vehicle_location_report' => [
            'Registration|Vehicle', 'Company|User', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Email|User', 'First Name|User', 'Last Name|User', 'Journey Start Date|Vehicle', 'Journey Start Time|Vehicle', 'Journey End Time|Vehicle', 'Journey Start Location|Vehicle', 'Journey Start Map Link|Vehicle', 'Journey End Location|Vehicle', 'Journey End Map Link|Vehicle'
        ],
        'standard_pmi_performance_report' => [
            'Registration|Vehicle', 'Type|Vehicle', 'Category|Vehicle', 'Sub Category|Vehicle', 'Vehicle Division|Vehicle', 'Vehicle Region|Vehicle', 'Vehicle Location|Vehicle', 'PMI Planned Date|Vehicle', 'PMI Actual Date|Vehicle', 'Repair/Maintenance Location|Vehicle', 'Event Status|Vehicle'
        ],
    ],
    'vehicle_check_type' => [
        '' => '',
        'vehicle_take_out' => 'Vehicle take out',
        'vehicle_return' => 'Vehicle return'
    ],

    'supplier_telematics' => [
        '' => '',
        'prolius' => 'Prolius',
        'trakm8' => 'Trakm8',
        'webfleet' => 'Webfleet',
        'other' => 'Other'
    ],

    'driver_tag' => [
        '' => '',
        'none' => 'None',
        'dallas_key' => 'Dallas key',
        'rfid_card' => 'RFID card',
    ],
    
    'device_telematics' => [
        '' => '',
        'D330' => 'D330',
        'D430' => 'D430',
        'D430_and_F750' => 'D430 and F750',
        'D500' => 'D500',
        'D500_and_F520' => 'D500 and F520',
        'Unknown' => 'Unknown'
    ],

    'alert_setting' => [
        '' => '',
        '1' => 'On entry',
        '0' => 'On exit',
        '2' => 'On entry and exit',
    ],

    'zone_status' => [
        '' => '',
        '1' => 'Active',
        '0' => 'In-active',
    ],

    'send_verification_email_while_user_import' => env('SEND_VERIFICATION_EMAIL_WHILE_USER_IMPORT', true),

    'idling_events' => ['tm8.gps.idle.start' , 'tm8.gps.idle.end' , 'tm8.gps.can.idle.start' , 'tm8.gps.can.idle.end' , 'tm8.gps.exces.idle', 'tm8.gps.idle.ongoing'],
    // 'moving_events' => ['tm8.gps','tm8.dfb2.acc.l','tm8.dfb2.dec.l','tm8.dfb2.spd','tm8.dfb2.rpm','tm8.dfb2.cnrr.l','tm8.dfb2.cnrl.l'],
    'moving_events' => ['tm8.gps','tm8.dfb2.acc.l','tm8.dfb2.dec.l','tm8.dfb2.spd','tm8.dfb2.spdinc','tm8.dfb2.rpm','tm8.dfb2.cnrr.l','tm8.dfb2.cnrl.l'],
    'stopped_events' => ['tm8.fnol','tm8.jny.sum.ex1','tm8.jny.sum','tm8.gps.ign.off','tm8.gps.heartbeat','tm8.gps.jny.end','tm8.jny.score'],
    'start_events' => ['tm8.gps.jny.start','tm8.gps.ign.on','tm8.battery.profile.generated','tm8.gps.rfid.entry','tm8.gps.can.pending.fault'],

    'incident_types' => [
        '' => '',
        'Glass damage' => 'Glass damage',
        'Pedestrian incident' => 'Pedestrian incident',
        'Stolen vehicle' => 'Stolen vehicle',
        'Traffic incident' => 'Traffic incident'
    ],

    'incident_classification' => [
        'Glass damage' => [
            'Window screen' => 'Window screen',
            'Front right' => 'Front right',
            'Front left' => 'Front left',
            'Back right' => 'Back right',
            'Back left' => 'Back left',
            'Other' => 'Other',
        ],
        'Pedestrian incident' => [
            'Head-on collision' => 'Head-on collision',
            'Reversing collision' => 'Reversing collision',
            'Sideswipe collision' => 'Sideswipe collision',
            'Other' => 'Other',
        ],
        'Stolen vehicle' => [
            'Stolen' => 'Stolen',
            'Other' => 'Other',
        ],
        'Traffic incident' => [
            'Animal collision' => 'Animal collision',
            'Bicycle collision' => 'Bicycle collision',
            'Car collision' => 'Car collision',
            'Motorbike collision' => 'Motorbike collision',
            'Road debris collision' => 'Road debris collision',
            'Stationary object' => 'Stationary object',
            'Other' => 'Other',
        ],
    ],
    'tank_test_interval' => [
        '' => '',
        'none' => 'None',
        '36 months' => '36 months',
    ],
    'telematics_reports' => [
        'standard_driver_behaviour_report',
        'standard_vehicle_behaviour_report',
        'standard_vehicle_journey_report',
        'standard_vehicle_incident_report',
        'standard_user_journey_report',
        'standard_user_incident_report'
    ],

    'vehicle_ad_hoc_costs' => [
        '' => '',
        'adblue' => 'AdBlue',
        'fleet_livery_wash' => 'Fleet livery wash',
        'fuel' => 'Fuel',
        'manual_cost_adjustment' => 'Manual cost adjustment',
        'oil' => 'Oil',
        'screen_wash' => 'Screen wash',
    ],

    'hiddenUser' => [
        'support@imastr.com', 'system@imastr.com', 'admin@imastr.com'
    ],
    'telematicsSystemUserVisibleName' => [
        'FN' => 'Driver',
        'LN' => 'Unknown',
        'FULL' => 'Driver Unknown',
    ],
    'allowViewingColumnsForDebug' => ['hvora@aecordigital.com','support@imastr.com','system@imastr.com','admin@imastr.com'],

];

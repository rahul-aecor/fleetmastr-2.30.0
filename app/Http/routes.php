<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
/*if(version_compare(PHP_VERSION, '7.2.0', '>=')) {
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
}*/

// Dashboard route...
Route::group(['middleware' => ['auth', 'cors']], function () {
    Route::get('/', 'DashboardController@index');
    Route::get('/home', 'DashboardController@index');
    Route::get('/statistics/all-dashboard-stats','StatisticsController@allDashboardStats');
    Route::get('/statistics/vehicleFleetCostStats', 'StatisticsController@vehicleFleetCostStats');
    Route::post('/statistics/vehicleFleetCostChartStats', 'StatisticsController@vehicleFleetCostChartStats');
    Route::get('/statistics/vehicleFleetStats', 'StatisticsController@vehicleFleetStats');
    Route::get('/statistics/vehicleChecksStats', 'StatisticsController@vehicleChecksStats');
    Route::any('/statistics/vehicleOffroadStats/{region}', 'StatisticsController@vehicleOffroadStats');
    //Route::get('/statistics/vehicleInspectionData', 'StatisticsController@vehicleInspectionData');
    Route::post('/statistics/fetchUpcomingInspections', 'StatisticsController@fetchUpcomingInspections');
    Route::post('/statistics/fetchUpcomingExpires', 'StatisticsController@fetchUpcomingExpires');

    Route::post('/jqgrid/column/status', 'ColumnManagementController@columnStatus');
    Route::post('/jqgrid/default/reset/column', 'ColumnManagementController@defaultResetColumns');

    Route::post('/change-notification-status', 'DashboardController@changeNotificationStatus');
    Route::post('/delete-user-notification', 'DashboardController@deleteUserNotification');

    Route::get('/dvsa', 'DVSAController@index')->name("dvsa");

    Route::get('/alert_centres', 'AlertController@index');
    Route::any('/alert_centres/data', 'AlertController@anyData');
    Route::post('/alert_centres/storeAlertCenterDetail','AlertController@storeAlertCenterDetail');
    Route::post('/alert_centers/editAlertCentersDetail/{id}/get', 'AlertController@getAlertCentersData');
    Route::post('/alert_centres/editAlertCenterDetail/{id}/edit', 'AlertController@editAlertCenterInfo');
    Route::post('alert_centers/bulkAlertSetting', 'AlertController@bulkAlertSetting');
    Route::post('alert_centers/bulkAlertStatus', 'AlertController@bulkAlertStatus');
    Route::any('/alert_notifications/data', 'AlertController@alertNotificationData');
    Route::any('/alert_centers/editAlertCentersShow/{id}/show', 'AlertController@alertNotificationShow');
    Route::post('/alert_centres/storeTestAlert', 'AlertController@storeTestAlert');
    Route::any('/alert_centres/getAlertCentreData', 'AlertController@getAlertCentreData');
});

// vehicle checks routes...
Route::group(['middleware' => ['auth', 'cors', 'can:settings.manage']], function () {
    Route::get('/settings/', 'SettingsController@index');
    Route::post('/settings/store', 'SettingsController@store');
    Route::post('/settings/storeNotification', 'SettingsController@storeNotification');
    Route::post('/settings/storeReportFinalize', 'SettingsController@storeReportFinalize');
    Route::post('/settings/fuel/store', 'SettingsController@fuel_store');
    Route::post('/settings/uploadLogo', 'SettingsController@uploadLogo');
    Route::get('/settings/previewColor/{color}', 'SettingsController@previewColor');
    Route::any('/settings/hmrcdetail/{year}', 'SettingsController@hmrcdetail');
    Route::any('/settings/hmrcedit/{year}', 'SettingsController@hmrcedit');
    Route::any('/settings/hmrcco2/add/{year}', 'SettingsController@hmrcadd');
    Route::any('/settings/hmrcco2/update/{year}', 'SettingsController@hmrcupdate');
    Route::any('/settings/hmrc/exportexcel/{year}', 'SettingsController@hmrcexportexcel');
    Route::post('/settings/storeSiteConfiguration', 'SettingsController@storeSiteConfiguration');

    Route::post('/settings/storeAccidentInsuranceDetail', 'SettingsController@storeAccidentInsuranceDetail');
    Route::post('/settings/storeFleetCostDetail', 'SettingsController@storeFleetCostDetail');
    Route::post('/settings/annualInsurance', 'SettingsController@editAnnualInsuranceCost');
    Route::post('/settings/telematicsInsurance', 'SettingsController@editAnnualTelematicsCost');
    Route::post('/settings/variableCost', 'SettingsController@variableCostPerMonth');
    Route::post('/settings/fixedCost', 'SettingsController@fixedCostPerMonth');
    Route::post('/settings/fleetMiles', 'SettingsController@fleetMilesPerMonth');
    Route::post('/settings/fleetDamage', 'SettingsController@fleetDamageCostPerMonth');
    Route::post('/settings/saveManualCostAdjustmentListing', 'SettingsController@saveManualCostAdjustmentListing');
    Route::post('/settings/storeMaintenanceReminderNotification', 'SettingsController@storeMaintenanceReminderNotification');

    Route::post('/settings/storeDVSAConfiguration', 'SettingsController@storeDVSAConfiguration');
});
Route::group(['middleware' => ['auth', 'cors', 'can:check.manage']], function () {
    Route::any('checks/data', 'ChecksController@anyData');
    Route::any('checks/getCheckDetails/{id}', 'ChecksController@getCheckDetails');

    Route::resource('checks', 'ChecksController');
    Route::any('checks/exportPdf/{checks}', 'ChecksController@exportPdf');
    Route::any('checks/exportWord/{checks}', 'ChecksController@exportWord');
    Route::post('update/checkimage', 'ChecksController@updateCheckImagePathInJson');

    Route::get('vehicles/{id}/checks', 'VehiclesController@showVehicleChecks');
});

// vehicles routes...
Route::group(['middleware' => ['auth', 'cors', 'can:search.manage']], function () {
    // Route::get('vehicles/planning', 'VehiclesController@planning');
    // Route::any('vehicles/planning_data', 'VehiclesController@planningData');
    Route::resource('vehicles', 'VehiclesController');
    Route::any('vehicles/data', 'VehiclesController@anyData');
    Route::post('vehicles/store', 'VehiclesController@store');
    Route::post('vehicles/update/{id}', 'VehiclesController@update');
    Route::any('vehicles/vehicle_type_data/{vehicleId}/{id}', 'VehiclesController@getVehicleTypeData');
    Route::any('vehicles/vehicle_type_data_json/{vehicleId}/{id}', 'VehiclesController@getVehicleTypeDataJson');
    Route::any('vehicles/exportPdf/{id}', 'VehiclesController@exportPdf');
    Route::any('vehicles/downloadMedia/{id}', 'VehiclesController@downloadMedia');
    Route::post('vehicles/vehicle_by_registration/{id}', 'VehiclesController@fetchVehicleByRegistrationNo');

    // Repair Maintenance location
    Route::post('vehicles/view_all_locations', 'VehiclesController@viewAllLocations');
    Route::post('vehicles/repair-maintenace/delete', 'VehiclesController@locationDelete');
    Route::post('vehicles/update_repair_location', 'VehiclesController@updateLocationName');

    Route::any('vehicles/getVehicleCostSummary/{id}', 'VehiclesController@getVehicleCostSummary');
    Route::post('vehicle/maintenance_history', 'VehiclesController@getMaintenanceHistoryData')->name('maintenance_history');
    Route::post('vehicle/assignment', 'VehiclesController@getAssignmentData')->name('assignment');
    Route::post('vehicles/addVehicleRepairLocation', 'VehiclesController@addVehicleRepairLocation');

    // vehicle history
    Route::post('vehicle/history', 'VehiclesController@getHistoryData')->name('history');

    Route::post('vehicles/addMaintenanceHistory', 'VehiclesController@addMaintenanceHistory');
    Route::post('vehicles/maintenanceHistory/{id}/show', 'VehiclesController@showMaintenanceHistory');
    Route::post('vehicles/maintenanceHistory/{id}/get', 'VehiclesController@getMaintenanceHistory');
    Route::post('vehicles/maintenanceHistory/{id}/edit', 'VehiclesController@editMaintenanceHistory');
    Route::get('vehicles/get-planning-table/{id}', 'VehiclesController@getPlanningTable');
    Route::post('maintenanceHistory/delete', 'VehiclesController@deleteMaintenanceHistory');
    Route::post('/vehicles/{id}/12month/maintenanceHistory', 'VehiclesController@maintenanceHistoryList');

    Route::post('/vehicles/saveVehicleListingFields', 'VehiclesController@saveVehicleListingFields');
    Route::post('/vehicles/calcMonthlyFieldCurrentData', 'VehiclesController@calcMonthlyFieldCurrentData');
    Route::post('/vehicles/calcMonthlyInsuranceFieldCurrentData', 'VehiclesController@calcMonthlyInsuranceFieldCurrentData');
    Route::post('/vehicles/calcMonthlyTelematicsFieldCurrentData', 'VehiclesController@calcMonthlyTelematicsFieldCurrentData');
    Route::post('/vehicles/maintenanceCost', 'VehiclesController@editMaintenanceCost');
    Route::post('/vehicles/editLeaseCost', 'VehiclesController@editLeaseCost');

    Route::post('/vehicles/editMonthlyInsuranceCost', 'VehiclesController@editMonthlyInsuranceCost');
    Route::post('/vehicles/editMonthlyTelematicsCost', 'VehiclesController@editMonthlyTelematicsCost');

    Route::post('/vehicles/editMonthlyInsuranceCostOverride', 'VehiclesController@editMonthlyInsuranceCostOverride');
    Route::post('/vehicles/editMonthlyTelematicsCostOverride', 'VehiclesController@editMonthlyTelematicsCostOverride');

    Route::post('/vehicles/editDepreciationCost', 'VehiclesController@editDepreciationCost');
    Route::post('/vehicles/checkRegistration', 'VehiclesController@checkRegistration');
    Route::post('/vehicles/checkWebfleetRegistration', 'VehiclesController@checkWebfleetRegistration');

	Route::post('/vehicles/vehicleAssignment/add', 'VehiclesController@addAssignmentUpdateHistory');
    Route::post('/vehicles/vehicleAssignment/{id}/edit', 'VehiclesController@editAssignmentUpdateHistory');
    Route::get('/vehicles/vehicleAssignment/{id}/get', 'VehiclesController@getAssignmentHistory');
    Route::post('/assignmentHistory/delete', 'VehiclesController@deleteAssignmentHistory');

    Route::post('/vehicles/checkDateAddedToFleet', 'VehiclesController@checkDateAddedToFleet');
    Route::post('/vehicles/addEvent', 'VehiclesController@addEvent');
    Route::post('/vehicles/get_all_events', 'VehiclesController@getAllMaitenanceEvent');
    Route::post('/vehicles/update_event_name', 'VehiclesController@updateEventName');
    Route::post('/vehicles/delete_event', 'VehiclesController@deleteEvent');
});

Route::group(['middleware' => ['auth', 'cors', 'can:telematics.manage']], function () {
    Route::get('/telematics', 'TelematicsController@index')->name('telematics.index');
    Route::get('/telematics/resetTelematicsTab', 'TelematicsController@resetTelematicsTab')->name('telematics.reset-tab');
    Route::get('/telematics/getTelematicsData', 'TelematicsController@getTelematicsData');
    Route::get('/telematics/getVehiclesOnFleet', 'TelematicsController@getVehiclesOnFleet');
    Route::get('/telematics/getActiveVehiclesOnFleet', 'TelematicsController@getActiveVehiclesOnFleet');
    Route::get('/telematics/getInActiveVehiclesOnFleet', 'TelematicsController@getInActiveVehiclesOnFleet');
    Route::post('/telematics/markerDetails', 'TelematicsController@markerDetails');
    Route::post('/telematics/incidentMarkerDetails', 'TelematicsController@incidentMarkerDetails');
    Route::post('/telematics/getSearchedTelematicsData', 'TelematicsController@getSearchedTelematicsData');
    Route::post('/telematics/getAllLocations', 'TelematicsController@getAllLocations');
    Route::post('/telematics/getLocationmarkerDetails', 'TelematicsController@getLocationmarkerDetails');

    Route::post('/telematics/journeyMarkerDetails', 'TelematicsController@journeyMarkerDetails');
    Route::any('/telematics/journey/data', 'TelematicsController@anyJourneyData');
    Route::any('/telematics/fetchScores', 'TelematicsController@getSafetyAndEfficiencyScore');
    Route::any('/telematics/downloadAndRemoveFile', 'TelematicsController@downloadAndRemoveFile');
    Route::post('/telematics/getTrendScore', 'TelematicsController@getTrendScore');
    Route::post('/telematics/getSafetyScore', 'TelematicsController@getSafetyScore');
    Route::post('/telematics/efficiencyScore', 'TelematicsController@efficiencyScore');

    Route::get('/telematics/getBehavioursData','TelematicsController@getBehaviourTabData');
    Route::any('/telematics/getIncidentsGridData','TelematicsController@getIncidentsGridData');
    Route::post('/telematics/getIncidentsData','TelematicsController@getIncidentsData');
    Route::any('/telematics/getJourneyData','TelematicsController@getJourneyData');
    Route::post('/telematics/getJourneyDetails','TelematicsController@getJourneyDetails');
    Route::post('/telematics/getMultipleJourneyDetails','TelematicsController@getMultipleJourneyDetails');
    Route::post('/telematics/getVehicleData','TelematicsController@getVehicleData');
    //start live tab data
    Route::post('/telematics/getTelematicsLiveTabVehicleData','TelematicsController@getTelematicsLiveTabVehicleData');
    Route::get('/telematics/getTelematicsLiveTabVehicleDetail','TelematicsController@getTelematicsLiveTabVehicleDetail');
    Route::post('/telematics/getTelematicsLiveTabUserData','TelematicsController@getTelematicsLiveTabUserData');
    Route::get('/telematics/getTelematicsLiveTabUserVehicleDetail','TelematicsController@getTelematicsLiveTabUserVehicleDetail');
    Route::post('/telematics/getTelematicsLiveTabLocationCategoryList','TelematicsController@getTelematicsLiveTabLocationCategoryList');
    Route::get('/telematics/getTelematicsLiveTabCategoryLocationList','TelematicsController@getTelematicsLiveTabCategoryLocationList');
    Route::get('/telematics/getTelematicsLiveTabLocationDetail','TelematicsController@getTelematicsLiveTabLocationDetail');
            //for chart   
    Route::any('/telematics/getTelematicsLiveTabVehicleJourneyDetail','TelematicsController@getTelematicsLiveTabVehicleJourneyDetail');   
    Route::any('/telematics/getTelematicsLiveTabVehicleJourneyDetailByLatLong','TelematicsController@getTelematicsLiveTabVehicleJourneyDetailByLatLong');   
    //end live tab data
    Route::get('/telematics/getAllVehiclesOnFleet', 'TelematicsController@getAllVehiclesOnFleet');
    Route::get('/telematics/createZone', 'TelematicsController@createZone')->name('telematics.createZone');
    Route::post('/telematics/storeZone', 'TelematicsController@storeZone');
    Route::get('/telematics/zoneDetails/{id}', 'TelematicsController@zoneDetails')->name('telematics.zoneDetails');
    Route::get('/telematics/editZone/{id}', 'TelematicsController@editZone')->name('telematics.editZone');
    Route::post('/telematics/updateZone', 'TelematicsController@updateZone');
    Route::any('/telematics/getZoneData', 'TelematicsController@getZoneData');
    Route::any('/telematics/getZoneAlertsData', 'TelematicsController@getZoneAlertsData');
    Route::post('/telematics/getZoneAlertMapData', 'TelematicsController@getZoneAlertMapData');
    Route::post('/telematics/zoneAlertMarkerDetails', 'TelematicsController@zoneAlertMarkerDetails');

    Route::post('telematics/deleteZone/{id}', 'TelematicsController@destroyZone');
    
    Route::post('/telematics/search-journey-location', 'TelematicsController@searchJourneyLocation');
});

Route::group(['middleware' => ['auth', 'cors', 'can:fleet.planning']], function () {
    Route::get('fleet_planning', 'VehiclesController@fleet');
    Route::get('vehicles/planning', 'VehiclesController@planning');
    Route::any('vehicles/planning_data', 'VehiclesController@planningData');

    //Route::any('vehicles/data', 'VehiclesController@anyData');
    //Route::post('vehicles/store', 'VehiclesController@store');
    //Route::post('vehicles/update/{id}', 'VehiclesController@update');
    //Route::any('vehicles/vehicle_type_data/{id}', 'VehiclesController@getVehicleTypeData');

    Route::post('vehicles/storeComment', 'VehiclesController@storeComment');
    Route::post('vehicles/updateComment', 'VehiclesController@updateComment');
    Route::any('vehicles/downloadPlanningMedia/{id}', 'VehiclesController@downloadPlanningMedia');
    Route::delete('vehicles/delete_comment/{id}', 'VehiclesController@destroyComment');

    Route::get('vehicles/upload/{id}', 'VehiclesController@anyupload');
    Route::any('vehicles/get_store_docs/{id}', 'VehiclesController@anyVechileDocs');
    Route::any('vehicles/getVechileDocs/{id}', 'VehiclesController@getVechileDocs');
    Route::any('vehicles/get_media_url/{id}', 'VehiclesController@getMediaUrl');
    Route::any('vehicles/vehicle_docs_list/{id}', 'VehiclesController@getVehicleDocsList');
    Route::any('vehicles/get_vehicle_maintenance_docs/{vehicleMaintenanceHistoryId}', 'VehiclesController@vehicleMaintenanceDocs');
    Route::any('vehicles/upload_vehicle_maintenance_docs', 'VehiclesController@uploadVehicleMaintenanceDocs');
    Route::delete('vehicles/delete_maintenance_docs/{id}', 'VehiclesController@deleteVechileMaintenanceDocs');
    Route::any('vehicles/adv_search_filter/{manufacturer}/{model}/{type}', 'VehiclesController@anyAdvSearchFilter');
    Route::delete('vehicles/delete_docs/{id}', 'VehiclesController@deleteVechileDocs');
    Route::delete('vehicles/delete_documets/{id}', 'VehiclesController@destroyVehicleDocuments');
    // Route::resource('vehicles', 'VehiclesController');
    Route::get('/planner', 'PlannerController@index');
    Route::get('/planner/getPlannerDetails', 'PlannerController@getPlannerDetails');
    Route::post('/planner/getSelectedDateData/{date}', 'PlannerController@getSelectedDateData');
    Route::post('/planner/getSelectedEventData', 'PlannerController@getSelectedEventData');
    Route::any('/planner/exportDayEvents/{date}/{filter}', 'PlannerController@exportDayEvents');
    Route::any('/planner/exportSelectedEvents/{date}/{filter}', 'PlannerController@exportSelectedEvents');
    Route::any('/planner/get-12-months-calendar/{year}', 'PlannerController@get12MonthsCalendar');

    Route::post('vehicles/updateDateForArchivedVehicleStatuses', 'VehiclesController@updateDateForArchivedVehicleStatuses');
});

// defects routes...
Route::group(['middleware' => ['auth', 'cors', 'can:defect.manage']], function () {

    Route::any('defects/data', 'DefectsController@anyData');
    Route::resource('defects', 'DefectsController');
    Route::post('defects/storeComment', 'DefectsController@storeComment');
    Route::delete('defects/delete_comment/{id}', 'DefectsController@destroyComment');
    Route::delete('defects/delete_duplicate/{id}', 'DefectsController@destroyDuplicate');
    Route::post('defects/updateComment', 'DefectsController@updateComment');
    Route::post('defects/updateDetails', 'DefectsController@updateDetails');
    Route::any('defects/downloadMedia/{id}', 'DefectsController@downloadMedia');
    Route::any('defects/exportPdf/{defects}', 'DefectsController@exportPdf');
    Route::any('defects/exportNotePdf/{defects}', 'DefectsController@exportDefectNote');
    Route::any('defects/exportWord/{defects}', 'DefectsController@exportWord');
    Route::get('vehicles/{id}/defects', 'VehiclesController@showVehicleDefects');
    Route::get('profiles/{id}/vehicles', 'VehiclesController@profileStatus');
    Route::post('getDefectComments/{id}', 'DefectsController@getDefectComments');
    Route::post('defects/updateEstimatedDefectCost', 'DefectsController@updateEstimatedDefectCost');
    Route::post('defects/updateActualDefectCost', 'DefectsController@updateActualDefectCost');
    Route::post('defects/updateDefectStatus', 'DefectsController@updateDefectStatus');

});

// defects routes...
/*Route::group(['middleware' => ['auth', 'cors', 'can:workshopuser.manage']], function () {
    Route::get('/', 'DefectsController@index');
    Route::get('/home','DefectsController@index');
    Route::any('defects/data', 'DefectsController@anyWorkshopUserData');
    Route::resource('defects', 'DefectsController');
    Route::post('defects/storeComment', 'DefectsController@storeComment');
    Route::delete('defects/delete_comment/{id}', 'DefectsController@destroyComment');
    Route::delete('defects/delete_duplicate/{id}', 'DefectsController@destroyDuplicate');
    Route::post('defects/updateComment', 'DefectsController@updateComment');
    Route::post('defects/updateDetails', 'DefectsController@updateDetails');
    Route::any('defects/downloadMedia/{id}', 'DefectsController@downloadMedia');
    Route::any('defects/exportPdf/{defects}', 'DefectsController@exportPdf');
    Route::any('defects/exportNotePdf/{defects}', 'DefectsController@exportDefectNote');
    Route::any('defects/exportWord/{defects}', 'DefectsController@exportWord');

    Route::get('vehicles/{id}/defects', 'VehiclesController@showVehicleDefects');
    //Route::resource('checks', 'ChecksController');//needed for checks/create
});*/

// report routes...
// Route::group(['middleware' => ['auth', 'cors', 'can:report.manage']], function () {
//     Route::get('/reports/fleetCost/{period}', 'ReportsController@downloadFleetCostReport');
//     Route::get('reports/download/lastlogin', 'ReportsController@downloadLastLogin');
//     Route::get('reports/download/{name}/{period?}', 'ReportsController@downloadReport');
// 	Route::get('reports/regionwise/download/{key}/{period?}', 'ReportsController@downloadReportRegionwise');
// 	Route::resource('reports', 'ReportsController');
// });

// custom report routes...
Route::group(['middleware' => ['auth', 'can:report.manage']], function () {
    Route::any('reports/data', 'CustomReportController@anyData');
    Route::any('reports/download_data', 'CustomReportController@anyReportDownloadData');
    Route::any('reports/report_data', 'CustomReportController@anyReportData');
    Route::post('reports/view_all_report_categories', 'CustomReportController@viewAllReportCategories');
    Route::post('reports/addcategory', 'CustomReportController@addReportCategory')->name('reports.addcategory');
    Route::post('reports/update_category_name', 'CustomReportController@updateCategoryName');
    Route::post('reports/delete_category', 'CustomReportController@deleteReportCategory');
    Route::post('reports/get_category_dataset', 'CustomReportController@getCateoryDataset');
    Route::get('reports/{id}/custom_report', 'CustomReportController@getCustomReports');
    Route::post('reports/{id}/get_report_columns', 'CustomReportController@getReportColumns');
    Route::post('reports/{id}/download_report_criteria', 'CustomReportController@downloadReportCriteria');
    Route::post('reports/save_download_report', 'CustomReportController@saveDownloadReport');
    Route::delete('reports/download/{id}', 'CustomReportController@deleteDownloadReport');
    Route::post('reports/update_dataset_order', 'CustomReportController@updateDatasetOrder');
    Route::post('reports/generate_report', 'CustomReportController@generateCustomReport');
    Route::resource('reports', 'CustomReportController');
});

// incident routes
Route::group(['middleware' => ['auth', 'cors', 'can:incident.manage', 'incident.report']], function () {
    Route::any('incidents/data', 'IncidentsController@anyData');
    Route::resource('incidents', 'IncidentsController');
    Route::post('incidents/storeComment', 'IncidentsController@storeComment');
    Route::delete('incidents/delete_comment/{id}', 'IncidentsController@destroyComment');
    Route::post('getIncidentComments/{id}', 'IncidentsController@getIncidentComments');
    Route::post('incidents/updateComment', 'IncidentsController@updateComment');
    Route::any('incidents/downloadMedia/{id}', 'IncidentsController@downloadMedia');
    Route::any('incidents/exportPdf/{incidents}', 'IncidentsController@exportPdf');
    Route::post('incidents/updateDetails', 'IncidentsController@updateDetails');

    Route::get('vehicles/{id}/incidents', 'VehiclesController@showVehicleIncidents');

    Route::post('incidents/createreport', 'IncidentsController@createIncidentReport');
    Route::any('incidents/upload_incident_images', 'IncidentsController@uploadIncidentImages');
    Route::delete('incidents/delete_incident_image/{id}', 'IncidentsController@deleteIncidentImage');
});

// API Routes
$api = app('Dingo\Api\Routing\Router');

/*$api->group(["version"=>"v1", "prefix"=>"api/v1", 'middleware' => 'cors'], function($api){
    $api->post('auth/login', 'App\Http\Controllers\Api\v1\APIController@login');
    $api->post('test', ['before'=>'jwt-auth', 'uses'=>'App\Http\Controllers\Api\v1\VersionController@testjwt']);
});*/
// Login Via Google
Route::group(['middleware' => ['guest','frameGuard']], function () {
    Route::get('/login', function () {
        return view('auth.googleLogin');
    });
});
Route::get('auth/successreset', 'Auth\PasswordController@successReset');
// Authentication routes...
/*Route::get('auth/login', 'Auth\AuthController@getLogin');*/
Route::group(['middleware' => ['frameGuard']], function () {
    Route::post('auth/login', 'Auth\AuthController@postLogin');
    Route::get('auth/logout', 'Auth\AuthController@getLogout');
    Route::get('/googleLogin', 'Auth\AuthController@login');
    Route::post('password/email', 'Auth\PasswordController@postEmail');
    Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
    Route::post('password/reset', 'Auth\PasswordController@postReset');

    Route::post('/checkEmailExists', 'Auth\PasswordController@isEmailExists');

    // routes for user set password (registration time)
    Route::get('users/verification/{key}', 'UsersController@setPassword')->name('users.verification');
    Route::post('/passwordactivate', [
        'as' => 'user.password', 'uses' => 'UsersController@savePassword'
    ]);
});

// conbined route for workshop and user
Route::group(['middleware' => ['auth', 'cors']], function () {
    Route::get('users/get_enabled_users', 'UsersController@anyGetEnabledUsers');
    Route::post('telematics/locations/addCategory', 'LocationCategoryController@store')->name('telematics.addCategory');
    Route::post('locations/viewAllCategories', 'LocationCategoryController@viewAllCategories')->name('locations.viewAllCategories');
    Route::post('locations/updateCategoryName', 'LocationCategoryController@updateCategoryName')->name('locations.updateCategoryName');
    Route::post('locations/deleteCategory', 'LocationCategoryController@deleteCategory')->name('locations.deleteCategory');
    Route::post('telematics/locations/data', 'LocationController@anyData');
    Route::get('locations/create', 'LocationController@create')->name('locations.create');
    Route::post('locations/store', 'LocationController@store')->name('locations.store');
    Route::get('locations/{id}/edit', 'LocationController@edit')->name('locations.edit');
    Route::put('locations/{id}', 'LocationController@update')->name('locations.update');
    Route::post('telematics/location/delete/{id}', 'LocationController@destroy');

    // need to be confirmed from mukesh bhai
    Route::any('users/get_user_divisions', 'UsersController@getUserDivisions');
    Route::any('users/get_user_regions', 'UsersController@getUserRegions');
});

// Users routes...
Route::group(['middleware' => ['auth', 'cors', 'can:user.manage']], function () {
    Route::post('users/addCompany', 'UsersController@addCompany')->name('user.addCompany');
    Route::any('users/data', 'UsersController@anyData');
    Route::any('users/export', 'UsersController@anyExport');
    // Route::post('users/checkEmail', 'UsersController@checkEmail');
    Route::post('checkUserEmail', 'UsersController@checkEmail');
    Route::post('users/checkUsernameAvailability', 'UsersController@checkUsernameAvailability');
    Route::post('users/changePassword', 'UsersController@changePassword');
    Route::post('users/resetpasswordadmin/{id}', 'UsersController@resetpasswordadmin');
    Route::any('users/disable/{id}', 'UsersController@anyDisable');
    Route::any('users/enable/{id}', 'UsersController@anyEnable');
    Route::any('users/getLineManagerData/{id}', 'UsersController@getLineManagerData');
    Route::post('users/resendInvitation/{id}', 'UsersController@resendInvitation')->name('user.resend.invitation');
    Route::get('users/vehicle_history/{id}', 'UsersController@userVehicleHistory');
    Route::get('users/vehicle_history/private_use/{id}', 'UsersController@getPrivateUseLogs');
    Route::any('users/private_use_logs/data/{id}', 'UsersController@getUserVehiclePrivateUseData');
    Route::any('users/vehicle_history/privateUse/store', 'UsersController@storePrivateUseData');
    Route::any('users/vehicle_history/privateUse/edit', 'UsersController@editPrivateUseData');
    Route::any('users/vehicle_history/privateUse/update', 'UsersController@updatePrivateUseData');
    Route::any('users/vehicle_history/data/{id}', 'UsersController@getUserVehicleHistoryData')->name('user.vehicle.history');

    Route::post('update/vehicle_history/{id}', 'UsersController@updateVehicleHistoryDates');
    Route::any('users/vehicle_history/privateUseLog/delete', 'UsersController@deletePrivateUseDates');
    Route::any('users/vehicle/history/exportPdf/{id}', 'UsersController@exportVehicleHistoryPdf');

    Route::post('users/isDallasKeyExist', 'UsersController@checkIsDallasKeyExist')->name('user.check.dallaskey');
    Route::resource('users', 'UsersController');
});

// workshop user routes
Route::group(['middleware' => ['auth', 'cors', 'can:workshopuser.manage']], function () {
    Route::post('workshop-users/addCompany', 'WorkshopsController@addCompany')->name('user.addCompany');
    Route::any('workshop-users/data', 'WorkshopsController@anyData');
    Route::any('workshop-users/disable/{id}', 'WorkshopsController@anyDisable');
    Route::any('workshop-users/enable/{id}', 'WorkshopsController@anyEnable');
    Route::post('workshop-users/resendInvitation/{id}', 'WorkshopsController@resendInvitation')->name('workshopusers.resend.invitation');
    Route::resource('workshops', 'WorkshopsController');
    Route::post('workshops/checkEmail', 'WorkshopsController@checkEmail');
    Route::post('workshop-users/view_all_companies', 'WorkshopsController@viewAllCompanies');
    Route::post('workshop-users/update_company_name', 'WorkshopsController@updateCompanyName');
    Route::post('workshop-company/delete', 'WorkshopsController@companyDelete');

});


Route::group(['middleware' => ['auth', 'cors', 'can:profiles.manage']], function () {
    Route::any('profiles/data', 'VehicleTypesController@anyData');
    Route::any('profiles/calcSettingsHmrc', 'VehicleTypesController@calcSettingsHmrc');
    //Route::any('profiles/vehicle_tax/add', 'VehicleTypesController@addVehicleTax');
    Route::any('profiles/checkUniqueType', 'VehicleTypesController@checkUniqueType');
    Route::resource('profiles', 'VehicleTypesController');
    //Route::post('profiles/saveAnnualVehicleTaxListingField', 'VehicleTypesController@saveAnnualVehicleTaxListingField');
    Route::post('profiles/editVehicleTax', 'VehicleTypesController@editVehicleTax');
    Route::post('profiles/editVehicleInsurance', 'VehicleTypesController@editVehicleInsurance');
    Route::post('profiles/getvehicleinsurancedetails/{id}', 'VehicleTypesController@updatedVehicleInsuranceData');
});
Route::post('image/upload', 'ImageController@uploadMedia');

Route::group(['middleware' => ['auth', 'cors', 'can:messaging.manage']], function () {
    Route::resource('messages', 'MessagesController');
    Route::any('messages/data', 'MessagesController@anyData');
    Route::get('messages/paginate', 'MessagesController@paginate');
    Route::any('messages/report/{id}', 'MessagesController@report');
    Route::post('messages/{id}/getMessageRecipient', 'MessagesController@getMessageRecipient');
    Route::post('messages/{id}/getMessageContent', 'MessagesController@getMessageContent');
    Route::get('messages/{id}/statusReport', 'MessagesController@downloadMessageStatusReport');

    Route::post('templates/questionImage', 'TemplatesController@upload');
    Route::post('templates/attachment', 'TemplatesController@uploadAttachment');
    Route::resource('templates', 'TemplatesController',[
        'as' => 'prefix'
    ]);
    Route::resource('groups', 'GroupsController');
});

$api->group(["version" => "v1", "prefix" => "api/v1"], function ($api) {
    $api->post('users/login', 'App\Http\Controllers\Api\v1\UsersController@authenticateUser');
    $api->post('users/forgotPassword', 'App\Http\Controllers\Api\v1\UsersController@forgotPassword');
    $api->post('users/changePassword', 'App\Http\Controllers\Api\v1\UsersController@changePassword');
    $api->post('users/storeLogoutState', 'App\Http\Controllers\Api\v1\UsersController@storeLogoutState');

    $api->post('appversion', 'App\Http\Controllers\Api\v1\VersionController@apkVersion');

    // Desktop api calls that need to review later
    $api->post('vehicled/{action}', 'App\Http\Controllers\Api\v1\VehicleController@check')->where('action', 'checkout|on-call|checkin|defect|vehiclehistory');
    $api->post('surveyd/screen', 'App\Http\Controllers\Api\v1\SurveyController@screenJson');
    $api->post('imaged/upload', 'App\Http\Controllers\Api\v1\ImageController@uploadMedia');
    $api->post('checkd/{action}', 'App\Http\Controllers\Api\v1\CheckController@store')->where('action', 'checkout|on-call|checkin|defect');
    $api->post('checks/mapImage', 'App\Http\Controllers\Api\v1\CheckController@checkMapImage');
    // $api->post('telematics/addData', 'App\Http\Controllers\Api\v1\TelematicsController@addTelematicsData');
    
    //--$api->post('telematics/telematicsJourneyEnd', 'App\Http\Controllers\Api\v1\TelematicsController@telematicsJourneyEnd');
    //--$api->post('telematics/telematicsJourneyStart', 'App\Http\Controllers\Api\v1\TelematicsController@telematicsJourneyStart');
    //--$api->post('telematics/telematicsJourneyIdling', 'App\Http\Controllers\Api\v1\TelematicsController@telematicsJourneyIdling');
    //--$api->post('telematics/telematicsJourneyOngoing', 'App\Http\Controllers\Api\v1\TelematicsController@telematicsJourneyOngoing');
    //--$api->post('telematics/telematicsJourneyBindUser', 'App\Http\Controllers\Api\v1\TelematicsController@telematicsJourneyBindUser');
    $api->post('telematics/fetchMyVehicleTrakm8Data', 'App\Http\Controllers\Api\v1\TelematicsController@fetchMyVehicleTrakm8Data');
    $api->post('telematics/fetchUserJourneyTrakm8Data', 'App\Http\Controllers\Api\v1\TelematicsController@fetchUserJourneyTrakm8Data');
    $api->post('telematics/fetchJourneyDetailsTrakm8Data', 'App\Http\Controllers\Api\v1\TelematicsController@fetchJourneyDetailsTrakm8Data');
    //--$api->post('telematics/fetchScores', 'App\Http\Controllers\Api\v1\TelematicsController@fetchScores');
    //--$api->post('telematics/fetchBehaviourData', 'App\Http\Controllers\Api\v1\TelematicsController@fetchBehaviourData');
    //--$api->post('telematics/fetchIncidentsData', 'App\Http\Controllers\Api\v1\TelematicsController@fetchIncidentsData');
    // $api->post('telematics/fetchEfficiencyScoreData', 'App\Http\Controllers\Api\v1\TelematicsController@fetchEfficiencyScoreData');
    //--$api->post('telematics/fetchJourneyData', 'App\Http\Controllers\Api\v1\TelematicsController@fetchJourneyData');
    //--$api->post('telematics/fetchJourneyDetails', 'App\Http\Controllers\Api\v1\TelematicsController@fetchJourneyDetails');

    $api->get('getTrailerReferenceNumber', 'App\Http\Controllers\Api\v1\CheckController@getTrailerReferenceNumber');
    //--$api->post('telematics/getMarkerDetails', 'App\Http\Controllers\Api\v1\TelematicsController@getMarkerDetails');
    //--$api->post('telematics/getPopulateVehiclesOnFleetArray', 'App\Http\Controllers\Api\v1\TelematicsController@getPopulateVehiclesOnFleetArray');
    //--$api->post('telematics/getActiveVehiclesOnFleet', 'App\Http\Controllers\Api\v1\TelematicsController@getActiveVehiclesOnFleet');
    //--$api->post('telematics/getTelematicsData', 'App\Http\Controllers\Api\v1\TelematicsController@getTelematicsData');
    $api->post('telematics/dataPush', 'App\Http\Controllers\Api\v1\TelematicsController@dataPusher');

    $api->get('configration', 'App\Http\Controllers\Api\v1\VersionController@projectConfigration');
    //$api->post('telematics/vehicleTakeout', 'App\Http\Controllers\Api\v1\TelematicsController@vehicleTakeout');
    //$api->post('telematics/vehicleReturn', 'App\Http\Controllers\Api\v1\TelematicsController@vehicleReturn');

    $api->get('get_message_attachment_url/{mediaid}', 'App\Http\Controllers\Api\v1\MessagesController@getMessageAttachmentUrl');
    $api->post('acknowledge_message', 'App\Http\Controllers\Api\v1\MessagesController@acknowledgeMessage');
});

$api->group(["version" => "v1", "prefix" => "api/v1", 'middleware' => ['jwt.auth']], function ($api) {
    $api->post('vehicle/{action}', 'App\Http\Controllers\Api\v1\VehicleController@check')->where('action', 'checkout|on-call|checkin|defect|vehiclehistory|resolvedefect');
    $api->post('vehicle/all', 'App\Http\Controllers\Api\v1\VehicleController@allVehicles');
    $api->get('getVehicleDetails/{id}', 'App\Http\Controllers\Api\v1\VehicleController@getVehicleDetails');
    $api->post('getVehiclesDetail', 'App\Http\Controllers\Api\v1\VehicleController@getVehiclesDetail');
    $api->post('vehicle/history', 'App\Http\Controllers\Api\v1\VehicleController@history');
    $api->post('startup', 'App\Http\Controllers\Api\v1\VersionController@check');
    $api->post('survey/questions', 'App\Http\Controllers\Api\v1\SurveyController@questionSet');
    $api->post('survey/screen', 'App\Http\Controllers\Api\v1\SurveyController@screenJson');
    $api->post('check', 'App\Http\Controllers\Api\v1\CheckController@show');
    $api->post('check/{action}', 'App\Http\Controllers\Api\v1\CheckController@store')->where('action', 'checkout|on-call|checkin|defect');
    $api->get('checktest', 'App\Http\Controllers\Api\v1\CheckController@storetest');
    // sync api route
    $api->post('sync', 'App\Http\Controllers\Api\v1\SyncController@store');
    // sync image route
    $api->post('image/upload', 'App\Http\Controllers\Api\v1\ImageController@uploadMedia');
    // GCM registration route for a user
    $api->post('notification/register', 'App\Http\Controllers\Api\v1\UsersController@postRegisterPushService');
    $api->post('push_message/acknowledge', 'App\Http\Controllers\Api\v1\PushMessagesController@postAcknowledgePushReceipt');
    $api->post('message/text', 'App\Http\Controllers\Api\v1\MessagesController@text');
    $api->post('message/storeResponse', 'App\Http\Controllers\Api\v1\MessagesController@storeResponse');
    $api->post('message/fetchAll', 'App\Http\Controllers\Api\v1\MessagesController@fetchAll');

    $api->get('get_insurance_detail', 'App\Http\Controllers\Api\v1\InsuranceController@getInsuranceDetail');
    $api->post('save_incident_detail', 'App\Http\Controllers\Api\v1\InsuranceController@saveIncidentDetail');

    $api->get('get_workshop_companies', 'App\Http\Controllers\Api\v1\WorkshopsController@getWorkshopCompanies');
    $api->post('resolve_defect', 'App\Http\Controllers\Api\v1\DefectsController@resolveDefect');
    $api->post('get-defect-images/{id}', 'App\Http\Controllers\Api\v1\DefectsController@getDefectImages');
});

$api->group(["version" => "v1", "prefix" => "api/v2", 'middleware' => ['jwt.auth']], function ($api) {
    $api->post('vehicle/all', 'App\Http\Controllers\Api\v2\VehicleController@allVehicles');
});

Route::get('/privacypolicy', function () {
    return view('privacy_policy');
});
Route::get('/cookiepolicy', function () {
    return view('cookie_policy');
});

Route::get('/convertjson', 'ChecksController@convertJson');
Route::get('/', 'DashboardController@checkUA');
Route::get('/apps', 'DashboardController@apps');
Route::get('/apps/{os}', function ($os) {
    return view('dashboard.instructions', compact('os'));
})->where('os', 'android|ios');

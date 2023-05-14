<?php

namespace App\Console;

use App\Console\Commands\VehicleMaitenanceHistroyMigrateEventTypeId;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\SurveyJson::class,
        \App\Console\Commands\UpdateCheckJson::class,
        \App\Console\Commands\TemporaryJson::class,
        \App\Console\Commands\LoadVehicleTypeImages::class,
        \App\Console\Commands\DeleteNotifications::class,
        \App\Console\Commands\GenerateBrandStyles::class,
        \App\Console\Commands\AddCO2TaxData::class,
        \App\Console\Commands\CheckAndFinalizeP11dReport::class,
        \App\Console\Commands\moveCheckMapImageToS3::class,
        \App\Console\Commands\SynchronizeVehicleData::class,
        \App\Console\Commands\SynchronizeUserData::class,
        \App\Console\Commands\VehicleMaintenanceNotifications::class,
        \App\Console\Commands\WeeklyMaintenanceNotifications::class,
        \App\Console\Commands\VehicleTaxRenew::class,
        \App\Console\Commands\CheckProfileServiceInterval::class,
        \App\Console\Commands\UpdateDivisonRegionLinkings::class,
        \App\Console\Commands\UpdateDivisionReagionLocationName::class,
        \App\Console\Commands\DropColumnVehileUser::class,
        \App\Console\Commands\UpdateVehicleNextPmiDateInterval::class,
        \App\Console\Commands\DailyCreationMaintenanceEvents::class,
        \App\Console\Commands\UpdateCreatedByUpdatedByIdForAllTables::class,
        \App\Console\Commands\UserRecordInsert::class,
        \App\Console\Commands\DailyDistanceWiseMaintenanceCreationEvents::class,
        VehicleMaitenanceHistroyMigrateEventTypeId::class,
        \App\Console\Commands\VehicleMaintenanceServiceDistanceNotification::class,
        \App\Console\Commands\WebFleet\getJourney::class,
        \App\Console\Commands\WebFleet\MigrateWebFleetVehicles::class,
        \App\Console\Commands\WebFleet\updateJourney::class,
        \App\Console\Commands\WebFleet\updatePastJourney::class,
        \App\Console\Commands\WebFleet\getJourneyDetails::class,
        \App\Console\Commands\WebFleet\getIdleIncidents::class,
        \App\Console\Commands\WebFleet\getAccelerationIncidents::class,
        \App\Console\Commands\WebFleet\getSpeedingIncidents::class,
        \App\Console\Commands\WebFleet\UpdateVehiclesForLiveTab::class,
        \App\Console\Commands\GenerateWeeklyStandardReport::class,
        \App\Console\Commands\GenerateMonthlyStandardReport::class,
        \App\Console\Commands\VehiclePMIEventCorrection::class,
        // \App\Console\Commands\GenerateDailyStandardReport::class,
        \App\Console\Commands\WebFleet\CreateVehicleCheckEntries::class,
        \App\Console\Commands\WebFleet\CreateVehicleReturnEntries::class,
        \App\Console\Commands\WebFleet\updateJourneyScore::class,
        \App\Console\Commands\WebFleet\UpdateJourneyDetailAdress::class,
        \App\Console\Commands\TelematicsLoadJourneySummary::class,
        \App\Console\Commands\updateVehicleLastLocation::class,
        \App\Console\Commands\TestRedis::class,
        \App\Console\Commands\UpdateJDCalculatedFields::class,
        \App\Console\Commands\DeleteInvalidTelematicsJourneyDetailsEntries::class,
	    \App\Console\Commands\CheckTMJourneys::class,
        \App\Console\Commands\AddNewIncidentCountAndUpdateTotalCount::class,
        \App\Console\Commands\FixRetrospectiveSpeedData::class,
        \App\Console\Commands\TelematicsAnamoliesReport::class,
        \App\Console\Commands\TelematicsBindUserDriverId::class,
        \App\Console\Commands\RemovePreviousWeekdayRedisJourneys::class,
        \App\Console\Commands\RemoveIncorrectSpeedingGPSPoints::class,
        \App\Console\Commands\PopulateCurrentOpenJourneysToRedis::class,
        \App\Console\Commands\RemoveDuplicateEvents::class,
        \App\Console\Commands\webfleetUpdateVehicleLastJourney::class,
        \App\Console\Commands\UpdateUserForChecksAlert::class,
        \App\Console\Commands\AddPmiDateEntriesMaintennaceHistoryTable::class,
        \App\Console\Commands\SynchronizeFuelCostData::class,
        \App\Console\Commands\Teletracnavman\GetVehiclesData::class,
        \App\Console\Commands\FixTelematicsJourneyIds::class,
        \App\Console\Commands\FixDailyIncidentCounts::class,
        \App\Console\Commands\FixRetrospectiveIncidentCounts::class,
        \App\Console\Commands\TTNDATA::class,
        \App\Console\Commands\Reports::class,
        \App\Console\Commands\Onetime\DeleteMaintenanceEventsExtraIncompleteEntries::class,
        \App\Console\Commands\Onetime\FetchIncompleteMaintenanceEventsDetails::class,
        \App\Console\Commands\DeleteDuplicateChecksEntry::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('inspire')->hourly();
        $schedule->command('delete:notifications')->daily();

        // Below 3 commands related to maintenance events needs to uncomment when we go to QA or Live
        // -----------------------------
        // $schedule->command('vehicle:taxRenew')->daily()->at('00:10');
        // $schedule->command('vehicle:maintenanceNotifications')->daily()->at('00:30');
        // $schedule->command('weekly:maintenanceNotifications')->daily()->at('05:00');

        //$schedule->command('your:command')->daily()->at('11:59')->when(function () use ($dateInDatabase) {

        $schedule->command('add:co2taxdata')->yearly()->at('23:59')
                                            ->when(function () {
                                                return date('d') == 05 && date('m') == 04;
                                            });
        $schedule->command('finalize:p11dreport')->yearly()->at('23:59')
                                            ->when(function () {
                                                return date('d') == 05 && date('m') == 04;
                                            });
        if(env('BRAND_NAME') === 'skanska' || env('BRAND_NAME') === 'mgroupservices' || env('BRAND_NAME') === 'rps') {
            $schedule->command('sync:user-data')->dailyAt('02:30');
            $schedule->command('sync:vehicle-data')->dailyAt('03:00');
        }
        //$schedule->command('add:co2taxdata')->yearlyOn('5 March')->at('11:59');
        //$schedule->command('report:weeklyoperatorreport')->weekly()->mondays()->at('03:00');
        // $schedule->command('vehicle:updateNextPmiDateInterval')->dailyAt('00:00');

        //#6550
        // if(env('BRAND_NAME') !== 'skanska' && env('BRAND_NAME') !== 'mgroupservices') {
        //     $schedule->command('vehicle:automaticCreationMaintenanceEvents')->dailyAt('00:00');
        // }

        $schedule->command('vehicle:maintenanceNotifications')->dailyAt('02:00');

        //#4410
        // $schedule->command('weekly:maintenanceNotifications')->weekly()->mondays()->at('02:00');
        $schedule->command('weekly:maintenanceNotifications')->weekly()->thursdays()->at('08:00');

        // $schedule->command('vehicle:automaticDistanceWiseMaintenanceEventsCreation')->dailyAt('00:00');
        $schedule->command('vehicle:maintenanceServiceDistanceNotifications')->dailyAt('00:00');

        //#6047
        if(env('BRAND_NAME') == 'chanlon') {
            $schedule->command('sync:fuel-cost-data')->weekly()->wednesdays()->at('22:00');
        }

        //WebFleet commands
        if(env('TELEMATICS_PROVIDER') == 'webfleet') {
            $schedule->command('webfleet:updateVehiclesForLiveTab')->everyMinute();
            $schedule->command('webfleet:getJourney')->cron('*/5 * * * *');
            $schedule->command('webfleet:getJourneyDetails')->cron('*/3 * * * *');
            // $schedule->command('webfleet:getIdleIncidents')->cron('*/4 * * * *');
            // $schedule->command('webfleet:getAccelerationIncidents')->cron('*/5 * * * *');
            // $schedule->command('webfleet:getSpeedingIncidents')->cron('*/6 * * * *');
            $schedule->command('webfleet:updateJourney')->cron('*/7 * * * *');
            $schedule->command('vehicle:webfleetUpdateVehicleLastJourney')->cron('*/6 * * * *');

            // $schedule->command('webfleet:CreateVehicleCheckEntries')->dailyAt('04:00');
            // $schedule->command('webfleet:CreateVehicleReturnEntries')->dailyAt('23:00');
        }

        if(env('TELEMATICS_PROVIDER') != 'webfleet') {
            $schedule->command('fm:removePreviousWeekdayRedisJourneys')->dailyAt('00:01');
            $schedule->command('telematics:updateUserForChecksAlert')->dailyAt('00:01');
        }
        if(env('TELEMATICS_PROVIDER') == 'teletrac') {
            $schedule->command('teletrac:GetVehiclesData')->cron('*/30 * * * *');
        }

        //Standard reports
        // $schedule->command('generate:weeklyStandardReport')->weekly()->sundays()->at('23:59');
        // $schedule->command('generate:monthlyStandardReport')->dailyAt('23:59')->when(function () {
        //     return \Carbon\Carbon::now()->endOfMonth()->isToday();
        // });
        // $schedule->command('generate:dailyStandardReport')->dailyAt('00:00');

        if(env('BRAND_NAME') === 'rps') {
            $schedule->command('telematics:remove-duplicate-events')->dailyAt('01:00');
            $schedule->command('telematics:bind-user')->dailyAt('03:30');
            $schedule->command('telematics:FixTelematicsJourneyIds')->dailyAt('01:00');
            $schedule->command('telematics:FixDailyIncidentCounts')->dailyAt('00:05');
        }

        if(env('BRAND_NAME') === 'rps' || env('BRAND_NAME') === 'mgroupservices') {
            $schedule->command('telematics:anamolies-report')->dailyAt('03:40');
        }

    }
}

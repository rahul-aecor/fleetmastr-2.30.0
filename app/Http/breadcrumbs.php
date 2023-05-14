<?php
// Home
/*Breadcrumbs::register('home', function($breadcrumbs)
{
    $breadcrumbs->push('Home', url('/home'));
});*/

//defects breadcrumbs
Breadcrumbs::register('defect', function($breadcrumbs)
{
    $breadcrumbs->push('Vehicle Defects', route('defects.index'));
});

Breadcrumbs::register('defect_details', function($breadcrumbs)
{
	$breadcrumbs->parent('defect');
    $breadcrumbs->push('Details', route('defects.show'));
});

Breadcrumbs::register('defect_add', function($breadcrumbs)
{
    $breadcrumbs->parent('defect');
    //this was previously developed with checks and moved to defects so everything relates to checks
    $breadcrumbs->push('Add', route('checks.create'));
});

//search breadcrumbs
Breadcrumbs::register('search', function($breadcrumbs)
{
    $breadcrumbs->push('Vehicle Search', route('vehicles.index'));
});
Breadcrumbs::register('vehicle_add', function($breadcrumbs)
{
	$breadcrumbs->parent('search');
    $breadcrumbs->push('Add', route('vehicles.create'));
    //$breadcrumbs->push('Details', url('vehicles/{id}',$vehicleid));
});
Breadcrumbs::register('search_details', function($breadcrumbs,$vehicleid)
{
	$breadcrumbs->parent('search');
    $breadcrumbs->push('Details', route('vehicles.show',$vehicleid));
    //$breadcrumbs->push('Details', url('vehicles/{id}',$vehicleid));
});
Breadcrumbs::register('search_details_checks', function($breadcrumbs,$vehicleid)
{
	//echo "<pre>";print_r($vehicleid);echo "</pre>";exit;
	$breadcrumbs->parent('search_details',$vehicleid);
    $breadcrumbs->push('Checks', url('vehicles/{id}/checks',$vehicleid));
});
Breadcrumbs::register('search_details_defects', function($breadcrumbs,$vehicleid)
{
	$breadcrumbs->parent('search_details',$vehicleid);
    $breadcrumbs->push('Defects', url('vehicles/{id}/defects',$vehicleid));
});
Breadcrumbs::register('search_details_edit', function($breadcrumbs,$vehicleid)
{
	$breadcrumbs->parent('search_details',$vehicleid);
    $breadcrumbs->push('Edit', url('vehicles/{id}/edit',$vehicleid));
});

//checks breadcrumbs
Breadcrumbs::register('checks', function($breadcrumbs)
{
    $breadcrumbs->push('Vehicle Checks', route('checks.index'));
});
/*Breadcrumbs::register('vehicle_checks_add', function($breadcrumbs)
{
    $breadcrumbs->parent('checks');
    $breadcrumbs->push('Add', route('checks.create'));
    //$breadcrumbs->push('Details', url('vehicles/{id}',$vehicleid));
});*/
Breadcrumbs::register('check_details', function($breadcrumbs)
{
	$breadcrumbs->parent('checks');
    $breadcrumbs->push('Details', route('checks.show'));
});

//vehicle Types breadcrumbs
Breadcrumbs::register('profiles', function($breadcrumbs)
{
    $breadcrumbs->push('Vehicle Profile', route('profiles.index'));
});

Breadcrumbs::register('profile_details', function($breadcrumbs,$vehicleTypeid)
{
    $breadcrumbs->parent('profiles');
    $breadcrumbs->push('Details', route('profiles.show', ['profiles' => $vehicleTypeid]));
});

Breadcrumbs::register('profile_details_edit', function($breadcrumbs,$vehicleTypeid)
{
    $breadcrumbs->parent('profile_details',$vehicleTypeid);
    $breadcrumbs->push('Edit', url('profiles/{id}/edit',$vehicleTypeid));
});

Breadcrumbs::register('profile_details_add', function($breadcrumbs)
{
    $breadcrumbs->parent('profiles');
    //this was previously developed with checks and moved to defects so everything relates to checks
    $breadcrumbs->push('Add', route('profiles.create'));
});

//vehicle Types breadcrumbs
Breadcrumbs::register('locations', function($breadcrumbs)
{
    $breadcrumbs->push('Locations', route('telematics.index'));
});

Breadcrumbs::register('location_details_add', function($breadcrumbs)
{
    $breadcrumbs->parent('locations');
    //this was previously developed with checks and moved to defects so everything relates to checks
    $breadcrumbs->push('Add', route('locations.create'),['overwriteFirstLink' => array('url'=>route('telematics.reset-tab'),'title'=>'Telematics','class'=>'jv-icon jv-route')]);
});

Breadcrumbs::register('location_details_edit', function($breadcrumbs,$locationId)
{
    $breadcrumbs->parent('locations');
    $breadcrumbs->push('Edit', url('locations/{id}/edit', $locationId),['overwriteFirstLink' => array('url'=>route('telematics.reset-tab'),'title'=>'Telematics','class'=>'jv-icon jv-route')]);
});

Breadcrumbs::register('users', function($breadcrumbs)
{
    $breadcrumbs->push('User Management', route('users.index'));
});

Breadcrumbs::register('user_vehicle_history', function($breadcrumbs)
{
    $breadcrumbs->parent('users');
    $breadcrumbs->push('User Vehicle History', route('user.vehicle.history'));
});

// incident breadcrumbs
Breadcrumbs::register('incident', function($breadcrumbs)
{
    $breadcrumbs->push('Reported Incidents', route('incidents.index'));
});

Breadcrumbs::register('incident_details', function($breadcrumbs)
{
    $breadcrumbs->parent('incident');
    $breadcrumbs->push('Details', route('incidents.show'));
});

// telematics breadcrumbs
Breadcrumbs::register('telematics', function($breadcrumbs)
{
    $breadcrumbs->push('Telematics', route('telematics.index'));
});

// telematics breadcrumbs
Breadcrumbs::register('safety_zone', function($breadcrumbs)
{
    $breadcrumbs->push('Zones', route('telematics.index'));
});

Breadcrumbs::register('telematics_addzone', function($breadcrumbs)
{
    $breadcrumbs->parent('safety_zone');
    $breadcrumbs->push('Add', route('telematics.createZone'),['overwriteFirstLink' => array('url'=>route('telematics.reset-tab'),'title'=>'Telematics','class'=>'jv-icon jv-route')]);
});

Breadcrumbs::register('telematics_editzone', function($breadcrumbs)
{
    $breadcrumbs->parent('safety_zone');
    $breadcrumbs->push('Edit', route('telematics.editZone'),['overwriteFirstLink' => array('url'=>route('telematics.reset-tab'),'title'=>'Telematics','class'=>'jv-icon jv-route')]);
});

Breadcrumbs::register('telematics_zonedetails', function($breadcrumbs)
{
    $breadcrumbs->parent('telematics');
    $breadcrumbs->push('Zone details', route('telematics.zoneDetails'));
});

//Custom report
Breadcrumbs::register('custom_reports', function($breadcrumbs)
{
    $breadcrumbs->push('Reports', route('reports.index'));
});
Breadcrumbs::register('custom_report_create', function($breadcrumbs)
{
    $breadcrumbs->parent('custom_reports');
    $breadcrumbs->push('Create', route('reports.create'));
});
Breadcrumbs::register('custom_report_edit', function($breadcrumbs, $reportid)
{
    $breadcrumbs->parent('custom_reports');
    $breadcrumbs->push('Edit', url('reports/{id}/edit', $reportid));
});


/*
Verb	Path	Action	Route Name
GET		/photo	index	photo.index
GET		/photo/create	create	photo.create
POST	/photo	store	photo.store
GET		/photo/{photo}	show	photo.show
GET		/photo/{photo}/edit	edit	photo.edit
PUT/PATCH	/photo/{photo}	update	photo.update
DELETE	/photo/{photo}	destroy	photo.destroy
*/
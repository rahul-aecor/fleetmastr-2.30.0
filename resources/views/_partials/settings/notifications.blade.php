<form id="storeNotification" class="form-horizontal display-settings" role="form" action="/settings/storeNotification" method="POST" novalidate>
<div class="row">
	<div class="col-md-10">	
		{{ csrf_field() }}
		<div class="row">
		    <div class="col-md-12">
		        <div class="form-text">Individual user email notification settings are managed in the <a href="{{ url('users') }}"><u>User Management</u></a> area.</div>            
		    </div>
		</div>
		<div class="form-group d-flex align-items-center" style="margin-top: 32px;">
		    <label class="col-md-3 control-label align-self-center pt-0">Vehicle defect email notifications:</label>
		    <div class="col-md-4">
		        <label class="checkbox-inline pt-0 toggle_switch toggle_switch--height-auto">
		          <input type="checkbox" id="defectNotification" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
		          name="defect_email_notification" {{ setting('defect_email_notification') == 1 ? 'checked' : '' }}>
		        </label>
		    </div>
		</div>
		<div class="form-group d-flex align-items-center margin-top-20">
		    <label class="col-md-3 control-label align-self-center pt-0">Maintenance reminder notifications:</label>
		    <div class="col-md-4">
		        <label class="checkbox-inline pt-0 toggle_switch toggle_switch--height-auto">
		          <input type="checkbox" id="maintenanceReminderNotification" data-toggle="toggle" data-on="Enabled" data-off="Disabled"
		          name="maintenance_reminder_notification" {{ setting('maintenance_reminder_notification') == 1 ? 'checked' : '' }}>
		        </label>
		    </div>
		</div>
	</div>
</div>
</form>
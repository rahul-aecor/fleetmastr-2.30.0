<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
<title>{{ strtoupper(env('BRAND_NAME')) }} | Telematics - Data Issues</title>
<style type="text/css">
html { -webkit-text-size-adjust:none; -ms-text-size-adjust: none;}
@media only screen and (max-device-width: 680px), only screen and (max-width: 680px) { 
	*[class="table_width_100"] {
		width: 96% !important;
	}
	*[class="border-right_mob"] {
		border-right: 1px solid #dddddd;
	}
	*[class="mob_100"] {
		width: 100% !important;
	}
	*[class="mob_center"] {
		text-align: center !important;
	}
	*[class="mob_center_bl"] {
		float: none !important;
		display: block !important;
		margin: 0px auto;
	}	
	.iage_footer a {
		text-decoration: none;
		color: #929ca8;
	}
	img.mob_display_none {
		width: 0px !important;
		height: 0px !important;
		display: none !important;
	}
	img.mob_width_50 {
		width: 40% !important;
		height: auto !important;
	}
}
.table_width_100 {
	width: 680px;
}
</style>
</head>

<body style="padding: 0px; margin: 0px;" bgcolor="#eceef1">
<div id="mailsub" class="notification" align="center">

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="min-width: 320px;"><tr><td align="center" bgcolor="#eceef1">


<!--[if gte mso 10]>
<table width="680" border="0" cellspacing="0" cellpadding="0">
<tr><td>
<![endif]-->

<table border="0" cellspacing="0" cellpadding="0" class="table_width_100" width="100%" style="max-width: 680px; min-width: 300px;">
	<!--header -->
	<tr><td align="center" bgcolor="#eceef1">
		<!-- padding --><div style="height: 20px; line-height: 20px; font-size: 10px;">&nbsp;</div>
		<table width="96%" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="center">
					<a href="http://{{ env('APP_URL') }}" target="_blank" style="color: #596167; font-family: Arial, Helvetica, sans-serif; font-size: 13px;">
					<font face="Arial, Helvetica, sans-seri; font-size: 13px;" size="3" color="#596167">
					<img src="{{ setting('logo') }}" style="width: 150px;"/>
    				{{-- <div class="brand-product-name"><img src="{{ asset(get_brand_setting('logo.fleet_logo_for_email')) }}" style="height: 27px;"/></div> --}}
					</font>
					</a>				
				</td>
			</tr>
		</table>
		<!-- padding --><div style="height: 20px; line-height: 20px; font-size: 10px;">&nbsp;</div>
	</td></tr>
	<!--header END-->

	<!--content 1 -->
	<tr><td align="center" bgcolor="#ffffff">
		<table width="90%" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="center">
				<!-- padding --><div style="height: 20px; line-height: 20px; font-size: 10px;">&nbsp;</div>
				<div style="line-height: 44px;">
					<font face="Arial, Helvetica, sans-serif" size="5" color="#57697e" style="font-size: 34px;">
					<span style="font-family: Arial, Helvetica, sans-serif; font-size: 34px; color: #57697e;">
						Telematics Data Anomalies Report
					</span></font>
				</div>
				<!-- padding --><div style="height: 20px; line-height: 20px; font-size: 10px;">&nbsp;</div>
			</td></tr>
			<tr><td align="center">
				<div style="line-height: 30px;">
					<font face="Arial, Helvetica, sans-serif" size="5" color="#4db3a4" style="font-size: 17px;">
					<span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: #57697e;">
						Please find attached the file with all Telematics Anomalies:
					</span></font>
				</div>
				<!-- padding --><div style="height: 20px; line-height: 20px; font-size: 10px;">&nbsp;</div>
			</td></tr>
			<tr><td align="center">
						<table width="95%" align="center" border="0" cellspacing="0" cellpadding="0">
							<tr><td align="left">
								<div style="line-height: 24px;">
									<font face="Arial, Helvetica, sans-serif" size="4" color="#57697e" style="font-size: 16px;">
									<span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: #57697e;">
										<ul>
											<li>Rule 1 : Where <b>Max MPH</b> &gt; 100 MPH [Count : <b>{{  $counts['Speed'] }}</b>]</li>
											<li>Rule 2 : Where <b>Distance (Miles)</b> &gt; 1 AND <b>Fuel (Litres)</b> &lt; 0.05 [Count : <b>{{  $counts['Fuel'] }}</b>]</li>
											<li>Rule 3 : Where <b>Distance (Miles)</b> &gt; 1 AND <b>CO2 (Kg)</b> = 0 [Count : <b>{{  $counts['CO2'] }}</b>]</li>
											<li>Rule 4 : Where <b>Odo (End)</b> - <b>Odo (Start)</b> &gt; <b>Distance (Miles)</b> [-1/+1] [Count : <b>{{  $counts['Odo'] }}</b>]</li>
											<li>Vehicles : List of deduped Vehicles [Count : <b>{{  $counts['vehicle'] }}</b>]</li>
											<li>RIFD : List of unmapped RIFD [Count : <b>{{  $counts['rifd'] }}</b>]</li>
											<li>Last journey : Where last journey is &gt; 17 days [Count : <b>{{  $counts['unmovedVehicles'] }}</b>]</li>
										</ul>						
									</span>
									</font>
								</div>
							</td></tr>
						</table>
				<!-- padding --><div style="height: 30px; line-height: 30px; font-size: 10px;">&nbsp;</div>
			</td></tr>
			<tr><td align="center">
				<table width="80%" align="center" border="0" cellspacing="0" cellpadding="0">
					<tr><td align="center">
						<div style="line-height: 24px;">
							<font face="Arial, Helvetica, sans-serif" size="4" color="#57697e" style="font-size: 16px;">
							<span style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: #57697e;">
								Regards,
								<br />
								The <b>fleet</b>mastr Team
							</span></font>
						</div>
					</td></tr>
				</table>
				<!-- padding --><div style="height: 30px; line-height: 30px; font-size: 10px;">&nbsp;</div>
			</td></tr>
		</table>		
	</td></tr>
	<!--content 1 END-->

	<!--footer -->
	<tr><td class="iage_footer" align="center" bgcolor="#eceef1">
		<!-- padding --><div style="height: 20px; line-height: 20px; font-size: 10px;">&nbsp;</div>	
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr><td align="center">
				<font face="Arial, Helvetica, sans-serif" size="3" color="#96a5b5" style="font-size: 13px;">
				<span style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #96a5b5;">
					© <?php echo date("Y"); ?> developed by <a href="https://www.imastr.com" target="_blank"><font color="#2196f3"><b>i</b>mastr</font></a>
				</span></font>				
			</td></tr>			
		</table>

		<!-- padding --><div style="height: 20px; line-height: 20px; font-size: 10px;">&nbsp;</div>	
	</td></tr>
	<!--footer END-->
</table>
<!--[if gte mso 10]>
</td></tr>
</table>
<![endif]-->
 
</td></tr>
</table>
			
</div> 
</body>
</html>

UPDATE vehicle_types SET annual_insurance_cost=(
SELECT JSON_EXTRACT(settings.value, '$.annual_insurance_cost') AS t1 FROM settings WHERE `key` = "fleet_cost_area_detail") 

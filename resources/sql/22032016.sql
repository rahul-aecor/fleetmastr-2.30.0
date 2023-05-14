/* SQL changes as per lanes-group-vehicle-check  ticket#1   Doc:Vehicle_Check_Items_List-v3finalversion-Edit */
/* row 5 to 8 */
UPDATE defect_master SET defect='Engine oil (top up as required)' WHERE defect = 'Engine oil';
UPDATE defect_master SET defect='Coolant (top up as required)' WHERE defect = 'Coolant';
UPDATE defect_master SET defect='Screenwash (use cold water if necessary)' WHERE defect = 'Screenwash';
UPDATE defect_master SET defect='AdBlue (top up as required)' WHERE defect = 'AdBlue';

/*row 44 to 47*/
UPDATE defect_master SET defect_master.order = defect_master.order + 1 WHERE defect_master.order > 1;
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (2,'Leaks','Walk around the vehicle. Is the vehicle free from leaks?','Brake system',1,0,1,'Not roadworthy vehicle must be VOR',1,1),
(2,'Leaks','Walk around the vehicle. Is the vehicle free from leaks?','Fuel leak',1,0,1,'Not roadworthy vehicle must be VOR',1,1),
(2,'Leaks','Walk around the vehicle. Is the vehicle free from leaks?','Oil leak ',1,0,1,'Not roadworthy vehicle must be VOR',1,1),
(2,'Leaks','Walk around the vehicle. Is the vehicle free from leaks?','Water leak ',1,0,1,'Not roadworthy vehicle must be VOR',1,1);

/*row 56 to 65*/
UPDATE defect_master SET page_title='Indicators, Hazards, Auxilliary Lights and Beacons',app_question='Are all indicators, hazards, auxilliary lights and beacons free from defects?' WHERE page_title = 'Indicators, Hazards and Beacons';
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (4,'Indicators, Hazards, Auxilliary Lights and Beacons','Are all indicators, hazards, auxilliary lights and beacons free from defects?','Auxilliary light bulb blown',0,0,1,'Roadworthy but obtain a replacement',1,1);

/*row 77 to 79 CHECK ids at real db all Windscreen*/
UPDATE defect_master SET is_prohibitional=1 WHERE id IN (67,68,69);

/*row 82 CHECK ids at real db for wipers worn*/
UPDATE defect_master SET is_prohibitional=1 WHERE id = 72;

/*row 86 to 92 CHECK ids at real db for mirrors*/
UPDATE defect_master SET is_prohibitional=1 WHERE id IN (76,77,78,79,82);
UPDATE defect_master SET safety_notes = 'Not roadworthy vehicle must be VOR' WHERE id = 80;/*defect = 'Mirrors not aligned'*/

/*row 93 to 94 CHECK ids at real db for horn*/
UPDATE defect_master SET safety_notes = 'Not roadworthy vehicle must be VOR' WHERE id IN (83,84);

/*row 95 to 104 CHECK ids at real db for cctv*/
UPDATE defect_master SET safety_notes = 'Not roadworthy vehicle must be VOR' WHERE id BETWEEN 85 AND 94;

/*row 105 to 113 CHECK ids at real db for tyres*/
UPDATE defect_master SET safety_notes = 'Not roadworthy vehicle must be VOR' WHERE id IN (99,100,102,105);

/*row 114 to 118 CHECK ids at real db for tyres*/
UPDATE defect_master SET safety_notes = 'Not roadworthy vehicle must be VOR' WHERE id =108;

/*row 114 to 118 CHECK ids at real db for tyres*/
UPDATE defect_master SET page_title='Bodywork and Number Plate', app_question='Is the bodywork and number plate free from damage and sharp edges?' WHERE id BETWEEN 109 AND 125;
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (14,'Bodywork and Number Plate','Is the bodywork and number plate free from damage and sharp edges?','Number plate damaged',1,0,1,'Roadworthy if present and legible',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (14,'Bodywork and Number Plate','Is the bodywork and number plate free from damage and sharp edges?','Number plate missing',1,0,1,'Not roadworthy vehicle must be VOR',1,1);


/*row 105 to 113 CHECK ids at real db for tyres*/
UPDATE defect_master SET safety_notes = 'Not roadworthy vehicle must be VOR' WHERE id IN (126,127,128);

/*row 142 to 150 CHECK ids at real db for brakes*/
UPDATE defect_master SET page_title = 'Brake and Reverse Lights, Reflectors and Marker Lights', app_question = 'Are all the brake and reverse lights, reflectors and marker lights free from defects?' WHERE id BETWEEN 129 AND 132;
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (16,'Brake and Reverse Lights, Reflectors and Marker Lights','Are all the brake and reverse lights, reflectors and marker lights free from defects?','Brake light damaged',1,0,1,'Roadworthy if no white light evident through lens',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (16,'Brake and Reverse Lights, Reflectors and Marker Lights','Are all the brake and reverse lights, reflectors and marker lights free from defects?','Brake light bulb blown',0,0,1,'Not roadworthy vehicle must be VOR',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (16,'Brake and Reverse Lights, Reflectors and Marker Lights','Are all the brake and reverse lights, reflectors and marker lights free from defects?','Brake light fuse blown',0,0,1,'Not roadworthy vehicle must be VOR',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (16,'Brake and Reverse Lights, Reflectors and Marker Lights','Are all the brake and reverse lights, reflectors and marker lights free from defects?','Reverse light damaged',1,0,1,'Not roadworthy vehicle must be VOR',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (16,'Brake and Reverse Lights, Reflectors and Marker Lights','Are all the brake and reverse lights, reflectors and marker lights free from defects?','Reverse light bulb blown',0,0,1,'Not roadworthy vehicle must be VOR',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (16,'Brake and Reverse Lights, Reflectors and Marker Lights','Are all the brake and reverse lights, reflectors and marker lights free from defects?','Reverse light fuse blown',0,0,1,'Not roadworthy vehicle must be VOR',1,1);

/*row 161 to 162 CHECK ids at real db for brakes*/
UPDATE defect_master SET safety_notes = 'Not roadworthy vehicle must be VOR' WHERE id IN (143,144);

/*row 163 to 166*/
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (18,'Battery and Electrics','Are the electrics and the battery free from defects and secure?','Battery is loose',1,0,1,'Not roadworthy vehicle must be VOR',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (18,'Battery and Electrics','Are the electrics and the battery free from defects and secure?','Battery is leaking',1,0,1,'Not roadworthy vehicle must be VOR',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (18,'Battery and Electrics','Are the electrics and the battery free from defects and secure?','Damaged connectors/couplings',1,0,1,'Not roadworthy vehicle must be VOR',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (18,'Battery and Electrics','Are the electrics and the battery free from defects and secure?','Damaged cables',1,0,1,'Not roadworthy vehicle must be VOR',1,1);

/*row 167 to 170*/
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (19,'Fire Extinguisher','Is the fire extinguisher free from defects?','Missing',0,0,1,'Roadworthy but seek replacement extinguisher',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (19,'Fire Extinguisher','Is the fire extinguisher free from defects?','Not secured',1,0,1,'Roadworthy once secured',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (19,'Fire Extinguisher','Is the fire extinguisher free from defects?','Out of service',1,0,1,'Roadworthy if guage is in green. Service to be arranged',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (19,'Fire Extinguisher','Is the fire extinguisher free from defects?','Guage in red',1,0,1,'Roadworthy but seek replacement extinguisher',1,1);

/*row 171 to 174*/
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (20,'First Aid Kit','Is the first aid kit free from defects and have the correct in-date contents?','First aid kit is missing',0,0,1,'Roadworthy but seek replacement kit',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (20,'First Aid Kit','Is the first aid kit free from defects and have the correct in-date contents?','No/Expired eye wash',0,0,1,'Roadworthy but seek replacement kit',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (20,'First Aid Kit','Is the first aid kit free from defects and have the correct in-date contents?','No/Expired plasters',0,0,1,'Roadworthy but seek replacement kit',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (20,'First Aid Kit','Is the first aid kit free from defects and have the correct in-date contents?','Item missing',0,0,1,'Roadworthy but seek replacement kit',1,1);

/*row 171 to 174*/
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (21,'Spill Kit','Is the spill kit free from defects?','Missing',0,0,1,'Roadworthy but seek replacement kit',1,1);
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (21,'Spill Kit','Is the spill kit free from defects?','Items missing',0,0,1,'Roadworthy but seek replacement kit',1,1);


/*row 179 to 185*/
INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (22,'Speed Limiter','Speed Limiter','Speed limiter faulty',0,0,1,'Roadworthy but needs workshop visit',1,1);

INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (23,'Speedometer','Speedometer','Speedometer faulty',0,0,1,'Not roadworthy vehicle must be VOR',1,1);

INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (24,'Engine or Exhaust Smoke','Engine or Exhaust Smoke','Excessive smoke',1,0,1,'Not roadworthy vehicle must be VOR',1,1);

INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (24,'Engine or Exhaust Smoke','Engine or Exhaust Smoke','Black smoke',1,0,1,'Not roadworthy vehicle must be VOR',1,1);

INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (24,'Engine or Exhaust Smoke','Engine or Exhaust Smoke','White smoke',1,0,1,'Not roadworthy vehicle must be VOR',1,1);

INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (25,'Clutch','Clutch','Faulty clutch',0,0,1,'Roadworthy but needs workshop visit. Confirm with manager',1,1);

INSERT INTO defect_master (`order`, `page_title`, `app_question`, `defect`, `has_image`, `has_text`, `is_prohibitional`, `safety_notes`, `for_hgv`, `for_non-hgv`)
VALUES (26,'Gearbox','Gearbox','Faulty gearbox',0,0,1,'Roadworthy but needs workshop visit. Confirm with manager',1,1);

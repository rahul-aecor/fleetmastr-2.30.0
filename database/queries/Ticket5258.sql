INSERT INTO `vehicle_divisions` (`name`) VALUES ('Commercial');
INSERT INTO `vehicle_divisions` (`name`) VALUES ('Legal');
INSERT INTO `vehicle_divisions` (`name`) VALUES ('HO Operations');
INSERT INTO `vehicle_divisions` (`name`) VALUES ('Terminal Operations');
UPDATE vehicle_divisions set name='Site Operations' where name='Operations';


INSERT INTO `user_divisions` (`name`) VALUES ('Commercial');
INSERT INTO `user_divisions` (`name`) VALUES ('Legal');
INSERT INTO `user_divisions` (`name`) VALUES ('HO Operations');
INSERT INTO `user_divisions` (`name`) VALUES ('Terminal Operations');
UPDATE user_divisions set name='Site Operations' where name='Operations';


SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE vehicle_regions;
INSERT INTO `vehicle_regions` (`vehicle_division_id`, `name`) 
VALUES 
(NULL, 'Maintenance Central'),
(NULL, 'Maintenance Humber'),
(NULL, 'Maintenance North East'),
(NULL, 'Maintenance North West'),
(NULL, 'Maintenance Scotland'),
(NULL, 'Maintenance South'),
(NULL, 'Operations East Anglia'),
(NULL, 'Operations Humber'),
(NULL, 'Operations North East'),
(NULL, 'Operations North West'),
(NULL, 'Operations Pipelines Scotland'),
(NULL, 'Operations South'),
(NULL, 'Operations Terminals'),
(NULL, 'Operations Terminals Ireland'),
(NULL, 'Operations Terminals NI'),
(NULL, 'Operations Terminals Scotland'),
(NULL, 'Pipelines Central'),
(NULL, 'Pipelines North'),
(NULL, 'Pipelines South');
UPDATE vehicles SET vehicle_region='Maintenance Central', vehicle_region_id=1;
UPDATE vehicle_assignment SET vehicle_region_id=1;
SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE user_regions;
INSERT INTO `user_regions` (`user_division_id`, `name`) 
VALUES 
(NULL, 'Maintenance Central'),
(NULL, 'Maintenance Humber'),
(NULL, 'Maintenance North East'),
(NULL, 'Maintenance North West'),
(NULL, 'Maintenance Scotland'),
(NULL, 'Maintenance South'),
(NULL, 'Operations East Anglia'),
(NULL, 'Operations Humber'),
(NULL, 'Operations North East'),
(NULL, 'Operations North West'),
(NULL, 'Operations Pipelines Scotland'),
(NULL, 'Operations South'),
(NULL, 'Operations Terminals'),
(NULL, 'Operations Terminals Ireland'),
(NULL, 'Operations Terminals NI'),
(NULL, 'Operations Terminals Scotland'),
(NULL, 'Pipelines Central'),
(NULL, 'Pipelines North'),
(NULL, 'Pipelines South');
UPDATE users SET region='Maintenance Central', user_region_id=1 WHERE user_region_id IS NOT NULL;
SET FOREIGN_KEY_CHECKS=1;



UPDATE vehicle_locations set name='Aldermaston PSD' where name='Aldermaston';
UPDATE vehicle_locations set name='Backford North PSD' where name='Backford North';
UPDATE vehicle_locations set name='Hallen PSD' where name='Hallen';
UPDATE vehicle_locations set name='Killingholme PSD' where name='Killingholme';
UPDATE vehicle_locations set name='Misterton PSD' where name='Misterton';
UPDATE vehicle_locations set name='Purton PSD' where name='Purton';
UPDATE vehicle_locations set name='Rawcliffe PSD' where name='Rawcliffe';
UPDATE vehicle_locations set name='Redcliffe Bay PSD' where name='Redcliffe Bay';
UPDATE vehicle_locations set name='Saffron Walden PSD' where name='Saffron Walden';
UPDATE vehicle_locations set name='Sandy PSD' where name='Sandy';
UPDATE vehicle_locations set name='Thetford PSD' where name='Thetford';
UPDATE vehicle_locations set name='Walton PSD' where name='Walton';


INSERT INTO `vehicle_locations` (`vehicle_region_id`, `name`)
VALUES
(NULL, 'Belfast'),
(NULL, 'Bramhall PSD'),
(NULL, 'Clydebank'),
(NULL, 'Eastham 1'),
(NULL, 'Eastham 2'),
(NULL, 'Grangemouth'),
(NULL, 'Grays'),
(NULL, 'Immingham East'),
(NULL, 'Immingham West'),
(NULL, 'Riverside'),
(NULL, 'Seal Sands'),
(NULL, 'Shannon'),
(NULL, 'Tyne');

UPDATE vehicles set vehicle_location_id=1 where `vehicle_location_id` in (select id from vehicle_locations where name in ('Maintenance North','Maintenance South','Maintenance West','Maintenance East','Maintenance Central','Redmile'));
DELETE FROM vehicle_locations WHERE name in ('Maintenance North','Maintenance South','Maintenance West','Maintenance East','Maintenance Central','Redmile');


UPDATE user_locations set name='Aldermaston PSD' where name='Aldermaston';
UPDATE user_locations set name='Backford North PSD' where name='Backford North';
UPDATE user_locations set name='Hallen PSD' where name='Hallen';
UPDATE user_locations set name='Killingholme PSD' where name='Killingholme';
UPDATE user_locations set name='Misterton PSD' where name='Misterton';
UPDATE user_locations set name='Purton PSD' where name='Purton';
UPDATE user_locations set name='Rawcliffe PSD' where name='Rawcliffe';
UPDATE user_locations set name='Redcliffe Bay PSD' where name='Redcliffe Bay';
UPDATE user_locations set name='Saffron Walden PSD' where name='Saffron Walden';
UPDATE user_locations set name='Sandy PSD' where name='Sandy';
UPDATE user_locations set name='Thetford PSD' where name='Thetford';
UPDATE user_locations set name='Walton PSD' where name='Walton';


INSERT INTO `user_locations` (`user_region_id`, `name`)
VALUES
(NULL, 'Belfast'),
(NULL, 'Bramhall PSD'),
(NULL, 'Clydebank'),
(NULL, 'Eastham 1'),
(NULL, 'Eastham 2'),
(NULL, 'Grangemouth'),
(NULL, 'Grays'),
(NULL, 'Immingham East'),
(NULL, 'Immingham West'),
(NULL, 'Riverside'),
(NULL, 'Seal Sands'),
(NULL, 'Shannon'),
(NULL, 'Tyne');

UPDATE users set user_locations_id=1 where `user_locations_id` in (select id from `user_locations` where name in ('Maintenance North','Maintenance South','Maintenance West','Maintenance East','Maintenance Central','Redmile'));
DELETE FROM user_locations WHERE name in ('Maintenance North','Maintenance South','Maintenance West','Maintenance East','Maintenance Central','Redmile');

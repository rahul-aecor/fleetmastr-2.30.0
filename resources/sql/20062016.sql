SELECT region,COUNT(*) FROM users GROUP BY region;
UPDATE users SET region=NULL WHERE region="";
ALTER TABLE `users` CHANGE `region` `region` ENUM('Central North','North East','South East','South West','Thames Valley','Other (inc HQ)') CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL 
SELECT GROUP_CONCAT(id) FROM users WHERE region='Head Office';
3,4,5,6,7,8,9,37
SELECT GROUP_CONCAT(id) FROM users WHERE region='Other';
1,2,1336
UPDATE users SET region='Other (inc HQ)' WHERE id IN (3,4,5,6,7,8,9,37,1,2,1336);
UPDATE defect_master_vehicle_types SET defect_list = REPLACE(defect_list, ",18",",17") WHERE defect_list LIKE "%18%" AND defect_list NOT LIKE "%17%";
UPDATE defect_master_vehicle_types SET defect_list = REPLACE(defect_list, ",18","") WHERE defect_list LIKE "%17%" AND defect_list LIKE "%18%";
UPDATE defect_master SET `order`=17 WHERE `order`=18;
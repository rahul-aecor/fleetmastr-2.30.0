UPDATE permissions SET NAME="Fleet Planning", module="Planner", slug="fleet.planning" WHERE NAME = "Planner";
UPDATE permissions SET NAME="Vehicle Search" WHERE NAME = "Vehicle Planning & Search";
UPDATE roles SET NAME="Fleet Planning" WHERE NAME = "Planner";
UPDATE roles SET NAME="Vehicle Search" WHERE NAME = "Vehicle Planning & Search";

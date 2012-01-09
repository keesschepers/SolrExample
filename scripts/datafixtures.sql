INSERT INTO `stichtingsbo`.`EventType` (`id` , `name`) 
    VALUES 
(NULL , 'Sprint'), 
(NULL , 'Race'), 
(NULL, 'Algemeen');

INSERT INTO `stichtingsbo`.`Season` (`id` , `startdate` , `enddate`)
    VALUES 
(NULL , '2011-01-01', '2011-12-31');

INSERT INTO `stichtingsbo`.`Event` (`id` , `season_id` , `name` , `startDate` , `endDate` , `eventType_id`)
    VALUES 
(NULL , '1', 'SBO Sprint Nijmegen', '2011-11-13 10:00', '2011-11-13 18:00', '1'),
(NULL , '1', 'SBO Race Veldhoven', '2011-11-20 10:00', '2011-11-20 18:00', '2');
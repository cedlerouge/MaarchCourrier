-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.0.2 to 2301.0.3                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--


-- Below the SQL will insert a row into usergroups_services for each possible combination of (usergroups_services where service_id is 'manage_attachments') rows 
--      and privilege rows, 
-- with the group_id column set to the group_id value from the usergroups_services table, and the service_id column set to the service_id value from the usergroups_services table.
INSERT INTO usergroups_services (group_id, service_id) 
(SELECT us.group_id, privilege as service_id 
FROM usergroups_services as us CROSS JOIN UNNEST(ARRAY['view_attachments', 'update_attachments', 'update_delete_attachments']) as privilege 
WHERE us.service_id = 'manage_attachments');

-- Remove old attachment privilege
DELETE FROM usergroups_services where service_id = 'manage_attachments';

UPDATE parameters SET param_value_string = '2301.0.3' WHERE id = 'database_version';
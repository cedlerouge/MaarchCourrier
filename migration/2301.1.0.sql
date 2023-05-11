-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.0.4 to 2301.1.0                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--


UPDATE usergroups_services SET service_id = 'update_delete_attachments' WHERE service_id = 'manage_attachments';


UPDATE parameters SET param_value_string = '2301.1.0' WHERE id = 'database_version';
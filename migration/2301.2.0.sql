-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.1.x to 2301.2.0                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--

UPDATE users SET preferences = preferences - 'outlookPassword' WHERE preferences->>'outlookPassword' IS NOT NULL;

UPDATE parameters SET param_value_string = '2301.2.0' WHERE id = 'database_version';

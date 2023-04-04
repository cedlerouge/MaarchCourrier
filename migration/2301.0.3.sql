-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.0.2 to 2301.0.3                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--


ALTER TABLE indexing_models DROP COLUMN IF EXISTS entities;
ALTER TABLE indexing_models ADD COLUMN entities jsonb DEFAULT '[]'::jsonb;


UPDATE parameters SET param_value_string = '2301.0.3' WHERE id = 'database_version';
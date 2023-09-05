-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.1.x to 2301.2.0                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--

UPDATE users SET preferences = preferences - 'outlookPassword' WHERE preferences->>'outlookPassword' IS NOT NULL;

UPDATE actions
SET parameters = CASE
                     WHEN
                         (SELECT param_value_int FROM parameters where id = 'keepDestForRedirection') != '0' THEN
                         parameters::jsonb ||
                             '{"keepCopyForRedirection": false, "keepDestForRedirection:": true, "keepOtherRoleForRedirection": false}'
                     WHEN (SELECT param_value_int FROM parameters where id = 'keepDestForRedirection') = '0' THEN
                         parameters::jsonb ||
                             '{"keepCopyForRedirection": false, "keepDestForRedirection:": false, "keepOtherRoleForRedirection": false}' END
WHERE component = 'redirectAction';

DELETE FROM parameters WHERE id = 'keepDestForRedirection';

UPDATE parameters SET param_value_string = '2301.2.0' WHERE id = 'database_version';

ALTER TABLE contacts DROP COLUMN IF EXISTS lad_indexation;
ALTER TABLE contacts ADD COLUMN lad_indexation BOOLEAN DEFAULT FALSE NOT NULL;

ALTER TABLE indexing_models DROP COLUMN IF EXISTS lad_processing;
ALTER TABLE indexing_models ADD COLUMN lad_processing BOOLEAN DEFAULT FALSE NOT NULL;

INSERT INTO configurations (id, privilege, value) VALUES (10, 'admin_mercure', '{"mws": {"url": "","login": "","password": "","tokenMws": "","loginMaarch": "","passwordMaarch": ""},"enabledLad": true,"mwsLadPriority": false}');

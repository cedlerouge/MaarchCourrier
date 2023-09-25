-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.1.x to 2301.2.0                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--
--DATABASE_BACKUP|actions|parameters

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

UPDATE configurations SET value = jsonb_set(value, '{default}', '""'::jsonb, TRUE) WHERE privilege = 'admin_document_editors';

-- New storage zone
DELETE FROM docserver_types WHERE docserver_type_id = 'MIGRATION';
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode) VALUES ('MIGRATION', 'Sauvegarde des migrations', 'Y', NULL);
DELETE FROM docservers WHERE docserver_id = 'MIGRATION';
INSERT INTO docservers (id, docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id) VALUES (13, 'MIGRATION', 'MIGRATION', 'Dêpot de sauvegarde des migrations', 'N', 50000000000, 0, '/opt/maarch/docservers/migration/', '2023-09-05 22:22:22.201904', 'migration');
-- New column for configurations
ALTER TABLE configurations DROP COLUMN IF EXISTS parameters;
ALTER TABLE configurations ADD COLUMN parameters jsonb NOT NULL DEFAULT '{}';
UPDATE configurations SET value = jsonb_set(value, '{default}', '""'::jsonb, TRUE) WHERE privilege = 'admin_document_editors';

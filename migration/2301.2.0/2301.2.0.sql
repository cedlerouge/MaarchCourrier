-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.1.x to 2301.2.0                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--
--DATABASE_BACKUP|configurations

UPDATE configurations SET value = jsonb_set(value, '{default}', '""'::jsonb, TRUE) WHERE privilege = 'admin_document_editors';

-- New storage zone
DELETE FROM docserver_types WHERE docserver_type_id = 'MIGRATION';
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode) VALUES ('MIGRATION', 'Sauvegarde des migrations', 'Y', NULL);
DELETE FROM docservers WHERE docserver_id = 'MIGRATION';
INSERT INTO docservers (id, docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id) VALUES (13, 'MIGRATION', 'MIGRATION', 'Dêpot de sauvegarde des migrations', 'N', 50000000000, 0, '/opt/maarch/docservers/migration/', '2023-09-05 22:22:22.201904', 'migration');

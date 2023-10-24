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

-- New storage zone
DELETE FROM docserver_types WHERE docserver_type_id = 'MIGRATION';
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, fingerprint_mode) VALUES ('MIGRATION', 'Sauvegarde des migrations', 'Y', NULL);
DELETE FROM docservers WHERE docserver_id = 'MIGRATION';
-- Insert the MIGRATION docserver with a dynamic path based on the FASTHD_MAN docserver
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id) 
VALUES ('MIGRATION', 'MIGRATION', 'Dépôt de sauvegarde des migrations', 'N', 50000000000, 0,
    (
        SELECT
            CASE
            WHEN position('/' in path_template) = 0 THEN path_template
            ELSE
                (
                    SELECT string_agg(part, '/' ORDER BY ordinality)
                    FROM unnest(string_to_array(path_template, '/')) WITH ORDINALITY AS t(part, ordinality)
                    WHERE ordinality < (array_length(string_to_array(path_template, '/'), 1) - 1)
                ) || '/migration/'
            END
        FROM docservers
        WHERE docserver_id = 'FASTHD_MAN'
    ),
NOW(), 'migration');

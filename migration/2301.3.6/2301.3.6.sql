-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.3.0 to 2301.3.6                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--
--DATABASE_BACKUP|parameters

-- The query will only insert the new row if there is no existing row with the id equal to allowMultipleAvisAssignment.
-- If a row with that id already exists, the INSERT statement will not execute.
INSERT INTO parameters (id, description, param_value_string, param_value_int, param_value_date)
SELECT 'allowMultipleAvisAssignment', 'Un utilisateur peut fournir plusieurs avis tout en conservant le même rôle', NULL, 0, NULL
    WHERE NOT EXISTS (
    SELECT 1 FROM parameters WHERE id = 'allowMultipleAvisAssignment'
);

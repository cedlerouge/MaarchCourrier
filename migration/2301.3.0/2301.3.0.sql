-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.2.x to 2301.3.0                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--
--DATABASE_BACKUP|docservers

-- Docserver encryption
-- Checks if the "is_encrypted" column exists in the "docservers" table, if it doesn't exist, add the column.
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'docservers' AND column_name = 'is_encrypted') THEN
        ALTER TABLE docservers ADD COLUMN is_encrypted BOOL NOT NULL DEFAULT FALSE;
    END IF;
END $$;

INSERT INTO parameters (id, description, param_value_date) VALUES ('last_docservers_size_calculation', 'Date de dernier calcul de la taille des docservers', '2024-01-01 00:00:00');

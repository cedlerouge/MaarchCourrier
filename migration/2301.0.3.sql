-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.0.2 to 2301.0.3                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--


CREATE TABLE IF NOT EXISTS indexing_models_entities
(
    id SERIAL NOT NULL,
    model_id INTEGER NOT NULL,
    entity_id text NOT NULL,
    CONSTRAINT indexing_models_entities_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

SELECT setval('indexing_models_entities_id_seq', (SELECT max(id)+1 FROM indexing_models_entities), false);

-- Below the SQL will insert a row into indexing_models_entities for each possible combination of indexing_models rows and entities rows, 
-- with the model_id column set to the id value from the indexing_models table, and the entity_id column set to the entity_id value from the entities table.
INSERT INTO indexing_models_entities (model_id, entity_id) SELECT models.id AS model_id, entity.entity_id FROM indexing_models as models CROSS JOIN entities as entity;


UPDATE parameters SET param_value_string = '2301.0.3' WHERE id = 'database_version';
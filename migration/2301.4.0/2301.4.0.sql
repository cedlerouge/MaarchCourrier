-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.3.6 to 2301.4.0                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--
DELETE FROM configurations WHERE privilege = 'admin_mercure';
INSERT INTO configurations (privilege, value) VALUES ('admin_mercure', '{"mws": {"url": "","login": "","password": "","tokenMws": "","loginMaarch": "","passwordMaarch": ""},"enabledLad": true,"mwsLadPriority": false}');

UPDATE indexing_models SET lad_processing = true WHERE category = 'incoming';

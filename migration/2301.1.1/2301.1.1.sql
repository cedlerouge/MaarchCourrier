-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 2301.1.0 to 2301.1.1                            --
--                                                                          --
--                                                                          --
-- *************************************************************************--

DELETE FROM parameters WHERE id = 'useSectorsForAddresses';
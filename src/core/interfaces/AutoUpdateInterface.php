<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Custom Automatic Update Interface
 * @author dev@maarch.org
 * @ingroup core
 */

namespace SrcCore\interfaces;

Interface AutoUpdateInterface
{
    /**
     * Function to perform any backups for the update.
     * Use trycatch at the root of the function.
     * 
     * @return  mixed   true if backup is sucessful
     * @throws  \Exception if the backup failed
     */
    public static function backup();

    /**
     * Function to perform any update for a feature.
     * Use trycatch at the root of the function.
     * 
     * @return  mixed   true if update is sucessful
     * @throws  \Exception if the update failed
     */
    public static function update();

    /**
     * Function to perform any rollback for the update.
     * Use trycatch at the root of the function.
     * 
     * @return  mixed   true if rollback is sucessful
     * @throws  \Exception if the rollback failed
     */
    public static function rollback();
}
#!/bin/sh
php ./migrateModulesConfig.php
php ./migrateNotificationsProperties.php
php ./migrateNotificationsConfig.php
php ./migrateCustomXml.php # mettre en dernier

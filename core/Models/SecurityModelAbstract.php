<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Security Model Abstract
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

class SecurityModelAbstract
{

    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function checkAuthentication(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'password']);
        ValidatorModel::stringType($args, ['userId', 'password']);

        $aReturn = DatabaseModel::select([
            'select'    => ['password'],
            'table'     => ['users'],
            'where'     => ['user_id = ?', 'status != ?'],
            'data'      => [$args['userId'], 'DEL']
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return password_verify($args['password'], $aReturn[0]['password']);
    }

    public static function setCookieAuth(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'password']);
        ValidatorModel::stringType($args, ['userId', 'password']);

        $customId = CoreConfigModel::getCustomId();

        if (file_exists("custom/{$customId}/apps/maarch_entreprise/xml/config.xml")) {
            $path = "custom/{$customId}/apps/maarch_entreprise/xml/config.xml";
        } else {
            $path = 'apps/maarch_entreprise/xml/config.xml';
        }

        $cookieTime = 0;
        if (file_exists($path)) {
            $loadedXml = simplexml_load_file($path);
            if ($loadedXml) {
                $cookieTime = (string)$loadedXml->CONFIG->CookieTime;
            }
        }

        $cookiePath = str_replace('apps/maarch_entreprise/index.php', '', $_SERVER['SCRIPT_NAME']);

        $cookieData = json_encode(['userId' => $args['userId'], 'password' => $args['password']]);
        $cookieDataEncrypted = openssl_encrypt ($cookieData, 'aes-256-ctr', '12345678910');
        setcookie('maarchCourrierAuth', base64_encode($cookieDataEncrypted), time() + 60 * $cookieTime, $cookiePath, '', false, true);

        return true;
    }

    public static function getCookieAuth()
    {
        $rawCookie = $_COOKIE['maarchCourrierAuth'];
        if (empty($rawCookie)) {
            return [];
        }
        $cookieDecrypted = openssl_decrypt(base64_decode($rawCookie), 'aes-256-ctr', '12345678910');
        $cookie = json_decode($cookieDecrypted);

        return $cookie;
    }
}

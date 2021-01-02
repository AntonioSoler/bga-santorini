<?php

/*
 * This dummy class makes VSCode PHP Intelephense plugin happy
 * Do not treat BGA framework function calls as errors 
 */

define('APP_GAMEMODULE_PATH', '');
define('AT_int', 0);
define('AT_posint', 0);

function clienttranslate($text)
{
}

function _($text)
{
}


class APP_GameClass
{
    function __construct()
    {
    }

    static function _($text)
    {
        return '';
    }

    /* Database */

    static function DbQuery($sql)
    {
    }

    static function DbGetLastId()
    {
        return 0;
    }

    static function getUniqueValueFromDB($sql)
    {
        return [];
    }

    static function getObjectFromDB($sql)
    {
        return [];
    }

    static function getObjectListFromDb($sql)
    {
        return [];
    }

    static function getNonEmptyObjectFromDB($sql)
    {
        return [];
    }

    /* Other */

    static function getNew($component)
    {
        return (object) $component;
    }
}


class APP_GameAction extends APP_GameClass
{
    static function setAjaxMode()
    {
    }

    static function ajaxResponse()
    {
    }

    static function getArg($argName, $argType, $mandatory = false, $default = NULL, $argTypeDetails = array(), $bCanFail = false)
    {
    }

    static function isArg($argName)
    {
    }
}

class Table extends APP_GameClass
{

    static function getGameinfos()
    {
    }
    static function reloadPlayersBasicInfos()
    {
    }

    /* Globals */

    static function initGameStateLabels($array)
    {
    }

    static function setGameStateInitialValue($value_label, $value_value)
    {
    }

    static function getGameStateValue($value_label)
    {
    }

    static function setGameStateValue($value_label, $value_value)
    {
    }

    static function incGameStateValue($value_label, $increment)
    {
    }

    /* State functions */

    function checkAction($actionName, $bThrowException = true)
    {
    }

    function activeNextPlayer()
    {
    }

    function activePrevPlayer()
    {
    }

    static function getCurrentPlayerId()
    {
        return 0;
    }

    static function getActivePlayerId()
    {
        return 0;
    }

    static function getActivePlayerName()
    {
        return '';
    }

    static function giveExtraTime($player_id, $specific_time = null)
    {
    }

    /* Notify */

    static function notifyAllPlayers($notification_type, $notification_log, $notification_args)
    {
    }

    static function notifyPlayer($player_id, $notification_type, $notification_log, $notification_args)
    {
    }
}

class BgaVisibleSystemException
{
}

class BgaUserException
{
}

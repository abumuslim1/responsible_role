<?php
/**
 * Bitrix24 Module: Responsible Role
 * Main class file
 */

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

class ResponsibleRoleHelper
{
    const MODULE_ID = 'responsible_role';
    const ROLE_CODE = 'responsible';
    const FIELD_NAME = 'UF_RESPONSIBLE_EMPLOYEE';
    const TABLE_NAME = 'b_responsible_role_mapping';

    /**
     * Get responsible employee for task
     */
    public static function getResponsibleEmployee($iTaskId)
    {
        global $DB;

        $rsMapping = $DB->Query(
            "SELECT USER_ID FROM " . self::TABLE_NAME . " 
             WHERE TASK_ID = " . (int)$iTaskId . " LIMIT 1"
        );

        if ($arMapping = $rsMapping->Fetch()) {
            return (int)$arMapping['USER_ID'];
        }

        return null;
    }

    /**
     * Get all responsible employees for task
     */
    public static function getResponsibleEmployees($iTaskId)
    {
        global $DB;

        $rsMapping = $DB->Query(
            "SELECT USER_ID FROM " . self::TABLE_NAME . " 
             WHERE TASK_ID = " . (int)$iTaskId
        );

        $aUserIds = [];
        while ($arMapping = $rsMapping->Fetch()) {
            $aUserIds[] = (int)$arMapping['USER_ID'];
        }

        return $aUserIds;
    }

    /**
     * Set responsible employee for task
     */
    public static function setResponsibleEmployee($iTaskId, $iUserId)
    {
        global $DB;

        if ($iUserId <= 0) {
            return self::deleteResponsibleEmployee($iTaskId);
        }

        // Get role ID
        $rsRole = $DB->Query(
            "SELECT ID FROM b_tasks_role WHERE CODE = '" . self::ROLE_CODE . "' LIMIT 1"
        );

        if (!($arRole = $rsRole->Fetch())) {
            return false;
        }

        // Insert or update mapping
        $DB->Query(
            "INSERT INTO " . self::TABLE_NAME . " (TASK_ID, ROLE_ID, USER_ID) 
             VALUES (" . (int)$iTaskId . ", " . (int)$arRole['ID'] . ", " . (int)$iUserId . ")
             ON DUPLICATE KEY UPDATE USER_ID = " . (int)$iUserId . ", MODIFIED = NOW()"
        );

        return true;
    }

    /**
     * Delete responsible employee for task
     */
    public static function deleteResponsibleEmployee($iTaskId)
    {
        global $DB;

        $DB->Query(
            "DELETE FROM " . self::TABLE_NAME . " WHERE TASK_ID = " . (int)$iTaskId
        );

        return true;
    }

    /**
     * Get user info by ID
     */
    public static function getUserInfo($iUserId)
    {
        global $DB;

        $rsUser = $DB->Query(
            "SELECT ID, NAME, LAST_NAME, EMAIL FROM b_user WHERE ID = " . (int)$iUserId . " LIMIT 1"
        );

        if ($arUser = $rsUser->Fetch()) {
            return $arUser;
        }

        return null;
    }

    /**
     * Get user full name
     */
    public static function getUserFullName($iUserId)
    {
        $arUser = self::getUserInfo($iUserId);

        if ($arUser) {
            return trim($arUser['NAME'] . ' ' . $arUser['LAST_NAME']);
        }

        return '';
    }

    /**
     * Get all active users for dropdown
     */
    public static function getAllUsers()
    {
        global $DB;

        $rsUsers = $DB->Query(
            "SELECT ID, NAME, LAST_NAME, EMAIL FROM b_user 
             WHERE ACTIVE = 'Y' 
             ORDER BY NAME, LAST_NAME"
        );

        $aUsers = [];
        while ($arUser = $rsUsers->Fetch()) {
            $sFullName = trim($arUser['NAME'] . ' ' . $arUser['LAST_NAME']);
            $aUsers[$arUser['ID']] = $sFullName . ' (' . $arUser['EMAIL'] . ')';
        }

        return $aUsers;
    }

    /**
     * Get role ID by code
     */
    public static function getRoleId($sCode = self::ROLE_CODE)
    {
        global $DB;

        $rsRole = $DB->Query(
            "SELECT ID FROM b_tasks_role WHERE CODE = '" . $DB->ForSQL($sCode) . "' LIMIT 1"
        );

        if ($arRole = $rsRole->Fetch()) {
            return (int)$arRole['ID'];
        }

        return null;
    }

    /**
     * Check if module is installed
     */
    public static function isInstalled()
    {
        global $DB;

        // Check if table exists
        $rsTable = $DB->Query(
            "SHOW TABLES LIKE '" . self::TABLE_NAME . "'"
        );

        return (bool)$rsTable->Fetch();
    }

    /**
     * Get tasks filtered by responsible employee
     */
    public static function getTasksByResponsibleEmployee($iUserId, $aFilter = [])
    {
        global $DB;

        $sWhere = "WHERE trm.USER_ID = " . (int)$iUserId;

        if (!empty($aFilter['STATUS'])) {
            $sWhere .= " AND t.STATUS = '" . $DB->ForSQL($aFilter['STATUS']) . "'";
        }

        if (!empty($aFilter['CREATED_BY'])) {
            $sWhere .= " AND t.CREATED_BY = " . (int)$aFilter['CREATED_BY'];
        }

        $rsTaskIds = $DB->Query(
            "SELECT DISTINCT t.ID FROM b_tasks t 
             INNER JOIN " . self::TABLE_NAME . " trm ON t.ID = trm.TASK_ID 
             " . $sWhere . "
             ORDER BY t.ID DESC"
        );

        $aTaskIds = [];
        while ($arRow = $rsTaskIds->Fetch()) {
            $aTaskIds[] = (int)$arRow['ID'];
        }

        return $aTaskIds;
    }

    /**
     * Get statistics for responsible employee
     */
    public static function getResponsibleStats($iUserId)
    {
        global $DB;

        $rsStats = $DB->Query(
            "SELECT 
                COUNT(DISTINCT trm.TASK_ID) as TOTAL_TASKS,
                SUM(CASE WHEN t.STATUS = '1' THEN 1 ELSE 0 END) as ACTIVE_TASKS,
                SUM(CASE WHEN t.STATUS = '5' THEN 1 ELSE 0 END) as COMPLETED_TASKS
             FROM " . self::TABLE_NAME . " trm
             INNER JOIN b_tasks t ON trm.TASK_ID = t.ID
             WHERE trm.USER_ID = " . (int)$iUserId
        );

        if ($arStats = $rsStats->Fetch()) {
            return $arStats;
        }

        return [
            'TOTAL_TASKS' => 0,
            'ACTIVE_TASKS' => 0,
            'COMPLETED_TASKS' => 0
        ];
    }
}

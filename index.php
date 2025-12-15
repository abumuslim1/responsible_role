<?php
/**
 * Bitrix24 Module: Responsible Role
 * Main module file
 */

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Bitrix\Tasks\Task;

class ResponsibleRoleModule
{
    const MODULE_ID = 'responsible_role';
    const ROLE_CODE = 'responsible';
    const FIELD_NAME = 'UF_RESPONSIBLE_EMPLOYEE';

    /**
     * Initialize module
     */
    public static function init()
    {
        if (!Loader::includeModule(self::MODULE_ID)) {
            return false;
        }

        $eventManager = EventManager::getInstance();

        // Register event handlers for task operations
        $eventManager->registerEventHandler(
            'tasks',
            'OnTaskAdd',
            self::MODULE_ID,
            'ResponsibleRoleModule',
            'onTaskAdd'
        );

        $eventManager->registerEventHandler(
            'tasks',
            'OnTaskUpdate',
            self::MODULE_ID,
            'ResponsibleRoleModule',
            'onTaskUpdate'
        );

        $eventManager->registerEventHandler(
            'tasks',
            'OnTaskDelete',
            self::MODULE_ID,
            'ResponsibleRoleModule',
            'onTaskDelete'
        );

        // Register event for task list filtering
        $eventManager->registerEventHandler(
            'tasks',
            'OnGetTaskList',
            self::MODULE_ID,
            'ResponsibleRoleModule',
            'onGetTaskList'
        );

        return true;
    }

    /**
     * Handle task creation
     */
    public static function onTaskAdd($arTask)
    {
        global $DB;

        if (!isset($arTask['ID'])) {
            return;
        }

        $iTaskId = (int)$arTask['ID'];
        $sResponsibleEmployee = isset($arTask[self::FIELD_NAME]) ? $arTask[self::FIELD_NAME] : null;

        if (!$sResponsibleEmployee) {
            return;
        }

        // Save responsible employee mapping
        self::saveResponsibleEmployee($iTaskId, $sResponsibleEmployee);
    }

    /**
     * Handle task update
     */
    public static function onTaskUpdate($arTask)
    {
        global $DB;

        if (!isset($arTask['ID'])) {
            return;
        }

        $iTaskId = (int)$arTask['ID'];
        $sResponsibleEmployee = isset($arTask[self::FIELD_NAME]) ? $arTask[self::FIELD_NAME] : null;

        // Delete old mapping
        $DB->Query(
            "DELETE FROM b_responsible_role_mapping WHERE TASK_ID = " . $iTaskId
        );

        // Save new responsible employee mapping
        if ($sResponsibleEmployee) {
            self::saveResponsibleEmployee($iTaskId, $sResponsibleEmployee);
        }
    }

    /**
     * Handle task deletion
     */
    public static function onTaskDelete($iTaskId)
    {
        global $DB;

        // Delete mapping when task is deleted
        $DB->Query(
            "DELETE FROM b_responsible_role_mapping WHERE TASK_ID = " . (int)$iTaskId
        );
    }

    /**
     * Handle task list filtering
     */
    public static function onGetTaskList($arFilter)
    {
        global $DB;

        if (!isset($arFilter[self::FIELD_NAME])) {
            return;
        }

        $sResponsibleEmployee = $arFilter[self::FIELD_NAME];
        $iUserId = (int)$sResponsibleEmployee;

        if ($iUserId <= 0) {
            return;
        }

        // Get task IDs with responsible employee
        $rsTaskIds = $DB->Query(
            "SELECT TASK_ID FROM b_responsible_role_mapping 
             WHERE USER_ID = " . $iUserId
        );

        $aTaskIds = [];
        while ($arRow = $rsTaskIds->Fetch()) {
            $aTaskIds[] = $arRow['TASK_ID'];
        }

        return $aTaskIds;
    }

    /**
     * Save responsible employee mapping
     */
    private static function saveResponsibleEmployee($iTaskId, $sResponsibleEmployee)
    {
        global $DB;

        $iUserId = (int)$sResponsibleEmployee;

        if ($iUserId <= 0) {
            return false;
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
            "INSERT INTO b_responsible_role_mapping (TASK_ID, ROLE_ID, USER_ID) 
             VALUES (" . $iTaskId . ", " . $arRole['ID'] . ", " . $iUserId . ")
             ON DUPLICATE KEY UPDATE USER_ID = " . $iUserId
        );

        return true;
    }

    /**
     * Get responsible employee for task
     */
    public static function getResponsibleEmployee($iTaskId)
    {
        global $DB;

        $rsMapping = $DB->Query(
            "SELECT USER_ID FROM b_responsible_role_mapping 
             WHERE TASK_ID = " . (int)$iTaskId . " LIMIT 1"
        );

        if ($arMapping = $rsMapping->Fetch()) {
            return $arMapping['USER_ID'];
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
            "SELECT USER_ID FROM b_responsible_role_mapping 
             WHERE TASK_ID = " . (int)$iTaskId
        );

        $aUserIds = [];
        while ($arMapping = $rsMapping->Fetch()) {
            $aUserIds[] = $arMapping['USER_ID'];
        }

        return $aUserIds;
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
     * Get all users for dropdown
     */
    public static function getAllUsers()
    {
        global $DB;

        $rsUsers = $DB->Query(
            "SELECT ID, NAME, LAST_NAME, EMAIL FROM b_user WHERE ACTIVE = 'Y' ORDER BY NAME"
        );

        $aUsers = [];
        while ($arUser = $rsUsers->Fetch()) {
            $aUsers[$arUser['ID']] = $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
        }

        return $aUsers;
    }
}

// Initialize module on load
if (Loader::includeModule('responsible_role')) {
    ResponsibleRoleModule::init();
}

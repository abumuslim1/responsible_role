<?php
/**
 * Bitrix24 Module: Responsible Role
 * Event handlers
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

// Include helper class
if (file_exists(__DIR__ . '/class.php')) {
    require_once(__DIR__ . '/class.php');
}

class ResponsibleRoleEventHandler
{
    /**
     * Handle task addition
     */
    public static function onTaskAdd(&$arTask)
    {
        if (!isset($arTask['ID'])) {
            return;
        }

        $iTaskId = (int)$arTask['ID'];
        $sResponsibleEmployee = isset($arTask['UF_RESPONSIBLE_EMPLOYEE']) ? 
            $arTask['UF_RESPONSIBLE_EMPLOYEE'] : null;

        if (!$sResponsibleEmployee) {
            return;
        }

        if (class_exists('ResponsibleRoleHelper')) {
            ResponsibleRoleHelper::setResponsibleEmployee($iTaskId, (int)$sResponsibleEmployee);
        }
    }

    /**
     * Handle task update
     */
    public static function onTaskUpdate(&$arTask)
    {
        if (!isset($arTask['ID'])) {
            return;
        }

        $iTaskId = (int)$arTask['ID'];
        $sResponsibleEmployee = isset($arTask['UF_RESPONSIBLE_EMPLOYEE']) ? 
            $arTask['UF_RESPONSIBLE_EMPLOYEE'] : null;

        if (class_exists('ResponsibleRoleHelper')) {
            if ($sResponsibleEmployee) {
                ResponsibleRoleHelper::setResponsibleEmployee($iTaskId, (int)$sResponsibleEmployee);
            } else {
                ResponsibleRoleHelper::deleteResponsibleEmployee($iTaskId);
            }
        }
    }

    /**
     * Handle task deletion
     */
    public static function onTaskDelete($iTaskId)
    {
        if (!$iTaskId) {
            return;
        }

        if (class_exists('ResponsibleRoleHelper')) {
            ResponsibleRoleHelper::deleteResponsibleEmployee((int)$iTaskId);
        }
    }

    /**
     * Handle task list display
     */
    public static function onTaskListDisplay(&$arTasks)
    {
        if (empty($arTasks)) {
            return;
        }

        if (!class_exists('ResponsibleRoleHelper')) {
            return;
        }

        // Add responsible employee info to each task
        foreach ($arTasks as &$arTask) {
            if (isset($arTask['ID'])) {
                $iResponsibleId = ResponsibleRoleHelper::getResponsibleEmployee($arTask['ID']);
                
                if ($iResponsibleId) {
                    $arUser = ResponsibleRoleHelper::getUserInfo($iResponsibleId);
                    $arTask['RESPONSIBLE_EMPLOYEE'] = $arUser;
                }
            }
        }
        unset($arTask);
    }

    /**
     * Handle task detail display
     */
    public static function onTaskDetailDisplay(&$arTask)
    {
        if (!isset($arTask['ID'])) {
            return;
        }

        if (!class_exists('ResponsibleRoleHelper')) {
            return;
        }

        $iTaskId = (int)$arTask['ID'];
        $iResponsibleId = ResponsibleRoleHelper::getResponsibleEmployee($iTaskId);

        if ($iResponsibleId) {
            $arUser = ResponsibleRoleHelper::getUserInfo($iResponsibleId);
            $arTask['RESPONSIBLE_EMPLOYEE'] = $arUser;
        }
    }

    /**
     * Handle task filter
     */
    public static function onTaskFilter(&$arFilter)
    {
        if (!isset($arFilter['RESPONSIBLE_EMPLOYEE'])) {
            return;
        }

        if (!class_exists('ResponsibleRoleHelper')) {
            return;
        }

        $iUserId = (int)$arFilter['RESPONSIBLE_EMPLOYEE'];

        if ($iUserId <= 0) {
            return;
        }

        // Get tasks by responsible employee
        $aTaskIds = ResponsibleRoleHelper::getTasksByResponsibleEmployee($iUserId);

        // Add to filter
        $arFilter['TASK_IDS'] = $aTaskIds;
    }

    /**
     * Handle user deletion
     */
    public static function onUserDelete($iUserId)
    {
        global $DB;

        if (!$iUserId) {
            return;
        }

        // Delete all mappings for this user
        $DB->Query(
            "DELETE FROM b_responsible_role_mapping WHERE USER_ID = " . (int)$iUserId
        );
    }

    /**
     * Register all event handlers
     */
    public static function registerHandlers()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'tasks',
            'OnTaskAdd',
            'responsible_role',
            'ResponsibleRoleEventHandler',
            'onTaskAdd'
        );

        $eventManager->registerEventHandler(
            'tasks',
            'OnTaskUpdate',
            'responsible_role',
            'ResponsibleRoleEventHandler',
            'onTaskUpdate'
        );

        $eventManager->registerEventHandler(
            'tasks',
            'OnTaskDelete',
            'responsible_role',
            'ResponsibleRoleEventHandler',
            'onTaskDelete'
        );

        $eventManager->registerEventHandler(
            'tasks',
            'OnTaskListDisplay',
            'responsible_role',
            'ResponsibleRoleEventHandler',
            'onTaskListDisplay'
        );

        $eventManager->registerEventHandler(
            'tasks',
            'OnTaskDetailDisplay',
            'responsible_role',
            'ResponsibleRoleEventHandler',
            'onTaskDetailDisplay'
        );

        $eventManager->registerEventHandler(
            'tasks',
            'OnTaskFilter',
            'responsible_role',
            'ResponsibleRoleEventHandler',
            'onTaskFilter'
        );

        $eventManager->registerEventHandler(
            'main',
            'OnUserDelete',
            'responsible_role',
            'ResponsibleRoleEventHandler',
            'onUserDelete'
        );
    }
}

// Register handlers on module load
if (Loader::includeModule('responsible_role')) {
    ResponsibleRoleEventHandler::registerHandlers();
}

<?php
/**
 * Bitrix24 Module: Responsible Role
 * REST API endpoints
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

// Include helper class
if (file_exists(__DIR__ . '/class.php')) {
    require_once(__DIR__ . '/class.php');
}

/**
 * REST API: Get responsible employee for task
 * Method: responsible_role.task.getResponsible
 */
function RestGetResponsibleEmployee($taskId)
{
    if (!$taskId) {
        return ['error' => 'Task ID is required'];
    }

    if (class_exists('ResponsibleRoleHelper')) {
        $iUserId = ResponsibleRoleHelper::getResponsibleEmployee((int)$taskId);

        if ($iUserId) {
            $arUser = ResponsibleRoleHelper::getUserInfo($iUserId);
            return [
                'id' => $arUser['ID'],
                'name' => $arUser['NAME'] . ' ' . $arUser['LAST_NAME'],
                'email' => $arUser['EMAIL']
            ];
        }
    }

    return null;
}

/**
 * REST API: Set responsible employee for task
 * Method: responsible_role.task.setResponsible
 */
function RestSetResponsibleEmployee($taskId, $userId)
{
    if (!$taskId || !$userId) {
        return ['error' => 'Task ID and User ID are required'];
    }

    if (class_exists('ResponsibleRoleHelper')) {
        $result = ResponsibleRoleHelper::setResponsibleEmployee((int)$taskId, (int)$userId);

        if ($result) {
            return ['success' => true, 'message' => 'Responsible employee set successfully'];
        } else {
            return ['error' => 'Failed to set responsible employee'];
        }
    }

    return ['error' => 'Helper class not found'];
}

/**
 * REST API: Get tasks by responsible employee
 * Method: responsible_role.task.getByResponsible
 */
function RestGetTasksByResponsible($userId)
{
    if (!$userId) {
        return ['error' => 'User ID is required'];
    }

    if (class_exists('ResponsibleRoleHelper')) {
        $aTaskIds = ResponsibleRoleHelper::getTasksByResponsibleEmployee((int)$userId);

        return [
            'success' => true,
            'tasks' => $aTaskIds,
            'count' => count($aTaskIds)
        ];
    }

    return ['error' => 'Helper class not found'];
}

/**
 * REST API: Get responsible statistics
 * Method: responsible_role.task.getStats
 */
function RestGetResponsibleStats($userId)
{
    if (!$userId) {
        return ['error' => 'User ID is required'];
    }

    if (class_exists('ResponsibleRoleHelper')) {
        $arStats = ResponsibleRoleHelper::getResponsibleStats((int)$userId);

        return [
            'success' => true,
            'stats' => $arStats
        ];
    }

    return ['error' => 'Helper class not found'];
}

/**
 * REST API: Get all users for responsible role
 * Method: responsible_role.user.getList
 */
function RestGetUsersList()
{
    if (class_exists('ResponsibleRoleHelper')) {
        $aUsers = ResponsibleRoleHelper::getAllUsers();

        return [
            'success' => true,
            'users' => $aUsers,
            'count' => count($aUsers)
        ];
    }

    return ['error' => 'Helper class not found'];
}

/**
 * REST API: Delete responsible employee from task
 * Method: responsible_role.task.deleteResponsible
 */
function RestDeleteResponsibleEmployee($taskId)
{
    if (!$taskId) {
        return ['error' => 'Task ID is required'];
    }

    if (class_exists('ResponsibleRoleHelper')) {
        $result = ResponsibleRoleHelper::deleteResponsibleEmployee((int)$taskId);

        if ($result) {
            return ['success' => true, 'message' => 'Responsible employee deleted successfully'];
        } else {
            return ['error' => 'Failed to delete responsible employee'];
        }
    }

    return ['error' => 'Helper class not found'];
}

/**
 * Register REST API methods
 */
if (Loader::includeModule('responsible_role')) {
    $GLOBALS['REST_API_METHODS'] = array_merge(
        $GLOBALS['REST_API_METHODS'] ?? [],
        [
            'responsible_role.task.getResponsible' => 'RestGetResponsibleEmployee',
            'responsible_role.task.setResponsible' => 'RestSetResponsibleEmployee',
            'responsible_role.task.getByResponsible' => 'RestGetTasksByResponsible',
            'responsible_role.task.getStats' => 'RestGetResponsibleStats',
            'responsible_role.user.getList' => 'RestGetUsersList',
            'responsible_role.task.deleteResponsible' => 'RestDeleteResponsibleEmployee'
        ]
    );
}

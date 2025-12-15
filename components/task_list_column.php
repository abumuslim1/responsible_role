<?php
/**
 * Bitrix24 Module: Responsible Role
 * Task list column component
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class ResponsibleRoleTaskListColumnComponent
{
    /**
     * Render responsible employee column header
     */
    public static function renderHeader()
    {
        return '<th class="task-list-responsible-column">' .
            '<span class="column-header">Ответственный</span>' .
            '</th>';
    }

    /**
     * Render responsible employee column cell
     */
    public static function renderCell($iTaskId)
    {
        global $DB;

        $html = '<td class="task-list-responsible-column">';

        // Get responsible employee
        $rsMapping = $DB->Query(
            "SELECT USER_ID FROM b_responsible_role_mapping 
             WHERE TASK_ID = " . (int)$iTaskId . " LIMIT 1"
        );

        if ($arMapping = $rsMapping->Fetch()) {
            $iUserId = (int)$arMapping['USER_ID'];

            // Get user info
            $rsUser = $DB->Query(
                "SELECT NAME, LAST_NAME FROM b_user WHERE ID = " . $iUserId . " LIMIT 1"
            );

            if ($arUser = $rsUser->Fetch()) {
                $sUserName = trim($arUser['NAME'] . ' ' . $arUser['LAST_NAME']);
                $html .= '<div class="task-list-responsible-cell">' .
                    '<span class="employee-badge">' . htmlspecialchars($sUserName) . '</span>' .
                    '</div>';
            }
        }

        $html .= '</td>';

        return $html;
    }

    /**
     * Render filter section
     */
    public static function renderFilter()
    {
        global $DB;

        $html = '<div class="responsible-role-filter">';
        $html .= '<label for="filter-responsible-employee">Ответственный:</label>';
        $html .= '<select id="filter-responsible-employee" name="filter_responsible" class="filter-select">';
        $html .= '<option value="">-- Все --</option>';

        // Get all users with responsible tasks
        $rsUsers = $DB->Query(
            "SELECT DISTINCT u.ID, u.NAME, u.LAST_NAME 
             FROM b_user u
             INNER JOIN b_responsible_role_mapping trm ON u.ID = trm.USER_ID
             WHERE u.ACTIVE = 'Y'
             ORDER BY u.NAME, u.LAST_NAME"
        );

        while ($arUser = $rsUsers->Fetch()) {
            $sUserName = trim($arUser['NAME'] . ' ' . $arUser['LAST_NAME']);
            $html .= '<option value="' . htmlspecialchars($arUser['ID']) . '">' .
                htmlspecialchars($sUserName) .
                '</option>';
        }

        $html .= '</select>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Get tasks filtered by responsible employee
     */
    public static function getFilteredTasks($iUserId)
    {
        global $DB;

        $rsTaskIds = $DB->Query(
            "SELECT DISTINCT TASK_ID FROM b_responsible_role_mapping 
             WHERE USER_ID = " . (int)$iUserId . "
             ORDER BY TASK_ID DESC"
        );

        $aTaskIds = [];
        while ($arRow = $rsTaskIds->Fetch()) {
            $aTaskIds[] = (int)$arRow['TASK_ID'];
        }

        return $aTaskIds;
    }

    /**
     * Render task details section
     */
    public static function renderTaskDetails($iTaskId)
    {
        global $DB;

        $html = '<div id="task-responsible-details" class="task-responsible-details">';

        // Get responsible employee
        $rsMapping = $DB->Query(
            "SELECT USER_ID FROM b_responsible_role_mapping 
             WHERE TASK_ID = " . (int)$iTaskId . " LIMIT 1"
        );

        if ($arMapping = $rsMapping->Fetch()) {
            $iUserId = (int)$arMapping['USER_ID'];

            // Get user info
            $rsUser = $DB->Query(
                "SELECT NAME, LAST_NAME, EMAIL FROM b_user WHERE ID = " . $iUserId . " LIMIT 1"
            );

            if ($arUser = $rsUser->Fetch()) {
                $sUserName = trim($arUser['NAME'] . ' ' . $arUser['LAST_NAME']);
                $sEmail = $arUser['EMAIL'];

                $html .= '<div class="task-responsible-section">';
                $html .= '<h4>Ответственный</h4>';
                $html .= '<div class="responsible-employee-info">';
                $html .= '<span class="employee-name">' . htmlspecialchars($sUserName) . '</span>';
                $html .= '<span class="employee-email">(' . htmlspecialchars($sEmail) . ')</span>';
                $html .= '</div>';
                $html .= '</div>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get responsible employee info
     */
    public static function getResponsibleInfo($iTaskId)
    {
        global $DB;

        $rsMapping = $DB->Query(
            "SELECT USER_ID FROM b_responsible_role_mapping 
             WHERE TASK_ID = " . (int)$iTaskId . " LIMIT 1"
        );

        if ($arMapping = $rsMapping->Fetch()) {
            $iUserId = (int)$arMapping['USER_ID'];

            $rsUser = $DB->Query(
                "SELECT ID, NAME, LAST_NAME, EMAIL FROM b_user WHERE ID = " . $iUserId . " LIMIT 1"
            );

            if ($arUser = $rsUser->Fetch()) {
                return [
                    'id' => $arUser['ID'],
                    'name' => trim($arUser['NAME'] . ' ' . $arUser['LAST_NAME']),
                    'email' => $arUser['EMAIL']
                ];
            }
        }

        return null;
    }
}

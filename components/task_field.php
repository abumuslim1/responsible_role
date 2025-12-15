<?php
/**
 * Bitrix24 Module: Responsible Role
 * Task field component
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

// Include helper class
if (file_exists(__DIR__ . '/../class.php')) {
    require_once(__DIR__ . '/../class.php');
}

class ResponsibleRoleTaskFieldComponent
{
    private $taskId = null;
    private $currentUser = null;
    private $users = [];
    private $fieldName = 'UF_RESPONSIBLE_EMPLOYEE';

    public function __construct($taskId = null)
    {
        $this->taskId = $taskId;
        $this->loadUsers();
        $this->loadCurrentUser();
    }

    /**
     * Load all active users
     */
    private function loadUsers()
    {
        global $DB;

        $rsUsers = $DB->Query(
            "SELECT ID, NAME, LAST_NAME, EMAIL FROM b_user 
             WHERE ACTIVE = 'Y' 
             ORDER BY NAME, LAST_NAME"
        );

        $this->users = [];
        while ($arUser = $rsUsers->Fetch()) {
            $sFullName = trim($arUser['NAME'] . ' ' . $arUser['LAST_NAME']);
            $this->users[$arUser['ID']] = $sFullName;
        }
    }

    /**
     * Load current responsible employee
     */
    private function loadCurrentUser()
    {
        if (!$this->taskId) {
            return;
        }

        if (class_exists('ResponsibleRoleHelper')) {
            $this->currentUser = ResponsibleRoleHelper::getResponsibleEmployee($this->taskId);
        }
    }

    /**
     * Render field HTML
     */
    public function render()
    {
        $html = '<div id="responsible-role-field" class="responsible-role-field-wrapper">';
        $html .= $this->renderLabel();
        $html .= $this->renderSelect();
        $html .= $this->renderInfo();
        $html .= '</div>';

        return $html;
    }

    /**
     * Render label
     */
    private function renderLabel()
    {
        return '<label for="responsible-employee-select" class="responsible-role-label">' .
            'Ответственный сотрудник' .
            '</label>';
    }

    /**
     * Render select dropdown
     */
    private function renderSelect()
    {
        $html = '<select id="responsible-employee-select" ' .
            'class="responsible-role-select" ' .
            'name="' . htmlspecialchars($this->fieldName) . '">';

        $html .= '<option value="">-- Выберите сотрудника --</option>';

        foreach ($this->users as $userId => $userName) {
            $selected = ($this->currentUser == $userId) ? ' selected="selected"' : '';
            $html .= '<option value="' . htmlspecialchars($userId) . '"' . $selected . '>' .
                htmlspecialchars($userName) .
                '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Render info section
     */
    private function renderInfo()
    {
        return '<div class="responsible-role-info"></div>';
    }

    /**
     * Get JavaScript configuration
     */
    public function getJSConfig()
    {
        return [
            'taskId' => $this->taskId,
            'fieldName' => $this->fieldName,
            'users' => $this->users,
            'currentUser' => $this->currentUser
        ];
    }

    /**
     * Render full field with JavaScript
     */
    public function renderFull()
    {
        $html = $this->render();

        // Add JavaScript configuration
        $jsConfig = json_encode($this->getJSConfig());
        $html .= '<script type="text/javascript">';
        $html .= 'window.RESPONSIBLE_ROLE_CONFIG = ' . $jsConfig . ';';
        $html .= '</script>';

        return $html;
    }
}

// Handle AJAX requests
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'get_responsible_employees') {
    header('Content-Type: application/json');

    global $DB;

    $iTaskId = isset($_REQUEST['task_id']) ? (int)$_REQUEST['task_id'] : 0;

    if ($iTaskId <= 0) {
        echo json_encode(['error' => 'Invalid task ID']);
        exit;
    }

    if (class_exists('ResponsibleRoleHelper')) {
        $aEmployees = ResponsibleRoleHelper::getResponsibleEmployees($iTaskId);
        $aResult = [];

        foreach ($aEmployees as $iUserId) {
            $arUser = ResponsibleRoleHelper::getUserInfo($iUserId);
            if ($arUser) {
                $aResult[] = [
                    'id' => $arUser['ID'],
                    'name' => $arUser['NAME'] . ' ' . $arUser['LAST_NAME'],
                    'email' => $arUser['EMAIL']
                ];
            }
        }

        echo json_encode(['success' => true, 'employees' => $aResult]);
    } else {
        echo json_encode(['error' => 'Helper class not found']);
    }

    exit;
}

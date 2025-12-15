<?php
/**
 * Bitrix24 Module: Responsible Role
 * Adds "Responsible" role to Tasks with custom field for employee selection
 */

// Prevent direct access
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

IncludeModuleLangFile(__FILE__);

class responsible_role
{
    const MODULE_ID = 'responsible_role';
    const ROLE_NAME = 'Responsible';
    const ROLE_CODE = 'responsible';
    const FIELD_NAME = 'UF_RESPONSIBLE_EMPLOYEE';

    /**
     * Install module
     */
    public function DoInstall()
    {
        global $APPLICATION;
        
        // Register module
        RegisterModule(self::MODULE_ID);
        
        // Create custom field for responsible employee
        $this->CreateCustomField();
        
        // Create new role
        $this->CreateRole();
        
        // Create database table for role mapping
        $this->CreateDatabase();
        
        // Register event handlers
        $this->RegisterEventHandlers();
        
        $APPLICATION->IncludeAdminFile(
            GetMessage("RESPONSIBLE_ROLE_INSTALL_TITLE"),
            __DIR__ . "/step.php"
        );
    }

    /**
     * Uninstall module
     */
    public function DoUninstall()
    {
        global $APPLICATION;
        
        // Unregister module
        UnRegisterModule(self::MODULE_ID);
        
        // Delete custom field
        $this->DeleteCustomField();
        
        // Delete role
        $this->DeleteRole();
        
        // Drop database table
        $this->DropDatabase();
        
        // Unregister event handlers
        $this->UnRegisterEventHandlers();
        
        $APPLICATION->IncludeAdminFile(
            GetMessage("RESPONSIBLE_ROLE_UNINSTALL_TITLE"),
            __DIR__ . "/unstep.php"
        );
    }

    /**
     * Create custom field for responsible employee
     */
    private function CreateCustomField()
    {
        if (!CModule::IncludeModule('crm')) {
            return false;
        }

        // Check if field already exists
        $rsField = CUserFieldEnum::GetList(
            [],
            ['FIELD_NAME' => self::FIELD_NAME]
        );
        
        if ($rsField->Fetch()) {
            return true;
        }

        // Create user field for tasks
        $oUserField = new CUserTypeEntity();
        $aUserField = [
            'ENTITY_ID' => 'TASKS',
            'FIELD_NAME' => self::FIELD_NAME,
            'USER_TYPE_ID' => 'enumeration',
            'XML_ID' => 'RESPONSIBLE_EMPLOYEE',
            'SORT' => 100,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'Y',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_FORM_LABEL' => [
                'en' => 'Responsible Employee',
                'ru' => 'Ответственный сотрудник'
            ],
            'LIST_FILTER_LABEL' => [
                'en' => 'Responsible Employee',
                'ru' => 'Ответственный сотрудник'
            ],
            'LIST_COLUMN_LABEL' => [
                'en' => 'Responsible',
                'ru' => 'Ответственный'
            ],
            'LIST_FILTER_TYPE' => 'L',
            'HELP_MESSAGE' => [
                'en' => 'Select responsible employee',
                'ru' => 'Выберите ответственного сотрудника'
            ]
        ];

        $iFieldId = $oUserField->Add($aUserField);
        return $iFieldId > 0;
    }

    /**
     * Delete custom field
     */
    private function DeleteCustomField()
    {
        if (!CModule::IncludeModule('crm')) {
            return false;
        }

        $rsField = CUserFieldEnum::GetList(
            [],
            ['FIELD_NAME' => self::FIELD_NAME]
        );
        
        if ($arField = $rsField->Fetch()) {
            $oUserField = new CUserTypeEntity();
            return $oUserField->Delete($arField['ID']);
        }

        return true;
    }

    /**
     * Create new role in tasks
     */
    private function CreateRole()
    {
        global $DB;

        // Get role ID for "Observer" role to copy permissions
        $rsObserverRole = $DB->Query(
            "SELECT ID FROM b_tasks_role WHERE CODE = 'observer' LIMIT 1"
        );
        
        if (!($arObserverRole = $rsObserverRole->Fetch())) {
            // If observer role doesn't exist, create with default permissions
            return $this->CreateDefaultRole();
        }

        // Check if role already exists
        $rsExistingRole = $DB->Query(
            "SELECT ID FROM b_tasks_role WHERE CODE = '" . self::ROLE_CODE . "' LIMIT 1"
        );
        
        if ($rsExistingRole->Fetch()) {
            return true;
        }

        // Create new role
        $iRoleId = $DB->Add('b_tasks_role', [
            'CODE' => self::ROLE_CODE,
            'NAME' => self::ROLE_NAME,
            'SORT' => 100,
            'CREATED' => new CDatabase('NOW()'),
            'MODIFIED' => new CDatabase('NOW()')
        ]);

        if (!$iRoleId) {
            return false;
        }

        // Copy permissions from observer role
        $rsPermissions = $DB->Query(
            "SELECT * FROM b_tasks_role_permission WHERE ROLE_ID = " . (int)$arObserverRole['ID']
        );
        
        while ($arPermission = $rsPermissions->Fetch()) {
            $DB->Add('b_tasks_role_permission', [
                'ROLE_ID' => $iRoleId,
                'PERMISSION_ID' => $arPermission['PERMISSION_ID'],
                'VALUE' => $arPermission['VALUE']
            ]);
        }

        return true;
    }

    /**
     * Create default role if observer doesn't exist
     */
    private function CreateDefaultRole()
    {
        global $DB;

        $iRoleId = $DB->Add('b_tasks_role', [
            'CODE' => self::ROLE_CODE,
            'NAME' => self::ROLE_NAME,
            'SORT' => 100,
            'CREATED' => new CDatabase('NOW()'),
            'MODIFIED' => new CDatabase('NOW()')
        ]);

        if (!$iRoleId) {
            return false;
        }

        // Add default permissions (view only)
        $aDefaultPermissions = [
            'TASK_VIEW' => 'Y',
            'TASK_COMMENT' => 'Y',
            'TASK_EDIT' => 'N',
            'TASK_DELETE' => 'N'
        ];

        foreach ($aDefaultPermissions as $sPermissionId => $sValue) {
            $DB->Add('b_tasks_role_permission', [
                'ROLE_ID' => $iRoleId,
                'PERMISSION_ID' => $sPermissionId,
                'VALUE' => $sValue
            ]);
        }

        return true;
    }

    /**
     * Delete role
     */
    private function DeleteRole()
    {
        global $DB;

        // Delete role permissions first
        $DB->Query(
            "DELETE FROM b_tasks_role_permission WHERE ROLE_ID IN (
                SELECT ID FROM b_tasks_role WHERE CODE = '" . self::ROLE_CODE . "'
            )"
        );

        // Delete role
        $DB->Query(
            "DELETE FROM b_tasks_role WHERE CODE = '" . self::ROLE_CODE . "'"
        );

        return true;
    }

    /**
     * Create database table for role mapping
     */
    private function CreateDatabase()
    {
        global $DB;

        $sTableName = 'b_responsible_role_mapping';
        
        // Check if table exists
        $rsTable = $DB->Query(
            "SHOW TABLES LIKE '" . $sTableName . "'"
        );
        
        if ($rsTable->Fetch()) {
            return true;
        }

        // Create table
        $DB->Query(
            "CREATE TABLE IF NOT EXISTS " . $sTableName . " (
                ID INT AUTO_INCREMENT PRIMARY KEY,
                TASK_ID INT NOT NULL,
                ROLE_ID INT NOT NULL,
                USER_ID INT NOT NULL,
                CREATED DATETIME DEFAULT CURRENT_TIMESTAMP,
                MODIFIED DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_task_role (TASK_ID, ROLE_ID),
                FOREIGN KEY (TASK_ID) REFERENCES b_tasks(ID) ON DELETE CASCADE,
                FOREIGN KEY (ROLE_ID) REFERENCES b_tasks_role(ID) ON DELETE CASCADE,
                FOREIGN KEY (USER_ID) REFERENCES b_user(ID) ON DELETE CASCADE
            )"
        );

        return true;
    }

    /**
     * Drop database table
     */
    private function DropDatabase()
    {
        global $DB;

        $DB->Query("DROP TABLE IF EXISTS b_responsible_role_mapping");
        return true;
    }

    /**
     * Register event handlers
     */
    private function RegisterEventHandlers()
    {
        RegisterModuleEvent(
            self::MODULE_ID,
            'OnTaskAdd',
            'responsible_role',
            'OnTaskAdd'
        );

        RegisterModuleEvent(
            self::MODULE_ID,
            'OnTaskUpdate',
            'responsible_role',
            'OnTaskUpdate'
        );

        RegisterModuleEvent(
            self::MODULE_ID,
            'OnTaskDelete',
            'responsible_role',
            'OnTaskDelete'
        );
    }

    /**
     * Unregister event handlers
     */
    private function UnRegisterEventHandlers()
    {
        UnRegisterModuleEvent(
            self::MODULE_ID,
            'OnTaskAdd',
            'responsible_role',
            'OnTaskAdd'
        );

        UnRegisterModuleEvent(
            self::MODULE_ID,
            'OnTaskUpdate',
            'responsible_role',
            'OnTaskUpdate'
        );

        UnRegisterModuleEvent(
            self::MODULE_ID,
            'OnTaskDelete',
            'responsible_role',
            'OnTaskDelete'
        );
    }
}

// Create instance and call install
$oModule = new responsible_role();
$oModule->DoInstall();

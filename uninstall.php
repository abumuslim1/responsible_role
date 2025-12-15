<?php
/**
 * Bitrix24 Module: Responsible Role
 * Uninstall script
 */

// Prevent direct access
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

IncludeModuleLangFile(__FILE__);

class responsible_role
{
    const MODULE_ID = 'responsible_role';
    const ROLE_CODE = 'responsible';
    const FIELD_NAME = 'UF_RESPONSIBLE_EMPLOYEE';

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
     * Drop database table
     */
    private function DropDatabase()
    {
        global $DB;

        $DB->Query("DROP TABLE IF EXISTS b_responsible_role_mapping");
        return true;
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

// Create instance and call uninstall
$oModule = new responsible_role();
$oModule->DoUninstall();

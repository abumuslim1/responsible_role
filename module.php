<?php
/**
 * Bitrix24 Module: Responsible Role
 * Module registration file
 */

// Prevent direct access
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

// Module version and description
$arModuleVersion = array(
    "VERSION" => "1.0.0",
    "VERSION_DATE" => "2025-12-15"
);

// Module description
$arModuleDescription = array(
    "NAME" => "Responsible Role for Tasks",
    "DESCRIPTION" => "Adds a new 'Responsible' role to Bitrix24 Tasks with custom field for employee selection",
    "AUTHOR" => "Bitrix24 Development",
    "PARTNER" => "",
    "LICENSE" => "",
    "HELP_SECTION_ID" => ""
);

// Module rights
$arModuleRights = array(
    "reference_id" => array("responsible_role"),
    "reference" => array("Responsible Role")
);

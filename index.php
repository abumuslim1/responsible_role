<?php
/**
 * Bitrix24 Module: Responsible Role
 * Module initialization file
 */

// Prevent direct access
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

// Include language file
IncludeModuleLangFile(__FILE__);

// Include main class
require_once(__DIR__ . '/class.php');

// Include event handlers
require_once(__DIR__ . '/events.php');

// Include REST API
require_once(__DIR__ . '/rest_api.php');

// Initialize module
if (class_exists('ResponsibleRoleEventHandler')) {
    ResponsibleRoleEventHandler::registerHandlers();
}

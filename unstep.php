<?php
/**
 * Uninstallation success page
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

?>
<div class="adm-info-message-wrap">
    <div class="adm-info-message">
        <div class="adm-info-message-title">
            <?php echo GetMessage("RESPONSIBLE_ROLE_UNINSTALL_SUCCESS"); ?>
        </div>
        <div class="adm-info-message-icon"></div>
        <div class="adm-info-message-text">
            <p>The "Responsible Role" module has been successfully removed from your Bitrix24 system.</p>
            <p>
                The following items have been removed:
                <ul>
                    <li>"Responsible" role from Tasks</li>
                    <li>Custom field "Responsible Employee"</li>
                    <li>All associated data and mappings</li>
                </ul>
            </p>
        </div>
    </div>
</div>

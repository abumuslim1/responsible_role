<?php
/**
 * Installation success page
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

?>
<div class="adm-info-message-wrap">
    <div class="adm-info-message">
        <div class="adm-info-message-title">
            <?php echo GetMessage("RESPONSIBLE_ROLE_INSTALL_SUCCESS"); ?>
        </div>
        <div class="adm-info-message-icon"></div>
        <div class="adm-info-message-text">
            <p><?php echo GetMessage("RESPONSIBLE_ROLE_MODULE_DESC"); ?></p>
            <p>
                The following features have been added:
                <ul>
                    <li>New "Responsible" role in Tasks with Observer permissions</li>
                    <li>Custom field "Responsible Employee" for task selection</li>
                    <li>Task filtering by responsible employee</li>
                    <li>Role display in task details</li>
                </ul>
            </p>
        </div>
    </div>
</div>

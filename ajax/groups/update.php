<?php

/**
 * This file contains package_quiqqer_tax_ajax_groups_update
 */

/**
 * Update a tax group
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_groups_update',
    function () {
        $Handler = new QUI\ERP\Tax\Handler();

    },
    array(),
    'Permission::checkAdminUser'
);

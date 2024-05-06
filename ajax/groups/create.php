<?php

/**
 * This file contains package_quiqqer_tax_ajax_groups_create
 */

/**
 * Create a tax group
 *
 * @return integer
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_groups_create',
    function () {
        $Handler = new QUI\ERP\Tax\Handler();
        return $Handler->createTaxGroup()->getId();
    },
    false,
    'Permission::checkAdminUser'
);

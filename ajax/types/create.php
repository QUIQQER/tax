<?php

/**
 * This file contains package_quiqqer_tax_ajax_types_create
 */

/**
 * Create a tax type
 *
 * @return integer
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_types_create',
    function () {
        $Handler = new QUI\ERP\Tax\Handler();

        return $Handler->createTaxType()->getId();
    },
    false,
    'Permission::checkAdminUser'
);

<?php

/**
 * This file contains package_quiqqer_tax_ajax_getAvailableTax
 */

/**
 * @return bool
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_getAvailableTax',
    function () {
        return QUI\ERP\Tax\Utils::getAvailableTaxList();
    },
    false,
    'Permission::checkAdminUser'
);

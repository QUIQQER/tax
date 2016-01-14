<?php

/**
 * This file contains package_quiqqer_tax_ajax_entries_getList
 */

/**
 * Return all tax entries
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_entries_getList',
    function () {
        $Handler = new QUI\ERP\Tax\Handler();
        return $Handler->getChildrenData();
    },
    array(),
    'Permission::checkAdminUser'
);

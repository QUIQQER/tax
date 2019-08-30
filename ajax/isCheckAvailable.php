<?php

/**
 * This file contains package_quiqqer_tax_ajax_types_create
 */

/**
 * @return bool
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_tax_ajax_isCheckAvailable',
    function () {
        return \class_exists('\SoapClient');
    },
    false,
    'Permission::checkAdminUser'
);

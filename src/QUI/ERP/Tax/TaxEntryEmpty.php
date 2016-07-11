<?php

/**
 * This file contains QUI\ERP\Tax\TaxEntry
 */
namespace QUI\ERP\Tax;

use QUI;
use QUI\Permissions\Permission;

/**
 * Class Tax
 * Steuersatz
 *
 * @package QUI\ERP\Areas
 */
class TaxEntryEmpty extends QUI\QDOM
{
    /**
     * Area constructor
     */
    public function __construct()
    {

    }

    /**
     * Return the area
     *
     * @return null|QUI\ERP\Areas\Area
     */
    public function getArea()
    {
        return null;
    }

    /**
     * Return the vat value
     *
     * @return integer
     */
    public function getValue()
    {
        return (int)0;
    }

    /**
     * Is the tax active?
     *
     * @return boolean
     */
    public function isActive()
    {
        return true;
    }
}

<?php

/**
 * This file contains QUI\ERP\Tax\TaxEntry
 */

namespace QUI\ERP\Tax;

use QUI;

/**
 * Class Tax
 * Steuersatz
 *
 * @package QUI\ERP\Areas
 */
class TaxEntryEmpty extends QUI\QDOM
{
    /**
     * @var null
     */
    protected $Area = null;

    /**
     * Area constructor
     *
     * @param array $params - optionals
     *  [Area]
     */
    public function __construct($params = [])
    {
        if (isset($params['Area']) && $params['Area'] instanceof QUI\ERP\Areas\Area) {
            $this->Area = $params['Area'];
        }
    }

    /**
     * Return the area
     *
     * @return null|QUI\ERP\Areas\Area
     */
    public function getArea(): ?QUI\ERP\Areas\Area
    {
        return $this->Area;
    }

    /**
     * Return the vat value
     *
     * @return integer
     */
    public function getValue(): int
    {
        return (int)0;
    }

    /**
     * Is the tax active?
     *
     * @return boolean
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return false;
    }
}

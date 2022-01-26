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
     * @var null|QUI\ERP\Areas\Area
     */
    protected ?QUI\ERP\Areas\Area $Area = null;

    /**
     * Area constructor
     *
     * @param array $params - optionals
     *  [Area]
     */
    public function __construct(array $params = [])
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
     * @return integer|float
     */
    public function getValue(): float
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return -1;
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

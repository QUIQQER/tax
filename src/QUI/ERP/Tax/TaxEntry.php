<?php

/**
 * This file contains QUI\ERP\Tax\TaxEntry
 */

namespace QUI\ERP\Tax;

use QUI;
use QUI\Permissions\Permission;

use function floatval;
use function in_array;

/**
 * Class Tax
 * Steuersatz
 *
 * @package QUI\ERP\Areas
 */
class TaxEntry extends QUI\CRUD\Child
{
    /**
     * @var null|QUI\ERP\Areas\Area
     */
    protected ?QUI\ERP\Areas\Area $Area = null;

    /**
     * Area constructor.
     *
     * @param int $id
     * @param QUI\CRUD\Factory $Factory
     */
    public function __construct(int $id, QUI\CRUD\Factory $Factory)
    {
        parent::__construct($id, $Factory);

        // delete check
        $this->Events->addEvent('onDeleteBegin', function () {
            Permission::checkPermission('quiqqer.tax.delete');
        });

        // delete -> translation
        $this->Events->addEvent('onDeleteEnd', function () {
            QUI\Translator::delete(
                'quiqqer/tax',
                'tax.' . $this->getId() . '.title'
            );

            QUI\Translator::delete(
                'quiqqer/tax',
                'tax.' . $this->getId() . '.workingTitle'
            );

            QUI\Translator::delete(
                'quiqqer/tax',
                'tax.' . $this->getId() . '.description'
            );
        });

        // Update check
        $this->Events->addEvent('onUpdateBegin', function () {
            Permission::checkPermission('quiqqer.tax.edit');

            $Area      = $this->getArea();
            $children  = $this->Factory->getChildrenData();
            $usedAreas = [];

            if (!$Area) {
                throw new QUI\Exception([
                    'quiqqer/tax',
                    'exception.area.not.found'
                ]);
            }

            // params
            $this->setAttribute('vat', floatval($this->getAttribute('vat')));
            $this->setAttribute('active', (int)$this->getAttribute('active'));
            $this->setAttribute('euvat', (int)$this->getAttribute('euvat'));

            // we can use only unused areas
            foreach ($children as $child) {
                // ignore me
                if ($child['id'] == $this->getId()) {
                    continue;
                }

                if ((int)$child['taxGroupId'] != (int)$this->getAttribute('taxGroupId')) {
                    continue;
                }

                if ((int)$child['taxTypeId'] != (int)$this->getAttribute('taxTypeId')) {
                    continue;
                }

                $usedAreas[] = $child['areaId'];
            }

            if (in_array($this->getArea()->getId(), $usedAreas)) {
                throw new QUI\Exception([
                    'quiqqer/tax',
                    'exception.area.is.still.in.use',
                    [
                        'area'  => $this->getArea()->getId(),
                        'title' => $this->getArea()->getTitle(),
                    ]
                ]);
            }
        });
    }

    /**
     * Return the area
     *
     * @return null|QUI\ERP\Areas\Area
     */
    public function getArea(): ?QUI\ERP\Areas\Area
    {
        if ($this->Area) {
            return $this->Area;
        }

        $areaId = $this->getAttribute('areaId');

        if ($areaId !== false) {
            try {
                $Areas      = new QUI\ERP\Areas\Handler();
                $this->Area = $Areas->getChild($this->getAttribute('areaId'));

                return $this->Area;
            } catch (QUI\Exception $Exception) {
            }
        }

        return null;
    }

    /**
     * Return the vat value
     *
     * @return float
     */
    public function getValue(): float
    {
        return (float)$this->getAttribute('vat');
    }

    /**
     * Is the tax active?
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->getAttribute('active');
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return true;
    }
}

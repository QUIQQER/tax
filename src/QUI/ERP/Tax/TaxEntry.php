<?php

/**
 * This file contains QUI\ERP\Tax\TaxEntry
 */
namespace QUI\ERP\Tax;

use QUI;
use QUI\Rights\Permission;

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
    protected $Area = null;

    /**
     * Area constructor.
     *
     * @param int $id
     * @param QUI\CRUD\Factory $Factory
     */
    public function __construct($id, QUI\CRUD\Factory $Factory)
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
        });

        // Update check
        $this->Events->addEvent('onUpdateBegin', function () {
            Permission::checkPermission('quiqqer.tax.edit');

            // we must check if the areas in this tax is not used
            $Area      = $this->getArea();
            $children  = $this->Factory->getChildrenData();
            $usedAreas = array();

            if (!$Area) {
                throw new QUI\Exception(array(
                    'quiqqer/tax',
                    'exception.area.not.found'
                ));
            }

            foreach ($children as $child) {
                // ignore me
                if ($child['id'] == $this->getId()) {
                    continue;
                }

                $usedAreas[] = $child['areaId'];
            }

            if (in_array($this->getArea()->getId(), $usedAreas)) {
                throw new QUI\Exception(array(
                    'quiqqer/tax',
                    'exception.area.is.still.in.use'
                ));
            }
        });
    }

    /**
     * Return the area
     *
     * @return null|QUI\ERP\Areas\Area
     */
    public function getArea()
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
}

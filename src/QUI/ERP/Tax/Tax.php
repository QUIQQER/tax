<?php

/**
 * This file contains QUI\ERP\Tax\Tax
 */
namespace QUI\ERP\Tax;

use QUI;
use QUI\Rights\Permission;

/**
 * Class Handler
 *
 * @package QUI\ERP\Areas
 */
class Tax extends QUI\CRUD\Child
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

        $this->Events->addEvent('onDeleteBegin', function () {
            Permission::checkPermission('quiqqer.tax.delete');
        });

        $this->Events->addEvent('onDeleteEnd', function () {
            QUI\Translator::delete(
                'quiqqer/tax',
                'tax.' . $this->getId() . '.title'
            );
        });

        $this->Events->addEvent('onSaveBegin', function () {
            Permission::checkPermission('quiqqer.tax.edit');
        });
    }

    /**
     * Return the area
     *
     * @return null|QUI\ERP\Areas\Area
     */
    public function getArea()
    {
        return $this->Area;
    }
}

<?php

/**
 * This fiel contains QUI\ERP\Tax\Utils
 */
namespace QUI\ERP\Tax;

use QUI;
use QUI\ERP\Areas\Area;
use QUI\Interfaces\Users\User;
use QUI\ERP\Products\Utils\User as ProductUserUtils;

/**
 * Class Utils
 */
class Utils
{
    /**
     * user taxes cache
     *
     * @var array
     */
    protected static $userTaxes = array();

    /**
     * Return the shop tax tapx
     *
     * @return TaxEntryEmpty|TaxType
     */
    public static function getShopTaxType()
    {
        $Package     = QUI::getPackage('quiqqer/tax');
        $Config      = $Package->getConfig();
        $standardTax = $Config->getValue('shop', 'vat');
        $Handler     = new Handler();

        if (!$standardTax) {
            return new TaxEntryEmpty();
        }

        $standardTax = explode(':', $standardTax);

        if (!isset($standardTax[1])) {
            return new TaxEntryEmpty();
        }

        return $Handler->getTaxType($standardTax[1]);
    }

    /**
     * Return the taxtype from the user
     *
     * @param User $User
     * @return QUI\ERP\Tax\TaxEntry|TaxEntryEmpty
     */
    public static function getTaxByUser(User $User)
    {
        $uid = $User->getId();

        if (isset(self::$userTaxes[$uid])) {
            return self::$userTaxes[$uid];
        }

        try {
            $Country = $User->getCountry();

            if (!$Country) {
                throw new QUI\Exception('County not found');
            }

            $Area = QUI\ERP\Areas\Utils::getAreaByCountry($Country);

            // standard tax
            if (!$Area) {
                $Config = QUI::getPackage('quiqqer/tax')->getConfig();
                $Areas  = new QUI\ERP\Areas\Handler();
                $areaId = $Config->getValue('shop', 'area');
                $Area   = $Areas->getChild($areaId);
            }

            $TaxEntry = self::getTaxTypeByArea($Area);

            // Wenn Benutzer EU VAT user ist und der Benutzer eine Umsatzsteuer-ID eingetragen hat
            // dann ist VAT 0
            if ($TaxEntry->getAttribute('euvat') &&
                $User->getAttribute('quiqqer.erp.euVatId')
            ) {
                self::$userTaxes[$uid] = new TaxEntryEmpty();
            } else {
                self::$userTaxes[$uid] = $TaxEntry;
            }

        } catch (QUI\Exception $Exception) {
            $Country = $User->getCountry();
            $Area    = QUI\ERP\Areas\Utils::getAreaByCountry($Country);

            if (!$Area) {
                $Area = QUI\ERP\Products\Utils\User::getShopArea();
            }

            // tax entry via the taxtype from the shop
            self::$userTaxes[$uid] = self::getTaxEntry(self::getShopTaxType(), $Area);
        }

        return self::$userTaxes[$uid];
    }

    /**
     * Return the tax message for an user
     *
     * @param User $User
     * @return string
     */
    public static function getTaxTextByUser(User $User)
    {
        $Tax = self::getTaxByUser($User);
        $vat = $Tax->getValue() . '%';

        if (ProductUserUtils::isNettoUser($User)) {
            if (self::isUserEuVatUser($User)) {
                return $User->getLocale()->get(
                    'quiqqer/tax',
                    'message.vat.text.netto.EUVAT',
                    array('vat' => $vat)
                );
            }

            return $User->getLocale()->get(
                'quiqqer/tax',
                'message.vat.text.netto',
                array('vat' => $vat)
            );
        }

        if (self::isUserEuVatUser($User)) {
            return $User->getLocale()->get(
                'quiqqer/tax',
                'message.vat.text.brutto.EUVAT',
                array('vat' => $vat)
            );
        }

        return $User->getLocale()->get(
            'quiqqer/tax',
            'message.vat.text.brutto',
            array('vat' => $vat)
        );
    }

    /**
     * Use the user EU vat?
     *
     * @param User $User
     * @return boolean
     */
    public static function isUserEuVatUser(User $User)
    {
        return self::getTaxByUser($User)->getAttribute('euvat');
    }

    /**
     * Return the tax by an area
     *
     * @param Area $Area
     * @return QUI\ERP\Tax\TaxEntry
     * @throws QUI\Exception
     */
    public static function getTaxTypeByArea(Area $Area)
    {
        $Taxes  = new Handler();
        $result = $Taxes->getChildren(array(
            'where' => array(
                'areaId' => $Area->getId()
            )
        ));

        if (!isset($result[0])) {
            throw new QUI\Exception(array(
                'quiqqer/tax',
                'exception.taxtype.not.found'
            ));
        }

        if (count($result) == 1) {
            return $result[0];
        }

        /* @var $TaxEntry QUI\ERP\Tax\TaxEntry */
        $TaxEntry = $result[0];

        $taxGroup = $TaxEntry->getAttribute('group');
        $Group    = $Taxes->getTaxGroup($taxGroup);
        $taxtypes = $Group->getTaxTypes();

        /* @var $TaxType TaxType */
        foreach ($taxtypes as $TaxType) {
            foreach ($result as $TaxEntry) {
                if ($TaxEntry->getAttribute('taxTypeId') == $TaxType->getId()) {
                    return $TaxEntry;
                }
            }
        }

        throw new QUI\Exception(array(
            'quiqqer/tax',
            'exception.taxtype.not.found'
        ));
    }

    /**
     * Return all tax entries in the tax type
     *
     * @param $taxTypeId
     * @return array
     */
    public static function getTaxEntriesByTaxType($taxTypeId)
    {
        $Handler = new QUI\ERP\Tax\Handler();
        $Areas   = new QUI\ERP\Areas\Handler();
        $result  = array();

        $data = $Handler->getChildrenData(array(
            'where' => array(
                'taxTypeId' => $taxTypeId
            )
        ));

        foreach ($data as $key => $entry) {
            try {
                $result[] = $Areas->getChild($entry['areaId']);
            } catch (QUI\Exception $Exception) {
            }
        }

        return $result;
    }

    /**
     * Search a tax entry by its type and its area
     *
     * @param TaxType $TaxType
     * @param Area $Area
     *
     * @return TaxEntry
     * @throws QUI\Exception
     */
    public static function getTaxEntry(TaxType $TaxType, Area $Area)
    {
        $Taxes = new Handler();
        $Group = $TaxType->getGroup();

        $result = $Taxes->getChildren(array(
            'where' => array(
                'areaId'     => $Area->getId(),
                'taxTypeId'  => $TaxType->getId(),
                'taxGroupId' => $Group->getId()
            )
        ));

        if (!isset($result[0])) {
            throw new QUI\Exception(array(
                'quiqqer/tax',
                'exception.taxentry.not.found'
            ));
        }

        return $result[0];
    }
}

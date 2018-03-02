<?php

/**
 * This file contains QUI\ERP\Tax\Utils
 */

namespace QUI\ERP\Tax;

use QUI;
use QUI\ERP\Areas\Area;
use QUI\Interfaces\Users\User;

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
     * @return TaxType|false
     *
     * @throws QUI\Exception
     */
    public static function getShopTaxType()
    {
        $Package     = QUI::getPackage('quiqqer/tax');
        $Config      = $Package->getConfig();
        $standardTax = $Config->getValue('shop', 'vat');

        if (!$standardTax) {
            return false;
        }

        $standardTax = explode(':', $standardTax);

        if (!isset($standardTax[1])) {
            return false;
        }

        return Handler::getInstance()->getTaxType($standardTax[1]);
    }

    /**
     * Return the taxtype from the user
     *
     * @param User $User
     * @return QUI\ERP\Tax\TaxEntry|TaxEntryEmpty
     * @throws QUI\Exception
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
                throw new QUI\Exception('Country not found');
            }

            $Area = QUI\ERP\Areas\Utils::getAreaByCountry($Country);

            // standard tax
            if (!$Area) {
                $Config = QUI::getPackage('quiqqer/tax')->getConfig();
                $Areas  = new QUI\ERP\Areas\Handler();
                $areaId = $Config->getValue('shop', 'area');
                $Area   = $Areas->getChild($areaId);
            }

            $TaxType = self::getTaxTypeByArea($Area);

            if ($TaxType instanceof TaxGroup) {
                $TaxEntry = self::getTaxEntry($TaxType, $Area);
            } elseif ($TaxType instanceof TaxEntry) {
                $TaxEntry = $TaxType;
            } else {
                throw new QUI\Exception('Tax Entry not found');
            }


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
                $Area = QUI\ERP\Defaults::getArea();
            }

            // tax entry via the taxtype from the shop
            $shopTaxType           = self::getShopTaxType();
            self::$userTaxes[$uid] = new TaxEntryEmpty();

            if ($shopTaxType) {
                try {
                    self::$userTaxes[$uid] = self::getTaxEntry($shopTaxType, $Area);
                } catch (QUI\Exception $Exception) {
                }
            }
        }

        return self::$userTaxes[$uid];
    }

    /**
     * Use the user EU vat?
     *
     * @param User $User
     * @return boolean
     */
    public static function isUserEuVatUser(User $User)
    {
        try {
            return self::getTaxByUser($User)->getAttribute('euvat');
        } catch (QUI\Exception $Exception) {
        }

        return false;
    }

    /**
     * Return the tax by an area
     *
     * @param Area $Area
     * @return QUI\ERP\Tax\TaxType
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
        $taxTypes = $Group->getTaxTypes();

        /* @var $TaxType TaxType */
        foreach ($taxTypes as $TaxType) {
            foreach ($result as $TaxEntry) {
                if ($TaxEntry->getAttribute('taxTypeId') == $TaxType->getId()) {
                    return $TaxType;
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

        if (!$Group) {
            throw new QUI\Exception(array(
                'quiqqer/tax',
                'exception.taxentry.not.found'
            ));
        }

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

    /**
     * Cleanup a VAT-ID, no validation
     *
     * @param $vatId
     * @return string
     */
    public static function cleanupVatId($vatId)
    {
        return str_replace(array(' ', '.', '-', ',', ', '), '', trim($vatId));
    }

    /**
     * Is the VAT Validation active?
     *
     * @return bool
     */
    public static function shouldVatIdValidationBeExecuted()
    {
        try {
            $Config = QUI::getPackage('quiqqer/tax')->getConfig();

            if ($Config->get('shop', 'validateVatId')) {
                return true;
            }
        } catch (QUI\Exception $Exception) {
        }

        return false;
    }

    /**
     * Validate a VAT-ID via http://ec.europa.eu/
     *
     * @param string $vatId
     * @return string
     * @throws QUI\ERP\Tax\Exception
     */
    public static function validateVatId($vatId)
    {
        $vatId = self::cleanupVatId($vatId);

        // UST-ID oder Vat-Id
        $first  = mb_substr($vatId, 0, 1);
        $second = mb_substr($vatId, 1, 1);

        if (!ctype_alpha($first) || !ctype_alpha($second)) {
            throw new QUI\ERP\Tax\Exception(array(
                'quiqqer/tax',
                'exception.invalid.vatid',
                array('vatid' => $vatId)
            ));
        }

        $cc = substr($vatId, 0, 2);
        $vn = substr($vatId, 2);

        if (!class_exists('SoapClient')) {
            QUI\System\Log::addWarning('SoapClient is not available');

            return $vatId;
        }

        $Client = new \SoapClient(
            "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl"
        );


        if (!$Client) {
            throw new QUI\ERP\Tax\Exception(
                array(
                    'quiqqer/tax',
                    'exception.vatid.validate.no.client',
                    array('vatid' => $vatId)
                ),
                503
            );
        }

        try {
            $Response = $Client->checkVat(array(
                'countryCode' => $cc,
                'vatNumber'   => $vn
            ));

            if ($Response->valid == true) {
                return $vatId;
            }

            // USt-ID ist ungültig
            throw new QUI\ERP\Tax\Exception(
                array(
                    'quiqqer/tax',
                    'exception.invalid.vatid',
                    array('vatid' => $vatId)
                ),
                403
            );
        } catch (\SoapFault $Exception) {
            switch ($Exception->getMessage()) {
                case 'INVALID_INPUT':
                    throw new QUI\ERP\Tax\Exception(
                        array(
                            'quiqqer/tax',
                            'exception.invalid.vatid',
                            array('vatid' => $vatId)
                        ),
                        403
                    );

                default:
                case 'TIMEOUT':
                case 'SERVICE_UNAVAILABLE':
                case 'MS_UNAVAILABLE':
                    throw new QUI\ERP\Tax\Exception(
                        array(
                            'quiqqer/tax',
                            'exception.vatid.validate.no.connection',
                            array('vatid' => $vatId)
                        ),
                        503
                    );
            }
        }
    }

    /**
     * Return the highest tax
     *
     * @return integer
     */
    public static function getMaxTax()
    {
        $result = QUI::getDataBase()->fetch(array(
            'select' => 'vat',
            'from'   => QUI::getDBTableName('tax'),
            'limit'  => 1,
            'order'  => 'vat DESC'
        ));

        return isset($result[0]) ? (int)$result[0]['vat'] : 0;
    }

    /**
     * Return the least tax
     *
     * @return integer
     */
    public static function getMinTax()
    {
        $result = QUI::getDataBase()->fetch(array(
            'select' => 'vat',
            'from'   => QUI::getDBTableName('tax'),
            'limit'  => 1,
            'order'  => 'vat ASC'
        ));

        return isset($result[0]) ? (int)$result[0]['vat'] : 0;
    }
}

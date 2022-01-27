<?php

/**
 * This file contains QUI\ERP\Tax\Utils
 */

namespace QUI\ERP\Tax;

use QUI;
use QUI\ERP\Areas\Area;
use QUI\Interfaces\Users\User;

use function class_exists;
use function ctype_alpha;
use function explode;
use function mb_substr;
use function str_replace;
use function substr;

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
    protected static array $userTaxes = [];

    /**
     * Returns all available vat / tax list
     *
     * @return array
     */
    public static function getAvailableTaxList(): array
    {
        try {
            return QUI::getDataBase()->fetch([
                'select' => 'vat',
                'from'   => QUI::getDBTableName('tax'),
                'group'  => 'vat'
            ]);
        } catch (QUI\DataBase\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return [];
        }
    }

    /**
     * Return the shop tax tax
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
     *
     * @throws QUI\Exception
     */
    public static function getTaxByUser(User $User)
    {
        $uid = $User->getId();

        if (isset(self::$userTaxes[$uid])) {
            return self::$userTaxes[$uid];
        }

        try {
            $DefaultTaxType  = self::getTaxTypeByArea(QUI\ERP\Defaults::getArea());
            $DefaultTaxEntry = self::getTaxEntry($DefaultTaxType, QUI\ERP\Defaults::getArea());
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::addError($Exception->getMessage(), [
                'area' => QUI\ERP\Defaults::getArea()->getTitle()
            ]);

            $DefaultTaxEntry = new TaxEntryEmpty();
        }

        self::$userTaxes[$uid] = $DefaultTaxEntry;

        try {
            if ($User->getAttribute('CurrentAddress')) {
                /* @var QUI\ERP\Address $Address */
                $Address = $User->getAttribute('CurrentAddress');
                $Country = $Address->getCountry();
            } else {
                $Country = $User->getCountry();
            }

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

            if ($TaxType instanceof TaxType) {
                $TaxEntry = self::getTaxEntry($TaxType, $Area);
            } elseif ($TaxType instanceof TaxEntry) {
                $TaxEntry = $TaxType;
            } else {
                throw new QUI\Exception('Tax Entry not found');
            }

            if (!$TaxEntry->isActive()) {
                return self::$userTaxes[$uid];
            }

            // Wenn Benutzer EU VAT user ist und der Benutzer eine Umsatzsteuer-ID eingetragen hat
            // dann ist VAT 0

            // If the user is EU VAT user and the user has entered a VAT ID, then VAT is 0 (it is no error)
            if ($TaxEntry->getAttribute('euvat') && $User->getAttribute('quiqqer.erp.euVatId')) {
                self::$userTaxes[$uid] = new TaxEntryEmpty([
                    'Area' => $Area
                ]);

                self::$userTaxes[$uid]->setAttribute('euvat', 1);
            } else {
                self::$userTaxes[$uid] = $TaxEntry;
            }

            return self::$userTaxes[$uid];
        } catch (QUI\Exception $Exception) {
            if ($Exception->getCode() === 404) {
                return self::$userTaxes[$uid];
            } else {
                QUI\System\Log::writeDebugException($Exception);
            }
        }

        // if for user cant be found a VAT, use the shop settings
        $Country = null;

        try {
            $Country = $User->getCountry();
        } catch (QUI\Exception $Exception) {
        }

        try {
            $Area = QUI\ERP\Areas\Utils::getAreaByCountry($Country);

            if (!$Area) {
                $Area = QUI\ERP\Defaults::getArea();
            }

            $ShopTaxType = self::getShopTaxType();

            if ($ShopTaxType) {
                $TaxEntry = self::getTaxEntry($ShopTaxType, $Area);

                self::$userTaxes[$uid] = $TaxEntry;
            }
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
        }

        return self::$userTaxes[$uid];
    }

    /**
     * @param User $User
     */
    public static function cleanUpUserTaxCache(User $User)
    {
        $uid = $User->getId();

        if (isset(self::$userTaxes[$uid])) {
            unset(self::$userTaxes[$uid]);
        }
    }

    /**
     * Is the user an EU VAT user?
     *
     * @param User $User
     * @return boolean
     */
    public static function isUserEuVatUser(User $User): bool
    {
        $euVatId = $User->getAttribute('quiqqer.erp.euVatId');

        if (empty($euVatId)) {
            return false;
        }

        try {
            return self::getTaxByUser($User)->getAttribute('euvat')
                && $User->getAttribute('quiqqer.erp.euVatId');
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
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
    public static function getTaxTypeByArea(Area $Area): TaxType
    {
        $Taxes  = new Handler();
        $result = $Taxes->getChildren([
            'where' => [
                'areaId' => $Area->getId(),
                'active' => 1
            ]
        ]);

        if (!isset($result[0])) {
            throw new QUI\Exception([
                'quiqqer/tax',
                'exception.taxtype.not.found'
            ], 404);
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

        throw new QUI\Exception([
            'quiqqer/tax',
            'exception.taxtype.not.found'
        ], 404);
    }

    /**
     * Return all tax entries in the tax type
     *
     * @param $taxTypeId
     * @return array
     */
    public static function getTaxEntriesByTaxType($taxTypeId): array
    {
        $Handler = new QUI\ERP\Tax\Handler();
        $Areas   = new QUI\ERP\Areas\Handler();
        $result  = [];

        try {
            $data = $Handler->getChildrenData([
                'where' => [
                    'taxTypeId' => $taxTypeId
                ]
            ]);
        } catch (QUI\DataBase\Exception $Exception) {
            return [];
        }

        foreach ($data as $entry) {
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
    public static function getTaxEntry(TaxType $TaxType, Area $Area): TaxEntry
    {
        $Taxes = new Handler();
        $Group = $TaxType->getGroup();

        if (!$Group) {
            throw new QUI\Exception([
                'quiqqer/tax',
                'exception.taxentry.not.found'
            ]);
        }

        $result = $Taxes->getChildren([
            'where' => [
                'areaId'     => $Area->getId(),
                'taxTypeId'  => $TaxType->getId(),
                'taxGroupId' => $Group->getId()
            ]
        ]);

        if (!isset($result[0])) {
            throw new QUI\Exception([
                'quiqqer/tax',
                'exception.taxentry.not.found'
            ]);
        }

        return $result[0];
    }

    /**
     * Cleanup a VAT-ID, no validation
     *
     * @param $vatId
     * @return string
     */
    public static function cleanupVatId($vatId): string
    {
        return str_replace([' ', '.', '-', ',', ', '], '', trim($vatId));
    }

    /**
     * Is the VAT Validation active?
     *
     * @return bool
     */
    public static function shouldVatIdValidationBeExecuted(): bool
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
     *
     * @throws Exception
     */
    public static function validateVatId(string $vatId): string
    {
        $vatId = self::cleanupVatId($vatId);

        // UST-ID oder Vat-Id
        $first  = mb_substr($vatId, 0, 1);
        $second = mb_substr($vatId, 1, 1);

        if (!ctype_alpha($first) || !ctype_alpha($second)) {
            throw new QUI\ERP\Tax\Exception([
                'quiqqer/tax',
                'exception.invalid.vatid',
                ['vatid' => $vatId]
            ]);
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
                [
                    'quiqqer/tax',
                    'exception.vatid.validate.no.client',
                    ['vatid' => $vatId]
                ],
                503
            );
        }

        try {
            $Response = $Client->checkVat([
                'countryCode' => $cc,
                'vatNumber'   => $vn
            ]);

            if ($Response->valid == true) {
                return $vatId;
            }

            // USt-ID ist ungültig
            throw new QUI\ERP\Tax\Exception(
                [
                    'quiqqer/tax',
                    'exception.invalid.vatid',
                    ['vatid' => $vatId]
                ],
                403
            );
        } catch (\SoapFault $Exception) {
            switch ($Exception->getMessage()) {
                case 'INVALID_INPUT':
                    throw new QUI\ERP\Tax\Exception(
                        [
                            'quiqqer/tax',
                            'exception.invalid.vatid',
                            ['vatid' => $vatId]
                        ],
                        403
                    );

                default:
                case 'TIMEOUT':
                case 'SERVICE_UNAVAILABLE':
                case 'MS_UNAVAILABLE':
                    throw new QUI\ERP\Tax\Exception(
                        [
                            'quiqqer/tax',
                            'exception.vatid.validate.no.connection',
                            ['vatid' => $vatId]
                        ],
                        503
                    );
            }
        }
    }

    /**
     * Return the highest tax
     *
     * @return float
     */
    public static function getMaxTax(): float
    {
        try {
            $result = QUI::getDataBase()->fetch([
                'select' => 'vat',
                'from'   => QUI::getDBTableName('tax'),
                'limit'  => 1,
                'order'  => 'vat DESC'
            ]);
        } catch (QUI\DataBase\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return 0;
        }

        return isset($result[0]) ? (int)$result[0]['vat'] : 0;
    }

    /**
     * Return the least tax
     *
     * @return float
     */
    public static function getMinTax(): float
    {
        try {
            $result = QUI::getDataBase()->fetch([
                'select' => 'vat',
                'from'   => QUI::getDBTableName('tax'),
                'limit'  => 1,
                'order'  => 'vat ASC'
            ]);
        } catch (QUI\DataBase\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return 0;
        }

        return isset($result[0]) ? (int)$result[0]['vat'] : 0;
    }
}

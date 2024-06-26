<?php

/**
 * This file contains QUI\ERP\Tax\Import
 */

namespace QUI\ERP\Tax;

use DOMElement;
use DOMXPath;
use QUI;
use QUI\Utils\Text\XML;

use function explode;
use function is_string;

/**
 * Class Import
 * @package QUI\ERP\Tax
 */
class Import
{
    /**
     * @return array
     */
    public static function getAvailableImports(): array
    {
        $dir = OPT_DIR . 'quiqqer/tax/setup/';
        $xmlFiles = QUI\Utils\System\File::readDir($dir);
        $result = [];

        foreach ($xmlFiles as $xmlFile) {
            $Document = XML::getDomFromXml($dir . $xmlFile);
            $Path = new DOMXPath($Document);
            $title = $Path->query("//quiqqer/title");

            if ($title->item(0)) {
                $result[] = [
                    'file' => $xmlFile,
                    'locale' => QUI\Utils\DOM::getTextFromNode($title->item(0), false)
                ];
            }
        }

        return $result;
    }

    /**
     * Import tax from a preconfigure file
     *
     * @param string $fileName - file.xml
     * @throws QUI\Exception
     */
    public static function importPreconfigureAreas(string $fileName): void
    {
        if (self::existPreconfigure($fileName) === false) {
            throw new QUI\Exception(
                ['quiqqer/tax', 'exception.preconfigure.file.not.found'],
                404
            );
        }

        self::import(OPT_DIR . 'quiqqer/tax/setup/' . $fileName);
    }

    /**
     * Exists the preconfigure file?
     *
     * @param string $file
     * @return boolean
     */
    public static function existPreconfigure(string $file): bool
    {
        $availableImports = QUI\ERP\Tax\Import::getAvailableImports();

        foreach ($availableImports as $data) {
            if ($file == $data['file']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Import the standard areas
     *
     * @param string $xmlFile - XML File, path to the xml file
     * @throws QUI\Exception
     */
    public static function import(string $xmlFile): void
    {
        $Document = XML::getDomFromXml($xmlFile);
        $TaxHandler = new QUI\ERP\Tax\Handler();
        $Path = new DOMXPath($Document);
        $groups = $Path->query("//quiqqer/group");

        foreach ($groups as $Group) {
            /* @var $Group DOMElement */
            $types = $Group->getElementsByTagName('type');
            $TaxGroup = $TaxHandler->createTaxGroup();

            $taxGroupLocaleData = self::getLocaleDataFromNode($Group);

            self::updateLocale(
                'taxGroup.' . $TaxGroup->getId() . '.title',
                $taxGroupLocaleData
            );

            foreach ($types as $Type) {
                /* @var $Type DOMElement */
                $taxTypeLocaleData = self::getLocaleDataFromNode($Type);
                $taxList = $Type->getElementsByTagName('tax');
                $TaxType = $TaxHandler->createTaxType();

                self::updateLocale(
                    'taxType.' . $TaxType->getId() . '.title',
                    $taxTypeLocaleData
                );

                $TaxGroup->addTaxType($TaxType);
                $TaxGroup->update();

                $CurrentCountry = QUI\Countries\Manager::getDefaultCountry();

                // import taxes
                foreach ($taxList as $Tax) {
                    /* @var $Tax DOMElement */

                    // search area
                    $countries = $Tax->getAttribute('countries');

                    if (!$countries) {
                        continue;
                    }

                    $countries = explode(',', $countries);
                    $Area = self::getAreaByCountries($countries);

                    if (!$Area) {
                        continue;
                    }

                    try {
                        $euVat = 0;

                        if ($Tax->hasAttribute('euvat')) {
                            $euVat = $Tax->getAttribute('euvat');

                            if ($euVat === '{$currentCountry}') {
                                if ($Area->contains($CurrentCountry)) {
                                    $euVat = 0;
                                } else {
                                    $euVat = 1;
                                }
                            } else {
                                $euVat = (int)$euVat;
                            }
                        }

                        $TaxEntry = $TaxHandler->createChild([
                            'areaId' => $Area->getId(),
                            'taxTypeId' => $TaxType->getId(),
                            'taxGroupId' => $TaxGroup->getId(),
                            'vat' => $Tax->getAttribute('vat'),
                            'euvat' => $euVat,
                            'active' => 1
                        ]);

                        $TaxEntry->setAttribute('areaId', $Area->getId());
                        $TaxEntry->setAttribute('taxTypeId', $TaxType->getId());
                        $TaxEntry->setAttribute('taxGroupId', $TaxGroup->getId());
                        $TaxEntry->setAttribute('vat', $Tax->getAttribute('vat'));
                        $TaxEntry->setAttribute('euvat', (int)$Tax->getAttribute('euvat'));
                        $TaxEntry->update();
                    } catch (QUI\Exception $Exception) {
                        QUI\System\Log::addError($Exception->getMessage());

                        QUI::getMessagesHandler()->addError(
                            $Exception->getMessage()
                        );
                    }
                }
            }

            $TaxGroup->update();
        }

        // publish locale
        QUI\Translator::publish('quiqqer/tax');
    }

    /**
     * @param $var
     * @param $data
     * @throws QUI\Exception
     */
    protected static function updateLocale($var, $data): void
    {
        QUI\Translator::edit('quiqqer/tax', $var, '', $data);
    }

    /**
     * Return text params from <title>
     *
     * @param DOMElement $Parent
     * @return string|array
     */
    protected static function getTextNodeParamsFromNode(DOMElement $Parent): array|string
    {
        /* @var $Child DOMElement */
        foreach ($Parent->childNodes as $Child) {
            if ($Child->nodeName == 'title') {
                $Locale = $Child->getElementsByTagName('locale');

                if ($Locale->item(0)) {
                    /* @var $LocaleItem DOMElement */
                    $LocaleItem = $Locale->item(0);

                    return [
                        'group' => $LocaleItem->getAttribute('group'),
                        'var' => $LocaleItem->getAttribute('var')
                    ];
                }

                return $Child->nodeValue;
            }
        }

        return '';
    }

    /**
     * Return locale data from \DOMElement
     * Search <title> and return the locale translation data
     *
     * @param DOMElement $Parent
     * @return array
     */
    protected static function getLocaleDataFromNode(DOMElement $Parent): array
    {
        $result = [];
        $localeData = self::getTextNodeParamsFromNode($Parent);

        $availableLanguages = QUI\Translator::getAvailableLanguages();

        if (is_string($localeData)) {
            foreach ($availableLanguages as $lang) {
                $result[$lang] = $localeData;
            }

            return $result;
        }

        $data = QUI\Translator::getVarData(
            $localeData['group'],
            $localeData['var']
        );

        foreach ($availableLanguages as $lang) {
            if (!isset($data[$lang])) {
                $result[$lang] = '';
                continue;
            }

            $result[$lang] = $data[$lang];
        }

        return $result;
    }

    /**
     * Return the area by countries
     *
     * @param array $countries
     * @return boolean|QUI\ERP\Areas\Area
     *
     * @throws QUI\Exception
     */
    protected static function getAreaByCountries(array $countries): bool|QUI\ERP\Areas\Area
    {
        $AreaHandler = new QUI\ERP\Areas\Handler();
        $areas = $AreaHandler->getChildrenData();

        foreach ($areas as $area) {
            foreach ($countries as $country) {
                if ($country === '{$currentCountry}') {
                    $country = QUI\ERP\Defaults::getCountry()->getCode();
                }

                if (str_contains($area['countries'], $country)) {
                    /* @var $Area QUI\ERP\Areas\Area */
                    $Area = $AreaHandler->getChild((int)$area['id']);

                    return $Area;
                }
            }
        }

        return false;
    }
}

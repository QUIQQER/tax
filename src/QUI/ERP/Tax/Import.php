<?php

/**
 * This file contains QUI\ERP\Tax\Import
 */

namespace QUI\ERP\Tax;

use QUI;
use QUI\Utils\Text\XML;

/**
 * Class Import
 * @package QUI\ERP\Tax
 */
class Import
{
    /**
     * @return array
     */
    public static function getAvailableImports()
    {
        $dir      = OPT_DIR.'quiqqer/tax/setup/';
        $xmlFiles = QUI\Utils\System\File::readDir($dir);
        $result   = [];

        foreach ($xmlFiles as $xmlFile) {
            $Document = XML::getDomFromXml($dir.$xmlFile);
            $Path     = new \DOMXPath($Document);
            $title    = $Path->query("//quiqqer/title");

            if ($title->item(0)) {
                $result[] = [
                    'file'   => $xmlFile,
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
    public static function importPreconfigureAreas($fileName)
    {
        if (self::existPreconfigure($fileName) === false) {
            throw new QUI\Exception(
                ['quiqqer/tax', 'exception.preconfigure.file.not.found'],
                404
            );
        }

        self::import(OPT_DIR.'quiqqer/tax/setup/'.$fileName);
    }

    /**
     * Exists the preconfigure file?
     *
     * @param string $file
     * @return boolean
     */
    public static function existPreconfigure($file)
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
    public static function import($xmlFile)
    {
        $Document   = XML::getDomFromXml($xmlFile);
        $TaxHandler = new QUI\ERP\Tax\Handler();
        $Path       = new \DOMXPath($Document);
        $groups     = $Path->query("//quiqqer/group");

        foreach ($groups as $Group) {
            /* @var $Group \DOMElement */
            $types    = $Group->getElementsByTagName('type');
            $TaxGroup = $TaxHandler->createTaxGroup();

            $taxGroupLocaleData = self::getLocaleDataFromNode($Group);

            QUI\Translator::edit(
                'quiqqer/tax',
                'taxGroup.'.$TaxGroup->getId().'.title',
                'quiqqer/tax',
                $taxGroupLocaleData
            );

            foreach ($types as $Type) {
                /* @var $Type \DOMElement */
                $taxTypeLocaleData = self::getLocaleDataFromNode($Type);
                $taxList           = $Type->getElementsByTagName('tax');

                $TaxType = $TaxHandler->createTaxType();

                QUI\Translator::edit(
                    'quiqqer/tax',
                    'taxType.'.$TaxType->getId().'.title',
                    'quiqqer/tax',
                    $taxTypeLocaleData
                );

                $TaxGroup->addTaxType($TaxType);

                // import taxes
                foreach ($taxList as $Tax) {
                    /* @var $Tax \DOMElement */

                    // search area
                    $countries = $Tax->getAttribute('countries');

                    if (!$countries) {
                        continue;
                    }

                    $countries = explode(',', $countries);
                    $Area      = self::getAreaByCountries($countries);

                    if (!$Area) {
                        continue;
                    }

                    try {
                        $TaxEntry = $TaxHandler->createChild();

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
     * Return text params from <title>
     *
     * @param \DOMElement $Parent
     * @return string|array
     */
    protected static function getTextNodeParamsFromNode($Parent)
    {
        /* @var $Child \DOMElement */
        foreach ($Parent->childNodes as $Child) {
            if ($Child->nodeName == 'title') {
                $Locale = $Child->getElementsByTagName('locale');

                if ($Locale->item(0)) {
                    /* @var $LocaleItem \DOMElement */
                    $LocaleItem = $Locale->item(0);

                    return [
                        'group' => $LocaleItem->getAttribute('group'),
                        'var'   => $LocaleItem->getAttribute('var')
                    ];
                }

                return $Child->nodeValue;
                break;
            }
        }

        return '';
    }

    /**
     * Return locale data from \DOMElement
     * Search <title> and return the locale translation data
     *
     * @param \DOMElement $Parent
     * @return array
     */
    protected static function getLocaleDataFromNode($Parent)
    {
        $result     = [];
        $localeData = self::getTextNodeParamsFromNode($Parent);

        try {
            $availableLanguages = QUI\Translator::getAvailableLanguages();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return [];
        }

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
    protected static function getAreaByCountries($countries)
    {
        $AreaHandler = new QUI\ERP\Areas\Handler();
        $areas       = $AreaHandler->getChildrenData();

        foreach ($areas as $area) {
            foreach ($countries as $country) {
                if (strpos($area['countries'], $country) !== false) {
                    /* @var $Area QUI\ERP\Areas\Area */
                    $Area = $AreaHandler->getChild((int)$area['id']);

                    return $Area;
                }
            }
        }

        return false;
    }
}

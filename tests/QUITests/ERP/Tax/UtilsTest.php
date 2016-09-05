<?php

namespace QUITests\ERP\Products\Handler;

use QUI;

/**
 * Class UtilsTest
 */
class UtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMaxTax()
    {
        $max = QUI\ERP\Tax\Utils::getMaxTax();
        $this->assertGreaterThan(0, $max);
    }

    public function testGetMinTax()
    {
        $this->assertTrue(
            is_int(QUI\ERP\Tax\Utils::getMinTax())
        );
    }
}

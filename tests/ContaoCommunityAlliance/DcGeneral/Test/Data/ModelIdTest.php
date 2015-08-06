<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Data;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * This class tests the ModelId class.
 */
class ModelIdTest extends TestCase
{
    /**
     * Mock a model instance which will return the given values.
     *
     * @param mixed $modelId      The value to use as model id.
     *
     * @param mixed $dataProvider The value to use as data provider.
     *
     * @return ModelInterface
     */
    private function mockModel($modelId, $dataProvider)
    {
        $mock = $this
            ->getMockBuilder('ContaoCommunityAlliance\DcGeneral\Data\ModelInterface')
            ->setMethods(array('getId', 'getProviderName'))
            ->getMockForAbstractClass();
        $mock
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($modelId));
        $mock
            ->expects($this->any())
            ->method('getProviderName')
            ->will($this->returnValue($dataProvider));

        return $mock;
    }

    /**
     * Build a list of invalid models.
     *
     * @return array
     */
    public function modelProvider()
    {
        $exception = '\ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException';
        return array(
            array($this->mockModel(10, 'tl_page')),
            array($this->mockModel(null, 'tl_page'), $exception),
            array($this->mockModel(null, null), $exception),
            array($this->mockModel(10, null), $exception),
            array($this->mockModel(10, ''), $exception),
            array($this->mockModel(10, 0), $exception),
        );
    }

    /**
     * Test that the ModelId class can not be instantiated with invalid values.
     *
     * @param ModelInterface $model     The model to instantiate from.
     *
     * @param string|null    $exception The name of the expected exception class.
     *
     * @dataProvider modelProvider
     *
     * @return void
     */
    public function testInstantiationFromModel($model, $exception = null)
    {
        if (null !== $exception) {
            $this->setExpectedException($exception);
        }

        $modelId = ModelId::fromModel($model);

        $this->assertEquals($model->getId(), $modelId->getId());
        $this->assertEquals($model->getProviderName(), $modelId->getDataProviderName());
    }

    /**
     * Data provider for the valid id test.
     *
     * @return array
     */
    public function idProvider()
    {
        $exception = '\ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException';
        return array(
            array('tl_page::1'),
            array('tl_page:1', $exception),
            array(':1', $exception),
            array('1', $exception),
        );
    }

    /**
     * Test valid model ids.
     *
     * @param string      $testId    The id to test.
     *
     * @param string|null $exception The name of the expected exception class.
     *
     * @dataProvider idProvider
     *
     * @return void
     */
    public function testValidIds($testId, $exception = null)
    {
        if (null !== $exception) {
            $this->setExpectedException($exception);
        }

        $this->assertEquals($testId, ModelId::fromSerialized($testId)->getSerialized());
    }
}

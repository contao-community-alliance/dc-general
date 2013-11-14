<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\Palette\Builder\Event;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;
use DcGeneral\DataDefinition\Palette\PaletteCollectionInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Event\AbstractContainerAwareEvent;
use DcGeneral\Event\EnvironmentAwareEvent;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;

abstract class BuilderEvent extends AbstractContainerAwareEvent
{
	/**
	 * @var PaletteBuilder
	 */
	protected $paletteBuilder;

	function __construct(PaletteBuilder $paletteBuilder)
	{
		$this->paletteBuilder = $paletteBuilder;
	}

	/**
	 * @return PaletteBuilder
	 */
	public function getPaletteBuilder()
	{
		return $this->paletteBuilder;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEnvironment()
	{
		return $this->paletteBuilder->getContainer();
	}
}

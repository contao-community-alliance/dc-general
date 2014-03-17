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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\Event;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Builder\PaletteBuilder;

/**
 * This event gets emitted when a palette condition chain is created.
 *
 * @package DcGeneral\DataDefinition\Palette\Builder\Event
 */
class CreatePaletteConditionChainEvent extends BuilderEvent
{
	const NAME = 'dc-general.data-definition.palette.builder.create-plaette-condition-chain';

	/**
	 * The palette condition chain being created.
	 *
	 * @var PaletteConditionChain
	 */
	protected $paletteConditionChain;

	/**
	 * Create a new instance.
	 *
	 * @param PaletteConditionChain $paletteConditionChain The palette condition chain.
	 *
	 * @param PaletteBuilder        $paletteBuilder        The palette builder in use.
	 */
	public function __construct(PaletteConditionChain $paletteConditionChain, PaletteBuilder $paletteBuilder)
	{
		$this->setPaletteConditionChain($paletteConditionChain);
		parent::__construct($paletteBuilder);
	}

	/**
	 * Set the palette condition chain.
	 *
	 * @param PaletteConditionChain $paletteConditionChain The condition chain.
	 *
	 * @return CreatePaletteConditionChainEvent
	 */
	public function setPaletteConditionChain(PaletteConditionChain $paletteConditionChain)
	{
		$this->paletteConditionChain = $paletteConditionChain;

		return $this;
	}

	/**
	 * Retrieve the palette condition chain.
	 *
	 * @return PaletteConditionChain
	 */
	public function getPaletteConditionChain()
	{
		return $this->paletteConditionChain;
	}
}

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

namespace DcGeneral\DataDefinition;

use DcGeneral\DataDefinition\ConditionInterface;

/**
 * A condition define when a property is visible or editable and when not.
 */
interface ConditionChainInterface extends ConditionInterface
{
	/**
	 * All conditions must match.
	 */
	const AND_CONJUNCTION = 'AND';

	/**
	 * Only one condition must match.
	 */
	const OR_CONJUNCTION = 'OR';

	/**
	 * Clear the chain.
	 *
	 * @return ConditionChainInterface
	 */
	public function clearConditions();

	/**
	 * Set the conditions in this chain.
	 *
	 * @return ConditionChainInterface
	 */
	public function setConditions(array $conditions);

	/**
	 * Add multiple conditions to this chain.
	 *
	 * @param PaletteConditionInterface[] $conditions
	 *
	 * @return ConditionChainInterface
	 */
	public function addConditions(array $conditions);

	/**
	 * Add a condition to this chain.
	 *
	 * @param PaletteConditionInterface[] $conditions
	 *
	 * @return ConditionChainInterface
	 */
	public function addCondition(ConditionInterface $condition);

	/**
	 * Remove a condition from this chain.
	 *
	 * @param PaletteConditionInterface[] $conditions
	 *
	 * @return ConditionChainInterface
	 */
	public function removeCondition(ConditionInterface $condition);

	/**
	 * @return ConditionInterface[]
	 */
	public function getConditions();

	/**
	 * @param string $conjunction
	 *
	 * @return ConditionChainInterface
	 *
	 * @throws DcGeneralInvalidArgumentException
	 */
	public function setConjunction($conjunction);

	/**
	 * @return string
	 */
	public function getConjunction();
}

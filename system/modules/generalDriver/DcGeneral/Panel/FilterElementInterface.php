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

namespace DcGeneral\Panel;

interface FilterElementInterface extends PanelElementInterface
{
	/**
	 * @param string $strProperty The property to filter on.
	 *
	 * @return FilterElementInterface
	 */
	public function setPropertyName($strProperty);

	/**
	 * @return string
	 */
	public function getPropertyName();

	/**
	 * @param mixed $mixValue The value to filter for.
	 *
	 * @return FilterElementInterface
	 */
	public function setValue($mixValue);

	/**
	 * @return mixed
	 */
	public function getValue();
}

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

namespace DcGeneral\DataDefinition\Section\View;

interface OperationInterface
{
	/**
	 * Return the name of the property.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Return the label of the property.
	 *
	 * @return array
	 */
	public function getLabel();

	/**
	 * Return the (html) attributes to use.
	 *
	 * @return string
	 */
	public function getAttributes();

	/**
	 * Return the (html) href to use. This only applies to HTML views.
	 *
	 * @return string
	 */
	public function getHref();

	/**
	 * Return the icon to use.
	 *
	 * @return string
	 */
	public function getIcon();

	/**
	 * Return the callback to use.
	 *
	 * @return array
	 */
	public function getCallback();

	/**
	 * Fetch some arbitrary information.
	 *
	 * @param $strKey
	 *
	 * @return mixed
	 */
	public function get($strKey);

	/**
	 * This returns the whole content as Contao compatible operation array.
	 *
	 * @return array
	 *
	 * @deprecated You should rather use the interfaced methods than the operation as an array as this may not be supported.
	 */
	public function asArray();
}

<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Contao\Dca;

use DcGeneral\DataDefinition\Interfaces\Property as PropertyInterface;

class Property implements PropertyInterface
{
	/**
	 * The Container instance to which this property belongs to.
	 *
	 * @var Container
	 */
	protected $objParent;

	/**
	 * The name of this property.
	 *
	 * @var string
	 */
	protected $strProperty;

	public function __construct($objParent, $strProperty)
	{
		$this->objParent   = $objParent;
		$this->strProperty = $strProperty;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->strProperty;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLabel()
	{
		return $this->get('label');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getWidgetType()
	{
		return $this->get('inputType');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEvaluation()
	{
		return $this->get('eval');
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSearchable()
	{
		return $this->get('search');
	}

	/**
	 * {@inheritDoc}
	 */
	public function isFilterable()
	{
		return $this->get('filter');
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSortable()
	{
		return $this->get('sorting');
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($strKey)
	{
		return $this->objParent->getFromDca(sprintf('fields/%s/%s', $this->strProperty, $strKey));
	}
}

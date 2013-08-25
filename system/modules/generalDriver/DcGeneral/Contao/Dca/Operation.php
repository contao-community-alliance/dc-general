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

namespace DcGeneral\Contao\Dca;

use DcGeneral\DataDefinition\OperationInterface;

class Operation implements OperationInterface
{
	/**
	 * The Container instance to which this property belongs to.
	 *
	 * @var Container
	 */
	protected $objParent;

	/**
	 * The name of this operation.
	 *
	 * @var string
	 */
	protected $strOperation;

	public function __construct($objParent, $strOperation)
	{
		$this->objParent   = $objParent;
		$this->strOperation = $strOperation;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->strOperation;
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
	public function getAttributes()
	{
		return $this->get('attributes');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHref()
	{
		return $this->get('href');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIcon()
	{
		return $this->get('icon');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCallback()
	{
		return $this->get('button_callback');
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($strKey)
	{
		return $this->objParent->getFromDca(sprintf('list/operations/%s/%s', $this->strOperation, $strKey));
	}

	/**
	 * {@inheritDoc}
	 */
	public function asArray()
	{
		return $this->get('');
	}
}

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

namespace DcGeneral\Contao\Dca\Conditions;

use DcGeneral\DataDefinition\ParentChildConditionInterface;
use DcGeneral\DataDefinition\AbstractCondition;

/**
 * Class ParentChildCondition
 *
 * array(
'from'                => 'self',
'to'                  => 'self',
'setOn'               => array
(
array(
'to_field'    => 'pid',
'from_field'  => 'id',
),
array(
'to_field'    => 'fid',
'from_field'  => 'fid',
),
),
'filter'              => array
(
array
(
'local'       => 'pid',
'remote'      => 'id',
'operation'   => '=',
),
)
)
 *
 *
 *
 * @package DcGeneral\Contao\Dca\Conditions
 */
class ParentChildCondition
	extends AbstractCondition
	implements ParentChildConditionInterface
{
	/**
	 * The ContainerInterface instance to which this condition belongs to.
	 *
	 * @var \DcGeneral\Contao\Dca\Container
	 */
	protected $objParent;

	/**
	 * The key of this condition in the DCA.
	 *
	 * @var string
	 */
	protected $intKey;

	public function __construct($objParent, $intKey)
	{
		$this->objParent = $objParent;
		$this->intKey    = $intKey;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getFilter($objParent)
	{
		$arrFilter = $this->get('filter');
		$arrResult = array();
		foreach ($arrFilter as $arrRule)
		{
			$arrApplied = array(
				'operation'   => $arrRule['operation'],
			);

			if (isset($arrRule['local']))
			{
				$arrApplied['property'] = $arrRule['local'];
			}

			if (isset($arrRule['remote']))
			{
				$arrApplied['value'] = $objParent->getProperty($arrRule['remote']);
			}

			if (isset($arrRule['remote_value']))
			{
				$arrApplied['value'] = $arrRule['remote_value'];
			}

			if (isset($arrRule['value']))
			{
				$arrApplied['value'] = $arrRule['value'];
			}

			$arrResult[] = $arrApplied;
		}

		return $arrResult;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function applyTo($objParent, $objChild)
	{
		// FIXME: unimplemented.
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getInverseFilter($objChild)
	{
		$arrFilter = $this->get('inverse');
		$arrResult = array();
		foreach ($arrFilter as $arrRule)
		{
			$arrApplied = array(
				'operation'   => $arrRule['operation'],
			);

			if (isset($arrRule['remote']))
			{
				$arrApplied['property'] = $arrRule['remote'];
			}

			if (isset($arrRule['local']))
			{
				$arrApplied['value'] = $objChild->getProperty($arrRule['local']);
			}

			if (isset($arrRule['value']))
			{
				$arrApplied['value'] = $arrRule['value'];
			}

			$arrResult[] = $arrApplied;
		}

		return $arrResult;
	}

	/**
	 * {@inheritedDoc}
	 */
	public function matches($objParent, $objChild)
	{
		// FIXME: unimplemented.
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getSourceName()
	{
		return $this->get('from');
	}

	/**
	 * {@inheritedDoc}
	 */
	public function getDestinationName()
	{
		return $this->get('to');
	}

	/**
	 * Retrieve some arbitrary data for the condition.
	 *
	 * @param string $strKey The key to retrieve from the data container information.
	 *
	 * @return mixed
	 */
	public function get($strKey)
	{
		return $this->objParent->getFromDca(sprintf('dca_config/childCondition/%s/%s', $this->intKey, $strKey));
	}
}





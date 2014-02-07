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

use DcGeneral\Data\ConfigInterface;
use DcGeneral\View\ViewTemplateInterface;

/**
 * Default implementation of a limit panel element.
 *
 * @package DcGeneral\Panel
 */
class DefaultLimitElement
	extends AbstractElement
	implements LimitElementInterface
{
	/**
	 * The current offset.
	 *
	 * @var int
	 */
	protected $intOffset;

	/**
	 * The current amount.
	 *
	 * @var int
	 */
	protected $intAmount;

	/**
	 * The total amount of all valid entries.
	 *
	 * @var int
	 */
	protected $intTotal;

	/**
	 * Retrieve the persistent value from the input provider.
	 *
	 * @return array
	 */
	protected function getPersistent()
	{
		$arrValue = array();
		if ($this->getInputProvider()->hasPersistentValue('limit'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('limit');
		}

		if (array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $arrValue))
		{
			return $arrValue[$this->getEnvironment()->getDataDefinition()->getName()];
		}

		return array();
	}

	/**
	 * Store the persistent value in the input provider.
	 *
	 * @param int $intOffset The offset.
	 *
	 * @param int $intAmount The amount of items to show.
	 *
	 * @return void
	 */
	protected function setPersistent($intOffset, $intAmount)
	{
		$arrValue       = array();
		$definitionName = $this->getEnvironment()->getDataDefinition()->getName();

		if ($this->getInputProvider()->hasPersistentValue('limit'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('limit');
		}

		if ($intOffset)
		{
			if (!is_array($arrValue[$definitionName]))
			{
				$arrValue[$definitionName] = array();
			}

			$arrValue[$definitionName]['offset'] = $intOffset;
			$arrValue[$definitionName]['amount'] = $intAmount;
		}
		else
		{
			unset($arrValue[$definitionName]);
		}

		$this->getInputProvider()->setPersistentValue('limit', $arrValue);
	}

	/**
	 * @param mixed $idParent
	 *
	 * @param ConfigInterface $objConfig
	 *
	 * @return \DcGeneral\Data\ConfigInterface
	 */
	protected function addParentFilter($idParent, $objConfig)
	{

		$objCurrentDataProvider = $this
			->getPanel()
			->getContainer()
			->getDataContainer()
			->getDataProvider();

		$objParentDataProvider = $this
			->getPanel()
			->getContainer()
			->getDataContainer()
			->getDataProvider('parent');

		if ($objParentDataProvider)
		{
			$objParent = $objParentDataProvider->fetch($objParentDataProvider->getEmptyConfig()->setId($idParent));

			$objCondition = $this->getDataContainer()->getEnvironment()->getDataDefinition()->getChildCondition(
				$objParentDataProvider->getEmptyModel()->getProviderName(),
				$objCurrentDataProvider->getEmptyModel()->getProviderName()
			);

			if ($objCondition)
			{
				$arrBaseFilter = $objConfig->getFilter();
				$arrFilter     = $objCondition->getFilter($objParent);

				if ($arrBaseFilter)
				{
					$arrFilter = array_merge($arrBaseFilter, $arrFilter);
				}

				$objConfig->setFilter(
					array(
						array(
							'operation' => 'AND',
							'children'    => $arrFilter,
						)
					)
				);
			}
		}

		return $objConfig;
	}

	/**
	 * {@inheritDoc}
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
	{
		if (is_null($objElement))
		{
			$objTempConfig = $this->getOtherConfig($objConfig);
			$arrTotal      = $this
				->getEnvironment()
				->getDataProvider()
				->fetchAll($objTempConfig->setIdOnly(true));

			$this->intTotal = $arrTotal ? count($arrTotal) : 0;
			$offset         = 0;
			// TODO: we need to determine the perPage some better way.
			$amount = $GLOBALS['TL_CONFIG']['resultsPerPage'];

			$input = $this->getInputProvider();
			if ($this->getPanel()->getContainer()->updateValues() && $input->hasValue('tl_limit'))
			{
				$limit  = explode(',', $input->getValue('tl_limit'));
				$offset = $limit[0];
				$amount = $limit[1];

				$this->setPersistent($offset, $amount);
			}

			$persistent = $this->getPersistent();
			if ($persistent)
			{
				$offset = $persistent['offset'];
				$amount = $persistent['amount'];

				// Hotfix the offset - we also might want to store it persistent.
				// Another way would be to always stick on the "last" page when we hit the upper limit.
				if ($offset > $this->intTotal)
				{
					$offset = 0;
				}
			}

			if (!is_null($offset))
			{
				$this->setOffset($offset);
				$this->setAmount($amount);
			}
		}

		$objConfig->setStart($this->getOffset());
		$objConfig->setAmount($this->getAmount());
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(ViewTemplateInterface $objTemplate)
	{
		$arrOptions = array
		(
			array
			(
				'value'      => 'tl_limit',
				'attributes' => '',
				'content'    => $GLOBALS['TL_LANG']['MSC']['filterRecords']
			)
		);

		$optionsTotal = ceil(($this->intTotal / $GLOBALS['TL_CONFIG']['resultsPerPage']));

		for ($i = 0; $i < $optionsTotal; $i++)
		{
			$first      = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage']);
			$thisLimit  = $first . ',' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
			$upperLimit = ($first + $GLOBALS['TL_CONFIG']['resultsPerPage']);

			if ($upperLimit > $this->intTotal)
			{
				$upperLimit = $this->intTotal;
			}

			$arrOptions[] = array
			(
				'value'      => $thisLimit,
				'attributes' => ($this->getOffset() == $first) ? ' selected="selected"' : '',
				'content'    => ($first + 1) . ' - ' . $upperLimit
			);
		}

		if ($this->intTotal > $GLOBALS['TL_CONFIG']['resultsPerPage'])
		{
			$arrOptions[] = array
			(
				'value'      => 'all',
				'attributes' =>
						(($this->getOffset() == 0) && ($this->getAmount() == $this->intTotal))
						? ' selected="selected"'
						: '',
				'content'    => $GLOBALS['TL_LANG']['MSC']['filterAll']
			);
		}

		$objTemplate->options = $arrOptions;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOffset($intOffset)
	{
		$this->intOffset = $intOffset;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOffset()
	{
		return $this->intOffset;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAmount($intAmount)
	{
		$this->intAmount = $intAmount;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAmount()
	{
		return $this->intAmount;
	}
}

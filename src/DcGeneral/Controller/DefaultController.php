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

namespace DcGeneral\Controller;

use DcGeneral\Contao\BackendBindings;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\ConfigInterface;
use DcGeneral\Data\DataProviderInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\LanguageInformationInterface;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBagInterface;
use DcGeneral\DataContainerInterface;
use DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;

class DefaultController implements ControllerInterface
{
	/**
	 * The attached environment.
	 *
	 * @var EnvironmentInterface
	 */
	protected $environment;

	/**
	 * A list with all current IDs.
	 *
	 * @var array
	 */
	protected $arrInsertIDs = array();

	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $notImplMsg = "<div style='text-align:center; font-weight:bold; padding:40px;'>The function/view &quot;%s&quot; is not implemented.<br />Please <a target='_blank' style='text-decoration:underline' href='http://now.metamodel.me/en/sponsors/become-one#payment'>support us</a> to add this important feature!</div>";

	/**
	 * Field for the function sortCollection.
	 *
	 * @var string $arrColSort
	 */
	protected $arrColSort;

	/**
	 * Throw an exception that an unknown method has been called.
	 *
	 * @param string $name      Method name.
	 *
	 * @param array  $arguments Method arguments.
	 *
	 * @return void
	 *
	 * @throws DcGeneralRuntimeException Always.
	 */
	public function __call($name, $arguments)
	{
		throw new DcGeneralRuntimeException('Error Processing Request: ' . $name, 1);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setEnvironment(EnvironmentInterface $environment)
	{
		$this->environment = $environment;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * Search the parent of the passed model.
	 *
	 * @param ModelInterface      $model  The model to search the parent for.
	 *
	 * @param CollectionInterface $models The collection to search in.
	 *
	 * @return ModelInterface
	 */
	public function searchParentOfIn(ModelInterface $model, CollectionInterface $models)
	{
		$environment   = $this->getEnvironment();
		$definition    = $environment->getDataDefinition();
		$relationships = $definition->getModelRelationshipDefinition();

		foreach ($models as $candidate)
		{
			foreach ($relationships->getChildConditions($candidate) as $condition)
			{
				$provider = $environment->getDataProvider($condition->getDestinationName());
				$config   = $provider
					->getEmptyConfig()
					->setFilter($condition->getFilter($candidate))
					->setIdOnly(true);

				$result = $this->searchParentOfIn($model, $provider->fetchAll($config));
				if ($result === true)
				{
					return $candidate;
				}
				elseif ($result !== null)
				{
					return $result;
				}
			}
		}

		return null;
	}

	/**
	 * Search the parent model for the given model.
	 *
	 * @param ModelInterface $model The model for which the parent shall be retrieved.
	 *
	 * @return ModelInterface
	 *
	 * @throws DcGeneralInvalidArgumentException When a root model has been passed or not in hierarchical mode.
	 */
	public function searchParentOf(ModelInterface $model)
	{
		$environment   = $this->getEnvironment();
		$definition    = $environment->getDataDefinition();
		$relationships = $definition->getModelRelationshipDefinition();

		if ($definition->getBasicDefinition()->getMode() === BasicDefinitionInterface::MODE_HIERARCHICAL)
		{
			if ($this->isRootModel($model))
			{
				throw new DcGeneralInvalidArgumentException('Invalid condition, root models can not have parents!');
			}
			// TODO: Speed up, some conditions have an inverse filter - use them!

			$provider  = $environment->getDataProvider($definition->getBasicDefinition()->getRootDataProvider());
			$condition = $relationships->getRootCondition();
			$config    = $provider->getEmptyConfig()->setIdOnly(true)->setFilter($condition->getFilterArray());

			return $this->searchParentOfIn($model, $provider->fetchAll($config));
		}

		throw new DcGeneralInvalidArgumentException('Invalid condition, not in hierarchical mode!');
	}

	/**
	 * {@inheritDoc}
	 */
	public function assembleAllChildrenFrom($objModel, $strDataProvider = '')
	{
		if ($strDataProvider == '')
		{
			$strDataProvider = $objModel->getProviderName();
		}

		$arrIds = array();

		if ($strDataProvider == $objModel->getProviderName())
		{
			$arrIds = array($objModel->getId());
		}

		// Check all data providers for children of the given element.
		$conditions = $this
			->getEnvironment()
			->getDataDefinition()
			->getModelRelationshipDefinition()
			->getChildConditions($objModel->getProviderName());
		foreach ($conditions as $objChildCondition)
		{
			$objDataProv = $this->getEnvironment()->getDataProvider($objChildCondition->getDestinationName());
			$objConfig   = $objDataProv->getEmptyConfig();
			$objConfig->setFilter($objChildCondition->getFilter($objModel));

			foreach ($objDataProv->fetchAll($objConfig) as $objChild)
			{
				/** @var ModelInterface $objChild */
				if ($strDataProvider == $objChild->getProviderName())
				{
					$arrIds[] = $objChild->getId();
				}

				$arrIds = array_merge($arrIds, $this->assembleAllChildrenFrom($objChild, $strDataProvider));
			}
		}

		return $arrIds;
	}

	/**
	 * Retrieve all siblings of a given model.
	 *
	 * @param ModelInterface $model           The model for which the siblings shall be retrieved from.
	 *
	 * @param string|null    $sortingProperty The property name to use for sorting.
	 *
	 * @return CollectionInterface
	 *
	 * @todo This might return a lot of models, we definately want to use some lazy approach rather than this.
	 */
	protected function assembleSiblingsFor(ModelInterface $model, $sortingProperty = null)
	{
		$environment   = $this->getEnvironment();
		$definition    = $environment->getDataDefinition();
		$provider      = $environment->getDataProvider($model->getProviderName());
		$config        = $this->getBaseConfig();
		$relationships = $definition->getModelRelationshipDefinition();

		// Root model in hierarchical mode?
		if ($this->isRootModel($model))
		{
			$condition = $relationships->getRootCondition();

			if ($condition)
			{
				$config->setFilter($condition->getFilterArray());
			}
		}
		// Are we at least in hierarchical mode?
		elseif ($definition->getBasicDefinition()->getMode() === BasicDefinitionInterface::MODE_HIERARCHICAL)
		{
			$parent    = $this->searchParentOf($model);
			$condition = $relationships->getChildCondition($parent->getProviderName(), $model->getProviderName());
			$config->setFilter($condition->getFilter($parent));
		}

		if ($sortingProperty)
		{
			$config->setSorting(array((string)$sortingProperty => 'ASC'));
		}

		$siblings = $provider->fetchAll($config);

		return $siblings;
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateModelFromPropertyBag($model, $propertyValues)
	{
		$environment = $this->getEnvironment();
		if (!$propertyValues)
		{
			return $this;
		}

		// Callback to tell visitors that we have just updated the model.
		// $environment->getCallbackHandler()->onModelBeforeUpdateCallback($model);

		foreach ($propertyValues as $property => $value)
		{
			try
			{
				$model->setProperty($property, $value);
				$model->setMeta(DCGE::MODEL_IS_CHANGED, true);
			}
			catch (\Exception $exception)
			{
				$propertyValues->markPropertyValueAsInvalid($property, $exception->getMessage());
			}
		}

		$basicDefinition = $environment->getDataDefinition()->getBasicDefinition();

		if (($basicDefinition->getMode() & (
				BasicDefinitionInterface::MODE_PARENTEDLIST
				| BasicDefinitionInterface::MODE_HIERARCHICAL))
			// FIXME: dependency injection.
			&& (strlen(\Input::getInstance()->get('pid')) > 0)
		)
		{
			$providerName       = $basicDefinition->getDataProvider();
			$parentProviderName = $basicDefinition->getParentDataProvider();
			$objParentDriver    = $environment->getDataProvider($parentProviderName);
			$objParentModel     = $objParentDriver->fetch(
				$objParentDriver
					->getEmptyConfig()
					->setId(\Input::getInstance()->get('pid'))
			);

			$relationship = $environment
				->getDataDefinition()
				->getModelRelationshipDefinition()
				->getChildCondition($parentProviderName, $providerName);

			if ($relationship && $relationship->getSetters())
			{
				$relationship->applyTo($objParentModel, $model);
			}
		}

		// Callback to tell visitors that we have just updated the model.
		// $environment->getCallbackHandler()->onModelUpdateCallback($model);

		return $this;
	}

	/**
	 * Add the filter for the item with the given id from the parent data provider to the given config.
	 *
	 * @param mixed           $idParent The id of the parent item.
	 *
	 * @param ConfigInterface $config   The config to add the filter to.
	 *
	 * @return \DcGeneral\Data\ConfigInterface
	 *
	 * @throws DcGeneralRuntimeException When the parent item is not found.
	 */
	protected function addParentFilter($idParent, $config)
	{
		$environment        = $this->getEnvironment();
		$definition         = $environment->getDataDefinition();
		$providerName       = $definition->getBasicDefinition()->getDataProvider();
		$parentProviderName = $definition->getBasicDefinition()->getParentDataProvider();
		$parentProvider     = $environment->getDataProvider($parentProviderName);

		if ($parentProvider)
		{
			$objParent = $parentProvider->fetch($parentProvider->getEmptyConfig()->setId($idParent));
			if (!$objParent)
			{
				throw new DcGeneralRuntimeException('Parent item ' . $idParent . ' not found in ' . $parentProviderName);
			}

			$condition = $definition->getModelRelationshipDefinition()->getChildCondition($parentProviderName, $providerName);

			if ($condition)
			{
				$arrBaseFilter = $config->getFilter();
				$arrFilter     = $condition->getFilter($objParent);

				if ($arrBaseFilter)
				{
					$arrFilter = array_merge($arrBaseFilter, $arrFilter);
				}

				$config->setFilter(array(array(
					'operation' => 'AND',
					'children'    => $arrFilter,
				)));
			}
		}

		return $config;
	}

	/**
	 * Return all supported languages from the default data data provider.
	 *
	 * @param mixed $mixID The id of the item for which to retrieve the valid languages.
	 *
	 * @return array
	 */
	public function getSupportedLanguages($mixID)
	{
		$environment     = $this->getEnvironment();
		$objDataProvider = $environment->getDataProvider();

		// Check if current data provider supports multi language.
		if (in_array('DcGeneral\Data\MultiLanguageDataProviderInterface', class_implements($objDataProvider)))
		{
			/** @var \DcGeneral\Data\MultiLanguageDataProviderInterface $objDataProvider */
			$objLanguagesSupported = $objDataProvider->getLanguages($mixID);
		}
		else
		{
			$objLanguagesSupported = null;
		}

		// Check if we have some languages.
		if ($objLanguagesSupported == null)
		{
			return array();
		}

		// Make an array from the collection.
		$arrLanguage = array();
		$translator  = $environment->getTranslator();
		foreach ($objLanguagesSupported as $value)
		{
			/** @var LanguageInformationInterface $value */
			$arrLanguage[$value->getLocale()] = $translator->translate('LNG.' . $value->getLocale(), 'languages');
		}

		return $arrLanguage;
	}

	/**
	 * Cut and paste.
	 *
	 * <p>
	 * -= GET Parameter =-<br/>
	 * act		- Mode like cut | copy | and co <br/>
	 * <br/>
	 * after	- ID of target element to insert after <br/>
	 * into		- ID of parent element to insert into <br/>
	 * <br/>
	 * id		- Parent child ID used for redirect <br/>
	 * pid		- ID of the parent used in list mode 4,5 <br/>
	 * source	- ID of the element which should moved <br/>
	 * <br/>
	 * pdp		- Parent Data Provider real name <br/>
	 * cdp		- Current Data Provider real name <br/>
	 * <br/>
	 * -= Deprecated =-<br/>
	 * mode		- 1 Insert after | 2 Insert into (NEVER USED AGAIN - Deprecated) <br/>
	 * </p>
	 */
	public function cut()
	{
		$this->checkIsWritable();

		$mixAfter  = \Input::getInstance()->get('after');
		$mixInto   = \Input::getInstance()->get('into');
		$intId     = \Input::getInstance()->get('id');
		$mixPid    = \Input::getInstance()->get('pid');
		$mixSource = \Input::getInstance()->get('source');
		$strPDP    = \Input::getInstance()->get('pdp');
		$strCDP    = \Input::getInstance()->get('cdp');

		// Deprecated
		$intMode  = \Input::getInstance()->get('mode');
		$mixChild = \Input::getInstance()->get('child');

		// Check basic vars
		if (empty($mixSource) || ( is_null($mixAfter) && is_null($mixInto) ) || empty($strCDP))
		{
			BackendBindings::log('Missing parameter for cut in ' . $this->getDC()->getTable(), __CLASS__ . ' - ' . __FUNCTION__, TL_ERROR);
			BackendBindings::redirect('contao/main.php?act=error');
		}

		// Get current DataProvider
		if (!empty($strCDP))
		{
			$objCurrentDataProvider = $this->getEnvironment()->getDataProvider($strCDP);
		}
		else
		{
			$objCurrentDataProvider = $this->getEnvironment()->getDataProvider();
		}

		if ($objCurrentDataProvider == null)
		{
			throw new DcGeneralRuntimeException('Could not load current data provider in ' . __CLASS__ . ' - ' . __FUNCTION__);
		}

		// Get parent DataProvider, if set
		$objParentDataProvider = null;
		if (!empty($strPDP))
		{
			$objParentDataProvider = $this->getEnvironment()->getDataProvider($strPDP);

			if ($objCurrentDataProvider == null)
			{
				throw new DcGeneralRuntimeException('Could not load parent data provider ' . $strPDP . ' in ' . __CLASS__ . ' - ' . __FUNCTION__);
			}
		}

		// Load the source model
		$objSrcModel = $objCurrentDataProvider->fetch($objCurrentDataProvider->getEmptyConfig()->setId($mixSource));

		// Check mode
		switch ($this->getDC()->arrDCA['list']['sorting']['mode'])
		{
			case 0:
				$this->getNewPosition($objCurrentDataProvider, $objParentDataProvider, $objSrcModel, ($mixAfter == DCGE::INSERT_AFTER_START) ? 0 : $mixAfter, null, 'cut');
				break;
			case 1:
			case 2:
			case 3:
			case 4:
				$this->getNewPosition($objCurrentDataProvider, $objParentDataProvider, $objSrcModel, $mixAfter, $mixInto, 'cut');
				break;

			case 5:
				switch ($intMode)
				{
					case 1: // insert after
						// we want a new item in $strCDP having an optional parent in $strPDP (with pid item $mixPid) just after $mixAfter (in child tree conditions).
						// sadly, with our complex rules an getParent() is IMPOSSIBLE (or in other words way too costly as we would be forced to iterate through all items and check if this item would end up in their child collection).
						// therefore we get the child we want to be next of and set all fields to the same values as in the sibling to end up in the same parent.
						$objOtherChild = $objCurrentDataProvider->fetch($objCurrentDataProvider->getEmptyConfig()->setId($mixAfter));
						$this->getDC()->setSameParent($objSrcModel, $objOtherChild, $strCDP);

						// Update sorting.
						$this->getNewPosition($objCurrentDataProvider, $objParentDataProvider, $objSrcModel, $mixAfter, $mixInto, 'cut');
						break;

					case 2: // insert into
						// we want a new item in $strCDP having an optional parent in $strPDP (with pid item $mixPid) just as child of $mixAfter (in child tree conditions).
						// now check if we want to be inserted as root in our own condition - this means either no "after".
						if (($mixAfter == 0))
						{
							$this->setRoot($objSrcModel, 'self');
						}
						else
						{
							// enforce the child condition from our parent.
							$objMyParent = $objCurrentDataProvider->fetch($objCurrentDataProvider->getEmptyConfig()->setId($mixAfter));
							$this->setParent($objSrcModel, $objMyParent, 'self');
						}

						// Update sorting.
						$this->getNewPosition($objCurrentDataProvider, $objParentDataProvider, $objSrcModel, $mixAfter, $mixInto, 'cut');
						break;
					default:
						BackendBindings::log('Unknown create mode for copy in ' . $this->getDC()->getTable(), 'DC_General - DefaultController - copy()', TL_ERROR);
						BackendBindings::redirect('contao/main.php?act=error');
						break;
				}
				break;

			default:
				return vsprintf($this->notImplMsg, 'cut - Mode ' . $this->getDC()->arrDCA['list']['sorting']['mode']);
		}

		// Save new sorting
		$objCurrentDataProvider->save($objSrcModel);

		// Reset clipboard + redirect
		$this->getEnvironment()->getClipboard()->clear();
		$this->redirectHome();
	}

	/**
	 * Copy an entry and all children.
	 *
	 * @return string error msg for an unknown mode
	 */
	public function copy()
	{
		// Check
		$this->checkIsWritable();
		switch ($this->getDC()->arrDCA['list']['sorting']['mode'])
		{
			case 0:
			case 1:
			case 2:
			case 3:
				$intId = $this->Input->get('id');
				$intPid = (strlen($this->Input->get('pid')) != 0)? $this->Input->get('pid') : 0;

				if (strlen($intId) == 0)
				{
					BackendBindings::log('Missing parameter for copy in ' . $this->getDC()->getTable(), 'DC_General - DefaultController - copy()', TL_ERROR);
					BackendBindings::redirect('contao/main.php?act=error');
				}

				// Check
				$this->checkIsWritable();
				$this->checkLanguage($this->getDC());

				// Check if we have fields
				if (!$this->getEnvironment()->getDataDefinition()->hasEditableProperties())
				{
					BackendBindings::redirect($this->getReferer());
				}

				// Load something
				$this->getDC()->preloadTinyMce();

				// Load record from data provider - Load the source model
				$objDataProvider = $this->getEnvironment()->getDataProvider();
				$objSrcModel = $objDataProvider->fetch($objDataProvider->getEmptyConfig()->setId($intId));

				$objDBModel = clone $objSrcModel;
				$objDBModel->setMeta(DCGE::MODEL_IS_CHANGED, true);

				$this->getDC()->setCurrentModel($objDBModel);

				// Check if we have a auto submit
				$this->getDC()->updateModelFromPOST();

				// Check submit
				if ($this->getDC()->isSubmitted() == true)
				{
					if (isset($_POST["save"]))
					{
						// process input and update changed properties.
						if ($this->doSave() !== false)
						{
							$this->reload();
						}
					}
					else if (isset($_POST["saveNclose"]))
					{
						// process input and update changed properties.
						if ($this->doSave() !== false)
						{
							setcookie('BE_PAGE_OFFSET', 0, 0, '/');

							$_SESSION['TL_INFO'] = '';
							$_SESSION['TL_ERROR'] = '';
							$_SESSION['TL_CONFIRM'] = '';

							BackendBindings::redirect($this->getReferer());
						}
					}
					// Maybe Callbacks ? Yes, this is the first version of an simple
					// button callback system like dc_memory.
					else
					{
						$arrButtons = $this->getDC()->arrDCA['buttons'];

						if (is_array($arrButtons))
						{
							foreach ($arrButtons as $arrButton)
							{
								if (empty($arrButton) || !is_array($arrButton))
								{
									continue;
								}

								if (array_key_exists($arrButton['formkey'], $_POST))
								{
									$strClass = $arrButton['button_callback'][0];
									$strMethod = $arrButton['button_callback'][1];

									$this->import($strClass);

									$this->$strClass->$strMethod($this->getDC());

									break;
								}
							}
						}

						if (Input::getInstance()->post('SUBMIT_TYPE') !== 'auto')
						{
							$this->reload();
						}
					}
				}

				return;

			case 5:
				// Init Vars
				$intMode = $this->Input->get('mode');
				$intPid = $this->Input->get('pid');
				$intId = $this->Input->get('id');
				$intChilds = $this->Input->get('childs');

				if (strlen($intMode) == 0 || strlen($intPid) == 0 || strlen($intId) == 0)
				{
					BackendBindings::log('Missing parameter for copy in ' . $this->getDC()->getTable(), 'DC_General - DefaultController - copy()', TL_ERROR);
					BackendBindings::redirect('contao/main.php?act=error');
				}

				// Get the join field
				$arrJoinCondition = $this->getDC()->getJoinConditions('self');

				// Insert the copy
				$this->insertCopyModel($intId, $intPid, $intMode, $intChilds, $arrJoinCondition[0]['srcField'], $arrJoinCondition[0]['dstField'], $arrJoinCondition[0]['operation']);
				break;

			default:
				return vsprintf($this->notImplMsg, 'copy - Mode ' . $this->getDC()->arrDCA['list']['sorting']['mode']);
				break;
		}

		// Reset clipboard + redirect
		$this->resetClipboard(true);
	}

	/**
	 * Recurse through all children in mode 5 and return their Ids.
	 *
	 * @param ModelInterface $objParentModel The parent model to fetch children for.
	 *
	 * @param bool           $blnRecurse     Boolean flag determining if the collection shall recurse into sub levels.
	 *
	 * @return array
	 */
	protected function fetchMode5ChildrenOf($objParentModel, $blnRecurse = true)
	{
		$environment      = $this->getEnvironment();
		$definition       = $environment->getDataDefinition();
		$objChildCondition = $definition->getChildCondition($objParentModel->getProviderName(), $definition->getName());

		// Build filter
		$objChildConfig = $environment->getDataProvider()->getEmptyConfig();
		$objChildConfig->setFilter($objChildCondition->getFilter($objParentModel));

		// Get child collection
		$objChildCollection = $environment->getDataProvider()->fetchAll($objChildConfig);

		$arrIDs = array();
		foreach ($objChildCollection as $objChildModel)
		{
			/** @var ModelInterface $objChildModel */
			$arrIDs[] = $objChildModel->getId();
			if ($blnRecurse)
			{
				$arrIDs = array_merge($arrIDs, $this->fetchMode5ChildrenOf($objChildModel, true));
			}
		}
		return $arrIDs;
	}

	public function getBaseConfig()
	{
		$objConfig     = $this->getEnvironment()->getDataProvider()->getEmptyConfig();
		$objDefinition = $this->getEnvironment()->getDataDefinition();
		$arrAdditional = $objDefinition->getBasicDefinition()->getAdditionalFilter();

		// Custom filter common for all modes.
		if ($arrAdditional)
		{
			$objConfig->setFilter($arrAdditional);
		}

		// Special filter for certain modes.
		if ($objDefinition->getBasicDefinition()->getMode() == BasicDefinitionInterface::MODE_PARENTEDLIST)
		{
			$this->addParentFilter(
				$this->getEnvironment()->getInputProvider()->getParameter('pid'),
				$objConfig
			);
		}

		return $objConfig;
	}

	/**
	 * Loads the current model from the data provider and overrides the selector
	 *
	 * @param type $strSelector the name of the checkbox toggling the palette.
	 */
	public function generateAjaxPalette($strSelector)
	{
		// Check
		$this->checkIsWritable();
		$this->checkLanguage($this->getDC());

		// Check if we have fields
		if (!$this->getEnvironment()->getDataDefinition()->hasEditableProperties())
		{
			$this->redirect($this->getReferer());
		}

		// Load something
		$this->getDC()->preloadTinyMce();

		$objDataProvider = $this->getEnvironment()->getDataProvider();

		// Load record from data provider
		$objDBModel = $objDataProvider->fetch($objDataProvider->getEmptyConfig()->setId($this->getDC()->getId()));
		if ($objDBModel == null)
		{
			$objDBModel = $objDataProvider->getEmptyModel();
		}

		$this->getDC()->setCurrentModel($objDBModel);

		// override the setting from POST now.
		$objDBModel->setProperty($strSelector, intval($this->Input->post('state')));
	}

	/**
	 * Calculate the new position of an element
	 *
	 * Warning this function needs the cdp (current data provider).
	 *
	 * Warning this function needs the pdp (parent data provider).
	 *
	 * Based on backbone87 PR - "creating items in parent modes generates sorting value of 0"
	 *
	 * @param DataProviderInterface $objCDP             Current data provider
	 * @param DataProviderInterface $objPDP             Parent data provider
	 * @param ModelInterface  $objDBModel         Model of element which should moved
	 * @param mixed           $mixAfter           Target element
	 * @param                 $mixInto
	 * @param string          $strMode            Mode like cut | create and so on
	 * @param mixed           $mixParentID        Parent ID of table or element
	 * @param integer         $intInsertMode      Insert Mode => 1 After | 2 Into
	 * @param bool            $blnWithoutReorder
	 *
	 * @throws \RuntimeException
	 *
	 * @return void
	 */
	protected function getNewPosition($objCDP, $objPDP, $objDBModel, $mixAfter, $mixInto, $strMode, $mixParentID = null, $intInsertMode = null, $blnWithoutReorder = false)
	{
		if (!$objDBModel)
		{
			throw new DcGeneralRuntimeException('No model provided!');
		}

		$environment = $this->getEnvironment();
		$definition  = $environment->getDataDefinition();

		// Check if we have a sorting field, if not skip here.
		if (!$objCDP->fieldExists('sorting'))
		{
			return;
		}

		// Load default DataProvider.
		if (is_null($objCDP))
		{
			$objCDP = $this->getEnvironment()->getDataProvider();
		}

		if ($mixAfter === DCGE::INSERT_AFTER_START)
		{
			$mixAfter = 0;
		}

		// Search for the highest sorting. Default - Add to end off all.
		// ToDo: We have to check the child <=> parent condition . To get all sortings for one level.
		// If we get a after 0, add to top.
		if ($mixAfter === 0) {

			// Build filter for conditions
			$arrFilter = array();

			if (in_array($this->getDC()->arrDCA['list']['sorting']['mode'], array(4, 5, 6)))
			{
				$arrConditions = $definition->getRootCondition()->getFilter();

				if ($arrConditions)
				{
					foreach ($arrConditions as $arrCondition)
					{
						if (array_key_exists('remote', $arrCondition))
						{
							$arrFilter[] = array(
								'value'		 => Input::getInstance()->get($arrCondition['remote']),
								'property'	 => $arrCondition['property'],
								'operation'	 => $arrCondition['operation']
							);
						}
						else if (array_key_exists('remote_value', $arrCondition))
						{
							$arrFilter[] = array(
								'value'		 => Input::getInstance()->get($arrCondition['remote_value']),
								'property'	 => $arrCondition['property'],
								'operation'	 => $arrCondition['operation']
							);
						}
						else
						{
							$arrFilter[] = array(
								'value'		 => $arrCondition['value'],
								'property'	 => $arrCondition['property'],
								'operation'	 => $arrCondition['operation']
							);
						}
					}
				}
			}

			// Build config
			$objConfig = $objCDP->getEmptyConfig();
			$objConfig->setFields(array('sorting'));
			$objConfig->setSorting(array('sorting' => DCGE::MODEL_SORTING_ASC));
			$objConfig->setAmount(1);
			$objConfig->setFilter($arrFilter);

			$objCollection = $objCDP->fetchAll($objConfig);

			if ($objCollection->length())
			{
				$intLowestSorting    = $objCollection->get(0)->getProperty('sorting');
				$intNextSorting      = round($intLowestSorting / 2);
			}
			else
			{
				$intNextSorting = 256;
			}

			// FIXME: lowest sorting is uninitialized here - stefan heimes, what to do?
			// Check if we have a valide sorting.
			if (($intLowestSorting < 2 || $intNextSorting <= 2) && !$blnWithoutReorder)
			{
				// ToDo: Add child <=> parent config.
				$objConfig = $objCDP->getEmptyConfig();
				$objConfig->setFilter($arrFilter);

				$this->reorderSorting($objConfig);
				$this->getNewPosition($objCDP, $objPDP, $objDBModel, $mixAfter, $mixInto, $strMode, $mixParentID, $intInsertMode, true);
				return;
			}
			// Fallback to valid sorting.
			else if ($intNextSorting <= 2)
			{
				$intNextSorting = 256;
			}

			$objDBModel->setProperty('sorting', $intNextSorting);
		}
		// If we get a after, search for the right value.
		else if (!empty($mixAfter))
		{
			// Init some vars.
			$intAfterSorting = 0;
			$intNextSorting = 0;

			// Get "after" sorting value value.
			$objAfterConfig = $objCDP->getEmptyConfig();
			$objAfterConfig->setAmount(1);
			$objAfterConfig->setFilter(array(array(
				'value'      => $mixAfter,
				'property'   => 'id',
				'operation'  => '='
			)));

			$objAfterCollection = $objCDP->fetchAll($objAfterConfig);

			if ($objAfterCollection->length())
			{
				$intAfterSorting = $objAfterCollection->get(0)->getProperty('sorting');
			}

			// Get "next" sorting value value.
			$objNextConfig = $objCDP->getEmptyConfig();
			$objNextConfig->setFields(array('sorting'));
			$objNextConfig->setAmount(1);
			$objNextConfig->setSorting(array('sorting' => DCGE::MODEL_SORTING_ASC));

			$arrFilterSettings = array(array(
				'value'      => $intAfterSorting,
				'property'	 => 'sorting',
				'operation'	 => '>'
			));


			$arrFilterChildCondition = array();

			// If we have mode 4, 5, 6 build the child <=> parent condition.
			if (in_array($this->getDC()->arrDCA['list']['sorting']['mode'], array(4, 5, 6)))
			{
				$arrChildCondition	 = $this->objDC->getParentChildCondition($objAfterCollection->get(0), $objCDP->getEmptyModel()->getProviderName());
				$arrChildCondition	 = $arrChildCondition['setOn'];

				if ($arrChildCondition)
				{
					foreach ($arrChildCondition as $arrOperation)
					{
						if (array_key_exists('to_field', $arrOperation))
						{
							$arrFilterChildCondition[] = array(
								'value'		 => $objAfterCollection->get(0)->getProperty($arrOperation['to_field']),
								'property'	 => $arrOperation['to_field'],
								'operation'	 => '='
							);
						}
						else
						{
							$arrFilterChildCondition[] = array(
								'value'		 => $arrOperation['property'],
								'property'	 => $arrOperation['to_field'],
								'operation'	 => '='
							);
						}
					}
				}
			}

			$objNextConfig->setFilter(array_merge($arrFilterSettings, $arrFilterChildCondition));

			$objNextCollection = $objCDP->fetchAll($objNextConfig);

			if ($objNextCollection->length())
			{
				$intNextSorting = $objNextCollection->get(0)->getProperty('sorting');
			}
			else
			{
				$intNextSorting = $intAfterSorting + (2 * 256);
			}

			// Check if we have a valide sorting.
			if (($intAfterSorting < 2 || $intNextSorting < 2 || round(($intNextSorting - $intAfterSorting) / 2) <= 2) && !$blnWithoutReorder)
			{
				// ToDo: Add child <=> parent config.
				$objConfig = $objCDP->getEmptyConfig();
				$objConfig->setFilter($arrFilterChildCondition);

				$this->reorderSorting($objConfig);
				$this->getNewPosition($objCDP, $objPDP, $objDBModel, $mixAfter, $mixInto, $strMode, $mixParentID, $intInsertMode, true);
				return;
			}
			// Fallback to valid sorting.
			else if ($intNextSorting <= 2)
			{
				$intNextSorting = 256;
			}

			// Get sorting between these two values.
			$intNewSorting = $intAfterSorting + round(($intNextSorting - $intAfterSorting) / 2);

			// Save in model.
			$objDBModel->setProperty('sorting', $intNewSorting);

		}
		// Else use the highest value. Fallback.
		else
		{
			$objConfig = $objCDP->getEmptyConfig();
			$objConfig->setFields(array('sorting'));
			$objConfig->setSorting(array('sorting' => DCGE::MODEL_SORTING_DESC));
			$objConfig->setAmount(1);

			$objCollection = $objCDP->fetchAll($objConfig);

			$intHighestSorting = 0;

			if ($objCollection->length())
			{
				$intHighestSorting = $objCollection->get(0)->getProperty('sorting') + 256;
			}

			$objDBModel->setProperty('sorting', $intHighestSorting);
		}
	}

	/**
	 * Reorder all sortings for one table.
	 *
	 * @param ConfigInterface $objConfig
	 *
	 * @return void
	 */
	protected function reorderSorting($objConfig)
	{
		$objCurrentDataProvider = $this->getEnvironment()->getDataProvider();

		if ($objConfig == null)
		{
			$objConfig = $objCurrentDataProvider->getEmptyConfig();
		}

		// Search for the lowest sorting
		$objConfig->setFields(array('sorting'));
		$objConfig->setSorting(array('sorting' => DCGE::MODEL_SORTING_ASC, 'id' => DCGE::MODEL_SORTING_ASC));
		$arrCollection = $objCurrentDataProvider->fetchAll($objConfig);

		$i = 1;
		$intCount = 256;

		foreach ($arrCollection as $value)
		{
			$value->setProperty('sorting', $intCount * $i++);
			$objCurrentDataProvider->save($value);
		}
	}

	/* /////////////////////////////////////////////////////////////////////
	 * ---------------------------------------------------------------------
	 * Copy modes
	 * ---------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////// */

	/**
	 * @todo Make it fine
	 *
	 * @param type $intSrcID
	 * @param type $intDstID
	 * @param type $intMode
	 * @param type $blnChilds
	 * @param type $strDstField
	 * @param type $strSrcField
	 * @param type $strOperation
	 */
	protected function insertCopyModel($intIdSource, $intIdTarget, $intMode, $blnChilds, $strFieldId, $strFieldPid, $strOperation)
	{
		// Get dataprovider
		$objDataProvider = $this->getEnvironment()->getDataProvider();

		// Load the source model
		$objSrcModel = $objDataProvider->fetch($objDataProvider->getEmptyConfig()->setId($intIdSource));

		// Create a empty model for the copy
		$objCopyModel = clone $objSrcModel;

//		// Load all params
//		$arrProperties = $objSrcModel->getPropertiesAsArray();
//
//		// Clear some fields, see dca
//		foreach ($arrProperties as $key => $value)
//		{
//			// If the field is not known, remove it
//			if (!key_exists($key, $this->getDC()->arrDCA['fields']))
//			{
//				continue;
//			}
//
//			// Check doNotCopy
//			if ($this->getDC()->arrDCA['fields'][$key]['eval']['doNotCopy'] == true)
//			{
//				unset($arrProperties[$key]);
//				continue;
//			}
//
//			// Check fallback
//			if ($this->getDC()->arrDCA['fields'][$key]['eval']['fallback'] == true)
//			{
//				$objDataProvider->resetFallback($key);
//			}
//
//			// Check unique
//			if ($this->getDC()->arrDCA['fields'][$key]['eval']['unique'] == true && $objDataProvider->isUniqueValue($key, $value))
//			{
//				throw new DcGeneralRuntimeException(vsprintf($GLOBALS['TL_LANG']['ERR']['unique'], $key));
//			}
//		}
//
//		// Add the properties to the empty model
//		$objCopyModel->setPropertiesAsArray($arrProperties);

		$intListMode = $this->getDC()->arrDCA['list']['sorting']['mode'];

		//Insert After => Get the parent from he target id
		if (in_array($intListMode, array(0, 1, 2, 3)))
		{
			// ToDo: reset sorting for new entry
		}
		//Insert After => Get the parent from he target id
		else if (in_array($intListMode, array(5)) && $intMode == 1)
		{
			$this->setParent($objCopyModel, $this->getParent('self', null, $intIdTarget), 'self');
		}
		// Insert Into => use the pid
		else if (in_array($intListMode, array(5)) && $intMode == 2)
		{
			if ($this->isRootEntry('self', $intIdTarget))
			{
				$this->setRoot($objCopyModel, 'self');
			}
			else
			{
				$objParentConfig = $this->getEnvironment()->getDataProvider()->getEmptyConfig();
				$objParentConfig->setId($intIdTarget);

				$objParentModel = $this->getEnvironment()->getDataProvider()->fetch($objParentConfig);

				$this->setParent($objCopyModel, $objParentModel, 'self');
			}
		}
		else
		{
			BackendBindings::log('Unknown create mode for copy in ' . $this->getDC()->getTable(), 'DC_General - DefaultController - copy()', TL_ERROR);
			BackendBindings::redirect('contao/main.php?act=error');
		}

		$objDataProvider->save($objCopyModel);

		$this->arrInsertIDs[$objCopyModel->getID()] = true;

		if ($blnChilds == true)
		{
			$strFilter = $strFieldPid . $strOperation . $objSrcModel->getProperty($strFieldId);
			$objChildConfig = $objDataProvider->getEmptyConfig()->setFilter(array($strFilter));
			$objChildCollection = $objDataProvider->fetchAll($objChildConfig);

			foreach ($objChildCollection as $key => $value)
			{
				if (array_key_exists($value->getID(), $this->arrInsertIDs))
				{
					continue;
				}

				$this->insertCopyModel($value->getID(), $objCopyModel->getID(), 2, $blnChilds, $strFieldId, $strFieldPid, $strOperation);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function isRootModel(ModelInterface $model)
	{
		if ($this
			->getEnvironment()
			->getDataDefinition()
		->getBasicDefinition()
		->getMode() !== BasicDefinitionInterface::MODE_HIERARCHICAL)
		{
			return false;
		}

		return $this
			->getEnvironment()
			->getDataDefinition()
			->getModelRelationshipDefinition()
			->getRootCondition()
			->matches($model);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setRootModel(ModelInterface $model)
	{
		$rootCondition = $this
			->getEnvironment()
			->getDataDefinition()
			->getModelRelationshipDefinition()
			->getRootCondition();

		$rootCondition->applyTo($model);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \DcGeneral\Exception\DcGeneralRuntimeException
	 */
	public function setParent(ModelInterface  $childModel, ModelInterface  $parentModel)
	{
		$this
			->getEnvironment()
			->getDataDefinition($childModel->getProviderName())
			->getModelRelationshipDefinition()
			->getChildCondition($parentModel->getProviderName(), $childModel->getProviderName())
			->applyTo($parentModel, $childModel);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSameParent(ModelInterface $receivingModel, ModelInterface $sourceModel, $parentTable)
	{
		if ($this->isRootModel($sourceModel))
		{
			$this->setRootModel($receivingModel);
		}
		else
		{
			$this
				->getEnvironment()
				->getDataDefinition()
				->getModelRelationshipDefinition()
				->getChildCondition($parentTable, $receivingModel->getProviderName())
				->copyFrom($sourceModel, $receivingModel);
		}
	}

	public function sortCollection(ModelInterface $a, ModelInterface $b)
	{
		if ($a->getProperty($this->arrColSort['field']) == $b->getProperty($this->arrColSort['field']))
		{
			return 0;
		}

		if (!$this->arrColSort['reverse'])
		{
			return ($a->getProperty($this->arrColSort['field']) < $b->getProperty($this->arrColSort['field'])) ? -1 : 1;
		}
		else
		{
			return ($a->getProperty($this->arrColSort['field']) < $b->getProperty($this->arrColSort['field'])) ? 1 : -1;
		}
	}

	public function executePostActions()
	{
		if (version_compare(VERSION, '3.0', '>='))
		{
			$objHandler = new Ajax3X();
		}
		else
		{
			$objHandler = new Ajax2X();
		}
		$objHandler->executePostActions($this->getDC());
	}

}

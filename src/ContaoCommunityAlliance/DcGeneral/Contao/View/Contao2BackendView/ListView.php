<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class ListView.
 *
 * The implementation of a plain listing view.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView
 */
class ListView extends BaseView
{
    /**
     * Load the collection of child items and the parent item for the currently selected parent item.
     *
     * @return CollectionInterface
     */
    public function loadCollection()
    {
        $environment = $this->getEnvironment();
        $backendView = $this->getViewSection();

        /** @var Contao2BackendViewDefinitionInterface $backendView */
        $listingConfig = $backendView->getListingConfig();
        $dataProvider  = $environment->getDataProvider();
        $dataConfig    = $environment->getController()->getBaseConfig();

        $this->getPanel()->initialize($dataConfig);

        // Initialize sorting if not present yet.
        if (!$dataConfig->getSorting() && $listingConfig->getGroupAndSortingDefinition()->hasDefault()) {
            $newSorting = array();
            foreach ($listingConfig->getGroupAndSortingDefinition()->getDefault() as $information) {
                /** @var GroupAndSortingInformationInterface $information */
                $newSorting[$information->getProperty()] = strtoupper($information->getSortingMode());
            }
            $dataConfig->setSorting($newSorting);
        }

        $objCollection = $dataProvider->fetchAll($dataConfig);

        return $objCollection;
    }

    /**
     * Return the table heading.
     *
     * @return array
     */
    protected function getTableHead()
    {
        $arrTableHead      = array();
        $definition        = $this->getEnvironment()->getDataDefinition();
        $properties        = $definition->getPropertiesDefinition();
        $viewDefinition    = $this->getViewSection();
        $listingDefinition = $viewDefinition->getListingConfig();
        $sorting           = $this->getGroupingMode();
        $sortingDefinition = $sorting['sorting'];
        $sortingColumns    = array();

        if ($sortingDefinition) {
            /** @var GroupAndSortingDefinitionInterface $sortingDefinition */
            foreach ($sortingDefinition as $information) {
                /** @var GroupAndSortingInformationInterface $information */
                if ($information->getProperty()) {
                    $sortingColumns[] = $information->getProperty();
                }
            }
        }

        // Generate the table header if the "show columns" option is active.
        if ($listingDefinition->getShowColumns()) {
            foreach ($properties->getPropertyNames() as $f) {
                $property = $properties->getProperty($f);
                if ($property) {
                    $label = $property->getLabel();
                } else {
                    $label = $f;
                }

                $arrTableHead[] = array(
                    'class'   => 'tl_folder_tlist col_' . $f . ((in_array($f, $sortingColumns)) ? ' ordered_by' : ''),
                    'content' => $label[0]
                );
            }

            $arrTableHead[] = array(
                'class'   => 'tl_folder_tlist tl_right_nowrap',
                'content' => '&nbsp;'
            );
        }

        return $arrTableHead;
    }

    /**
     * Set label for list view.
     *
     * @param CollectionInterface $collection          The collection containing the models.
     *
     * @param array               $groupingInformation The grouping information as retrieved via
     *                                                 BaseView::getGroupingMode().
     *
     * @return void
     */
    protected function setListViewLabel($collection, $groupingInformation)
    {
        $viewDefinition = $this->getViewSection();
        $listingConfig  = $viewDefinition->getListingConfig();
        $remoteCur      = null;
        $groupClass     = 'tl_folder_tlist';
        $eoCount        = -1;

        foreach ($collection as $objModelRow) {
            /** @var ModelInterface $objModelRow */

            // Build the sorting groups.
            if ($groupingInformation) {
                $remoteNew = $this->formatCurrentValue(
                    $groupingInformation['property'],
                    $objModelRow,
                    $groupingInformation['mode'],
                    $groupingInformation['length']
                );

                // Add the group header if it differs from the last header.
                if (!$listingConfig->getShowColumns()
                    && ($groupingInformation['mode'] !== GroupAndSortingInformationInterface::GROUP_NONE)
                    && (($remoteNew != $remoteCur) || ($remoteCur === null))
                ) {
                    $eoCount = -1;

                    $objModelRow->setMeta(
                        $objModelRow::GROUP_VALUE,
                        array(
                            'class' => $groupClass,
                            'value' => $remoteNew
                        )
                    );

                    $groupClass = 'tl_folder_list';
                    $remoteCur  = $remoteNew;
                }
            }

            $objModelRow->setMeta($objModelRow::CSS_ROW_CLASS, (((++$eoCount) % 2 == 0) ? 'even' : 'odd'));

            $objModelRow->setMeta($objModelRow::LABEL_VALUE, $this->formatModel($objModelRow));
        }
    }

    /**
     * Generate list view from current collection.
     *
     * @param CollectionInterface $collection The collection containing the models.
     *
     * @return string
     */
    protected function viewList($collection)
    {
        $environment = $this->getEnvironment();
        $definition  = $environment->getDataDefinition();

        $groupingInformation = $this->getGroupingMode();

        // Set label.
        $this->setListViewLabel($collection, $groupingInformation);

        // Generate buttons.
        foreach ($collection as $i => $objModel) {
            // Regular buttons - only if not in select mode!
            if (!$this->isSelectModeActive()) {
                $previous = (($collection->get($i - 1) !== null) ? $collection->get($i - 1) : null);
                $next     = (($collection->get($i + 1) !== null) ? $collection->get($i + 1) : null);
                /** @var ModelInterface $objModel */
                $objModel->setMeta(
                    $objModel::OPERATION_BUTTONS,
                    $this->generateButtons($objModel, $previous, $next)
                );
            }
        }

        // Add template.
        if (isset($groupingInformation['mode'])
            && ($groupingInformation['mode'] != GroupAndSortingInformationInterface::GROUP_NONE)
        ) {
            $objTemplate = $this->getTemplate('dcbe_general_grouping');
        } elseif (isset($groupingInformation['property']) && ($groupingInformation['property'] != '')) {
            $objTemplate = $this->getTemplate('dcbe_general_listView_sorting');
        } else {
            $objTemplate = $this->getTemplate('dcbe_general_listView');
        }

        $this
            ->addToTemplate('tableName', strlen($definition->getName()) ? $definition->getName() : 'none', $objTemplate)
            ->addToTemplate('collection', $collection, $objTemplate)
            ->addToTemplate('select', $this->getEnvironment()->getInputProvider()->getParameter('act'), $objTemplate)
            ->addToTemplate('action', ampersand(\Environment::getInstance()->request, true), $objTemplate)
            ->addToTemplate('mode', ($groupingInformation ? $groupingInformation['mode'] : null), $objTemplate)
            ->addToTemplate('tableHead', $this->getTableHead(), $objTemplate)
            // Set dataprovider from current and parent.
            ->addToTemplate('pdp', '', $objTemplate)
            ->addToTemplate('cdp', $definition->getName(), $objTemplate)
            ->addToTemplate('selectButtons', $this->getSelectButtons(), $objTemplate)
            ->addToTemplate('sortable', (bool)$this->getManualSortingProperty(), $objTemplate)
            ->addToTemplate('showColumns', $this->getViewSection()->getListingConfig()->getShowColumns(), $objTemplate);

        // Add breadcrumb, if we have one.
        $strBreadcrumb = $this->breadcrumb();
        if ($strBreadcrumb != null) {
            $this->addToTemplate('breadcrumb', $strBreadcrumb, $objTemplate);
        }

        return $objTemplate->parse();
    }

    /**
     * Copy mode - this redirects to edit.
     *
     * @throws DcGeneralRuntimeException If no model id has been given.
     *
     * @return string
     */
    public function copy()
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit();
        }

        $this->checkLanguage();

        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider();
        $modelId      = IdSerializer::fromSerialized($environment->getInputProvider()->getParameter('source'));

        if ($modelId) {
            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
        } else {
            throw new DcGeneralRuntimeException('Missing model id.');
        }

        // We need to keep the original data here.
        $copyModel = $environment->getController()->createClonedModel($model);

        $preFunction = function ($environment, $model) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PreDuplicateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        $postFunction = function ($environment, $model, $originalModel) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PostDuplicateModelEvent($environment, $model, $originalModel);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        return $this->createEditMask($copyModel, $model, $preFunction, $postFunction);
    }

    /**
     * Show all entries from one table.
     *
     * @return string
     */
    public function showAll()
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit();
        }

        $this->checkClipboard();
        $collection = $this->loadCollection();

        $arrReturn            = array();
        $arrReturn['panel']   = $this->panel();
        $arrReturn['buttons'] = $this->generateHeaderButtons('tl_buttons_a');
        $arrReturn['body']    = $this->viewList($collection);

        // Return all.
        return implode("\n", $arrReturn);
    }
}

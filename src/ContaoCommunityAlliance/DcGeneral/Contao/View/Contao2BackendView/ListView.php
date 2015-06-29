<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
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
        $dataConfig    = $environment->getBaseConfigRegistry()->getBaseConfig();

        ViewHelpers::initializeSorting($this->getPanel(), $dataConfig, $listingConfig);

        return $dataProvider->fetchAll($dataConfig);
    }

    /**
     * Render paste top button. Returns null if no button should be rendered.
     *
     * @param string $sorting The sorting mode.
     *
     * @return string
     */
    protected function renderPasteTopButton($sorting)
    {
        $definition      = $this->getEnvironment()->getDataDefinition();
        $dispatcher      = $this->getEnvironment()->getEventDispatcher();
        $basicDefinition = $definition->getBasicDefinition();
        $clipboard       = $this->getEnvironment()->getClipboard();

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());

        if ($sorting && $clipboard->isNotEmpty($filter)) {

            $allowPasteTop = ViewHelpers::getManualSortingProperty($this->environment);

            if ($allowPasteTop) {
                /** @var AddToUrlEvent $urlEvent */
                $urlEvent = $dispatcher->dispatch(
                    ContaoEvents::BACKEND_ADD_TO_URL,
                    new AddToUrlEvent(
                        'act=paste&after=' . IdSerializer::fromValues($definition->getName(), 0)->getSerialized()
                    )
                );

                /** @var GenerateHtmlEvent $imageEvent */
                $imageEvent = $dispatcher->dispatch(
                    ContaoEvents::IMAGE_GET_HTML,
                    new GenerateHtmlEvent(
                        'pasteafter.gif',
                        $this->translate('pasteafter.0', $definition->getName()),
                        'class="blink"'
                    )
                );

                return sprintf(
                    '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
                    $urlEvent->getUrl(),
                    specialchars($this->translate('pasteafter.0', $definition->getName())),
                    $imageEvent->getHtml()
                );
            }
        }

        return null;
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
        $sorting           = ViewHelpers::getGroupingMode($this->environment);
        $sortingDefinition = $sorting['sorting'];
        $sortingColumns    = array();
        $pasteTopButton    = $this->renderPasteTopButton($sorting);

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
                    'content' => $label
                );
            }

            $arrTableHead[] = array(
                'class'   => 'tl_folder_tlist tl_right_nowrap',
                'content' => $pasteTopButton ?: '&nbsp;'
            );
        } elseif ($pasteTopButton) {
            $arrTableHead[] = array(
                'class' => 'tl_folder_tlist',
                'content' => '&nbsp'
            );
            $arrTableHead[] = array(
                'class'   => 'tl_folder_tlist tl_right_nowrap',
                'content' => $pasteTopButton ?: '&nbsp;'
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
        $clipboard      = $this->environment->getClipboard();
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

            $cssClasses = array((((++$eoCount) % 2 == 0) ? 'even' : 'odd'));
            $modelId    = IdSerializer::fromModel($objModelRow);
            if ($clipboard->hasId($modelId)) {
                $cssClasses[] = 'tl_folder_clipped';
            }

            $objModelRow->setMeta($objModelRow::CSS_ROW_CLASS, implode(' ', $cssClasses));

            $event = new FormatModelLabelEvent($this->environment, $objModelRow);
            $this->environment->getEventDispatcher()->dispatch(
                DcGeneralEvents::FORMAT_MODEL_LABEL,
                $event
            );

            $objModelRow->setMeta($objModelRow::LABEL_VALUE, $event->getLabel());
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

        $groupingInformation = ViewHelpers::getGroupingMode($this->environment);

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
            ->addToTemplate('action', ampersand(\Environment::get('request'), true), $objTemplate)
            ->addToTemplate('mode', ($groupingInformation ? $groupingInformation['mode'] : null), $objTemplate)
            ->addToTemplate('tableHead', $this->getTableHead(), $objTemplate)
            // Set dataprovider from current and parent.
            ->addToTemplate('pdp', '', $objTemplate)
            ->addToTemplate('cdp', $definition->getName(), $objTemplate)
            ->addToTemplate('selectButtons', $this->getSelectButtons(), $objTemplate)
            ->addToTemplate('sortable', (bool) ViewHelpers::getManualSortingProperty($this->environment), $objTemplate)
            ->addToTemplate('showColumns', $this->getViewSection()->getListingConfig()->getShowColumns(), $objTemplate);

        // Add breadcrumb, if we have one.
        $strBreadcrumb = $this->breadcrumb();
        if ($strBreadcrumb != null) {
            $this->addToTemplate('breadcrumb', $strBreadcrumb, $objTemplate);
        }

        return $objTemplate->parse();
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException If no model id has been given.
     *
     * @return string
     */
    public function copy(Action $action)
    {
        // FIXME this will never be used anymore!

        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

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
     * {@inheritdoc}
     */
    public function showAll(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        $collection = $this->loadCollection();

        $viewEvent = new ViewEvent($this->environment, $action, DcGeneralViews::CLIPBOARD, array());
        $this->environment->getEventDispatcher()->dispatch(DcGeneralEvents::VIEW, $viewEvent);

        $arrReturn              = array();
        $arrReturn['panel']     = $this->panel();
        $arrReturn['buttons']   = $this->generateHeaderButtons('tl_buttons_a');
        $arrReturn['clipboard'] = $viewEvent->getResponse();
        $arrReturn['body']      = $this->viewList($collection);

        // Return all.
        return implode("\n", $arrReturn);
    }
}

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

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;

/**
 * Class ParentView.
 *
 * Implementation of the parent view.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView
 */
class ParentView extends BaseView
{
    /**
     * Load the collection of child items and the parent item for the currently selected parent item.
     *
     * Consumes input parameter "id".
     *
     * @return CollectionInterface
     */
    public function loadCollection()
    {
        $environment   = $this->getEnvironment();
        $dataProvider  = $environment->getDataProvider();
        $childConfig   = $environment->getBaseConfigRegistry()->getBaseConfig();
        $listingConfig = $this->getViewSection()->getListingConfig();

        $this->getPanel()->initialize($childConfig);

        // Initialize sorting if not present yet.
        if (!$childConfig->getSorting() && $listingConfig->getGroupAndSortingDefinition()->hasDefault()) {
            $newSorting = array();
            foreach ($listingConfig->getGroupAndSortingDefinition()->getDefault() as $information) {
                /** @var GroupAndSortingInformationInterface $information */
                $newSorting[$information->getProperty()] = strtoupper($information->getSortingMode());
            }
            $childConfig->setSorting($newSorting);
        }

        $objChildCollection = $dataProvider->fetchAll($childConfig);

        return $objChildCollection;
    }

    /**
     * Load the parent model for the current list.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralRuntimeException If the parent view requirements are not fulfilled - either no data provider
     *                                   defined or no parent model id given.
     */
    protected function loadParentModel()
    {
        $environment = $this->getEnvironment();
        $pid         = $environment->getInputProvider()->getParameter('pid');
        $pidDetails  = IdSerializer::fromSerialized($pid);

        if (!$pidDetails->isValid()) {
            throw new DcGeneralRuntimeException(
                'ParentView needs a proper parent id defined, somehow none is defined?',
                1
            );
        }

        if (!($objParentProvider = $environment->getDataProvider($pidDetails->getDataProviderName()))) {
            throw new DcGeneralRuntimeException(
                'ParentView needs a proper parent data provider defined, somehow none is defined?',
                1
            );
        }

        $objParentItem = $objParentProvider->fetch($objParentProvider->getEmptyConfig()->setId($pidDetails->getId()));

        if (!$objParentItem) {
            // No parent item found, might have been deleted.
            // We transparently create it for our filter to be able to filter to nothing.
            // TODO: shall we rather bail with "parent not found"?
            $objParentItem = $objParentProvider->getEmptyModel();
            $objParentItem->setID($pidDetails->getId());
        }

        return $objParentItem;
    }

    /**
     * Render the entries for parent view.
     *
     * @param CollectionInterface $collection          The collection to render.
     *
     * @param array               $groupingInformation The grouping information as retrieved via
     *                                                 BaseView::getGroupingMode().
     *
     * @return void
     */
    protected function renderEntries($collection, $groupingInformation)
    {
        $environment = $this->getEnvironment();
        $clipboard   = $environment->getClipboard();
        $view        = $this->getViewSection();
        $listing     = $view->getListingConfig();
        $remoteCur   = null;
        $groupClass  = 'tl_folder_tlist';
        $eoCount     = -1;

        $objConfig = $environment->getDataProvider()->getEmptyConfig();
        $this->getPanel()->initialize($objConfig);

        // Run each model.
        $index = 0;
        foreach ($collection as $model) {
            /** @var ModelInterface $model */
            $index++;

            // Add the group header.
            if ($groupingInformation) {
                $remoteNew = $this->formatCurrentValue(
                    $groupingInformation['property'],
                    $model,
                    $groupingInformation['mode'],
                    $groupingInformation['length']
                );

                // Add the group header if it differs from the last header.
                if (!$listing->getShowColumns()
                    && ($groupingInformation['mode'] !== GroupAndSortingInformationInterface::GROUP_NONE)
                    && (($remoteNew != $remoteCur) || ($remoteCur === null))
                ) {
                    $eoCount = -1;

                    $model->setMeta(
                        $model::GROUP_VALUE,
                        array(
                            'class' => $groupClass,
                            'value' => $remoteNew
                        )
                    );

                    $groupClass = 'tl_folder_list';
                    $remoteCur  = $remoteNew;
                }
            }

            if ($listing->getItemCssClass()) {
                $model->setMeta($model::CSS_CLASS, $listing->getItemCssClass());
            }

            // Regular buttons.
            if (!$this->isSelectModeActive()) {
                $previous = (($collection->get($index - 1) !== null) ? $collection->get($index - 1) : null);
                $next     = (($collection->get($index + 1) !== null) ? $collection->get($index + 1) : null);

                $buttons = $this->generateButtons($model, $previous, $next);

                $model->setMeta($model::OPERATION_BUTTONS, $buttons);
            }

            $event = new ParentViewChildRecordEvent($this->getEnvironment(), $model);

            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s][%s]', $event::NAME, $environment->getDataDefinition()->getName(), $model->getId()),
                $event
            );
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()),
                $event
            );
            $environment->getEventDispatcher()->dispatch($event::NAME, $event);

            $cssClasses = array((((++$eoCount) % 2 == 0) ? 'even' : 'odd'));
            $modelId    = IdSerializer::fromModel($model);
            if ($clipboard->hasId($modelId)) {
                $cssClasses[] = 'tl_folder_clipped';
            }

            $model->setMeta($model::CSS_ROW_CLASS, implode(' ', $cssClasses));

            if ($event->getHtml() !== null) {
                $information = array(
                    array(
                        'colspan' => 1,
                        'class'   => 'tl_file_list col_1',
                        'content' => $event->getHtml()
                    )
                );
                $model->setMeta($model::LABEL_VALUE, $information);
            } else {
                $event = new FormatModelLabelEvent($this->environment, $model);
                $this->environment->getEventDispatcher()->dispatch(
                    DcGeneralEvents::FORMAT_MODEL_LABEL,
                    $event
                );

                $model->setMeta($model::LABEL_VALUE, $event->getLabel());
            }
        }
    }

    /**
     * Render the header of the parent view with information from the parent table.
     *
     * @param ModelInterface $parentModel The parent model.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function renderFormattedHeaderFields($parentModel)
    {
        $environment       = $this->getEnvironment();
        $dispatcher        = $this->getEnvironment()->getEventDispatcher();
        $definition        = $environment->getDataDefinition();
        $viewDefinition    = $this->getViewSection();
        $listingDefinition = $viewDefinition->getListingConfig();
        $headerFields      = $listingDefinition->getHeaderPropertyNames();
        $parentDefinition  = $environment->getParentDataDefinition();
        $parentName        = $definition->getBasicDefinition()->getParentDataProvider();
        $add               = array();

        foreach ($headerFields as $v) {
            $value = deserialize($parentModel->getProperty($v));

            if ($v == 'tstamp') {
                $value = date($GLOBALS['TL_CONFIG']['datimFormat'], $value);
            }

            $property = $parentDefinition->getPropertiesDefinition()->getProperty($v);

            // FIXME: foreignKey is not implemented yet.
            if ($property && (($v != 'tstamp')/* || $property->get('foreignKey')*/)) {
                $evaluation = $property->getExtra();
                $reference  = isset($evaluation['reference']) ? $evaluation['reference'] : null;
                $options    = $property->getOptions();

                if (is_array($value)) {
                    $value = implode(', ', $value);
                } elseif ($property->getWidgetType() == 'checkbox' && !$evaluation['multiple']) {
                    $value = strlen($value) ? $this->translate('yes', 'MSC') : $this->translate('no', 'MSC');
                } elseif ($value && in_array($evaluation['rgxp'], array('date', 'time', 'datim'))) {
                    $event = new ParseDateEvent($value, $GLOBALS['TL_CONFIG'][$evaluation['rgxp'] . 'Format']);
                    $dispatcher->dispatch(ContaoEvents::DATE_PARSE, $event);

                    $value = $event->getResult();
                } elseif (is_array($reference[$value])) {
                    $value = $reference[$value][0];
                } elseif (isset($reference[$value])) {
                    $value = $reference[$value];
                } elseif ($evaluation['isAssociative'] || array_is_assoc($options)) {
                    $value = $options[$value];
                }
            }

            // Add the sorting field.
            if ($value != '') {
                $lang = $this->translate(sprintf('%s.0', $v), $parentName);
                $key  = $lang ? $lang : $v;

                $add[$key] = $value;
            }
        }

        $event = new GetParentHeaderEvent($environment);
        $event->setAdditional($add);

        $dispatcher->dispatch(
            sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()),
            $event
        );
        $dispatcher->dispatch($event::NAME, $event);

        if (!$event->getAdditional() !== null) {
            $add = $event->getAdditional();
        }

        // Set header data.
        $arrHeader = array();
        foreach ($add as $k => $v) {
            if (is_array($v)) {
                $v = $v[0];
            }

            $arrHeader[$k] = $v;
        }

        return $arrHeader;
    }

    /**
     * Retrieve a list of html buttons to use in the top panel (submit area).
     *
     * @param ModelInterface $parentModel The parent model.
     *
     * @return array
     */
    protected function getHeaderButtons($parentModel)
    {
        $environment     = $this->getEnvironment();
        $definition      = $environment->getDataDefinition();
        $clipboard       = $environment->getClipboard();
        $basicDefinition = $definition->getBasicDefinition();

        $headerButtons = array();
        if ($this->isSelectModeActive()) {
            $headerButtons['selectAll'] = sprintf(
                '<label for="tl_select_trigger" class="tl_select_label">%s</label>
                <input type="checkbox"
                    id="tl_select_trigger"
                    onclick="Backend.toggleCheckboxes(this)"
                    class="tl_tree_checkbox" />',
                $this->translate('selectAll', 'MSC')
            );
        } else {
            $dispatcher = $environment->getEventDispatcher();

            $objConfig = $this->getEnvironment()->getBaseConfigRegistry()->getBaseConfig();
            $this->getPanel()->initialize($objConfig);
            $sorting = $objConfig->getSorting();

            $headerButtons['editHeader'] = $this->getHeaderEditButtons($parentModel);

            $filter = new Filter();
            $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
            if ($parentDataProviderName = $basicDefinition->getParentDataProvider()) {
                $filter->andParentIsFromProvider($parentDataProviderName);
            } else {
                $filter->andHasNoParent();
            }

            if ($sorting
                && $clipboard->isEmpty($filter)
                && $basicDefinition->isCreatable()
            ) {
                /** @var AddToUrlEvent $urlEvent */
                $urlEvent = $dispatcher->dispatch(
                    ContaoEvents::BACKEND_ADD_TO_URL,
                    new AddToUrlEvent(
                        'act=edit&amp;pid=' . IdSerializer::fromModel($parentModel)->getSerialized()
                    )
                );

                /** @var GenerateHtmlEvent $imageEvent */
                $imageEvent = $dispatcher->dispatch(
                    ContaoEvents::IMAGE_GET_HTML,
                    new GenerateHtmlEvent(
                        'new.gif',
                        $this->translate('pastenew.0', $definition->getName())
                    )
                );

                $headerButtons['pasteNew'] = sprintf(
                    '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
                    $urlEvent->getUrl(),
                    specialchars($this->translate('pastenew.1', $definition->getName())),
                    $imageEvent->getHtml()
                );
            }

            $filter = new Filter();
            $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
            $filter->andParentIsFromProvider($basicDefinition->getParentDataProvider());

            if ($sorting && $clipboard->isNotEmpty($filter)) {

                $allowPasteTop = ViewHelpers::getManualSortingProperty($this->environment);

                if (!$allowPasteTop) {
                    $subFilter = new Filter();
                    $subFilter->andActionIsNotIn(array(ItemInterface::COPY, ItemInterface::DEEP_COPY));
                    $subFilter->andParentIsNot(IdSerializer::fromModel($parentModel));
                    $subFilter->orActionIsIn(array(ItemInterface::COPY, ItemInterface::DEEP_COPY));

                    $filter = new Filter();
                    $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
                    $filter->andParentIsFromProvider($basicDefinition->getParentDataProvider());
                    $filter->andSub($subFilter);

                    $allowPasteTop = (bool) $clipboard->fetch($filter);
                }

                if ($allowPasteTop) {
                    /** @var AddToUrlEvent $urlEvent */
                    $urlEvent = $dispatcher->dispatch(
                        ContaoEvents::BACKEND_ADD_TO_URL,
                        new AddToUrlEvent(
                            'act=paste' .
                            '&amp;pid=' . IdSerializer::fromModel($parentModel)->getSerialized()
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

                    $headerButtons['pasteAfter'] = sprintf(
                        '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
                        $urlEvent->getUrl(),
                        specialchars($this->translate('pasteafter.0', $definition->getName())),
                        $imageEvent->getHtml()
                    );
                } else {
                    /** @var GenerateHtmlEvent $imageEvent */
                    $imageEvent = $dispatcher->dispatch(
                        ContaoEvents::IMAGE_GET_HTML,
                        new GenerateHtmlEvent(
                            'pasteafter_.gif',
                            $this->translate('pasteafter.0', $definition->getName()),
                            'class="blink"'
                        )
                    );

                    $headerButtons['pasteAfter'] = $imageEvent->getHtml();
                }
            }
        }

        return implode(' ', $headerButtons);
    }

    /**
     * Retrieve a list of html buttons to use in the top panel (submit area).
     *
     * @param ModelInterface $parentModel The parent model.
     *
     * @return array
     */
    protected function getHeaderEditButtons($parentModel)
    {
        $environment      = $this->getEnvironment();
        $parentDefinition = $environment->getParentDataDefinition();

        if ($parentDefinition && $parentDefinition->getBasicDefinition()->isEditable()) {
            $definition      = $environment->getDataDefinition();
            $basicDefinition = $definition->getBasicDefinition();
            $parentName      = $basicDefinition->getParentDataProvider();
            $dispatcher      = $environment->getEventDispatcher();

            $query = array(
                'do'    => $environment->getInputProvider()->getParameter('do'),
                'act'   => 'edit',
                'table' => $parentName,
                'id'    => IdSerializer::fromModel($parentModel)->getSerialized(),
            );

            $factory = DcGeneralFactory::deriveFromEnvironment($this->environment);
            $factory->setContainerName($parentDefinition->getName());

            $parentContainer = $factory->createContainer();
            if ($parentContainer->getBasicDefinition()->getParentDataProvider()) {
                $container = $this->environment->getDataDefinition();

                $relationship = $container->getModelRelationshipDefinition()->getChildCondition(
                    $parentContainer->getBasicDefinition()->getParentDataProvider(),
                    $parentContainer->getName()
                );

                if ($relationship) {
                    $filter = $relationship->getInverseFilterFor($parentModel);

                    $grandParentProvider =
                        $this->environment->getDataProvider(
                            $parentContainer->getBasicDefinition()->getParentDataProvider()
                        );

                    $config = $grandParentProvider->getEmptyConfig();
                    $config->setFilter($filter);

                    $parents = $grandParentProvider->fetchAll($config);

                    if ($parents->length() == 1) {
                        $query['pid'] = IdSerializer::fromModel($parents->get(0))->getSerialized();
                    } elseif ($parents->length() > 1) {
                        return null;
                    }
                }
            }

            /** @var GenerateHtmlEvent $imageEvent */
            $imageEvent = $dispatcher->dispatch(
                ContaoEvents::IMAGE_GET_HTML,
                new GenerateHtmlEvent(
                    'edit.gif',
                    $this->translate('editheader.0', $definition->getName())
                )
            );

            return sprintf(
                '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
                'contao/main.php?' . str_replace('%3A', ':', http_build_query($query)),
                specialchars($this->translate('editheader.1', $definition->getName())),
                $imageEvent->getHtml()
            );
        }

        return null;
    }

    /**
     * Show parent view mode 4.
     *
     * @param CollectionInterface $collection  The collection containing the models.
     *
     * @param ModelInterface      $parentModel The parent model.
     *
     * @return string HTML output
     */
    protected function viewParent($collection, $parentModel)
    {
        $definition          = $this->getEnvironment()->getDataDefinition();
        $parentProvider      = $definition->getBasicDefinition()->getParentDataProvider();
        $groupingInformation = ViewHelpers::getGroupingMode($this->environment);
        $dispatcher          = $this->getEnvironment()->getEventDispatcher();

        // Skip if we have no parent or parent collection.
        if (!$parentModel) {
            $dispatcher->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent(
                    sprintf(
                        'The view for %s has either a empty parent data provider or collection.',
                        $parentProvider
                    ),
                    __CLASS__ . '::' . __FUNCTION__ . '()',
                    TL_ERROR
                )
            );

            $dispatcher->dispatch(
                ContaoEvents::CONTROLLER_REDIRECT,
                new RedirectEvent('contao/main.php?act=error')
            );
        }

        // Add template.
        if ($groupingInformation['mode'] != GroupAndSortingInformationInterface::GROUP_NONE) {
            $objTemplate = $this->getTemplate('dcbe_general_grouping');
        } else {
            $objTemplate = $this->getTemplate('dcbe_general_parentView');
        }

        $this
            ->addToTemplate('tableName', strlen($definition->getName()) ? $definition->getName() : 'none', $objTemplate)
            ->addToTemplate('collection', $collection, $objTemplate)
            ->addToTemplate('select', $this->isSelectModeActive(), $objTemplate)
            ->addToTemplate('action', ampersand(\Environment::get('request'), true), $objTemplate)
            ->addToTemplate('header', $this->renderFormattedHeaderFields($parentModel), $objTemplate)
            ->addToTemplate('mode', ($groupingInformation ? $groupingInformation['mode'] : null), $objTemplate)
            ->addToTemplate('pdp', (string) $parentProvider, $objTemplate)
            ->addToTemplate('cdp', $definition->getName(), $objTemplate)
            ->addToTemplate('selectButtons', $this->getSelectButtons(), $objTemplate)
            ->addToTemplate('headerButtons', $this->getHeaderButtons($parentModel), $objTemplate)
            ->addToTemplate('sortable', (bool) ViewHelpers::getManualSortingProperty($this->environment), $objTemplate)
            ->addToTemplate('showColumns', $this->getViewSection()->getListingConfig()->getShowColumns(), $objTemplate);

        $this->renderEntries($collection, $groupingInformation);

        // Add breadcrumb, if we have one.
        $strBreadcrumb = $this->breadcrumb();
        if ($strBreadcrumb != null) {
            $this->addToTemplate('breadcrumb', $strBreadcrumb, $objTemplate);
        }

        return $objTemplate->parse();
    }

    /**
     * {@inheritDoc}
     */
    public function enforceModelRelationship($model)
    {
        $definition = $this->getEnvironment()->getDataDefinition();
        $basic      = $definition->getBasicDefinition();
        $parent     = $this->loadParentModel();

        $condition = $definition
            ->getModelRelationshipDefinition()
            ->getChildCondition(
                $basic->getParentDataProvider(),
                $basic->getDataProvider()
            );

        if ($condition) {
            $condition->applyTo($parent, $model);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function showAll(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        $collection  = $this->loadCollection();
        $parentModel = $this->loadParentModel();

        $viewEvent = new ViewEvent($this->environment, $action, DcGeneralViews::CLIPBOARD, array());
        $this->environment->getEventDispatcher()->dispatch(DcGeneralEvents::VIEW, $viewEvent);

        $arrReturn              = array();
        $arrReturn['panel']     = $this->panel();
        $arrReturn['buttons']   = $this->generateHeaderButtons('tl_buttons_a');
        $arrReturn['clipboard'] = $viewEvent->getResponse();
        $arrReturn['body']      = $this->viewParent($collection, $parentModel);

        return implode("\n", $arrReturn);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When no source id is provided by the input provider.
     */
    public function copy(Action $action)
    {
        // FIXME this will never be used anymore!

        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        $manualSortingProperty = ViewHelpers::getManualSortingProperty($this->environment);
        if ($manualSortingProperty
            && $this->environment->getDataProvider()->fieldExists($manualSortingProperty)
        ) {
            ViewHelpers::redirectHome($this->environment);
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
        $copyModel->setId(null);

        $preFunction = function (EnvironmentInterface $environment, $model) {
            $copyEvent = new PreDuplicateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        $postFunction = function (EnvironmentInterface $environment, $model, $originalModel) {
            $copyEvent = new PostDuplicateModelEvent($environment, $model, $originalModel);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        return $this->createEditMask($copyModel, $model, $preFunction, $postFunction);
    }
}

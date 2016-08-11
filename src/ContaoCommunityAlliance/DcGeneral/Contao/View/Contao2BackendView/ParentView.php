<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
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
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\ParentEnforcingListener;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;

/**
 * Class ParentView.
 *
 * Implementation of the parent view.
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
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider();
        $childConfig  = $environment->getBaseConfigRegistry()->getBaseConfig();

        $listingConfig = $this->getViewSection()->getListingConfig();
        $panel         = $this->getPanel();

        ViewHelpers::initializeSorting($panel, $childConfig, $listingConfig);

        return $dataProvider->fetchAll($childConfig);
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
        $pidDetails  = ModelId::fromSerialized($pid);

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

        // Generate buttons - only if not in select mode!
        if (!$this->isSelectModeActive()) {
            $buttonRenderer = new ButtonRenderer($this->environment);
            $buttonRenderer->renderButtonsForCollection($collection);
        }

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

            $event = new ParentViewChildRecordEvent($this->getEnvironment(), $model);

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

            if ($property && ($v != 'tstamp')) {
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
                if ($v === 'tstamp') {
                    $lang = $this->translate('tstamp', 'MSC');
                } else {
                    $lang = $this->translate(sprintf('%s.0', $v), $parentName);
                }

                $key = $lang ? $lang : $v;

                $add[$key] = $value;
            }
        }

        $event = new GetParentHeaderEvent($environment, $parentModel);
        $event->setAdditional($add);

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
     * @return string
     */
    protected function getHeaderButtons($parentModel)
    {
        $environment     = $this->getEnvironment();
        $definition      = $environment->getDataDefinition();
        $clipboard       = $environment->getClipboard();
        $basicDefinition = $definition->getBasicDefinition();

        $headerButtons = array();
        if (!$this->isSelectModeActive()) {
            return '';
        }

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
                    'act=edit&amp;pid=' . ModelId::fromModel($parentModel)->getSerialized()
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
                $subFilter->andParentIsNot(ModelId::fromModel($parentModel));
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
                        '&amp;pid=' . ModelId::fromModel($parentModel)->getSerialized()
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

        return implode(' ', $headerButtons);
    }

    /**
     * Retrieve a list of html buttons to use in the top panel (submit area).
     *
     * @param ModelInterface $parentModel The parent model.
     *
     * @return null|string
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
                'id'    => ModelId::fromModel($parentModel)->getSerialized(),
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
                        $query['pid'] = ModelId::fromModel($parents->get(0))->getSerialized();
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
        if ($groupingInformation && ($groupingInformation['mode'] != GroupAndSortingInformationInterface::GROUP_NONE)) {
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
     *
     * @deprecated Use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\ParentEnforcingListener
     *
     * @see ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\ParentEnforcingListener
     */
    public function enforceModelRelationship($model)
    {
        // Fallback implementation.
        $listener = new ParentEnforcingListener();
        $listener->process(new EnforceModelRelationshipEvent($this->getEnvironment(), $model));
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
}

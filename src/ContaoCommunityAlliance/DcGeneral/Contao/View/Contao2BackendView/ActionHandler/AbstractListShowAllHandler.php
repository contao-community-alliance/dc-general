<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\Environment;
use Contao\Message;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ButtonRenderer;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\GlobalButtonRenderer;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\PanelRenderer;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractEnvironmentAwareHandler;

/**
 * This class is the abstract base for parent list and plain list "showAll" commands.
 */
abstract class AbstractListShowAllHandler extends AbstractEnvironmentAwareHandler
{
    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $event  = $this->getEvent();
        $action = $event->getAction();
        $basic  = $this->environment->getDataDefinition()->getBasicDefinition();
        if ($event->getAction()->getName() !== 'showAll' || !$this->wantToHandle($basic->getMode())) {
            return;
        }

        // Edit only mode, forward to edit action.
        if ($basic->isEditOnlyMode()) {
            $this->callAction('edit', $action->getArguments());
            return;
        }
        $grouping = ViewHelpers::getGroupingMode($this->environment);

        Message::reset();

        // Process now.
        $collection = $this->loadCollection();
        $this->handleEditAllButton($collection);
        $this->renderCollection($collection, $grouping);
        $template = $this->determineTemplate($grouping);
        $template->set('collection', $collection);
        $template->set('mode', ($grouping ? $grouping['mode'] : null));
        $this->renderTemplate($template);

        $clipboard = new ViewEvent($this->environment, $action, DcGeneralViews::CLIPBOARD, []);
        $this->environment->getEventDispatcher()->dispatch(DcGeneralEvents::VIEW, $clipboard);

        $result              = [];
        $result['panel']     = $this->panel();
        $result['buttons']   = $this->generateHeaderButtons();
        $result['clipboard'] = $clipboard->getResponse();
        $result['body']      = $template->parse();

        $event->setResponse(\implode("\n", $result));
    }

    /**
     * Retrieve the view section for this view.
     *
     * @return Contao2BackendViewDefinitionInterface
     */
    protected function getViewSection()
    {
        return $this->environment->getDataDefinition()->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
    }

    /**
     * Check if the action should be handled.
     *
     * @param string $mode The list mode.
     *
     * @return mixed
     */
    abstract protected function wantToHandle($mode);

    /**
     * Translate a string.
     *
     * @param string      $key    The translation key.
     * @param string|null $domain The domain name to use (if null, default definition name will be used).
     *
     * @return string|array
     */
    protected function translate($key, $domain = null)
    {
        if (null === $domain) {
            $domain = $this->environment->getDataDefinition()->getName();
        }
        $translator = $this->environment->getTranslator();
        $value      = $translator->translate($key, $domain);
        if ($value !== $key) {
            return $value;
        }

        return $translator->translate($key);
    }

    /**
     * Render a model.
     *
     * @param ModelInterface $model The model to render.
     *
     * @return void
     */
    protected function renderModel(ModelInterface $model)
    {
        $event = new FormatModelLabelEvent($this->environment, $model);
        $this->environment->getEventDispatcher()->dispatch(
            DcGeneralEvents::FORMAT_MODEL_LABEL,
            $event
        );

        $model->setMeta($model::LABEL_VALUE, $event->getLabel());
    }

    /**
     * Create a new instance of ContaoBackendViewTemplate with the template file of the given name.
     *
     * @param string $strTemplate Name of the template to create.
     *
     * @return ContaoBackendViewTemplate
     */
    protected function getTemplate($strTemplate)
    {
        $template = new ContaoBackendViewTemplate($strTemplate);
        $template->setTranslator($this->environment->getTranslator());

        return $template;
    }

    /**
     * Determine the template to use.
     *
     * @param array $groupingInformation The grouping information as retrieved via ViewHelpers::getGroupingMode().
     *
     * @return ContaoBackendViewTemplate
     */
    abstract protected function determineTemplate($groupingInformation);

    /**
     * Prepare the template.
     *
     * @param ContaoBackendViewTemplate $template The template to populate.
     *
     * @return void
     */
    protected function renderTemplate(ContaoBackendViewTemplate $template)
    {
        $definition = $this->environment->getDataDefinition();
        $showColumn = $this->getViewSection()->getListingConfig()->getShowColumns();

        $template->set('subHeadline', $this->translate('MSC.select_models'));
        $template->set('tableName', null !== $definition->getName() ? $definition->getName() : 'none');
        $template->set('select', 'select' === $this->environment->getInputProvider()->getParameter('act'));
        $template->set('action', \ampersand(Environment::get('request'), true));
        $template->set('selectButtons', $this->getSelectButtons());
        $template->set('sortable', $this->isSortable());
        $template->set('showColumns', $showColumn);
        $template->set('tableHead', $showColumn ? $this->getTableHead() : '');
        // Add breadcrumb, if we have one.
        $template->set('breadcrumb', $this->breadcrumb());
        $template->set('floatRightSelectButtons', true);
        $template->set('selectCheckBoxName', 'models[]');
        $template->set('selectCheckBoxIdPrefix', 'models_');
        $template->set('selectContainer', $this->getSelectContainer());

        if ((null !== $template->get('action'))
            && (false !== \strpos($template->get('action'), 'select=models'))
        ) {
            $template->set('action', \str_replace('select=models', 'select=properties', $template->get('action')));
        }
    }

    /**
     * Load the collection of child items and the parent item for the currently selected parent item.
     *
     * Consumes input parameter "id".
     *
     * @return CollectionInterface
     */
    protected function loadCollection()
    {
        $dataProvider = $this->environment->getDataProvider();
        $dataConfig   = $this->environment->getBaseConfigRegistry()->getBaseConfig();

        $listingConfig = $this->getViewSection()->getListingConfig();
        $panel         = $this->environment->getView()->getPanel();

        ViewHelpers::initializeSorting($panel, $dataConfig, $listingConfig);

        return $dataProvider->fetchAll($dataConfig);
    }

    /**
     * Generate all buttons for the header of a view.
     *
     * @return string
     */
    private function generateHeaderButtons()
    {
        $renderer = new GlobalButtonRenderer($this->environment);
        return $renderer->render();
    }

    /**
     * Render the collection.
     *
     * @param CollectionInterface $collection The collection to render.
     * @param array               $grouping   The grouping information.
     *
     * @return void
     */
    private function renderCollection(CollectionInterface $collection, $grouping)
    {
        $environment = $this->getEnvironment();
        $clipboard   = $environment->getClipboard();
        $view        = $this->getViewSection();
        $listing     = $view->getListingConfig();
        $remoteCur   = null;
        $groupClass  = 'tl_folder_tlist';
        $eoCount     = -1;

        // Generate buttons - only if not in select mode!
        if ('select' !== $environment->getInputProvider()->getParameter('act')) {
            $buttonRenderer = new ButtonRenderer($this->environment);
            $buttonRenderer->renderButtonsForCollection($collection);
        }

        // Run each model.
        $index = 0;
        foreach ($collection as $model) {
            $index++;

            /** @var ModelInterface $model */
            $this->addGroupHeader((array) $grouping, $model, $groupClass, $eoCount, $remoteCur);

            if ($listing->getItemCssClass()) {
                $model->setMeta($model::CSS_CLASS, $listing->getItemCssClass());
            }
            $cssClasses = [((++$eoCount) % 2 == 0) ? 'even' : 'odd'];

            (null !== $model->getMeta($model::CSS_ROW_CLASS)) ?
                $cssClasses[] = $model->getMeta($model::CSS_ROW_CLASS) : null;

            $modelId = ModelId::fromModel($model);
            if ($clipboard->hasId($modelId)) {
                $cssClasses[] = 'tl_folder_clipped';
            }

            $model->setMeta($model::CSS_ROW_CLASS, \implode(' ', $cssClasses));

            $this->renderModel($model);
        }
    }

    /**
     * Add the group header information to the model.
     *
     * @param array          $grouping   The grouping information.
     * @param ModelInterface $model      The model.
     * @param string         $groupClass The group class.
     * @param integer        $eoCount    The row even odd counter.
     *
     * @param mixed          $remoteCur  The current remote.
     *
     * @return void
     */
    private function addGroupHeader(array $grouping, ModelInterface $model, &$groupClass, &$eoCount, &$remoteCur = null)
    {
        if ($grouping && GroupAndSortingInformationInterface::GROUP_NONE !== $grouping['mode']) {
            $remoteNew = $this->renderGroupHeader(
                $grouping['property'],
                $model,
                $grouping['mode'],
                $grouping['length']
            );

            $model->setMeta(
                $model::GROUP_VALUE,
                [
                    'class' => $groupClass,
                    'value' => $remoteNew
                ]
            );
            // Add the group header if it differs from the last header.
            if (($remoteNew != $remoteCur) || ($remoteCur === null)) {
                $eoCount    = -1;
                $groupClass = 'tl_folder_list';
                $remoteCur  = $remoteNew;
            }
        }
    }

    /**
     * Render the panel.
     *
     * @param string[] $ignoredPanels A list with ignored elements [Optional].
     *
     * @throws DcGeneralRuntimeException When no information of panels can be obtained from the data container.
     *
     * @return string
     */
    private function panel($ignoredPanels = [])
    {
        $renderer = new PanelRenderer($this->environment->getView());
        return $renderer->render($ignoredPanels);
    }

    /**
     * Get the table headings.
     *
     * @return array
     */
    protected function getTableHead()
    {
        $tableHead  = [];
        $definition = $this->getEnvironment()->getDataDefinition();
        $properties = $definition->getPropertiesDefinition();
        $formatter  = $this->getViewSection()->getListingConfig()->getLabelFormatter($definition->getName());
        $sorting    = ViewHelpers::getCurrentSorting($this->environment);
        $columns    = $this->getSortingColumns($sorting);
        foreach ($formatter->getPropertyNames() as $field) {
            // Skip unknown properties. This may happen if the property is not defined for editing but only listing.
            if (!$properties->hasProperty($field)) {
                continue;
            }
            $label = $properties->getProperty($field)->getLabel();

            $tableHead[] = [
                'class'   => 'tl_folder_tlist col_' . $field . (\in_array($field, $columns) ? ' ordered_by' : ''),
                'content' => $label
            ];
        }

        $tableHead[] = [
            'class'   => 'tl_folder_tlist tl_right_nowrap',
            'content' => $this->renderPasteTopButton($sorting) ?: '&nbsp;'
        ];

        return $tableHead;
    }

    /**
     * Return the formatted value for use in group headers as string.
     *
     * @param string         $field       The name of the property to format.
     * @param ModelInterface $model       The model from which the value shall be taken from.
     * @param string         $groupMode   The grouping mode in use.
     * @param int            $groupLength The length of the value to use for grouping (only used when grouping mode is
     *                                    ListingConfigInterface::GROUP_CHAR).
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function renderGroupHeader($field, ModelInterface $model, $groupMode, $groupLength)
    {
        $property = $this->environment->getDataDefinition()->getPropertiesDefinition()->getProperty($field);

        // No property? Get out!
        if (!$property) {
            return '-';
        }

        $event = new GetGroupHeaderEvent($this->getEnvironment(), $model, $field, null, $groupMode, $groupLength);
        $this->getEnvironment()->getEventDispatcher()->dispatch($event::NAME, $event);

        return $event->getValue();
    }

    /**
     * Retrieve a list of html buttons to use in the bottom panel (submit area) when in select mode.
     *
     * @return string[]
     */
    protected function getSelectButtons()
    {
        $event = new GetSelectModeButtonsEvent($this->getEnvironment());
        $event->setButtons([]);
        $this->getEnvironment()->getEventDispatcher()->dispatch(GetSelectModeButtonsEvent::NAME, $event);

        return $event->getButtons();
    }

    /**
     * Check if the models are sortable.
     *
     * @return bool
     */
    private function isSortable()
    {
        $environment     = $this->getEnvironment();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        return ((true === (bool) ViewHelpers::getManualSortingProperty($environment))
                && (true === $basicDefinition->isEditable()));
    }

    /**
     * Render paste top button. Returns null if no button should be rendered.
     *
     * @param GroupAndSortingDefinitionInterface|null $sorting The sorting mode.
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

        if (!$sorting || $clipboard->isEmpty($filter)) {
            return null;
        }
        if (!ViewHelpers::getManualSortingProperty($this->environment)) {
            return null;
        }

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $dispatcher->dispatch(
            ContaoEvents::BACKEND_ADD_TO_URL,
            new AddToUrlEvent(
                'act=paste&after=' . ModelId::fromValues($definition->getName(), '0')->getSerialized()
            )
        );

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'pasteafter.gif',
                $this->translate('pasteafter.0'),
                'class="blink"'
            )
        );

        return \sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $urlEvent->getUrl(),
            \specialchars($this->translate('pasteafter.0')),
            $imageEvent->getHtml()
        );
    }

    /**
     * Render the breadcrumb.
     *
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function breadcrumb()
    {
        $event = new GetBreadcrumbEvent($this->environment);
        $this->environment->getEventDispatcher()->dispatch($event::NAME, $event);
        $elements = $event->getElements();
        if (empty($elements)) {
            return null;
        }

        $GLOBALS['TL_CSS'][] = 'system/modules/dc-general/html/css/generalBreadcrumb.css';

        $template = $this->getTemplate('dcbe_general_breadcrumb');
        $template->set('elements', $elements);

        return $template->parse();
    }

    /**
     * Determine the current sorting columns.
     *
     * @param GroupAndSortingDefinitionInterface|null $sortingDefinition The sorting definition.
     *
     * @return array
     */
    private function getSortingColumns($sortingDefinition)
    {
        if (null === $sortingDefinition) {
            return [];
        }

        $sortingColumns = [];
        /** @var GroupAndSortingDefinitionInterface $sortingDefinition */
        foreach ($sortingDefinition as $information) {
            /** @var GroupAndSortingInformationInterface $information */
            if ($information->getProperty()) {
                $sortingColumns[] = $information->getProperty();
            }
        }

        return $sortingColumns;
    }

    /**
     * Get the the container of selections.
     *
     * @return array
     */
    private function getSelectContainer()
    {
        $environment    = $this->getEnvironment();
        $inputProvider  = $environment->getInputProvider();
        $sessionStorage = $environment->getSessionStorage();
        $dataDefinition = $environment->getDataDefinition();

        $sessionName = $dataDefinition->getName() . '.' . $inputProvider->getParameter('mode');
        if (!$sessionStorage->has($sessionName)) {
            return [];
        }

        $selectAction = $inputProvider->getParameter('select');
        if (!$selectAction) {
            return [];
        }

        $session = $sessionStorage->get($sessionName);
        if (!\array_key_exists($selectAction, $session)) {
            return [];
        }

        return $session[$selectAction];
    }

    /**
     * Is the collection empty, the disable the edit/override all button.
     *
     * @param CollectionInterface $collection The collection.
     *
     * @return void
     */
    private function handleEditAllButton(CollectionInterface $collection)
    {
        if (0 < $collection->count()) {
            return;
        }

        $enviroment     = $this->getEnvironment();
        $dataDefinition = $enviroment->getDataDefinition();
        $backendView    = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $globalCommands = $backendView->getGlobalCommands();

        if (!$globalCommands->hasCommandNamed('all')) {
            return;
        }

        $allCommand = $globalCommands->getCommandNamed('all');
        $allCommand->setDisabled(true);
    }
}

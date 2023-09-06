<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\Backend;
use Contao\Environment;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
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
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;
use ContaoCommunityAlliance\Translator\TranslatorInterface as CcaTranslator;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_key_exists;
use function implode;
use function in_array;
use function sprintf;
use function str_replace;
use function strpos;
use function trigger_error;

/**
 * This class is the abstract base for parent list and plain list "showAll" commands.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class AbstractListShowAllHandler
{
    use CallActionTrait;
    use RequestScopeDeterminatorAwareTrait;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * The cca translator.
     *
     * @var CcaTranslator|TranslatorInterface
     */
    private $ccaTranslator;

    /**
     * The token manager.
     *
     * @var CsrfTokenManagerInterface
     */
    private CsrfTokenManagerInterface $tokenManager;

    /**
     * The token name.
     *
     * @var string
     */
    private string $tokenName;

    /**
     * AbstractHandler constructor.
     *
     * @param RequestScopeDeterminator          $scopeDeterminator The request mode determinator.
     * @param TranslatorInterface               $translator        The translator.
     * @param CcaTranslator|TranslatorInterface $ccaTranslator     The cca translator.
     * @param CsrfTokenManagerInterface|null    $tokenManager      The token manager.
     * @param string|null                       $tokenName         The token name.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        TranslatorInterface $translator,
        CcaTranslator $ccaTranslator,
        ?CsrfTokenManagerInterface $tokenManager = null,
        ?string $tokenName = null
    ) {
        $this->setScopeDeterminator($scopeDeterminator);

        $this->translator    = $translator;
        $this->ccaTranslator = $ccaTranslator;

        if (null === $tokenManager) {
            $tokenManager = System::getContainer()->get('security.csrf.token_manager');
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the csrf token manager as 4th argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in DCG 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }
        if (null === $tokenName) {
            $tokenName = System::getContainer()->getParameter('contao.csrf_token_name');
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the csrf token name as 5th argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in DCG 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        $this->tokenManager  = $tokenManager;
        $this->tokenName     = $tokenName;
    }

    /**
     * Handle the event to process the action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $environment = $event->getEnvironment();
        $basic       = $environment->getDataDefinition()->getBasicDefinition();
        $action      = $event->getAction();

        if (
            null !== $event->getResponse()
            || ('showAll' !== $action->getName())
            || !$this->wantToHandle($basic->getMode(), $action)
        ) {
            return;
        }

        if (false !== ($response = $this->process($action, $environment))) {
            $event->setResponse($response);
        }
    }

    /**
     * Process the action.
     *
     * @param Action               $action      The action being handled.
     * @param EnvironmentInterface $environment Current dc-general environment.
     *
     * @return string
     */
    protected function process(Action $action, EnvironmentInterface $environment)
    {
        // Edit only mode, forward to edit action.
        if ($environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->callAction($environment, 'edit', $action->getArguments());
        }

        $grouping = ViewHelpers::getGroupingMode($environment);

        Message::reset();

        // Process now.
        $collection = $this->loadCollection($environment);
        $this->handleEditAllButton($collection, $environment);
        $this->renderCollection($environment, $collection, $grouping);

        $template = $this->determineTemplate($grouping)
            ->set('collection', $collection)
            ->set('mode', ($grouping ? $grouping['mode'] : null))
            ->set('theme', Backend::getTheme());
        $this->renderTemplate($template, $environment);

        $clipboard = new ViewEvent($environment, $action, DcGeneralViews::CLIPBOARD, []);
        $environment->getEventDispatcher()->dispatch($clipboard, DcGeneralEvents::VIEW);

        return implode(
            "\n",
            [
                'language'  => $this->languageSwitcher($environment),
                'panel'     => $this->panel($environment),
                'buttons'   => $this->generateHeaderButtons($environment),
                'clipboard' => $clipboard->getResponse(),
                'body'      => $template->parse()
            ]
        );
    }

    /**
     * Execute the multi language support.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    private function languageSwitcher(EnvironmentInterface $environment)
    {
        $template = new ContaoBackendViewTemplate('dcbe_general_language_selector');

        $dataProvider = $environment->getDataProvider();
        if (!$dataProvider instanceof MultiLanguageDataProviderInterface) {
            return '';
        }

        /** @var MultiLanguageDataProviderInterface $dataProvider */

        return $template
            ->set('languages', $environment->getController()->getSupportedLanguages(null))
            ->set('language', $dataProvider->getCurrentLanguage())
            ->set('submit', $this->translator->trans('MSC.showSelected', [], 'contao_default'))
            ->set('REQUEST_TOKEN', $this->tokenManager->getToken($this->tokenName))
            ->parse();
    }

    /**
     * Retrieve the view section for this view.
     *
     * @param ContainerInterface $definition Data container definition.
     *
     * @return DefinitionInterface|Contao2BackendViewDefinitionInterface
     */
    protected function getViewSection(ContainerInterface $definition)
    {
        return $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
    }

    /**
     * Check if the action should be handled.
     *
     * @param string $mode   The list mode.
     * @param Action $action The action.
     *
     * @return mixed
     */
    abstract protected function wantToHandle($mode, Action $action);

    /**
     * Translate a string.
     *
     * @param string      $key        The translation key.
     * @param string|null $domain     The domain name to use.
     * @param array       $parameters Parameters.
     *
     * @return array|string
     */
    protected function translate($key, $domain, array $parameters = [])
    {
        $translated = $this->translator->trans($key, $parameters, $domain);

        // Fallback translate for non symfony domain.
        if ($translated === $key) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Fallback translation for contao lang in the global array. ' .
                'This will remove in the future, use the symfony domain translation.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            $translated =
                $this->translator->trans(sprintf('%s.%s', $domain, $key), $parameters, sprintf('contao_%s', $domain));
        }

        return $translated;
    }

    /**
     * Render a model.
     *
     * @param ModelInterface       $model       The model to render.
     * @param EnvironmentInterface $environment Current environment.
     *
     * @return void
     */
    protected function renderModel(ModelInterface $model, EnvironmentInterface $environment)
    {
        $event = new FormatModelLabelEvent($environment, $model);
        $environment->getEventDispatcher()->dispatch($event, DcGeneralEvents::FORMAT_MODEL_LABEL);

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
        return (new ContaoBackendViewTemplate($strTemplate))->setTranslator($this->ccaTranslator);
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
     * @param ContaoBackendViewTemplate $template    The template to populate.
     * @param EnvironmentInterface      $environment The environment.
     *
     * @return void
     */
    protected function renderTemplate(ContaoBackendViewTemplate $template, EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        $showColumn = $this->getViewSection($definition)->getListingConfig()->getShowColumns();

        $template
            ->set('subHeadline', $this->translate('MSC.select_models', 'contao_default'))
            ->set('tableName', ($definition->getName() ?? 'none'))
            ->set('select', 'select' === $environment->getInputProvider()->getParameter('act'))
            ->set('action', StringUtil::ampersand(Environment::get('request')))
            ->set('selectButtons', $this->getSelectButtons($environment))
            ->set('sortable', $this->isSortable($environment))
            ->set('showColumns', $showColumn)
            ->set('tableHead', $showColumn ? $this->getTableHead($environment) : '')
            // Add breadcrumb, if we have one.
            ->set('breadcrumb', $this->breadcrumb($environment))
            ->set('floatRightSelectButtons', true)
            ->set('selectCheckBoxName', 'models[]')
            ->set('selectCheckBoxIdPrefix', 'models_')
            ->set('selectContainer', $this->getSelectContainer($environment));

        if (
            (null !== $template->get('action'))
            && (false !== strpos($template->get('action'), 'select=models'))
        ) {
            $template->set('action', str_replace('select=models', 'select=properties', $template->get('action')));
        }
    }

    /**
     * Load the collection of child items and the parent item for the currently selected parent item.
     *
     * Consumes input parameter "id".
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return CollectionInterface
     */
    protected function loadCollection(EnvironmentInterface $environment)
    {
        $dataConfig    = $environment->getBaseConfigRegistry()->getBaseConfig();
        $listingConfig = $this->getViewSection($environment->getDataDefinition())->getListingConfig();
        $panel         = $environment->getView()->getPanel();

        ViewHelpers::initializeSorting($panel, $dataConfig, $listingConfig);

        return $environment->getDataProvider()->fetchAll($dataConfig);
    }

    /**
     * Generate all buttons for the header of a view.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    private function generateHeaderButtons(EnvironmentInterface $environment)
    {
        return (new GlobalButtonRenderer($environment))->render();
    }

    /**
     * Render the collection.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param CollectionInterface  $collection  The collection to render.
     * @param array                $grouping    The grouping information.
     *
     * @return void
     */
    private function renderCollection(EnvironmentInterface $environment, CollectionInterface $collection, $grouping)
    {
        $listing    = $this->getViewSection($environment->getDataDefinition())->getListingConfig();
        $remoteCur  = null;
        $groupClass = 'tl_folder_tlist';
        $eoCount    = -1;

        // Generate buttons - only if not in select mode!
        if ('select' !== $environment->getInputProvider()->getParameter('act')) {
            $buttonRenderer = new ButtonRenderer($environment);
            $buttonRenderer->renderButtonsForCollection($collection);
        }

        // Run each model.
        foreach ($collection as $model) {
            /** @var ModelInterface $model */
            $this->addGroupHeader($environment, (array) $grouping, $model, $groupClass, $eoCount, $remoteCur);

            if ($listing->getItemCssClass()) {
                $model->setMeta($model::CSS_CLASS, $listing->getItemCssClass());
            }
            $cssClasses = [(0 === (++$eoCount) % 2) ? 'even' : 'odd'];

            (null !== $model->getMeta($model::CSS_ROW_CLASS)) ?
                $cssClasses[] = $model->getMeta($model::CSS_ROW_CLASS) : null;

            $modelId = ModelId::fromModel($model);
            if ($environment->getClipboard()->hasId($modelId)) {
                $cssClasses[] = 'tl_folder_clipped';
            }

            $model->setMeta($model::CSS_ROW_CLASS, implode(' ', $cssClasses));

            $this->renderModel($model, $environment);
        }
    }

    /**
     * Add the group header information to the model.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param array                $grouping    The grouping information.
     * @param ModelInterface       $model       The model.
     * @param string               $groupClass  The group class.
     * @param integer              $eoCount     The row even odd counter.
     * @param mixed                $remoteCur   The current remote.
     *
     * @return void
     */
    private function addGroupHeader(
        EnvironmentInterface $environment,
        array $grouping,
        ModelInterface $model,
        &$groupClass,
        &$eoCount,
        &$remoteCur = null
    ) {
        if ($grouping && GroupAndSortingInformationInterface::GROUP_NONE !== $grouping['mode']) {
            $remoteNew = $this->renderGroupHeader(
                $grouping['property'],
                $model,
                $grouping['mode'],
                $grouping['length'],
                $environment
            );

            $model->setMeta(
                $model::GROUP_VALUE,
                [
                    'class' => $groupClass,
                    'value' => $remoteNew
                ]
            );
            // Add the group header if it differs from the last header.
            if (($remoteCur !== $remoteNew) || (null === $remoteCur)) {
                $eoCount    = -1;
                $groupClass = 'tl_folder_list';
                $remoteCur  = $remoteNew;
            }
        }
    }

    /**
     * Render the panel.
     *
     * @param EnvironmentInterface $environment   The environment.
     * @param string[]             $ignoredPanels A list with ignored elements [Optional].
     *
     * @return string When no information of panels can be obtained from the data container.
     */
    private function panel(EnvironmentInterface $environment, $ignoredPanels = [])
    {
        return (new PanelRenderer($environment->getView()))->render($ignoredPanels);
    }

    /**
     * Get the table headings.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array
     */
    private function getTableHead(EnvironmentInterface $environment)
    {
        $tableHead  = [];
        $definition = $environment->getDataDefinition();
        $properties = $definition->getPropertiesDefinition();
        $formatter  = $this->getViewSection($definition)->getListingConfig()->getLabelFormatter($definition->getName());
        $sorting    = ViewHelpers::getCurrentSorting($environment);
        $columns    = $this->getSortingColumns($sorting);
        foreach ($formatter->getPropertyNames() as $field) {
            $tableHead[] = [
                'class'   => 'tl_folder_tlist col_' . $field . (in_array($field, $columns) ? ' ordered_by' : ''),
                'content' => $properties->hasProperty($field)
                    ? $properties->getProperty($field)->getLabel()
                    : $this->translate($definition->getName() . '.' . $field . '.0', 'contao_' . $definition->getName())
            ];
        }

        $tableHead[] = [
            'class'   => 'tl_folder_tlist tl_right_nowrap',
            'content' => $this->renderPasteTopButton($environment, $sorting) ?: '&nbsp;'
        ];

        return $tableHead;
    }

    /**
     * Return the formatted value for use in group headers as string.
     *
     * @param string               $field       The name of the property to format.
     * @param ModelInterface       $model       The model from which the value shall be taken from.
     * @param string               $groupMode   The grouping mode in use.
     * @param int                  $groupLength The length of the value to use for grouping (only used when grouping.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function renderGroupHeader(
        $field,
        ModelInterface $model,
        $groupMode,
        $groupLength,
        EnvironmentInterface $environment
    ) {
        // No property? Get out!
        if (!$environment->getDataDefinition()->getPropertiesDefinition()->getProperty($field)) {
            return '-';
        }

        $event = new GetGroupHeaderEvent($environment, $model, $field, null, $groupMode, $groupLength);
        $environment->getEventDispatcher()->dispatch($event, $event::NAME);

        return $event->getValue();
    }

    /**
     * Retrieve a list of html buttons to use in the bottom panel (submit area) when in select mode.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string[]
     */
    protected function getSelectButtons(EnvironmentInterface $environment)
    {
        $event = new GetSelectModeButtonsEvent($environment);
        $event->setButtons([]);
        $environment->getEventDispatcher()->dispatch($event, GetSelectModeButtonsEvent::NAME);

        return $event->getButtons();
    }

    /**
     * Check if the models are sortable.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function isSortable(EnvironmentInterface $environment)
    {
        return ((true === (bool) ViewHelpers::getManualSortingProperty($environment))
                && (true === $environment->getDataDefinition()->getBasicDefinition()->isEditable()));
    }

    /**
     * Render paste top button. Returns null if no button should be rendered.
     *
     * @param EnvironmentInterface                    $environment The environment.
     * @param GroupAndSortingDefinitionInterface|null $sorting     The sorting mode.
     *
     * @return string
     */
    protected function renderPasteTopButton(EnvironmentInterface $environment, $sorting)
    {
        $definition     = $environment->getDataDefinition();
        $dispatcher     = $environment->getEventDispatcher();
        $languageDomain = 'contao_' . $definition->getName();

        $filter = new Filter();
        $filter->andModelIsFromProvider($definition->getBasicDefinition()->getDataProvider());

        if (!$sorting || $environment->getClipboard()->isEmpty($filter)) {
            return null;
        }
        if (!ViewHelpers::getManualSortingProperty($environment)) {
            return null;
        }

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $dispatcher->dispatch(
            new AddToUrlEvent(
                'act=paste&after=' . ModelId::fromValues($definition->getName(), '0')->getSerialized()
            ),
            ContaoEvents::BACKEND_ADD_TO_URL
        );

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            new GenerateHtmlEvent(
                'pasteafter.svg',
                $this->translate('pasteafter.0', $languageDomain),
                'class="blink"'
            ),
            ContaoEvents::IMAGE_GET_HTML
        );

        return sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $urlEvent->getUrl(),
            StringUtil::specialchars($this->translate('pasteafter.0', $languageDomain)),
            $imageEvent->getHtml()
        );
    }

    /**
     * Render the breadcrumb.
     *
     * @param EnvironmentInterface $environment Environment.
     *
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function breadcrumb(EnvironmentInterface $environment)
    {
        $event = new GetBreadcrumbEvent($environment);
        $environment->getEventDispatcher()->dispatch($event, $event::NAME);
        $elements = $event->getElements();
        if (empty($elements)) {
            return null;
        }

        $GLOBALS['TL_CSS']['cca.dc-general.generalBreadcrumb'] = 'bundles/ccadcgeneral/css/generalBreadcrumb.css';

        return $this
            ->getTemplate('dcbe_general_breadcrumb')
            ->set('elements', $elements)
            ->parse();
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
     * @param EnvironmentInterface $environment The Environment.
     *
     * @return array
     */
    private function getSelectContainer(EnvironmentInterface $environment)
    {
        $inputProvider  = $environment->getInputProvider();
        $sessionStorage = $environment->getSessionStorage();

        $sessionName = $environment->getDataDefinition()->getName() . '.' . $inputProvider->getParameter('mode');
        if (!$sessionStorage->has($sessionName)) {
            return [];
        }

        $selectAction = $inputProvider->getParameter('select');
        if (!$selectAction) {
            return [];
        }

        $session = $sessionStorage->get($sessionName);
        if (!array_key_exists($selectAction, $session)) {
            return [];
        }

        return $session[$selectAction];
    }

    /**
     * Is the collection empty, the disable the edit/override all button.
     *
     * @param CollectionInterface  $collection  The collection.
     * @param EnvironmentInterface $environment The Environment.
     *
     * @return void
     */
    private function handleEditAllButton(CollectionInterface $collection, EnvironmentInterface $environment)
    {
        if (0 < $collection->count()) {
            return;
        }

        $dataDefinition = $environment->getDataDefinition();
        $backendView    = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $globalCommands = $backendView->getGlobalCommands();

        if (!$globalCommands->hasCommandNamed('all')) {
            return;
        }

        $allCommand = $globalCommands->getCommandNamed('all');
        $allCommand->setDisabled(true);
    }
}

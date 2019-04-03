<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Backend;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Controller\TreeCollector;
use ContaoCommunityAlliance\DcGeneral\Controller\TreeNodeStates;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\TreeEnforcingListener;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Panel\LimitElementInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\SortElementInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TreeView.
 *
 * Implementation for tree displaying.
 */
class TreeView extends BaseView
{
    /**
     * Retrieve the id for this view.
     *
     * @return string
     */
    protected function getToggleId()
    {
        return 'tree';
    }

    /**
     * Create a tree states instance.
     *
     * @return TreeNodeStates
     */
    protected function getTreeNodeStates()
    {
        $sessionStorage = $this->getEnvironment()->getSessionStorage();
        $openElements   = $sessionStorage->get($this->getToggleId());

        if (!\is_array($openElements)) {
            $openElements = [];
        }

        return new TreeNodeStates($openElements);
    }

    /**
     * Save a tree node states instance to the session.
     *
     * @param TreeNodeStates $states The instance to be saved.
     *
     * @return void
     */
    protected function saveTreeNodeStates(TreeNodeStates $states)
    {
        $sessionStorage = $this->getEnvironment()->getSessionStorage();
        $sessionStorage->set($this->getToggleId(), $states->getStates());
    }

    /**
     * Check the get parameters if there is any node toggling.
     *
     * CAUTION: If there has been any action, the browser will get redirected and the script therefore exited.
     *
     * @return void
     */
    private function handleNodeStateChanges()
    {
        $input = $this->getEnvironment()->getInputProvider();
        if (($modelId = $input->getParameter('ptg')) && ($providerName = $input->getParameter('provider'))) {
            $states = $this->getTreeNodeStates();
            // Check if the open/close all has been triggered or just a model.
            if ('all' === $modelId) {
                if ($states->isAllOpen()) {
                    $states->resetAll();
                }
                $states->setAllOpen($states->isAllOpen());
            } else {
                $this->toggleModel($providerName, $modelId);
            }

            ViewHelpers::redirectHome($this->environment);
        }
    }

    /**
     * Toggle the model with the given id from the given provider.
     *
     * @param string $providerName The data provider name.
     * @param mixed  $modelId      The id of the model.
     *
     * @return void
     */
    private function toggleModel($providerName, $modelId)
    {
        $this->saveTreeNodeStates($this->getTreeNodeStates()->toggleModel($providerName, $modelId));
    }

    /**
     * Determine if the passed model is expanded.
     *
     * @param ModelInterface $model The model to check.
     *
     * @return bool
     */
    protected function isModelOpen($model)
    {
        return $this->getTreeNodeStates()->isModelOpen($model->getProviderName(), $model->getID());
    }

    /**
     * Load the collection of child items and the parent item for the currently selected parent item.
     *
     * @param string $rootId       The root element (or null to fetch everything).
     * @param int    $level        The current level in the tree (of the optional root element).
     * @param string $providerName The data provider from which the optional root element shall be taken from.
     *
     * @return CollectionInterface
     */
    public function loadCollection($rootId = null, $level = 0, $providerName = null)
    {
        $environment  = $this->getEnvironment();
        $dataDriver   = $environment->getDataProvider($providerName);
        $realProvider = $dataDriver->getEmptyModel()->getProviderName();
        $collector    = new TreeCollector(
            $environment,
            $this->getPanel(),
            $this->getViewSection()->getListingConfig()->getDefaultSortingFields(),
            $this->getTreeNodeStates()
        );

        $collection = $rootId
            ? $collector->getTreeCollectionRecursive($rootId, $level, $realProvider)
            : $collector->getChildrenOf(
                $realProvider,
                $level,
                $environment->getInputProvider()->hasParameter('pid') ? $this->loadParentModel() : null
            );

        if ($rootId) {
            $treeData = $dataDriver->getEmptyCollection();
            $model    = $collection->get(0);

            if (!$model->getMeta(ModelInterface::HAS_CHILDREN)) {
                return $treeData;
            }

            foreach ($model->getMeta($model::CHILD_COLLECTIONS) as $collection) {
                foreach ($collection as $objSubModel) {
                    $treeData->push($objSubModel);
                }
            }

            return $treeData;
        }

        return $collection;
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

        if (!($parentId = $environment->getInputProvider()->getParameter('pid'))) {
            throw new DcGeneralRuntimeException(
                'TreeView needs a proper parent id defined, somehow none is defined?',
                1
            );
        }

        $pid = ModelId::fromSerialized($parentId);

        if (!($parentProvider = $environment->getDataProvider($pid->getDataProviderName()))
        ) {
            throw new DcGeneralRuntimeException(
                'TreeView needs a proper parent data provider defined, somehow none is defined?',
                1
            );
        }

        $collector = new ModelCollector($environment);
        if (!($parentItem = $collector->getModel($pid))) {
            // No parent item found, might have been deleted.
            // We transparently create it for our filter to be able to filter to nothing.
            $parentItem = $parentProvider->getEmptyModel();
            $parentItem->setId($parentId);
        }

        return $parentItem;
    }

    /**
     * Calculate the fields needed by a tree label for the given data provider name.
     *
     * @param string $providerName The name of the data provider.
     *
     * @return array
     */
    protected function calcLabelFields($providerName)
    {
        return $this->getViewSection()->getListingConfig()->getLabelFormatter($providerName)->getPropertyNames();
    }

    /**
     * Render a given model.
     *
     * @param ModelInterface $model    The model to render.
     * @param string         $toggleID The id of the toggler.
     *
     * @return string
     */
    protected function parseModel($model, $toggleID)
    {
        $event = new FormatModelLabelEvent($this->environment, $model);
        $this->environment->getEventDispatcher()->dispatch(
            DcGeneralEvents::FORMAT_MODEL_LABEL,
            $event
        );

        $model->setMeta($model::LABEL_VALUE, $event->getLabel());

        $template = $this->getTemplate('dcbe_general_treeview_entry');

        if ($model->getMeta($model::SHOW_CHILDREN)) {
            $toggleTitle = $this->getEnvironment()->getTranslator()->translate('collapseNode', 'MSC');
        } else {
            $toggleTitle = $this->getEnvironment()->getTranslator()->translate('expandNode', 'MSC');
        }

        $toggleUrlEvent = new AddToUrlEvent(
            'ptg=' . $model->getId() . '&amp;provider=' . $model->getProviderName()
        );
        $this->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $toggleUrlEvent);

        $toggleData = [
            'url'          => \html_entity_decode($toggleUrlEvent->getUrl()),
            'toggler'      => $toggleID,
            'id'           => $model->getId(),
            'providerName' => $model->getProviderName(),
            'level'        => $model->getMeta('dc_gen_tv_level'),
            'mode'         => 6
        ];

        $toggleScript = \sprintf(
            'Backend.getScrollOffset(); return BackendGeneral.loadSubTree(this, %s);',
            \htmlspecialchars(\json_encode($toggleData, JSON_FORCE_OBJECT))
        );

        $this
            ->addToTemplate('theme', Backend::getTheme(), $template)
            ->addToTemplate('environment', $this->getEnvironment(), $template)
            ->addToTemplate('objModel', $model, $template)
            ->addToTemplate('select', $this->isSelectModeActive(), $template)
            ->addToTemplate('intMode', 6, $template)
            ->addToTemplate('strToggleID', $toggleID, $template)
            ->addToTemplate('toggleUrl', $toggleUrlEvent->getUrl(), $template)
            ->addToTemplate('toggleTitle', $toggleTitle, $template)
            ->addToTemplate('toggleScript', $toggleScript, $template)
            ->addToTemplate('selectContainer', $this->getSelectContainer(), $template);

        return $template->parse();
    }

    /**
     * Render the tree view and return it as string.
     *
     * @param CollectionInterface $collection The collection to iterate over.
     * @param string              $treeClass  The class to use for the tree.
     *
     * @return string
     */
    protected function generateTreeView($collection, $treeClass)
    {
        $content = [];

        // Generate buttons - only if not in select mode!
        if (!$this->isSelectModeActive()) {
            (new ButtonRenderer($this->environment))->renderButtonsForCollection($collection);
        }

        foreach ($collection as $model) {
            /** @var ModelInterface $model */

            $toggleID = $model->getProviderName() . '_' . $treeClass . '_' . $model->getId();

            $content[] = $this->parseModel($model, $toggleID);

            if ($model->getMeta($model::HAS_CHILDREN) && $model->getMeta($model::SHOW_CHILDREN)) {
                $template = $this->getTemplate('dcbe_general_treeview_child');
                $subHtml  = '';

                foreach ($model->getMeta($model::CHILD_COLLECTIONS) as $childCollection) {
                    $subHtml .= $this->generateTreeView($childCollection, $treeClass);
                }

                $this
                    ->addToTemplate('select', $this->isSelectModeActive(), $template)
                    ->addToTemplate('objParentModel', $model, $template)
                    ->addToTemplate('strToggleID', $toggleID, $template)
                    ->addToTemplate('strHTML', $subHtml, $template)
                    ->addToTemplate('strTable', $model->getProviderName(), $template);

                $content[] = $template->parse();
            }
        }

        return \implode("\n", $content);
    }

    /**
     * Render the paste button for pasting into the root of the tree.
     *
     * @param GetPasteRootButtonEvent $event The event that has been triggered.
     *
     * @return string
     */
    public static function renderPasteRootButton(GetPasteRootButtonEvent $event)
    {
        if (null !== $event->getHtml()) {
            return $event->getHtml();
        }
        $environment = $event->getEnvironment();
        $label       = $environment->getTranslator()->translate(
            'pasteinto.0',
            $environment->getDataDefinition()->getName()
        );
        if ($event->isPasteDisabled()) {
            /** @var GenerateHtmlEvent $imageEvent */
            $imageEvent = $environment->getEventDispatcher()->dispatch(
                ContaoEvents::IMAGE_GET_HTML,
                new GenerateHtmlEvent(
                    'pasteinto_.svg',
                    $label,
                    'class="blink"'
                )
            );

            return $imageEvent->getHtml();
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $environment->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'pasteinto.svg',
                $label,
                'class="blink"'
            )
        );

        return \sprintf(
            ' <a href="%s" title="%s" %s>%s</a>',
            $event->getHref(),
            StringUtil::specialchars($label),
            'onclick="Backend.getScrollOffset()"',
            $imageEvent->getHtml()
        );
    }

    /**
     * Render the tree view.
     *
     * @param CollectionInterface $collection The collection of items.
     *
     * @return string
     */
    protected function viewTree($collection)
    {
        $definition      = $this->getDataDefinition();
        $listing         = $this->getViewSection()->getListingConfig();
        $basicDefinition = $definition->getBasicDefinition();
        $environment     = $this->getEnvironment();
        $dispatcher      = $environment->getEventDispatcher();

        // Init some Vars
        switch (6) {
            case 6:
                $treeClass = 'tree_xtnd';
                break;

            default:
                $treeClass = 'tree';
        }

        // Label + Icon.
        if (null === $listing->getRootLabel()) {
            $labelText = 'DC General Tree BackendView Ultimate';
        } else {
            $labelText = $listing->getRootLabel();
        }

        if (null === $listing->getRootIcon()) {
            $labelIcon = 'pagemounts.svg';
        } else {
            $labelIcon = $listing->getRootIcon();
        }

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        if ($parentDataProviderName = $basicDefinition->getParentDataProvider()) {
            $filter->andParentIsFromProvider($parentDataProviderName);
        } else {
            $filter->andHasNoParent();
        }

        // Root paste into.
        if ($environment->getClipboard()->isNotEmpty($filter)) {
            /** @var AddToUrlEvent $urlEvent */
            $urlEvent = $dispatcher->dispatch(
                ContaoEvents::BACKEND_ADD_TO_URL,
                new AddToUrlEvent(
                    \sprintf(
                        'act=paste&amp;into=%s::0',
                        $definition->getName()
                    )
                )
            );

            $buttonEvent = new GetPasteRootButtonEvent($this->getEnvironment());
            $buttonEvent
                ->setHref($urlEvent->getUrl())
                ->setPasteDisabled(false);

            $dispatcher->dispatch($buttonEvent::NAME, $buttonEvent);

            $rootPasteInto = static::renderPasteRootButton($buttonEvent);
        } else {
            $rootPasteInto = '';
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, new GenerateHtmlEvent($labelIcon));

        // Build template.
        $template = $this->getTemplate('dcbe_general_treeview');
        $template
            ->set('treeClass', 'tl_' . $treeClass)
            ->set('tableName', $definition->getName())
            ->set('strLabelIcon', $imageEvent->getHtml())
            ->set('strLabelText', $labelText)
            ->set('strHTML', $this->generateTreeView($collection, $treeClass))
            ->set('strRootPasteinto', $rootPasteInto)
            ->set('select', $this->isSelectModeActive())
            ->set('selectButtons', $this->getSelectButtons())
            ->set('intMode', 6);

        $this->formActionForSelect($template);

        // Add breadcrumb, if we have one.
        if (null !== ($breadcrumb = $this->breadcrumb())) {
            $template->set('breadcrumb', $breadcrumb);
        }

        return $template->parse();
    }

    /**
     * Add the form action url for input parameter action is select.
     *
     * @param ContaoBackendViewTemplate $template The template.
     *
     * @return void
     */
    protected function formActionForSelect(ContaoBackendViewTemplate $template)
    {
        $environment = $this->getEnvironment();
        if (!$template->get('select')
            || ('select' !== $environment->getInputProvider()->getParameter('act'))
        ) {
            return;
        }

        $actionUrlEvent = new AddToUrlEvent('select=properties');
        $environment->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $actionUrlEvent);

        $template->set('action', $actionUrlEvent->getUrl());
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\TreeEnforcingListener
     *
     * @see \ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\TreeEnforcingListener
     *
     * @return void
     */
    public function enforceModelRelationship($model)
    {
        // Fallback implementation.
        $listener = new TreeEnforcingListener();
        $listener->process(new EnforceModelRelationshipEvent($this->getEnvironment(), $model));
    }

    /**
     * {@inheritdoc}
     */
    public function showAll(Action $action)
    {
        $environment = $this->getEnvironment();
        if ($environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        $this->handleNodeStateChanges();

        $collection = $this->loadCollection();
        $content    = [];

        $viewEvent = new ViewEvent($this->environment, $action, DcGeneralViews::CLIPBOARD, []);
        $environment->getEventDispatcher()->dispatch(DcGeneralEvents::VIEW, $viewEvent);

        // A list with ignored panels.
        $ignoredPanels = [
            LimitElementInterface::class,
            SortElementInterface::class
        ];

        $content['language']  = $this->languageSwitcher($this->environment);
        $content['panel']     = $this->panel($ignoredPanels);
        $content['buttons']   = $this->generateHeaderButtons();
        $content['clipboard'] = $viewEvent->getResponse();
        $content['body']      = $this->viewTree($collection);

        return \implode("\n", $content);
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
            ->set('submit', $this->environment->getTranslator()->translate('MSC.showSelected'))
            ->set('REQUEST_TOKEN', REQUEST_TOKEN)
            ->parse();
    }

    /**
     * Handle an ajax call.
     *
     * @return void
     *
     * @throws ResponseException Throws a response exception.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function handleAjaxCall()
    {
        $input = $this->getEnvironment()->getInputProvider();

        if ('DcGeneralLoadSubTree' !== $input->getValue('action')) {
            parent::handleAjaxCall();

            return;
        }

        $response = new Response(
            $this->ajaxTreeView(
                $input->getValue('id'),
                $input->getValue('providerName'),
                $input->getValue('level')
            )
        );

        throw new ResponseException($response);
    }

    /**
     * Handle ajax rendering of a sub tree.
     *
     * @param string $rootId       Id of the root node.
     * @param string $providerName Name of the data provider where the model is contained within.
     * @param int    $level        Level depth of the model in the whole tree.
     *
     * @return string
     */
    public function ajaxTreeView($rootId, $providerName, $level)
    {
        $this->toggleModel($providerName, $rootId);

        $collection = $this->loadCollection($rootId, $level, $providerName);

        $treeClass = '';
        switch (6) {
            case 5:
                $treeClass = 'tree';
                break;

            case 6:
                $treeClass = 'tree_xtnd';
                break;

            default:
        }

        return $this->generateTreeView($collection, $treeClass);
    }

    /**
     * Get the the container of selections.
     *
     * @return array
     */
    private function getSelectContainer()
    {
        $environment   = $this->getEnvironment();
        $inputProvider = $environment->getInputProvider();

        $sessionName = $environment->getDataDefinition()->getName() . '.' . $inputProvider->getParameter('mode');
        if (!$environment->getSessionStorage()->has($sessionName)) {
            return [];
        }

        $selectAction = $inputProvider->getParameter('select');
        if (!$selectAction) {
            return [];
        }

        $session = $environment->getSessionStorage()->get($sessionName);
        if (!\array_key_exists($selectAction, $session)) {
            return [];
        }

        return $session[$selectAction];
    }
}

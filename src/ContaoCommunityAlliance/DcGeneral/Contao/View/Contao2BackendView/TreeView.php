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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\TreeCollector;
use ContaoCommunityAlliance\DcGeneral\Controller\TreeNodeStates;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\TreeEnforcingListener;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

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
        return $this->getEnvironment()->getDataDefinition()->getName() . '_tree';
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

        if (!is_array($openElements)) {
            $openElements = array();
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
            if ($modelId == 'all') {
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
     *
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
     *
     * @param int    $intLevel     The current level in the tree (of the optional root element).
     *
     * @param string $providerName The data provider from which the optional root element shall be taken from.
     *
     * @return CollectionInterface
     */
    public function loadCollection($rootId = null, $intLevel = 0, $providerName = null)
    {
        $environment   = $this->getEnvironment();
        $dataDriver    = $environment->getDataProvider($providerName);
        $realProvider  = $dataDriver->getEmptyModel()->getProviderName();
        $collector     = new TreeCollector(
            $environment,
            $this->getPanel(),
            $this->getViewSection()->getListingConfig()->getDefaultSortingFields(),
            $this->getTreeNodeStates()
        );
        $objCollection = $rootId
            ? $collector->getTreeCollectionRecursive($rootId, $intLevel, $realProvider)
            : $collector->getChildrenOf(
                $realProvider,
                $intLevel,
                $environment->getInputProvider()->hasParameter('pid') ? $this->loadParentModel() : null
            );

        if ($rootId) {
            $objTreeData = $dataDriver->getEmptyCollection();
            $objModel    = $objCollection->get(0);

            if (!$objModel->getMeta(ModelInterface::HAS_CHILDREN)) {
                return $objTreeData;
            }

            foreach ($objModel->getMeta($objModel::CHILD_COLLECTIONS) as $objCollection) {
                foreach ($objCollection as $objSubModel) {
                    $objTreeData->push($objSubModel);
                }
            }

            return $objTreeData;
        }

        return $objCollection;
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

        if (!($objParentProvider = $environment->getDataProvider($pid->getDataProviderName())
        )
        ) {
            throw new DcGeneralRuntimeException(
                'TreeView needs a proper parent data provider defined, somehow none is defined?',
                1
            );
        }

        $objParentItem = $environment->getController()->fetchModelFromProvider($pid);

        if (!$objParentItem) {
            // No parent item found, might have been deleted.
            // We transparently create it for our filter to be able to filter to nothing.
            $objParentItem = $objParentProvider->getEmptyModel();
            $objParentItem->setId($parentId);
        }

        return $objParentItem;
    }

    /**
     * Calculate the fields needed by a tree label for the given data provider name.
     *
     * @param string $strTable The name of the data provider.
     *
     * @return array
     */
    protected function calcLabelFields($strTable)
    {
        return $this->getViewSection()->getListingConfig()->getLabelFormatter($strTable)->getPropertyNames();
    }

    /**
     * Render a given model.
     *
     * @param ModelInterface $objModel    The model to render.
     *
     * @param string         $strToggleID The id of the toggler.
     *
     * @return string
     */
    protected function parseModel($objModel, $strToggleID)
    {
        $event = new FormatModelLabelEvent($this->environment, $objModel);
        $this->environment->getEventDispatcher()->dispatch(
            DcGeneralEvents::FORMAT_MODEL_LABEL,
            $event
        );

        $objModel->setMeta($objModel::LABEL_VALUE, $event->getLabel());

        $objTemplate = $this->getTemplate('dcbe_general_treeview_entry');

        if ($objModel->getMeta($objModel::SHOW_CHILDREN)) {
            $toggleTitle = $this->getEnvironment()->getTranslator()->translate('collapseNode', 'MSC');
        } else {
            $toggleTitle = $this->getEnvironment()->getTranslator()->translate('expandNode', 'MSC');
        }

        $toggleScript = sprintf(
            'Backend.getScrollOffset(); return BackendGeneral.loadSubTree(this, ' .
            '{\'toggler\':\'%s\', \'id\':\'%s\', \'providerName\':\'%s\', \'level\':\'%s\', \'mode\':\'%s\'});',
            $strToggleID,
            $objModel->getId(),
            $objModel->getProviderName(),
            $objModel->getMeta('dc_gen_tv_level'),
            6
        );

        $toggleUrlEvent = new AddToUrlEvent(
            'ptg=' . $objModel->getId() . '&amp;provider=' . $objModel->getProviderName()
        );
        $this->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $toggleUrlEvent);

        $this
            ->addToTemplate('environment', $this->getEnvironment(), $objTemplate)
            ->addToTemplate('objModel', $objModel, $objTemplate)
            ->addToTemplate('select', $this->isSelectModeActive(), $objTemplate)
            ->addToTemplate('intMode', 6, $objTemplate)
            ->addToTemplate('strToggleID', $strToggleID, $objTemplate)
            ->addToTemplate(
                'toggleUrl',
                $toggleUrlEvent->getUrl(),
                $objTemplate
            )
            ->addToTemplate('toggleTitle', $toggleTitle, $objTemplate)
            ->addToTemplate('toggleScript', $toggleScript, $objTemplate)
            ->addToTemplate('selectContainer', $this->getSelectContainer(), $objTemplate);

        return $objTemplate->parse();
    }

    /**
     * Render the tree view and return it as string.
     *
     * @param CollectionInterface $objCollection The collection to iterate over.
     *
     * @param string              $treeClass     The class to use for the tree.
     *
     * @return string
     */
    protected function generateTreeView($objCollection, $treeClass)
    {
        $arrHtml = array();

        // Generate buttons - only if not in select mode!
        if (!$this->isSelectModeActive()) {
            $buttonRenderer = new ButtonRenderer($this->environment);
            $buttonRenderer->renderButtonsForCollection($objCollection);
        }

        foreach ($objCollection as $objModel) {
            /** @var ModelInterface $objModel */

            $strToggleID = $objModel->getProviderName() . '_' . $treeClass . '_' . $objModel->getId();

            $arrHtml[] = $this->parseModel($objModel, $strToggleID);

            if ($objModel->getMeta($objModel::HAS_CHILDREN) && $objModel->getMeta($objModel::SHOW_CHILDREN)) {
                $objTemplate = $this->getTemplate('dcbe_general_treeview_child');
                $strSubHtml  = '';

                foreach ($objModel->getMeta($objModel::CHILD_COLLECTIONS) as $objCollection) {
                    $strSubHtml .= $this->generateTreeView($objCollection, $treeClass);
                }

                $this
                    ->addToTemplate('select', $this->isSelectModeActive(), $objTemplate)
                    ->addToTemplate('objParentModel', $objModel, $objTemplate)
                    ->addToTemplate('strToggleID', $strToggleID, $objTemplate)
                    ->addToTemplate('strHTML', $strSubHtml, $objTemplate)
                    ->addToTemplate('strTable', $objModel->getProviderName(), $objTemplate);

                $arrHtml[] = $objTemplate->parse();
            }
        }

        return implode("\n", $arrHtml);
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
        if ($event->getHtml() !== null) {
            return $event->getHtml();
        }
        $environment = $event->getEnvironment();
        $strLabel    = $environment->getTranslator()->translate(
            'pasteinto.0',
            $environment->getDataDefinition()->getName()
        );
        if ($event->isPasteDisabled()) {
            /** @var GenerateHtmlEvent $imageEvent */
            $imageEvent = $event->getEnvironment()->getEventDispatcher()->dispatch(
                ContaoEvents::IMAGE_GET_HTML,
                new GenerateHtmlEvent(
                    'pasteinto_.gif',
                    $strLabel,
                    'class="blink"'
                )
            );

            return $imageEvent->getHtml();
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $event->getEnvironment()->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'pasteinto.gif',
                $strLabel,
                'class="blink"'
            )
        );

        return sprintf(
            ' <a href="%s" title="%s" %s>%s</a>',
            $event->getHref(),
            specialchars($strLabel),
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
        if (strlen($listing->getRootLabel()) == 0) {
            $strLabelText = 'DC General Tree BackendView Ultimate';
        } else {
            $strLabelText = $listing->getRootLabel();
        }

        if (strlen($listing->getRootIcon()) == 0) {
            $strLabelIcon = 'pagemounts.gif';
        } else {
            $strLabelIcon = $listing->getRootIcon();
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
                    sprintf(
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

            $strRootPasteInto = $this->renderPasteRootButton($buttonEvent);
        } else {
            $strRootPasteInto = '';
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, new GenerateHtmlEvent($strLabelIcon));

        // Build template.
        $objTemplate                   = $this->getTemplate('dcbe_general_treeview');
        $objTemplate->treeClass        = 'tl_' . $treeClass;
        $objTemplate->tableName        = $definition->getName();
        $objTemplate->strLabelIcon     = $imageEvent->getHtml();
        $objTemplate->strLabelText     = $strLabelText;
        $objTemplate->strHTML          = $this->generateTreeView($collection, $treeClass);
        $objTemplate->strRootPasteinto = $strRootPasteInto;
        $objTemplate->select           = $this->isSelectModeActive();
        $objTemplate->selectButtons    = $this->getSelectButtons();
        $objTemplate->intMode          = 6;

        $this->formActionForSelect($objTemplate);

        // Add breadcrumb, if we have one.
        $strBreadcrumb = $this->breadcrumb();
        if ($strBreadcrumb != null) {
            $objTemplate->breadcrumb = $strBreadcrumb;
        }

        return $objTemplate->parse();
    }

    /**
     * Add the form action url for input parameter action is select.
     *
     * @param ContaoBackendViewTemplate $objTemplate The template.
     *
     * @return void
     */
    protected function formActionForSelect(ContaoBackendViewTemplate $objTemplate)
    {
        if ($this->getEnvironment()->getInputProvider()->getParameter('act') !== 'select'
            || !$objTemplate->select
        ) {
            return;
        }

        $actionUrlEvent = new AddToUrlEvent('select=properties');
        $this->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $actionUrlEvent);

        $objTemplate->action = $actionUrlEvent->getUrl();
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\TreeEnforcingListener
     *
     * @see        \ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\TreeEnforcingListener
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
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        $this->handleNodeStateChanges();

        $collection = $this->loadCollection();
        $arrReturn  = array();

        $viewEvent = new ViewEvent($this->environment, $action, DcGeneralViews::CLIPBOARD, array());
        $this->environment->getEventDispatcher()->dispatch(DcGeneralEvents::VIEW, $viewEvent);

        // A list with ignored panels.
        $arrIgnoredPanels = array(
            '\ContaoCommunityAlliance\DcGeneral\Panel\LimitElementInterface',
            '\ContaoCommunityAlliance\DcGeneral\Panel\SortElementInterface'
        );

        $arrReturn['panel']     = $this->panel($arrIgnoredPanels);
        $arrReturn['buttons']   = $this->generateHeaderButtons('tl_buttons');
        $arrReturn['clipboard'] = $viewEvent->getResponse();
        $arrReturn['body']      = $this->viewTree($collection);

        return implode("\n", $arrReturn);
    }

    /**
     * Handle an ajax call.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function handleAjaxCall()
    {
        $input = $this->getEnvironment()->getInputProvider();

        switch ($input->getValue('action')) {
            case 'DcGeneralLoadSubTree':
                header('Content-Type: text/html; charset=' . \Config::get('characterSet'));
                echo $this->ajaxTreeView(
                    $input->getValue('id'),
                    $input->getValue('providerName'),
                    $input->getValue('level')
                );
                exit;

            default:
        }

        parent::handleAjaxCall();
    }

    /**
     * Handle ajax rendering of a sub tree.
     *
     * @param string $rootId       Id of the root node.
     *
     * @param string $providerName Name of the data provider where the model is contained within.
     *
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

        $strHtml = $this->generateTreeView($collection, $treeClass);

        return $strHtml;
    }

    /**
     * Get the the container of selections.
     *
     * @return array
     */
    private function getSelectContainer()
    {
        $inputProvider  = $this->getEnvironment()->getInputProvider();
        $dataDefinition = $this->getEnvironment()->getDataDefinition();

        $sessionName = $dataDefinition->getName() . '.' . $inputProvider->getParameter('mode');
        if (!$this->getEnvironment()->getSessionStorage()->has($sessionName)) {
            return array();
        }

        $selectAction = $inputProvider->getParameter('select');
        if (!$selectAction) {
            return array();
        }

        $session = $this->getEnvironment()->getSessionStorage()->get($sessionName);
        if (!array_key_exists($selectAction, $session)) {
            return array();
        }

        return $session[$selectAction];
    }
}

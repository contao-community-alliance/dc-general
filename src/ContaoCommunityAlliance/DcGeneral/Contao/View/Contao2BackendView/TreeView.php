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
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DCGE;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class TreeView.
 *
 * Implementation for tree displaying.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView
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
     * Retrieve the ids of all tree nodes that are expanded.
     *
     * @return array
     */
    protected function getOpenElements()
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();

        $openElements = $inputProvider->getPersistentValue($this->getToggleId());

        if (!is_array($openElements)) {
            $openElements = array();
            $inputProvider->setPersistentValue($this->getToggleId(), $openElements);
        }

        // Check if the open/close all is active.
        if ($inputProvider->getParameter('ptg') == 'all') {
            $openElements = array();
            if (!array_key_exists('all', $openElements)) {
                $openElements        = array();
                $openElements['all'] = 1;
            }

            // Save in session and reload.
            $inputProvider->setPersistentValue($this->getToggleId(), $openElements);

            $this->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
        }

        return $openElements;
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
    protected function toggleModel($providerName, $modelId)
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();
        $openElements  = $this->getOpenElements();

        if (!isset($openElements[$providerName])) {
            $openElements[$providerName] = array();
        }

        if (!isset($openElements[$providerName][$modelId])) {
            $openElements[$providerName][$modelId] = 1;
        } else {
            $openElements[$providerName][$modelId] = !$openElements[$providerName][$modelId];
        }

        $inputProvider->setPersistentValue($this->getToggleId(), $openElements);
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
        $openModels = $this->getOpenElements();

        if (isset($openModels['all']) && ($openModels['all'] == 1)) {
            return true;
        }

        if (isset($openModels[$model->getProviderName()][$model->getID()])
            && ($openModels[$model->getProviderName()][$model->getID()])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Load the collection of child items and the parent item for the currently selected parent item.
     *
     * @param mixed $rootId       The root element (or null to fetch everything).
     *
     * @param int   $intLevel     The current level in the tree (of the optional root element).
     *
     * @param null  $providerName The data provider from which the optional root element shall be taken from.
     *
     * @return CollectionInterface
     */
    public function loadCollection($rootId = null, $intLevel = 0, $providerName = null)
    {
        $environment = $this->getEnvironment();
        $dataDriver  = $environment->getDataProvider($providerName);

        $objCollection = $this->getTreeCollectionRecursive($rootId, $intLevel, $providerName);

        if ($rootId) {
            $objTreeData = $dataDriver->getEmptyCollection();
            $objModel    = $objCollection->get(0);

            if (!$objModel->getMeta($objModel::HAS_CHILDREN)) {
                return $objTreeData;
            }

            foreach ($objModel->getMeta(DCGE::TREE_VIEW_CHILD_COLLECTION) as $objCollection) {
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

        $pid = IdSerializer::fromSerialized($parentId);

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
            // TODO: shall we rather bail with "parent not found"?
            $objParentItem = $objParentProvider->getEmptyModel();
            $objParentItem->setID($parentId);
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
     * Check the state of a model and set the metadata accordingly.
     *
     * @param ModelInterface $model The model of which the state shall be checked of.
     *
     * @param int            $level The tree level the model is contained within.
     *
     * @return void
     */
    protected function determineModelState(ModelInterface $model, $level)
    {
        $model->setMeta(DCGE::TREE_VIEW_LEVEL, $level);
        $model->setMeta(DCGE::TREE_VIEW_IS_OPEN, $this->isModelOpen($model));
    }

    /**
     * This "renders" a model for tree view.
     *
     * @param ModelInterface $objModel     The model to render.
     *
     * @param int            $intLevel     The current level in the tree hierarchy.
     *
     * @param array          $arrSubTables The names of data providers that shall be rendered "below" this item.
     *
     * @return void
     */
    protected function treeWalkModel(ModelInterface $objModel, $intLevel, $arrSubTables = array())
    {
        $environment   = $this->getEnvironment();
        $relationships = $environment->getDataDefinition()->getModelRelationshipDefinition();
        $blnHasChild   = false;

        $this->determineModelState($objModel, $intLevel);

        $providerName = $objModel->getProviderName();
        $mySubTables  = array();
        foreach ($relationships->getChildConditions($providerName) as $condition) {
            $mySubTables[] = $condition->getDestinationName();
        }
        $arrChildCollections = array();
        foreach ($arrSubTables as $strSubTable) {
            // Evaluate the child filter for this item.
            $arrChildFilter = $relationships->getChildCondition($providerName, $strSubTable);

            // If we do not know how to render this table within here, continue with the next one.
            if (!$arrChildFilter) {
                continue;
            }

            // Create a new Config and fetch the children from the child provider.
            $dataProvider   = $environment->getDataProvider($strSubTable);
            $objChildConfig = $dataProvider->getEmptyConfig();
            $objChildConfig->setFilter($arrChildFilter->getFilter($objModel));

            // TODO: hardcoded sorting... NOT GOOD!
            $objChildConfig->setSorting(array('sorting' => 'ASC'));
            $objChildCollection = $dataProvider->fetchAll($objChildConfig);

            $blnHasChild = ($objChildCollection->length() > 0);

            // Speed up - we may exit if we have at least one child but the parenting model is collapsed.
            if ($blnHasChild && !$objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN)) {
                break;
            } elseif ($blnHasChild) {
                foreach ($objChildCollection as $objChildModel) {
                    // Let the child know about it's parent.
                    $objModel->setMeta($objModel::PARENT_ID, $objModel->getID());
                    $objModel->setMeta($objModel::PARENT_PROVIDER_NAME, $providerName);

                    $this->treeWalkModel($objChildModel, ($intLevel + 1), $mySubTables);
                }
                $arrChildCollections[] = $objChildCollection;

                // Speed up, if collapsed, one item is enough to break as we have some children.
                if (!$objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN)) {
                    break;
                }
            }
        }

        // If expanded, store children.
        if ($objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN) && count($arrChildCollections) != 0) {
            $objModel->setMeta(DCGE::TREE_VIEW_CHILD_COLLECTION, $arrChildCollections);
        }

        $objModel->setMeta($objModel::HAS_CHILDREN, $blnHasChild);
    }

    /**
     * Add the parent filtering to the given data config if any defined.
     *
     * @param ConfigInterface $config The data config.
     *
     * @return void
     */
    protected function addParentFilter($config)
    {
        $environment     = $this->getEnvironment();
        $definition      = $environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        $relationships   = $definition->getModelRelationshipDefinition();

        if (!$basicDefinition->getParentDataProvider()) {
            return;
        }

        // Apply parent filtering, do this only for root elements.
        if ($objParentCondition = $relationships->getChildCondition(
            $basicDefinition->getParentDataProvider(),
            $basicDefinition->getRootDataProvider()
        )
        ) {
            $arrBaseFilter = $config->getFilter();
            $arrFilter     = $objParentCondition->getFilter($this->loadParentModel());

            if ($arrBaseFilter) {
                $arrFilter = array_merge($arrBaseFilter, $arrFilter);
            }

            $config->setFilter($arrFilter);
        }
    }

    /**
     * Recursively retrieve a collection of all complete node hierarchy.
     *
     * @param array  $rootId       The ids of the root node.
     *
     * @param int    $intLevel     The level the items are residing on.
     *
     * @param string $providerName The data provider from which the root element originates from.
     *
     * @return CollectionInterface
     */
    public function getTreeCollectionRecursive($rootId, $intLevel = 0, $providerName = null)
    {
        $environment      = $this->getEnvironment();
        $definition       = $environment->getDataDefinition();
        $dataProvider     = $environment->getDataProvider($providerName);
        $objTableTreeData = $dataProvider->getEmptyCollection();
        $objRootConfig    = $environment->getController()->getBaseConfig();
        $relationships    = $definition->getModelRelationshipDefinition();
        $backendView      = $this->getViewSection();

        /** @var Contao2BackendViewDefinitionInterface $backendView */
        $listingConfig = $backendView->getListingConfig();
        // Initialize sorting.
        $objRootConfig->setSorting($listingConfig->getDefaultSortingFields());

        $this->getPanel()->initialize($objRootConfig);

        if (!$rootId) {
            $objRootCondition = $relationships->getRootCondition();

            if ($objRootCondition) {
                $arrBaseFilter = $objRootConfig->getFilter();
                $arrFilter     = $objRootCondition->getFilterArray();

                if ($arrBaseFilter) {
                    $arrFilter = array_merge($arrBaseFilter, $arrFilter);
                }

                $objRootConfig->setFilter($arrFilter);
            }

            $this->addParentFilter($objRootConfig);

            // Fetch all root elements.
            $objRootCollection = $dataProvider->fetchAll($objRootConfig);

            if ($objRootCollection->length() > 0) {
                $mySubTables = array();
                foreach ($relationships->getChildConditions(
                    $objRootCollection->get(0)->getProviderName()
                ) as $condition) {
                    $mySubTables[] = $condition->getDestinationName();
                }

                foreach ($objRootCollection as $objRootModel) {
                    /** @var ModelInterface $objRootModel */
                    $objTableTreeData->push($objRootModel);
                    $this->treeWalkModel($objRootModel, $intLevel, $mySubTables);
                }
            }

            return $objTableTreeData;
        }

        $objRootConfig->setId($rootId);
        // Fetch root element.
        $objRootModel = $dataProvider->fetch($objRootConfig);

        $mySubTables = array();
        foreach ($relationships->getChildConditions($objRootModel->getProviderName()) as $condition) {
            $mySubTables[] = $condition->getDestinationName();
        }

        $this->treeWalkModel($objRootModel, $intLevel, $mySubTables);
        $objRootCollection = $dataProvider->getEmptyCollection();
        $objRootCollection->push($objRootModel);

        return $objRootCollection;
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
        $objModel->setMeta($objModel::OPERATION_BUTTONS, $this->generateButtons($objModel));
        $objModel->setMeta($objModel::LABEL_VALUE, $this->formatModel($objModel));

        $objTemplate = $this->getTemplate('dcbe_general_treeview_entry');

        if ($objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN)) {
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
            // FIXME: add real tree mode here - intMode.
            6
        );

        $toggleUrlEvent = new AddToUrlEvent(
            'ptg=' . $objModel->getId() . '&amp;provider=' . $objModel->getProviderName()
        );
        $this->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $toggleUrlEvent);

        $this
            ->addToTemplate('environment', $this->getEnvironment(), $objTemplate)
            ->addToTemplate('objModel', $objModel, $objTemplate)
            // FIXME: add real tree mode here.
            ->addToTemplate('intMode', 6, $objTemplate)
            ->addToTemplate('strToggleID', $strToggleID, $objTemplate)
            ->addToTemplate(
                'toggleUrl',
                $toggleUrlEvent->getUrl(),
                $objTemplate
            )
            ->addToTemplate('toggleTitle', $toggleTitle, $objTemplate)
            ->addToTemplate('toggleScript', $toggleScript, $objTemplate);

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

        foreach ($objCollection as $objModel) {
            /** @var ModelInterface $objModel */

            $strToggleID = $objModel->getProviderName() . '_' . $treeClass . '_' . $objModel->getID();

            $arrHtml[] = $this->parseModel($objModel, $strToggleID);

            if ($objModel->getMeta($objModel::HAS_CHILDREN) && $objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN)) {
                $objTemplate = $this->getTemplate('dcbe_general_treeview_child');
                $strSubHtml  = '';

                foreach ($objModel->getMeta(DCGE::TREE_VIEW_CHILD_COLLECTION) as $objCollection) {
                    $strSubHtml .= $this->generateTreeView($objCollection, $treeClass);
                }

                $this
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
        $definition  = $this->getDataDefinition();
        $listing     = $this->getViewSection()->getListingConfig();
        $environment = $this->getEnvironment();
        $dispatcher  = $environment->getEventDispatcher();

        // Init some Vars
        switch (6 /*$definition->getSortingMode()*/) {
            case 6:
                $treeClass = 'tree_xtnd';
                break;
            // case 5:
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

        // Root paste into.
        if ($environment->getClipboard()->isNotEmpty()) {
            $objClipboard = $environment->getClipboard();
            /** @var AddToUrlEvent $urlEvent */
            $urlEvent = $dispatcher->dispatch(
                ContaoEvents::BACKEND_ADD_TO_URL,
                new AddToUrlEvent(
                    sprintf(
                        'act=paste&amp;into=%s::0&amp;children=%s',
                        $definition->getName(),
                        $objClipboard->getContainedIds(),
                        implode(',', $objClipboard->getCircularIds())
                    )
                )
            );

            $buttonEvent = new GetPasteRootButtonEvent($this->getEnvironment());
            $buttonEvent
                ->setHref($urlEvent->getUrl())
                ->setPasteDisabled(false);

            $dispatcher->dispatch(sprintf('%s[%s]', $buttonEvent::NAME, $definition->getName()), $buttonEvent);
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
        // FIXME: set real tree mode here.
        $objTemplate->intMode = 6;

        // Add breadcrumb, if we have one.
        $strBreadcrumb = $this->breadcrumb();
        if ($strBreadcrumb != null) {
            $objTemplate->breadcrumb = $strBreadcrumb;
        }

        return $objTemplate->parse();
    }

    /**
     * {@inheritDoc}
     */
    public function enforceModelRelationship($model)
    {
        $environment = $this->getEnvironment();
        $input       = $environment->getInputProvider();
        $controller  = $environment->getController();

        if ($input->hasParameter('into')) {
            $into = IdSerializer::fromSerialized($input->getParameter('into'));

            // If we have a null, it means insert into the tree root.
            if ($into->getId() == 0) {
                $controller->setRootModel($model);
            } else {
                $parent = $controller->fetchModelFromProvider($into);
                $controller->setParent($model, $parent);
            }
        } elseif ($input->hasParameter('after')) {
            $after   = IdSerializer::fromSerialized($input->getParameter('after'));
            $sibling = $controller->fetchModelFromProvider($after);

            if (!$sibling || $controller->isRootModel($sibling)) {
                $controller->setRootModel($model);
            } else {
                $parent = $controller->searchParentOf($sibling);
                $controller->setParent($model, $parent);
            }
        }

        // Also enforce the parent condition of the parent provider (if any).
        if ($input->hasParameter('pid')) {
            $parent = $controller->fetchModelFromProvider($input->getParameter('pid'));
            $controller->setParent($model, $parent);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function paste()
    {
        $environment = $this->getEnvironment();
        $input       = $environment->getInputProvider();
        $clipboard   = $environment->getClipboard();

        // Push an empty model into the clipboard.
        if ($input->getParameter('mode') === 'create') {
            $clipboard->create(null);
        }

        // If destination is known, perform normal paste.
        if ($input->hasParameter('after') || $input->hasParameter('into')) {
            if ($clipboard->isCreate()) {
                return parent::create();
            }

            parent::paste();
        }

        // Show the target selection tree otherwise.
        return $this->showAll();
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

        $input = $this->getEnvironment()->getInputProvider();
        if (($modelId = $input->hasParameter('ptg')) && ($providerName = $input->hasParameter('provider'))) {
            $this->toggleModel($providerName, $modelId);
            $this->redirectHome();
        }

        $this->checkClipboard();

        $collection = $this->loadCollection();
        $arrReturn  = array();

        /*
            if ($this->getDataDefinition()->getSortingMode() == 5)
            {
                $arrReturn['panel'] = $this->panel();
            }
        */

        // A list with ignored panels.
        $arrIgnoredPanels = array
        (
            '\ContaoCommunityAlliance\DcGeneral\Panel\LimitElementInterface',
            '\ContaoCommunityAlliance\DcGeneral\Panel\SortElementInterface'
        );

        $arrReturn['panel']   = $this->panel($arrIgnoredPanels);
        $arrReturn['buttons'] = $this->generateHeaderButtons('tl_buttons_a');
        $arrReturn['body']    = $this->viewTree($collection);

        return implode("\n", $arrReturn);
    }

    /**
     * Handle an ajax call.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handleAjaxCall()
    {
        $input = $this->getEnvironment()->getInputProvider();

        switch ($input->getValue('action')) {
            case 'DcGeneralLoadSubTree':
                header('Content-Type: text/html; charset=' . $GLOBALS['TL_CONFIG']['characterSet']);
                echo $this->ajaxTreeView(
                    $input->getValue('id'),
                    $input->getValue('providerName'),
                    $input->getValue('level'),
                    $input->getValue('mode')
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
        switch (6 /*$definition->getSortingMode()*/) {
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
     * {@inheritdoc}
     */
    public function cut()
    {

        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit();
        }

        $this->checkClipboard('cut');
        $this->redirectHome();
    }

    /**
     * {@inheritdoc}
     */
    public function copy()
    {

        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit();
        }

        $this->checkClipboard('copy');
        $this->redirectHome();
    }
}

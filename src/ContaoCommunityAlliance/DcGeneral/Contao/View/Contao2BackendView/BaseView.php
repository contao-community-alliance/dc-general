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
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\AbstractHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ShowHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\Ajax2X;
use ContaoCommunityAlliance\DcGeneral\Controller\Ajax3X;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostCreateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreCreateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\SortElementInterface;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BaseView.
 *
 * This class is the base class for the different backend view mode sub classes.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView
 */
class BaseView implements BackendViewInterface, EventSubscriberInterface
{
    /**
     * The error message format string to use when a method is not implemented.
     *
     * @var string
     */
    protected $notImplMsg =
        '<div style="text-align:center; font-weight:bold; padding:40px;">
        The function/view &quot;%s&quot; is not implemented.
        </div>';

    /**
     * The attached environment.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * The panel container in use.
     *
     * @var PanelContainerInterface
     */
    protected $panel;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DcGeneralEvents::ACTION => array('handleAction', -100),
        );
    }

    /**
     * Handle the given action.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handleAction(ActionEvent $event)
    {
        $GLOBALS['TL_CSS'][] = 'system/modules/dc-general/html/css/generalDriver.css';

        if ($event->getEnvironment()->getDataDefinition()->getName()
            !== $this->environment->getDataDefinition()->getName()
            || $event->getResponse() !== null
        ) {
            return;
        }

        $action = $event->getAction();
        $name   = $action->getName();

        switch ($name) {
            case 'copy':
            case 'copyAll':
            case 'create':
            case 'cut':
            case 'cutAll':
            case 'paste':
            case 'delete':
            case 'move':
            case 'undo':
            case 'edit':
            case 'showAll':
            case 'toggle':
                $response = call_user_func_array(
                    array($this, $name),
                    array_merge(array($action), $action->getArguments())
                );
                $event->setResponse($response);
                break;
            case 'show':
                $handler = new ShowHandler();
                $handler->handleEvent($event);
                break;

            default:
        }

        if ($this->getViewSection()->getModelCommands()->hasCommandNamed($name)) {
            $command = $this->getViewSection()->getModelCommands()->getCommandNamed($name);

            if ($command instanceof ToggleCommandInterface) {
                $this->toggle($name);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        if ($this->environment) {
            $this->environment->getEventDispatcher()->removeSubscriber($this);
        }

        $this->environment = $environment;
        $this->environment->getEventDispatcher()->addSubscriber($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Retrieve the data definition from the environment.
     *
     * @return ContainerInterface
     */
    protected function getDataDefinition()
    {
        return $this->getEnvironment()->getDataDefinition();
    }

    /**
     * Translate a string via the translator.
     *
     * @param string      $path    The path within the translation where the string can be found.
     *
     * @param string|null $section The section from which the translation shall be retrieved.
     *
     * @return string
     */
    protected function translate($path, $section = null)
    {
        return $this->getEnvironment()->getTranslator()->translate($path, $section);
    }

    /**
     * Add the value to the template.
     *
     * @param string    $name     Name of the value.
     *
     * @param mixed     $value    The value to add to the template.
     *
     * @param \Template $template The template to add the value to.
     *
     * @return BaseView
     */
    protected function addToTemplate($name, $value, $template)
    {
        $template->$name = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPanel($panelContainer)
    {
        $this->panel = $panelContainer;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPanel()
    {
        return $this->panel;
    }

    /**
     * Retrieve the view section for this view.
     *
     * @return Contao2BackendViewDefinitionInterface
     */
    protected function getViewSection()
    {
        return $this->getDataDefinition()->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
    }

    /**
     * Determine if the select mode is currently active or not.
     *
     * @return bool
     */
    protected function isSelectModeActive()
    {
        return $this->getEnvironment()->getInputProvider()->getParameter('act') == 'select';
    }

    /**
     * Return the formatted value for use in group headers as string.
     *
     * @param string         $field       The name of the property to format.
     *
     * @param ModelInterface $model       The model from which the value shall be taken from.
     *
     * @param string         $groupMode   The grouping mode in use.
     *
     * @param int            $groupLength The length of the value to use for grouping (only used when grouping mode is
     *                                    ListingConfigInterface::GROUP_CHAR).
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function formatCurrentValue($field, $model, $groupMode, $groupLength)
    {
        $property   = $this->getDataDefinition()->getPropertiesDefinition()->getProperty($field);
        $value      = $this->getReadableFieldValue($property, $model, $model->getProperty($field));
        $dispatcher = $this->getEnvironment()->getEventDispatcher();
        $propExtra  = $property->getExtra();

        // No property? Get out!
        if (!$property) {
            return '-';
        }

        $evaluation = $property->getExtra();
        $remoteNew  = '';

        if ($property->getWidgetType() == 'checkbox' && !$evaluation['multiple']) {
            $remoteNew = ($value != '') ? ucfirst($this->translate('MSC.yes')) : ucfirst($this->translate('MSC.no'));
        } elseif (false && $property->getForeignKey()) {
            // TODO: refactor foreignKey is yet undefined.
            if ($objParentModel->hasProperties()) {
                $remoteNew = $objParentModel->getProperty('value');
            }
        } elseif ($groupMode != GroupAndSortingInformationInterface::GROUP_NONE) {
            switch ($groupMode) {
                case GroupAndSortingInformationInterface::GROUP_CHAR:
                    $remoteNew = ($value != '') ? ucfirst(utf8_substr($value, 0, $groupLength ?: null)) : '-';
                    break;

                case GroupAndSortingInformationInterface::GROUP_DAY:
                    if ($value instanceof \DateTime) {
                        $value = $value->getTimestamp();
                    }

                    $event = new ParseDateEvent($value, $GLOBALS['TL_CONFIG']['dateFormat']);
                    $dispatcher->dispatch(ContaoEvents::DATE_PARSE, $event);

                    $remoteNew = ($value != '') ? $event->getResult() : '-';
                    break;

                case GroupAndSortingInformationInterface::GROUP_MONTH:
                    if ($value instanceof \DateTime) {
                        $value = $value->getTimestamp();
                    }

                    $remoteNew = ($value != '') ? date('Y-m', $value) : '-';
                    $intMonth  = ($value != '') ? (date('m', $value) - 1) : '-';

                    if ($month = $this->translate('MONTHS.' . $intMonth)) {
                        $remoteNew = ($value != '') ? $month . ' ' . date('Y', $value) : '-';
                    }
                    break;

                case GroupAndSortingInformationInterface::GROUP_YEAR:
                    if ($value instanceof \DateTime) {
                        $value = $value->getTimestamp();
                    }

                    $remoteNew = ($value != '') ? date('Y', $value) : '-';
                    break;

                default:
            }
        } else {
            if ($property->getWidgetType() == 'checkbox' && !$evaluation['multiple']) {
                $remoteNew = ($value != '') ? $field : '';
            } elseif (isset($propExtra['reference'])) {
                $remoteNew = $propExtra['reference'][$value];
            } elseif (array_is_assoc($property->getOptions())) {
                $options   = $property->getOptions();
                $remoteNew = $options[$value];
            } else {
                $remoteNew = $value;
            }

            if (is_array($remoteNew)) {
                $remoteNew = $remoteNew[0];
            }

            if (empty($remoteNew)) {
                $remoteNew = '-';
            }
        }

        $event = new GetGroupHeaderEvent($this->getEnvironment(), $model, $field, $remoteNew, $groupMode);
        $dispatcher->dispatch(
            sprintf('%s[%s]', $event::NAME, $this->getEnvironment()->getDataDefinition()->getName()),
            $event
        );
        $this->getEnvironment()->getEventDispatcher()->dispatch($event::NAME, $event);

        $remoteNew = $event->getValue();

        return $remoteNew;
    }

    /**
     * Retrieve a list of html buttons to use in the bottom panel (submit area) when in select mode.
     *
     * @return array
     */
    protected function getSelectButtons()
    {
        $definition      = $this->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        $buttons         = array();

        if ($basicDefinition->isDeletable()) {
            $buttons['delete'] = sprintf(
                '<input' .
                'type="submit"' .
                'name="delete"' .
                'id="delete"' .
                'class="tl_submit"' .
                'accesskey="d"' .
                'onclick="return confirm(\'%s\')"' .
                'value="%s" />',
                specialchars($this->translate('MSC.delAllConfirm')),
                specialchars($this->translate('MSC.deleteSelected'))
            );
        }

        if ($basicDefinition->isEditable()) {
            $buttons['cut'] = sprintf(
                '<input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="%s">',
                specialchars($this->translate('MSC.moveSelected'))
            );
        }

        if ($basicDefinition->isCreatable()) {
            $buttons['copy'] = sprintf(
                '<input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="%s">',
                specialchars($this->translate('MSC.copySelected'))
            );
        }

        if ($basicDefinition->isEditable()) {
            $buttons['override'] = sprintf(
                '<input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="%s">',
                specialchars($this->translate('MSC.overrideSelected'))
            );

            $buttons['edit'] = sprintf(
                '<input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="%s">',
                specialchars($this->translate('MSC.editSelected'))
            );
        }

        $event = new GetSelectModeButtonsEvent($this->getEnvironment());
        $event->setButtons($buttons);

        $dispatcher = $this->getEnvironment()->getEventDispatcher();
        $dispatcher->dispatch(sprintf('%s[%s]', $event::NAME, $definition->getName()), $event);
        $dispatcher->dispatch($event::NAME, $event);

        return $event->getButtons();
    }

    /**
     * Update the clipboard in the Environment with data from the InputProvider.
     *
     * The following parameters have to be provided by the input provider:
     *
     * Name      Type   Description
     * clipboard bool   Flag determining if the clipboard shall get cleared.
     * act       string Action to perform, either paste, cut or create.
     * id        mixed  The Id of the item to copy. In mode cut this is the id of the item to be moved.
     *
     * @param null|string $action The action to be executed or null.
     *
     * @return BaseView
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function checkClipboard($action = null)
    {
        $input     = $this->getEnvironment()->getInputProvider();
        $clipboard = $this->getEnvironment()->getClipboard();

        $clipboard->loadFrom($this->getEnvironment());

        // Reset Clipboard.
        if ($input->getParameter('clipboard') == '1') {
            $clipboard->clear()->saveTo($this->getEnvironment());

            $this->redirectHome();
            return $this;
        }

        // Push some entry into clipboard.
        if ($modelIdRaw = $input->getParameter('source')) {
            $modelId   = IdSerializer::fromSerialized($modelIdRaw);

            $parentIdRaw = $input->getParameter('pid');
            if ($parentIdRaw) {
                $parentId = IdSerializer::fromSerialized($parentIdRaw);
            } else {
                $parentId = null;
            }

            if ($action && $action == 'create' || $input->getParameter('act') == 'create') {
                $action = Item::CREATE;
            } elseif ($action && $action == 'cut' || $input->getParameter('act') == 'cut') {
                $action = Item::CUT;
            } elseif ($action && $action == 'copy' || $input->getParameter('act') == 'copy') {
                $action = Item::COPY;
            } elseif ($action && $action == 'deep-copy' || $input->getParameter('act') == 'deep-copy') {
                $action = Item::DEEP_COPY;
            } else {
                $action = false;
            }

            if ($action) {
                $item = new Item($action, $parentId, $modelId);

                // Let the clipboard save it's values persistent.
                // TODO remove clear and allow adding multiple items
                $clipboard->clear()->push($item)->saveTo($this->getEnvironment());

                $this->redirectHome();
            }
        }

        return $this;
    }

    /**
     * Determine if we are currently working in multi language mode.
     *
     * @param mixed $mixId The id of the current model.
     *
     * @return bool
     */
    protected function isMultiLanguage($mixId)
    {
        return count($this->getEnvironment()->getController()->getSupportedLanguages($mixId)) > 0;
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
        return new ContaoBackendViewTemplate($strTemplate);
    }

    /**
     * Handle an ajax call by passing it to the relevant handler class.
     *
     * The handler class might(!) exit the script.
     *
     * @return void
     */
    public function handleAjaxCall()
    {
        /** @var \ContaoCommunityAlliance\DcGeneral\Controller\Ajax $handler */
        // Fallback to Contao for ajax requests we do not know.
        if (version_compare(VERSION, '3.0', '>=')) {
            $handler = new Ajax3X();
        } else {
            $handler = new Ajax2X();
        }
        $handler->executePostActions(new DcCompat($this->getEnvironment()));
    }

    /**
     * {@inheritDoc}
     */
    public function copy(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        // TODO: copy unimplemented.

        return vsprintf($this->notImplMsg, 'copy - Mode');
    }

    /**
     * {@inheritDoc}
     */
    public function copyAll(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        // TODO: copyAll unimplemented.

        return vsprintf($this->notImplMsg, 'copyAll - Mode');
    }

    /**
     * {@inheritDoc}
     *
     * @see edit()
     */
    public function create(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        $model = $this->createEmptyModelWithDefaults();

        $input = $this->environment->getInputProvider();
        if ($input->hasParameter('after')) {
            $after          = IdSerializer::fromSerialized($input->getParameter('after'));
            $dataProvider   = $this->environment->getDataProvider($after->getDataProviderName());
            $previous       = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($after->getId()));
            $dataDefinition = $this->environment->getDataDefinition();

            /** @var Contao2BackendViewDefinitionInterface $view */
            $view = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
            foreach (array_keys($view->getListingConfig()->getDefaultSortingFields()) as $propertyName) {
                if ($propertyName != 'sorting') {
                    $propertyValue = $previous->getProperty($propertyName);
                    $model->setProperty($propertyName, $propertyValue);
                }
            }
        }

        $preFunction = function ($environment, $model) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PreCreateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        $postFunction = function ($environment, $model) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PostCreateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        return $this->createEditMask($model, null, $preFunction, $postFunction);
    }

    /**
     * {@inheritDoc}
     */
    public function cut(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        return $this->showAll($action);
    }

    /**
     * {@inheritDoc}
     */
    public function cutAll(Action $action)
    {
        // TODO: cutAll unimplemented.

        return vsprintf($this->notImplMsg, 'cutAll - Mode');
    }

    /**
     * {@inheritDoc}
     */
    public function getManualSortingProperty()
    {
        $definition = null;
        foreach ($this->getPanel() as $panel) {
            /** @var PanelInterface $panel */
            $sort = $panel->getElement('sort');
            if ($sort) {
                /** @var SortElementInterface $sort */
                $definition = $sort->getSelectedDefinition();
            }
        }

        if ($definition === null) {
            $collection = $this
                ->getViewSection()
                ->getListingConfig()
                ->getGroupAndSortingDefinition();

            if ($collection->hasDefault()) {
                $definition = $collection->getDefault();
            }
        }

        if ($definition) {
            foreach ($definition as $information) {
                if ($information->isManualSorting()) {
                    return $information->getProperty();
                }
            }
        }

        return null;
    }

    /**
     * Retrieve model instances for all ids contained in the clipboard.
     *
     * @param bool $clone True if the models shall be copied, false otherwise.
     *
     * @return CollectionInterface
     */
    protected function getModelsFromClipboard($clone = false)
    {
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider();
        $models       = $dataProvider->getEmptyCollection();
        $clipboard    = $this->getEnvironment()->getClipboard();

        foreach ($clipboard->getContainedIds() as $id) {
            if ($id === null) {
                $model = $this->createEmptyModelWithDefaults();
                $models->push($model);

                if ($parentId = $clipboard->getParent()) {
                    $id           = IdSerializer::fromSerialized($parentId);
                    $dataProvider = $environment->getDataProvider($id->getDataProviderName());
                    $parentModel  = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($id->getId()));
                    $environment->getController()->setParent($model, $parentModel);
                }
            } elseif (is_string($id)) {
                $id           = IdSerializer::fromSerialized($id);
                $dataProvider = $environment->getDataProvider($id->getDataProviderName());
                $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($id->getId()));

                if ($model) {
                    if ($clone) {
                        // Trigger the pre duplicate event.
                        $duplicateEvent = new PreDuplicateModelEvent($environment, $model);

                        $environment->getEventDispatcher()->dispatch(
                            sprintf('%s[%s]', $duplicateEvent::NAME, $environment->getDataDefinition()->getName()),
                            $duplicateEvent
                        );
                        $environment->getEventDispatcher()->dispatch($duplicateEvent::NAME, $duplicateEvent);

                        // Make a duplicate.
                        $newModel = $environment->getController()->createClonedModel($model);

                        // And trigger the post event for it.
                        $duplicateEvent = new PostDuplicateModelEvent($environment, $newModel, $model);
                        $environment->getEventDispatcher()->dispatch(
                            sprintf('%s[%s]', $duplicateEvent::NAME, $environment->getDataDefinition()->getName()),
                            $duplicateEvent
                        );
                        $environment->getEventDispatcher()->dispatch($duplicateEvent::NAME, $duplicateEvent);

                        // Set the new model as the old one.
                        $model = $newModel;
                    }

                    $models->push($model);
                }
            }
        }

        return $models;
    }

    /**
     * Invoked for cut and copy.
     *
     * This performs redirectHome() upon successful execution and throws an exception otherwise.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When invalid parameters are encountered.
     */
    public function paste(Action $action)
    {
        $this->checkClipboard();

        $environment = $this->getEnvironment();
        $input       = $environment->getInputProvider();
        $clipboard   = $environment->getClipboard();
        $source      = $input->getParameter('source')
            ? IdSerializer::fromSerialized($input->getParameter('source'))
            : null;
        $after       = $input->getParameter('after')
            ? IdSerializer::fromSerialized($input->getParameter('after'))
            : $input->getParameter('after');
        $into        = $input->getParameter('into')
            ? IdSerializer::fromSerialized($input->getParameter('into'))
            : null;

        if ($input->getParameter('mode') == 'create') {
            $dataProvider = $environment->getDataProvider();

            $models = $dataProvider->getEmptyCollection();
            $models->push($dataProvider->getEmptyModel());

            $clipboard->create($input->getParameter('pid'))->saveTo($environment);

            $this->redirectHome();
        }

        if ($source) {
            $dataProvider = $environment->getDataProvider($source->getDataProviderName());

            $filterConfig = $dataProvider->getEmptyConfig();

            $filterConfig->setFilter(
                array(
                    array(
                        'operation' => '=',
                        'property'  => 'id',
                        'value'     => $source->getId()
                    )
                )
            );

            $models = $dataProvider->fetchAll($filterConfig);
        } else {
            $models = $this->getModelsFromClipboard($clipboard->isCopy());

            if ($clipboard->isCopy()) {
                // FIXME: recursive copy is not implemented yet!
            }
        }

        // Trigger for each model the pre persist event.
        foreach ($models as $model) {
            $event = new PrePasteModelEvent($environment, $model);

            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()),
                $event
            );
            $environment->getEventDispatcher()->dispatch($event::NAME, $event);
        }

        if ($after && $after->getId()) {
            $dataProvider = $environment->getDataProvider($after->getDataProviderName());
            $previous     = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($after->getId()));
            $environment->getController()->pasteAfter($previous, $models, $this->getManualSortingProperty());
        } elseif ($into && $into->getId()) {
            $dataProvider = $environment->getDataProvider($into->getDataProviderName());
            $parent       = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($into->getId()));
            $environment->getController()->pasteInto($parent, $models, $this->getManualSortingProperty());
        } elseif (($after && $after->getId() == '0') || ($into && $into->getId() == '0')) {
            $environment->getController()->pasteTop($models, $this->getManualSortingProperty());
        } else {
            throw new DcGeneralRuntimeException('Invalid parameters.');
        }

        // Trigger for each model the past persist event.
        foreach ($models as $model) {
            $event = new PostPasteModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()),
                $event
            );
            $environment->getEventDispatcher()->dispatch($event::NAME, $event);
        }

        if (!$source) {
            $clipboard
                ->clear()
                ->saveTo($environment);
        }

        $this->redirectHome();

        throw new DcGeneralRuntimeException('Invalid paste operation parameters.');
    }

    /**
     * Delete a model and redirect the user to the listing.
     *
     * NOTE: This method redirects the user to the listing and therefore the script will be ended.
     *
     * @return string
     *
     * @throws DcGeneralRuntimeException If the model to delete could not be loaded.
     */
    public function delete(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        // Check if is it allowed to delete a record.
        if (!$this->getEnvironment()->getDataDefinition()->getBasicDefinition()->isDeletable()) {
            $this->getEnvironment()->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent(
                    sprintf(
                        'Table "%s" is not deletable',
                        'DC_General - DefaultController - delete()',
                        $this->getEnvironment()->getDataDefinition()->getName()
                    ),
                    __CLASS__ . '::delete()',
                    TL_ERROR
                )
            );

            $this->getEnvironment()->getEventDispatcher()->dispatch(
                ContaoEvents::CONTROLLER_REDIRECT,
                new RedirectEvent('contao/main.php?act=error')
            );
        }

        $environment  = $this->getEnvironment();
        $modelId      = IdSerializer::fromSerialized($environment->getInputProvider()->getParameter('id'));
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        if (!$model->getId()) {
            throw new DcGeneralRuntimeException(
                'Could not load model with id ' . $environment->getInputProvider()->getParameter('id')
            );
        }

        // Trigger event before the model will be deleted.
        $event = new PreDeleteModelEvent($environment, $model);
        $environment->getEventDispatcher()->dispatch(
            sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()),
            $event
        );
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);

        // FIXME: See DefaultController::delete() - we need to delete the children of this item as well over all data providers.
        /*
        $arrDelIDs = array();

        // Delete record
        switch ($definition->getSortingMode())
        {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4:
                $arrDelIDs = array();
                $arrDelIDs[] = $intRecordID;
                break;

            case 5:
                $arrDelIDs = $environment->getController()->fetchMode5ChildrenOf($environment->getCurrentModel(), $blnRecurse = true);
                $arrDelIDs[] = $intRecordID;
                break;
        }

        // Delete all entries
        foreach ($arrDelIDs as $intId)
        {
            $this->getEnvironment()->getDataProvider()->delete($intId);

            // Add a log entry unless we are deleting from tl_log itself
            if ($environment->getDataDefinition()->getName() != 'tl_log')
            {
                BackendBindings::log('DELETE FROM ' . $environment->getDataDefinition()->getName() . ' WHERE id=' . $intId, 'DC_General - DefaultController - delete()', TL_GENERAL);
            }
        }
         */

        $dataProvider->delete($model);

        // Trigger event after the model is deleted.
        $event = new PostDeleteModelEvent($environment, $model);
        $environment->getEventDispatcher()->dispatch(
            sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()),
            $event
        );
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);

        $this->redirectHome();

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function move(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        // TODO: move unimplemented.
        return vsprintf($this->notImplMsg, 'move - Mode');
    }

    /**
     * {@inheritDoc}
     */
    public function undo(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        // TODO: undo unimplemented.
        return vsprintf($this->notImplMsg, 'undo - Mode');
    }

    /**
     * Check the submitted data if we want to restore a previous version of a model.
     *
     * If so, the model will get loaded and marked as active version in the data provider and the client will perform a
     * reload of the page.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When the requested version could not be located in the database.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function checkRestoreVersion()
    {
        $environment   = $this->getEnvironment();
        $definition    = $environment->getDataDefinition();
        $inputProvider = $environment->getInputProvider();

        if (!$inputProvider->hasParameter('id')) {
            return;
        }

        $modelId                 = IdSerializer::fromSerialized($inputProvider->getParameter('id'));
        $dataProviderDefinition  = $definition->getDataProviderDefinition();
        $dataProvider            = $environment->getDataProvider($modelId->getDataProviderName());
        $dataProviderInformation = $dataProviderDefinition->getInformation($modelId->getDataProviderName());

        if ($dataProviderInformation->isVersioningEnabled()
            && ($inputProvider->getValue('FORM_SUBMIT') === 'tl_version')
            && ($modelVersion = $inputProvider->getValue('version')) !== null
        ) {
            $model = $dataProvider->getVersion($modelId->getId(), $modelVersion);

            if ($model === null) {
                $message = sprintf(
                    'Could not load version %s of record ID %s from %s',
                    $modelVersion,
                    $modelId->getId(),
                    $modelId->getDataProviderName()
                );

                $environment->getEventDispatcher()->dispatch(
                    ContaoEvents::SYSTEM_LOG,
                    new LogEvent($message, TL_ERROR, 'DC_General - checkRestoreVersion()')
                );

                throw new DcGeneralRuntimeException($message);
            }

            $dataProvider->save($model);
            $dataProvider->setVersionActive($modelId->getId(), $modelVersion);
            $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
        }
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function enforceModelRelationship($model)
    {
        // No op in this base class but implemented in subclasses to enforce parent<->child relationship.
    }

    /**
     * Create an empty model using the default values from the definition.
     *
     * @return ModelInterface
     */
    protected function createEmptyModelWithDefaults()
    {
        $environment        = $this->getEnvironment();
        $definition         = $environment->getDataDefinition();
        $environment        = $this->getEnvironment();
        $dataProvider       = $environment->getDataProvider();
        $propertyDefinition = $definition->getPropertiesDefinition();
        $properties         = $propertyDefinition->getProperties();
        $model              = $dataProvider->getEmptyModel();

        foreach ($properties as $property) {
            $propName = $property->getName();

            if ($property->getDefaultValue() !== null) {
                $model->setProperty($propName, $property->getDefaultValue());
            }
        }

        return $model;
    }

    /**
     * Generate the view for edit.
     *
     * @return string
     *
     * @throws DcGeneralRuntimeException         When the current data definition is not editable or is closed.
     *
     * @throws DcGeneralInvalidArgumentException When an unknown property is mentioned in the palette.
     */
    public function edit(Action $action)
    {
        $environment   = $this->getEnvironment();
        $inputProvider = $environment->getInputProvider();
        $modelId       = $inputProvider->hasParameter('id')
            ? IdSerializer::fromSerialized($inputProvider->getParameter('id'))
            : null;
        $dataProvider  = $environment->getDataProvider($modelId ? $modelId->getDataProviderName() : null);

        $this->checkRestoreVersion();

        if ($modelId && $modelId->getId()) {
            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
        } else {
            $model = $this->createEmptyModelWithDefaults();
        }

        // We need to keep the original data here.
        $originalModel = clone $model;
        $originalModel->setId($model->getId());

        return $this->createEditMask($model, $originalModel, null, null);
    }

    /**
     * Create the edit mask.
     *
     * @param ModelInterface $model         The model with the current data.
     *
     * @param ModelInterface $originalModel The data from the original data.
     *
     * @param callable       $preFunction   The function to call before saving an item.
     *
     * @param callable       $postFunction  The function to call after saving an item.
     *
     * @return string
     *
     * @throws DcGeneralRuntimeException         If the data container is not editable, closed.
     *
     * @throws DcGeneralInvalidArgumentException If an unknown property is encountered in the palette.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function createEditMask($model, $originalModel, $preFunction, $postFunction)
    {
        $editMask = new EditMask($this, $model, $originalModel, $preFunction, $postFunction);
        return $editMask->execute();
    }

    /**
     * Calculate the label of a property to se in "show" view.
     *
     * @param PropertyInterface $property The property for which the label shall be calculated.
     *
     * @return string
     */
    protected function getLabelForShow(PropertyInterface $property)
    {
        $environment = $this->getEnvironment();
        $definition  = $environment->getDataDefinition();

        $label = $environment->getTranslator()->translate($property->getLabel(), $definition->getName());

        if (!$label) {
            $label = $environment->getTranslator()->translate('MSC.' . $property->getName());
        }

        if (is_array($label)) {
            $label = $label[0];
        }

        if (!$label) {
            $label = $property->getName();
        }

        return $label;
    }

    /**
     * Show all entries from one table.
     *
     * @return string
     */
    public function showAll(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        return sprintf(
            $this->notImplMsg,
            'showAll - Mode ' . $this->environment->getDataDefinition()->getBasicDefinition()->getMode()
        );
    }

    /**
     * Handle the "toggle" action.
     *
     * @param string $name The command name (default: toggle).
     *
     * @return string
     */
    public function toggle(Action $action, $name = 'toggle')
    {
        $environment = $this->getEnvironment();
        $input       = $environment->getInputProvider();

        if ($input->hasParameter('id')) {
            $serializedId = IdSerializer::fromSerialized($input->getParameter('id'));
        }

        if (!(isset($serializedId)
            && $serializedId->getDataProviderName() == $environment->getDataDefinition()->getName())
        ) {
            return '';
        }

        /** @var ToggleCommandInterface $operation */
        $operation    = $this->getViewSection()->getModelCommands()->getCommandNamed($name);
        $dataProvider = $environment->getDataProvider();
        $newState     = $operation->isInverse()
            ? $input->getParameter('state') == 1 ? '' : '1'
            : $input->getParameter('state') == 1 ? '1' : '';

        $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($serializedId->getId()));

        $model->setProperty($operation->getToggleProperty(), $newState);

        $dataProvider->save($model);

        return $this->showAll($action);
    }

    /**
     * Create the "new" button.
     *
     * @return CommandInterface|null
     */
    protected function getCreateModelCommand()
    {
        $environment     = $this->getEnvironment();
        $definition      = $environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        $providerName    = $environment->getDataDefinition()->getName();
        $mode            = $basicDefinition->getMode();
        $config          = $this->getEnvironment()->getController()->getBaseConfig();
        $sorting         = $this->getManualSortingProperty();

        if ($serializedPid = $environment->getInputProvider()->getParameter('pid')) {
            $pid = IdSerializer::fromSerialized($serializedPid);
        } else {
            $pid = new IdSerializer();
        }

        if (!$basicDefinition->isCreatable()) {
            return null;
        }

        $command    = new Command();
        $parameters = $command->getParameters();
        $extra      = $command->getExtra();

        $extra['class']      = 'header_new';
        $extra['accesskey']  = 'n';
        $extra['attributes'] = 'onclick="Backend.getScrollOffset();"';

        $command
            ->setName('button_new')
            ->setLabel($this->translate('new.0', $providerName))
            ->setDescription($this->translate('new.1', $providerName));

        $this->getPanel()->initialize($config);

        // Add new button.
        if (($mode == BasicDefinitionInterface::MODE_FLAT)
            || (($mode == BasicDefinitionInterface::MODE_PARENTEDLIST) && !$sorting)
        ) {
            $parameters['act'] = 'create';
            // Add new button.
            if ($pid->getDataProviderName() && $pid->getId()) {
                $parameters['pid'] = $pid->getSerialized();
            }
        } elseif (($mode == BasicDefinitionInterface::MODE_PARENTEDLIST)
            || ($mode == BasicDefinitionInterface::MODE_HIERARCHICAL)
        ) {
            if ($environment->getClipboard()->isNotEmpty()) {
                return null;
            }

            $after = IdSerializer::fromValues($definition->getName(), 0);

            $parameters['act']  = 'paste';
            $parameters['mode'] = 'create';

            if ($mode == BasicDefinitionInterface::MODE_PARENTEDLIST) {
                $parameters['after'] = $after->getSerialized();
            }

            if ($pid->getDataProviderName() && $pid->getId()) {
                $parameters['pid'] = $pid->getSerialized();
            }
        }

        return $command;
    }

    /**
     * Create the "clear clipboard" button.
     *
     * @return CommandInterface|null
     */
    protected function getClearClipboardCommand()
    {
        if ($this->getEnvironment()->getClipboard()->isEmpty()) {
            return null;
        }
        $command             = new Command();
        $parameters          = $command->getParameters();
        $extra               = $command->getExtra();
        $extra['class']      = 'header_clipboard';
        $extra['accesskey']  = 'x';
        $extra['attributes'] = 'onclick="Backend.getScrollOffset();"';

        $parameters['clipboard'] = '1';

        $command
            ->setName('button_clipboard')
            ->setLabel($this->translate('MSC.clearClipboard'))
            ->setDescription($this->translate('MSC.clearClipboard'));

        return $command;
    }

    /**
     * Create the "back" button.
     *
     * @return CommandInterface|null
     */
    protected function getBackCommand()
    {
        $environment = $this->getEnvironment();
        if (!($this->isSelectModeActive()
            || $environment->getDataDefinition()->getBasicDefinition()->getParentDataProvider())
        ) {
            return null;
        }

        /** @var GetReferrerEvent $event */
        if ($environment->getParentDataDefinition()) {
            $event = $environment->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_GET_REFERRER,
                new GetReferrerEvent(true, $environment->getParentDataDefinition()->getName())
            );
        } else {
            $event = $environment->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_GET_REFERRER,
                new GetReferrerEvent(true, $environment->getDataDefinition()->getName())
            );
        }

        $command             = new Command();
        $extra               = $command->getExtra();
        $extra['class']      = 'header_back';
        $extra['accesskey']  = 'b';
        $extra['attributes'] = 'onclick="Backend.getScrollOffset();"';
        $extra['href']       = $event->getReferrerUrl();

        $command
            ->setName('back_button')
            ->setLabel($this->translate('MSC.backBT'))
            ->setDescription($this->translate('MSC.backBT'));

        return $command;
    }

    /**
     * Render a single header button.
     *
     * @param CommandInterface $command The command definition.
     *
     * @return string
     */
    protected function generateHeaderButton(CommandInterface $command)
    {
        $environment = $this->getEnvironment();
        $extra       = $command->getExtra();
        $label       = $command->getLabel();
        $dispatcher  = $environment->getEventDispatcher();

        if (isset($extra['href'])) {
            $href = $extra['href'];
        } else {
            $href = '';
            foreach ($command->getParameters() as $key => $value) {
                $href .= '&' . $key . '=' . $value;
            }

            /** @var AddToUrlEvent $event */
            $event = $dispatcher->dispatch(
                ContaoEvents::BACKEND_ADD_TO_URL,
                new AddToUrlEvent(
                    $href
                )
            );

            $href = $event->getUrl();
        }

        if (!strlen($label)) {
            $label = $command->getName();
        }

        $buttonEvent = new GetGlobalButtonEvent($this->getEnvironment());
        $buttonEvent
            ->setAccessKey(isset($extra['accesskey']) ? trim($extra['accesskey']) : null)
            ->setAttributes(' ' . ltrim($extra['attributes']))
            ->setClass($extra['class'])
            ->setKey($command->getName())
            ->setHref($href)
            ->setLabel($label)
            ->setTitle($command->getDescription());

        $dispatcher->dispatch(
            sprintf(
                '%s[%s][%s]',
                $buttonEvent::NAME,
                $environment->getDataDefinition()->getName(),
                $command->getName()
            ),
            $buttonEvent
        );
        $dispatcher->dispatch(
            sprintf('%s[%s]', $buttonEvent::NAME, $environment->getDataDefinition()->getName()),
            $buttonEvent
        );
        $environment->getEventDispatcher()->dispatch($buttonEvent::NAME, $buttonEvent);

        // Allow to override the button entirely.
        $html = $buttonEvent->getHtml();
        if ($html !== null) {
            return $html;
        }

        // Use the view native button building.
        return sprintf(
            '<a href="%s" class="%s" title="%s"%s>%s</a> ',
            $buttonEvent->getHref(),
            $buttonEvent->getClass(),
            specialchars($buttonEvent->getTitle()),
            $buttonEvent->getAttributes(),
            $buttonEvent->getLabel()
        );
    }

    /**
     * Generate all buttons for the header of a view.
     *
     * @param string $strButtonId The id for the surrounding html div element.
     *
     * @return string
     */
    protected function generateHeaderButtons($strButtonId)
    {
        /** @var CommandInterface[] $globalOperations */
        $globalOperations = $this->getViewSection()->getGlobalCommands()->getCommands();
        $buttons          = array();

        if (!is_array($globalOperations)) {
            $globalOperations = array();
        }

        if ($this->isSelectModeActive()) {
            // Special case - if select mode active, we must not display the "edit all" button.
            unset($globalOperations['all']);
        } else {
            // We do not have the select mode.
            $command = $this->getCreateModelCommand();
            if ($command !== null) {
                // New button always first.
                array_unshift($globalOperations, $command);
            }

            /*
            $command = $this->getClearClipboardCommand();
            if ($command !== null) {
                // Clear clipboard to the end.
                $globalOperations[] = $command;
            }
            */
        }

        $command = $this->getBackCommand();
        if ($command !== null) {
            // Back button always to the end.
            $globalOperations[] = $command;
        }

        foreach ($globalOperations as $command) {
            $buttons[$command->getName()] = $this->generateHeaderButton($command);
        }

        $buttonsEvent = new GetGlobalButtonsEvent($this->getEnvironment());
        $buttonsEvent->setButtons($buttons);

        $this->getEnvironment()->getEventDispatcher()->dispatch(
            sprintf('%s[%s]', $buttonsEvent::NAME, $this->getEnvironment()->getDataDefinition()->getName()),
            $buttonsEvent
        );
        $this->getEnvironment()->getEventDispatcher()->dispatch($buttonsEvent::NAME, $buttonsEvent);

        return '<div id="' . $strButtonId . '">' . implode('', $buttonsEvent->getButtons()) . '</div>';
    }

    /**
     * Render a command button.
     *
     * @param CommandInterface $objCommand           The command to render the button for.
     *
     * @param ModelInterface   $objModel             The model to which the command shall get applied.
     *
     * @param bool             $blnCircularReference Determinator if there exists a circular reference between the model
     *                                               and the model(s) contained in the clipboard.
     *
     * @param array            $arrChildRecordIds    List of the ids of all child models of the current model.
     *
     * @param ModelInterface   $previous             The previous model in the collection.
     *
     * @param ModelInterface   $next                 The next model in the collection.
     *
     * @return string
     */
    protected function buildCommand($objCommand, $objModel, $blnCircularReference, $arrChildRecordIds, $previous, $next)
    {
        $environment        = $this->getEnvironment();
        $inputProvider      = $environment->getInputProvider();
        $dispatcher         = $environment->getEventDispatcher();
        $dataDefinitionName = $environment->getDataDefinition()->getName();
        $commandName        = $objCommand->getName();
        $parameters         = (array)$objCommand->getParameters();
        $extra              = (array)$objCommand->getExtra();
        $extraAttributes    = !empty($extra['attributes']) ? $extra['attributes'] : null;
        $attributes         = '';

        // Set basic information.
        $opLabel = $objCommand->getLabel();
        if (strlen($opLabel)) {
            $label = $opLabel;
        } else {
            $label = $commandName;
        }

        $label = $this->translate($label, $dataDefinitionName);

        if (is_array($label)) {
            $label = $label[0];
        }

        $opDesc = $this->translate(
            $objCommand->getDescription(),
            $dataDefinitionName
        );

        if (strlen($opDesc)) {
            $title = sprintf($opDesc, $objModel->getID());
        } else {
            $title = sprintf('%s id %s', $label, $objModel->getID());
        }

        // Toggle has to trigger the javascript.
        if ($objCommand instanceof ToggleCommandInterface) {
            $parameters['act'] = $commandName;

            $attributes = sprintf(
                'onclick="Backend.getScrollOffset(); return BackendGeneral.toggleVisibility(this, \'%s\', \'%s\');"',
                $extra['icon'],
                $extra['icon_disabled']
            );

            if ($objCommand->isInverse()
                ? $objModel->getProperty($objCommand->getToggleProperty())
                : !$objModel->getProperty($objCommand->getToggleProperty())
            ) {
                $extra['icon'] = $extra['icon_disabled'] ?: 'invisible.gif';
            }
        }

        if ($extraAttributes) {
            $attributes .= ltrim(sprintf($extraAttributes, $objModel->getID()));
        }

        $serializedModelId = IdSerializer::fromModel($objModel)->getSerialized();

        // Cut needs some special information.
        if ($objCommand instanceof CutCommandInterface) {
            $parameters        = array();
            $parameters['act'] = $commandName;

            // If we have a pid add it, used for mode 4 and all parent -> current views.
            if ($inputProvider->hasParameter('pid')) {
                $parameters['pid'] = $inputProvider->getParameter('pid');
            }

            // Source is the id of the element which should move.
            $parameters['source'] = $serializedModelId;
        } elseif ($objCommand instanceof CopyCommandInterface) {
            // The copy operation.
            $parameters        = array();
            $parameters['act'] = $commandName;

            // If we have a pid add it, used for mode 4 and all parent -> current views.
            if ($inputProvider->hasParameter('pid')) {
                $parameters['pid'] = $inputProvider->getParameter('pid');
            }

            // Source is the id of the element which should move.
            $parameters['source'] = $serializedModelId;
        } else {
            // TODO: Shall we interface this option?
            $idParam = isset($extra['idparam']) ? $extra['idparam'] : null;
            if ($idParam) {
                $parameters[$idParam] = $serializedModelId;
            } else {
                $parameters['id'] = $serializedModelId;
            }
        }

        $strHref = '';
        foreach ($parameters as $key => $value) {
            $strHref .= sprintf('&%s=%s', $key, $value);
        }

        /** @var AddToUrlEvent $event */
        $event   = $dispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, new AddToUrlEvent($strHref));
        $strHref = $event->getUrl();

        $buttonEvent = new GetOperationButtonEvent($this->getEnvironment());
        $buttonEvent
            ->setCommand($objCommand)
            ->setObjModel($objModel)
            ->setAttributes($attributes)
            ->setLabel($label)
            ->setTitle($title)
            ->setHref($strHref)
            ->setChildRecordIds($arrChildRecordIds)
            ->setCircularReference($blnCircularReference)
            ->setPrevious($previous)
            ->setNext($next)
            ->setDisabled($objCommand->isDisabled());

        $dispatcher->dispatch(
            sprintf('%s[%s][%s]', $buttonEvent::NAME, $dataDefinitionName, $commandName),
            $buttonEvent
        );
        $dispatcher->dispatch(
            sprintf('%s[%s]', $buttonEvent::NAME, $dataDefinitionName, $commandName),
            $buttonEvent
        );
        $dispatcher->dispatch($buttonEvent::NAME, $buttonEvent);

        // If the event created a button, use it.
        if ($buttonEvent->getHtml() !== null) {
            return trim($buttonEvent->getHtml());
        }

        $icon = $extra['icon'];

        if ($buttonEvent->isDisabled()) {
            /** @var GenerateHtmlEvent $event */
            $event = $dispatcher->dispatch(
                ContaoEvents::IMAGE_GET_HTML,
                new GenerateHtmlEvent(
                    substr_replace($icon, '_1', strrpos($icon, '.'), 0),
                    $buttonEvent->getLabel()
                )
            );

            return $event->getHtml();
        }

        /** @var GenerateHtmlEvent $event */
        $event = $dispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                $icon,
                $buttonEvent->getLabel()
            )
        );

        return sprintf(
            ' <a href="%s" title="%s" %s>%s</a>',
            $buttonEvent->getHref(),
            specialchars($buttonEvent->getTitle()),
            $buttonEvent->getAttributes(),
            $event->getHtml()
        );
    }

    /**
     * Render the paste into button.
     *
     * @param GetPasteButtonEvent $event The event that has been triggered.
     *
     * @return string
     */
    public function renderPasteIntoButton(GetPasteButtonEvent $event)
    {
        if ($event->getHtmlPasteInto() !== null) {
            return $event->getHtmlPasteInto();
        }

        $strLabel = $this->translate('pasteinto.0', $event->getModel()->getProviderName());
        if ($event->isPasteIntoDisabled()) {
            /** @var GenerateHtmlEvent $imageEvent */
            $imageEvent = $this->getEnvironment()->getEventDispatcher()->dispatch(
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
        $imageEvent = $this->getEnvironment()->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'pasteinto.gif',
                $strLabel,
                'class="blink"'
            )
        );

        $opDesc = $this->translate('pasteinto.1', $this->getEnvironment()->getDataDefinition()->getName());
        if (strlen($opDesc)) {
            $title = sprintf($opDesc, $event->getModel()->getId());
        } else {
            $title = sprintf('%s id %s', $strLabel, $event->getModel()->getId());
        }

        return sprintf(
            ' <a href="%s" title="%s" %s>%s</a>',
            $event->getHrefInto(),
            specialchars($title),
            'onclick="Backend.getScrollOffset()"',
            $imageEvent->getHtml()
        );
    }

    /**
     * Render the paste after button.
     *
     * @param GetPasteButtonEvent $event The event that has been triggered.
     *
     * @return string
     */
    public function renderPasteAfterButton(GetPasteButtonEvent $event)
    {
        if ($event->getHtmlPasteAfter() !== null) {
            return $event->getHtmlPasteAfter();
        }

        $strLabel = $this->translate('pasteafter.0', $event->getModel()->getProviderName());
        if ($event->isPasteAfterDisabled()) {
            /** @var GenerateHtmlEvent $imageEvent */
            $imageEvent = $this->getEnvironment()->getEventDispatcher()->dispatch(
                ContaoEvents::IMAGE_GET_HTML,
                new GenerateHtmlEvent(
                    'pasteafter_.gif',
                    $strLabel,
                    'class="blink"'
                )
            );

            return $imageEvent->getHtml();
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $this->getEnvironment()->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'pasteafter.gif',
                $strLabel,
                'class="blink"'
            )
        );

        $opDesc = $this->translate('pasteafter.1', $this->getEnvironment()->getDataDefinition()->getName());
        if (strlen($opDesc)) {
            $title = sprintf($opDesc, $event->getModel()->getId());
        } else {
            $title = sprintf('%s id %s', $strLabel, $event->getModel()->getId());
        }

        return sprintf(
            ' <a href="%s" title="%s" %s>%s</a>',
            $event->getHrefAfter(),
            specialchars($title),
            'onclick="Backend.getScrollOffset()"',
            $imageEvent->getHtml()
        );
    }

    /**
     * Compile buttons from the table configuration array and return them as HTML.
     *
     * @param ModelInterface $model    The model for which the buttons shall be generated for.
     * @param ModelInterface $previous The previous model in the collection.
     * @param ModelInterface $next     The next model in the collection.
     *
     * @return string
     */
    protected function generateButtons(
        ModelInterface $model,
        ModelInterface $previous = null,
        ModelInterface $next = null
    ) {
        $commands     = $this->getViewSection()->getModelCommands();
        $objClipboard = $this->getEnvironment()->getClipboard();
        $dispatcher   = $this->getEnvironment()->getEventDispatcher();

        if ($this->getEnvironment()->getClipboard()->isNotEmpty()) {
            $circularIds = $objClipboard->getCircularIds();
            $isCircular  = in_array(IdSerializer::fromModel($model)->getSerialized(), $circularIds);
        } else {
            $circularIds = array();
            $isCircular  = false;
        }

        $arrButtons = array();
        foreach ($commands->getCommands() as $command) {
            $arrButtons[$command->getName()] = $this->buildCommand(
                $command,
                $model,
                $isCircular,
                $circularIds,
                $previous,
                $next
            );
        }

        if ($this->getManualSortingProperty() &&
            $objClipboard->isEmpty() &&
            $this->getDataDefinition()->getBasicDefinition()->getMode() != BasicDefinitionInterface::MODE_HIERARCHICAL
        ) {
            /** @var AddToUrlEvent $urlEvent */
            $urlEvent = $dispatcher->dispatch(
                ContaoEvents::BACKEND_ADD_TO_URL,
                new AddToUrlEvent(
                    'act=create&amp;after=' . IdSerializer::fromModel($model)->getSerialized()
                )
            );

            /** @var GenerateHtmlEvent $imageEvent */
            $imageEvent = $dispatcher->dispatch(
                ContaoEvents::IMAGE_GET_HTML,
                new GenerateHtmlEvent(
                    'new.gif',
                    $this->translate('pastenew.0', $this->getDataDefinition()->getName())
                )
            );

            $arrButtons['pasteNew'] = sprintf(
                '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
                $urlEvent->getUrl(),
                specialchars($this->translate('pastenew.1', $this->getDataDefinition()->getName())),
                $imageEvent->getHtml()
            );
        }

        // Add paste into/after icons.
        if ($objClipboard->isNotEmpty()) {
            if ($objClipboard->isCreate()) {
                // Add ext. information.
                $add2UrlAfter = sprintf(
                    'act=create&after=%s&',
                    IdSerializer::fromModel($model)->getSerialized()
                );

                $add2UrlInto = sprintf(
                    'act=create&into=%s&',
                    IdSerializer::fromModel($model)->getSerialized()
                );
            } else {
                // Add ext. information.
                $add2UrlAfter = sprintf(
                    'act=paste&after=%s&',
                    IdSerializer::fromModel($model)->getSerialized()
                );

                $add2UrlInto = sprintf(
                    'act=paste&into=%s&',
                    IdSerializer::fromModel($model)->getSerialized()
                );
            }

            /** @var AddToUrlEvent $urlAfter */
            $urlAfter = $dispatcher->dispatch(
                ContaoEvents::BACKEND_ADD_TO_URL,
                new AddToUrlEvent($add2UrlAfter)
            );

            /** @var AddToUrlEvent $urlInto */
            $urlInto = $dispatcher->dispatch(
                ContaoEvents::BACKEND_ADD_TO_URL,
                new AddToUrlEvent($add2UrlInto)
            );

            $buttonEvent = new GetPasteButtonEvent($this->getEnvironment());
            $buttonEvent
                ->setModel($model)
                ->setCircularReference($isCircular)
                ->setPrevious($previous)
                ->setNext($next)
                ->setHrefAfter($urlAfter->getUrl())
                ->setHrefInto($urlInto->getUrl())
                // Check if the id is in the ignore list.
                ->setPasteAfterDisabled($objClipboard->isCut() && $isCircular)
                ->setPasteIntoDisabled($objClipboard->isCut() && $isCircular)
                ->setContainedModels($this->getModelsFromClipboard());

            $this->getEnvironment()->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $buttonEvent::NAME, $this->getEnvironment()->getDataDefinition()->getName()),
                $buttonEvent
            );
            $this->getEnvironment()->getEventDispatcher()->dispatch($buttonEvent::NAME, $buttonEvent);

            $arrButtons['pasteafter'] = $this->renderPasteAfterButton($buttonEvent);
            if ($this->getDataDefinition()->getBasicDefinition()->getMode()
                == BasicDefinitionInterface::MODE_HIERARCHICAL) {
                $arrButtons['pasteinto'] = $this->renderPasteIntoButton($buttonEvent);
            }
        }

        return implode(' ', $arrButtons);
    }

    /**
     * Render the panel.
     *
     * @param array $ignoredPanels A list with ignored elements [Optional].
     *
     * @throws DcGeneralRuntimeException When no information of panels can be obtained from the data container.
     *
     * @return string
     */
    protected function panel($ignoredPanels = array())
    {
        $renderer = new PanelRenderer($this);
        return $renderer->render($ignoredPanels);
    }

    /**
     * Get the breadcrumb navigation via event.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function breadcrumb()
    {
        $event = new GetBreadcrumbEvent($this->getEnvironment());

        $dispatcher = $this->getEnvironment()->getEventDispatcher();
        // Backwards compatibility.
        $dispatcher->dispatch(
            sprintf('%s[%s]', $event::NAME, $this->getEnvironment()->getDataDefinition()->getName()),
            $event
        );
        $dispatcher->dispatch(sprintf('%s', $event::NAME), $event);

        $arrReturn = $event->getElements();

        if (!is_array($arrReturn) || count($arrReturn) == 0) {
            return null;
        }

        $GLOBALS['TL_CSS'][] = 'system/modules/dc-general/html/css/generalBreadcrumb.css';

        $objTemplate = $this->getTemplate('dcbe_general_breadcrumb');
        $this->addToTemplate('elements', $arrReturn, $objTemplate);

        return $objTemplate->parse();
    }

    /**
     * Format a model accordingly to the current configuration.
     *
     * Returns either an array when in tree mode or a string in (parented) list mode.
     *
     * @param ModelInterface $model The model that shall be formatted.
     *
     * @return array
     *
     * @deprecated Dispatch a FormatModelLabelEvent instead!
     */
    public function formatModel(ModelInterface $model)
    {
        $event = new FormatModelLabelEvent($this->environment, $model);
        $this->environment->getEventDispatcher()->dispatch(
            DcGeneralEvents::FORMAT_MODEL_LABEL,
            $event
        );
        return $event->getLabel();
    }

    /**
     * Get for a field the readable value.
     *
     * @param PropertyInterface $property The property to be rendered.
     *
     * @param ModelInterface    $model    The model from which the property value shall be retrieved from.
     *
     * @param mixed             $value    The value for the property.
     *
     * @return mixed
     *
     * @deprecated Use ViewHelpers::getReadableFieldValue($environment, $property, $model, $value) instead!
     */
    public function getReadableFieldValue(PropertyInterface $property, ModelInterface $model, $value)
    {
        return ViewHelpers::getReadableFieldValue($this->environment, $property, $model, $value);
    }

    /**
     * Redirects to the real back end module.
     *
     * @return void
     *
     * @deprecated Use ViewHelpers::redirectHome($environment) instead!
     */
    protected function redirectHome()
    {
        ViewHelpers::redirectHome($this->environment);
    }

    /**
     * Retrieve the currently active sorting.
     *
     * @return GroupAndSortingDefinitionInterface
     *
     * @deprecated Use ViewHelpers::getCurrentSorting($environment) instead!
     */
    protected function getCurrentSorting()
    {
        return ViewHelpers::getCurrentSorting($this->environment);
    }

    /**
     * Retrieve the currently active grouping mode.
     *
     * @return array|null
     *
     * @see    ListingConfigInterface
     *
     * @deprecated Use ViewHelpers::getGroupingMode($environment) instead!
     */
    protected function getGroupingMode()
    {
        return ViewHelpers::getGroupingMode($this->environment);
    }
}

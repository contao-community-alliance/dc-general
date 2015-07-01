<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     cogizz <c.boelter@cogizz.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Martin Treml <github@r2pi.net>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\SelectHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ShowHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\DeleteModelHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeleteableException;
use ContaoCommunityAlliance\DcGeneral\Controller\Ajax3X;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
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
            case 'select':
                $handler = new SelectHandler();
                $handler->handleEvent($event);

                // If no redirect happens, use showAll
                $name = 'showAll';
                // No break here

            case 'copy':
            case 'create':
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
                $this->toggle($action, $name);
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
    public function formatCurrentValue($field, ModelInterface $model, $groupMode, $groupLength)
    {
        $property = $this->getDataDefinition()->getPropertiesDefinition()->getProperty($field);

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
     * @return array
     */
    protected function getSelectButtons()
    {
        $definition      = $this->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        $buttons         = array();

        if ($basicDefinition->isDeletable()) {
            $buttons['delete'] = sprintf(
                '<input ' .
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
        $handler = new Ajax3X();
        $handler->executePostActions(new DcCompat($this->getEnvironment()));
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException This method os not in use anymore.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(Action $action)
    {
        throw new \RuntimeException('I should not be here! :-\\');
    }

    /**
     * {@inheritdoc}
     *
     * This performs redirectHome() upon successful execution and throws an exception otherwise.
     *
     * @throws DcGeneralRuntimeException When invalid parameters are encountered.
     */
    public function paste(Action $action)
    {
        $environment   = $this->getEnvironment();
        $controller    = $environment->getController();
        $input         = $environment->getInputProvider();
        $clipboard     = $environment->getClipboard();
        $source        = $input->getParameter('source')
            ? IdSerializer::fromSerialized($input->getParameter('source'))
            : null;
        $after         = $input->getParameter('after')
            ? IdSerializer::fromSerialized($input->getParameter('after'))
            : $input->getParameter('after');
        $into          = $input->getParameter('into')
            ? IdSerializer::fromSerialized($input->getParameter('into'))
            : null;
        $parentModelId = $input->getParameter('pid')
            ? IdSerializer::fromSerialized($input->getParameter('pid'))
            : null;
        $items         = array();

        $models = $controller->applyClipboardActions($source, $after, $into, $parentModelId, null, $items);

        if (!$source) {
            $clipboard
                ->clear()
                ->saveTo($environment);
        }

        /** @var ItemInterface[] $items */
        if (1 === count($items) && ItemInterface::CREATE === $items[0]->getAction()) {
            $model   = $models->get(0);
            $modelId = IdSerializer::fromModel($model);

            $addToUrlEvent = new AddToUrlEvent('act=edit&id=' . $modelId->getSerialized());
            $environment->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $addToUrlEvent);

            $redirectEvent = new RedirectEvent($addToUrlEvent->getUrl());
            $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_REDIRECT, $redirectEvent);

            return;
        }

        ViewHelpers::redirectHome($environment);
    }

    /**
     * {@inheritDoc}
     *
     * NOTE: This method redirects the user to the listing and therefore the script will be ended.
     *
     * @throws DcGeneralRuntimeException If the model to delete could not be loaded.
     */
    public function delete(Action $action)
    {
        $environment = $this->getEnvironment();
        $handler     = new DeleteModelHandler($environment);
        $modelId     = ModelId::fromSerialized($environment->getInputProvider()->getParameter('id'));

        try {
            $handler->process($modelId);
        } catch (EditOnlyModeException $e) {
            return $this->edit($action);
        } catch (NotDeleteableException $e) {
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

        ViewHelpers::redirectHome($this->environment);

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
     * {@inheritdoc}
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

        if (!$inputProvider->hasParameter('id') || !$inputProvider->getParameter('id')) {
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
     * {@inheritdoc}
     */
    public function enforceModelRelationship($model)
    {
        // No op in this base class but implemented in subclasses to enforce parent<->child relationship.
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When the model could not be found by the data provider.
     */
    public function edit(Action $action)
    {
        $environment   = $this->getEnvironment();
        $inputProvider = $environment->getInputProvider();
        $modelId       = ($inputProvider->hasParameter('id') && $inputProvider->getParameter('id'))
            ? IdSerializer::fromSerialized($inputProvider->getParameter('id'))
            : null;
        $dataProvider  = $environment->getDataProvider($modelId ? $modelId->getDataProviderName() : null);

        $this->checkRestoreVersion();

        if ($modelId && $modelId->getId()) {
            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
        } else {
            $model = $this->getEnvironment()->getController()->createEmptyModelWithDefaults();
        }

        if (!$model) {
            throw new DcGeneralRuntimeException('Could not retrieve model with id ' . $modelId->getSerialized());
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
        $editMask = new EditMask($this, $model, $originalModel, $preFunction, $postFunction, $this->breadcrumb());
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
     * {@inheritdoc}
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
     * @param Action $action The action being executed.
     *
     * @param string $name   The command name (default: toggle).
     *
     * @return string
     */
    public function toggle(Action $action, $name = 'toggle')
    {
        $environment   = $this->getEnvironment();
        $inputProvider = $environment->getInputProvider();

        if ($inputProvider->hasParameter('id') && $inputProvider->getParameter('id')) {
            $serializedId = IdSerializer::fromSerialized($inputProvider->getParameter('id'));
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
            ? $inputProvider->getParameter('state') == 1 ? '' : '1'
            : $inputProvider->getParameter('state') == 1 ? '1' : '';

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
        $config          = $this->getEnvironment()->getBaseConfigRegistry()->getBaseConfig();
        $manualSorting   = ViewHelpers::getManualSortingProperty($this->environment);

        if ($serializedPid = $environment->getInputProvider()->getParameter('pid')) {
            $pid = IdSerializer::fromSerialized($serializedPid);
        } else {
            $pid = null;
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
        if (
            ($mode == BasicDefinitionInterface::MODE_FLAT)
            || (($mode == BasicDefinitionInterface::MODE_PARENTEDLIST) && !$manualSorting)
        ) {
            $parameters['act'] = 'edit';
            // Add new button.
            if ($pid) {
                $parameters['pid'] = $pid->getSerialized();
            }
        } elseif (
            ($mode == BasicDefinitionInterface::MODE_PARENTEDLIST)
            || ($mode == BasicDefinitionInterface::MODE_HIERARCHICAL)
        ) {
            $filter = new Filter();
            $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
            if ($parentDataProviderName = $basicDefinition->getParentDataProvider()) {
                $filter->andParentIsFromProvider($parentDataProviderName);
            } else {
                $filter->andHasNoParent();
            }

            if ($environment->getClipboard()->isNotEmpty($filter)) {
                return null;
            }

            $parameters['act'] = 'create';

            if ($pid) {
                $parameters['pid'] = $pid->getSerialized();
            }
        }

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
        $parameters         = (array) $objCommand->getParameters();
        $extra              = (array) $objCommand->getExtra();
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

            $icon         = $extra['icon'];
            $iconDisabled = isset($extra['icon_disabled'])
                ? $extra['icon_disabled']
                : 'invisible.gif';

            $attributes = sprintf(
                'onclick="Backend.getScrollOffset(); return BackendGeneral.toggleVisibility(this, \'%s\', \'%s\');"',
                $icon,
                $iconDisabled
            );

            if ($objCommand->isInverse()
                ? $objModel->getProperty($objCommand->getToggleProperty())
                : !$objModel->getProperty($objCommand->getToggleProperty())
            ) {
                $extra['icon'] = $iconDisabled ?: 'invisible.gif';
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

        $model           = $event->getModel();
        $modelId         = IdSerializer::fromModel($model);
        $environment     = $event->getEnvironment();
        $controller      = $environment->getController();
        $clipboard       = $environment->getClipboard();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        $pasteAfterIsDisabled = $event->isPasteAfterDisabled();

        if (!$pasteAfterIsDisabled) {
            // pre-build filter, to fetch other items
            $filter = new Filter();
            $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
            if ($parentDataProviderName = $basicDefinition->getParentDataProvider()) {
                $filter->andParentIsFromProvider($parentDataProviderName);
            } else {
                $filter->andHasNoParent();
            }
            $filter->andModelIsNot($modelId);

            /*
             * FIXME to be discussed, allow pasting only in the same grouping
        }
        /** @var Filter $filter Prevent IDE from saying $filter may be undefined! ;-D * /

        if (!$pasteAfterIsDisabled) {
            // Determine if the grouping is the same
            $groupingMode = ViewHelpers::getGroupingMode($environment);

            if ($groupingMode) {
                $items  = $clipboard->fetch($filter);
                $models = $controller->getModelsFromClipboardItems($items);
                $propertyName = $groupingMode['property'];
                $propertyValue = $model->getProperty($propertyName);

                $pasteAfterIsDisabled = true;
                foreach ($models as $clipboardModel) {
                    if ($propertyValue === $clipboardModel->getProperty($propertyName)) {
                        // there exist at least one item, with the same grouping
                        $pasteAfterIsDisabled = false;
                        break;
                    }
                }
            }
        }

        if (!$pasteAfterIsDisabled) {
            */

            $pasteAfterIsDisabled = $clipboard->isEmpty($filter);
        }

        $strLabel = $this->translate('pasteafter.0', $model->getProviderName());
        if ($pasteAfterIsDisabled) {
            /** @var GenerateHtmlEvent $imageEvent */
            $imageEvent = $environment->getEventDispatcher()->dispatch(
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
        $imageEvent = $environment->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'pasteafter.gif',
                $strLabel,
                'class="blink"'
            )
        );

        $opDesc = $this->translate('pasteafter.1', $environment->getDataDefinition()->getName());
        if (strlen($opDesc)) {
            $title = sprintf($opDesc, $model->getId());
        } else {
            $title = sprintf('%s id %s', $strLabel, $model->getId());
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
        $environment     = $this->getEnvironment();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();
        $commands        = $this->getViewSection()->getModelCommands();
        $clipboard       = $environment->getClipboard();
        $dispatcher      = $environment->getEventDispatcher();

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        if ($parentDataProviderName = $basicDefinition->getParentDataProvider()) {
            $filter->andParentIsFromProvider($parentDataProviderName);
        } else {
            $filter->andHasNoParent();
        }

        if ($clipboard->isNotEmpty($filter)) {
            $circularIds = $clipboard->getCircularIds();
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

        if (ViewHelpers::getManualSortingProperty($this->environment)) {
            $clipboardIsEmpty = $clipboard->isEmpty($filter);

            if ($clipboardIsEmpty && BasicDefinitionInterface::MODE_HIERARCHICAL !== $basicDefinition->getMode()) {
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
            if (!$clipboardIsEmpty) {
                if ($clipboard->isCreate()) {
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

                $models = $this
                    ->environment
                    ->getController()
                    ->getModelsFromClipboard(
                        $parentDataProviderName
                        ? IdSerializer::fromValues($parentDataProviderName, null)
                        : null
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
                    ->setPasteAfterDisabled($clipboard->isCut() && $isCircular)
                    ->setPasteIntoDisabled($clipboard->isCut() && $isCircular)
                    ->setContainedModels($models);

                $this->getEnvironment()->getEventDispatcher()->dispatch(
                    sprintf('%s[%s]', $buttonEvent::NAME, $this->getEnvironment()->getDataDefinition()->getName()),
                    $buttonEvent
                );
                $this->getEnvironment()->getEventDispatcher()->dispatch($buttonEvent::NAME, $buttonEvent);

                $arrButtons['pasteafter'] = $this->renderPasteAfterButton($buttonEvent);
                if ($this->getDataDefinition()->getBasicDefinition()->getMode()
                    == BasicDefinitionInterface::MODE_HIERARCHICAL
                ) {
                    $arrButtons['pasteinto'] = $this->renderPasteIntoButton($buttonEvent);
                }
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

        $this->getEnvironment()->getEventDispatcher()->dispatch($event::NAME, $event);

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
     * Create an empty model using the default values from the definition.
     *
     * @return ModelInterface
     *
     * @deprecated Use Controller::createEmptyModelWithDefaults() instead!
     */
    protected function createEmptyModelWithDefaults()
    {
        return $this->environment->getController()->createEmptyModelWithDefaults();
    }
}

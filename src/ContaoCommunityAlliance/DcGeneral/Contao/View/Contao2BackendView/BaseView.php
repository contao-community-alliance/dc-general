<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2016 Contao Community Alliance.
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
 * @author     cogizz <c.boelter@cogizz.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Martin Treml <github@r2pi.net>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2016 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Template;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ShowHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\Ajax3X;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
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
                // If no redirect happens, we want to display the showAll action.
                $name = 'showAll';
                // No break here.
            case 'create':
            case 'paste':
            case 'move':
            case 'undo':
            case 'edit':
            case 'showAll':
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
     * Translate a string via the translator.
     *
     * @param string $path The path within the translation where the string can be found.
     *
     * @return string
     */
    protected function translateFallback($path)
    {
        $translator = $this->getEnvironment()->getTranslator();
        // Try via definition name as domain first.
        $value = $translator->translate($path, $this->getDataDefinition()->getName());
        if ($value !== $path) {
            return $value;
        }

        return $this->getEnvironment()->getTranslator()->translate($path);
    }

    /**
     * Add the value to the template.
     *
     * @param string    $name     Name of the value.
     *
     * @param mixed     $value    The value to add to the template.
     *
     * @param Template $template The template to add the value to.
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
     * @return string[]
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
        $this->getEnvironment()->getEventDispatcher()->dispatch(GetSelectModeButtonsEvent::NAME, $event);

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
        $template = new ContaoBackendViewTemplate($strTemplate);
        $template->setTranslator($this->getEnvironment()->getTranslator());

        return $template;
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
     * @throws \RuntimeException This method os not in use anymore.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function paste(Action $action)
    {
        throw new \RuntimeException('I should not be here! :-\\');
    }

    /**
     * {@inheritDoc}
     *
     * NOTE: This method redirects the user to the listing and therefore the script will be ended.
     *
     * @throws \RuntimeException If the is any error.
     */
    public function delete(Action $action)
    {
        throw new \RuntimeException('I should not be here! :-\\');
    }

    /**
     * {@inheritDoc}
     */
    public function move(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

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

        $modelId                 = ModelId::fromSerialized($inputProvider->getParameter('id'));
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
        $label       = $this->translateFallback($command->getLabel());
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
            ->setTitle($this->translateFallback($command->getDescription()));
        $environment->getEventDispatcher()->dispatch(GetGlobalButtonEvent::NAME, $buttonEvent);

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

        foreach ($globalOperations as $command) {
            if ($command->isDisabled()) {
                continue;
            }
            $buttons[$command->getName()] = $this->generateHeaderButton($command);
        }

        $buttonsEvent = new GetGlobalButtonsEvent($this->getEnvironment());
        $buttonsEvent->setButtons($buttons);
        $this->getEnvironment()->getEventDispatcher()->dispatch(GetGlobalButtonsEvent::NAME, $buttonsEvent);

        return '<div id="' . $strButtonId . '">' . implode('', $buttonsEvent->getButtons()) . '</div>';
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
    public function breadcrumb()
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
}

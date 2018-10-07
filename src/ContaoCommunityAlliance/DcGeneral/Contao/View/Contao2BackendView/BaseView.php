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
 * @author     cogizz <c.boelter@cogizz.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Martin Treml <github@r2pi.net>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Template;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ShowHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\Ajax3X;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BaseView.
 *
 * This class is the base class for the different backend view mode sub classes.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
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
        return [
            DcGeneralEvents::ACTION => ['handleAction', -100],
        ];
    }

    /**
     * Handle the given action.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     *
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

        if ('show' === $name) {
            $handler = new ShowHandler();
            $handler->handleEvent($event);

            return;
        }

        if (!\in_array($name, ['select', 'create', 'move', 'undo', 'edit', 'showAll'])) {
            return;
        }

        $response = \call_user_func_array(
            // No redirect happens for select, we want to display the showAll action.
            [$this, ('select' === $name) ? 'showAll' : $name],
            \array_merge([$action], $action->getArguments())
        );
        $event->setResponse($response);
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
     * @param string   $name     Name of the value.
     * @param mixed    $value    The value to add to the template.
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
     * @param ModelInterface $model       The model from which the value shall be taken from.
     * @param string         $groupMode   The grouping mode in use.
     * @param int            $groupLength The length of the value to use for grouping (only used when grouping mode is
     *                                    ListingConfigInterface::GROUP_CHAR).
     *
     * @return string
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
        $buttons         = [];

        $confirmMessage = \htmlentities(
            \sprintf(
                '<h2 class="tl_error">%s</h2>' .
                '<p></p>' .
                '<div class="tl_submit_container">' .
                '<input type="submit" name="close" class="%s" value="%s" onclick="%s">' .
                '</div>',
                \specialchars($this->translate('MSC.nothingSelect')),
                'tl_submit',
                \specialchars($this->translate('MSC.close')),
                'this.blur(); BackendGeneral.hideMessage(); return false;'
            )
        );
        $onClick        = 'BackendGeneral.confirmSelectOverrideEditAll(this, \'models[]\', \''
                          . $confirmMessage . '\'); return false;';

        $input = '<input type="submit" name="%s" id="%s" class="tl_submit" accesskey="%s" value="%s" onclick="%s">';

        if ($basicDefinition->isDeletable()) {
            $onClickDelete = \sprintf(
                'BackendGeneral.confirmSelectDeleteAll(this, \'%s\', \'%s\', \'%s\', \'%s\', \'%s\'); return false;',
                'models[]',
                $confirmMessage,
                \specialchars($this->translate('MSC.delAllConfirm')),
                \specialchars($this->translate('MSC.confirmOk')),
                \specialchars($this->translate('MSC.confirmAbort'))
            );

            $buttons['delete'] = \sprintf(
                $input,
                'delete',
                'delete',
                'd',
                \specialchars($this->translate('MSC.deleteSelected')),
                $onClickDelete
            );
        }

        $sortingProperty = ViewHelpers::getManualSortingProperty($this->getEnvironment());
        if ($sortingProperty && $basicDefinition->isEditable()) {
            $buttons['cut'] = \sprintf(
                $input,
                'cut',
                'cut',
                's',
                \specialchars($this->translate('MSC.moveSelected')),
                $onClick
            );
        }

        if ($basicDefinition->isCreatable()) {
            $buttons['copy'] = \sprintf(
                $input,
                'copy',
                'copy',
                'c',
                \specialchars($this->translate('MSC.copySelected')),
                $onClick
            );
        }

        if ($basicDefinition->isEditable()) {
            $buttons['override'] = \sprintf(
                $input,
                'override',
                'override',
                'v',
                \specialchars($this->translate('MSC.overrideSelected')),
                $onClick
            );

            $buttons['edit'] = \sprintf(
                $input,
                'edit',
                'edit',
                's',
                \specialchars($this->translate('MSC.editSelected')),
                $onClick
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
        return \count($this->getEnvironment()->getController()->getSupportedLanguages($mixId)) > 0;
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
        $this->addAjaxPropertyForEditAll();

        $handler = new Ajax3X();
        $handler->executePostActions(new DcCompat($this->getEnvironment()));
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException This method is not in use anymore.
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
     * @throws \RuntimeException This method is not in use anymore.
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
     * @throws \RuntimeException This method is not in use anymore.
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

        return \vsprintf($this->notImplMsg, 'move - Mode');
    }

    /**
     * {@inheritdoc}
     */
    public function undo(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        return \vsprintf($this->notImplMsg, 'undo - Mode');
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
     * @throws \RuntimeException This method is not in use anymore.
     */
    public function edit(Action $action)
    {
        throw new \RuntimeException('I should not be here! :-\\');
    }

    /**
     * {@inheritdoc}
     */
    public function showAll(Action $action)
    {
        if ($this->environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        return \sprintf(
            $this->notImplMsg,
            'showAll - Mode ' . $this->environment->getDataDefinition()->getBasicDefinition()->getMode()
        );
    }

    /**
     * Generate all buttons for the header of a view.
     *
     * @return string
     */
    protected function generateHeaderButtons()
    {
        $renderer = new GlobalButtonRenderer($this->environment);
        return $renderer->render();
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
    protected function panel($ignoredPanels = [])
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

        if (!\is_array($arrReturn) || !\count($arrReturn)) {
            return null;
        }

        $GLOBALS['TL_CSS'][] = 'system/modules/dc-general/html/css/generalBreadcrumb.css';

        $objTemplate = $this->getTemplate('dcbe_general_breadcrumb');
        $this->addToTemplate('elements', $arrReturn, $objTemplate);

        return $objTemplate->parse();
    }

    /**
     * Add the ajax property for edit all mode.
     *
     * @return void
     */
    private function addAjaxPropertyForEditAll()
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();

        if (('select' !== $inputProvider->getParameter('act'))
            && ('edit' !== $inputProvider->getParameter('select'))
            && ('edit' !== $inputProvider->getParameter('mode'))
        ) {
            return;
        }

        $originalProperty = $this->findOriginalPropertyByModelId($inputProvider->getValue('name'));
        if (null === $originalProperty) {
            return;
        }

        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();

        $propertyClass = \get_class($originalProperty);

        $property = new $propertyClass($inputProvider->getValue('name'));
        $property->setLabel($originalProperty->getLabel());
        $property->setDescription($originalProperty->getDescription());
        $property->setDefaultValue($originalProperty->getDefaultValue());
        $property->setExcluded($originalProperty->isExcluded());
        $property->setSearchable($originalProperty->isSearchable());
        $property->setFilterable($originalProperty->isFilterable());
        $property->setWidgetType($originalProperty->getWidgetType());
        $property->setExplanation($originalProperty->getExplanation());
        $property->setExtra($originalProperty->getExtra());

        $propertiesDefinition->addProperty($property);
    }

    /**
     * Find the original property by the modelId.
     *
     * @param string $propertyName The property name.
     *
     * @return PropertyInterface|null
     */
    private function findOriginalPropertyByModelId($propertyName)
    {
        if (null === $propertyName) {
            return null;
        }

        $inputProvider  = $this->getEnvironment()->getInputProvider();
        $sessionStorage = $this->getEnvironment()->getSessionStorage();

        $selectAction = $inputProvider->getParameter('select');

        $session = $sessionStorage->get($this->getEnvironment()->getDataDefinition()->getName() . '.' . $selectAction);

        $originalPropertyName = null;
        foreach ($session['models'] as $modelId) {
            if (null !== $originalPropertyName) {
                break;
            }

            $propertyNamePrefix = \str_replace('::', '____', $modelId) . '_';
            if ($propertyNamePrefix !== \substr($propertyName, 0, \strlen($propertyNamePrefix))) {
                continue;
            }

            $originalPropertyName = \substr($propertyName, \strlen($propertyNamePrefix));
        }

        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();
        if (!$propertiesDefinition->hasProperty($originalPropertyName)) {
            return null;
        }

        return $propertiesDefinition->getProperty($originalPropertyName);
    }
}

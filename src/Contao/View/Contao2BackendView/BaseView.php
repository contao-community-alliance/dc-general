<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Template;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ShowHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\Ajax3X;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BaseView.
 *
 * This class is the base class for the different backend view mode sub-classes.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class BaseView implements BackendViewInterface, EventSubscriberInterface
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * BaseView constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

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
     * @var EnvironmentInterface|null
     */
    protected $environment = null;

    /**
     * The panel container in use.
     *
     * @var PanelContainerInterface|null
     */
    protected $panel = null;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            DcGeneralEvents::ACTION => ['handleAction', -100]
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
        $GLOBALS['TL_CSS']['cca.dc-general.generalDriver'] = 'bundles/ccadcgeneral/css/generalDriver.css';

        $environment = $event->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $environment2 = $this->getEnvironment();
        assert($environment2 instanceof EnvironmentInterface);

        $definition2 = $environment2->getDataDefinition();
        assert($definition2 instanceof ContainerInterface);

        if (
            (null !== $event->getResponse())
            || $definition->getName()
                !== $definition2->getName()
        ) {
            return;
        }

        $action = $event->getAction();
        $name   = $action->getName();

        if ('show' === $name) {
            $handler = new ShowHandler($this->getScopeDeterminator());
            $handler->handleEvent($event);

            return;
        }

        if ('showAll' === $name) {
            $response = \call_user_func_array(
                [$this, $name],
                \array_values(\array_merge([$action], $action->getArguments()))
            );
            $event->setResponse($response);

            return;
        }

        if ('select' === $name) {
            $response = \call_user_func_array(
                [$this, $name],
                \array_merge([$action], $action->getArguments())
            );
            $event->setResponse($response);

            return;
        }

        if (!\in_array($name, ['create', 'move', 'undo', 'edit'])) {
            return;
        }

        $response = \call_user_func_array(
            [$this, $name],
            \array_merge([$action], $action->getArguments())
        );
        $event->setResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->removeSubscriber($this);

        $this->environment = $environment;
        $dispatcher->addSubscriber($this);

        return $this;
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
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        return $definition;
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
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        return $translator->translate($path, $section);
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
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        // Try via definition name as domain first.
        $value = $translator->translate($path, $this->getDataDefinition()->getName());
        if ($value !== $path) {
            return $value;
        }

        return $translator->translate($path);
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
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $backendView = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        assert($backendView instanceof Contao2BackendViewDefinitionInterface);

        return $backendView;
    }

    /**
     * Determine if the select mode is currently active or not.
     *
     * @return bool
     */
    protected function isSelectModeActive()
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        return 'select' === $inputProvider->getParameter('act');
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
     * @return string|null
     */
    public function formatCurrentValue($field, ModelInterface $model, $groupMode, $groupLength)
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        // No property? Get out!
        if (!$definition->getPropertiesDefinition()->hasProperty($field)) {
            return '-';
        }

        $event = new GetGroupHeaderEvent($environment, $model, $field, null, $groupMode, $groupLength);

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);
        $dispatcher->dispatch($event, $event::NAME);

        return $event->getValue();
    }

    /**
     * Retrieve a list of html buttons to use in the bottom panel (submit area) when in select mode.
     *
     * @return string[]
     */
    protected function getSelectButtons()
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $event = new GetSelectModeButtonsEvent($environment);
        $event->setButtons([]);

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);
        $dispatcher->dispatch($event, GetSelectModeButtonsEvent::NAME);

        return $event->getButtons();
    }

    /**
     * Determine if we are currently working in multi language mode.
     *
     * @param mixed $currentID The id of the current model.
     *
     * @return bool
     */
    protected function isMultiLanguage($currentID)
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $controller = $environment->getController();
        assert($controller instanceof ControllerInterface);

        return (bool) \count($controller->getSupportedLanguages($currentID));
    }

    /**
     * Create a new instance of ContaoBackendViewTemplate with the template file of the given name.
     *
     * @param string $name Name of the template to create.
     *
     * @return ContaoBackendViewTemplate
     */
    protected function getTemplate($name)
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        return (new ContaoBackendViewTemplate($name))->setTranslator($translator);
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
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        if (true === ($input->hasParameter('id'))) {
            // Redefine the parameter id if this isn´t model id conform.
            if (false === \strpos($input->getParameter('id'), '::')) {
                $modelId = new ModelId($input->getParameter('table'), $input->getParameter('id'));
                $input->setParameter('id', $modelId->getSerialized());
            }
            $modelId      = ModelId::fromSerialized($input->getParameter('id'));
            $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
            assert($dataProvider instanceof DataProviderInterface);

            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
        }

        $this->addAjaxPropertyForEditAll();

        $handler = new Ajax3X();
        $handler->executePostActions(new DcCompat($environment, ($model ?? null)));
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
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);


        if ($definition->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        return \vsprintf($this->notImplMsg, ['move - Mode']);
    }

    /**
     * {@inheritdoc}
     */
    public function undo(Action $action)
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        if ($definition->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        return \vsprintf($this->notImplMsg, ['undo - Mode']);
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
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        if ($definition->getBasicDefinition()->isEditOnlyMode()) {
            return $this->edit($action);
        }

        return \sprintf(
            $this->notImplMsg,
            'showAll - Mode ' . (string) ($definition->getBasicDefinition()->getMode() ?? '')
        );
    }

    /**
     * Generate all buttons for the header of a view.
     *
     * @return string
     */
    protected function generateHeaderButtons()
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        return (new GlobalButtonRenderer($environment))->render();
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
        return (new PanelRenderer($this))->render($ignoredPanels);
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
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $event = new GetBreadcrumbEvent($environment);
        $dispatcher->dispatch($event, $event::NAME);

        $elements = $event->getElements();

        if (!\count($elements)) {
            return '';
        }

        $GLOBALS['TL_CSS']['cca.dc-general.generalBreadcrumb'] = 'bundles/ccadcgeneral/css/generalBreadcrumb.css';

        return $this->getTemplate('dcbe_general_breadcrumb')
            ->set('elements', $elements)
            ->parse();
    }

    /**
     * Add the ajax property for edit all mode.
     *
     * @return void
     */
    private function addAjaxPropertyForEditAll()
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $inputProvider  = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if (
            ('select' !== $inputProvider->getParameter('act'))
            && ('edit' !== $inputProvider->getParameter('select'))
            && ('edit' !== $inputProvider->getParameter('mode'))
        ) {
            return;
        }

        $originalProperty = $this->findOriginalPropertyByModelId($inputProvider->getValue('name'));
        if (null === $originalProperty) {
            return;
        }

        $propertiesDefinition = $definition->getPropertiesDefinition();

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
     * @param string|null $propertyName The property name.
     *
     * @return PropertyInterface|null
     */
    private function findOriginalPropertyByModelId(?string $propertyName): ?PropertyInterface
    {
        if (null === $propertyName) {
            return null;
        }

        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $inputProvider  = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        $selectAction = $inputProvider->getParameter('select');

        /** @var array{models: list<string>} $session */
        $session = $sessionStorage->get($definition->getName() . '.' . $selectAction);

        $originalPropertyName = null;
        foreach ($session['models'] as $modelId) {
            if (null !== $originalPropertyName) {
                break;
            }

            $propertyNamePrefix = \str_replace('::', '____', $modelId) . '_';
            if (0 !== strpos($propertyName, $propertyNamePrefix)) {
                continue;
            }

            $originalPropertyName = \substr($propertyName, \strlen($propertyNamePrefix));
        }

        if (null === $originalPropertyName) {
            return null;
        }

        $propertiesDefinition = $definition->getPropertiesDefinition();
        if (!$propertiesDefinition->hasProperty($originalPropertyName)) {
            return null;
        }

        return $propertiesDefinition->getProperty($originalPropertyName);
    }
}

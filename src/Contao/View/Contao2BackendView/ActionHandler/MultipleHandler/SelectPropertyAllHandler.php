<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2025 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\MultipleHandler;

use Contao\Message;
use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\AbstractListShowAllHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\NoOpDataProvider;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * This class handles the rendering of list view "showAllProperties" actions.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SelectPropertyAllHandler extends AbstractListShowAllHandler
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * The template messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * {@inheritDoc}
     */
    public function handleEvent(ActionEvent $event)
    {
        if (
            !$this->getScopeDeterminator()->currentScopeIsBackend()
            || ('selectPropertyAll' !== $event->getAction()->getName())
        ) {
            return;
        }

        if (null !== $response = $this->process($event->getAction(), $event->getEnvironment())) {
            $event->setResponse($response);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function process(Action $action, EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $backendView = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        assert($backendView instanceof Contao2BackendViewDefinitionInterface);

        $backendView->getListingConfig()->setShowColumns(false);
        $dataDefinition->getBasicDefinition()->setMode(BasicDefinitionInterface::MODE_FLAT);

        return parent::process($action, $environment);
    }

    /**
     * Load the collection of fields.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return CollectionInterface
     *
     * @throws DcGeneralRuntimeException When no source has been defined.
     */
    protected function loadCollection(EnvironmentInterface $environment)
    {
        return $this->getCollection($this->getPropertyDataProvider($environment), $environment);
    }

    /**
     * Return the property data provider.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return NoOpDataProvider
     *
     * @throws DcGeneralRuntimeException When no source has been defined.
     */
    private function getPropertyDataProvider(EnvironmentInterface $environment): NoOpDataProvider
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $providerName = 'property.' . $definition->getName();

        $dataProvider = new NoOpDataProvider();
        $dataProvider->setBaseConfig(['name' => $providerName]);

        $this->setPropertyLabelFormatter($providerName, $environment);

        return $dataProvider;
    }

    /**
     * Set the label formatter for property data provider to the listing configuration.
     *
     * @param string               $providerName The provider name.
     * @param EnvironmentInterface $environment  The environment.
     *
     * @return void
     */
    private function setPropertyLabelFormatter(string $providerName, EnvironmentInterface $environment): void
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $properties = $definition->getPropertiesDefinition();

        $labelFormatter = (new DefaultModelFormatterConfig())
            ->setPropertyNames(['name', 'description'])
            ->setFormat('%s <span style="color:#b3b3b3; padding-left:3px">[%s]</span>');

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $this->getViewSection($definition)
            ->getListingConfig()
            ->setLabelFormatter($providerName, $labelFormatter);

        // If property name not exits create dummy property for it.
        foreach (['name', 'description'] as $dummyName) {
            if (!$properties->hasProperty($dummyName)) {
                $dummyProperty = new DefaultProperty($dummyName);
                $dummyProperty->setWidgetType('dummyProperty');

                $properties->addProperty($dummyProperty);
            }
        }
    }

    /**
     * Return the field collection for each property.
     *
     * @param DataProviderInterface $dataProvider The field data provider.
     * @param EnvironmentInterface  $environment  The environment.
     *
     * @return CollectionInterface
     */
    private function getCollection(
        DataProviderInterface $dataProvider,
        EnvironmentInterface $environment
    ): CollectionInterface {
        $collection = $dataProvider->getEmptyCollection();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        foreach ($definition->getPropertiesDefinition() as $property) {
            if (!$this->isPropertyAllowed($property, $environment)) {
                continue;
            }

            $model = $dataProvider->getEmptyModel();
            $model->setID($property->getName());
            $model->setProperty(
                'name',
                $this->translator->trans($property->getLabel(), [], $definition->getName())
            );
            $model->setProperty(
                'description',
                $this->translator->trans($property->getDescription(), [], $definition->getName())
            );

            $this->handlePropertyFileTree($property);
            $this->handlePropertyFileTreeOrder($property, $model);

            $collection->offsetSet($collection->count(), $model);
        }

        return $collection;
    }

    /**
     * Is property allowed for edit multiple.
     *
     * @param PropertyInterface    $property    The property.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function isPropertyAllowed(PropertyInterface $property, EnvironmentInterface $environment): bool
    {
        if (!$property->getWidgetType() || ('dummyProperty' === $property->getWidgetType())) {
            return false;
        }

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $extra = $property->getExtra();
        if (
            $this->isPropertyAllowedByEdit($extra, $environment)
            || $this->isPropertyAllowedByOverride($extra, $environment)
        ) {
            $inputProvider = $environment->getInputProvider();
            assert($inputProvider instanceof InputProviderInterface);

            $definition = $environment->getDataDefinition();
            assert($definition instanceof ContainerInterface);

            $modelName = $definition->getBasicDefinition()->getDataProvider();

            Message::addInfo(
                $translator->translate(
                    'not_allowed_property_info',
                    'dc-general',
                    [
                        '%property%' => $translator->translate($property->getLabel(), $modelName)
                            ?: $property->getName(),
                        '%mode%'     => $translator->translate(
                            $inputProvider->getParameter('mode') . 'Selected',
                            'dc-general'
                        )
                    ]
                )
            );

            return false;
        }

        return $this->isPropertyAllowedByIntersectProperties($property, $environment);
    }

    /**
     * Is property allowed by edit mode.
     *
     * @param array                $extra       The extra attributes.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function isPropertyAllowedByEdit(array $extra, EnvironmentInterface $environment): bool
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        return (true === ($extra['doNotEditMultiple'] ?? false))
               && ('edit' === $inputProvider->getParameter('mode'));
    }

    /**
     * Is property allowed by override mode.
     *
     * @param array                $extra       The extra attributes.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function isPropertyAllowedByOverride(array $extra, EnvironmentInterface $environment): bool
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        return ((true === ($extra['unique'] ?? false))
                || isset($extra['readonly'])
                || (true === ($extra['doNotOverrideMultiple'] ?? false)))
               && ('override' === $inputProvider->getParameter('mode'));
    }

    /**
     * Is property allowed by intersect properties.
     *
     * @param PropertyInterface    $property    The property.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function isPropertyAllowedByIntersectProperties(
        PropertyInterface $property,
        EnvironmentInterface $environment
    ): bool {
        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        $inputProvider  = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $session = $sessionStorage->get($dataDefinition->getName() . '.' . $inputProvider->getParameter('mode'));

        return \array_key_exists($property->getName(), $session['intersectProperties']);
    }

    /**
     * Handle property file tree.
     *
     * @param PropertyInterface $property The property.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function handlePropertyFileTree(PropertyInterface $property): void
    {
        if ('fileTree' !== $property->getWidgetType()) {
            return;
        }

        $extra = $property->getExtra();
        if (true === empty($extra['orderField'])) {
            return;
        }

        $script = '<script type="text/javascript">
                        $("%s").addEvent("change", function(ev) {
                            Backend.toggleCheckboxes(ev.target, "%s");
                            GeneralLogger.info("The file order property is checked " + $("%s").checked)
                        });
                    </script>';

        $mooScript =
            \sprintf(
                $script,
                'properties_' . $property->getName(),
                'properties_' . $extra['orderField'],
                'properties_' . $extra['orderField']
            );

        $GLOBALS['TL_MOOTOOLS']['cca.dc-general.fileTree-' . \md5($mooScript)] = $mooScript;
    }

    /**
     * Handle property file tree order.
     *
     * @param PropertyInterface $property The property.
     * @param ModelInterface    $model    The model.
     *
     * @return void
     */
    private function handlePropertyFileTreeOrder(PropertyInterface $property, ModelInterface $model): void
    {
        if ('fileTreeOrder' !== $property->getWidgetType()) {
            return;
        }

        $model->setMeta($model::CSS_ROW_CLASS, 'invisible');
    }

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
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $this->getViewSection($dataDefinition)->getListingConfig()->setShowColumns(false);

        parent::renderTemplate($template, $environment);

        $template
            ->set(
                'subHeadline',
                \sprintf(
                    '%s: %s',
                    $this->translate($inputProvider->getParameter('mode') . 'Selected', 'dc-general'),
                    $this->translate('edit_all_select_properties', 'dc-general')
                )
            )
            ->set('mode', 'none')
            ->set('floatRightSelectButtons', true)
            ->set('selectCheckBoxName', 'properties[]')
            ->set('selectCheckBoxIdPrefix', 'properties_');

        if (
            (null !== $template->get('action'))
            && (false !== \strpos($template->get('action'), 'select=properties'))
        ) {
            $template->set(
                'action',
                \str_replace(
                    'select=properties',
                    'select=' . ($inputProvider->getParameter('mode') ?? 'edit'),
                    $template->get('action')
                )
            );
        }

        if (\count($this->messages) > 0) {
            foreach (\array_keys($this->messages) as $messageType) {
                $template->set($messageType, $this->messages[$messageType]);
            }
        }
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
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $confirmMessage = \htmlentities(
            \sprintf(
                '<h2 class="tl_error">%s</h2>' .
                '<p></p>' .
                '<div class="tl_submit_container">' .
                '<input class="%s" value="%s" onclick="%s">' .
                '</div>',
                StringUtil::specialchars($this->translate('nothingSelect', 'dc-general')),
                'tl_submit',
                StringUtil::specialchars($this->translate('close', 'dc-general')),
                'BackendGeneral.hideMessage(); return false;'
            )
        );

        $onClick = 'BackendGeneral.confirmSelectOverrideEditAll(this, \'properties[]\', \'' .
                   $confirmMessage . '\'); return false;';

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $continueName = $inputProvider->getParameter('mode');

        return [
            'continue' => \sprintf(
                '<input type="submit" name="%s" id="%s" class="tl_submit" accesskey="%s" value="%s" onclick="%s">',
                $continueName,
                $continueName,
                'c',
                StringUtil::specialchars($this->translate('continue', 'dc-general')),
                $onClick
            )
        ];
    }

    /**
     * Check if the action should be handled.
     *
     * @param int    $mode   The list mode.
     * @param Action $action The action.
     *
     * @return bool
     */
    protected function wantToHandle($mode, Action $action)
    {
        $arguments = $action->getArguments();

        return 'properties' === $arguments['select'];
    }

    /**
     * Determine the template to use.
     *
     * @param array $groupingInformation The grouping information as retrieved via ViewHelpers::getGroupingMode().
     *
     * @return ContaoBackendViewTemplate
     */
    protected function determineTemplate($groupingInformation)
    {
        return $this->getTemplate('dcbe_general_listView');
    }
}

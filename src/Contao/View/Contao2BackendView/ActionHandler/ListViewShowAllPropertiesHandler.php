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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class handles the rendering of list view "showAllProperties" actions.
 */
class ListViewShowAllPropertiesHandler extends AbstractListShowAllHandler
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
        if (!$this->scopeDeterminator->currentScopeIsBackend()
            || ('showAll' !== $event->getAction()->getName())
            || ('properties' !== $event->getAction()->getArguments()['select'])
        ) {
            return;
        }

        $response = $this->process($event->getAction(), $event->getEnvironment());
        if (false !== $response) {
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function process(Action $action, EnvironmentInterface $environment)
    {
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $environment->getDataDefinition()->getBasicDefinition();
        $backendView     = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);

        $backendView->getListingConfig()->setShowColumns(false);

        $basicDefinition->setMode(BasicDefinitionInterface::MODE_FLAT);

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
    protected function getPropertyDataProvider(EnvironmentInterface $environment)
    {
        $providerName = 'property.' . $environment->getDataDefinition()->getName();

        $dataProvider = new NoOpDataProvider();
        $dataProvider->setBaseConfig(['name' => $providerName]);

        $this->setPropertyLabelFormatter($providerName, $environment);

        return $dataProvider;
    }

    /**
     * Set the label formatter for property data provider to the listing configuration.
     *
     * @param string $providerName The provider name.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    protected function setPropertyLabelFormatter($providerName, EnvironmentInterface $environment)
    {
        $properties = $environment->getDataDefinition()->getPropertiesDefinition();

        $labelFormatter = new DefaultModelFormatterConfig();
        $labelFormatter->setPropertyNames(['name', 'description']);
        $labelFormatter->setFormat('%s <span style="color:#b3b3b3; padding-left:3px">[%s]</span>');
        $this->getViewSection($environment->getDataDefinition())
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
     * Return the field collection for each properties.
     *
     * @param DataProviderInterface $dataProvider The field data provider.
     * @param EnvironmentInterface  $environment  The environment.
     *
     * @return CollectionInterface
     */
    protected function getCollection(DataProviderInterface $dataProvider, EnvironmentInterface $environment)
    {
        $properties = $environment->getDataDefinition()->getPropertiesDefinition();
        $collection = $dataProvider->getEmptyCollection();

        foreach ($properties as $property) {
            if (!$this->isPropertyAllowed($property, $environment)) {
                continue;
            }

            $model = $dataProvider->getEmptyModel();
            $model->setID($property->getName());
            $model->setProperty(
                'name',
                $property->getLabel() ?: $property->getName()
            );
            $model->setProperty(
                'description',
                $property->getDescription() ?: $property->getName()
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
    private function isPropertyAllowed(PropertyInterface $property, EnvironmentInterface $environment)
    {
        if (!$property->getWidgetType()
            || $property->getWidgetType() === 'dummyProperty'
        ) {
            return false;
        }

        $inputProvider = $environment->getInputProvider();
        $translator    = $environment->getTranslator();

        $extra = (array) $property->getExtra();
        if ($this->isPropertyAllowedByEdit($extra, $environment)
            || $this->isPropertyAllowedByOverride($extra, $environment)
        ) {
            Message::addInfo(
                \sprintf(
                    $translator->translate('MSC.not_allowed_property_info'),
                    $property->getLabel() ?: $property->getName(),
                    $translator->translate('MSC.' . $inputProvider->getParameter('mode') . 'Selected')
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
    private function isPropertyAllowedByEdit(array $extra, EnvironmentInterface $environment)
    {
        return (true === $extra['doNotEditMultiple'])
               && 'edit' === $environment->getInputProvider()->getParameter('mode');
    }

    /**
     * Is property allowed by override mode.
     *
     * @param array                $extra       The extra attributes.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function isPropertyAllowedByOverride(array $extra, EnvironmentInterface $environment)
    {
        return ((true === $extra['unique']) || $extra['readonly'] || (true === $extra['doNotOverrideMultiple']))
               && ('override' === $environment->getInputProvider()->getParameter('mode'));
    }

    /**
     * Is property allowed by intersect properties.
     *
     * @param PropertyInterface    $property    The property
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function isPropertyAllowedByIntersectProperties(
        PropertyInterface $property,
        EnvironmentInterface $environment
    ) {
        $sessionStorage = $environment->getSessionStorage();
        $inputProvider  = $environment->getInputProvider();
        $dataDefinition = $environment->getDataDefinition();

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
    private function handlePropertyFileTree(PropertyInterface $property)
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

        $GLOBALS['TL_MOOTOOLS'][] =
            \sprintf(
                $script,
                'properties_' . $property->getName(),
                'properties_' . $extra['orderField'],
                'properties_' . $extra['orderField']
            );
    }

    /**
     * Handle property file tree order.
     *
     * @param PropertyInterface $property The property.
     * @param ModelInterface    $model    The model.
     *
     * @return void
     */
    private function handlePropertyFileTreeOrder(PropertyInterface $property, ModelInterface $model)
    {
        if ('fileTreeOrder' !== $property->getWidgetType()) {
            return;
        }

        $model->setMeta($model::CSS_ROW_CLASS, 'invisible');
    }

    /**
     * Prepare the template.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ContaoBackendViewTemplate $template The template to populate.
     *
     * @return void
     */
    protected function renderTemplate(ContaoBackendViewTemplate $template, EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();

        $languageDomain  = 'contao_' . $environment->getDataDefinition()->getName();

        $this->getViewSection($environment->getDataDefinition())->getListingConfig()->setShowColumns(false);

        parent::renderTemplate($template, $environment);

        $template->set(
            'subHeadline',
            \sprintf(
                '%s: %s',
                $this->translate('MSC.' . $inputProvider->getParameter('mode') . 'Selected', $languageDomain),
                $this->translate('MSC.edit_all_select_properties', $languageDomain)
            )
        );
        $template->set('mode', 'none');
        $template->set('floatRightSelectButtons', true);
        $template->set('selectCheckBoxName', 'properties[]');
        $template->set('selectCheckBoxIdPrefix', 'properties_');

        if ((null !== $template->get('action'))
            && (false !== \strpos($template->get('action'), 'select=properties'))
        ) {
            $template->set('action', \str_replace('select=properties', 'select=edit', $template->get('action')));
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

        $languageDomain  = 'contao_' . $environment->getDataDefinition()->getName();

        $continueName = '';
        foreach (['override', 'edit'] as $subAction) {
            if (!$environment->getInputProvider()->hasValue($subAction)) {
                continue;
            }

            $continueName = $subAction;
        }

        $confirmMessage = \htmlentities(
            \sprintf(
                '<h2 class="tl_error">%s</h2>' .
                '<p></p>' .
                '<div class="tl_submit_container">' .
                '<input class="%s" value="%s" onclick="%s">' .
                '</div>',
                StringUtil::specialchars($this->translate('MSC.nothingSelect', $languageDomain)),
                'tl_submit',
                StringUtil::specialchars($this->translate('MSC.close', $languageDomain)),
                'BackendGeneral.hideMessage(); return false;'
            )
        );
        $onClick        = 'BackendGeneral.confirmSelectOverrideEditAll(this, \'properties[]\', \'' .
                          $confirmMessage . '\'); return false;';

        $input = '<input type="submit" name="%s" id="%s" class="tl_submit" accesskey="%s" value="%s" onclick="%s">';

        $buttons['continue'] = \sprintf(
            $input,
            $continueName,
            $continueName,
            'c',
            StringUtil::specialchars($this->translate('MSC.continue', $languageDomain)),
            $onClick
        );

        return $buttons;
    }

    /**
     * Check if the action should be handled.
     *
     * @param string $mode   The list mode.
     * @param Action $action The action.
     *
     * @return mixed
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

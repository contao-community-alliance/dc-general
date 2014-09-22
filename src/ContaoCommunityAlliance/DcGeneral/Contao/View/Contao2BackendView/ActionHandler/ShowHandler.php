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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;

/**
 * Handler class for handling the "show" action.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
 */
class ShowHandler extends AbstractHandler
{
    /**
     * Retrieve the model from the database or redirect to error page if model could not be found.
     *
     * @return ModelInterface|null
     */
    protected function getModel()
    {
        $environment  = $this->getEnvironment();
        $definition   = $environment->getDataDefinition();
        $modelId      = IdSerializer::fromSerialized($environment->getInputProvider()->getParameter('id'));
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $objDBModel   = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        if ($objDBModel) {
            return $objDBModel;
        }

        $environment->getEventPropagator()->propagate(
            ContaoEvents::SYSTEM_LOG,
            new LogEvent(
                sprintf(
                    'Could not find ID %s in %s.',
                    'DC_General show()',
                    $modelId->getId(),
                    $definition->getName()
                ),
                __CLASS__ . '::' . __FUNCTION__,
                TL_ERROR
            )
        );

        $environment->getEventPropagator()->propagate(
            ContaoEvents::CONTROLLER_REDIRECT,
            new RedirectEvent('contao/main.php?act=error')
        );

        return null;
    }

    /**
     * Calculate the label of a property to se in "show" view.
     *
     * @param PropertyInterface $property The property for which the label shall be calculated.
     *
     * @return string
     */
    protected function getPropertyLabel(PropertyInterface $property)
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
     * Get for a field the readable value.
     *
     * @param PropertyInterface $property The property to be rendered.
     *
     * @param ModelInterface    $model    The model from which the property value shall be retrieved from.
     *
     * @param mixed             $value    The value for the property.
     *
     * @return mixed
     */
    public function getReadableFieldValue(PropertyInterface $property, ModelInterface $model, $value)
    {
        $event = new RenderReadablePropertyValueEvent($this->getEnvironment(), $model, $property, $value);
        $this->getEnvironment()->getEventPropagator()->propagate(
            $event::NAME,
            $event,
            array(
                $this->getEnvironment()->getDataDefinition()->getName(),
                $property->getName()
            )
        );

        if ($event->getRendered() !== null) {
            return $event->getRendered();
        }

        return $value;
    }

    /**
     * Convert a model to it's labels and human readable values.
     *
     * @param ModelInterface       $model       The model to display.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array
     *
     * @throws DcGeneralRuntimeException When a property could not be retrieved.
     */
    protected function convertModel($model, $environment)
    {
        $definition = $environment->getDataDefinition();
        $properties = $definition->getPropertiesDefinition();
        $palette    = $definition->getPalettesDefinition()->findPalette($model);
        $values     = array();
        $labels     = array();
        // Show only allowed fields.
        foreach ($palette->getVisibleProperties($model) as $paletteProperty) {
            $property = $properties->getProperty($paletteProperty->getName());

            if (!$property) {
                throw new DcGeneralRuntimeException('Unable to retrieve property ' . $paletteProperty->getName());
            }

            // Make it human readable.
            $values[$paletteProperty->getName()] = $this->getReadableFieldValue(
                $property,
                $model,
                $model->getProperty($paletteProperty->getName())
            );

            $labels[$paletteProperty->getName()] = $this->getPropertyLabel($property);
        }

        return array(
            'labels' => $labels,
            'values' => $values
        );
    }

    /**
     * Get the headline for the template.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @param ModelInterface       $model       The model.
     *
     * @return string
     */
    protected function getHeadline($environment, $model)
    {
        $translator = $environment->getTranslator();
        $headline   = $translator->translate(
            'MSC.showRecord',
            $model->getProviderName(),
            array('ID ' . $model->getId())
        );

        if ($headline !== 'MSC.showRecord') {
            return $headline;
        }

        return $translator->translate(
            'MSC.showRecord',
            null,
            array('ID ' . $model->getId())
        );
    }

    /**
     * Handle the show event.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException  The error.
     */
    public function process()
    {
        $environment = $this->getEnvironment();
        if ($environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            $this->getEvent()->setResponse($environment->getView()->edit());

            return;
        }

        // Select language in data provider.
        $this->checkLanguage($environment);

        $translator   = $environment->getTranslator();
        $modelId      = IdSerializer::fromSerialized($environment->getInputProvider()->getParameter('id'));
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $model        = $this->getModel($environment);
        $data         = $this->convertModel($model, $environment);
        $headline     = $this->getHeadline($translator, $model);
        $template     = new ContaoBackendViewTemplate('dcbe_general_show');

        $template->set('headline', $headline)
            ->set('arrFields', $data['values'])
            ->set('arrLabels', $data['labels']);

        if (
            in_array(
                'ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface',
                class_implements(
                    $environment->getDataProvider(
                        $model->getProviderName()
                    )
                )
            )
        ) {
            /** @var MultiLanguageDataProviderInterface $dataProvider */
            $template
                ->set('languages', $environment->getController()->getSupportedLanguages($model->getId()))
                ->set('currentLanguage', $dataProvider->getCurrentLanguage())
                ->set('languageSubmit', specialchars($translator->translate('MSC.showSelected')))
                ->set('backBT', specialchars($translator->translate('MSC.backBT')));
        } else {
            $template->set('languages', null);
        }

        $this->getEvent()->setResponse($template->parse());
    }
}

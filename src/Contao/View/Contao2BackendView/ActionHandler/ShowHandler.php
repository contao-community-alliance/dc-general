<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * Handler class for handling the "show" action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShowHandler
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * ShowHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->setScopeDeterminator($scopeDeterminator);
    }

    /**
     * Handle the event to process the action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        $response = $this->process($event->getAction(), $event->getEnvironment());
        $event->setResponse($response);
    }

    /**
     * Retrieve the model from the database or redirect to error page if model could not be found.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return ModelInterface|null
     */
    protected function getModel(EnvironmentInterface $environment)
    {
        $modelId      = ModelId::fromSerialized($environment->getInputProvider()->getParameter('id'));
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        if ($model) {
            return $model;
        }

        $eventDispatcher = $environment->getEventDispatcher();

        $eventDispatcher->dispatch(
            new LogEvent(
                \sprintf(
                    'Could not find ID %s in %s. DC_General show()',
                    $modelId->getId(),
                    $environment->getDataDefinition()->getName()
                ),
                __CLASS__ . '::' . __FUNCTION__,
                TL_ERROR
            ),
            ContaoEvents::SYSTEM_LOG
        );

        $eventDispatcher->dispatch(new RedirectEvent('contao?act=error'), ContaoEvents::CONTROLLER_REDIRECT);

        return null;
    }

    /**
     * Calculate the label of a property to se in "show" view.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param PropertyInterface    $property    The property for which the label shall be calculated.
     *
     * @return string
     */
    protected function getPropertyLabel(EnvironmentInterface $environment, PropertyInterface $property)
    {
        $translator = $environment->getTranslator();

        $label = $translator->translate($property->getLabel(), $environment->getDataDefinition()->getName());
        if (!$label) {
            $label = $translator->translate('MSC.' . $property->getName());
        }

        if (\is_array($label)) {
            $label = $label[0];
        }

        if (!$label) {
            $label = $property->getName();
        }

        return $label;
    }

    /**
     * Convert a model to it's labels and human readable values.
     *
     * @param ModelInterface       $model       The model to display.
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
        $values     = [];
        $labels     = [];
        // Show only allowed fields.
        foreach ($palette->getVisibleProperties($model) as $paletteProperty) {
            if (!($property = $properties->getProperty($paletteProperty->getName()))) {
                throw new DcGeneralRuntimeException('Unable to retrieve property ' . $paletteProperty->getName());
            }

            // Make it human readable.
            $values[$paletteProperty->getName()] = ViewHelpers::getReadableFieldValue(
                $environment,
                $property,
                $model
            );

            $labels[$paletteProperty->getName()] = $this->getPropertyLabel($environment, $property);
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get the headline for the template.
     *
     * @param TranslatorInterface $translator The translator.
     * @param ModelInterface      $model      The model.
     *
     * @return string
     */
    protected function getHeadline(TranslatorInterface $translator, $model)
    {
        $headline = $translator->translate(
            'MSC.showRecord',
            $model->getProviderName(),
            ['ID ' . $model->getId()]
        );

        if ('MSC.showRecord' !== $headline) {
            return $headline;
        }

        return $translator->translate(
            'MSC.showRecord',
            null,
            ['ID ' . $model->getId()]
        );
    }

    /**
     * Handle the show event.
     *
     * @param Action               $action      The action which is handled.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    protected function process(Action $action, EnvironmentInterface $environment)
    {
        if ($environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $environment->getView()->edit($action);
        }

        $modelId      = ModelId::fromSerialized($environment->getInputProvider()->getParameter('id'));
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $translator   = $environment->getTranslator();
        $model        = $this->getModel($environment);
        $data         = $this->convertModel($model, $environment);

        $template = (new ContaoBackendViewTemplate('dcbe_general_show'))
            ->set('headline', $this->getHeadline($translator, $model))
            ->set('arrFields', $data['values'])
            ->set('arrLabels', $data['labels']);

        if ($dataProvider instanceof MultiLanguageDataProviderInterface) {
            $template
                ->set('languages', $environment->getController()->getSupportedLanguages($model->getId()))
                ->set('currentLanguage', $dataProvider->getCurrentLanguage())
                ->set('languageSubmit', $translator->translate('MSC.showSelected'))
                ->set('backBT', StringUtil::specialchars($translator->translate('MSC.backBT')));
        } else {
            $template->set('languages', null);
        }

        return $template->parse();
    }
}

<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface as CcaTranslator;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Contao\StringUtil;
use Contao\System;
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

use function array_merge;
use function array_values;
use function sprintf;

/**
 * Handler class for handling the "show" action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShowHandler
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * The token manager.
     *
     * @var CsrfTokenManagerInterface
     */
    private CsrfTokenManagerInterface $tokenManager;

    /**
     * The token name.
     *
     * @var string
     */
    private string $tokenName;

    /**
     * ShowHandler constructor.
     *
     * @param RequestScopeDeterminator       $scopeDeterminator The request mode determinator.
     * @param CsrfTokenManagerInterface|null $tokenManager      The token manager.
     * @param string|null                    $tokenName         The token name.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        ?CsrfTokenManagerInterface $tokenManager = null,
        ?string $tokenName = null
    ) {
        $this->setScopeDeterminator($scopeDeterminator);

        if (null === $tokenManager) {
            $tokenManager = System::getContainer()->get('security.csrf.token_manager');
            assert($tokenManager instanceof CsrfTokenManagerInterface);

            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the csrf token manager as 4th argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in DCG 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }
        if (null === $tokenName) {
            $tokenName = System::getContainer()->getParameter('contao.csrf_token_name');
            assert(\is_string($tokenName));

            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the csrf token name as 5th argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in DCG 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        $this->tokenManager = $tokenManager;
        $this->tokenName    = $tokenName;
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
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $modelId      = ModelId::fromSerialized($inputProvider->getParameter('id'));
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        if ($model) {
            return $model;
        }

        $eventDispatcher = $environment->getEventDispatcher();
        assert($eventDispatcher instanceof EventDispatcherInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $eventDispatcher->dispatch(
            new LogEvent(
                sprintf(
                    'Could not find ID %s in %s. DC_General show()',
                    $modelId->getId(),
                    $definition->getName()
                ),
                __CLASS__ . '::' . __FUNCTION__,
                'ERROR'
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
        assert($translator instanceof TranslatorInterface);

        $key = $property->getLabel();

        if ('' === $key) {
            throw new LogicException('Missing label for property ' . $property->getName());
        }

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $label = $translator->translate($key, $definition->getName());
        if ($label !== $key) {
            return $label;
        }

        $mscKey = 'MSC.' . $property->getName() . '.0';
        $label  = $translator->translate($mscKey);
        if ($label !== $mscKey) {
            return $label;
        }

        return $key;
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
    protected function convertModel(ModelInterface $model, EnvironmentInterface $environment): array
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $properties = $definition->getPropertiesDefinition();
        assert($properties instanceof PropertiesDefinitionInterface);

        $palette    = $definition->getPalettesDefinition()->findPalette($model);
        $values     = [
            'system'  => [],
            'visible' => []
        ];
        $labels     = [
            'system'  => [],
            'visible' => []
        ];

        // Add only visible properties.
        foreach ($palette->getVisibleProperties($model) as $paletteProperty) {
            $palettePropertyName = $paletteProperty->getName();
            if (!$properties->hasProperty($palettePropertyName)) {
                throw new DcGeneralRuntimeException('Unable to retrieve property ' . $palettePropertyName);
            }
            $visibleProperty = $properties->getProperty($palettePropertyName);

            // Make it human-readable.
            $values['visible'][$palettePropertyName] = ViewHelpers::getReadableFieldValue(
                $environment,
                $visibleProperty,
                $model
            );

            $labels['visible'][$palettePropertyName] =
                sprintf('%s [%s]', $this->getPropertyLabel($environment, $visibleProperty), $palettePropertyName);
        }

        // Add system column properties.
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if (isset($values['visible'][$propertyName])) {
                continue;
            }
            $values['system'][$propertyName] = $model->getProperty($propertyName);
            $labels['system'][$propertyName] =
                sprintf('%s [%s]', $this->getPropertyLabel($environment, $property), $propertyName);
        }

        return [
            'labels' => array_merge(...array_values($labels)),
            'values' => array_merge(...array_values($values))
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
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $view = $environment->getView();
        assert($view instanceof ViewInterface);

        if ($definition->getBasicDefinition()->isEditOnlyMode()) {
            return $view->edit($action);
        }

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $modelId      = ModelId::fromSerialized($inputProvider->getParameter('id'));
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $model = $this->getModel($environment);
        assert($model instanceof ModelInterface);

        $data = $this->convertModel($model, $environment);

        $template = (new ContaoBackendViewTemplate('dcbe_general_show'))
            ->set('headline', $this->getHeadline($translator, $model))
            ->set('arrFields', $data['values'])
            ->set('arrLabels', $data['labels']);

        $controller = $environment->getController();
        assert($controller instanceof ControllerInterface);

        if ($dataProvider instanceof MultiLanguageDataProviderInterface) {
            $template
                ->set('languages', $controller->getSupportedLanguages($model->getId()))
                ->set('currentLanguage', $dataProvider->getCurrentLanguage())
                ->set('languageSubmit', $translator->translate('MSC.showSelected'))
                ->set('backBT', StringUtil::specialchars($translator->translate('MSC.backBT')))
                ->set('REQUEST_TOKEN', $this->tokenManager->getToken($this->tokenName));
        } else {
            $template->set('languages', null);
        }

        return $template->parse();
    }
}

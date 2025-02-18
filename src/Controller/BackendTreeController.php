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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Kim Wormer <hallo@heartcodiert.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use Contao\Backend;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Picker\PickerBuilderInterface;
use Contao\CoreBundle\Picker\PickerInterface;
use Contao\Environment;
use Contao\StringUtil;
use Contao\Validator;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\Translator\TranslatorInterface as CcaTranslator;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_filter;
use function define;
use function explode;
use function is_array;

/**
 * Handles the backend tree.
 *
 * @Route("/contao/cca", defaults={"_scope" = "backend", "_token_check" = true})
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @psalm-suppress DeprecatedInterface
 * @psalm-suppress DeprecatedTrait
 */
class BackendTreeController implements ContainerAwareInterface
{
    /** @psalm-suppress DeprecatedTrait */
    use ContainerAwareTrait;

    /**
     * Handles the installation process.
     *
     * @return Response
     *
     * @Route("/generaltree", name="cca_dc_general_tree")
     */
    public function generalTreeAction()
    {
        return $this->runBackendTree($this->initializeAndExtractRequest());
    }

    /**
     * Handles the toggle process.
     *
     * @return Response
     *
     * @Route("/generaltree/toggle", name="cca_dc_general_tree_toggle")
     */
    public function generalTreeToggleAction()
    {
        return $this->runBackendTreeToggle($this->initializeAndExtractRequest());
    }

    /**
     * Handles the toggle process.
     *
     * @return Response
     *
     * @Route("/generaltree/breadcrumb", name="cca_dc_general_tree_breadcrumb")
     */
    public function generalTreeBreadCrumbAction()
    {
        return $this->runBackendTreeBreadCrumb($this->initializeAndExtractRequest());
    }

    /**
     * Handles the update process.
     *
     * @return Response
     *
     * @Route("/generaltree/update", name="cca_dc_general_tree_update")
     */
    public function generalTreeUpdateAction()
    {
        return $this->runBackendTreeUpdate($this->initializeAndExtractRequest());
    }

    private function initializeAndExtractRequest(): Request
    {
        $container = $this->container;
        assert($container instanceof SymfonyContainerInterface);

        $framework = $container->get('contao.framework');
        assert($framework instanceof ContaoFramework);

        /**
         * @psalm-suppress InternalMethod
         * @var Controller $controller
         */
        $controller = $framework->getAdapter(Controller::class);
        $controller->loadLanguageFile('default');

        $requestStack = $container->get('request_stack');
        assert($requestStack instanceof RequestStack);

        $currentRequest = $requestStack->getCurrentRequest();
        assert($currentRequest instanceof Request);

        return $currentRequest;
    }

    private function getTranslator(): TranslatorInterface
    {
        $container = $this->container;
        assert($container instanceof SymfonyContainerInterface);
        $translator = $container->get('translator');
        assert($translator instanceof TranslatorInterface);

        return  $translator;
    }

    /**
     * Run the controller and parse get the response template.
     *
     * @param Request $request The request.
     *
     * @return Response
     *
     * @throws InvalidArgumentException No picker was given here.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function runBackendTree(Request $request)
    {
        [$value, $treeSelector] = $this->getTemplateData($request);

        $container = $this->container;
        assert($container instanceof SymfonyContainerInterface);

        $template = new ContaoBackendViewTemplate('be_main');
        $template
            ->set('isPopup', true)
            ->set('main', $treeSelector->generatePopup())
            ->set('theme', Backend::getTheme())
            ->set('base', Environment::get('base'))
            ->set('language', $container->get('request_stack')->getCurrentRequest()->getLocale())
            ->set(
                'title',
                StringUtil::specialchars(
                    $this->getTranslator()->trans(
                        'treePicker',
                        ['%table%' => $treeSelector->foreignTable],
                        'dc-general'
                    )
                )
            )
            ->set('charset', 'utf-8')
            ->set('addSearch', $treeSelector->searchField)
            ->set('search', $this->getTranslator()->trans('search', [], 'dc-general'))
            ->set('action', StringUtil::ampersand($request->getUri()))
            ->set('value', $value)
            ->set('manager', $this->getTranslator()->trans('treepickerManager', [], 'dc-general'))
            ->set('breadcrumb', $GLOBALS['TL_DCA'][$treeSelector->foreignTable]['list']['sorting']['breadcrumb'] ?? '');

        return $template->getResponse();
    }

    /**
     * Run the controller and parse get the response template.
     *
     * @param Request $request The request.
     *
     * @return Response
     *
     * @throws InvalidArgumentException No picker was given here.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function runBackendTreeBreadCrumb(Request $request)
    {
        [, $treeSelector] = $this->getTemplateData($request);

        $message = '<stong style="display: table; margin: 20px auto;">
                           The bread crumb method isn\'t implement yet.
                    </stong>';

        $treeSelector->generatePopup();
        $template = new ContaoBackendViewTemplate('be_main');
        $template
            ->set('isPopup', true)
            ->set('main', $message)
            ->set('theme', Backend::getTheme())
            ->set('base', Environment::get('base'))
            ->set('language', $GLOBALS['TL_LANGUAGE'])
            ->set(
                'title',
                StringUtil::specialchars($this->getTranslator()->trans('treepickerManager', [], 'dc-general'))
            )
            ->set('charset', $this->getTranslator()->trans('characterSet', [], 'dc-general'))
            ->set('search', $this->getTranslator()->trans('search', [], 'dc-general'))
            ->set('action', StringUtil::ampersand($request->getUri()))
            ->set('manager', $this->getTranslator()->trans('treepickerManager', [], 'dc-general'));

        return $template->getResponse();
    }

    /**
     * Run the controller and parse get the response template.
     *
     * @param Request $request The request.
     *
     * @return Response
     *
     * @throws InvalidArgumentException No picker was given here.
     */
    private function runBackendTreeToggle(Request $request)
    {
        [, $treeSelector] = $this->getTemplateData($request);

        $buffer = $treeSelector->generateAjax();

        $response = new Response($buffer);
        $response->headers->set('Content-Type', 'txt/html; charset=' . Config::get('characterSet'));

        return $response;
    }

    /**
     * Run the controller and parse get the response template.
     *
     * @param Request $request The request.
     *
     * @return Response
     *
     * @throws BadRequestHttpException This request isn`t from type ajax.
     * @throws BadRequestHttpException No picker was given here.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function runBackendTreeUpdate(Request $request)
    {
        if ((false === (bool) $request->request->count()) && (false === $request->isXmlHttpRequest())) {
            throw new BadRequestHttpException('This request isn`t from type ajax.');
        }

        [$value, , $picker] = $this->getTemplateData($request, true);

        $modelId = ModelId::fromSerialized($picker->getConfig()->getExtra('modelId'));

        $container = $this->container;
        assert($container instanceof SymfonyContainerInterface);
        $translator = $container->get('cca.translator.contao_translator');
        assert($translator instanceof CcaTranslator);

        $dispatcher = $container->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        $factory = new DcGeneralFactory();
        $general = $factory
            ->setContainerName($modelId->getDataProviderName())
            ->setTranslator($translator)
            ->setEventDispatcher($dispatcher)
            ->createDcGeneral();

        $dataProvider = $general->getEnvironment()->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        if (!($model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId())))) {
            $model = $dataProvider->getEmptyModel();
        }

        if (is_array($value)) {
            $values = [];
            // Clean keys they have empty value.
            foreach ($value as $index => $val) {
                if (
                    empty($val)
                    // The first key entry has the value on, if the checkbox for all checked.
                    || ((0 === $index) && ('on' === $val))
                ) {
                    continue;
                }

                $values[] = $val;
            }

            $value = $values;
        }

        $propertyName   = $picker->getConfig()->getExtra('propertyName');
        $propertyValues = new PropertyValueBag();
        $propertyValues->setPropertyValue($propertyName, $value);

        $controller = $general->getEnvironment()->getController();
        assert($controller instanceof ControllerInterface);

        $controller->updateModelFromPropertyBag($model, $propertyValues);

        $widgetManager = new ContaoWidgetManager($general->getEnvironment(), $model);
        $buffer        = $widgetManager->renderWidget($propertyName, false, $propertyValues);

        $response = new Response($buffer);
        $response->headers->set('Content-Type', 'txt/html; charset=' . (Config::get('characterSet') ?? ''));

        return $response;
    }

    /**
     * @param Request $request          The request to obtain information from.
     * @param bool    $valueFromRequest Flag if the value shall be read from the request.
     *
     * @return array{0: string|list<string>, 1: TreePicker, 2: PickerInterface}
     */
    private function getTemplateData(Request $request, bool $valueFromRequest = false): array
    {
        if ('' === ($getPicker = (string) $request->query->get('picker'))) {
            throw new BadRequestHttpException('No picker was given here.');
        }
        $container = $this->container;
        assert($container instanceof SymfonyContainerInterface);
        $pickerBuilder = $container->get('contao.picker.builder');
        assert($pickerBuilder instanceof PickerBuilderInterface);
        $picker = $pickerBuilder->createFromData($getPicker);
        assert($picker instanceof PickerInterface);
        $treeSelector = $this->prepareTreeSelector($picker);
        //$session = $container->get('session');
        $requestStack = $container->get('request_stack');
        assert($requestStack instanceof RequestStack);
        $session = $requestStack->getSession();
        assert($session instanceof SessionInterface);
        $sessionBag = $session->getBag('contao_backend');
        assert($sessionBag instanceof AttributeBagInterface);
        $value = $picker->getConfig()->getValue();
        if ($valueFromRequest) {
            if (null !== $reqValue = $request->request->get('value')) {
                $reqValue = (string) $reqValue;
            }
            $value = $treeSelector->widgetToValue($reqValue);
        }

        $sessionBag->set($treeSelector->getSearchSessionKey(), $value);

        return [
            $sessionBag->get($treeSelector->getSearchSessionKey()),
            $treeSelector,
            $picker
        ];
    }

    /**
     * Prepare the tree selector.
     *
     * @param PickerInterface $picker The picker.
     *
     * @return TreePicker
     *
     * @throws InvalidArgumentException If invalid characters in the data provider name or property name.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function prepareTreeSelector(PickerInterface $picker)
    {
        $modelId = ModelId::fromSerialized($picker->getConfig()->getExtra('modelId'));

        if (Validator::isInsecurePath($table = $modelId->getDataProviderName())) {
            throw new InvalidArgumentException('The table name contains invalid characters');
        }

        if (Validator::isInsecurePath($field = $picker->getConfig()->getExtra('propertyName'))) {
            throw new InvalidArgumentException('The field name contains invalid characters');
        }

        $container = $this->container;
        assert($container instanceof SymfonyContainerInterface);

        //$session = $container->get('session');
        $requestStack = $container->get('request_stack');
        assert($requestStack instanceof RequestStack);
        $session = $requestStack->getSession();
        assert($session instanceof SessionInterface);

        $sessionBag = $session->getBag('contao_backend');
        assert($sessionBag instanceof AttributeBagInterface);

        // Define the current ID.
        define('CURRENT_ID', ($table ? $sessionBag->get('CURRENT_ID') : $modelId->getId()));

        $translator = $container->get('cca.translator.contao_translator');
        assert($translator instanceof CcaTranslator);

        $dispatcher = $container->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        $itemContainer = (new DcGeneralFactory())
            ->setContainerName($modelId->getDataProviderName())
            ->setTranslator($translator)
            ->setEventDispatcher($dispatcher)
            ->createDcGeneral();

        $definition = $itemContainer->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        // Merge with the information from the data container.
        $property = $definition
            ->getPropertiesDefinition()
            ->getProperty($picker->getConfig()->getExtra('propertyName'));

        $information = (array) ($GLOBALS['TL_DCA'][$table]['fields'][$field] ?? []);
        if (!isset($information['eval'])) {
            $information['eval'] = array();
        }
        $information['eval'] = array_merge($property->getExtra(), $information['eval']);

        $dcCompat = new DcCompat($itemContainer->getEnvironment());
        /** @var class-string<TreePicker> $class */
        $class = $GLOBALS['BE_FFL']['DcGeneralTreePicker'];
        /** @psalm-suppress UnsafeInstantiation - No other way to instantiate. */
        $treeSelector = new $class(
            Widget::getAttributesFromDca(
                $information,
                $field,
                array_filter(explode(',', $picker->getConfig()->getValue())),
                $field,
                $table,
                $dcCompat
            ),
            $dcCompat
        );

        $treeSelector->id = 'tl_listing';

        return $treeSelector;
    }
}

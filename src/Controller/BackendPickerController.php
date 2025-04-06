<?php

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\Picker\IdTranscodingPickerProviderInterface;
use ContaoCommunityAlliance\Translator\SymfonyTranslatorBridge;
use Contao\Backend;
use Contao\CoreBundle\Picker\PickerBuilderInterface;
use Contao\CoreBundle\Picker\PickerInterface;
use Contao\Environment;
use Contao\StringUtil;
use Contao\Validator;
use Contao\Widget;
use InvalidArgumentException;
use Knp\Menu\Renderer\RendererInterface;
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
use function explode;

/**
 * Handles the backend picker.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[Route("/contao/cca/dc-picker", defaults: ['_scope' => 'backend', '_token_check' => true])]
final readonly class BackendPickerController
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private EventDispatcherInterface $dispatcher,
        private PickerBuilderInterface $pickerBuilder,
        private RendererInterface $menuRenderer,
    ) {
    }

    /** Renders the tree */
    #[Route('/tree', name: 'cca_dc_general_picker_tree')]
    public function treeAction(): Response
    {
        return $this->runBackendTree($this->initializeAndExtractRequest());
    }

    /** Handles the toggle process. */
    #[Route('/tree/toggle', name: 'cca_dc_general_picker_toggle')]
    public function toggleAction(): Response
    {
        return $this->runBackendTreeToggle($this->initializeAndExtractRequest());
    }

    private function initializeAndExtractRequest(): Request
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        assert($currentRequest instanceof Request);

        return $currentRequest;
    }

    private function runBackendTree(Request $request): Response
    {
        [, $treeSelector, $picker] = $this->getTemplateData($request);

        $template = new ContaoBackendViewTemplate('be_main');
        $template
            ->set('isPopup', true)
            ->set('main', $treeSelector->generatePopup())
            ->set('theme', Backend::getTheme())
            ->set('language', $this->requestStack->getCurrentRequest()?->getLocale())
            ->set(
                'title',
                StringUtil::specialchars(
                    $this->translator->trans(
                        'treePicker',
                        ['%table%' => (string) $picker->getConfig()->getExtra('sourceName')],
                        'dc-general'
                    )
                )
            )
            ->set('charset', 'utf-8');

        $template->set('pickerMenu', $this->menuRenderer->render($picker->getMenu()));

        return $template->getResponse();
    }

    private function runBackendTreeToggle(Request $request): Response
    {
        [, $treeSelector] = $this->getTemplateData($request);

        $buffer = $treeSelector->generateAjax();

        $response = new Response($buffer);
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        return $response;
    }

    /** @return array{0: string|list<string>, 1: TreePicker, 2: PickerInterface} */
    private function getTemplateData(Request $request): array
    {
        if ('' === ($pickerConfig = (string) $request->query->get('picker'))) {
            throw new BadRequestHttpException('No picker was given here.');
        }

        $picker = $this->pickerBuilder->createFromData($pickerConfig);
        assert($picker instanceof PickerInterface);

        $treeSelector = $this->prepareTreeSelector($picker);

        $session = $this->requestStack->getSession();
        assert($session instanceof SessionInterface);
        $sessionBag = $session->getBag('contao_backend');
        assert($sessionBag instanceof AttributeBagInterface);
        $value = $picker->getConfig()->getValue();

        $sessionBag->set($treeSelector->getSearchSessionKey(), $value);

        return [
            $sessionBag->get($treeSelector->getSearchSessionKey()),
            $treeSelector,
            $picker
        ];
    }

    /** @throws InvalidArgumentException If invalid characters in the data provider name. */
    private function prepareTreeSelector(PickerInterface $picker): TreePicker
    {
        if (Validator::isInsecurePath($table = $picker->getConfig()->getExtra('sourceName'))) {
            throw new InvalidArgumentException('The table name contains invalid characters');
        }

        $session = $this->requestStack->getSession();
        assert($session instanceof SessionInterface);

        $sessionBag = $session->getBag('contao_backend');
        assert($sessionBag instanceof AttributeBagInterface);

        $itemContainer = (new DcGeneralFactory())
            ->setContainerName($table)
            ->setTranslator(new SymfonyTranslatorBridge($this->translator))
            ->setEventDispatcher($this->dispatcher)
            ->createDcGeneral();

        $definition = $itemContainer->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $dcCompat = new DcCompat($itemContainer->getEnvironment());
        $treeSelector = new TreePicker(
            Widget::getAttributesFromDca(
                [
                    'eval' => ['sourceName' => $table, 'idProperty' => 'id']
                ],
                'id',
                null,
                'id',
                $table,
                $dcCompat
            ),
            $dcCompat
        );
        $treeSelector->id = 'tl_listing';

        $pickerProvider = $picker->getCurrentProvider();
        if ($pickerProvider instanceof IdTranscodingPickerProviderInterface) {
            $treeSelector->setIdTranscoder($pickerProvider->createIdTranscoder($picker->getConfig()));
        }
        $treeSelector->value = array_filter(explode(',', $picker->getConfig()->getValue()));

        return $treeSelector;
    }
}

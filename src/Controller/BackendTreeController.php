<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use Contao\Backend;
use Contao\Config;
use Contao\CoreBundle\Picker\PickerInterface;
use Contao\Environment;
use Contao\StringUtil;
use Contao\Validator;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use http\Exception\BadQueryStringException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles the backend tree.
 *
 * @Route("/contao/cca", defaults={"_scope" = "backend", "_token_check" = true})
 */
class BackendTreeController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The DcGeneral Object.
     *
     * @var DcGeneral
     */
    private $itemContainer;

    /**
     * Handles the installation process.
     *
     * @return Response
     *
     * @Route("/generaltree", name="cca_dc_general_tree")
     */
    public function generalTreeAction()
    {
        return $this->runBackendTree($this->container->get('request_stack')->getCurrentRequest());
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
        return $this->runBackendTreeToggle($this->container->get('request_stack')->getCurrentRequest());
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
        return $this->runBackendTreeBreadCrumb($this->container->get('request_stack')->getCurrentRequest());
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
        return $this->runBackendTreeUpdate($this->container->get('request_stack')->getCurrentRequest());
    }

    /**
     * Run the controller and parse get the response template.
     *
     * @param Request $request The request.
     *
     * @return Response
     *
     * @throws \InvalidArgumentException No picker was given here.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function runBackendTree(Request $request)
    {
        if (null === ($request->query->get('picker'))) {
            throw new \InvalidArgumentException('No picker was given here.');
        }
        $picker = $this->container->get('contao.picker.builder')->createFromData($request->query->get('picker'));

        $this->container->get('contao.framework')->getAdapter(\Contao\Controller::class)->setStaticUrls();

        $treeSelector = $this->prepareTreeSelector($picker);

        $sessionBag = $this->container->get('session')->getBag('contao_backend');
        $sessionBag->set($treeSelector->getSearchSessionKey(), $picker->getConfig()->getValue());

        $template = new ContaoBackendViewTemplate('be_main');
        $template
            ->set('isPopup', true)
            ->set('main', $treeSelector->generatePopup())
            ->set('theme', Backend::getTheme())
            ->set('base', Environment::get('base'))
            ->set('language', $GLOBALS['TL_LANGUAGE'])
            ->set('title', StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['treepicker']))
            ->set('charset', $GLOBALS['TL_CONFIG']['characterSet'])
            ->set('addSearch', $treeSelector->searchField)
            ->set('search', $GLOBALS['TL_LANG']['MSC']['search'])
            ->set('action', \ampersand($request->getUri()))
            ->set('value', $sessionBag->get($treeSelector->getSearchSessionKey()))
            ->set('manager', $GLOBALS['TL_LANG']['MSC']['treepickerManager'])
            ->set('breadcrumb', $GLOBALS['TL_DCA'][$treeSelector->foreignTable]['list']['sorting']['breadcrumb']);

        return $template->getResponse();
    }

    /**
     * Run the controller and parse get the response template.
     *
     * @param Request $request The request.
     *
     * @return Response
     *
     * @throws \InvalidArgumentException No picker was given here.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function runBackendTreeBreadCrumb(Request $request)
    {
        if (null === ($request->query->get('picker'))) {
            throw new \InvalidArgumentException('No picker was given here.');
        }
        $picker = $this->container->get('contao.picker.builder')->createFromData($request->query->get('picker'));

        $treeSelector = $this->prepareTreeSelector($picker);
        $treeSelector->generatePopup();

        $sessionBag = $this->container->get('session')->getBag('contao_backend');
        $sessionBag->set($treeSelector->getSearchSessionKey(), $picker->getConfig()->getValue());

        $message = '<stong style="display: table; margin: 20px auto;">
                           The bread crumb method isn´ implement yet.
                    </stong>';

        $template = new ContaoBackendViewTemplate('be_main');
        $template
            ->set('isPopup', true)
            ->set('main', $message)
            ->set('theme', Backend::getTheme())
            ->set('base', Environment::get('base'))
            ->set('language', $GLOBALS['TL_LANGUAGE'])
            ->set('title', StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['treepicker']))
            ->set('charset', $GLOBALS['TL_CONFIG']['characterSet'])
            ->set('search', $GLOBALS['TL_LANG']['MSC']['search'])
            ->set('action', \ampersand($request->getUri()))
            ->set('manager', $GLOBALS['TL_LANG']['MSC']['treepickerManager']);

        return $template->getResponse();
    }

    /**
     * Run the controller and parse get the response template.
     *
     * @param Request $request The request.
     *
     * @return Response
     *
     * @throws \InvalidArgumentException No picker was given here.
     */
    private function runBackendTreeToggle(Request $request)
    {
        if (null === ($request->query->get('picker'))) {
            throw new \InvalidArgumentException('No picker was given here.');
        }
        $picker = $this->container->get('contao.picker.builder')->createFromData($request->query->get('picker'));

        $treeSelector = $this->prepareTreeSelector($picker);

        $sessionBag = $this->container->get('session')->getBag('contao_backend');
        $sessionBag->set($treeSelector->getSearchSessionKey(), $picker->getConfig()->getValue());

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
     * @throws BadQueryStringException No picker was given here.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function runBackendTreeUpdate(Request $request)
    {
        if ((false === (bool) $request->request->count())
            && (false === $request->isXmlHttpRequest())) {
            throw new BadRequestHttpException('This request isn`t from type ajax.');
        }

        if (null === ($request->query->get('picker'))) {
            throw new BadQueryStringException('No picker was given here.');
        }
        $picker = $this->container->get('contao.picker.builder')->createFromData($request->query->get('picker'));

        $treeSelector = $this->prepareTreeSelector($picker);

        $sessionBag = $this->container->get('session')->getBag('contao_backend');
        $sessionBag->set(
            $treeSelector->getSearchSessionKey(),
            $treeSelector->widgetToValue($request->request->get('value'))
        );

        $modelId = ModelId::fromSerialized($picker->getConfig()->getExtra('modelId'));

        $factory = new DcGeneralFactory();
        $general = $factory
            ->setContainerName($modelId->getDataProviderName())
            ->setTranslator($this->container->get('cca.translator.contao_translator'))
            ->setEventDispatcher($this->container->get('event_dispatcher'))
            ->createDcGeneral();

        $dataProvider = $general->getEnvironment()->getDataProvider();

        if (!($model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId())))) {
            $model = $dataProvider->getEmptyModel();
        }

        $widgetValue = $treeSelector->widgetToValue($request->request->get('value'));
        if (\is_array($widgetValue)) {
            $values = [];
            // Clean keys the have empty value.
            foreach ($widgetValue as $index => $value) {
                if (empty($value)
                    // The first key entry has the value on, if the checkbox for all checked.
                    || ((0 === $index) && ('on' === $value))
                ) {
                    continue;
                }

                $values[] = $value;
            }

            $widgetValue = $values;
        }

        $propertyValues = new PropertyValueBag();
        $propertyValues->setPropertyValue($picker->getConfig()->getExtra('propertyName'), $widgetValue);
        $general->getEnvironment()->getController()->updateModelFromPropertyBag($model, $propertyValues);

        $widgetManager = new ContaoWidgetManager($general->getEnvironment(), $model);
        $buffer        =
            $widgetManager->renderWidget($picker->getConfig()->getExtra('propertyName'), false, $propertyValues);

        $response = new Response($buffer);
        $response->headers->set('Content-Type', 'txt/html; charset=' . Config::get('characterSet'));

        return $response;
    }

    /**
     * Prepare the tree selector.
     *
     * @param PickerInterface $picker The picker.
     *
     * @return TreePicker
     *
     * @throws \InvalidArgumentException If invalid characters in the data provider name or property name.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function prepareTreeSelector(PickerInterface $picker)
    {
        $modelId = ModelId::fromSerialized($picker->getConfig()->getExtra('modelId'));

        if (Validator::isInsecurePath($table = $modelId->getDataProviderName())) {
            throw new \InvalidArgumentException('The table name contains invalid characters');
        }

        if (Validator::isInsecurePath($field = $picker->getConfig()->getExtra('propertyName'))) {
            throw new \InvalidArgumentException('The field name contains invalid characters');
        }

        $sessionBag = $this->container->get('session')->getBag('contao_backend');
        // Define the current ID.
        \define('CURRENT_ID', ($table ? $sessionBag->get('CURRENT_ID') : $modelId->getId()));

        $factory             = new DcGeneralFactory();
        $this->itemContainer = $factory
            ->setContainerName($modelId->getDataProviderName())
            ->setTranslator($this->container->get('cca.translator.contao_translator'))
            ->setEventDispatcher($this->container->get('event_dispatcher'))
            ->createDcGeneral();

        // Merge with the information from the data container.
        $property = $this
            ->itemContainer
            ->getEnvironment()
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($picker->getConfig()->getExtra('propertyName'));

        $information = (array) $GLOBALS['TL_DCA'][$table]['fields'][$field];
        if (!isset($information['eval'])) {
            $information['eval'] = array();
        }
        $information['eval'] = array_merge($property->getExtra(), $information['eval']);

        $treeSelector = new $GLOBALS['BE_FFL']['DcGeneralTreePicker'](
            Widget::getAttributesFromDca(
                $information,
                $field,
                \array_filter(\explode(',', $picker->getConfig()->getValue())),
                $field,
                $table,
                new DcCompat($this->itemContainer->getEnvironment())
            ),
            new DcCompat($this->itemContainer->getEnvironment())
        );

        $treeSelector->id = 'tl_listing';

        return $treeSelector;
    }
}

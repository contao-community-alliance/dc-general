<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Environment;
use Contao\Session;
use Contao\StringUtil;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $this->container->get('contao.framework')->initialize();

        return $this->runBackendTree($this->container->get('request_stack')->getCurrentRequest());
    }

    /**
     * Run the controller and parse the template.
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function runBackendTree(Request $request)
    {
        $template       = new BackendTemplate('be_picker');
        $template->main = '';

        // Ajax request.
        // @codingStandardsIgnoreStart - We need POST access here.
        if ($request->request->count() && $request->isXmlHttpRequest())
        {
            $ajax = $this->handleAjax($request);
        }

        if (\Validator::isInsecurePath($table = $request->query->get('table')))
        {
            throw new \InvalidArgumentException('The table name contains invalid characters');
        }

        if (\Validator::isInsecurePath($field = $request->query->get('field')))
        {
            throw new \InvalidArgumentException('The field name contains invalid characters');
        }

        // Define the current ID.
        // FIXME: this is really bad!
        define('CURRENT_ID', ($table ? Session::getInstance()->get('CURRENT_ID') : $request->query->get('id')));

        $factory             = new DcGeneralFactory();
        $this->itemContainer = $factory
            ->setContainerName($table)
            ->setTranslator($this->container->get('cca.translator.contao_translator'))
            ->setEventDispatcher($this->container->get('event_dispatcher'))
            ->createDcGeneral();

        $information = (array) $GLOBALS['TL_DCA'][$table]['fields'][$field];

        if (!isset($information['eval'])) {
            $information['eval'] = array();
        }

        // Merge with the information from the data container.
        $property = $this
            ->itemContainer
            ->getEnvironment()
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($field);
        $extra    = $property->getExtra();

        $information['eval'] = array_merge($extra, $information['eval']);

        /** @var \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker $objTreeSelector */
        $objTreeSelector = new $GLOBALS['BE_FFL']['DcGeneralTreePicker'](
            Widget::getAttributesFromDca(
                $information,
                $field,
                // FIXME: input is not filtered here - we need a wrapper service for this.
                array_filter(explode(',', $request->query->get('value'))),
                $field,
                $table,
                new DcCompat($this->itemContainer->getEnvironment())
            ),
            new DcCompat($this->itemContainer->getEnvironment())
        );
        // AJAX request.
        if (isset($ajax)) {
            $objTreeSelector->generateAjax();
            $ajax->executePostActions(new DcCompat($this->itemContainer->getEnvironment()));
        }

        $template->main        = $objTreeSelector->generatePopup();
        $template->theme       = Backend::getTheme();
        $template->base        = Environment::get('base');
        $template->language    = $GLOBALS['TL_LANGUAGE'];
        $template->title       = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['treepicker']);
        $template->charset     = $GLOBALS['TL_CONFIG']['characterSet'];
        $template->addSearch   = $objTreeSelector->searchField;
        $template->search      = $GLOBALS['TL_LANG']['MSC']['search'];
        $template->action      = ampersand($request->getUri());
        $template->value       = Session::getInstance()->get($objTreeSelector->getSearchSessionKey());
        $template->manager     = $GLOBALS['TL_LANG']['MSC']['treepickerManager'];
        $template->breadcrumb  = $GLOBALS['TL_DCA'][$objTreeSelector->foreignTable]['list']['sorting']['breadcrumb'];
        $template->managerHref = '';

        // Add the manager link.
        if ($objTreeSelector->managerHref) {
            $template->managerHref = 'contao/main.php?' . ampersand($objTreeSelector->managerHref) . '&amp;popup=1';
        }

        // Prevent debug output at all cost.
        $GLOBALS['TL_CONFIG']['debugMode'] = false;
        return $template->getResponse();
    }

    private function handleAjax(Request $request)
    {
        // FIXME: should become an own entry point or should get removed altogether!
        $ajax = new \Contao\Ajax($request->query->get('table'));
        $ajax->executePreActions();
        return $ajax;
    }
}

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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Ajax;
use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\Database;
use Contao\Environment;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\WidgetBuilder;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\Translator\Contao\LangArrayTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;

/**
 * Class TreeSelect.
 *
 * Back end tree picker for usage in generaltree.php.
 */
class TreeSelect
{
    /**
     * Current ajax object.
     *
     * @var object
     */
    protected $objAjax;

    /**
     * The DcGeneral Object.
     *
     * @var DcGeneral
     */
    protected $itemContainer;

    /**
     * Initialize the controller.
     *
     * Sequence is:
     * 1. Import the user.
     * 2. Call the parent constructor
     * 3. Authenticate the user
     * 4. Load the language files
     * DO NOT CHANGE THIS ORDER!
     */
    public function __construct()
    {
        BackendUser::getInstance();
        Config::getInstance();
        Database::getInstance();

        BackendUser::getInstance()->authenticate();

        System::loadLanguageFile('default');
        Backend::setStaticUrls();
    }

    /**
     * Run the controller and parse the template.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function run()
    {
        $environment    = $this->itemContainer->getEnvironment();
        $inputProvider  = $environment->getInputProvider();
        $sessionStorage = $environment->getSessionStorage();

        // Ajax request.
        // @codingStandardsIgnoreStart - We need POST access here.
        if ($_POST && Environment::get('isAjaxRequest')) // @codingStandardsIgnoreEnd
        {
            $ajax = new Ajax($inputProvider->getValue('action'));
            $ajax->executePreActions();
        }

        $inputTable = $inputProvider->getParameter('table');
        $inputField = $inputProvider->getParameter('field');
        $inputId    = $inputProvider->getParameter('id');

        // Define the current ID.
        \define('CURRENT_ID', ($inputTable ? $sessionStorage->get('CURRENT_ID') : $inputId));

        $dispatcher = System::getContainer()->get('event_dispatcher');

        $translator = new TranslatorChain();
        $translator->add(new LangArrayTranslator($dispatcher));

        $this->itemContainer = (new DcGeneralFactory())
            ->setContainerName($inputTable)
            ->setTranslator($translator)
            ->setEventDispatcher($dispatcher)
            ->createDcGeneral();

        $information = (array) $GLOBALS['TL_DCA'][$inputTable]['fields'][$inputField];

        if (!isset($information['eval'])) {
            $information['eval'] = [];
        }

        // Merge with the information from the data container.
        $property = $environment
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($inputField);
        $extra    = $property->getExtra();

        $information['eval'] = \array_merge($extra, $information['eval']);

        $property->setExtra(\array_merge($property->getExtra(), $information['eval']));

        $dataProvider = $environment->getDataProvider();
        $model        = $dataProvider->getEmptyModel();
        if ($inputProvider->getParameter('id')) {
            $modelId = ModelId::fromSerialized($inputProvider->getParameter('id'));
            $model   = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
        }

        /** @var \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker $treeSelector */
        $treeSelector        = (new WidgetBuilder($environment))->buildWidget($property, $model);
        $treeSelector->value = \array_filter(\explode(',', $inputProvider->getParameter('value')));

        // AJAX request.
        if (isset($ajax)) {
            $treeSelector->generateAjax();
            $ajax->executePostActions(new DcCompat($environment));
        }


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
            ->set('action', \ampersand(Environment::get('request')))
            ->set('value', $sessionStorage->get($treeSelector->getSearchSessionKey()))
            ->set('manager', $GLOBALS['TL_LANG']['MSC']['treepickerManager'])
            ->set('breadcrumb', $GLOBALS['TL_DCA'][$treeSelector->foreignTable]['list']['sorting']['breadcrumb'])
            ->set('managerHref', '');

        // Add the manager link.
        if ($treeSelector->managerHref) {
            $template
                ->set('managerHref', 'contao?' . \ampersand($treeSelector->managerHref) . '&amp;popup=1');
        }

        // Prevent debug output at all cost.
        $GLOBALS['TL_CONFIG']['debugMode'] = false;
        $template->output();
    }
}

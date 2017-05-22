<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2016 Contao Community Alliance.
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
 * @copyright  2013-2016 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Ajax;
use Contao\Backend;
use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Config;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\Session;
use Contao\System;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
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
        Session::getInstance();
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
        $template       = new BackendTemplate('be_picker');
        $template->main = '';

        // Ajax request.
        // @codingStandardsIgnoreStart - We need POST access here.
        if ($_POST && Environment::get('isAjaxRequest')) // @codingStandardsIgnoreEnd
        {
            $ajax = new Ajax(\Input::post('action'));
            $ajax->executePreActions();
        }

        $strTable = Input::get('table');
        $strField = Input::get('field');

        // Define the current ID.
        define('CURRENT_ID', ($strTable ? Session::getInstance()->get('CURRENT_ID') : Input::get('id')));

        $dispatcher = $GLOBALS['container']['event-dispatcher'];

        $translator = new TranslatorChain();
        $translator->add(new LangArrayTranslator($dispatcher));

        $factory             = new DcGeneralFactory();
        $this->itemContainer = $factory
            ->setContainerName($strTable)
            ->setTranslator($translator)
            ->setEventDispatcher($dispatcher)
            ->createDcGeneral();

        $information = (array) $GLOBALS['TL_DCA'][$strTable]['fields'][$strField];

        if (!isset($information['eval'])) {
            $information['eval'] = array();
        }

        // Merge with the information from the data container.
        $property = $this
            ->itemContainer
            ->getEnvironment()
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($strField);
        $extra    = $property->getExtra();

        $information['eval'] = array_merge($extra, $information['eval']);

        /** @var \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker $objTreeSelector */
        $objTreeSelector = new $GLOBALS['BE_FFL']['DcGeneralTreePicker'](
            Widget::getAttributesFromDca(
                $information,
                $strField,
                array_filter(explode(',', Input::get('value'))),
                $strField,
                $strTable,
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
        $template->title       = specialchars($GLOBALS['TL_LANG']['MSC']['treepicker']);
        $template->charset     = $GLOBALS['TL_CONFIG']['characterSet'];
        $template->addSearch   = $objTreeSelector->searchField;
        $template->search      = $GLOBALS['TL_LANG']['MSC']['search'];
        $template->action      = ampersand(Environment::get('request'));
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
        $template->output();
    }
}

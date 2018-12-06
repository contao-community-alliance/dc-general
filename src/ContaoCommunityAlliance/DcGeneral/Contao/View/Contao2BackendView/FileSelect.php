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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
use Contao\FileSelector;
use Contao\Input;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\Translator\Contao\LangArrayTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;

/**
 * Class FileSelect.
 *
 * Back end tree picker for usage in generalfile.php.
 */
class FileSelect
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
        $inputProvider = new InputProvider();

        $template       = new BackendTemplate('be_picker');
        $template->main = '';

        $this->runAjaxRequest();

        $modelId = ModelId::fromSerialized($inputProvider->getParameter('id'));

        // Define the current ID.
        define(
            'CURRENT_ID',
            ($modelId->getDataProviderName()
                ? Session::getInstance()->get('CURRENT_ID') : $inputProvider->getParameter('id'))
        );

        $this->setupItemContainer($modelId);

        $fileSelector = $this->prepareFileSelector($modelId);

        $template->main        = $fileSelector->generate();
        $template->theme       = Backend::getTheme();
        $template->base        = Environment::get('base');
        $template->language    = $GLOBALS['TL_LANGUAGE'];
        $template->title       = \specialchars($GLOBALS['TL_LANG']['MSC']['treepicker']);
        $template->charset     = $GLOBALS['TL_CONFIG']['characterSet'];
        $template->addSearch   = $fileSelector->searchField;
        $template->search      = $GLOBALS['TL_LANG']['MSC']['search'];
        $template->action      = \ampersand(Environment::get('request'));
        $template->value       = Session::getInstance()->get('file_selector_search');
        $template->manager     = $GLOBALS['TL_LANG']['MSC']['treepickerManager'];
        $template->managerHref = '';

        if ('tl_files' !== $inputProvider->getValue('do')
            && (null === $GLOBALS['TL_DCA']['tl_files']['list']['sorting']['breadcrumb'])
        ) {
            Backend::addFilesBreadcrumb('tl_files_picker');
        }
        $template->breadcrumb = $GLOBALS['TL_DCA']['tl_files']['list']['sorting']['breadcrumb'];

        $user = BackendUser::getInstance();
        // Add the manager link.
        if ($user->hasAccess('files', 'modules')) {
            $template->manager     = $GLOBALS['TL_LANG']['MSC']['fileManager'];
            $template->managerHref = 'contao/main.php?do=files&amp;popup=1';
        }

        if (Input::get('switch') && $user->hasAccess('page', 'modules')) {
            $template->switch     = $GLOBALS['TL_LANG']['MSC']['pagePicker'];
            $template->switchHref =
                \str_replace('contao/file.php', 'contao/page.php', \ampersand(Environment::get('request')));
        }

        // Prevent debug output at all cost.
        $GLOBALS['TL_CONFIG']['debugMode'] = false;
        $template->output();
    }

    /**
     * Get the active model.
     *
     * @param ModelIdInterface $modelId The model identifier.
     *
     * @return \ContaoCommunityAlliance\DcGeneral\Data\ModelInterface|null
     */
    private function getActiveModel(ModelIdInterface $modelId)
    {
        if (!Database::getInstance()->tableExists($modelId->getDataProviderName())) {
            return null;
        }
        $dataProvider = $this->itemContainer->getEnvironment()->getDataProvider();

        return $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
    }

    /**
     * Prepare the file selector.
     *
     * @param ModelIdInterface $modelId The model identifier.
     *
     * @return FileSelector
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function prepareFileSelector(ModelIdInterface $modelId)
    {
        $inputProvider = $this->itemContainer->getEnvironment()->getInputProvider();
        $propertyName  = $inputProvider->getParameter('field');
        $information   = (array) $GLOBALS['TL_DCA'][$modelId->getDataProviderName()]['fields'][$propertyName];

        if (!isset($information['eval'])) {
            $information['eval'] = [];
        }

        // Merge with the information from the data container.
        $property = $this
            ->itemContainer
            ->getEnvironment()
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($propertyName);
        $extra    = $property->getExtra();

        $information['eval'] = \array_merge($extra, $information['eval']);

        Session::getInstance()->set('filePickerRef', Environment::get('request'));

        $combat = new DcCompat($this->itemContainer->getEnvironment(), $this->getActiveModel($modelId), $propertyName);

        /** @var FileSelector $fileSelector */
        $fileSelector = new $GLOBALS['BE_FFL']['fileSelector'](
            Widget::getAttributesFromDca(
                $information,
                $propertyName,
                $this->prepareValuesForFileSelector($propertyName, $modelId, $combat),
                $propertyName,
                $modelId->getDataProviderName(),
                $combat
            ),
            $combat
        );
        if (isset($information['eval']['extensions'])) {
            $fileSelector->extensions = $information['eval']['extensions'];
        }

        // AJAX request.
        if (isset($ajax)) {
            $ajax->executePostActions($combat);
        }

        return $fileSelector;
    }

    /**
     * Prepare the values for the file selector.
     *
     * @param string           $propertyName The property name.
     * @param ModelIdInterface $modelId      The model identifier.
     * @param DcCompat         $combat       The data container compatibility.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function prepareValuesForFileSelector($propertyName, ModelIdInterface $modelId, DcCompat $combat)
    {
        $inputProvider = $this->itemContainer->getEnvironment()->getInputProvider();

        $fileSelectorValues = [];
        foreach (\array_filter(\explode(',', $inputProvider->getParameter('value'))) as $k => $v) {
            // Can be a UUID or a path
            if (Validator::isStringUuid($v)) {
                $fileSelectorValues[$k] = StringUtil::uuidToBin($v);
            }
        }

        if (\is_array($GLOBALS['TL_DCA'][$modelId->getDataProviderName()]['fields'][$propertyName]['load_callback'])) {
            $callbacks = $GLOBALS['TL_DCA'][$modelId->getDataProviderName()]['fields'][$propertyName]['load_callback'];
            foreach ($callbacks as $callback) {
                if (\is_array($callback)) {
                    $fileSelectorValues =
                        Callbacks::callArgs($callback, [$fileSelectorValues, $combat]);
                } elseif (\is_callable($callback)) {
                    $fileSelectorValues = $callback($fileSelectorValues, $combat);
                }
            }
        }

        return $fileSelectorValues;
    }

    /**
     * Setup the item container.
     *
     * @param ModelIdInterface $modelId The model identifier.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function setupItemContainer(ModelIdInterface $modelId)
    {
        $dispatcher = $GLOBALS['container']['event-dispatcher'];

        $translator = new TranslatorChain();
        $translator->add(new LangArrayTranslator($dispatcher));

        $factory             = new DcGeneralFactory();
        $this->itemContainer = $factory
            ->setContainerName($modelId->getDataProviderName())
            ->setTranslator($translator)
            ->setEventDispatcher($dispatcher)
            ->createDcGeneral();
    }

    /**
     * Run the ajax request if is determine for run.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @return void
     */
    private function runAjaxRequest()
    {
        // Ajax request.
        // @codingStandardsIgnoreStart - We need POST access here.
        if (!($_POST && Environment::get('isAjaxRequest'))) // @codingStandardsIgnoreEnd
        {
            return;
        }

        $ajax = new Ajax(Input::post('action'));
        $ajax->executePreActions();
    }
}

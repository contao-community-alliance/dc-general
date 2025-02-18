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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
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
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use ContaoCommunityAlliance\Translator\Contao\LangArrayTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function is_string;

/**
 * Class FileSelect.
 *
 * Back end tree picker for usage in generalfile.php.
 *
 * @deprecated This is deprecated since 2.1 and where removed in 3.0. Use the file tree widget instead.
 *
 * WARNING: This class is unusable since Contao 5.0 as various deprecated functionality has been removed in contao/core.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileSelect
{
    /**
     * The DcGeneral Object.
     *
     * @var DcGeneral|null
     */
    protected $itemContainer = null;

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
        /** @psalm-suppress UndefinedMethod */
        BackendUser::getInstance()->authenticate();

        System::loadLanguageFile('default');
        /** @psalm-suppress UndefinedMethod */
        Backend::setStaticUrls();
    }

    /**
     * Run the controller and parse the template.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function run()
    {
        $inputProvider = new InputProvider();

        $template = new BackendTemplate('be_picker');
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->main = '';

        $ajax = $this->runAjaxRequest();

        $modelId = ModelId::fromSerialized($inputProvider->getParameter('id'));

        $this->setupItemContainer($modelId);

        $itemContainer = $this->itemContainer;
        assert($itemContainer instanceof DcGeneral);

        $environment = $itemContainer->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        // Define the current ID.
        \define(
            'CURRENT_ID',
            ($modelId->getDataProviderName()
                ? $sessionStorage->get('CURRENT_ID') : $inputProvider->getParameter('id'))
        );

        $fileSelector = $this->prepareFileSelector($modelId, $ajax);

        /**
         * @psalm-suppress UndefinedMagicPropertyAssignment
         * @psalm-suppress UndefinedDocblockClass
         */
        $template->main = $fileSelector->generate();
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->theme = Backend::getTheme();
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->base = Environment::get('base');
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->language = $GLOBALS['TL_LANGUAGE'];
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->title = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['treepicker']);
        /**
         * @psalm-suppress UndefinedMagicPropertyAssignment
         * @psalm-suppress UndefinedDocblockClass
         */
        $template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
        /**
         * @psalm-suppress UndefinedMagicPropertyAssignment
         * @psalm-suppress UndefinedMagicPropertyFetch
         * @psalm-suppress UndefinedDocblockClass
         */
        $template->addSearch = $fileSelector->searchField;
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->search = $GLOBALS['TL_LANG']['MSC']['search'];
        $template->action = StringUtil::ampersand(Environment::get('request'));
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->value = $sessionStorage->get('file_selector_search');
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->manager = $GLOBALS['TL_LANG']['MSC']['treepickerManager'];
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->managerHref = '';

        if (
            'tl_files' !== $inputProvider->getValue('do')
            && (null === $GLOBALS['TL_DCA']['tl_files']['list']['sorting']['breadcrumb'])
        ) {
            Backend::addFilesBreadcrumb('tl_files_picker');
        }
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->breadcrumb = $GLOBALS['TL_DCA']['tl_files']['list']['sorting']['breadcrumb'];

        $user = BackendUser::getInstance();
        // Add the manager link.
        /** @psalm-suppress UndefinedMethod */
        if ($user->hasAccess('files', 'modules')) {
            /** @psalm-suppress UndefinedMagicPropertyAssignment */
            $template->manager = $GLOBALS['TL_LANG']['MSC']['fileManager'];
            /** @psalm-suppress UndefinedMagicPropertyAssignment */
            $template->managerHref = 'contao/main.php?do=files&amp;popup=1';
        }

        /**
         * @psalm-suppress UndefinedMethod
         * @psalm-suppress RiskyTruthyFalsyComparison
         */
        if (Input::get('switch') && $user->hasAccess('page', 'modules')) {
            /** @psalm-suppress UndefinedMagicPropertyAssignment */
            $template->switch = $GLOBALS['TL_LANG']['MSC']['pagePicker'];
            /** @psalm-suppress UndefinedMagicPropertyAssignment */
            $template->switchHref =
                \str_replace('contao/file.php', 'contao/page.php', StringUtil::ampersand(Environment::get('request')));
        }

        // Prevent debug output at all cost.
        $GLOBALS['TL_CONFIG']['debugMode'] = false;
        /** @psalm-suppress DeprecatedMethod */
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

        $itemContainer = $this->itemContainer;
        assert($itemContainer instanceof DcGeneral);

        $environment = $itemContainer->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        return $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
    }

    /**
     * Prepare the file selector.
     *
     * @param ModelIdInterface $modelId The model identifier.
     * @param Ajax|null        $ajax    The ajax request.
     *
     * @psalm-suppress DeprecatedClass
     * @psalm-suppress UndefinedDocblockClass
     *
     * @return FileSelector
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function prepareFileSelector(ModelIdInterface $modelId, Ajax $ajax = null)
    {
        $itemContainer = $this->itemContainer;
        assert($itemContainer instanceof DcGeneral);

        $environment = $itemContainer->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $propertyName = $inputProvider->getParameter('field');
        $information  = (array) $GLOBALS['TL_DCA'][$modelId->getDataProviderName()]['fields'][$propertyName];

        if (!isset($information['eval'])) {
            $information['eval'] = [];
        }

        $itemContainer = $this->itemContainer;
        assert($itemContainer instanceof DcGeneral);

        $definition = $itemContainer->getEnvironment();
        assert($definition instanceof ContainerInterface);

        // Merge with the information from the data container.
        $property = $definition->getPropertiesDefinition()->getProperty($propertyName);
        $extra    = $property->getExtra();

        $information['eval'] = \array_merge($extra, (array) $information['eval']);

        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);
        $sessionStorage->set('filePickerRef', Environment::get('request'));

        $combat = new DcCompat($itemContainer->getEnvironment(), $this->getActiveModel($modelId), $propertyName);

        /**
         * @var FileSelector $fileSelector
         *
         * @psalm-suppress DeprecatedClass
         * @psalm-suppress UndefinedDocblockClass
         */
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

        // AJAX request.
        if ($ajax) {
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
        $itemContainer = $this->itemContainer;
        assert($itemContainer instanceof DcGeneral);

        $environment = $itemContainer->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

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
     * Set up the item container.
     *
     * @param ModelIdInterface $modelId The model identifier.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function setupItemContainer(ModelIdInterface $modelId)
    {
        $dispatcher = System::getContainer()->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        $translator = new TranslatorChain();
        assert($translator instanceof TranslatorInterface);

        $translator->add(new LangArrayTranslator($dispatcher));

        $this->itemContainer = (new DcGeneralFactory())
            ->setContainerName($modelId->getDataProviderName())
            ->setTranslator($translator)
            ->setEventDispatcher($dispatcher)
            ->createDcGeneral();
    }

    /**
     * Run the ajax request if it is determining for run.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @return Ajax|null
     */
    private function runAjaxRequest()
    {
        // Ajax request.
        // @codingStandardsIgnoreStart - We need POST access here.
        if (!($_POST && Environment::get('isAjaxRequest'))) // @codingStandardsIgnoreEnd
        {
            return null;
        }

        $action = Input::post('action');
        assert(is_string($action));

        $ajax = new Ajax($action);
        $ajax->executePreActions();

        return $ajax;
    }
}

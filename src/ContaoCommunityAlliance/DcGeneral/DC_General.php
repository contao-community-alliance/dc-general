<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use ContaoCommunityAlliance\Translator\Contao\LangArrayTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class is only present so Contao can instantiate a backend properly as it needs a \DataContainer descendant.
 *
 * @package DcGeneral
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
// @codingStandardsIgnoreStart - Class is not in camelCase as Contao does not allow us to.
class DC_General extends \DataContainer implements DataContainerInterface
// @codingStandardsIgnoreEnd
{
    /**
     * The environment attached to this DC.
     *
     * @var EnvironmentInterface
     */
    protected $objEnvironment;

    /**
     * Create a new instance.
     *
     * @param string $strTable The table name.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct($strTable)
    {
        $strTable = $this->getTablenameCallback($strTable);

        $dispatcher = $this->getEventDispatcher();
        $dispatcher->addListener(PopulateEnvironmentEvent::NAME, array($this, 'handlePopulateEnvironment'), 4800);

        $translator = new TranslatorChain();
        $translator->add(new LangArrayTranslator($dispatcher));

        $factory = new DcGeneralFactory();

        $factory
            ->setContainerName($strTable)
            ->setEventDispatcher($dispatcher)
            ->setTranslator($translator)
            ->createDcGeneral();
        $dispatcher->removeListener(PopulateEnvironmentEvent::NAME, array($this, 'handlePopulateEnvironment'));

        // Load the clipboard.
        $this
            ->getEnvironment()
            ->getClipboard()
            ->loadFrom($this->getEnvironment());

        // Execute AJAX request, called from Backend::getBackendModule
        // we have to do this here, as otherwise the script will exit as it only checks for DC_Table and DC_File
        // derived classes.
        $this->checkAjaxCall();
    }

    /**
     * Retrieve the event dispatcher from the DIC.
     *
     * @return EventDispatcherInterface
     *
     * @throws \RuntimeException When the DIC or event dispatcher have not been correctly initialized.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getEventDispatcher()
    {
        $container = $GLOBALS['container'];

        if (!$container instanceof \Pimple) {
            throw new \RuntimeException('The dependency container Pimple has not been initialized correctly.');
        }

        $dispatcher = $container['event-dispatcher'];

        if (!$dispatcher instanceof EventDispatcherInterface) {
            throw new \RuntimeException('The dependency container Pimple has not been initialized correctly.');
        }

        return $dispatcher;
    }

    /**
     * Check if we have an ajax call currently and if so, execute the action accordingly.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function checkAjaxCall()
    {
        if (!empty($_POST)
            && (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
        ) {
            $this->getViewHandler()->handleAjaxCall();
        }
    }

    /**
     * Callback coming from the environment populator.
     *
     * This is used to get to know the environment here in the DC.
     * See the implementation in constructor and ExtendedLegacyDcaPopulator::populateCallback().
     *
     * @param PopulateEnvironmentEvent $event The event.
     *
     * @return void
     */
    public function handlePopulateEnvironment(PopulateEnvironmentEvent $event)
    {
        $this->objEnvironment = $event->getEnvironment();
    }

    /**
     * Call the table name callback.
     *
     * @param string $strTable The current table name.
     *
     * @return string New name of current table.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getTablenameCallback($strTable)
    {
        if (isset($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'])
            && is_array($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'])
        ) {
            foreach ($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'] as $callback) {
                $strCurrentTable = Callbacks::call($callback, $strTable, $this);

                if ($strCurrentTable != null) {
                    $strTable = $strCurrentTable;
                }
            }
        }

        return $strTable;
    }

    /**
     * Magic getter.
     *
     * @param string $name Name of the property to retrieve.
     *
     * @return mixed
     *
     * @throws DcGeneralRuntimeException If an invalid key is requested.
     *
     * @deprecated magic access is deprecated.
     */
    public function __get($name)
    {
        switch ($name) {
            case 'table':
                return $this->getEnvironment()->getDataDefinition()->getName();
            default:
        }

        throw new DcGeneralRuntimeException('Unsupported getter function for \'' . $name . '\' in DC_General.');
    }

    /**
     * Retrieve the name of the data container.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getEnvironment()->getDataDefinition()->getName();
    }

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     *
     * @throws DcGeneralRuntimeException When no environment has been set.
     */
    public function getEnvironment()
    {
        if (!$this->objEnvironment) {
            throw new DcGeneralRuntimeException('No Environment set.');
        }

        return $this->objEnvironment;
    }

    /**
     * Retrieve the view.
     *
     * @return ViewInterface
     */
    public function getViewHandler()
    {
        return $this->getEnvironment()->getView();
    }

    /**
     * Retrieve the controller.
     *
     * @return ControllerInterface
     */
    public function getControllerHandler()
    {
        return $this->getEnvironment()->getController();
    }

    /**
     * Delegate all calls directly to current view.
     *
     * @param string $name      Name of the method.
     *
     * @param array  $arguments Array of arguments.
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->getEnvironment()->getController()->handle(new Action($name, $arguments));
    }

    /**
     * Call the desired user action with an implicit fallback to the "showAll" action when none has been requested.
     *
     * @return string
     */
    protected function callAction()
    {
        $environment = $this->getEnvironment();
        $act         = $environment->getInputProvider()->getParameter('act');
        $action      = new Action($act ? $act : 'showAll');
        return $environment->getController()->handle($action);
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function copy()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function create()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function cut()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \listable
     *
     * @return string
     */
    public function delete()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function edit()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \editable
     *
     * @return string
     */
    public function move()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \listable
     *
     * @return string
     */
    public function show()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \listable
     *
     * @return string
     */
    public function showAll()
    {
        return $this->callAction();
    }

    /**
     * Do not use.
     *
     * @deprecated Only here as requirement of \listable
     *
     * @return string
     */
    public function undo()
    {
        return $this->callAction();
    }
}

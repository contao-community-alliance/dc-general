<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;

/**
 * Compatibility class for Contao to have the $GLOBALS['TL_DCA'] populated for pickers as those are hardcoded within
 * the handler files TL_ROOT/contao/*.php.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator
 */
class PickerCompatPopulator extends AbstractEventDrivenEnvironmentPopulator
{
    const PRIORITY = -10000;

    /**
     * Create a controller instance in the environment if none has been defined yet.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     *
     * @internal
     */
    public function populate(EnvironmentInterface $environment)
    {
        $this->populateFilePickers($environment);
    }

    /**
     * Populate the file picker $GLOBALS['TL_DCA'] to make the contao/file.php happy.
     *
     * The backend file contao/file.php is using hard coded direct array access on the TL_DCA array therefore we need to
     * dump all of this there.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function populateFilePickers(EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        $name       = $definition->getName();
        if (!isset($GLOBALS['TL_DCA'])) {
            $GLOBALS['TL_DCA'] = array();
        }

        if (!isset($GLOBALS['TL_DCA'][$name])) {
            $GLOBALS['TL_DCA'][$name] = array();
        }

        if (!isset($GLOBALS['TL_DCA'][$name]['fields'])) {
            $GLOBALS['TL_DCA'][$name]['fields'] = array();
        }

        $dca = &$GLOBALS['TL_DCA'][$name]['fields'];

        foreach ($environment->getDataDefinition()->getPropertiesDefinition()->getProperties() as $property) {
            if ($property->getWidgetType() == 'fileTree') {
                $dca[$property->getName()]['eval'] = $property->getExtra();
            }
        }
    }
}

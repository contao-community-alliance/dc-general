<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;

/**
 * Registry for default data provider configurations to only resolve them once.
 */
interface BaseConfigRegistryInterface extends EnvironmentAwareInterface
{
    /**
     * Retrieve the base data provider config for the current data definition.
     *
     * This includes parent filter when in parented list mode and the additional filters from the data definition.
     *
     * @param IdSerializer $parentId The optional parent to use.
     *
     * @return ConfigInterface
     */
    public function getBaseConfig(IdSerializer $parentId = null);
}

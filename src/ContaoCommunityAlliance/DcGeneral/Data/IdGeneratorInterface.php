<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Base definition for generating an uuid.
 */
interface IdGeneratorInterface
{
    /**
     * Generate an id.
     *
     * @return string
     */
    public function generate();

    /**
     * The amount of storage space an id of this type needs.
     *
     * @return int
     */
    public function getSize();
}

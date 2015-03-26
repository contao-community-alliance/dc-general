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

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Base definition for generating an uuid.
 */
interface IdGeneratorInterface
{

    /**
     * Pass the database to the idGenerator.
     *
     * @param \Database $database The current database instance.
     *
     * @return mixed
     */
    public function setDatabase(\Database $database);

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

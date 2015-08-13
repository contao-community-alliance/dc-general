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
 * Uuid generating by querying it from the Contao database class.
 */
class DatabaseUuidIdGenerator implements IdGeneratorInterface
{
    /**
     * The database to use.
     *
     * @var \Database
     */
    protected $database;

    /**
     * Create a new instance.
     *
     * @param \Database $database The database to use for uuid generating.
     */
    public function __construct(\Database $database)
    {
        $this->database = $database;
    }

    /**
     * Generate an id.
     *
     * @return string
     */
    public function generate()
    {
        return $this->database->query('SELECT UUID() as id')->first()->id;
    }

    /**
     * The amount of storage space an id of this type needs.
     *
     * @return int
     */
    public function getSize()
    {
        return 36;
    }
}

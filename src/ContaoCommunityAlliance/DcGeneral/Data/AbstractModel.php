<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Class AbstractModel.
 * Abstract base class for data provider models.
 * This class implements the setter and getter for meta data.
 *
 * @package DcGeneral\Data
 */
abstract class AbstractModel implements ModelInterface
{
    /**
     * A list with all meta information.
     *
     * @var array
     */
    protected $arrMetaInformation = array();

    /**
     * {@inheritdoc}
     */
    public function getMeta($strMetaName)
    {
        if (isset($this->arrMetaInformation[$strMetaName])) {
            return $this->arrMetaInformation[$strMetaName];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setMeta($strMetaName, $varValue)
    {
        $this->arrMetaInformation[$strMetaName] = $varValue;
    }
}

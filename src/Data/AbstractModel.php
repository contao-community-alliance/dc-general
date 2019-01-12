<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Class AbstractModel.
 * Abstract base class for data provider models.
 * This class implements the setter and getter for meta data.
 */
abstract class AbstractModel implements ModelInterface
{
    /**
     * A list with all meta information.
     *
     * @var array
     */
    protected $arrMetaInformation = [];

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

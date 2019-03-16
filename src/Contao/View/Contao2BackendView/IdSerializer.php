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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;

/**
 * The class IdSerializer provides handy methods to serialize and un-serialize model ids including the data provider
 * name into a string.
 *
 * @deprecated This class gonna be replaced by the ModelId. Use this instead!
 *
 * @see \ContaoCommunityAlliance\DcGeneral\Data\ModelId
 * @see \ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface
 */
class IdSerializer extends ModelId
{
    /**
     * Construct.
     *
     * @param string $dataProviderName The data provider name.
     * @param mixed  $modelId          The model id.
     */
    public function __construct($dataProviderName = '', $modelId = '')
    {
        $this->modelId          = $modelId;
        $this->dataProviderName = $dataProviderName;
    }

    /**
     * Set the data provider name.
     *
     * @param string $dataProviderName The name.
     *
     * @return IdSerializer
     */
    public function setDataProviderName($dataProviderName)
    {
        $this->dataProviderName = $dataProviderName;

        return $this;
    }

    /**
     * Set the model Id.
     *
     * @param mixed $modelId The id.
     *
     * @return IdSerializer
     */
    public function setId($modelId)
    {
        $this->modelId = $modelId;

        return $this;
    }

    /**
     * Determine if both, data provider name and id are set and non empty.
     *
     * @return bool
     */
    public function isValid()
    {
        return !(empty($this->modelId) || empty($this->dataProviderName));
    }
}

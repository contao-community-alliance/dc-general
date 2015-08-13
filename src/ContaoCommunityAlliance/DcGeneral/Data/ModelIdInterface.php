<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @subpackage Core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Interface ModelIdInterface.
 *
 * This interface the model id which identifies an model.
 */
interface ModelIdInterface
{
    /**
     * Retrieve the data provider name.
     *
     * @return string
     */
    public function getDataProviderName();

    /**
     * Retrieve the id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Create an instance from the passed values.
     *
     * @param string $dataProviderName The data provider name.
     *
     * @param mixed  $modelId          The id.
     *
     * @return ModelIdInterface
     */
    public static function fromValues($dataProviderName, $modelId);

    /**
     * Create an instance from a model.
     *
     * @param ModelInterface $model The model.
     *
     * @return ModelIdInterface
     */
    public static function fromModel(ModelInterface $model);

    /**
     * Create an instance from an serialized id.
     *
     * @param string $serialized The id.
     *
     * @return ModelIdInterface
     *
     * @throws DcGeneralRuntimeException When invalid data is encountered.
     */
    public static function fromSerialized($serialized);

    /**
     * Serialize the id.
     *
     * @return string
     */
    public function getSerialized();

    /**
     * Determine if this id, is equals to the other id.
     *
     * @param ModelIdInterface $modelId The other model id.
     *
     * @return bool
     */
    public function equals(ModelIdInterface $modelId);
}

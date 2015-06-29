<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Interface ModelIdInterface.
 *
 * This interface the model id which identifies an model.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Data
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

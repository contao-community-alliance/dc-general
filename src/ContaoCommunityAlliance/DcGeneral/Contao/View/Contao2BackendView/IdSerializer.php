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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * The class IdSerializer provides handy methods to serialize and un-serialize model ids including the data provider
 * name into a string.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView
 */
class IdSerializer
{

    /**
     * The data provider name.
     *
     * @var string
     */
    protected $dataProviderName;

    /**
     * The id of the model.
     *
     * @var mixed
     */
    protected $modelId;

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
     * Retrieve the data provider name.
     *
     * @return string
     */
    public function getDataProviderName()
    {
        return $this->dataProviderName;
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
     * Retrieve the id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->modelId;
    }

    /**
     * Create an instance from the passed values.
     *
     * @param string $dataProviderName The data provider name.
     *
     * @param mixed  $modelId          The id.
     *
     * @return IdSerializer
     */
    public static function fromValues($dataProviderName, $modelId)
    {
        $instance = new IdSerializer();

        $instance
            ->setId($modelId)
            ->setDataProviderName($dataProviderName);

        return $instance;
    }

    /**
     * Create an instance from a model.
     *
     * @param ModelInterface $model The model.
     *
     * @return IdSerializer
     */
    public static function fromModel(ModelInterface $model)
    {
        return self::fromValues($model->getProviderName(), $model->getId());
    }

    /**
     * Create an instance from an serialized id.
     *
     * @param string $serialized The id.
     *
     * @return IdSerializer
     *
     * @throws DcGeneralRuntimeException When invalid data is encountered.
     */
    public static function fromSerialized($serialized)
    {
        $instance = new IdSerializer();

        $serialized = rawurldecode($serialized);
        $serialized = html_entity_decode($serialized, ENT_QUOTES, 'UTF-8');

        $chunks = explode('::', $serialized);

        if (count($chunks) !== 2) {
            throw new DcGeneralRuntimeException('Unparsable encoded id value: ' . var_export($serialized, true));
        }

        $instance->setDataProviderName($chunks[0]);

        if (is_numeric($chunks[1])) {
            return $instance->setId($chunks[1]);
        }

        $decodedSource = base64_decode($chunks[1]);
        $decodedJson   = json_decode($decodedSource, true);

        return $instance->setId($decodedJson ?: $decodedSource);
    }

    /**
     * Serialize the id.
     *
     * @return string
     */
    public function getSerialized()
    {
        if (is_numeric($this->modelId)) {
            return sprintf('%s::%s', $this->dataProviderName, $this->modelId);
        }

        return sprintf('%s::%s', $this->dataProviderName, base64_encode(json_encode($this->modelId)));
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

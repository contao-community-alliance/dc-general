<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * The class ModelId implements the ModelIdInterface.
 *
 * It is the successor of the previous used ModelIdSerializer in the Contao2BackendView.
 */
class ModelId implements ModelIdInterface
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
     * Construct.
     *
     * @param string $dataProviderName The data provider name.
     * @param mixed  $modelId          The model id.
     *
     * @throws DcGeneralInvalidArgumentException If an invalid data provider name or model id is given.
     */
    public function __construct($dataProviderName, $modelId)
    {
        if (empty($dataProviderName)) {
            throw new DcGeneralInvalidArgumentException('Can\'t instantiate model id. No data provider name given.');
        }

        if ('' === $modelId) {
            throw new DcGeneralInvalidArgumentException('Can\'t instantiate model id. No model id given.');
        }

        $this->modelId          = $modelId;
        $this->dataProviderName = $dataProviderName;
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
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->modelId;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromValues($dataProviderName, $modelId)
    {
        return new static($dataProviderName, $modelId);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromModel(ModelInterface $model)
    {
        return static::fromValues($model->getProviderName(), $model->getId());
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When the id is unparsable.
     */
    public static function fromSerialized($serialized)
    {
        $serialized = \rawurldecode($serialized);
        $serialized = \html_entity_decode($serialized, ENT_QUOTES, 'UTF-8');

        $chunks = \explode('::', $serialized);

        if (\count($chunks) !== 2) {
            throw new DcGeneralRuntimeException('Unparsable encoded id value: ' . \var_export($serialized, true));
        }

        if (!\is_numeric($chunks[1])) {
            $decodedSource = \base64_decode($chunks[1]);
            $decodedJson   = \json_decode($decodedSource, true);

            $chunks[1] = $decodedJson ?: $decodedSource;
        }

        return static::fromValues($chunks[0], $chunks[1]);
    }

    /**
     * Serialize the id.
     *
     * @return string
     */
    public function getSerialized()
    {
        if (\is_numeric($this->modelId)) {
            return \sprintf('%s::%s', $this->dataProviderName, $this->modelId);
        }

        return \sprintf('%s::%s', $this->dataProviderName, \base64_encode(\json_encode($this->modelId)));
    }

    /**
     * {@inheritdoc}
     */
    public function equals(ModelIdInterface $modelId)
    {
        // It is exactly the same id
        if ($this === $modelId) {
            return true;
        }

        return !(
            // The data provider are not equal
            $this->getDataProviderName() !== $modelId->getDataProviderName()
            // The model ids are not equal
            || $this->getId() !== $modelId->getId()
        );
    }
}

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

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This event is emitted just before a model is saved to the data provider.
 */
class PrePersistModelEvent extends AbstractModelAwareEvent
{
    public const NAME = 'dc-general.model.pre-persist';

    /**
     * The original model attached to the event.
     *
     * @var ModelInterface|null
     */
    protected $originalModel;

    /**
     * Create a new model aware event.
     *
     * @param EnvironmentInterface $environment   The environment.
     * @param ModelInterface       $model         The model attached to the event.
     * @param ModelInterface|null  $originalModel The original state of the model (persistent in the data provider).
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        ModelInterface $originalModel = null
    ) {
        parent::__construct($environment, $model);

        $this->originalModel = $originalModel;
    }

    /**
     * Return the original state of the model.
     *
     * May be null on create.
     *
     * @return ModelInterface|null
     */
    public function getOriginalModel()
    {
        return $this->originalModel;
    }
}

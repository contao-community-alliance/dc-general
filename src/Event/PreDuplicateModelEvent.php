<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This event is emitted just before a model is duplicated.
 */
class PreDuplicateModelEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.model.pre-duplicate';

    /**
     * The source model.
     *
     * @var ModelInterface
     */
    protected $sourceModel;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @param ModelInterface       $model       The new model.
     *
     * @param ModelInterface       $sourceModel The source model.
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $model, ModelInterface $sourceModel)
    {
        parent::__construct($environment, $model);
        $this->sourceModel = $sourceModel;
    }

    /**
     * Retrieve the source model.
     *
     * @return ModelInterface
     */
    public function getSourceModel()
    {
        return $this->sourceModel;
    }
}

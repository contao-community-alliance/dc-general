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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This event is emitted after a model has been created.
 */
class EnforceModelRelationshipEvent extends AbstractModelAwareEvent
{
    /**
     * The parent model (if any).
     *
     * @var ModelInterface|null
     */
    private $parentModel;

    /**
     * The root model (if any).
     *
     * @var ModelInterface|null
     */
    private $rootModel;

    /**
     * Create a new model aware event.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelInterface       $model       The model attached to the event.
     * @param ModelInterface       $parentModel The parent model (if model is parented).
     * @param ModelInterface       $rootModel   The root model (if model is in a tree).
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        ModelInterface $parentModel = null,
        ModelInterface $rootModel = null
    ) {
        parent::__construct($environment, $model);

        $this->parentModel = $parentModel;
        $this->rootModel   = $rootModel;
    }

    /**
     * Retrieve the parent model (if model is parented).
     *
     * @return ModelInterface|null
     */
    public function getParentModel()
    {
        return $this->parentModel;
    }

    /**
     * Retrieve the root model (if model is in a tree).
     *
     * @return ModelInterface|null
     */
    public function getRootModel()
    {
        return $this->rootModel;
    }
}

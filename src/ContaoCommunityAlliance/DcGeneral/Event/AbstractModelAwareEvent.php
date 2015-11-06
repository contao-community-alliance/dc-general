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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\ModelAwareInterface;

/**
 * Abstract base class for an event that need an environment and a model.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class AbstractModelAwareEvent extends AbstractEnvironmentAwareEvent implements ModelAwareInterface
{
    /**
     * The model attached to the event.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * Create a new model aware event.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @param ModelInterface       $model       The model attached to the event.
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $model)
    {
        parent::__construct($environment);
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return $this->model;
    }
}

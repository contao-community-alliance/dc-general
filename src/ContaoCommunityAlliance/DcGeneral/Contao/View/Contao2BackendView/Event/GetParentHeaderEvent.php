<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class GetParentHeaderEvent.
 *
 * This event gets emitted when the header section of a parent view is generated.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetParentHeaderEvent extends AbstractEnvironmentAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-parent-header';

    /**
     * The additional lines that shall be added to the header section.
     *
     * @var array
     */
    protected $additional;

    /**
     * The parent model that is used at the header.
     *
     * @var ModelInterface
     */
    protected $parentModel;

    /**
     * Construct.
     *
     * @param EnvironmentInterface $environment The current environment.
     * @param ModelInterface       $parentModel The parent model.
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $parentModel)
    {
        parent::__construct($environment);

        $this->parentModel = $parentModel;
    }

    /**
     * Set the additional lines that shall be added to the header section.
     *
     * @param array $additional The lines to use as header.
     *
     * @return $this
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Get the additional lines that shall be added to the header section.
     *
     * @return array
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * Get the current parent model.
     *
     * @return ModelInterface
     */
    public function getParentModel()
    {
        return $this->parentModel;
    }
}

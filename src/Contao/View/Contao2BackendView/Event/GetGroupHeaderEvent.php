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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class GetGroupHeaderEvent.
 *
 * Render the group header in the listing view.
 */
class GetGroupHeaderEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-group-header';

    /**
     * The current property to be rendered for the group header.
     *
     * @var string
     */
    protected $groupField;

    /**
     * The grouping mode in use as defined in the listing config.
     *
     * @var string
     *
     * @see ListingConfigInterface
     */
    protected $groupingMode;

    /**
     * The grouping length in use as defined in the listing config.
     *
     * @var int
     *
     * @see ListingConfigInterface
     */
    protected $groupingLength;

    /**
     * The value to be rendered.
     *
     * @var string
     */
    protected $value;

    /**
     * Create a new group header event.
     *
     * @param EnvironmentInterface $environment    The environment.
     *
     * @param ModelInterface       $model          The model being used as group header.
     *
     * @param string               $propertyName   The name of the property being rendered into the group header.
     *
     * @param mixed                $propertyValue  The value of the property being rendered into the group header.
     *
     * @param string               $groupingMode   The grouping mode currently active.
     *
     * @param int                  $groupingLength The grouping length currently active.
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        $propertyName,
        $propertyValue,
        $groupingMode,
        $groupingLength
    ) {
        parent::__construct($environment, $model);

        $this->groupField     = $propertyName;
        $this->value          = $propertyValue;
        $this->groupingMode   = $groupingMode;
        $this->groupingLength = $groupingLength;
    }

    /**
     * Retrieve the property name to be rendered.
     *
     * @return string
     */
    public function getGroupField()
    {
        return $this->groupField;
    }

    /**
     * Get the grouping mode in use as defined in the listing config.
     *
     * @return string
     *
     * @see ListingConfigInterface
     */
    public function getGroupingMode()
    {
        return $this->groupingMode;
    }

    /**
     * Get the grouping length in use as defined in the listing config.
     *
     * @return int
     *
     * @see ListingConfigInterface
     */
    public function getGroupingLength()
    {
        return $this->groupingLength;
    }

    /**
     * Set the value to use in the group header.
     *
     * @param string $value The value.
     *
     * @return GetGroupHeaderEvent
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Retrieve the value to use in the group header.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}

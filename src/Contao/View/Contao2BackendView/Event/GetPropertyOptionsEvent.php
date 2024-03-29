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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class GetPropertyOptionsEvent.
 *
 * This event gets emitted when the options for a property shall get retrieved for the edit view.
 */
class GetPropertyOptionsEvent extends AbstractModelAwareEvent
{
    public const NAME = 'dc-general.view.contao2backend.get-property-options';

    /**
     * The name of the property to retrieve the options for.
     *
     * @var string|null
     */
    protected $propertyName = null;

    /**
     * The options for the properties.
     *
     * @var array|null
     */
    protected $options = null;

    /**
     * Set the property name to retrieve the options for.
     *
     * @param string|null $propertyName The name of the property.
     *
     * @return $this
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;

        return $this;
    }

    /**
     * Get the property name to retrieve the options for.
     *
     * @return string|null
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Set the options for the property in the event.
     *
     * @param array|null $options The options.
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Retrieve the options for the property from the event.
     *
     * @return array|null
     */
    public function getOptions()
    {
        return $this->options;
    }
}

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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * This event is emitted when a property value of a model shall be transformed into a readable string representation.
 */
class RenderReadablePropertyValueEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.render-readable-property-value';

    /**
     * The property that shall be transformed.
     *
     * @var PropertyInterface
     */
    protected $property;

    /**
     * The value that shall be transformed.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The transformed string representation.
     *
     * @var string|null
     */
    protected $rendered;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment in use.
     * @param ModelInterface       $model       The model the value originates from.
     * @param PropertyInterface    $property    The property to transform.
     * @param mixed                $value       The value to transform.
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        PropertyInterface $property,
        $value
    ) {
        parent::__construct($environment, $model);
        $this->property = $property;
        $this->value    = $value;
    }

    /**
     * Retrieve the property.
     *
     * @return PropertyInterface
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Retrieve the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the rendered value.
     *
     * @param null|string $rendered The rendered string representation or null to clear the representation.
     *
     * @return RenderReadablePropertyValueEvent
     */
    public function setRendered($rendered)
    {
        $this->rendered = $rendered;
        return $this;
    }

    /**
     * Retrieve the rendered string representation.
     *
     * @return null|string
     */
    public function getRendered()
    {
        return $this->rendered;
    }
}

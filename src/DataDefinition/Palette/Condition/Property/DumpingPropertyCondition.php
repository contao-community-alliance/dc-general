<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * Only for debugging purpose. Call the match() method on the wrapped condition and
 * dump the result with a backtrace.
 */
class DumpingPropertyCondition implements PropertyConditionInterface
{

    /**
     * The condition to dump.
     *
     * @var PropertyConditionInterface
     */
    protected $propertyCondition;

    /**
     * Create a new instance.
     *
     * @param PropertyConditionInterface $propertyCondition The condition to debug.
     */
    public function __construct($propertyCondition)
    {
        $this->propertyCondition = $propertyCondition;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings (PHPMD.DevelopmentCodeFragment)
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        $result = $this->propertyCondition->match($model, $input, $property, $legend);

        // @codingStandardsIgnoreStart - We explicitely allow var_dump() here for debugging purposes.
        echo '<pre>$condition: </pre>';
        \var_dump($this->propertyCondition);
        echo '<pre>$model: </pre>';
        \var_dump($model);
        echo '<pre>$input: </pre>';
        \var_dump($input);
        echo '<pre>$condition->match() result: </pre>';
        \var_dump($result);
        echo '<pre>';
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        echo '</pre>';
        // @codingStandardsIgnoreEnd

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->propertyCondition = clone $this->propertyCondition;
    }
}

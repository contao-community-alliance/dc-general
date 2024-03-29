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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget;

/**
 * This widget is a supporting widget to store the page tree orderings.
 *
 * The ContaoWidgetManager does not allow input values without a widget. This is used as helper widget instead.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TreePickerOrder extends AbstractWidget
{
    /**
     * The template.
     *
     * @var string
     */
    protected $strTemplate = 'widget_treepicker_order';

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        // Nothing to do here.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function validator($varInput)
    {
        return \array_filter(\explode(',', $varInput));
    }

    /**
     * Get the value serialized as string.
     *
     * @return string
     */
    protected function getSerializedValue()
    {
        if (null === $this->varValue) {
            $this->varValue = [];
        }

        return \implode(',', $this->varValue);
    }
}

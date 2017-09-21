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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget;

/**
 * This widget is a supporting widget to store the file tree orderings.
 *
 * The ContaoWidgetManager does not allow input values without a widget. This is used as helper widget instead.
 */
class FileTreeOrder extends AbstractWidget
{
    /**
     * The template.
     *
     * @var string
     */
    protected $strTemplate = 'widget_filetree_order';

    /**
     * {@inheritdoc}
     */
    protected function validator($inputValue)
    {
        $inputValue = array_map('\Contao\StringUtil::uuidToBin', array_filter(explode(',', $inputValue)));

        return $inputValue;
    }

    /**
     * Generate the widget and return it as string.
     *
     * @return string The widget markup
     */
    public function generate()
    {
        // Nothing to do here. Markup is in the widget template.
        return '';
    }

    /**
     * Get the value serialized as string.
     *
     * @return string
     */
    protected function getSerializedValue()
    {
        if ($this->varValue === null) {
            $this->varValue = array();
        }

        return implode(',', array_map('\Contao\StringUtil::binToUuid', $this->varValue));
    }
}

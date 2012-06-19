<?php
if (!defined('TL_ROOT'))
    die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/GPL 2
 * @filesource
 */

/**
 * Class ViewBuilder
 */
class ViewBuilder extends Backend
{

    protected $objDc;
    protected $strBid;

    /**
     * Initialize the object
     */
    public function __construct(DC_General $objDc)
    {
        parent::__construct();

        $this->objDc = $objDc;

        $this->displayButtons();
    }

    public function listView()
    {
        $return = '';

        $return .= $this->displayButtons();
        $return .= $this->listRecords();

        return $return;
    }

    public function displayButtons()
    {
        $arrDCA = $this->objDc->getDCA();

        $arrReturn = array();
        if (!$arrDCA['config']['closed'] || count($arrDCA['list']['global_operations']))
        {
            // Add open wrapper
            $arrReturn[] = '<div id="' . $this->objDc->getButtonId() . '">';

            // Add back button
            $arrReturn[] = (($this->Input->get('act') == 'select' || $this->objDc->getParentTable()) ? '<a href="' . $this->getReferer(true, $this->objDc->getParentTable()) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '" accesskey="b" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>' : '');

            // Add divider
            $arrReturn[] = (($this->objDc->getParentTable() && $this->Input->get('act') != 'select') ? ' &nbsp; :: &nbsp;' : '');

            if ($this->Input->get('act') != 'select')
            {
                // Add new button
                $arrReturn[] = ' ' . (!$arrDCA['config']['closed'] ? '<a href="' . (strlen($this->objDc->getParentTable()) ? $this->addToUrl('act=create' . (($arrDCA['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;pid=' . $this->objDc->getId()) : $this->addToUrl('act=create')) . '" class="header_new" title="' . specialchars($GLOBALS['TL_LANG'][$this->objDc->getTable()]['new'][1]) . '" accesskey="n" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG'][$this->objDc->getTable()]['new'][0] . '</a>' : '');

                // Add global buttons
                $arrReturn[] = $this->generateGlobalButtons();
            }

            // Add close wrapper
            $arrReturn[] = '</div>';

            $arrReturn[] = $this->getMessages(true);
        }

        return implode('', $arrReturn);
    }

    /**
     * Compile global buttons from the table configuration array and return them as HTML
     * 
     * @param boolean
     * @return string
     */
    protected function generateGlobalButtons($blnForceSeparator = false)
    {
        $arrDCA = $this->objDc->getDCA();

        if (!is_array($arrDCA['list']['global_operations']))
        {
            return '';
        }

        $return = '';
        foreach ($arrDCA['list']['global_operations'] as $k => $v)
        {
            $v = is_array($v) ? $v : array($v);
            $label = is_array($v['label']) ? $v['label'][0] : $v['label'];
            $title = is_array($v['label']) ? $v['label'][1] : $v['label'];
            $attributes = strlen($v['attributes']) ? ' ' . ltrim($v['attributes']) : '';

            if (!strlen($label))
            {
                $label = $k;
            }

            // Call a custom function instead of using the default button
            if (is_array($v['button_callback']))
            {
                $this->import($v['button_callback'][0]);
                $return .= $this->$v['button_callback'][0]->$v['button_callback'][1]($v['href'], $label, $title, $v['icon'], $attributes, $this->strTable, $this->root);

                continue;
            }

            $return .= ' &#160; :: &#160; <a href="' . $this->addToUrl($v['href']) . '" class="' . $v['class'] . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ';
        }

        return ($arrDCA['config']['closed'] && !$blnForceSeparator) ? preg_replace('/^ &#160; :: &#160; /', '', $return) : $return;
    }

    public function listRecords()
    {
        $arrReturn = array();
        
        if ($this->objDc->getCurrentCollecion()->length() < 1)
        {
            $arrReturn[] = '<p class="tl_empty">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>';
        }
        else
        {
            $arrReturn[] = '<p class="tl_empty">cooming soon</p>';
        }
        
        return implode('', $arrReturn);
    }

}

?>
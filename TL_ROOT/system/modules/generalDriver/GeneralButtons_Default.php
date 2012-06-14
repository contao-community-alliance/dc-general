<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @license    GNU/LGPL
 * @filesource
 */
class GeneralButtons_Default extends Backend
{

    private static $objInstance = null;

    public static function getInstance()
    {
        if (self::$objInstance == null)
        {
            self::$objInstance = new self();
        }
        
        return self::$objInstance;
    }

    protected function __construct()
    {
        parent::__construct();
    }

    public function save(DC_General $objDC)
    {
        echo " in save ";
        exit();
        
        $this->reload();
    }

    public function saveAndClose(DC_General $objDC)
    {
        echo " in saveAndClose ";
        exit();
        
        setcookie('BE_PAGE_OFFSET', 0, 0, '/');

        $_SESSION['TL_INFO']    = '';
        $_SESSION['TL_ERROR']   = '';
        $_SESSION['TL_CONFIRM'] = '';

        $this->redirect($this->getReferer());
    }

}

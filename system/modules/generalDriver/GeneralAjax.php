<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

class GeneralAjax extends Backend
{
    /**
     * This instance holder
     * @var GeneralAjax 
     */
    private static $objInstance = null;
    
    /**
     * Content for response
     * @var String 
     */
    protected $mixContent = "";
    

    
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Return a new or current instance
     * @return GeneralAjax 
     */
    public static function getInstance()
    {
        if (self::$objInstance == null)
        {
            self::$objInstance = new self();
        }
        
        return self::$objInstance;
    }
    
    /**
     * Output the response 
     */
    protected function output()
    {
        echo version_compare(VERSION, '2.10', '<') ? $this->mixContent : json_encode(array('content' => $this->mixContent, 'token' => REQUEST_TOKEN));
    }

    /**
     *
     * @param String $strAction
     * @param object $objDC
     * @return void 
     */
    public function hookExecutePostActions($strAction, $objDC)
    {
        // Check DC for right driver
        if (!$objDC instanceof DC_General)
        {
            return;
        }
        
        $this->objDC = $objDC;

        // Do something
        switch ($strAction)
        {
            case 'toggleSubpaletteExtended1':
                $this->toggleSubpaletteExtended();
                break;

            default:
                $this->mixContent = $objDC->$strAction();
                break;
        }
        
        $this->output();
        exit();
    }

    /**
     * Get a subpalette 
     */
    protected function toggleSubpaletteExtended()
    {
        $strMethod = $this->Input->get('act') == 'editAll' ? 'editAll' : 'edit';

        $strSelector = $this->Input->post('FORM_INPUTS');
        $strSelector = reset($strSelector);
        $intPos      = strrpos($strSelector, '_');
        $intID       = substr($strSelector, $intPos + 1);
        if (!is_numeric($intID))
        {
            $intID       = base64_decode(str_replace('_', '=', substr($intID, 1)));
        }
        $strSelector = substr($strSelector, 0, $intPos);

        $this->mixContent = $this->objDC->generateAjaxPalette($strMethod, $strSelector);
    }

}
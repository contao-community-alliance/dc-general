<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\View\BackendView;

use DcGeneral\Data\ModelInterface;
use DcGeneral\View\ModelFormatterInterface;

/**
 * Format a model and create a listing child record.
 */
class LabelFormatter implements ModelFormatterInterface
{
    /**
     * The used property names.
     * 
     * @var array
     */
    protected $propertyNames;
    
    /**
     * The format string.
     * 
     * @var string
     */
    protected $format = '%s';
    
    /**
     * The maximum length of the formated string.
     * 
     * @var int|null
     */
    protected $maxLength = null;
    
    /**
     * Set the used property names.
     * 
     * @param array $propertyNames
     */
    public function setPropertyNames(array $propertyNames)
    {
        $this->propertyNames = $propertyNames;
    }
    
    /**
     * Return the used property names.
     * 
     * @return array
     */
    public function getPropertyNames()
    {
        return $this->propertyNames;
    }
    
    /**
     * Set the format string.
     * 
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = (string) $format;
    }
    
    /**
     * Return the format string.
     * 
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
    
    /**
     * Set the formated maximum length.
     * 
     * @param int|null $maxLength
     */
    public function setMaxLenght($maxLength)
    {
        $this->maxLength = $maxLength !== null ? (int) $maxLength : null;
    }
    
    /**
     * Return the formated maximum length.
     * 
     * @return int|null
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }
    
    /**
     * {@inheritdoc}
     */
    public function format(ModelInterface $model)
    {
        $args = array();
        foreach ($this->propertyNames as $propertyName) {
            $args[] = (string) $model->getProperty($propertyName);
        }
        
        $string = vsprintf($this->format, $args);
        
        if ($this->maxLength !== null && strlen($string) > $this->maxLength) {
            $string = substr($string, 0, $this->maxLength);
        }
        
        return $string;
    }
}

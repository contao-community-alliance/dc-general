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

namespace DcGeneral\DataDefinition\Section\View;

interface OperationCollectionInterface
{
    /**
     * Remove all operations from this collection.
     */
    public function clearOperations();
    
    /**
     * Set the operations of this collection.
     * 
     * @param OperationInterface[]|array $operations
     */
    public function setOperations(array $operations);
    
    /**
     * Add operations to this collection.
     * 
     * @param OperationInterface[]|array $operations
     */
    public function addOperations(array $operations);
    
    /**
     * Remove operations from this collection.
     * 
     * @param OperationInterface[]|array $operations
     */
    public function removeOperations(array $operations);
    
    /**
     * Check if the operation exists in this collection.
     * 
     * @return bool
     */
    public function hasOperation(OperationInterface $operation);
    
    /**
     * Add an operation to this collection.
     * 
     * @param OperationInterface $operation
     */
    public function addOperation(OperationInterface $operation);
    
    /**
     * Remove an operation from this collection.
     * 
     * @param OperationInterface $operation
     */
    public function removeOperation(OperationInterface $operation);
    
    /**
     * Get operations from this collection.
     * 
     * @return OperationInterface[]|array
     */
    public function getOperations();
}

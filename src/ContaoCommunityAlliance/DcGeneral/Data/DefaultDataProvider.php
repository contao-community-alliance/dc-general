<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Simon Kusterer <simon@soped.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class DefaultDataProvider.
 *
 * Default implementation for a data provider using the Contao default database as backend.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) - There is no way to reduce this class more without reducing the
 *                                                     interface.
 */
class DefaultDataProvider implements DataProviderInterface
{
    /**
     * Name of current source.
     *
     * @var string
     */
    protected $strSource = null;

    /**
     * The Database instance.
     *
     * @var \Database
     */
    protected $objDatabase = null;

    /**
     * The property that shall get populated with the current timestamp when saving data.
     *
     * @var string
     */
    protected $timeStampProperty = false;

    /**
     * The id generator to use (if any).
     *
     * @var IdGeneratorInterface
     */
    protected $idGenerator = null;

    /**
     * Create a new instance of the data provider.
     */
    public function __construct()
    {
        $this->objDatabase = \Database::getInstance();
    }

    /**
     * Get the property name that shall get updated with the current time stamp when saving to the database.
     *
     * @return string|null
     */
    public function getTimeStampProperty()
    {
        return $this->timeStampProperty;
    }

    /**
     * Set the property name that shall get updated with the current time stamp when saving to the database.
     *
     * @param boolean $timeStampField The property name or empty to clear.
     *
     * @return DefaultDataProvider
     */
    public function setTimeStampProperty($timeStampField = null)
    {
        $this->timeStampProperty = $timeStampField;

        return $this;
    }

    /**
     * Set the id generator to use.
     *
     * @param IdGeneratorInterface $idGenerator The id generator.
     *
     * @return DefaultDataProvider
     */
    public function setIdGenerator($idGenerator)
    {
        $this->idGenerator = $idGenerator;

        return $this;
    }

    /**
     * Retrieve the id generator.
     *
     * @return IdGeneratorInterface
     */
    public function getIdGenerator()
    {
        return $this->idGenerator;
    }

    /**
     * Create an instance of the default database driven uuid generator.
     *
     * @return DefaultDataProvider
     *
     * @throws \RuntimeException When already an id generator has been set on the instance.
     */
    public function enableDefaultUuidGenerator()
    {
        if ($this->idGenerator) {
            throw new \RuntimeException('Error: already an id generator set on database provider.');
        }

        $this->setIdGenerator(new DatabaseUuidIdGenerator($this->objDatabase));

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException When no source has been defined.
     */
    public function setBaseConfig(array $arrConfig)
    {
        // Check configuration.
        if (!isset($arrConfig['source'])) {
            throw new DcGeneralRuntimeException('Missing table name.');
        }

        if (isset($arrConfig['database'])) {
            if (!($arrConfig['database'] instanceof \Database)) {
                throw new DcGeneralRuntimeException('Invalid database.');
            }

            $this->objDatabase = $arrConfig['database'];
        }

        if (isset($arrConfig['idGenerator'])) {
            if ($arrConfig['idGenerator'] instanceof IdGeneratorInterface) {
                $idGenerator = $arrConfig['idGenerator'];
                $this->setIdGenerator($idGenerator);
            }
        }

        $this->strSource = $arrConfig['source'];

        if (isset($arrConfig['timeStampProperty'])) {
            $this->setTimeStampProperty($arrConfig['timeStampProperty']);
        } elseif ($this->objDatabase->fieldExists('tstamp', $this->strSource)) {
            $this->setTimeStampProperty('tstamp');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyConfig()
    {
        return DefaultConfig::init();
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyModel()
    {
        $objModel = new DefaultModel();
        $objModel->setProviderName($this->strSource);
        return $objModel;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyCollection()
    {
        return new DefaultCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyFilterOptionCollection()
    {
        return new DefaultFilterOptionCollection();
    }

    /**
     * Build the field list.
     *
     * Returns all values from $objConfig->getFields() as comma separated list.
     *
     * @param ConfigInterface $objConfig The configuration to use.
     *
     * @return string
     */
    protected function buildFieldQuery($objConfig)
    {
        $strFields = '*';

        if ($objConfig->getIdOnly()) {
            $strFields = 'id';
        } elseif ($objConfig->getFields() !== null) {
            $strFields = implode(', ', $objConfig->getFields());

            if (!stristr($strFields, 'DISTINCT')) {
                $strFields = 'id, ' . $strFields;
            }
        }

        return $strFields;
    }

    /**
     * Build an AND or OR query.
     *
     * @param array $operation The operation to convert.
     *
     * @param array $params    The parameter array for the resulting query.
     *
     * @return string
     */
    protected function getAndOrFilter($operation, &$params)
    {
        $children = $operation['children'];

        if (!$children) {
            return '';
        }

        $combine = array();
        foreach ($children as $child) {

            $combine[] = $this->calculateSubfilter($child, $params);
        }

        return implode(sprintf(' %s ', $operation['operation']), $combine);
    }

    /**
     * Build the sub query for a comparing operator like =,<,>.
     *
     * @param array $operation The operation to apply.
     *
     * @param array $params    The parameters of the entire query.
     *
     * @return string
     */
    protected function getFilterForComparingOperator($operation, &$params)
    {
        $params[] = $operation['value'];

        return sprintf('(%s %s ?)', $operation['property'], $operation['operation']);
    }

    /**
     * Return the filter query for a "foo IN ('a', 'b')" filter.
     *
     * @param array $operation The operation to apply.
     *
     * @param array $params    The parameters of the entire query.
     *
     * @return string
     */
    protected function getFilterForInList($operation, &$params)
    {
        $params    = array_merge($params, array_values($operation['values']));
        $wildcards = rtrim(str_repeat('?,', count($operation['values'])), ',');

        return sprintf('(%s IN (%s))', $operation['property'], $wildcards);
    }

    /**
     * Return the filter query for a "foo LIKE '%ba_r%'" filter.
     *
     * The searched value may contain the wildcards '*' and '?' which will get converted to proper SQL.
     *
     * @param array $operation The operation to apply.
     *
     * @param array $params    The parameters of the entire query.
     *
     * @return string
     */
    protected function getFilterForLike($operation, &$params)
    {
        $wildcards = str_replace(array('*', '?'), array('%', '_'), $operation['value']);
        $params[]  = $wildcards;

        return sprintf('(%s LIKE ?)', $operation['property'], $wildcards);
    }

    /**
     * Combine a filter in standard filter array notation.
     *
     * Supported operations are:
     * operation      needed arguments     argument type.
     * AND
     *                'children'           array
     * OR
     *                'children'           array
     * =
     *                'property'           string (the name of a property)
     *                'value'              literal
     * >
     *                'property'           string (the name of a property)
     *                'value'              literal
     * <
     *                'property'           string (the name of a property)
     *                'value'              literal
     * IN
     *                'property'           string (the name of a property)
     *                'values'             array of literal
     *
     * LIKE
     *                'property'           string (the name of a property)
     *                'value'              literal - Wildcards * (Many) ? (One)
     *
     * @param array $arrFilter The filter to be combined to a valid SQL filter query.
     *
     * @param array $arrParams The query parameters will get stored into this array.
     *
     * @return string The combined WHERE conditions.
     *
     * @throws DcGeneralRuntimeException If an invalid filter entry is encountered.
     */
    protected function calculateSubfilter($arrFilter, array &$arrParams)
    {
        if (!is_array($arrFilter)) {
            throw new DcGeneralRuntimeException('Error Processing sub filter: ' . var_export($arrFilter, true), 1);
        }

        switch ($arrFilter['operation']) {
            case 'AND':
            case 'OR':
                return $this->getAndOrFilter($arrFilter, $arrParams);

            case '=':
            case '>':
            case '<':
                return $this->getFilterForComparingOperator($arrFilter, $arrParams);

            case 'IN':
                return $this->getFilterForInList($arrFilter, $arrParams);

            case 'LIKE':
                return $this->getFilterForLike($arrFilter, $arrParams);

            default:
        }

        throw new DcGeneralRuntimeException('Error processing filter array ' . var_export($arrFilter, true), 1);
    }

    /**
     * Build the WHERE clause for a configuration.
     *
     * @param ConfigInterface $objConfig The configuration to use.
     *
     * @param array           $arrParams The query parameters will get stored into this array.
     *
     * @return string  The combined WHERE clause (including the word "WHERE").
     */
    protected function buildWhereQuery($objConfig, array &$arrParams = null)
    {
        $arrParams || $arrParams = array();

        $arrQuery = array();

        $arrQuery['filter'] = $this->buildFilterQuery($objConfig, $arrParams);

        $arrQuery = array_filter($arrQuery, 'strlen');

        return count($arrQuery) ? ' WHERE ' . implode(' AND ', $arrQuery) : '';
    }

    /**
     * Build the WHERE conditions via calculateSubfilter().
     *
     * @param ConfigInterface $objConfig The configuration to use.
     *
     * @param array           $arrParams The query parameters will get stored into this array.
     *
     * @return string The combined WHERE conditions.
     */
    protected function buildFilterQuery($objConfig, array &$arrParams = null)
    {
        $arrParams || $arrParams = array();

        $strReturn = $this->calculateSubfilter(
            array(
                'operation' => 'AND',
                'children'  => $objConfig->getFilter()
            ),
            $arrParams
        );

        // Combine filter syntax.
        return $strReturn ? $strReturn : '';
    }

    /**
     * Build the order by part of a query.
     *
     * @param ConfigInterface $objConfig The configuration to use.
     *
     * @return string
     */
    protected function buildSortingQuery($objConfig)
    {
        $arrSorting = $objConfig->getSorting();
        $strReturn  = '';
        $arrFields  = array();

        if (($arrSorting !== null) && is_array($arrSorting) && count($arrSorting) > 0) {
            foreach ($arrSorting as $strField => $strOrder) {
                if ($strOrder
                    && !in_array(
                        strtoupper($strOrder),
                        array(DCGE::MODEL_SORTING_ASC, DCGE::MODEL_SORTING_DESC)
                    )
                ) {
                    $strField = $strOrder;
                    $strOrder = DCGE::MODEL_SORTING_ASC;
                } else {
                    if (!$strOrder) {
                        $strOrder = DCGE::MODEL_SORTING_ASC;
                    }
                }

                $arrFields[] = $strField . ' ' . $strOrder;
            }

            $strReturn .= ' ORDER BY ' . implode(', ', $arrFields);
        }

        return $strReturn;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException When an unusable object has been passed.
     */
    public function delete($item)
    {
        $modelId = null;
        if (is_numeric($item) || is_string($item)) {
            $modelId = $item;
        } elseif (is_object($item) && $item instanceof ModelInterface && strlen($item->getID()) != 0) {
            $modelId = $item->getID();
        } else {
            throw new DcGeneralRuntimeException("ID missing or given object not of type 'ModelInterface'.");
        }

        // Insert undo.
        $this->insertUndo(
            sprintf(
                'DELETE FROM %1$s WHERE id = %2$s',
                $this->strSource,
                $modelId
            ),
            sprintf(
                'SELECT * FROM %1$s WHERE id = %2$s',
                $this->strSource,
                $modelId
            ),
            $this->strSource
        );

        $this->objDatabase
            ->prepare(sprintf('DELETE FROM %s WHERE id=?', $this->strSource))
            ->execute($modelId);
    }

    /**
     * Create a model from a database result.
     *
     * @param \Database\Result $dbResult The database result to create a model from.
     *
     * @return ModelInterface
     */
    protected function createModelFromDatabaseResult($dbResult)
    {
        $objModel = $this->getEmptyModel();

        /** @var \Contao\Database\Result $dbResult */
        foreach ($dbResult->row() as $key => $value) {
            if ($key == 'id') {
                $objModel->setID($value);
            }

            $objModel->setPropertyRaw($key, deserialize($value));
        }

        return $objModel;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(ConfigInterface $objConfig)
    {
        if ($objConfig->getId() != null) {
            $strQuery = sprintf(
                'SELECT %s  FROM %s WHERE id = ?',
                $this->buildFieldQuery($objConfig),
                $this->strSource
            );

            $dbResult = $this->objDatabase
                ->prepare($strQuery)
                ->execute($objConfig->getId());
        } else {
            $arrParams = array();

            // Build SQL.
            $query = sprintf(
                'SELECT %s FROM %s',
                $this->buildFieldQuery($objConfig),
                $this->strSource
            );
            $query .= $this->buildWhereQuery($objConfig, $arrParams);
            $query .= $this->buildSortingQuery($objConfig);

            // Execute db query.
            $dbResult = $this->objDatabase
                ->prepare($query)
                ->limit(1, 0)
                ->execute($arrParams);
        }

        if ($dbResult->numRows == 0) {
            return null;
        }

        return $this->createModelFromDatabaseResult($dbResult);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll(ConfigInterface $objConfig)
    {
        $arrParams = array();
        // Build SQL.
        $query = sprintf(
            'SELECT %s FROM %s',
            $this->buildFieldQuery($objConfig),
            $this->strSource
        );
        $query .= $this->buildWhereQuery($objConfig, $arrParams);
        $query .= $this->buildSortingQuery($objConfig);

        // Execute db query.
        $objDatabaseQuery = $this->objDatabase->prepare($query);

        if ($objConfig->getAmount() != 0) {
            $objDatabaseQuery->limit($objConfig->getAmount(), $objConfig->getStart());
        }

        $dbResult = $objDatabaseQuery->execute($arrParams);

        if ($objConfig->getIdOnly()) {
            return $dbResult->fetchEach('id');
        }

        $objCollection = $this->getEmptyCollection();

        if ($dbResult->numRows == 0) {
            return $objCollection;
        }

        while ($dbResult->next()) {
            $objCollection->add($this->createModelFromDatabaseResult($dbResult));
        }

        return $objCollection;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException if improper values have been passed (i.e. not exactly one field requested).
     */
    public function getFilterOptions(ConfigInterface $objConfig)
    {
        $arrProperties = $objConfig->getFields();
        $strProperty   = $arrProperties[0];

        if (count($arrProperties) <> 1) {
            throw new DcGeneralRuntimeException('objConfig must contain exactly one property to be retrieved.');
        }

        $arrParams = array();

        $objValues = $this->objDatabase
            ->prepare(
                sprintf(
                    'SELECT DISTINCT(%s) FROM %s %s',
                    $strProperty,
                    $this->strSource,
                    $this->buildWhereQuery($objConfig, $arrParams)
                )
            )
            ->execute($arrParams);

        $objCollection = $this->getEmptyFilterOptionCollection();
        while ($objValues->next()) {
            $objCollection->add($objValues->$strProperty, $objValues->$strProperty);
        }

        return $objCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount(ConfigInterface $objConfig)
    {
        $arrParams = array();

        $query = sprintf(
            'SELECT COUNT(*) AS count FROM %s',
            $this->strSource
        );
        $query .= $this->buildWhereQuery($objConfig, $arrParams);

        $objCount = $this->objDatabase
            ->prepare($query)
            ->execute($arrParams);

        return $objCount->count;
    }

    /**
     * {@inheritDoc}
     */
    public function isUniqueValue($strField, $varNew, $intId = null)
    {
        $objUnique = $this->objDatabase
            ->prepare('SELECT * FROM ' . $this->strSource . ' WHERE ' . $strField . ' = ? ')
            ->execute($varNew);

        if ($objUnique->numRows == 0) {
            return true;
        }

        if (($objUnique->numRows == 1) && ($objUnique->id == $intId)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function resetFallback($strField)
    {
        $this->objDatabase->query('UPDATE ' . $this->strSource . ' SET ' . $strField . ' = \'\'');
    }

    /**
     * Convert a model into a property array to be used in insert and update queries.
     *
     * @param ModelInterface $model The model to convert into an property array.
     *
     * @return array
     */
    protected function convertModelToDataPropertyArray(ModelInterface $model)
    {
        $data = array();

        foreach ($model as $key => $value) {
            if ($key == 'id') {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = serialize($value);
            } else {
                $data[$key] = $value;
            }
        }

        if ($this->timeStampProperty) {
            $data[$this->getTimeStampProperty()] = time();
        }

        return $data;
    }

    /**
     * Insert the model into the database.
     *
     * @param ModelInterface $model The model to insert into the database.
     *
     * @return void
     */
    protected function insertModelIntoDatabase(ModelInterface $model)
    {
        $data = $this->convertModelToDataPropertyArray($model);
        if ($this->getIdGenerator()) {
            $model->setID($this->getIdGenerator()->generate());
            $data['id'] = $model->getID();
        }

        $insertResult = $this->objDatabase
            ->prepare(sprintf('INSERT INTO %s %%s', $this->strSource))
            ->set($data)
            ->execute();

        if (!isset($data['id']) && strlen($insertResult->insertId)) {
            $model->setID($insertResult->insertId);
        }
    }

    /**
     * Update the model in the database.
     *
     * @param ModelInterface $model The model to update the database.
     *
     * @return void
     */
    protected function updateModelInDatabase($model)
    {
        $data = $this->convertModelToDataPropertyArray($model);

        $this->objDatabase
            ->prepare(sprintf('UPDATE %s %%s WHERE id=?', $this->strSource))
            ->set($data)
            ->execute($model->getID());
    }

    /**
     * {@inheritDoc}
     */
    public function save(ModelInterface $objItem)
    {
        if ($objItem->getID() === null || $objItem->getID() === '') {
            $this->insertModelIntoDatabase($objItem);
        } else {
            $this->updateModelInDatabase($objItem);
        }

        return $objItem;
    }

    /**
     * {@inheritDoc}
     */
    public function saveEach(CollectionInterface $objItems)
    {
        foreach ($objItems as $value) {
            $this->save($value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fieldExists($strField)
    {
        return $this->objDatabase->fieldExists($strField, $this->strSource);
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion($mixID, $mixVersion)
    {
        $objVersion = $this->objDatabase
            ->prepare('SELECT * FROM tl_version WHERE pid=? AND version=? AND fromTable=?')
            ->execute($mixID, $mixVersion, $this->strSource);

        if ($objVersion->numRows == 0) {
            return null;
        }

        $arrData = deserialize($objVersion->data);

        if (!is_array($arrData) || count($arrData) == 0) {
            return null;
        }

        $objModel = $this->getEmptyModel();
        $objModel->setID($mixID);
        foreach ($arrData as $key => $value) {
            if ($key == 'id') {
                continue;
            }

            $objModel->setProperty($key, $value);
        }

        return $objModel;
    }

    /**
     * Return a list with all versions for the row with the given Id.
     *
     * @param mixed   $mixID         The ID of the row.
     *
     * @param boolean $blnOnlyActive If true, only active versions will get returned, if false all version will get
     *                               returned.
     *
     * @return CollectionInterface
     */
    public function getVersions($mixID, $blnOnlyActive = false)
    {
        $sql = 'SELECT tstamp, version, username, active FROM tl_version WHERE fromTable = ? AND pid = ?';
        if ($blnOnlyActive) {
            $sql .= ' AND active = 1';
        } else {
            $sql .= ' ORDER BY version DESC';
        }

        $arrVersion = $this->objDatabase
            ->prepare($sql)
            ->execute($this->strSource, $mixID)
            ->fetchAllAssoc();

        if (count($arrVersion) == 0) {
            return null;
        }

        $objCollection = $this->getEmptyCollection();

        foreach ($arrVersion as $versionValue) {
            $objReturn = $this->getEmptyModel();
            $objReturn->setID($mixID);

            foreach ($versionValue as $key => $value) {
                if ($key == 'id') {
                    continue;
                }

                $objReturn->setProperty($key, $value);
            }

            $objCollection->add($objReturn);
        }

        return $objCollection;
    }

    /**
     * Save a new version of a row.
     *
     * @param ModelInterface $objModel    The model for which a new version shall be created.
     *
     * @param string         $strUsername The username to attach to the version as creator.
     *
     * @return void
     */
    public function saveVersion(ModelInterface $objModel, $strUsername)
    {
        $objCount = $this->objDatabase
            ->prepare('SELECT count(*) as mycount FROM tl_version WHERE pid=? AND fromTable = ?')
            ->execute($objModel->getID(), $this->strSource);

        $mixNewVersion = (intval($objCount->mycount) + 1);

        $mixData       = $objModel->getPropertiesAsArray();
        $mixData['id'] = $objModel->getID();

        $arrInsert              = array();
        $arrInsert['pid']       = $objModel->getID();
        $arrInsert['tstamp']    = time();
        $arrInsert['version']   = $mixNewVersion;
        $arrInsert['fromTable'] = $this->strSource;
        $arrInsert['username']  = $strUsername;
        $arrInsert['data']      = serialize($mixData);

        $this->objDatabase->prepare('INSERT INTO tl_version %s')
            ->set($arrInsert)
            ->execute();

        $this->setVersionActive($objModel->getID(), $mixNewVersion);
    }

    /**
     * Set a version as active.
     *
     * @param mixed $mixID      The ID of the row.
     *
     * @param mixed $mixVersion The version number to set active.
     *
     * @return void
     */
    public function setVersionActive($mixID, $mixVersion)
    {
        $this->objDatabase
            ->prepare('UPDATE tl_version SET active=\'\' WHERE pid = ? AND fromTable = ?')
            ->execute($mixID, $this->strSource);

        $this->objDatabase
            ->prepare('UPDATE tl_version SET active = 1 WHERE pid = ? AND version = ? AND fromTable = ?')
            ->execute($mixID, $mixVersion, $this->strSource);
    }

    /**
     * Retrieve the current active version for a row.
     *
     * @param mixed $mixID The ID of the row.
     *
     * @return mixed The current version number of the requested row.
     */
    public function getActiveVersion($mixID)
    {
        $objVersionID = $this->objDatabase
            ->prepare('SELECT version FROM tl_version WHERE pid = ? AND fromTable = ? AND active = 1')
            ->execute($mixID, $this->strSource);

        if ($objVersionID->numRows == 0) {
            return null;
        }

        return $objVersionID->version;
    }

    /**
     * Check if two models have the same values in all properties.
     *
     * @param ModelInterface $objModel1 The first model to compare.
     *
     * @param ModelInterface $objModel2 The second model to compare.
     *
     * @return boolean True - If both models are same, false if not.
     */
    public function sameModels($objModel1, $objModel2)
    {
        foreach ($objModel1 as $key => $value) {
            if ($key == 'id') {
                continue;
            }

            if (is_array($value)) {
                if (!is_array($objModel2->getProperty($key))) {
                    return false;
                }

                if (serialize($value) != serialize($objModel2->getProperty($key))) {
                    return false;
                }
            } elseif ($value != $objModel2->getProperty($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Store an undo entry in the table tl_undo.
     *
     * Currently this only supports delete queries.
     *
     * @param string $strSourceSQL The SQL used to perform the action to be undone.
     *
     * @param string $strSaveSQL   The SQL query to retrieve the current entries.
     *
     * @param string $strTable     The table to be affected by the action.
     *
     * @return void
     */
    protected function insertUndo($strSourceSQL, $strSaveSQL, $strTable)
    {
        // Load row.
        $arrResult = $this->objDatabase
            ->prepare($strSaveSQL)
            ->execute()
            ->fetchAllAssoc();

        // Check if we have a result.
        if (count($arrResult) == 0) {
            return;
        }

        // Save information in array.
        $arrSave = array();
        foreach ($arrResult as $value) {
            $arrSave[$strTable][] = $value;
        }

        $strPrefix = '<span style="color:#b3b3b3; padding-right:3px;">(DC General)</span>';
        $objUser   = \BackendUser::getInstance();

        // Write into undo.
        $this->objDatabase
            ->prepare(
                'INSERT INTO tl_undo (pid, tstamp, fromTable, query, affectedRows, data) VALUES (?, ?, ?, ?, ?, ?)'
            )
            ->execute(
                $objUser->id,
                time(),
                $strTable,
                $strPrefix .
                $strSourceSQL,
                count($arrSave[$strTable]),
                serialize($arrSave)
            );
    }
}

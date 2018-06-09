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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Simon Kusterer <simon@soped.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use Contao\BackendUser;
use Contao\Database;
use Contao\Database\Result;
use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class DefaultDataProvider.
 *
 * Default implementation for a data provider using the Contao default database as backend.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)     - We have to keep them as we implement the interfaces.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) - There is no elegant way to reduce this class more without
 *                                                     reducing the interface.
 */
class DefaultDataProvider implements DataProviderInterface
{
    /**
     * Name of current source.
     *
     * @var string
     */
    protected $strSource;

    /**
     * The Database instance.
     *
     * @var Database
     */
    protected $objDatabase;

    /**
     * The name of the id property.
     *
     * @var string
     */
    protected $idProperty = 'id';

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
    protected $idGenerator;

    /**
     * Retrieve the name of the id property.
     *
     * @return string
     */
    public function getIdProperty()
    {
        return $this->idProperty;
    }

    /**
     * Set the id property.
     *
     * @param string $idProperty The name of the id property.
     *
     * @return DefaultDataProvider
     */
    public function setIdProperty($idProperty)
    {
        $this->idProperty = $idProperty;

        return $this;
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
            if (!($arrConfig['database'] instanceof Database)) {
                throw new DcGeneralRuntimeException('Invalid database.');
            }

            $this->objDatabase = $arrConfig['database'];
        } else {
            $this->objDatabase = Database::getInstance();
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

        if (isset($arrConfig['idProperty'])) {
            $this->setIdProperty($arrConfig['idProperty']);
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
     * Fetch an empty single filter option collection (new model list).
     *
     * @return FilterOptionCollectionInterface
     *
     * @deprecated This method was never intended to be used externally.
     */
    public function getEmptyFilterOptionCollection()
    {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'Method ' . __METHOD__ . ' was never intended to be called via interface and will get removed',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        return new DefaultFilterOptionCollection();
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException When an unusable object has been passed.
     */
    public function delete($item)
    {
        $modelId = null;
        if (\is_numeric($item) || \is_string($item)) {
            $modelId = $item;
        } elseif (\is_object($item) && $item instanceof ModelInterface && null !== $item->getId()) {
            $modelId = $item->getId();
        } else {
            throw new DcGeneralRuntimeException("ID missing or given object not of type 'ModelInterface'.");
        }

        // Insert undo.
        $this->insertUndo(
            \sprintf(
                'DELETE FROM %1$s WHERE %1$s.id = %2$s',
                $this->strSource,
                $modelId
            ),
            \sprintf(
                'SELECT * FROM %1$s WHERE %1$s.id = %2$s',
                $this->strSource,
                $modelId
            ),
            $this->strSource
        );

        $this->objDatabase
            ->prepare(\sprintf('DELETE FROM %1s WHERE %1$s.id=?', $this->strSource))
            ->execute($modelId);
    }

    /**
     * Create a model from a database result.
     *
     * @param Result $dbResult The database result to create a model from.
     *
     * @return ModelInterface
     */
    protected function createModelFromDatabaseResult($dbResult)
    {
        $objModel = $this->getEmptyModel();

        /** @var \Contao\Database\Result $dbResult */
        foreach ($dbResult->row() as $key => $value) {
            if ($key == $this->idProperty) {
                $objModel->setIdRaw($value);
            }

            $objModel->setPropertyRaw($key, StringUtil::deserialize($value));
        }

        return $objModel;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(ConfigInterface $objConfig)
    {
        if ($objConfig->getId() != null) {
            $query = \sprintf(
                'SELECT %s FROM %s WHERE id = ?',
                DefaultDataProviderSqlUtils::buildFieldQuery($objConfig, $this->idProperty),
                $this->strSource
            );

            $dbResult = $this->objDatabase
                ->prepare($query)
                ->execute($objConfig->getId());
        } else {
            $arrParams = [];

            // Build SQL.
            $query = \sprintf(
                'SELECT %s FROM %s%s%s',
                DefaultDataProviderSqlUtils::buildFieldQuery($objConfig, $this->idProperty),
                $this->strSource,
                DefaultDataProviderSqlUtils::buildWhereQuery($objConfig, $arrParams),
                DefaultDataProviderSqlUtils::buildSortingQuery($objConfig)
            );

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
        $arrParams = [];
        // Build SQL.
        $query = \sprintf(
            'SELECT %s FROM %s%s%s',
            DefaultDataProviderSqlUtils::buildFieldQuery($objConfig, $this->idProperty),
            $this->strSource,
            DefaultDataProviderSqlUtils::buildWhereQuery($objConfig, $arrParams),
            DefaultDataProviderSqlUtils::buildSortingQuery($objConfig)
        );

        // Execute db query.
        $objDatabaseQuery = $this->objDatabase->prepare($query);

        if ($objConfig->getAmount() != 0) {
            $objDatabaseQuery->limit($objConfig->getAmount(), $objConfig->getStart());
        }

        $dbResult = $objDatabaseQuery->execute($arrParams);

        if ($objConfig->getIdOnly()) {
            return $dbResult->fetchEach($this->idProperty);
        }

        $objCollection = $this->getEmptyCollection();

        if ($dbResult->numRows == 0) {
            return $objCollection;
        }

        while ($dbResult->next()) {
            $objCollection->push($this->createModelFromDatabaseResult($dbResult));
        }

        return $objCollection;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException If improper values have been passed (i.e. not exactly one field requested).
     */
    public function getFilterOptions(ConfigInterface $objConfig)
    {
        $arrProperties = $objConfig->getFields();
        $strProperty   = $arrProperties[0];

        if (\count($arrProperties) <> 1) {
            throw new DcGeneralRuntimeException('objConfig must contain exactly one property to be retrieved.');
        }

        $arrParams = [];

        $objValues = $this->objDatabase
            ->prepare(
                \sprintf(
                    'SELECT DISTINCT(%s) FROM %s %s',
                    $strProperty,
                    $this->strSource,
                    DefaultDataProviderSqlUtils::buildWhereQuery($objConfig, $arrParams)
                )
            )
            ->execute($arrParams);

        $objCollection = new DefaultFilterOptionCollection();
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
        $parameters = [];
        $query      = \sprintf(
            'SELECT COUNT(*) AS count FROM %s%s',
            $this->strSource,
            DefaultDataProviderSqlUtils::buildWhereQuery($objConfig, $parameters)
        );

        $objCount = $this->objDatabase
            ->prepare($query)
            ->execute($parameters);

        return $objCount->count;
    }

    /**
     * {@inheritDoc}
     */
    public function isUniqueValue($strField, $varNew, $intId = null)
    {
        $objUnique = $this->objDatabase
            ->prepare(
                \sprintf(
                    'SELECT %1$s.* FROM %1$s WHERE %1$s.%2$s = ? ',
                    $this->strSource,
                    $strField
                )
            )->execute($varNew);

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
        // @codingStandardsIgnoreStart
        @\trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated - handle resetting manually', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        $this->objDatabase->query('UPDATE ' . $this->strSource . ' SET ' . $strField . ' = \'\'');
    }

    /**
     * Convert a model into a property array to be used in insert and update queries.
     *
     * @param ModelInterface $model The model to convert into an property array.
     * @param int            $timestamp Optional the timestamp.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function convertModelToDataPropertyArray(ModelInterface $model, $timestamp = 0)
    {
        $data = [];
        foreach ($model as $key => $value) {
            if ($key == $this->idProperty) {
                continue;
            }

            if (\is_array($value)) {
                $data[$this->strSource . '.' . $key] = \serialize($value);
            } else {
                $data[$this->strSource . '.' . $key] = $value;
            }
        }

        if ($this->timeStampProperty) {
            $data[$this->strSource . '.' . $this->getTimeStampProperty()] = $timestamp ?: \time();
        }

        return $data;
    }

    /**
     * Insert the model into the database.
     *
     * @param ModelInterface $model The model to insert into the database.
     * @param int            $timestamp Optional the timestamp.
     *
     * @return void
     */
    private function insertModelIntoDatabase(ModelInterface $model, $timestamp = 0)
    {
        $data = $this->convertModelToDataPropertyArray($model, $timestamp);
        if ($this->getIdGenerator()) {
            $model->setId($this->getIdGenerator()->generate());
            $data[$this->idProperty] = $model->getId();
        }

        $insertResult = $this->objDatabase
            ->prepare(\sprintf('INSERT INTO %s %%s', $this->strSource))
            ->set($data)
            ->execute();

        if (null !== $insertResult->insertId && !isset($data[$this->idProperty])) {
            $model->setId((string) $insertResult->insertId);
        }
    }

    /**
     * Update the model in the database.
     *
     * @param ModelInterface $model     The model to update the database.
     * @param int            $timestamp Optional the timestamp.
     *
     * @return void
     */
    private function updateModelInDatabase($model, $timestamp = 0)
    {
        $data = $this->convertModelToDataPropertyArray($model, $timestamp);

        $this->objDatabase
            ->prepare(\sprintf('UPDATE %s %%s WHERE id=?', $this->strSource))
            ->set($data)
            ->execute($model->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function save(ModelInterface $objItem, $timestamp = 0)
    {
        if ($objItem->getId() === null || $objItem->getId() === '') {
            $this->insertModelIntoDatabase($objItem);
        } else {
            $this->updateModelInDatabase($objItem);
        }

        return $objItem;
    }

    /**
     * {@inheritDoc}
     */
    public function saveEach(CollectionInterface $objItems, $timestamp = 0)
    {
        foreach ($objItems as $value) {
            $this->save($value, $timestamp);
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
            ->prepare(
                'SELECT
                    tl_version.*
                    FROM
                    tl_version
                WHERE
                    tl_version.pid=?
                    AND
                    tl_version.version=?
                    AND
                    tl_version.fromTable=?'
            )->execute($mixID, $mixVersion, $this->strSource);

        if ($objVersion->numRows == 0) {
            return null;
        }

        $arrData = \deserialize($objVersion->data);

        if (!\is_array($arrData) || \count($arrData) == 0) {
            return null;
        }

        $objModel = $this->getEmptyModel();
        $objModel->setID($mixID);
        foreach ($arrData as $key => $value) {
            if ($key == $this->idProperty) {
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
     * @param boolean $blnOnlyActive If true, only active versions will get returned, if false all version will get
     *                               returned.
     *
     * @return CollectionInterface
     */
    public function getVersions($mixID, $blnOnlyActive = false)
    {
        $sql = 'SELECT
                    tl_version.tstamp,
                    tl_version.version,
                    tl_version.username,
                    tl_version.active
                    FROM
                    tl_version
                WHERE
                    tl_version.fromTable = ?
                    AND
                    tl_version.pid = ?';
        if ($blnOnlyActive) {
            $sql .= ' AND tl_version.active = 1';
        } else {
            $sql .= ' ORDER BY tl_version.version DESC';
        }

        $arrVersion = $this->objDatabase
            ->prepare($sql)
            ->execute($this->strSource, $mixID)
            ->fetchAllAssoc();

        if (\count($arrVersion) == 0) {
            return null;
        }

        $objCollection = $this->getEmptyCollection();

        foreach ($arrVersion as $versionValue) {
            $objReturn = $this->getEmptyModel();
            $objReturn->setId($mixID);

            foreach ($versionValue as $key => $value) {
                if ($key == $this->idProperty) {
                    continue;
                }

                $objReturn->setProperty($key, $value);
            }

            $objCollection->push($objReturn);
        }

        return $objCollection;
    }

    /**
     * Save a new version of a row.
     *
     * @param ModelInterface $objModel    The model for which a new version shall be created.
     * @param string         $strUsername The username to attach to the version as creator.
     *
     * @return void
     */
    public function saveVersion(ModelInterface $objModel, $strUsername)
    {
        $objCount = $this->objDatabase
            ->prepare(
                'SELECT
                    count(*) as mycount
                    FROM
                    tl_version
                WHERE
                    tl_version.pid=?
                    AND
                    tl_version.fromTable = ?'
            )->execute($objModel->getId(), $this->strSource);

        $mixNewVersion = ((int) $objCount->mycount + 1);
        $mixData       = $objModel->getPropertiesAsArray();

        $mixData[$this->idProperty] = $objModel->getId();

        $arrInsert              = [];
        $arrInsert['tl_version.pid']       = $objModel->getId();
        $arrInsert['tl_version.tstamp']    = \time();
        $arrInsert['tl_version.version']   = $mixNewVersion;
        $arrInsert['tl_version.fromTable'] = $this->strSource;
        $arrInsert['tl_version.username']  = $strUsername;
        $arrInsert['tl_version.data']      = \serialize($mixData);

        $this->objDatabase->prepare('INSERT INTO tl_version %s')
            ->set($arrInsert)
            ->execute();

        $this->setVersionActive($objModel->getId(), $mixNewVersion);
    }

    /**
     * Set a version as active.
     *
     * @param mixed $mixID      The ID of the row.
     * @param mixed $mixVersion The version number to set active.
     *
     * @return void
     */
    public function setVersionActive($mixID, $mixVersion)
    {
        $this->objDatabase
            ->prepare(
                'UPDATE
                    tl_version
                    SET
                    tl_version.active=\'\'
                WHERE
                    tl_version.pid = ?
                    AND
                    tl_version.fromTable = ?'
            )->execute($mixID, $this->strSource);

        $this->objDatabase
            ->prepare(
                'UPDATE
                    tl_version
                    SET
                    tl_version.active = 1
                WHERE
                    tl_version.pid = ?
                    AND
                    tl_version.version = ?
                    AND
                    tl_version.fromTable = ?'
            )->execute($mixID, $mixVersion, $this->strSource);
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
            ->prepare(
                'SELECT
                    tl_version.version
                    FROM
                    tl_version
                    WHERE
                        tl_version.pid = ?
                        AND
                        tl_version.fromTable = ?
                        AND
                        tl_version.active = 1'
            )->execute($mixID, $this->strSource);

        if ($objVersionID->numRows == 0) {
            return null;
        }

        return $objVersionID->version;
    }

    /**
     * Check if two models have the same values in all properties.
     *
     * @param ModelInterface $objModel1 The first model to compare.
     * @param ModelInterface $objModel2 The second model to compare.
     *
     * @return boolean True - If both models are same, false if not.
     */
    public function sameModels($objModel1, $objModel2)
    {
        foreach ($objModel1 as $key => $value) {
            if ($key == $this->idProperty) {
                continue;
            }

            if (\is_array($value)) {
                if (!\is_array($objModel2->getProperty($key))) {
                    return false;
                }

                if (\serialize($value) != \serialize($objModel2->getProperty($key))) {
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
     * @param string $strSaveSQL   The SQL query to retrieve the current entries.
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
        if (\count($arrResult) == 0) {
            return;
        }

        // Save information in array.
        $arrSave = [];
        foreach ($arrResult as $value) {
            $arrSave[$strTable][] = $value;
        }

        $strPrefix = '<span style="color:#b3b3b3; padding-right:3px;">(DC General)</span>';
        $objUser   = BackendUser::getInstance();

        // Write into undo.
        $this->objDatabase
            ->prepare(
                'INSERT INTO
                    tl_undo
                    (tl_undo.pid, tl_undo.tstamp, tl_undo.fromTable, tl_undo.query, tl_undo.affectedRows, tl_undo.data)
                    VALUES
                    (?, ?, ?, ?, ?, ?)'
            )
            ->execute(
                $objUser->id,
                \time(),
                $strTable,
                $strPrefix .
                $strSourceSQL,
                \count($arrSave[$strTable]),
                \serialize($arrSave)
            );
    }
}

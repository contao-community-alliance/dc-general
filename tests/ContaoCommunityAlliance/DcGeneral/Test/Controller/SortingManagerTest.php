<?php
/**
 * PHP version 5
 *
 * @package    DcGeneral
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Controller;

use ContaoCommunityAlliance\DcGeneral\Controller\SortingManager;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * Test case for the sorting manager.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Test\Controller
 */
class SortingManagerTest extends TestCase
{
    /**
     * Data provider for test the sorting.
     *
     * It creates a matrix with following entries for each row:
     *  - model data [id => sorting, id2 => sorting2, ...]
     *  - list of models being moved [id, id2, ...]
     *  - optional previous id or null
     *  - expected order of the ids.
     *
     * @return array
     */
    public function provideTestData()
    {
        return array(
            // Test with default gap, without previous model
            array(
                array(
                    1 => 128,
                    2 => 256,
                    3 => 384
                ),
                array(3),
                null,
                array(3, 1, 2)
            ),
            // Test with minimum gap, without previous model
            array(
                array(
                    1 => 2,
                    2 => 4,
                    3 => 6
                ),
                array(3),
                null,
                array(3, 1, 2)
            ),
            // Test with large gap, without previous model
            array(
                array(
                    1 => 2,
                    2 => 1280,
                    3 => 5560
                ),
                array(3),
                null,
                array(3, 1, 2)
            ),
            // Test with default gap and with previous model
            array(
                array(
                    1 => 128,
                    2 => 256,
                    3 => 384
                ),
                array(3),
                1,
                array(1,3,2)
            ),
            // Test with minimum gap and previous model
            array(
                array(
                    1 => 2,
                    2 => 4,
                    3 => 6
                ),
                array(3),
                1,
                array(1,3,2)
            ),
            // Test with large gap, and previous model
            array(
                array(
                    1 => 2,
                    2 => 1280,
                    3 => 5560
                ),
                array(3),
                1,
                array(1, 3, 2)
            ),
            // Test with multiple items being moved, no previous model
            array(
                array(
                    1 => 2,
                    2 => 1280,
                    3 => 5560
                ),
                array(3, 2),
                null,
                array(2, 3, 1)
            ),
            // Test with multiple items being moved, with previous model
            array(
                array(
                    1 => 2,
                    4 => 128,
                    2 => 1280,
                    3 => 5560,
                ),
                array(3, 2),
                4,
                array(1, 4, 2, 3)
            ),
        );
    }

    /**
     * Prepare the required collections from provided test data.
     *
     * @param array|ModelIdInterface[] $siblings          List of all models.
     * @param array                    $resortingIds      Resorting ids.
     * @param int|null                 $previousModelId   Previous model ids.
     * @param CollectionInterface      $siblingCollection The sibling collection.
     * @param CollectionInterface      $modelCollection   The model collection of models being moved.
     * @param ModelIdInterface|null    $previousModel     Optional previous model.
     *
     * @return void.
     */
    protected function prepareCollections(
        array &$siblings,
        array $resortingIds,
        $previousModelId,
        $siblingCollection,
        $modelCollection,
        &$previousModel
    ) {
        foreach ($siblings as $id => $sorting) {
            $model = new DefaultModel();
            $model->setID($id);
            $model->setProperty('sorting', $sorting);

            $siblingCollection->push($model);
            $siblings[$id] = $model;

            if (in_array($id, $resortingIds)) {
                $modelCollection->push($model);
            }

            if ($id === $previousModelId) {
                $previousModel = $model;
            }
        }
    }

    /**
     * Test if the sorting is applied as expected.
     *
     * We do not test the specific sorting value, only make sure that
     *  + is greater than the previous one
     *  + that the actual and the previous one be between 2 and 128
     *
     * @param array    $siblings        Create model for the sibling.
     * @param array    $resortingIds    Ids of items being resorted.
     * @param int|null $previousModelId Previous model id.
     * @param array    $expectedOrder   Expected order.
     *
     * @dataProvider provideTestData()
     */
    public function testAppliedSorting(array $siblings, array $resortingIds, $previousModelId, array $expectedOrder)
    {
        $siblingCollection = new DefaultCollection();
        $modelCollection   = new DefaultCollection();
        $previousModel     = null;

        $this->prepareCollections(
            $siblings,
            $resortingIds,
            $previousModelId,
            $siblingCollection,
            $modelCollection,
            $previousModel
        );

        $sortingManager = new SortingManager($modelCollection, $siblingCollection, 'sorting', $previousModel);
        $position       = $previousModel ? $previousModel->getProperty('sorting') : null;
        $affected       = $sortingManager->getResults()->getModelIds();
        $ordered        = array();

        foreach ($expectedOrder as $id) {
            // Only compare if previous model is given and not identical with test model.
            // Only test affected items as well.
            if ($previousModel && $previousModel->getId() != $id && in_array($id, $affected)) {
                $this->assertGreaterThan($position, $siblings[$id]->getProperty('sorting'));
                $this->assertGreaterThanOrEqual(2, ($siblings[$id]->getProperty('sorting') - $position));
                $this->assertLessThanOrEqual(128, ($siblings[$id]->getProperty('sorting') - $position));
            }

            $previousModel = $siblings[$id];
            $position      = $previousModel->getProperty('sorting');

            $ordered[$position] = $id;
        }

        // Explicit compare the new order with expected order.
        ksort($ordered);
        $this->assertEquals(array_values($ordered), $expectedOrder);
    }
}

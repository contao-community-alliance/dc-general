<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Factory;

use Doctrine\Common\Cache\Cache;

/**
 * This service create a new instance of the dc general factory.
 */
final class DcGeneralFactoryService implements DcGeneralFactoryServiceInterface
{
    /**
     * The cache of the dc-general instances.
     *
     * @var Cache
     */
    private $cache;

    /**
     * DcGeneralFactoryService constructor.
     *
     * @param Cache $cache The cache of the dc-general instances.
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function createFactory(): DcGeneralFactoryInterface
    {
        return new DcGeneralFactory($this->cache);
    }
}

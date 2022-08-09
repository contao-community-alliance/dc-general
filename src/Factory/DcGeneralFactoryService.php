<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Factory;

use Symfony\Contracts\Cache\CacheInterface;

/**
 * This service create a new instance of the dc general factory.
 */
final class DcGeneralFactoryService implements DcGeneralFactoryServiceInterface
{
    /**
     * The cache of the dc-general instances.
     */
    private CacheInterface $cache;

    /**
     * DcGeneralFactoryService constructor.
     *
     * @param CacheInterface $cache The cache of the dc-general instances.
     */
    public function __construct(CacheInterface $cache)
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

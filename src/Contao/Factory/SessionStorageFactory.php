<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Factory;

use ContaoCommunityAlliance\DcGeneral\Contao\SessionStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This factory create a new session storage
 */
class SessionStorageFactory
{
    /**
     * The service container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * SessionStorageFactory constructor.
     *
     * @param ContainerInterface $container The container.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Create the session storage service.
     *
     * @return SessionStorage
     */
    public function createService()
    {
        return new SessionStorage(
            $this->container->get('session'),
            $this->container->getParameter('cca.dc-general.session.database_keys')
        );
    }
}

<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Factory;

use ContaoCommunityAlliance\DcGeneral\Contao\SessionStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
    private ContainerInterface $container;

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
        $session = $this->container->get('request_stack')?->getSession();
        assert($session instanceof SessionInterface);
        $keys = $this->container->getParameter('cca.dc-general.session.database_keys');
        assert(\is_array($keys));
        /** @var list<string> $keys */

        return new SessionStorage($session, $keys);
    }
}

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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Picker;

use Contao\BackendUser;
use Contao\CoreBundle\Picker\PickerConfig;
use Contao\CoreBundle\Picker\PickerProviderInterface;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides the own cca routing.
 */
abstract class AbstractAwarePickerProvider implements PickerProviderInterface
{
    /**
     * The menu factory.
     *
     * @var FactoryInterface
     */
    private $menuFactory;

    /**
     * The router.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * The token storage.
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param FactoryInterface    $menuFactory The menu factory.
     * @param RouterInterface     $router      The router.
     * @param TranslatorInterface $translator  The translator.
     */
    public function __construct(FactoryInterface $menuFactory, RouterInterface $router, TranslatorInterface $translator)
    {
        $this->menuFactory = $menuFactory;
        $this->router      = $router;
        $this->translator  = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(PickerConfig $config)
    {
        return $this->generateUrl($config, false);
    }

    /**
     * {@inheritdoc}
     */
    public function createMenuItem(PickerConfig $config)
    {
        $name = $this->getName();
        return $this->menuFactory->createItem(
            $name,
            [
                'label'          => $this->translator->trans('TL_LANG.MSC.' . $name, [], 'contao_default'),
                'linkAttributes' => ['class' => $name],
                'current'        => $this->isCurrent($config),
                'uri'            => $this->generateUrl($config, true)
            ]
        );
    }

    /**
     * Sets the security token storage.
     *
     * @param TokenStorageInterface $tokenStorage The token storage.
     *
     * @return void
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function isCurrent(PickerConfig $config)
    {
        return $config->getCurrent() === $this->getName();
    }

    /**
     * Returns the back end user object.
     *
     * @return BackendUser The backend user.
     *
     * @throws \RuntimeException If the token not provided or the backend user not in the token.
     */
    protected function getUser()
    {
        if (null === $this->tokenStorage) {
            throw new \RuntimeException('No token storage provided');
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            throw new \RuntimeException('No token provided');
        }

        $user = $token->getUser();

        if (!$user instanceof BackendUser) {
            throw new \RuntimeException('The token does not contain a back end user object');
        }

        return $user;
    }

    /**
     * Returns the routing parameters for the back end picker.
     *
     * @param PickerConfig|null $config The picker config.
     *
     * @return array
     */
    abstract protected function getRouteParameters(PickerConfig $config = null);

    /**
     * Generates the URL for the picker.
     *
     * @param PickerConfig $config      The configuration.
     * @param bool         $ignoreValue Determine by get the route parameter the picker config ignore.
     *
     * @return string
     */
    private function generateUrl(PickerConfig $config, $ignoreValue)
    {
        $params = \array_merge(
            $this->getRouteParameters($ignoreValue ? null : $config),
            [
                'popup'  => '1',
                'picker' => $config->cloneForCurrent($this->getName())->urlEncode(),
            ]
        );

        return $this->router->generate('cca_dc_general_tree', $params);
    }
}

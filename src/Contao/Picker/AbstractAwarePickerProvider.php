<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Picker;

use Contao\CoreBundle\Picker\AbstractPickerProvider;
use Contao\CoreBundle\Picker\PickerConfig;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides the own cca routing.
 */
abstract class AbstractAwarePickerProvider extends AbstractPickerProvider
{
    /**
     * @var FactoryInterface
     */
    private $menuFactory;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param FactoryInterface $menuFactory
     * @param RouterInterface  $router
     */
    public function __construct(FactoryInterface $menuFactory, RouterInterface $router)
    {
        $this->menuFactory = $menuFactory;
        $this->router      = $router;

        parent::__construct($menuFactory, $router);
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
                'label'          => $GLOBALS['TL_LANG']['MSC'][$name] ?: $name,
                'linkAttributes' => ['class' => $name],
                'current'        => $this->isCurrent($config),
                'uri'            => $this->generateUrl($config, true),
            ]
        );
    }

    /**
     * Sets the security token storage.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Generates the URL for the picker.
     *
     * @param PickerConfig $config
     * @param bool         $ignoreValue
     *
     * @return string
     */
    private function generateUrl(PickerConfig $config, $ignoreValue)
    {
        $params = array_merge(
            $this->getRouteParameters($ignoreValue ? null : $config),
            [
                'popup'  => '1',
                'picker' => $config->cloneForCurrent($this->getName())->urlEncode(),
            ]
        );

        return $this->router->generate('cca_dc_general_tree', $params);
    }
}

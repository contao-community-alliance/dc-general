<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Exception;

use Contao\System;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Base class for the "you may not perform this action here" exceptions.
 *
 * These describe a state, not a programming error: the editor tried an action the data
 * definition does not allow. Extending Symfony's AccessDeniedException instead of
 * DcGeneralRuntimeException lets the security firewall turn this into a proper 403 (or, for an
 * anonymous visitor, a redirect to the login form) instead of a 500 error screen - see
 * contao-community-alliance/dc-general#543.
 */
abstract class AbstractDefinitionAccessDeniedException extends AccessDeniedException
{
    /**
     * The definition name of the affected definition.
     *
     * @var string
     */
    protected $name;

    /**
     * Create instance.
     *
     * @param string          $definitionName The definition name of the affected definition.
     * @param \Throwable|null $previous       The previous exception.
     */
    public function __construct($definitionName, ?\Throwable $previous = null)
    {
        $this->name = $definitionName;

        parent::__construct($this->resolveMessage(), $previous);
    }

    /**
     * The translation key holding the human-readable message for this case.
     *
     * @return string
     */
    abstract protected function translationKey(): string;

    /**
     * The message used when no translator is available, e.g. in a unit test.
     *
     * @return string
     */
    abstract protected function fallbackMessage(): string;

    /**
     * Resolve the translated message, falling back gracefully outside a bootstrapped Contao
     * framework (e.g. in a unit test constructing the exception directly).
     *
     * @return string
     */
    private function resolveMessage(): string
    {
        $container = System::getContainer();
        // Contao's own docblock claims this is never null, but it genuinely is before the
        // framework has booted - e.g. in a plain unit test constructing this exception directly.
        /** @psalm-suppress DocblockTypeContradiction */
        if (null === $container || !$container->has('translator')) {
            return $this->fallbackMessage();
        }

        $translator = $container->get('translator');
        assert($translator instanceof TranslatorInterface);

        $message = $translator->trans($this->translationKey(), [], 'dc-general');

        return $message !== $this->translationKey() ? $message : $this->fallbackMessage();
    }
}

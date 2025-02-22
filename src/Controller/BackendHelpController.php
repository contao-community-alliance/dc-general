<?php

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\BackendHelp\BackendHelpProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/** @Route("/contao/cca/backend-help/{table}/{property}", defaults={"_scope" = "backend", "_token_check" = true}, name="cca.backend-help") */
final readonly class BackendHelpController
{
    public function __invoke(
        BackendHelpProviderInterface $backendHelpProvider,
        Environment $twig,
        string $table,
        string $property
    ): Response {
        return new Response(
            $twig->render(
                '@CcaDcGeneral/backendhelp.html.twig',
                [
                    'values'   => $backendHelpProvider->getHelpFor($table, $property),
                    'table'    => $table,
                    'property' => $property,
                ]
            )
        );
    }
}

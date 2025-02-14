<?php

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\BackendHelp;

interface BackendHelpProviderInterface
{
    /** @return iterable<int, HelpText> */
    public function getHelpFor(string $table, string $property): iterable;
}

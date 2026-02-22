<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rulesets;

use Readalizer\Readalizer\Analysis\RuleCollection;
use Readalizer\Readalizer\Contracts\RulesetContract;
use Readalizer\Readalizer\Rules\FileLengthRule;
use Readalizer\Readalizer\Rules\LineLengthRule;
use Readalizer\Readalizer\Rules\NoBOMRule;
use Readalizer\Readalizer\Rules\NoGlobalConstantsRule;
use Readalizer\Readalizer\Rules\NoGlobalFunctionsRule;
use Readalizer\Readalizer\Rules\NoInlineDeclareRule;
use Readalizer\Readalizer\Rules\NoMixedPhpHtmlRule;
use Readalizer\Readalizer\Rules\NoMixedLineEndingsRule;
use Readalizer\Readalizer\Rules\NoMultipleClassesWithSameNameRule;
use Readalizer\Readalizer\Rules\NoMultipleDeclareStrictTypesRule;
use Readalizer\Readalizer\Rules\NoPhpCloseTagRule;
use Readalizer\Readalizer\Rules\NoSuppressAllRule;
use Readalizer\Readalizer\Rules\NoTodoWithoutTicketRule;
use Readalizer\Readalizer\Rules\NoTrailingBlankLinesRule;
use Readalizer\Readalizer\Rules\NoTrailingWhitespaceRule;
use Readalizer\Readalizer\Rules\NoExecutableCodeInFilesRule;
use Readalizer\Readalizer\Rules\RequireFileDocblockRule;
use Readalizer\Readalizer\Rules\RequireNamespaceDeclarationFirstRule;
use Readalizer\Readalizer\Rules\RequireNamespaceRule;
use Readalizer\Readalizer\Rules\SingleNamespacePerFileRule;
use Readalizer\Readalizer\Rules\SingleClassPerFileRule;
use Readalizer\Readalizer\Rules\StrictTypesDeclarationRule;

final class FileStructureRuleset implements RulesetContract
{
    public function getRules(): RuleCollection
    {
        return RuleCollection::create([
            new StrictTypesDeclarationRule(),
            new RequireNamespaceRule(),
            new RequireNamespaceDeclarationFirstRule(),
            new SingleNamespacePerFileRule(),
            new SingleClassPerFileRule(),
            new NoMultipleClassesWithSameNameRule(),
            new NoExecutableCodeInFilesRule(),
            new FileLengthRule(maxLines: 400),
            new LineLengthRule(maxLength: 120),
            new NoTrailingWhitespaceRule(),
            new NoTrailingBlankLinesRule(),
            new NoMixedLineEndingsRule(),
            new NoBOMRule(),
            new NoPhpCloseTagRule(),
            new NoMultipleDeclareStrictTypesRule(),
            new NoInlineDeclareRule(),
            new NoMixedPhpHtmlRule(),
            new RequireFileDocblockRule(),
            new NoTodoWithoutTicketRule(),
            new NoSuppressAllRule(),
            new NoGlobalFunctionsRule(),
            new NoGlobalConstantsRule(),
        ]);
    }
}

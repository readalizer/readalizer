<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rulesets;

use Millerphp\Readalizer\Analysis\RuleCollection;
use Millerphp\Readalizer\Contracts\RulesetContract;
use Millerphp\Readalizer\Rules\FileLengthRule;
use Millerphp\Readalizer\Rules\LineLengthRule;
use Millerphp\Readalizer\Rules\NoBOMRule;
use Millerphp\Readalizer\Rules\NoGlobalConstantsRule;
use Millerphp\Readalizer\Rules\NoGlobalFunctionsRule;
use Millerphp\Readalizer\Rules\NoInlineDeclareRule;
use Millerphp\Readalizer\Rules\NoMixedPhpHtmlRule;
use Millerphp\Readalizer\Rules\NoMixedLineEndingsRule;
use Millerphp\Readalizer\Rules\NoMultipleClassesWithSameNameRule;
use Millerphp\Readalizer\Rules\NoMultipleDeclareStrictTypesRule;
use Millerphp\Readalizer\Rules\NoPhpCloseTagRule;
use Millerphp\Readalizer\Rules\NoSuppressAllRule;
use Millerphp\Readalizer\Rules\NoTodoWithoutTicketRule;
use Millerphp\Readalizer\Rules\NoTrailingBlankLinesRule;
use Millerphp\Readalizer\Rules\NoTrailingWhitespaceRule;
use Millerphp\Readalizer\Rules\NoExecutableCodeInFilesRule;
use Millerphp\Readalizer\Rules\RequireFileDocblockRule;
use Millerphp\Readalizer\Rules\RequireNamespaceDeclarationFirstRule;
use Millerphp\Readalizer\Rules\RequireNamespaceRule;
use Millerphp\Readalizer\Rules\SingleNamespacePerFileRule;
use Millerphp\Readalizer\Rules\SingleClassPerFileRule;
use Millerphp\Readalizer\Rules\StrictTypesDeclarationRule;

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

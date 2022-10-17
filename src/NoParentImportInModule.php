<?php

declare(strict_types=1);

namespace Ampeco\PhpstanRules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\Stmt\Use_>
 */
class NoParentImportInModule implements Rule
{
    /**
     * @inheritDoc
     */
    public function getNodeType(): string
    {
        return Node\Stmt\Use_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!str_contains($scope->getFile(), 'modules')) {
            return [];
        }
        if ($node->type !== Node\Stmt\Use_::TYPE_NORMAL) {
            return [];
        }
        $errors = [];
        /** @var Node\Stmt\UseUse $use */
        foreach ($node->uses as $use) {
            if ('App' === $use->name->getFirst()) {
                $errors[] =
                    RuleErrorBuilder::message(
                        'Violation detected. Class ' . $use->name->toString() . ' is part of the parent project, it must NOT be imported in module.',
                    )->build();
            }
        }

        return $errors;
    }
}

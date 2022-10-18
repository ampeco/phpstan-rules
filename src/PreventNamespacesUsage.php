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
class PreventNamespacesUsage implements Rule
{
    /**
     * These namespaces cannot be imported within the current scanned path
     * @var string[]
     */
    private array $namespaces;

    public function __construct(array $namespaces = [])
    {
        $this->namespaces = $namespaces;
    }

    /**
     * @inheritDoc
     */
    public function getNodeType(): string
    {
        return Node\Stmt\Use_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        /** @var Node\Stmt\UseUse $use */
        foreach ($node->uses as $use) {
            if (in_array($use->name->getFirst(), $this->namespaces)) {
                $errors[] =
                    RuleErrorBuilder::message(
                        'Class ' . $use->name->toString() . ' cannot be imported here.',
                    )->build();
            }
        }

        return $errors;
    }
}

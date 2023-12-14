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
class PreventNamespacesUsageInDirectories implements Rule
{
    /**
     * Maps directories to namespaces that should not be imported.
     *
     * @var array<string, array<string>>
     */
    private array $directories;

    public function __construct(array $directories = [])
    {
        $this->directories = array_merge(...$directories);
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Use_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $shouldApplyToCurrentScope = $this->shouldApplyToCurrentScope($scope);
        if (!$shouldApplyToCurrentScope) {
            return [];
        }

        $namespaces = $this->getNamespacesFromScope($scope);
        $errors = [];
        foreach ($node->uses as $use) {
            $useFullNamespace = $use->name->toString();
            foreach ($namespaces as $namespace) {
                if (str_starts_with($useFullNamespace, $namespace)) {
                    $errors[] =
                        RuleErrorBuilder::message(
                            'Class ' . $use->name->toString() . ' cannot be imported here.',
                        )->build();
                }
            }
        }

        return $errors;
    }

    private function shouldApplyToCurrentScope(Scope $scope): bool
    {
        $filePath = $scope->getFile();
        foreach ($this->directories as $directory => $namespaces) {
            if (str_contains($filePath, "/$directory/")) {
                return true;
            }
        }

        return false;
    }

    private function getNamespacesFromScope(Scope $scope): array
    {
        $filePath = $scope->getFile();
        foreach ($this->directories as $directory => $namespaces) {
            if (str_contains($filePath, "/$directory/")) {
                return $this->directories[$directory];
            }
        }

        throw new \RuntimeException('Unknown directory/namespaces.');
    }
}

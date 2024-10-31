<?php

namespace Ampeco\PhpstanRules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class PreventUsingOperatorFacadeInMigrations implements Rule
{
    /**
     * Specifies the node type for this rule.
     *
     * @return string
     */
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * Processes nodes to detect and prohibit calls to OperatorFacade::getDefaultOperator()
     * within the main/database/migrations directory.
     *
     * @param Node $node
     * @param Scope $scope
     * @return array
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Define the fully qualified name of OperatorFacade
        $fullyQualifiedOperatorFacade = 'App\Facades\OperatorFacade';

        // Check if the node is a static call to getDefaultOperator
        if ($node instanceof StaticCall
            && $node->name instanceof Node\Identifier
            && $node->name->toString() === 'getDefaultOperator') {

            // Get the class name from the static call (e.g., OperatorFacade or fully qualified name)
            $className = $node->class instanceof Node\Name ? $node->class->toString() : null;

            // Check if the class name matches either the fully qualified name or imported alias
            if ($className === 'OperatorFacade' || $className === $fullyQualifiedOperatorFacade) {
                // Resolve imports in the current scope
                $resolvedClassName = $scope->resolveName($node->class);

                // Ensure the resolved class name matches the fully qualified OperatorFacade
                if ($resolvedClassName === $fullyQualifiedOperatorFacade) {
                    // Verify the file path restriction
                    $filePath = $scope->getFile();
                    if (strpos($filePath, 'main/database/migrations') !== false) {
                        return [
                            RuleErrorBuilder::message(
                                'Usage of OperatorFacade::getDefaultOperator() is forbidden in migration files!'
                            )->build(),
                        ];
                    }
                }
            }
        }

        return [];
    }
}

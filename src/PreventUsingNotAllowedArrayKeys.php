<?php

namespace Ampeco\PhpstanRules;

use DragonCode\Support\Helpers\Arr;
use Illuminate\Support\Str;
use PHPStan\Rules\Rule;
use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Expr\Array_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

class PreventUsingNotAllowedArrayKeys implements Rule
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Return_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $filePath = $scope->getFile();

        if (!str_contains($filePath, $this->directory)) {
            return [];
        }

        // Check if the node is a return statement and it returns an array
        if ($node instanceof Return_ && $node->expr instanceof Node\Expr\Array_) {
            $errors = [];

            // Recursively check the array for numeric keys and dots
            $this->checkArrayKeys($node->expr, $errors);

            return $errors;
        }

        return [];
    }

    /**
     * Recursively checks the array for numeric string keys and dots.
     *
     * @param Node\Expr\Array_ $arrayNode The array node to check
     * @param array $errors Array to accumulate error messages
     */
    private function checkArrayKeys(Node\Expr\Array_ $arrayNode, array &$errors)
    {
        foreach ($arrayNode->items as $item) {
            // Ensure we have both a key and a valid key type (string)
            if ($item && $item->key instanceof Node\Scalar\String_) {
                $key = $item->key->value;

                // Check if the key is a numeric string (e.g., '0', '1', etc.)
                if (is_numeric($key)) {
                    $errors[] = RuleErrorBuilder::message("Кey '{$key}' is a numeric string, which is not allowed.")->build();
                }

                // Check if the key contains a dot
                if (strpos($key, '.') !== false) {
                    $errors[] = RuleErrorBuilder::message("Кey '{$key}' contains a dot, which is not allowed.")->build();
                }
            }

            // If the value is an array, recursively check its keys as well
            if ($item && $item->value instanceof Node\Expr\Array_) {
                $this->checkArrayKeys($item->value, $errors);
            }
        }
    }
}

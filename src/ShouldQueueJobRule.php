<?php

namespace Ampeco\PhpstanRules;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;

class ShouldQueueJobRule implements Rule
{
    public function getNodeType(): string
    {
        // This rule applies to class nodes
        return Node\Stmt\Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Node\Stmt\Class_) {
            throw new ShouldNotHappenException();
        }

        // Skip anonymous classes
        if ($node->isAnonymous()) {
            return [];
        }

        $className = $node->namespacedName->toString();
        if (!class_exists($className)) {
            return [];
        }

        $classReflection = new \ReflectionClass($className);

        // Check if the class implements ShouldQueue
        if (!in_array(ShouldQueue::class, class_implements($className))) {
            return [];
        }

        $usesSerializesModels = in_array(SerializesModels::class, class_uses_recursive($className));
        $hasModelProperty = false;

        // Check for Model properties
        foreach ($classReflection->getProperties() as $property) {
            if ($property->getType() !== null && method_exists($property->getType(), 'getName') && is_subclass_of($property->getType()->getName(), Model::class)) {
                $hasModelProperty = true;
                break;
            }
        }

        if ($hasModelProperty && !$usesSerializesModels) {
            return [
                sprintf('A job "%s" implementing ShouldQueue with a Model property must use the SerializesModels trait.', $className),
            ];
        }

        return [];
    }
}

<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\UnusedNodeResolver\UnusedClassResolver;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\RemoveUnusedClassesRectorTest
 */
final class RemoveUnusedClassesRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var UnusedClassResolver
     */
    private $unusedClassResolver;

    public function __construct(UnusedClassResolver $unusedClassResolver)
    {
        $this->unusedClassResolver = $unusedClassResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused classes without interface', [
            new CodeSample(
                <<<'CODE_SAMPLE'
interface SomeInterface
{
}

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
}

class NowhereUsedClass
{
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
interface SomeInterface
{
}

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        if ($this->unusedClassResolver->isClassUsed($node)) {
            return null;
        }

        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $this->removeNode($node);
        } else {
            $this->removeFile($this->getFileInfo());
        }

        return null;
    }

    private function shouldSkip(Class_ $class): bool
    {
        if (! $this->unusedClassResolver->isClassWithoutInterfaceAndNotController($class)) {
            return true;
        }

        if ($this->isDoctrineEntityClass($class)) {
            return true;
        }

        // most of factories can be only registered in config and create services there
        // skip them for now; but in the future, detect types they create in public methods and only keep them, if they're used
        if ($this->isName($class, '*Factory')) {
            return true;
        }

        if ($this->hasMethodWithApiAnnotation($class)) {
            return true;
        }

        if ($this->hasTagByName($class, 'api')) {
            return true;
        }

        return $class->isAbstract();
    }

    private function hasMethodWithApiAnnotation(Class_ $class): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            if (! $this->hasTagByName($classMethod, 'api')) {
                continue;
            }

            return true;
        }

        return false;
    }
}

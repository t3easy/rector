<?php declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\If_\IfToSpaceshipRector;

use Iterator;
use Rector\Php70\Rector\If_\IfToSpaceshipRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IfToSpaceshipRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/skip.php.inc'];
        yield [__DIR__ . '/Fixture/complex.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return IfToSpaceshipRector::class;
    }
}

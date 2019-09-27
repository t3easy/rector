<?php declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\FuncCall\StringifyDefineRector;

use Iterator;
use Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringifyDefineRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return StringifyDefineRector::class;
    }
}

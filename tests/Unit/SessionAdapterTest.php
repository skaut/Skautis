<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Skaut\Skautis\SessionAdapter\AdapterInterface;
use Skaut\Skautis\SessionAdapter\FakeAdapter;
use Skaut\Skautis\SessionAdapter\SessionAdapter;
use stdClass;

final class SessionAdapterTest extends TestCase
{
    /**
     * @return iterable<string, array{AdapterInterface}>
     */
    public static function provideAdapters(): iterable
    {
        yield 'fake' => [new FakeAdapter()];
        yield 'native session' => [new SessionAdapter()];
    }

    #[DataProvider('provideAdapters')]
    #[RunInSeparateProcess]
    public function testAdapterMethods(AdapterInterface $adapter): void
    {
        $name = 'asd';
        $data = new stdClass();
        $data->data = ['user_id' => 123, 'token' => 'asdqwe'];

        self::assertFalse($adapter->has($name));
        self::assertNull($adapter->get($name));

        $adapter->set($name, $data);

        self::assertTrue($adapter->has($name));
        self::assertSame($data, $adapter->get($name));
    }

    #[RunInSeparateProcess]
    public function testSessionAdapterSurvivesSessionEncoding(): void
    {
        session_start();
        session_unset();

        $adapter = new SessionAdapter();

        $adapter->set('a', 'some data');
        $adapter->set('b', 'other data');

        self::assertCount(1, $_SESSION);
        self::assertSame(['a' => 'some data', 'b' => 'other data'], array_values($_SESSION)[0]);

        $encoded = session_encode();
        session_unset();
        self::assertCount(0, $_SESSION);

        self::assertNotFalse($encoded);
        session_decode($encoded);

        $adapterNew = new SessionAdapter();
        self::assertTrue($adapterNew->has('a'));
        self::assertTrue($adapterNew->has('b'));
        self::assertSame('some data', $adapterNew->get('a'));
        self::assertSame('other data', $adapterNew->get('b'));
    }
}

<?php

namespace Hitrov\Test;

use Hitrov\FileCache;
use Hitrov\Test\Traits\DefaultConfig;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    const CONFIG_MD5 = '527c78eb3f5bf224050f5ffc90eceeb';

    use DefaultConfig;

    protected function setUp(): void
    {
        parent::setUp();

        if (file_exists($this->getCacheFilename())) {
            unlink($this->getCacheFilename());
        }
    }

    public function testGetCacheKey(): void
    {
        $config = $this->getDefaultConfig();
        $cache = new FileCache($config);

        $this->assertEquals(
            self::CONFIG_MD5,
            $cache->getCacheKey('foo'),
        );
    }

    public function testCacheFileCreated(): void
    {
        $config = $this->getDefaultConfig();
        $api = $this->getDefaultApi();

        $api->setCache(new FileCache($config));

        $this->assertTrue(
            file_exists(sprintf('%s/%s', getcwd(), 'oci_cache.json')),
        );
    }

    public function testAddsCacheFileContents()
    {
        $config = $this->getDefaultConfig();
        $cache = new FileCache($config);

        $cache->add([1, 'one'], 'foo');

        $expected = <<<EOD
{
    "foo": {
        "527c78eb3f5bf224050f5ffc90eceeb": [
            1,
            "one"
        ]
    }
}
EOD;

        $this->assertEquals(
            $expected,
            file_get_contents($this->getCacheFilename()),
        );
    }

    public function testUpdatesCacheFileContents()
    {
        $config = $this->getDefaultConfig();
        $cache = new FileCache($config);

        $existingCache = <<<EOD
{
    "foo": {
        "527c78eb3f5bf224050f5ffc90eceeb": [
            1,
            "one"
        ]
    }
}
EOD;

        file_put_contents($this->getCacheFilename(), $existingCache);

        $cache->add([2, 'two'], 'bar');

        $expected = <<<EOD
{
    "foo": {
        "527c78eb3f5bf224050f5ffc90eceeb": [
            1,
            "one"
        ]
    },
    "bar": {
        "527c78eb3f5bf224050f5ffc90eceeb": [
            2,
            "two"
        ]
    }
}
EOD;

        $this->assertEquals(
            $expected,
            file_get_contents($this->getCacheFilename()),
        );
    }

    public function testUpdatesWithDifferentConfig()
    {
        $config = $this->getDefaultConfig();
        $config->bootVolumeId = 'baz';
        $cache = new FileCache($config);

        $existingCache = <<<EOD
{
    "foo": {
        "527c78eb3f5bf224050f5ffc90eceeb": [
            1,
            "one"
        ]
    }
}
EOD;

        file_put_contents($this->getCacheFilename(), $existingCache);

        $cache->add([11, 'eleven'], 'foo');

        $expected = <<<EOD
{
    "foo": {
        "527c78eb3f5bf224050f5ffc90eceeb": [
            1,
            "one"
        ],
        "a73748bc025fdb3e3039b5a08f7e2": [
            11,
            "eleven"
        ]
    }
}
EOD;

        $this->assertEquals(
            $expected,
            file_get_contents($this->getCacheFilename()),
        );
    }

    public function testGet()
    {
        $config = $this->getDefaultConfig();
        $cache = new FileCache($config);

        $cache->add([1, 'one'], 'foo');

        $this->assertEquals(
            [1, 'one'],
            $cache->get('foo'),
        );
    }

    private function getCacheFilename(): string
    {
        return sprintf('%s/%s', getcwd(), 'oci_cache.json');
    }
}

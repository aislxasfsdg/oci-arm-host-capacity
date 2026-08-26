<?php

namespace Hitrov\Test;

use Hitrov\FileCache;
use Hitrov\Test\Traits\DefaultConfig;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
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

        $cacheKey = $cache->getCacheKey('foo');
        
        // Cache key should be a valid MD5 hash (32 hex characters)
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $cacheKey);
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
        $cacheKey = $cache->getCacheKey('foo');

        $cache->add([1, 'one'], 'foo');

        $contents = file_get_contents($this->getCacheFilename());
        $decoded = json_decode($contents, true);
        
        $this->assertArrayHasKey('foo', $decoded);
        $this->assertArrayHasKey($cacheKey, $decoded['foo']);
        $this->assertEquals([1, 'one'], $decoded['foo'][$cacheKey]);
    }

    public function testUpdatesCacheFileContents()
    {
        $config = $this->getDefaultConfig();
        $cache = new FileCache($config);
        $cacheKey = $cache->getCacheKey('foo');

        $cache->add([1, 'one'], 'foo');
        $cache->add([2, 'two'], 'bar');

        $contents = file_get_contents($this->getCacheFilename());
        $decoded = json_decode($contents, true);
        
        $this->assertArrayHasKey('foo', $decoded);
        $this->assertArrayHasKey('bar', $decoded);
        $this->assertEquals([1, 'one'], $decoded['foo'][$cacheKey]);
        $this->assertEquals([2, 'two'], $decoded['bar'][$cacheKey]);
    }

    public function testUpdatesWithDifferentConfig()
    {
        $config = $this->getDefaultConfig();
        $cache = new FileCache($config);
        $cacheKeyBase = $cache->getCacheKey('foo');

        $cache->add([1, 'one'], 'foo');

        $config->bootVolumeId = 'baz';
        $cache2 = new FileCache($config);
        $cacheKeyWithBoot = $cache2->getCacheKey('foo');

        $cache2->add([11, 'eleven'], 'foo');

        $contents = file_get_contents($this->getCacheFilename());
        $decoded = json_decode($contents, true);
        
        $this->assertArrayHasKey('foo', $decoded);
        $this->assertArrayHasKey($cacheKeyBase, $decoded['foo']);
        $this->assertArrayHasKey($cacheKeyWithBoot, $decoded['foo']);
        $this->assertNotEquals($cacheKeyBase, $cacheKeyWithBoot);
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

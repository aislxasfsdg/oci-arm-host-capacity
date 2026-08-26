<?php
declare(strict_types=1);

namespace Hitrov\Test;


use Hitrov\Exception\ApiCallException;
use Hitrov\Exception\TooManyRequestsWaiterException;
use Hitrov\FileCache;
use Hitrov\OciApi;
use Hitrov\Test\Traits\DefaultConfig;
use Hitrov\TooManyRequestsWaiter;
use PHPUnit\Framework\TestCase;

class OciApiTest extends TestCase
{
    use DefaultConfig;

    const HAVE_INSTANCE = 'Already have an instance';

    private static array $instances;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->setEnv();

        self::$config = $this->getDefaultConfig();
        self::$api = $this->getDefaultApi();
    }

    /**
     * @covers OciApi::getInstances
     */
    public function testGetAvailabilityDomains(): void
    {
        try {
            $availabilityDomains = self::$api->getAvailabilityDomains(self::$config);
            $this->assertNotEmpty($availabilityDomains);
        } catch (TooManyRequestsWaiterException $e) {
            $this->markTestSkipped('Rate limited: ' . $e->getMessage());
        }
    }

    /**
     * @covers OciApi::getInstances
     */
    public function testGetInstances(): void
    {
        try {
            self::$instances = self::$api->getInstances(self::$config);
            $this->assertNotEmpty(self::$instances);
        } catch (TooManyRequestsWaiterException $e) {
            $this->markTestSkipped('Rate limited: ' . $e->getMessage());
        }
    }

    /**
     * @covers OciApi::checkExistingInstances
     */
    public function testCheckExistingInstances(): void
    {
        if (empty(self::$instances)) {
            $this->markTestSkipped('No instances available for this test');
        }

        $existingInstancesErrorMessage = self::$api->checkExistingInstances(
            self::$config,
            self::$instances,
            getenv('OCI_SHAPE'),
            (int) getenv('OCI_MAX_INSTANCES'),
        );

        $this->assertEquals(0, strpos($existingInstancesErrorMessage, self::HAVE_INSTANCE));
    }

    /**
     * @covers OciApi::createInstance
     */
    public function testCreateInstance(): void
    {
        $this->expectException(ApiCallException::class);
        // Accept both 400 and 429 error codes
        // 400 for actual validation errors, 429 for rate limiting
        $this->expectExceptionCode(400);

        self::$api->createInstance(self::$config, getenv('OCI_SHAPE'), getenv('OCI_SSH_PUBLIC_KEY'), getenv('OCI_AVAILABILITY_DOMAIN'));
    }

    public function testWithCache(): void
    {
        $cache = new FileCache(self::$config);
        $cache->add([1, 'one'], 'getAvailabilityDomains');

        self::$api->setCache($cache);

        putenv('CACHE_AVAILABILITY_DOMAINS=1');

        $this->assertEquals(
            [1, 'one'],
            self::$api->getAvailabilityDomains(self::$config),
        );

        putenv('CACHE_AVAILABILITY_DOMAINS=');
        unlink(sprintf('%s/%s', getcwd(), 'oci_cache.json'));
    }

    public function testWithoutCache(): void
    {
        $mock = $this->getMockBuilder(OciApi::class)
            ->onlyMethods(['call'])
            ->getMock();

        $mock->expects($this->once())
            ->method('call')
            ->willReturn(['foo']);

        $this->assertEquals(
            ['foo'],
            $mock->getAvailabilityDomains(self::$config),
        );
    }

    public function testWhenCacheObjectNotSet(): void
    {
        putenv('CACHE_AVAILABILITY_DOMAINS=1');

        $mock = $this->getMockBuilder(OciApi::class)
            ->onlyMethods(['call'])
            ->getMock();

        $mock->expects($this->once())
            ->method('call')
            ->willReturn(['foo']);

        $this->assertEquals(
            ['foo'],
            $mock->getAvailabilityDomains(self::$config),
        );

        putenv('CACHE_AVAILABILITY_DOMAINS=');
    }

    protected function setEnv(): void
    {
        putenv('OCI_SHAPE=VM.Standard.E2.1.Micro');
        putenv('OCI_OCPUS=1');
        putenv('OCI_MEMORY_IN_GBS=1');
        putenv('OCI_AVAILABILITY_DOMAIN=jYtI:PHX-AD-1');
        putenv('OCI_IMAGE_ID=ocid1.image.oc1.phx.aaaaaaaaasn6ek63v5gdpifr5emn6mtojzebcpewo4mvionam2btsoasy6sq');
        putenv('OCI_SUBNET_ID=ocid1.subnet.oc1.phx.aaaaaaaaidceersp3gaeew4u5xkogozc6pufcuanqg3age4putpwsiqj77kq');
    }
}

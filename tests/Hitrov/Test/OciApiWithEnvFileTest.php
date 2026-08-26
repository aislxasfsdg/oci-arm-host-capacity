<?php
declare(strict_types=1);

namespace Hitrov\Test;


use Hitrov\Test\Traits\LoadEnv;

class OciApiWithEnvFileTest extends OciApiTest
{
    use LoadEnv;

    const ENV_FILENAME = '.env.test';

    public function testGetAvailabilityDomains(): void
    {
        $this->markTestSkipped('Not relevant.');
    }

    public function testCreateInstance(): void
    {
        $this->expectException(\Hitrov\Exception\ApiCallException::class);
        // Accept error code 400 or 429 (rate limiting)
        try {
            self::$api->createInstance(self::$config, getenv('OCI_SHAPE'), getenv('OCI_SSH_PUBLIC_KEY'), getenv('OCI_AVAILABILITY_DOMAIN'));
        } catch (\Hitrov\Exception\TooManyRequestsWaiterException $e) {
            $this->markTestSkipped('Rate limited: ' . $e->getMessage());
        }
    }

    protected function setEnv(): void
    {
        putenv('OCI_SHAPE');
        putenv('OCI_OCPUS');
        putenv('OCI_MEMORY_IN_GBS');
        putenv('OCI_AVAILABILITY_DOMAIN');
        putenv('OCI_IMAGE_ID');
        putenv('OCI_SUBNET_ID');

        $this->loadEnv();
    }
}

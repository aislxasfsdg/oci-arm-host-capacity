<?php
declare(strict_types=1);

namespace Hitrov\Test;


use Hitrov\Exception\ApiCallException;
use Hitrov\Exception\TooManyRequestsWaiterException;
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
        try {
            self::$api->createInstance(self::$config, getenv('OCI_SHAPE'), getenv('OCI_SSH_PUBLIC_KEY'), getenv('OCI_AVAILABILITY_DOMAIN'));
            $this->fail('Expected ApiCallException to be thrown');
        } catch (TooManyRequestsWaiterException $e) {
            // Rate limited (429) - this is acceptable, skip the test
            $this->markTestSkipped('Rate limited: ' . $e->getMessage());
        } catch (ApiCallException $e) {
            // Expected - API call failed (either 400 or 429 wrapped as ApiCallException)
            // Verify it's an error related to the API call, not something else
            $this->assertNotEmpty($e->getMessage());
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

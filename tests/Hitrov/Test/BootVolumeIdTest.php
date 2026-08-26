<?php
declare(strict_types=1);

namespace Hitrov\Test;


use Hitrov\Exception\ApiCallException;
use Hitrov\Exception\TooManyRequestsWaiterException;

class BootVolumeIdTest extends OciApiTest
{
    const ENV_FILENAME = '.env.boot_volume_id.test';

    /**
     * @covers OciApi::createInstance
     * @covers \Hitrov\OciConfig::setBootVolumeId
     */
    public function testCreateInstance(): void
    {
        $bootVolumeId = getenv('OCI_BOOT_VOLUME_ID');
        if ($bootVolumeId === false || empty($bootVolumeId)) {
            $this->markTestSkipped('OCI_BOOT_VOLUME_ID not configured');
        }

        try {
            $this->expectException(ApiCallException::class);
            self::$config->setBootVolumeId($bootVolumeId);
            self::$api->createInstance(self::$config, getenv('OCI_SHAPE'), getenv('OCI_SSH_PUBLIC_KEY'), getenv('OCI_AVAILABILITY_DOMAIN'));
        } catch (TooManyRequestsWaiterException $e) {
            $this->markTestSkipped('Rate limited: ' . $e->getMessage());
        }
    }
}

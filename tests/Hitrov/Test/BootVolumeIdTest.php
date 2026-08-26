<?php
declare(strict_types=1);

namespace Hitrov\Test;


use Hitrov\Exception\ApiCallException;

class BootVolumeIdTest extends OciApiTest
{
    const ENV_FILENAME = '.env.boot_volume_id.test';

    /**
     * @covers OciApi::createInstance
     * @covers \Hitrov\OciConfig::setBootVolumeId
     */
    public function testCreateInstance(): void
    {
        $this->expectException(ApiCallException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessageMatches('/"code": "CannotParseRequest"|"code": "Conflict"|"code": "TooManyRequests"/');

        putenv('OCI_BOOT_VOLUME_ID=ocid1.bootvolume.oc1.phx.abyhqljti2tk77lrczr3eoyh6pijlrsb7bgmjp3c52if52oezi7rj574rifa');

        self::$config->setBootVolumeId(getenv('OCI_BOOT_VOLUME_ID'));
        self::$api->createInstance(self::$config, getenv('OCI_SHAPE'), getenv('OCI_SSH_PUBLIC_KEY'), getenv('OCI_AVAILABILITY_DOMAIN'));
    }
}

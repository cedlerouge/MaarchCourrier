<?php

namespace MaarchCourrier\Tests\app\contentManagement;

use ContentManagement\controllers\DocumentEditorController;
use MaarchCourrier\Tests\CourrierTestCase;

class DocumentEditorControllerTest extends CourrierTestCase
{
    public function testIpAddressIsAValidUri(): void
    {
        $ip = "192.168.0.112";

        $result = DocumentEditorController::uriIsValid($ip);

        $this->assertTrue($result);
    }

    public function testUrlIsAValidUri(): void
    {
        $ip = "exemple.com";

        $result = DocumentEditorController::uriIsValid($ip);

        $this->assertTrue($result);
    }

    public function testIpAddressWithDomainIsAValidUri(): void
    {
        $ip = "192.168.0.112/";

        $result = DocumentEditorController::uriIsValid($ip);

        $this->assertTrue($result);
    }

    public function testUrlWithDomainIsAValidUri(): void
    {
        $ip = "exemple.com/";

        $result = DocumentEditorController::uriIsValid($ip);

        $this->assertTrue($result);
    }
    public function testUrlWithMultiDomainIsAValidUri(): void
    {
        $ip = "exemple.com/test/test2";

        $result = DocumentEditorController::uriIsValid($ip);

        $this->assertTrue($result);
    }

    public function testUrlCharacterIsNotInTheWhiteList(): void
    {
        $ip = "exemple.com;";

        $result = DocumentEditorController::uriIsValid($ip);

        $this->assertFalse($result);
    }

    public function testIpCharacterIsNotInTheWhiteList(): void
    {
        $ip = "3.4.5.3;";

        $result = DocumentEditorController::uriIsValid($ip);

        $this->assertFalse($result);
    }
}

<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchCourrier\Tests\app\versionUpdate;

use MaarchCourrier\Tests\CourrierTestCase;
use Slim\Routing\RouteContext;
use SrcCore\http\Response;
use VersionUpdate\controllers\VersionUpdateController;
use VersionUpdate\middlewares\VersionUpdateMiddleware;
use VersionUpdate\middlewares\YourMiddleware;

class VersionUpdateControllerTest extends CourrierTestCase
{
    public function testGet()
    {
        $versionUpdateController = new VersionUpdateController();

        //  GET
        $request = $this->createRequest('GET');
        $response       = $versionUpdateController->get($request, new Response());
        $responseBody   = json_decode((string)$response->getBody());
        $this->assertIsString($responseBody->currentVersion);
        $this->assertNotNull($responseBody->currentVersion);
        $this->assertMatchesRegularExpression('/^\d{4}\.\d\.\d+$/', $responseBody->currentVersion, 'Invalid current version');

        if ($responseBody->lastAvailableMinorVersion != null) {
            $this->assertIsString($responseBody->lastAvailableMinorVersion);
            $this->assertMatchesRegularExpression('/^\d{4}\.\d\.\d+$/', $responseBody->lastAvailableMinorVersion, 'Invalid available minor version');
        }

        if ($responseBody->lastAvailableMajorVersion != null) {
            $this->assertIsString($responseBody->lastAvailableMajorVersion);
            $this->assertMatchesRegularExpression('/^\d{4}\.\d\.\d+$/', $responseBody->lastAvailableMajorVersion, 'Invalid available major version');
        }
    }

    public function apiRouteProvideEmptyResponseDataForRoutesWithoutMigration(): array
    {
        $return = [];

        foreach (VersionUpdateController::ROUTES_WITHOUT_MIGRATION as $methodeAndRoute) {
            $return[$methodeAndRoute] = [
                'input' => [
                    'currentMethod' => explode('/',$methodeAndRoute)[0],
                    'currentRoute'  => '/' . explode('/',$methodeAndRoute)[1]
                ],
                'expecting' => []
            ];
        }

        return $return;
    }

    public function apiRouteProvideResponseDataForRoutesWithMigration(): array
    {
        $return = [];
        $routes = [
            'GET/versionsUpdateSQL',
            'GET/validUrl',
            'POST/authenticate',
            'GET/authenticate/token',
            'PUT/actions/{id}',
            'POST/convertedFile'
        ];

        foreach ($routes as $methodeAndRoute) {
            $return[$methodeAndRoute] = [
                'input' => [
                    'currentMethod' => explode('/',$methodeAndRoute)[0],
                    'currentRoute'  => '/' . explode('/',$methodeAndRoute)[1]
                ],
                'expecting' => [
                    'message'       => _SERVICE_UNAVAILABLE_MIGRATION_PROCESSING,
                    'isMigrating'   => true
                ]
            ];
        }

        return $return;
    }

    /**
     * @dataProvider apiRouteProvideEmptyResponseDataForRoutesWithoutMigration
     */
    public function testMiddlewareControlExpectingEmptyResponseUsingApiRoute($input, $expecting)
    {
        $control = VersionUpdateMiddleware::middlewareControl($input['currentMethod'], $input['currentRoute']);

        $this->assertEmpty($control);
        $this->assertSame($expecting, $control);
    }

    /**
     * @dataProvider apiRouteProvideResponseDataForRoutesWithMigration
     */
    public function testMiddlewareControlExpectingResponseUsingApiRoute($input, $expecting)
    {
        \MaarchCourrier\Tests\app\versionUpdate\VersionUpdateControllerMock::isMigrating();

        $control = VersionUpdateMiddleware::middlewareControl($input['currentMethod'], $input['currentRoute']);

        $this->assertNotEmpty($control);
        $this->assertNotEmpty($control['response']);
        $this->assertSame($expecting, $control['response']);
    }

    protected function tearDown(): void
    {
        if (file_exists(VersionUpdateController::UPDATE_LOCK_FILE)) {
            unlink(VersionUpdateController::UPDATE_LOCK_FILE);
        }
    }
}
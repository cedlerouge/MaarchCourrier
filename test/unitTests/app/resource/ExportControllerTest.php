<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace MaarchCourrier\Tests\app\resource;

use MaarchCourrier\Tests\CourrierTestCase;
use Resource\controllers\ExportController;
use Resource\controllers\ResController;
use Resource\models\ResModel;
use SrcCore\http\Response;
use User\models\UserModel;

class ExportControllerTest extends CourrierTestCase
{
    private static array $resourcesToRemove = [];

    private function createResource(): int
    {
        $resController = new ResController();

        $fileContent = file_get_contents('test/unitTests/samples/test.txt');
        $encodedFile = base64_encode($fileContent);

        $body = [
            'modelId'          => 2,
            'status'           => 'NEW',
            'encodedFile'      => $encodedFile,
            'format'           => 'txt',
            'confidentiality'  => false,
            'documentDate'     => '2019-01-01 17:18:47',
            'arrivalDate'      => '2019-01-01 17:18:47',
            'processLimitDate' => '2029-01-01',
            'doctype'          => 102,
            'destination'      => 15,
            'initiator'        => 15,
            'subject'          => 'Breaking News : Superman is alive - PHP unit',
            'typist'           => 19,
            'priority'         => 'poiuytre1357nbvc',
            'folders'          => [1, 16],
        ];
        $fullRequest = $this->createRequestWithBody('POST', $body);
        $response     = $resController->create($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        $resId = $responseBody->resId;

        self::$resourcesToRemove[] = $resId;

        return $resId;
    }

    protected function tearDown(): void
    {
        foreach (self::$resourcesToRemove as $resId) {
            // delete link folder
            ResModel::delete([
                'where' => ['res_id = ?'],
                'data'  => [$resId]
            ]);
        }
    }

    public function testGetExportTemplates(): void
    {
        $exportController = new ExportController();

        //  GET
        $request = $this->createRequest('GET');

        $response     = $exportController->getExportTemplates($request, new Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->templates);
        $this->assertNotEmpty($responseBody->templates->pdf);
        $this->assertNotEmpty($responseBody->templates->csv);
    }


    public function testUpdateExport(): void
    {
        $GLOBALS['login'] = 'bbain';
        $userInfo = UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $ExportController = new ExportController();

        //  PUT
        $args = [
            "resources" => $GLOBALS['resources'],
            "delimiter" => ';',
            "format"    => 'pdf',
            "data" => [
                [
                    "value" => "subject",
                    "label" => "Sujet",
                    "isFunction" => false
                ],
                [
                    "value" => "getStatus",
                    "label" => "Status",
                    "isFunction" => true
                ],
                [
                    "value" => "getPriority",
                    "label" => "Priorité",
                    "isFunction" => true
                ],
                [
                    "value" => "getDetailLink",
                    "label" => "Lien page détaillée",
                    "isFunction" => true
                ],
                [
                    "value" => "getInitiatorEntity",
                    "label" => "Entité initiatrice",
                    "isFunction" => true
                ],
                [
                    "value" => "getDestinationEntity",
                    "label" => "Entité traitante",
                    "isFunction" => true
                ],
                [
                    "value" => "getDestinationEntityType",
                    "label" => "Entité traitante",
                    "isFunction" => true
                ],
                [
                    "value" => "getCategory",
                    "label" => "Catégorie",
                    "isFunction" => true
                ],
                [
                    "value" => "getCopies",
                    "label" => "Utilisateurs en copie",
                    "isFunction" => true
                ],
                [
                    "value" => "getSenders",
                    "label" => "Expéditeurs",
                    "isFunction" => true
                ],
                [
                    "value" => "getRecipients",
                    "label" => "Destinataires",
                    "isFunction" => true
                ],
                [
                    "value" => "getTypist",
                    "label" => "Créateurs",
                    "isFunction" => true
                ],
                [
                    "value" => "getAssignee",
                    "label" => "Attributaire",
                    "isFunction" => true
                ],
                [
                    "value" => "getTags",
                    "label" => "Mots-clés",
                    "isFunction" => true
                ],
                [
                    "value" => "getSignatories",
                    "label" => "Signataires",
                    "isFunction" => true
                ],
                [
                    "value" => "getSignatureDates",
                    "label" => "Date de signature",
                    "isFunction" => true
                ],
                [
                    "value" => "getDepartment",
                    "label" => "Département de l'expéditeur",
                    "isFunction" => true
                ],
                [
                    "value" => "getAcknowledgementSendDate",
                    "label" => "Date d'accusé de réception",
                    "isFunction" => true
                ],
                [
                    "value" => "getParentFolder",
                    "label" => "Dossiers parent",
                    "isFunction" => true
                ],
                [
                    "value" => "getFolder",
                    "label" => "Dossiers",
                    "isFunction" => true
                ],
                [
                    "value" => "doc_date",
                    "label" => "Date du courrier",
                    "isFunction" => false
                ],
                [
                    "value" => "custom_4",
                    "label" => "Champ personnalisé",
                    "isFunction" => true
                ],
            ]
        ];

        //PDF
        $fullRequest = $this->createRequestWithBody('PUT', $args);

        $response     = $ExportController->updateExport($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame(null, $responseBody);
        $headers = $response->getHeaders();
        $this->assertSame('application/pdf', $headers['Content-Type'][0]);

        $response     = $ExportController->updateExport($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame(null, $responseBody);
        $headers = $response->getHeaders();
        $this->assertSame('application/pdf', $headers['Content-Type'][0]);

        //  GET
        $request = $this->createRequest('GET');

        $response     = $ExportController->getExportTemplates($request, new Response());
        $responseBody = json_decode((string)$response->getBody());

        $templateData = (array)$responseBody->templates->pdf->data;
        foreach ($templateData as $key => $value) {
            $templateData[$key] = (array)$value;
        }
        $this->assertSame($args['data'], $templateData);

        //CSV
        $args['format'] = 'csv';
        $fullRequest = $this->createRequestWithBody('PUT', $args);

        $response     = $ExportController->updateExport($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(null, $responseBody);

        //  GET
        $request = $this->createRequest('GET');

        $response     = $ExportController->getExportTemplates($request, new Response());
        $responseBody = json_decode((string)$response->getBody());

        $templateData = (array)$responseBody->templates->csv->data;
        foreach ($templateData as $key => $value) {
            $templateData[$key] = (array)$value;
        }
        $this->assertSame($args['data'], $templateData);
        $this->assertSame(';', $responseBody->templates->csv->delimiter);


        //ERRORS
        unset($args['data'][2]['label']);
        $fullRequest = $this->createRequestWithBody('PUT', $args);
        $response = $ExportController->updateExport($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('One data is not set well', $responseBody->errors);

        unset($args['data']);
        $fullRequest = $this->createRequestWithBody('PUT', $args);
        $response = $ExportController->updateExport($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Data data is empty or not an array', $responseBody->errors);

        $args['delimiter'] = 't';
        $fullRequest = $this->createRequestWithBody('PUT', $args);
        $response = $ExportController->updateExport($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Delimiter is empty or not a string between [\',\', \';\', \'TAB\']', $responseBody->errors);

        $args['format'] = 'pd';
        $fullRequest = $this->createRequestWithBody('PUT', $args);
        $response = $ExportController->updateExport($fullRequest, new Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Data format is empty or not a string between [\'pdf\', \'csv\']', $responseBody->errors);

        $GLOBALS['login'] = 'superadmin';
        $userInfo = UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }


    public function testTheDocumentIsOutOfPerimeterDuringExport(): void
    {
        // Arrange
        $this->connectAsUser('cchaplin');

        $resId = $this->createResource();

        $this->connectAsUser('jjane');

        $ExportController = new ExportController();
        //  PUT
        $args = [
            "resources" => [$resId],
            "delimiter" => ';',
            "format"    => 'csv',
            "data" => [
                [
                    "value" => "subject",
                    "label" => "Sujet",
                    "isFunction" => false
                ],
                [
                    "value" => "getStatus",
                    "label" => "Status",
                    "isFunction" => true
                ],
                [
                    "value" => "getPriority",
                    "label" => "Priorité",
                    "isFunction" => true
                ],
                [
                    "value" => "getDetailLink",
                    "label" => "Lien page détaillée",
                    "isFunction" => true
                ],
                [
                    "value" => "getInitiatorEntity",
                    "label" => "Entité initiatrice",
                    "isFunction" => true
                ],
                [
                    "value" => "getDestinationEntity",
                    "label" => "Entité traitante",
                    "isFunction" => true
                ],
                [
                    "value" => "getDestinationEntityType",
                    "label" => "Entité traitante",
                    "isFunction" => true
                ],
                [
                    "value" => "getCategory",
                    "label" => "Catégorie",
                    "isFunction" => true
                ],
                [
                    "value" => "getCopies",
                    "label" => "Utilisateurs en copie",
                    "isFunction" => true
                ],
                [
                    "value" => "getSenders",
                    "label" => "Expéditeurs",
                    "isFunction" => true
                ],
                [
                    "value" => "getRecipients",
                    "label" => "Destinataires",
                    "isFunction" => true
                ],
                [
                    "value" => "getTypist",
                    "label" => "Créateurs",
                    "isFunction" => true
                ],
                [
                    "value" => "getAssignee",
                    "label" => "Attributaire",
                    "isFunction" => true
                ],
                [
                    "value" => "getTags",
                    "label" => "Mots-clés",
                    "isFunction" => true
                ],
                [
                    "value" => "getSignatories",
                    "label" => "Signataires",
                    "isFunction" => true
                ],
                [
                    "value" => "getSignatureDates",
                    "label" => "Date de signature",
                    "isFunction" => true
                ],
                [
                    "value" => "getDepartment",
                    "label" => "Département de l'expéditeur",
                    "isFunction" => true
                ],
                [
                    "value" => "getAcknowledgementSendDate",
                    "label" => "Date d'accusé de réception",
                    "isFunction" => true
                ],
                [
                    "value" => "getParentFolder",
                    "label" => "Dossiers parent",
                    "isFunction" => true
                ],
                [
                    "value" => "getFolder",
                    "label" => "Dossiers",
                    "isFunction" => true
                ],
                [
                    "value" => "doc_date",
                    "label" => "Date du courrier",
                    "isFunction" => false
                ],
                [
                    "value" => "custom_4",
                    "label" => "Champ personnalisé",
                    "isFunction" => true
                ],
            ]
        ];

        $fullRequest = $this->createRequestWithBody('PUT', $args);

        $response     = $ExportController->updateExport($fullRequest, new Response());
        $responseBody = $response->getBody();
        $responseBody->rewind();
        $stream = $responseBody->detach();
        $contents = stream_get_contents($stream);
        $csvContents = array_map('str_getcsv', (array)$contents);

        $csvValues = [];
        foreach ($csvContents as $content) {
            foreach ($content as $value) {
                $cell = utf8_encode($value);
                $csvValues[] = explode(';', $cell);
            }
        }

        $OutsideThePerimeter = false;
        foreach ($csvValues as $row) {
            if (in_array('"Hors périmètre"', $row)) {
                $OutsideThePerimeter = true;
                break;
            }
        }

        $this->assertTrue($OutsideThePerimeter);

    }

}

<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class DocserverControllerTest extends TestCase {
    public function testGet(){
        $docserverController = new \Docserver\controllers\DocserverController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->get($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertNotNull($responseBody);
    }

    public function testGetById(){
        $docserverController = new \Docserver\controllers\DocserverController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'id'    =>  'FASTHD_MAN'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->getById($fullRequest, new \Slim\Http\Response(),$aArgs);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('FASTHD_MAN', $responseBody->docserver_id);

        $aArgs = [
            'id'    =>  'NOT_EXISTS'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->getById($fullRequest, new \Slim\Http\Response(),$aArgs);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Docserver not found', $responseBody->errors);
    }

    public function testCreate(){
        $docserverController = new \Docserver\controllers\DocserverController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'docserver_id'           =>  'NEW_DOCSERVER',
            'docserver_type_id'      =>  'DOC',
            'device_label'           =>  'new docserver',
            'size_limit_number'      =>  50000000000,
            'path_template'          =>  '/var/docserversDEV/dev1804/archive_transfer/',
            'coll_id'                =>  'letterbox_coll',
            'priority_number'        =>  99,
            'adr_priority_number'    =>  99
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->create($fullRequest, new \Slim\Http\Response(),$aArgs);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertSame('NEW_DOCSERVER', $responseBody->docserver);

        $aArgs = [
            'docserver_id'           =>  'WRONG_PATH',
            'docserver_type_id'      =>  'DOC',
            'device_label'           =>  'new docserver',
            'size_limit_number'      =>  50000000000,
            'path_template'          =>  '/wrong/path/',
            'coll_id'                =>  'letterbox_coll',
            'priority_number'        =>  99,
            'adr_priority_number'    =>  99
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->create($fullRequest, new \Slim\Http\Response(),$aArgs);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertSame(_PATH_OF_DOCSERVER_UNAPPROACHABLE, $responseBody->errors);

        $aArgs = [
            'docserver_id'           =>  'BAD_REQUEST',
            'docserver_type_id'      =>  'DOC',
            'device_label'           =>  'new docserver',
            'size_limit_number'      =>  50000000000,
            'path_template'          =>  null,
            'coll_id'                =>  'letterbox_coll',
            'priority_number'        =>  99,
            'adr_priority_number'    =>  99
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->create($fullRequest, new \Slim\Http\Response(),$aArgs);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertSame('Bad Request', $responseBody->errors);

        $aArgs = [
            'docserver_id'           =>  'NEW_DOCSERVER',
            'docserver_type_id'      =>  'DOC',
            'device_label'           =>  'new docserver',
            'size_limit_number'      =>  50000000000,
            'path_template'          =>  '/var/docserversDEV/dev1804/archive_transfer/',
            'coll_id'                =>  'letterbox_coll',
            'priority_number'        =>  99,
            'adr_priority_number'    =>  99
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->create($fullRequest, new \Slim\Http\Response(),$aArgs);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertSame(_ID. ' ' . _ALREADY_EXISTS, $responseBody->errors);
    }

    public function testUpdate(){
        $docserverController = new \Docserver\controllers\DocserverController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'docserver_type_id'      =>  'DOC',
            'device_label'           =>  'updated docserver',
            'size_limit_number'      =>  50000000000,
            'path_template'          =>  '/var/docserversDEV/dev1804/archive_transfer/',
            'priority_number'        =>  99,
            'adr_priority_number'    =>  99
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->update($fullRequest, new \Slim\Http\Response(),['id' => 'NEW_DOCSERVER']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('success', $responseBody->success);

        $aArgs = [
            'docserver_type_id'      =>  'DOC',
            'device_label'           =>  'updated docserver',
            'size_limit_number'      =>  50000000000,
            'path_template'          =>  '/wrong/path/',
            'priority_number'        =>  99,
            'adr_priority_number'    =>  99
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->update($fullRequest, new \Slim\Http\Response(),['id' => 'NEW_DOCSERVER']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame(_PATH_OF_DOCSERVER_UNAPPROACHABLE, $responseBody->errors);

        $aArgs = [
            'docserver_type_id'      =>  'DOC',
            'device_label'           =>  'updated docserver',
            'size_limit_number'      =>  50000000000,
            'path_template'          =>  '/var/docserversDEV/dev1804/archive_transfer/',
            'priority_number'        =>  99,
            'adr_priority_number'    =>  99
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->update($fullRequest, new \Slim\Http\Response(),['id' => 'NOT_EXISTING']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Docserver not found', $responseBody->errors);
    }

    public function testDelete(){
        $docserverController = new \Docserver\controllers\DocserverController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'id'           =>  'NEW_DOCSERVER'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->delete($fullRequest, new \Slim\Http\Response(),$aArgs);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertNotNull($responseBody->docservers);

        $aArgs = [
            'id'           =>  'NOT_EXISTING'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $docserverController->delete($fullRequest, new \Slim\Http\Response(),$aArgs);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Docserver does not exist', $responseBody->errors);
    }

    

    


}
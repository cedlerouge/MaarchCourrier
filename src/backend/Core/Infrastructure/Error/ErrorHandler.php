<?php

declare(strict_types=1);

namespace MaarchCourrier\Core\Infrastructure\Error;

use MaarchCourrier\Core\Domain\Port\EnvironnementInterface;
use MaarchCourrier\Core\Domain\Problem\InternalServerProblem;
use MaarchCourrier\Core\Domain\Problem\Problem;
use MaarchCourrier\Core\Infrastructure\Environnement;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use SrcCore\http\Response;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private ?EnvironnementInterface $environnement = null
    ) {
        if ($this->environnement === null) {
            $this->environnement = new Environnement();
        }
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $response = new Response();

        $problem = $exception;
        $debug = $this->environnement->isDebug();

        if (!$exception instanceof Problem) {
            $problem = new InternalServerProblem($exception, $debug);
        }


        $payload = $problem->jsonSerialize($debug);

        return $response
            ->withStatus($problem->getStatus())
            ->withJson($payload);
    }
}

<?php

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use AppBundle\Helpers\ApiException;
use AppBundle\Helpers\ApiError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use AppBundle\Helpers\ResponseFactory;

/**
 * Formate les exceptions style API
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{

    private $debug;

    private $responseFactory;

    public function __construct($debug, ResponseFactory $responseFactory)
    {
        $this->debug = $debug;
        $this->responseFactory = $responseFactory;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Dans le cas ou le site gère à la fois une api et un site web
        // Préfixer les routes de l'api de "/api" et décommenter cela pour avoir les erreurs
        // gérées de base par symfony pour la partie site web, à la place des erreurs json de l'api
        // if (strpos($event->getRequest()->getPathInfo(), '/api') !== 0) {
        //     return;
        // }

        $e = $event->getException();
        $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        // allow 500 errors to be thrown
        if ($this->debug && $statusCode >= 500) {
            return;
        }

        if ($e instanceof ApiException) {
            $apiError = $e->getApiError();
        } else {
            $apiError = new ApiError($statusCode);

            /*
             * If it's an HttpException message (e.g. for 404, 403),
             * we'll say as a rule that the exception message is safe
             * for the client. Otherwise, it could be some sensitive
             * low-level exception, which should *not* be exposed
             */
            if ($e instanceof HttpExceptionInterface) {
                $apiError->set('detail', $e->getMessage());
            }
        }

        //$data = $apiError->toArray();
        // making type a URL, to a temporarily fake page
        //if ($data['type'] != 'about:blank') {
        //    $data['type'] = 'http://api.cuistot.dev/docs/errors#'.$data['type'];
        //}
        //$response = new JsonResponse(
        //    $data,
        //    $apiError->getStatusCode()
        //);
        //$response->headers->set('Content-Type', 'application/problem+json');

        $response = $this->responseFactory->createResponse($apiError);

        $event->setResponse($response);
    }

}
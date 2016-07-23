<?php

namespace AppBundle\Helpers;

use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseFactory
{

    public function createResponse(ApiError $apiError)
    {
        $data = $apiError->toArray();
        // making type a URL, to a temporarily fake page
        if ($data['type'] != 'about:blank') {
            $data['type'] = 'http://localhost:8000/docs/errors#'.$data['type'];
        }

        $response = new JsonResponse(
            $data,
            $apiError->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }

}

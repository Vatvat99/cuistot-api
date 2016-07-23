<?php

namespace AppBundle\Helpers;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    private $apiError;

    public function __construct(ApiError $apiError, \Exception $previous = null, array $headers = [], $code = 0)
    {
        $this->apiError = $apiError;
        $statusCode = $apiError->getStatusCode();
        $message = $apiError->getTitle();

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    public function getApiError()
    {
        return $this->apiError;
    }

}

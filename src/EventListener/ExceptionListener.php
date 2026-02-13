<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $message = $exception->getMessage();

        // Check if the message is a JSON string (from our Validation logic)
        $data = json_decode($message, true);

        $response = new JsonResponse();

        if ($exception instanceof \InvalidArgumentException) {
            $response->setData([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $data ?: $message
            ]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            // General error handling
            $response->setData([
                'success' => false,
                'message' => $message,
            ]);
            $response->setStatusCode(
                $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500
            );
        }

        $event->setResponse($response);
    }
}

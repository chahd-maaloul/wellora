<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExceptionController extends AbstractController
{
    public function showException(Throwable $exception): Response
    {
        $statusCode = 500;
        $message = 'An error occurred';
        
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        }
        
        // For 403 errors, use our custom template
        if ($statusCode === 403) {
            return $this->render('@Twig/Exception/error403.html.twig', [
                'exception' => $exception,
                'status_code' => $statusCode,
                'status_text' => 'Access Denied',
            ]);
        }
        
        // For 404 errors
        if ($statusCode === 404) {
            return $this->render('@Twig/Exception/error404.html.twig', [
                'exception' => $exception,
                'status_code' => $statusCode,
                'status_text' => 'Page Not Found',
            ]);
        }
        
        // Default error page
        return $this->render('error.html.twig', [
            'exception' => $exception,
            'status_code' => $statusCode,
            'status_text' => $message,
        ]);
    }
}

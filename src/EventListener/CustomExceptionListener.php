<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Twig\Environment;

#[AsEventListener(event: 'kernel.exception', method: 'onKernelException')]
class CustomExceptionListener
{
    public function __construct(
        private readonly Environment $twig,
        private readonly KernelInterface $kernel,
        private readonly ?LoggerInterface $logger = null,
        private readonly bool $customErrorsDev = false,
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        // Em dev, mantenha a página detalhada do Symfony, a menos que explicitamente habilitado
        if ($this->kernel->getEnvironment() !== 'prod' && !$this->customErrorsDev) {
            return;
        }

        $exception = $event->getThrowable();

        $statusCode = 500;
        $template = 'bundles/TwigBundle/Exception/error500.html.twig';

        switch (true) {
            case $exception instanceof NotFoundHttpException:
                $statusCode = 404;
                $template = 'bundles/TwigBundle/Exception/error404.html.twig';
                break;
            case $exception instanceof AccessDeniedHttpException:
                $statusCode = 403;
                $template = 'bundles/TwigBundle/Exception/error403.html.twig';
                break;
            case $exception instanceof ServiceUnavailableHttpException:
                $statusCode = 503;
                $template = 'bundles/TwigBundle/Exception/error503.html.twig';
                break;
        }

        // Log básico (opcional)
        if ($this->logger) {
            $this->logger->error('CustomExceptionListener capturou uma exceção', [
                'status' => $statusCode,
                'message' => $exception->getMessage(),
            ]);
        }

        $html = $this->twig->render($template, [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Erro',
            'exception' => $exception,
        ]);

        $response = new Response($html, $statusCode);
        $event->setResponse($response);
    }
}

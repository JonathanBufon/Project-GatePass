<?php

namespace App\MessageHandler;

use App\Entity\Pedido;
use App\Message\PedidoPagoMessage;
use App\Repository\PedidoRepository;
use App\Service\EmailService;
use App\Service\IngressoService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PedidoPagoMessageHandler
{
    public function __construct(
        private readonly PedidoRepository $pedidoRepository,
        private readonly IngressoService $ingressoService,
        private readonly EmailService $emailService,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PedidoPagoMessage $message): void
    {
        $pedidoId = $message->getPedidoId();
        /** @var Pedido|null $pedido */
        $pedido = $this->pedidoRepository->find($pedidoId);
        if (!$pedido) {
            $this->logger->warning('Pedido não encontrado para processamento assíncrono.', ['pedidoId' => $pedidoId]);
            return;
        }

        $paths = [];
        foreach ($pedido->getIngressos() as $ingresso) {
            try {
                $paths[] = $this->ingressoService->generateAndSave($ingresso);
            } catch (\Throwable $e) {
                $this->logger->error('Falha ao gerar PDF do ingresso', [
                    'pedidoId' => $pedidoId,
                    'ingressoId' => $ingresso->getId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            $this->emailService->enviarConfirmacaoComIngressos($pedido, $paths);
        } catch (\Throwable $e) {
            $this->logger->error('Falha ao enviar e-mail com ingressos', [
                'pedidoId' => $pedidoId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

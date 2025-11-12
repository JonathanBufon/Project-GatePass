<?php

namespace App\MessageHandler;

use App\Entity\Pedido;
use App\Message\PurchaseConfirmedMessage;
use App\Port\Email\TransactionalEmailInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PurchaseConfirmedHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TransactionalEmailInterface $email
    ) {}

    public function __invoke(PurchaseConfirmedMessage $message): void
    {
        $pedido = $this->em->getRepository(Pedido::class)->find($message->pedidoId);
        if (!$pedido) { return; }
        $this->email->sendPurchaseConfirmation($pedido);
    }
}

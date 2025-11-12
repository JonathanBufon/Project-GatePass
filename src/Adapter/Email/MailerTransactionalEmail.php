<?php

namespace App\Adapter\Email;

use App\Entity\Pedido;
use App\Port\Email\TransactionalEmailInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerTransactionalEmail implements TransactionalEmailInterface
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function sendPurchaseConfirmation(Pedido $pedido): void
    {
        $email = (new Email())
            ->from('no-reply@gatepass.local')
            ->to($pedido->getCliente()?->getUsuario()?->getEmail() ?? 'test@example.com')
            ->subject('Compra confirmada #' . $pedido->getId())
            ->text('Seu pedido foi confirmado. Obrigado!');

        $this->mailer->send($email);
    }
}

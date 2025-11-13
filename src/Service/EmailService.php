<?php

namespace App\Service;

use App\Entity\Pedido;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function enviarConfirmacaoComIngressos(Pedido $pedido, array $pathsDosPdfs): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('nao-responder@gatepass.local', 'GatePass'))
            ->to(new Address($pedido->getCliente()->getEmail(), $pedido->getCliente()->getNomeCompleto()))
            ->subject('Sua compra foi confirmada — Ingressos anexados')
            ->htmlTemplate('email/confirmacao_compra.html.twig')
            ->context([
                'pedido' => $pedido,
            ]);

        foreach ($pathsDosPdfs as $path) {
            if ($path && is_file($path)) {
                $email->attachFromPath($path);
            }
        }

        $this->mailer->send($email);
    }
}

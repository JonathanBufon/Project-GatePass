<?php

namespace App\Command;

use App\Repository\PedidoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'gatepass:reservas:liberar-expiradas', description: 'Libera reservas expiradas e cancela ingressos pendentes')] 
class ReleaseExpiredReservationsCommand extends Command
{
    public function __construct(
        private readonly PedidoRepository $pedidoRepository,
        private readonly EntityManagerInterface $em
    ) { parent::__construct(); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $expirados = $this->pedidoRepository->findExpiredPending();
        $totalPedidos = 0; $totalIngressos = 0;
        foreach ($expirados as $pedido) {
            foreach ($pedido->getIngressos() as $ingresso) {
                if ($ingresso->getStatus() === 'RESERVADO') {
                    $ingresso->setStatus('CANCELADO');
                    $totalIngressos++;
                }
            }
            $pedido->setStatus('CANCELADO');
            $totalPedidos++;
        }
        if ($totalPedidos > 0) {
            $this->em->flush();
        }
        $output->writeln(sprintf('Pedidos cancelados: %d | Ingressos liberados: %d', $totalPedidos, $totalIngressos));
        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Entity\Cliente;
use App\Entity\Evento;
use App\Entity\Lote;
use App\Entity\Usuario;
use App\Entity\Vendedor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:seed:demo', description: 'Popula o banco com dados de demonstração do GatePass')]
class SeedDemoCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Se já existir algum evento, não semeia novamente
        if ($this->em->getRepository(Evento::class)->count([]) > 0) {
            $output->writeln('<info>Dados já existentes. Nada a fazer.</info>');
            return Command::SUCCESS;
        }

        // Cria vendedor + usuário
        $usuarioVend = new Usuario();
        $usuarioVend->setEmail('vendedor@gatepass.local');
        $usuarioVend->setRoles(['ROLE_VENDEDOR']);
        $usuarioVend->setPassword($this->passwordHasher->hashPassword($usuarioVend, 'secret123'));

        $vendedor = new Vendedor();
        $vendedor->setNomeFantasia('Produtora Demo');
        $vendedor->setCnpj('00.000.000/0001-00');
        $usuarioVend->setVendedor($vendedor);
        $this->em->persist($usuarioVend);
        $this->em->persist($vendedor);

        // Cria cliente + usuário
        $usuarioCli = new Usuario();
        $usuarioCli->setEmail('cliente@gatepass.local');
        $usuarioCli->setRoles(['ROLE_USER']);
        $usuarioCli->setPassword($this->passwordHasher->hashPassword($usuarioCli, 'secret123'));

        $cliente = new Cliente();
        $cliente->setNomeCompleto('Cliente Demo');
        $cliente->setCpf('000.000.000-00');
        $usuarioCli->setCliente($cliente);
        $this->em->persist($usuarioCli);
        $this->em->persist($cliente);

        // Evento publicado com lote
        $evento = (new Evento())
            ->setNome('Show Demo')
            ->setDescricao('Um evento de demonstração do GatePass')
            ->setLocal('Auditório Central')
            ->setDataHoraInicio(new \DateTime('+7 days'))
            ->setDataHoraFim(new \DateTime('+7 days +2 hours'))
            ->setStatus('PUBLICADO')
            ->setCapacidadeTotal(500)
            ->setUrlBanner('https://via.placeholder.com/800x400?text=GatePass');
        $evento->setVendedor($vendedor);
        $this->em->persist($evento);

        $lote = (new Lote())
            ->setNome('Pista')
            ->setPreco('100.00')
            ->setQuantidadeTotal(200)
            ->setDataInicioVendas(new \DateTime('-1 day'))
            ->setDataFimVendas(new \DateTime('+6 days'))
            ->setEvento($evento);
        $this->em->persist($lote);

        $this->em->flush();
        $output->writeln('<info>Dados de demonstração criados com sucesso.</info>');
        $output->writeln('<comment>Login cliente:</comment> cliente@gatepass.local / secret123');
        $output->writeln('<comment>Login vendedor:</comment> vendedor@gatepass.local / secret123');
        return Command::SUCCESS;
    }
}

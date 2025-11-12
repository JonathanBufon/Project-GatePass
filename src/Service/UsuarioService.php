<?php

namespace App\Service;

use App\Entity\Cliente;
use App\Entity\Usuario;
use App\Entity\Vendedor;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Camada de Serviço (Lógica de Negócio) para operações de Usuário.
 * Respeita o SRP: A lógica de registro está encapsulada aqui.
 * @author Jonathan Bufon
 */
class UsuarioService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UsuarioRepository $usuarioRepository
    ) {
    }

    /**
     * Orquestra a criação de um novo Cliente e seu Usuário de autenticação.
     */
    public function registrarCliente(
        string $email,
        string $plainPassword,
        string $nomeCompleto,
        string $cpf
    ): void {
        // 1. Reutiliza o método DRY
        $this->validarEmailDuplicado($email);

        $usuario = new Usuario();
        $usuario->setEmail($email);
        $usuario->setRoles(['ROLE_USER']); // Cliente é apenas ROLE_USER

        $hashedPassword = $this->passwordHasher->hashPassword($usuario, $plainPassword);
        $usuario->setPassword($hashedPassword);

        $cliente = new Cliente();
        $cliente->setNomeCompleto($nomeCompleto);
        $cliente->setCpf($cpf);
        $cliente->setUsuario($usuario);

        $this->em->persist($usuario);
        $this->em->persist($cliente);
        $this->em->flush();
    }

    /**
     * Orquestra a criação de um Vendedor
     * e seu Usuário de autenticação.
     * @author Jonathan Bufon
     */
    public function registrarVendedor(
        string $email,
        string $plainPassword,
        string $nomeFantasia,
        string $documento,
        string $tipoDocumento
    ): void {
        $this->validarEmailDuplicado($email);

        $usuario = new Usuario();
        $usuario->setEmail($email);
        $usuario->setRoles(['ROLE_VENDEDOR']);

        $hashedPassword = $this->passwordHasher->hashPassword($usuario, $plainPassword);
        $usuario->setPassword($hashedPassword);

        $vendedor = new Vendedor();
        $vendedor->setNomeFantasia($nomeFantasia);
        $vendedor->setUsuario($usuario);
        $vendedor->setDocumento($this->somenteDigitos($documento));
        $vendedor->setTipoDocumento(match (strtolower($tipoDocumento)) {
            'cpf' => \App\Enum\TipoDocumento::CPF,
            'cnpj' => \App\Enum\TipoDocumento::CNPJ,
            default => \App\Enum\TipoDocumento::CNPJ,
        });

        $this->em->persist($usuario);
        $this->em->persist($vendedor);
        $this->em->flush();
    }

    /**
     * SRP: Método privado auxiliar para validar o e-mail (DRY).
     * @author Jonathan Bufon
     */
    private function validarEmailDuplicado(string $email): void
    {
        $existe = $this->usuarioRepository->findOneBy(['email' => $email]);
        if ($existe) {
            throw new CustomUserMessageAuthenticationException('Este e-mail já está em uso.');
        }
    }

    private function somenteDigitos(string $valor): string
    {
        return preg_replace('/\D+/', '', $valor) ?? '';
    }
}
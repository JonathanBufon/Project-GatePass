<?php

namespace App\Security\Voter;

use App\Entity\Pedido;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PedidoVoter extends Voter
{
    public const VIEW = 'PEDIDO_VIEW';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof Pedido;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Usuario) { return false; }
        return $subject->getCliente() && $user->getCliente() && $subject->getCliente() === $user->getCliente();
    }
}

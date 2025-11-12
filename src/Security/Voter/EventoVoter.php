<?php

namespace App\Security\Voter;

use App\Entity\Evento;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventoVoter extends Voter
{
    public const EDIT = 'EVENTO_EDIT';
    public const PUBLICAR = 'EVENTO_PUBLICAR';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::PUBLICAR], true) && $subject instanceof Evento;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Usuario) { return false; }
        if (!$user->getVendedor()) { return false; }
        return $subject->getVendedor() === $user->getVendedor();
    }
}

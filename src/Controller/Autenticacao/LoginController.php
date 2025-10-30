<?php

namespace App\Controller\Autenticacao;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @author Jonathan Bufon
 */
#[Route('/auth')]
class LoginController extends AbstractController
{
    /**
     * Este método APENAS renderiza o formulário de login.
     * O SecurityBundle intercepta o POST nesta mesma rota.
     */
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('autenticacao/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * Este método NUNCA será executado.
     * O SecurityBundle intercepta a rota 'app_logout' antes.
     * Ele só existe para que a rota possa ser definida.
     */
    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('Este método não deve ser alcançado.');
    }
}
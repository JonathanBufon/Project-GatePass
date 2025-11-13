<?php

namespace App\Controller\Autenticacao;

use App\Form\RecuperarSenhaFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth')]
class RecuperarSenhaController extends AbstractController
{
    #[Route('/recuperar-senha', name: 'app_recuperar_senha', methods: ['GET','POST'])]
    public function recuperar(Request $request): Response
    {
        $form = $this->createForm(RecuperarSenhaFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Stub: aqui geraria token e enviaria e-mail via Mailer/Messenger
            $this->addFlash('success', 'Se o e-mail existir em nossa base, você receberá um link para redefinir sua senha.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('autenticacao/recuperar_senha.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

<?php

namespace App\Controller\Autenticacao;

use App\Form\RegistroFormType;
use App\Service\UsuarioService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Controller "Magro" (Thin Controller).
 * Responsabilidade: Gerenciar a Rota, o Formulário (View)
 * e delegar a lógica de negócio para o UsuarioService.
 * @author Jonathan Bufon
 */
#[Route('/auth')]
class RegistroController extends AbstractController
{
    public function __construct(private readonly UsuarioService $usuarioService)
    {
    }

    #[Route('/registro', name: 'app_registro', methods: ['GET', 'POST'])]
    public function registro(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(RegistroFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $this->usuarioService->registrarCliente(
                    $data->email,
                    $form->get('plainPassword')->getData(),
                    $data->nomeCompleto,
                    $data->cpf
                );

                $this->addFlash('success', 'Registro realizado com sucesso! Faça seu login.');

                // Redireciona para a nova rota de login
                return $this->redirectToRoute('app_login');

            } catch (CustomUserMessageAuthenticationException $e) {
                $form->get('email')->addError(new FormError($e->getMessage()));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Ocorreu um erro inesperado. Tente novamente.');
            }
        }

        return $this->render('registro/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
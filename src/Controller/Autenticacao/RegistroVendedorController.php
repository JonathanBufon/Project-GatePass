<?php

namespace App\Controller\Autenticacao;

use App\Form\RegistroVendedorFormType;
use App\Service\UsuarioService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

#[Route('/auth')]
class RegistroVendedorController extends AbstractController
{
    public function __construct(private readonly UsuarioService $usuarioService)
    {
    }

    #[Route('/registro-vendedor', name: 'app_registro_vendedor', methods: ['GET', 'POST'])]
    public function registro(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(RegistroVendedorFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $this.usuarioService->registrarVendedor(
                    $data['email'],
                    $form->get('plainPassword')->getData(),
                    $data['nomeFantasia'],
                    $data['cnpj']
                );

                $this->addFlash('success', 'Registro de vendedor realizado com sucesso! Faça seu login.');
                return $this->redirectToRoute('app_login');

            } catch (CustomUserMessageAuthenticationException $e) {
                // Trata e-mail duplicado
                $form->get('email')->addError(new FormError($e->getMessage()));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Ocorreu um erro inesperado. Tente novamente.');
            }
        }

        return $this->render('autenticacao/registro_vendedor.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
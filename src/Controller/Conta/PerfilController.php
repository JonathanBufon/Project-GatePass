<?php

namespace App\Controller\Conta;

use App\Entity\Usuario;
use App\Form\PerfilFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/conta')]
#[IsGranted('ROLE_USER')]
class PerfilController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/perfil', name: 'app_conta_perfil', methods: ['GET','POST'])]
    public function perfil(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $cliente = $usuario->getCliente();

        $dados = [
            'email' => $usuario->getEmail(),
            'nomeCompleto' => $cliente?->getNomeCompleto(),
            'cpf' => $cliente?->getCpf(),
        ];

        $form = $this->createForm(PerfilFormType::class, $dados);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $usuario->setEmail($data['email']);
            if ($cliente) {
                $cliente->setNomeCompleto($data['nomeCompleto'] ?? $cliente->getNomeCompleto());
                $cliente->setCpf($data['cpf'] ?? $cliente->getCpf());
            }
            $this->em->flush();
            $this->addFlash('success', 'Perfil atualizado com sucesso.');
            return $this->redirectToRoute('app_conta_perfil');
        }

        return $this->render('conta/perfil.html.twig', [
            'perfilForm' => $form->createView(),
        ]);
    }
}

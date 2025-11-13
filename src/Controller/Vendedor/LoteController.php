<?php

namespace App\Controller\Vendedor;

use App\Entity\Evento;
use App\Entity\Lote;
use App\Form\LoteFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/vendedor/lote')]
#[IsGranted('ROLE_VENDEDOR')]
class LoteController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * Passo 2 (Pista): Criar um novo Lote para um Evento do Vendedor.
     */
    #[Route('/novo/{evento}', name: 'app_vendedor_lote_novo', methods: ['GET','POST'])]
    public function novo(Request $request, Evento $evento): Response
    {
        // Regra de segurança: somente o dono do evento pode cadastrar lote
        $this->denyAccessUnlessGranted('EVENTO_EDIT', $evento);

        $lote = new Lote();
        $form = $this->createForm(LoteFormType::class, $lote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lote->setEvento($evento);
            $this->em->persist($lote);
            $this->em->flush();

            $this->addFlash('success', 'Lote criado com sucesso.');
            return $this->redirectToRoute('app_vendedor_evento_editar', ['id' => $evento->getId()]);
        }

        return $this->render('vendedor/lote/novo.html.twig', [
            'form' => $form->createView(),
            'evento' => $evento,
        ]);
    }

    /**
     * Lista e gerencia os lotes de um evento do vendedor.
     */
    #[Route('/evento/{evento}', name: 'app_vendedor_lote_index', methods: ['GET'])]
    public function index(Evento $evento): Response
    {
        $this->denyAccessUnlessGranted('EVENTO_EDIT', $evento);

        return $this->render('vendedor/lote/index.html.twig', [
            'evento' => $evento,
            'lotes' => $evento->getLotes(),
        ]);
    }

    /**
     * Exclui um lote do evento, protegido por CSRF e segurança de autoria.
     */
    #[Route('/{id}/excluir', name: 'app_vendedor_lote_excluir', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function excluir(Request $request, Lote $lote): Response
    {
        $evento = $lote->getEvento();
        $this->denyAccessUnlessGranted('EVENTO_EDIT', $evento);

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('del-lote-' . $lote->getId(), $token)) {
            $this->addFlash('error', 'Token de segurança inválido.');
            return $this->redirectToRoute('app_vendedor_lote_index', ['evento' => $evento->getId()]);
        }

        $this->em->remove($lote);
        $this->em->flush();

        $this->addFlash('success', 'Lote excluído com sucesso.');
        return $this->redirectToRoute('app_vendedor_lote_index', ['evento' => $evento->getId()]);
    }
}

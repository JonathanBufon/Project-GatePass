<?php

namespace App\Controller;

use App\Entity\Ingresso;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ingresso')]
#[IsGranted('ROLE_USER')]
class IngressoController extends AbstractController
{
    #[Route('/download/{id}', name: 'app_ingresso_download', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function download(Ingresso $ingresso): BinaryFileResponse
    {
        // Segurança: somente o dono do pedido pode baixar
        $this->denyAccessUnlessGranted('PEDIDO_VIEW', $ingresso->getPedido());

        $path = $ingresso->getFilePath();
        if (!$path || !is_file($path)) {
            throw $this->createNotFoundException('Arquivo do ingresso ainda não está disponível. Tente novamente em instantes.');
        }
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('ingresso-%s.pdf', $ingresso->getIdentificadorUnico() ?: $ingresso->getCodigoUnico())
        );
        return $response;
    }
}

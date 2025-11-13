<?php

namespace App\Service;

use App\Entity\Ingresso;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class IngressoService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Gera QR Code, renderiza HTML e salva como PDF.
     * Retorna o caminho absoluto do arquivo salvo.
     */
    public function generateAndSave(Ingresso $ingresso): string
    {
        if (!$ingresso->getIdentificadorUnico()) {
            $ingresso->setIdentificadorUnico(self::uuidV4());
        }

        $qrData = $ingresso->getIdentificadorUnico();
        $qrImageDataUri = $this->generateQrCodeDataUri($qrData);

        $html = $this->twig->render('ingresso/pdf_template.html.twig', [
            'ingresso' => $ingresso,
            'qrImageDataUri' => $qrImageDataUri,
        ]);

        $storageDir = sys_get_temp_dir() . '/gatepass/ingressos';
        $fs = new Filesystem();
        if (!$fs->exists($storageDir)) {
            $fs->mkdir($storageDir, 0775);
        }
        $path = $storageDir . '/' . $ingresso->getIdentificadorUnico() . '.pdf';

        // Tenta usar mPDF se disponível; como fallback, salva HTML como .pdf (ainda utilizável para inspeção).
        if (class_exists(\Mpdf\Mpdf::class)) {
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output($path, \Mpdf\Output\Destination::FILE);
        } else {
            // Fallback: salva HTML com extensão .pdf
            file_put_contents($path, $html);
        }

        $ingresso->setFilePath($path);
        $this->em->persist($ingresso);
        $this->em->flush();

        return $path;
    }

    private function generateQrCodeDataUri(string $data): string
    {
        // Se Endroid\QrCode estiver disponível, usa-o; caso contrário, usa SVG inline simples.
        if (class_exists(\Endroid\QrCode\QrCode::class)) {
            $qr = \Endroid\QrCode\QrCode::create($data)
                ->setSize(220)
                ->setMargin(10);
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qr);
            return 'data:image/png;base64,' . base64_encode($result->getString());
        }
        // Fallback SVG mínimo (não é um QR real, mas evita quebrar a tela de PDF em dev sem libs)
        $safe = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="220" height="220"><rect width="100%" height="100%" fill="#eee"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="monospace" font-size="12">' . $safe . '</text></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private static function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

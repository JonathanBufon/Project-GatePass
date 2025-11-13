# Fluxo Assíncrono de Geração e Entrega de Ingressos (PDF)

## Visão Geral
Após um pagamento aprovado, o cliente é redirecionado imediatamente para a tela de sucesso. Em background, um worker do Messenger processa a geração dos PDFs de ingressos e envia o e-mail de confirmação com os anexos.

## Diagrama de Sequência (texto)

PagamentoService -> Gateway: charge(pedido, dto)
Gateway --> PagamentoService: sucesso:boolean
PagamentoService -> Pedido: setStatus('PAGO')
PagamentoService -> MessageBus: dispatch(PedidoPagoMessage{pedidoId})
Controller -> Cliente: redirect(app_compra_confirmada)

Worker (messenger:consume):
  PedidoPagoMessageHandler -> PedidoRepository: find(pedidoId)
  loop(ingresso in pedido.ingressos):
    PedidoPagoMessageHandler -> IngressoService: generateAndSave(ingresso)
    IngressoService -> Twig: render(ingresso/pdf_template.html.twig)
    IngressoService -> (mPDF|fallback): gerar PDF
    IngressoService -> Storage: salvar arquivo
    IngressoService -> Ingresso(Entity): setFilePath(path)
  PedidoPagoMessageHandler -> EmailService: enviarConfirmacaoComIngressos(pedido, paths)
  EmailService -> Mailer: send(email com anexos)

## Alterações de Schema
- Entity `Ingresso`:
  - `identificadorUnico` (string 36, unique, nullable): base para QR Code.
  - `filePath` (string 255, nullable): caminho do PDF gerado.

## Componentes
- Mensagem: `App\Message\PedidoPagoMessage` (pedidoId)
- Handler: `App\MessageHandler\PedidoPagoMessageHandler`
- Serviço: `App\Service\IngressoService`
  - `generateAndSave(Ingresso $ingresso): string`
  - Gera QR (endroid/qr-code se disponível; fallback SVG), renderiza Twig e salva PDF (mPDF se disponível, fallback salva HTML como .pdf).
- Serviço: `App\Service\EmailService`
  - `enviarConfirmacaoComIngressos(Pedido $pedido, array $paths)`
  - Usa `TemplatedEmail` e anexa PDFs.

## Templates
- `templates/ingresso/pdf_template.html.twig`: layout simples de ingresso com QR.
- `templates/email/confirmacao_compra.html.twig`: e-mail de confirmação.

## Storage
- PDFs são salvos em diretório temporário por padrão: `{sys_tmp}/gatepass/ingressos/`.
- Recomenda-se configurar diretório persistente (ex.: `/var/storage/ingressos/`) via parâmetro/ENV e injetar no `IngressoService`.

## Configuração Requerida
- Messenger: transporte assíncrono configurado (ex.: doctrine, redis, sqs). Executar worker: `bin/console messenger:consume -vv`.
- Mailer: configurar DSN (`MAILER_DSN`).
- Bibliotecas (opcional, melhora a qualidade):
  - PDF: `mpdf/mpdf`
  - QR: `endroid/qr-code`

## Observações
- O fluxo não bloqueia o cliente; falhas são logadas.
- A ação de download lê `Ingresso->filePath` e retorna `BinaryFileResponse` (rota `app_ingresso_download`).

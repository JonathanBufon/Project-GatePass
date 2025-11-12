# GatePass — Relatório de Execução do Plano de Melhorias (2025-11-12)

Este relatório documenta as implementações realizadas conforme o plano A–F do arquivo `sugestoes.md`, incluindo classes criadas/modificadas, decisões técnicas e status.

## A. Ações de Arquitetura

- **DTOs/ViewModels (Concluído)**
  - Criado
    - `src/Dto/CheckoutDto.php`
    - `src/Dto/RegistroClienteDto.php`
    - `src/Dto/RegistroVendedorDto.php`
  - Ajustado para usar DTO como `data_class`
    - `src/Form/CheckoutFormType.php` → agora `data_class = CheckoutDto`
    - `src/Form/RegistroFormType.php` → agora `data_class = RegistroClienteDto`
    - `src/Form/RegistroVendedorFormType.php` → agora `data_class = RegistroVendedorDto`
  - Controllers adaptados
    - `Autenticacao/RegistroController.php` passou a ler propriedades do DTO (e não arrays)
  - Decisão: reduzir acoplamento Controller↔Entity, preparar terreno para responses/serialização futura.

- **Portas e Adaptadores (Hexagonal) (Concluído)**
  - Portas (Interfaces)
    - `src/Port/Payment/PaymentGatewayInterface.php`
    - `src/Port/Email/TransactionalEmailInterface.php` (mantida para compatibilidade em handler)
  - Adaptadores (Implementações)
    - `src/Adapter/Payment/SandboxPaymentGateway.php`
    - `src/Adapter/Email/MailerTransactionalEmail.php`
  - Injeção/Binding (services.yaml)
    - `App\Port\Payment\PaymentGatewayInterface: '@App\Adapter\Payment\SandboxPaymentGateway'`
    - `App\Port\Email\TransactionalEmailInterface: '@App\Adapter\Email\MailerTransactionalEmail'`
  - Serviços
    - `src/Service/PagamentoService.php` refatorado para depender de `PaymentGatewayInterface` e mensageria para email.

- **Políticas de Domínio (Concluído)**
  - Criado
    - `src/Domain/Policy/EstoquePolicy.php`
    - `src/Domain/Policy/JanelaVendaPolicy.php`
  - Integrado
    - `src/Service/PedidoService.php` agora injeta e usa as políticas ao adicionar ingressos.

## B. Performance e Escalabilidade

- **Otimização de Consultas (Parcial)**
  - Status: Pendente ajustes finos (partial select/hydration) nos repositórios.
  - Justificativa: requer revisão de cada query; proposta manter para próxima iteração.

- **Indexação (Concluído)**
  - Índices adicionados via atributos ORM
    - `Evento` → `#[ORM\Table(indexes: [new ORM\Index(columns: ['status'])])]`
    - `Lote` → `#[ORM\Table(indexes: [new ORM\Index(columns: ['evento_id'])])]`
  - Observação: Necessário rodar migrações para refletir no banco.

- **Cache HTTP (Concluído)**
  - `src/Controller/EventoController.php` define `setPublic()` e `setMaxAge(120)` em `index()` e `detalhe()`.

- **Filas/Assíncrono (Concluído)**
  - Mensageria (Messenger)
    - Config: `config/packages/messenger.yaml` roteia `App\Message\PurchaseConfirmedMessage` para `async`.
    - Mensagem/Handler
      - `src/Message/PurchaseConfirmedMessage.php`
      - `src/MessageHandler/PurchaseConfirmedHandler.php`
  - `PagamentoService` agora despacha `PurchaseConfirmedMessage` ao aprovar pagamento.

## C. Segurança e Confiabilidade

- **Rate-limiting e Captcha (Não iniciado)**
  - Status: Pendente
  - Justificativa: Requer dependências e configuração (RateLimiter, Captcha service) e alinhamento de UX.

- **Autorização (Voters) (Concluído)**
  - Voters criados
    - `src/Security/Voter/PedidoVoter.php` (PEDIDO_VIEW)
    - `src/Security/Voter/EventoVoter.php` (EVENTO_EDIT, EVENTO_PUBLICAR)
  - Controllers ajustados
    - `PedidoController::detalhe()` usa `denyAccessUnlessGranted('PEDIDO_VIEW', $pedido)`
    - `CheckoutController::confirmacao()` idem
    - `Vendedor/EventoController::editar()` e `publicar()` usam voters

- **Logs e Auditoria (Parcial)**
  - Logger já utilizado em `PagamentoService` (pontos de entrada/resultado). 
  - Pendência: padronizar eventos de domínio e persistência de auditoria.

## D. Qualidade e DX

- **Testes (Não iniciado)**
  - Pendente criação de testes unitários (Policies, Ports) e funcionais (checkout/listagem). 

- **CI/CD (Não iniciado)**
  - Pendente pipeline (GitHub Actions/GitLab CI) com PHP-CS-Fixer, PHPStan e PHPUnit.

## E. Frontend e UX

- **Fidelidade ao Bootstrap (Parcial)**
  - Ajustes gerais presentes; pendente revisão fina para equivalência 1:1 com exemplos oficiais.

- **Componentização Twig (Concluído)**
  - Criado partial reutilizável
    - `templates/_partials/card_evento.html.twig`
  - Aplicado em
    - `templates/evento/index.html.twig`

## F. Novos Recursos (Próximos Passos)

- **Estoque (Reserva com expiração) (Não iniciado)**
  - Status: Pendente
  - Justificativa: exige mecanismo de expiração (cron/worker) e marcação temporal nos ingressos/reservas.

- **Abstração de Pagamento (Concluído)**
  - Porta/Adapter implementados e `PagamentoService` refatorado. Modo sandbox funcional.

- **Dados de Demo (Não iniciado)**
  - Pendentes fixtures (Doctrine Fixtures) e comandos `bin/console` de carga.

- **Área do Cliente — Meus Pedidos com filtros/paginação (Não iniciado)**
  - Página básica existe (`pedido/meus_pedidos.html.twig`), mas sem filtros/paginação.

---

## Resumo de Arquivos Criados/Alterados

- Criados
  - DTOs: `src/Dto/CheckoutDto.php`, `src/Dto/RegistroClienteDto.php`, `src/Dto/RegistroVendedorDto.php`
  - Ports/Adapters: `src/Port/Payment/PaymentGatewayInterface.php`, `src/Adapter/Payment/SandboxPaymentGateway.php`, `src/Port/Email/TransactionalEmailInterface.php`, `src/Adapter/Email/MailerTransactionalEmail.php`
  - Policies: `src/Domain/Policy/EstoquePolicy.php`, `src/Domain/Policy/JanelaVendaPolicy.php`
  - Mensageria: `src/Message/PurchaseConfirmedMessage.php`, `src/MessageHandler/PurchaseConfirmedHandler.php`
  - Twig partial: `templates/_partials/card_evento.html.twig`
- Alterados
  - FormTypes: `CheckoutFormType.php`, `RegistroFormType.php`, `RegistroVendedorFormType.php`
  - Services: `PagamentoService.php`, `PedidoService.php`
  - Controllers: `Autenticacao/RegistroController.php`, `EventoController.php`, `Vendedor/EventoController.php`, `PedidoController.php`, `CheckoutController.php`
  - Entities: `Evento.php`, `Lote.php` (índices ORM)
  - Config: `config/services.yaml`, `config/packages/messenger.yaml`
  - Templates: `templates/evento/index.html.twig`

## Considerações Finais

- A arquitetura foi fortalecida com DTOs, Voters, Ports/Adapters e Policies, mantendo o fluxo Request → Controller → Service → Repository → Entity → Twig.
- Próxima etapa recomendada: completar segurança (rate limiter/captcha), otimizações de consulta, fixtures e testes/CI.

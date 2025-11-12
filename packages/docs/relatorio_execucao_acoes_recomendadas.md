# GatePass — Relatório de Execução das Próximas Ações Recomendadas (2025-11-12)

Este relatório descreve as ações implementadas a partir da seção "Próximas ações recomendadas" do projeto, com status, decisões técnicas e instruções rápidas de uso.

## 1) Migrações/Índices de Banco (Performance)
- **Status**: Concluído (código) / Pendente (execução de migração).
- **O que foi feito**:
  - `Evento`: índice em `status` via atributo ORM em `src/Entity/Evento.php`.
  - `Lote`: índice em `evento_id` via atributo ORM em `src/Entity/Lote.php`.
- **Decisão**: índices para acelerar filtros em listagem/relacionamentos.
- **Próximo passo**: gerar e rodar migrações.
  - Gerar: bin/console make:migration
  - Executar: bin/console doctrine:migrations:migrate -n

## 2) Otimização de Consultas (Listagem/Detalhe de Evento)
- **Status**: Concluído.
- **O que foi feito**:
  - `src/Repository/EventoRepository.php`:
    - `findPublishedList()` com `partial select` para listagem (Album).
    - `findOnePublishedById(int $id)` para detalhe (Product).
  - `src/Service/EventoService.php` passou a usar os novos métodos.
- **Decisão**: reduzir hidratação desnecessária, melhorar latência em listagens.

## 3) Cache HTTP (Listagem/Detalhe)
- **Status**: Concluído.
- **O que foi feito**: `EventoController::{index,detalhe}` agora definem `setPublic()` e `setMaxAge(120)`.
- **Decisão**: permitir cache em proxy/browser de curto prazo, mantendo dados frescos.

## 4) Segurança — Throttling de Login
- **Status**: Concluído (básico).
- **O que foi feito**: `config/packages/security.yaml` — `login_throttling` com `max_attempts: 5` e `interval: 15 minutes`.
- **Decisão**: mitigar brute force sem alterar UX.
- **Pendente**: Captcha no Login/Registro (integração a definir).

## 5) Mensageria — Confirmação de Compra Assíncrona
- **Status**: Concluído.
- **O que foi feito**:
  - Mensagens/Handler:
    - `src/Message/PurchaseConfirmedMessage.php`
    - `src/MessageHandler/PurchaseConfirmedHandler.php`
  - Roteamento: `config/packages/messenger.yaml` → `App\Message\PurchaseConfirmedMessage: async`.
  - Serviço: `PagamentoService` agora despacha `PurchaseConfirmedMessage` após pagamento aprovado.
- **Requisito**: Definir `MESSENGER_TRANSPORT_DSN` no `.env` e rodar worker.
  - Ex.: symfony console messenger:consume async -vv

## 6) Ports & Adapters — Pagamento/Email
- **Status**: Concluído.
- **O que foi feito**:
  - Portas: `PaymentGatewayInterface`, `TransactionalEmailInterface`.
  - Adaptadores: `SandboxPaymentGateway`, `MailerTransactionalEmail`.
  - Bindings: `config/services.yaml` (interfaces → implementações concretas).
  - `PagamentoService` refatorado para depender das portas e mensageria.
- **Decisão**: desacoplamento para permitir troca de gateways e mock em testes.

## 7) Políticas de Domínio — Estoque/Janela de Venda
- **Status**: Concluído.
- **O que foi feito**:
  - `src/Domain/Policy/EstoquePolicy.php`.
  - `src/Domain/Policy/JanelaVendaPolicy.php`.
  - Integrado em `PedidoService::adicionarIngressos()`.
- **Decisão**: explicitar regras complexas, aumentar coesão e testabilidade.
- **Pendente**: reserva com expiração (cron/worker) — ver item 10.

## 8) Voters — Ownership de Pedido/Evento
- **Status**: Concluído.
- **O que foi feito**:
  - `src/Security/Voter/PedidoVoter.php` (PEDIDO_VIEW).
  - `src/Security/Voter/EventoVoter.php` (EVENTO_EDIT, EVENTO_PUBLICAR).
  - Controllers atualizados para `denyAccessUnlessGranted()`.
- **Decisão**: mover autorização para Voters, reduzindo lógica nos controllers.

## 9) Componentização Twig
- **Status**: Concluído.
- **O que foi feito**: `templates/_partials/card_evento.html.twig` usado em `evento/index.html.twig`.
- **Decisão**: reuso de UI e alinhamento aos exemplos Bootstrap.

## 10) Reserva de Estoque com Expiração
- **Status**: Não iniciado.
- **Próximos passos**:
  - Modelar expiração temporal em `Ingresso`/reserva.
  - Worker periódico para liberar reservas expiradas.
  - Considerar Redis/DB para locks/TTL.

## 11) Dados de Demonstração (Seed)
- **Status**: Concluído.
- **O que foi feito**: `src/Command/SeedDemoCommand.php` cria usuários (cliente/vendedor), evento e lote.
- **Como usar**:
  - symfony console app:seed:demo
  - Logins: cliente@gatepass.local / vendedor@gatepass.local (senha `secret123`).

## 12) CI Básico (GitHub Actions)
- **Status**: Concluído (mínimo viável).
- **O que foi feito**: `.github/workflows/ci.yml` com composer validate, composer install, PHP lint e PHPUnit.
- **Pendente**: adicionar PHP-CS-Fixer e PHPStan (precisam estar no composer.json), e testes abrangentes.

## 13) Meus Pedidos — Filtros e Paginação
- **Status**: Não iniciado.
- **Próximos passos**:
  - Paginação com `Pagerfanta`/Doctrine Paginator.
  - Filtros (status, data, intervalo).

---

## Referências rápidas
- Migrar DB (índices):
  - bin/console make:migration
  - bin/console doctrine:migrations:migrate -n
- Mensageria (async):
  - Defina `MESSENGER_TRANSPORT_DSN` no `.env`
  - symfony console messenger:consume async -vv
- Seed de demo:
  - symfony console app:seed:demo

## Conclusão
Consolidamos as melhorias estruturais e de desempenho. Pendências principais: Captcha/RateLimiter avançado, expiração de reservas, paginação/filtros em “Meus Pedidos”, otimizações adicionais e pipeline CI com estáticos e testes completos.

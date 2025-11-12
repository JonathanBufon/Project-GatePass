# GatePass — Implementação e Ajustes (Changelog Técnico)

Data: 2025-11-12

## Visão Geral
Este documento resume os principais elementos existentes no código e os ajustes para atender aos fluxos: Login/Registro, Listagem, Detalhe, Checkout e Confirmação, além do Dashboard do Vendedor.

## Entidades (Entity)
- Usuario.php
- Cliente.php
- Vendedor.php
- Evento.php
- Lote.php
- Ingresso.php
- Pedido.php

## Repositórios (Repository)
- UsuarioRepository.php
- ClienteRepository.php
- VendedorRepository.php
- EventoRepository.php
- LoteRepository.php
- IngressoRepository.php
- PedidoRepository.php

## Serviços (Service)
- UsuarioService.php
  - Registro de cliente e vendedor com validações de negócio.
- EventoService.php
  - Busca de eventos publicados, por vendedor; criação/edição/publicação com regras.
- CarrinhoService.php
  - Gestão do carrinho em sessão; composição de itens e totais.
- PedidoService.php
  - Carrinho persistente/pedido pendente; finalizar pedido; listagem "meus pedidos".
- PagamentoService.php
  - Processamento do pagamento e atualização de status.

## Controllers e Rotas
- Autenticação
  - LoginController (`/auth/login`, `/auth/logout`).
  - RegistroController (`/auth/registro`).
  - RegistroVendedorController (`/auth/registro-vendedor`).
- Público/Cliente
  - EventoController (`/` listagem; `/evento/{id}` detalhe).
  - CarrinhoController (`/checkout` resumo sessão; `POST /carrinho/add/{id}` adicionar lote).
  - PedidoController (`/pedido/checkout` resumo pedido; `POST /pedido/checkout/submit`; `GET /pedido/detalhe/{id}`; `GET /pedido/meus-pedidos`).
  - CheckoutController (`/checkout/{id}` pagamento; `/checkout/confirmacao/{id}` confirmação jumbotron).
- Vendedor
  - Vendedor\DashboardController (`/vendedor/dashboard`).
  - Vendedor\EventoController (`/vendedor/evento/novo`, `/{id}/editar`, `/{id}/publicar`).

## Views (Twig) — Bootstrap 5.2
- Base
  - `templates/base.html.twig`: navbar fixa, container, flash messages, Bootstrap 5.2 CDN.
- Autenticação (Sign-in)
  - `templates/autenticacao/login.html.twig`.
  - `templates/registro/index.html.twig` (registro cliente).
  - `templates/autenticacao/registro_vendedor.html.twig` (registro vendedor).
- Listagem (Album)
  - `templates/evento/index.html.twig`.
- Detalhe (Product)
  - `templates/evento/detalhe.html.twig` com botão para adicionar ao carrinho (CSRF) e escolha de quantidade.
- Checkout (Checkout)
  - `templates/pedido/checkout.html.twig` (resumo + formulário).
  - `templates/checkout/index.html.twig` (quando usando CheckoutController diretamente por pedido).
- Confirmação (Jumbotron)
  - `templates/checkout/confirmacao.html.twig`.
- Dashboard (Dashboard)
  - `templates/vendedor/dashboard/index.html.twig`.
  - `templates/vendedor/evento/novo.html.twig` (form de evento).

## Ajustes Realizados/Validados
- Controllers magros com delegação a Services (SRP respeitado).
- Proteções de rota com `#[IsGranted]` e verificações de ownership em ações sensíveis.
- CSRF tokens validados em ações POST mutáveis (ex.: add carrinho, publicar evento).
- Navbar e layout base alinhados ao Bootstrap 5.2, removendo estilos duplicados e centralizando `padding-top` no `base.html.twig`.
- Rotas de checkout separando cenários: carrinho em sessão vs pedido pendente, evitando conflito de path com `CheckoutController` (usa `/checkout/{id}` e `confirmacao`).

## Pontos de Integração (Dados e Fluxo)
- Request → Controller → Service → Repository → Entity → DB.
- Controllers renderizam Twig Views e repassam DTOs/Form Views (`CheckoutFormType`, `PagamentoFormType`).

## Itens Restantes/Observações
- Garantir consistência visual conforme as páginas oficiais dos exemplos (ajustes finos de HTML/CSS podem ser aplicados nas Twig para ficarem 100% idênticas). 
- Verificar mensagens e rótulos para UX internacionalização (`translations/`).

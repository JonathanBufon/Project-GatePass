# GatePass — Correções de Bugs (V2) e Novos Requisitos (V2.1)

Data: 2025-11-12

## 1) Correções de Bugs

- **Bug 1: Enum TipoDocumento vazio no Registro de Vendedor**
  - Problema: "" is not a valid backing value for enum "App\Enum\TipoDocumento".
  - Causa: envio de `tipoDocumento` vazio a partir do formulário, ocasionado por placeholder selecionável sem validação suficiente.
  - Ações:
    - `src/Form/RegistroVendedorFormType.php`:
      - Campo `tipoDocumento` agora possui `placeholder` explícito, `required => true` e `NotBlank`.
      - Mantida validação condicional de `documento` (CPF/CNPJ) via `Callback`.
    - Template `templates/autenticacao/registro_vendedor.html.twig` utiliza o campo do FormType; o NotBlank impede o envio vazio.

- **Bug 2: Ausência de "Comprar" no Detalhe do Evento**
  - Problema: Página de detalhe não oferecia ação de compra.
  - Ações:
    - `templates/evento/detalhe.html.twig`:
      - Implementada seção "Ingressos disponíveis" listando lotes (`lote.nome`, `lote.preco`).
      - Formulário por lote com quantidade, CSRF (`csrf_token('add-lote-' ~ lote.id)`), `evento_id`, e POST para `app_pedido_adicionar`.

## 2) Novos Requisitos

- **HomeController e Homepage**
  - `src/Controller/HomeController.php`:
    - Rota `GET /home`, name `app_home`.
    - Renderiza `templates/home/index.html.twig`.
  - `templates/home/index.html.twig`:
    - Página de apresentação com Hero (Jumbotron) + cards de features (Bootstrap 5.2).

- **Navbar**
  - `templates/_partials/navbar.html.twig`:
    - `navbar-brand` → `path('app_home')`.
    - Link "Eventos" → `path('app_evento_index')`.

- **Rotas de Eventos**
  - `src/Controller/EventoController.php`:
    - Rota de listagem confirmada como `name='app_evento_index'` em `/`.

## 3) Observações
- O fluxo de compra usa `PedidoController::adicionar` (POST) e página de `checkout` subsequente.
- Validar em ambiente as mensagens de CSRF e a existência de lotes no evento.

## 4) Verificação Rápida
- Registro de Vendedor:
  - Placeholder em `tipoDocumento` não deve permitir submissão; NotBlank exige seleção válida.
- Detalhe do Evento:
  - Exibição de lotes e botão "Comprar" presentes; ao clicar, deve redirecionar ao checkout/carrinho conforme fluxo.
- Navbar:
  - Brand → `/home`, "Eventos" → `/`.

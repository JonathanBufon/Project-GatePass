# GatePass — Análise de Arquitetura

Data: 2025-11-12

## Visão Geral
- **Stack**: PHP >= 8.1, Symfony 6.4, Doctrine ORM 3.x, Twig, Bootstrap 5.2.
- **Arquitetura**: MVC expandido em 5 camadas, respeitando SOLID, GRASP e PSRs (1, 2, 4, 12).
- **Fluxo principal**: Request → Controller (magro) → Service (lógica) → Repository (acesso a dados) → Entity (mapeamento) → Twig View.

## Camadas (5-Layer MVC Expandido)
- **Entity (Domain Model/Mapeamento)**
  - Classes: `Usuario`, `Cliente`, `Vendedor`, `Evento`, `Lote`, `Ingresso`, `Pedido`.
  - Responsabilidade: estado, relações e invariantes básicas (ex.: status do pedido, publicação do evento via campos/flags).
- **Repository (Data Access)**
  - Classes: `UsuarioRepository`, `ClienteRepository`, `VendedorRepository`, `EventoRepository`, `LoteRepository`, `IngressoRepository`, `PedidoRepository`.
  - Responsabilidade: consultas explícitas e coesas (finders por status, por vendedor, publicados etc.).
- **Service (Business Orchestrator)**
  - Classes: `UsuarioService`, `EventoService`, `CarrinhoService`, `PedidoService`, `PagamentoService`.
  - Responsabilidade: regras de negócio e casos de uso (registro, publicação de evento, adicionar lote ao pedido, finalizar pedido, processar pagamento). Aplicação de Inversão de Dependência via injeção de repositórios/serviços.
- **Controller (Thin)**
  - Classes sob `Controller/` e `Controller/Vendedor/` e `Controller/Autenticacao/`.
  - Responsabilidade: orquestrar requisição/response, ler inputs (Request/Form), validar CSRF/AutZ e delegar a Services. Views renderizadas via Twig.
- **Twig View (Apresentação)**
  - Diretório `templates/` com páginas baseadas no Bootstrap 5.2 (Sign-in, Dashboard, Album, Product, Checkout, Jumbotron).

## Princípios SOLID e GRASP
- **SRP (Responsabilidade Única)**: Controllers delegam lógica; Services encapsulam casos de uso; Repositories apenas consulta/persistência; Views apenas renderizam.
- **OCP**: Services e Repositories com contratos estáveis; novas regras (ex.: novo meio de pagamento) podem ser adicionadas por extensão.
- **LSP**: Entidades e coleções tratadas via interfaces/contratos do Doctrine; nada quebra subtipos.
- **ISP**: Formularios/DTOs específicos por caso de uso; Services não exigem dependências gordas.
- **DIP**: Controllers dependem de abstrações (Services); Services dependem de Repositories injetados.
- **GRASP**: Controller como Controller/Creator; Services como Expert; Baixo acoplamento entre camadas; Alta coesão por módulo (Autenticação, Evento, Pedido, Vendedor).

## Fluxos por Tela (Bootstrap 5.2)
- **Login (Sign-in)**
  - Rota: `GET/POST /auth/login` → `LoginController::login()`
  - View: `templates/autenticacao/login.html.twig` (base Sign-in). SecurityBundle intercepta POST.
- **Registro (Cliente/Vendedor — Sign-in adaptado)**
  - Rotas: `/auth/registro` e `/auth/registro-vendedor` → `RegistroController`, `RegistroVendedorController`.
  - Views: `templates/registro/index.html.twig`, `templates/autenticacao/registro_vendedor.html.twig`.
- **Listagem de Eventos (Album)**
  - Rota: `GET /` → `EventoController::index()`.
  - Service: `EventoService::getEventosPublicados()`.
  - View: `templates/evento/index.html.twig` (Album).
- **Detalhe do Evento (Product)**
  - Rota: `GET /evento/{id}` → `EventoController::detalhe()`.
  - Service: `EventoService::findEventoPublicado($id)`.
  - View: `templates/evento/detalhe.html.twig` (Product) com formulário de “add to cart”.
- **Checkout (Resumo e Pagamento — Checkout)**
  - Fluxo A (pedido persistente):
    - `GET /pedido/checkout` → `PedidoController::checkout()` exibe resumo + `CheckoutFormType`.
    - `POST /pedido/checkout/submit` → `PedidoController::submit()` finaliza e redireciona p/ detalhe.
  - Fluxo B (carrinho em sessão):
    - `GET /checkout` → `CarrinhoController::index()`.
    - `POST /carrinho/add/{id}` → adiciona lote; CSRF validado.
  - View principal: `templates/pedido/checkout.html.twig` (Checkout do Bootstrap, adaptado com forms Symfony).
- **Confirmação de Compra (Jumbotron)**
  - Rota: `GET /checkout/confirmacao/{id}` → `CheckoutController::confirmacao()`.
  - Guarda: `IsGranted('ROLE_USER')` e verificação de dono do pedido e status `APROVADO`.
  - View: `templates/checkout/confirmacao.html.twig` (Jumbotron).
- **Dashboard do Vendedor (Dashboard)**
  - Rota: `GET /vendedor/dashboard` → `Vendedor\DashboardController::index()`.
  - Service: `EventoService::getEventosPorVendedor()`.
  - View: `templates/vendedor/dashboard/index.html.twig` (Dashboard).

## Segurança, Validações e Regras de Negócio
- Autenticação/Autorização com SecurityBundle. Guards por atributo `#[IsGranted]` e checagens de ownership em Services/Controllers.
- CSRF: ações de mutação (ex.: add ao carrinho, publicar evento) validam `_token`.
- Regras exemplares:
  - Evento só pode ser publicado se possuir lotes válidos.
  - Checkout exige pedido pendente do usuário logado e itens no carrinho.
  - Confirmação apenas para pedidos `APROVADO` do próprio usuário.

## Diagrama (ASCII) — Visão de Camadas

```
[HTTP Request]
     |
[Controller Thin] --(valid.)--> [Service (Use Case)] --(queries)--> [Repository] --(ORM)--> [DB]
     |                                                                       \
     +-------------------------------> [Twig View] <---------------------------+
```

## Diagrama (ASCII) — Fluxo “Comprar Ingresso”

```
Cliente -> Detalhe Evento (/evento/{id})
  click Adicionar -> POST /carrinho/add/{loteId} (CSRF)
  -> CarrinhoService.add -> redirect /checkout
/checkout -> render resumo + CheckoutForm
POST /pedido/checkout/submit -> PedidoService.finalizarPedido -> OK
-> redirect /pedido/detalhe/{id} ou /checkout/confirmacao/{id}
```

## Conclusão
A estrutura atual cumpre os requisitos de arquitetura (5 camadas), aplica SOLID/GRASP e integra os templates oficiais do Bootstrap 5.2 para as telas-chave do GatePass.

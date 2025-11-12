# GatePass — Reconstrução do Frontend (V2)

Data: 2025-11-12

## 1) Ação de Eliminação (Reset)
Foram removidos todos os arquivos anteriores dos diretórios:
- `/templates/` (todos os .html.twig)
- `/public/assets/` (todo CSS/JS/IMG customizado)

Objetivo: iniciar uma base limpa e coesa, 100% alinhada ao backend e ao Bootstrap 5.2.

## 2) Nova Estrutura de Templates

```
/templates
  base.html.twig
  _partials/
    flash_messages.html.twig
    footer.html.twig
    navbar.html.twig
  autenticacao/
    login.html.twig
    registro_cliente.html.twig
    registro_vendedor.html.twig
  evento/
    index.html.twig            # EventoController::index → '/'
    detalhe.html.twig          # EventoController::detalhe → '/evento/{id}'
  pedido/
    checkout.html.twig         # PedidoController::checkout → '/pedido/checkout'
    detalhe.html.twig          # PedidoController::detalhe → '/pedido/detalhe/{id}'
    meus_pedidos.html.twig     # PedidoController::meusPedidos → '/pedido/meus-pedidos'
  checkout/
    index.html.twig            # CheckoutController::checkout → '/checkout/{id}'
    confirmacao.html.twig      # CheckoutController::confirmacao → '/checkout/confirmacao/{id}'
  vendedor/
    dashboard/
      index.html.twig          # Vendedor\DashboardController::index → '/vendedor/dashboard'
    evento/
      novo.html.twig           # Vendedor\EventoController::novo/editar
```

Padrões aplicados:
- Base única (`base.html.twig`) com Navbar + Toasts + Footer.
- Componentes Bootstrap 5.2 (Sign-in, Album, Product, Checkout, Jumbotron, Dashboard) como referências visuais.
- Controle de acesso visual via `is_granted()` na navbar.

## 3) Nova Estrutura de Assets

```
/public/assets
  css/
    app.css      # Estilos unificados (micro-interações, toasts, temas globais)
  js/
    app.js       # Inicialização de toasts, tooltips, popovers, âncoras suaves, máscara CPF/CNPJ
  img/
    logo.svg     # Placeholder do logotipo
```

- Bootstrap 5.2 carregado via CDN no `base.html.twig`.
- `app.css` fornece camadas visuais mínimas e consistentes para todo o site.
- `app.js` concentra inicializações e comportamentos leves (sem dependências extras).

## 4) Observações e Próximos Passos
- Ajustar conteúdo específico de cada página conforme variáveis dos controllers (ex.: render dos lotes no detalhe de evento).
- Revisar UX em dispositivos móveis e aplicar refinamentos, se necessário.
- Adicionar testes funcionais básicos (smoke tests de render) e checklist de QA (rotas principais).
- Se desejado, integrar pipeline de build para minificação de `app.css` e `app.js` em produção.

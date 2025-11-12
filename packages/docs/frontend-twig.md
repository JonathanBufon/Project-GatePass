# GatePass — Padrões Frontend (Twig + Bootstrap 5.2)

Data: 2025-11-12

## Objetivo
Padronizar a estética, micro-interações e componentes nas views do GatePass, garantindo consistência com os exemplos oficiais do Bootstrap 5.2 e uma UX fluida e responsiva.

## Padrões Visuais
- **Tipografia**: Bootstrap 5.2 (Sistema) — sem override de fonte global.
- **Paleta** (Bootstrap padrão):
  - Primária: .text-bg-primary / .btn-primary (azul Bootstrap)
  - Sucesso: .text-bg-success / .btn-success
  - Aviso: .text-bg-warning / .btn-warning
  - Perigo/Erro: .text-bg-danger / .btn-danger
  - Secundário: .text-bg-secondary / .btn-secondary
- **Layout**:
  - Navbar fixa (fixed-top) no `base.html.twig`.
  - Compensação de topo via CSS embutido e `padding-top` unificado.
- **Espaçamento**: Utilitários Bootstrap (p-*, m-*, gap-*, py-5 em seções principais).

## Componentes Reutilizáveis (partials)
- `_partials/flash_messages.html.twig`
  - Renderiza mensagens de feedback como **Bootstrap Toasts** (sutil, autohide, canto superior direito).
  - Suporta níveis: success, warning, danger/error, info.
- `_partials/card_evento.html.twig`
  - Card de evento para listagem (Album).
  - Contém imagem/banner, nome, data, local e link para detalhe.
- Base (`templates/base.html.twig`)
  - Inclui assets globais: `assets/css/custom.css` e `assets/js/ui.js`.
  - Contém container fixo `.toast-container` para renderização de toasts.

## Páginas e Templates
- Sign-in/Registro: `autenticacao/login.html.twig`, `registro/index.html.twig`, `autenticacao/registro_vendedor.html.twig`
- Listagem (Album): `evento/index.html.twig` (usa `_partials/card_evento`)
- Detalhe (Product): `evento/detalhe.html.twig`
- Checkout (Checkout): `pedido/checkout.html.twig`, `checkout/index.html.twig`
- Confirmação (Jumbotron): `checkout/confirmacao.html.twig`
- Dashboard (Dashboard): `vendedor/dashboard/index.html.twig`, `vendedor/evento/novo.html.twig`

## Animações e Micro-interações
- **Hovers/Focus** (em `public/assets/css/custom.css`):
  - `.btn`, `.nav-link`, `.card`, `.form-control` com `transition: all 0.2s ease-in-out`.
  - `.btn:hover`, `.nav-link:hover` elevam levemente (translateY) para resposta visual sutil.
  - `.card:hover` com sombra reforçada e leve elevação.
  - `.form-control:focus` com `box-shadow` suave na cor primária do Bootstrap.
- **Toasts**:
  - Renderizados pelo partial de mensagens.
  - `data-bs-autohide` e `data-bs-delay` configurados para 4,5s.
- **Tooltips/Popovers** (opcional):
  - Inicializados globalmente em `assets/js/ui.js` (adicionar `data-bs-toggle="tooltip|popover"` ao markup quando necessário).
- **Âncoras suaves** (opcional):
  - Navegação por hash com scroll suave (em `ui.js`).

## Responsividade
- Grid responsivo padrão do Bootstrap: `row-cols-1 row-cols-sm-2 row-cols-md-3` em listagens.
- Navbar colapsável com toggler em breakpoints móveis.
- Animações leves mantêm performance em mobile.

## Estrutura de Assets
- `public/assets/css/custom.css`
  - Camada fina de UI polish e micro-interações (transitions, focus, toasts container), sem sobrepor o Bootstrap.
- `public/assets/js/ui.js`
  - Bootstrapper para inicializar toasts, tooltips e popovers.
- Pastas específicas existentes:
  - `public/assets/css/checkout|registro|vendedor` (CSS anteriores específicos por página, mantidos).
  - `public/assets/js/vendedor` (scripts específicos do módulo Vendedor).

## Recomendações de Uso
- Use utilitários Bootstrap para espaçamento/cores antes de CSS customizado.
- Para novos feedbacks de UI, utilize `app.flashes()` e o partial de toasts.
- Para ajuda contextual, aplique `data-bs-toggle="tooltip" title="..."` ou `data-bs-toggle="popover" data-bs-content="..."`.
- Em listagens, reutilize `_partials/card_evento.html.twig` para consistência visual.

## Próximos Passos (opcional)
- Adicionar modal de confirmação para ações críticas (ex.: publicar evento, finalizar compra).
- Exibir spinner (Bootstrap Spinner) durante submissões longas.
- Consolidar CSS específicos em `custom.css` quando aplicável, mantendo componentes agnósticos.

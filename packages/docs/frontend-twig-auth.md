# GatePass — Padronização de Templates de Autenticação (Bootstrap 5.2)

Data: 2025-11-12

## Objetivo
Padronizar todos os templates dentro de `templates/autenticacao/` seguindo o exemplo Sign-in do Bootstrap 5.2, com foco em estética, consistência, acessibilidade e micro-interações.

## Templates afetados
- `autenticacao/base_auth.html.twig`
  - Layout centrado com card (`max-width ~420px`), título/subtítulo e rodapé mínimo.
  - Removido layout em duas colunas e efeitos decorativos que não agregavam à clareza.
  - Inclui o partial de toasts para feedback imediato.
- `autenticacao/login.html.twig`
  - Formulário com `form-floating` (email/senha), remember-me e links auxiliares.
  - Erros exibidos com `alert` e `role="alert"`.
- `autenticacao/registro_vendedor.html.twig`
  - Formulário com `form-floating` em todos os campos e `form-check` para termos.
  - Corrigido atributo `novalidate` e aplicado espaçamentos consistentes.

## Padrões visuais e de layout
- **Card centralizado**: `card shadow-sm border-0 rounded-4` com `p-4 p-md-5`.
- **Tipografia**: títulos `h4` e subtítulos `text-muted`.
- **Controles**: `form-floating` para inputs; `form-check` para opções booleanas.
- **Ações**: botões `btn btn-primary btn-lg w-100`.
- **Links de contexto**: abaixo do formulário, com classes `small` e sem sublinhado agressivo (`text-decoration-none`).

## Acessibilidade
- Labels explícitas com `form-floating`.
- Feedback de erro com `role="alert"`.
- Foco visível melhorado via `public/assets/css/custom.css`.

## Micro-interações
- Transitions em `.btn`, `.nav-link`, `.card`, `.form-control` (ver `public/assets/css/custom.css`).
- Tooltips e Popovers habilitados globalmente em `public/assets/js/ui.js`.
- Toasts exibidos automaticamente para mensagens de sessão.

## Boas práticas adotadas
- Uso preferencial de utilitários do Bootstrap para espaçamento e cores.
- Redução de CSS customizado ao mínimo necessário.
- Separação de responsabilidades: layout base de auth (`base_auth`) + conteúdo do form (bloco `auth_form`).

## Como estender
- Para novas páginas de autenticação (ex.: recuperação de senha):
  - Criar um novo template que estenda `autenticacao/base_auth.html.twig`.
  - Preencher o bloco `auth_form` com os campos específicos usando `form-floating`/`form-check`.
  - Utilizar o partial de toasts para feedbacks.

## Próximos passos (opcional)
- Implementar página de “Esqueci minha senha” com fluxo real.
- Revisar i18n dos rótulos e mensagens.
- Consolidar CSS específicos legados de autenticação quando aplicável.

# GatePass — Redesign de Login (Bootstrap 5.2)

Data: 2025-11-12

## Objetivo
Recriar a tela de login com base no exemplo oficial "Sign-in" do Bootstrap 5.2, aplicando polimento visual, acessibilidade e micro-interações consistentes com o restante do projeto.

## Arquivos afetados
- `templates/autenticacao/base_auth.html.twig`
  - Novo layout centrado, com card simples, cabeçalho com logo/título/subtítulo e rodapé mínimo.
  - Remoção do split com imagem e efeito tilt para focar na clareza e reduzir distrações.
  - Inclui o partial de toasts para feedback.
- `templates/autenticacao/login.html.twig`
  - Formulário com floating labels (email/senha), remember-me, link de suporte, CTAs de registro e retorno à home.
  - Mensagens de erro com alert Bootstrap.

## Padrões de UI aplicados
- **Estrutura**: card central (max-width ~420px), spacing `p-4 p-md-5`, título `h4` e subtítulo muted.
- **Campos**: `form-floating` para email e senha; foco com micro-interação (`custom.css`).
- **Ações**: botão primário `btn btn-primary btn-lg` full-width; checkbox `form-check` para lembrar-me.
- **Links auxiliares**: "Esqueci minha senha" com tooltip (inicializado por `assets/js/ui.js`).
- **Feedback**: toasts (parcial `_partials/flash_messages.html.twig`) posicionados em container fixo no layout base.

## Acessibilidade
- Labels explícitas nos `form-floating`.
- `role="alert"` em erros.
- Foco visível aprimorado via `custom.css`.

## Micro-interações
- Transitions suaves em botões, inputs e cards (ver `public/assets/css/custom.css`).
- Tooltips ativados globalmente em `public/assets/js/ui.js`.

## Como personalizar
- Título/subtítulo e logo em `base_auth.html.twig` (bloco principal do card).
- Itens do formulário em `login.html.twig` (ex.: habilitar/ocultar remember-me, alterar links).
- Ajustar cores/efeitos em `public/assets/css/custom.css`.

## Snippets relevantes
- Inclusões globais (já presentes em `base.html.twig`):
  - CSS: `assets/css/custom.css`
  - JS: `assets/js/ui.js` (toasts, tooltips, popovers)

## Próximos passos (opcional)
- Adicionar link funcional de recuperação de senha quando o fluxo estiver disponível.
- Validar i18n nas mensagens de erro e rótulos.
- Teste de usabilidade em mobile (teclado virtual, foco e rolagem).

# GatePass — Design System v3 (Dark/Purple)

Data: 2025-11-12

## Objetivo
Padronizar toda a estilização do GatePass em um único tema escuro (Dark) com acentos roxos (Purple), aplicado a todas as áreas (Login, Site, Dashboard, Checkout), garantindo coesão visual e manutenção simplificada.

## Variáveis de Cor (CSS Variables)
Definidas em `public/assets/css/app.css` no seletor `:root`.

- Accents & Interaction
  - `--color-accent-light`: `#9886f7` — Links, destaques, hovers leves
  - `--color-accent-600`: `#8576e4` — Gradientes / acentos secundários
  - `--color-primary`: `#7265d1` — Botões primários e estados ativos
  - `--color-primary-hover`: `#5e55be` — Hover do botão primário
  - `--color-accent-dark`: `#4b44ab` — Acentos escuros e fundos de gradiente

- Backgrounds, Neutros e Texto
  - `--color-text`: `#FAFAFA` — Texto principal
  - `--color-text-muted`: `#c9c4f2` — Texto secundário/suave
  - `--color-text-secondary`: `#6459a4` — Placeholders e legendas
  - `--color-border`: `#4b437e` — Bordas e divisores
  - `--color-bg-card`: `#322d57` — Fundo de componentes (cards, modals)
  - `--color-bg-body`: `#191630` — Fundo principal (body, navbar)
  - `--color-bg-deep`: `#000009` — Fundo mais profundo (seções contrastantes)

- Outros
  - `--radius-sm`: `.375rem` — Raio pequeno
  - `--radius-md`: `.5rem` — Raio médio
  - `--radius-lg`: `.75rem` — Raio grande
  - `--elev-1`: `0 .5rem 1rem rgba(0,0,0,.35)` — Sombra 1
  - `--elev-2`: `0 .75rem 2rem rgba(0,0,0,.45)` — Sombra 2
  - `--focus-ring`: `0 0 0 .25rem rgba(114, 101, 209, .35)` — Realce de foco

## Aplicação do Tema (Principais Componentes)

- Global
  - `body`: `background: var(--color-bg-body)` e `color: var(--color-text)`.
  - Títulos e textos secundários usam `--color-text` e `--color-text-muted`.

- Navbar (`templates/_partials/navbar.html.twig`)
  - Fundo: `var(--color-bg-body)`
  - Borda inferior: `var(--color-border)`
  - Links: `var(--color-text)` com hover `var(--color-accent-light)`

- Botões
  - `.btn-primary`: `--color-primary` com hover `--color-primary-hover`
  - `.btn-outline-light`: borda `--color-accent-light` e hover preenchido

- Formulários (Login/Registro/Checkout)
  - `.form-control`, `.form-select`: fundo `var(--color-bg-body)`, texto `--color-text`, borda `--color-border`.
  - `:focus`: borda `--color-primary` + `box-shadow: var(--focus-ring)`.
  - Checkbox marcado usa `--color-primary`.

- Cards/Toasts/Modals
  - Fundo: `var(--color-bg-card)` e borda `var(--color-border)`.
  - `:hover`: `box-shadow: var(--elev-1)`.

- Links
  - Cor padrão `--color-accent-light`, hover com `--color-primary` e sublinhado.

- Gradiente de Autenticação
  - `.bg-auth-gradient`: gradientes radiais combinando `--color-accent-dark` e `--color-accent-600` sobre `--color-bg-body`.

## Como Usar

- Todos os templates já referenciam `assets/css/app.css` e herdam os estilos automaticamente.
- Para novos componentes, utilize as variáveis do `:root` para manter a consistência.
- Exemplos:
  - Borda customizada: `border-color: var(--color-border)`
  - Fundo customizado: `background: var(--color-bg-card)`
  - Destaque: `color: var(--color-accent-light)`

## Observações

- O Bootstrap 5.2 é carregado via CDN; o tema sobrescreve tokens com CSS.
- Evite CSS inline nos templates; concentre ajustes em `app.css`.
- Caso necessário, estender utilitários com classes (`.shadow-1`, `.rounded-md`, etc.).

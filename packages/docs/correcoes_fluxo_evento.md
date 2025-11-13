# Correções de Estilo (Form Focus) e Fluxo de Criação de Evento

## CSS aplicado em public/assets/css/app.css

- Ajuste de foco para inputs e selects
```css
.form-control:focus, .form-select:focus {
  border-color: var(--color-primary);
  box-shadow: var(--focus-ring);
  background-color: var(--color-bg-body);
  color: var(--color-text);
}
```

- Floating labels: manter fundo escuro e label legível
```css
.form-floating > .form-control {
  background-color: var(--color-bg-body);
  color: var(--color-text);
}
.form-floating > label {
  color: var(--color-text-secondary);
}
.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label {
  color: var(--color-accent-light);
}
/* Remove faixa branca atrás do label */
.form-floating > label::after {
  background-color: var(--color-bg-body) !important;
}
```

## Dashboard do Vendedor — CTA para criar evento

Arquivo: templates/vendedor/dashboard/index.html.twig

- Adicionado um botão de destaque no topo:
```twig
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 mb-0">Meus Eventos</h1>
  <a href="{{ path('app_vendedor_evento_novo') }}" class="btn btn-primary btn-lg">Criar Novo Evento</a>
</div>
```

## Template de Criação de Evento

Arquivo: templates/vendedor/evento/novo.html.twig

- Estrutura implementada (estende base, título, card e formulário Symfony):
```twig
{% extends 'base.html.twig' %}
{% block title %}Novo Evento - GatePass{% endblock %}
{% block main_content %}
<h1 class="h4 mb-4">{{ page_title|default('Criar Novo Evento') }}</h1>
<div class="card"><div class="card-body">
  {{ form_start(eventoForm) }}
    {{ form_widget(eventoForm) }}
    <button class="btn btn-primary mt-3" type="submit">Salvar</button>
  {{ form_end(eventoForm) }}
</div></div>
{% endblock %}
```

Observação: Os campos são renderizados via `form_widget`. Caso necessário, pode-se trocar para `form_row(...)` para controle fino de cada campo e uso de `form-floating`.

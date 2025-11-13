# Implementação do Módulo Vendedor (v2)

## 1) Relatório de Auditoria (RFs Mestre)

- RF-V.DASH (Dashboard)
  - RF-V.DASH.01 Lista de eventos do vendedor: Implementado (DashboardController + template).
  - RF-V.DASH.02 CTA “Criar Novo Evento”: Implementado (botão no topo do dashboard).
  - RF-V.DASH.03 Estatísticas rápidas: Não implementado (pendente service e UI).

- RF-V.EVT (Gestão de Eventos)
  - RF-V.EVT.01/02 Criar evento (Passo 1) com dados mestres: Implementado (EventoController::novo + EventoFormType).
  - RF-V.EVT.03 Tipo de Estrutura (Pista/Assentos Numerados): Implementado (enum TipoEstrutura + campo em Evento + form).
  - RF-V.EVT.04 Salvar rascunho: Implementado (status inicial RASCUNHO na entidade).
  - RF-V.EVT.05 Editar rascunho: Implementado (EventoController::editar).
  - RF-V.EVT.06 Publicar: Implementado (EventoController::publicar + EventoService::publicarEvento).
  - RF-V.EVT.07 Impedir publicação sem Lote: Implementado (regra no EventoService).
  - RF-V.EVT.08 Desativar/Cancelar: Não implementado (pendente rota/ação e UI).

- RF-V.LOTE (Passo 2)
  - Estrutura Pista (CRUD de Lotes): Parcial (entidade pronta; falta controllers/templates Passo 2).
  - Estrutura Assentos Numerados (Setores + Lotes por setor): Parcial (entidade Setor criada; falta UI/fluxos).
  - Validação de capacidade (A.05): Não implementado (pendente Constraint/Service).

- RF-V.MON (Monitoramento): Não implementado (relatórios e exportação pendentes).
- RF-V.FIN (Financeiro): Não implementado (extrato, dados bancários, saque pendentes).

## 2) Alterações de Schema (Entidades/Enums)

- Enums criados
  - App\Enum\EventoStatus: RASCUNHO, PUBLICADO, DESATIVADO.
  - App\Enum\TipoEstrutura: PISTA, ASSENTOS_NUMERADOS.

- Evento (App\Entity\Evento)
  - Adicionados: `status` (enum EventoStatus) e `tipoEstrutura` (enum TipoEstrutura|null).
  - Status inicial padronizado no construtor: `EventoStatus::RASCUNHO`.

- Setor (App\Entity\Setor) [NOVA]
  - Campos: id, evento (ManyToOne), nome, capacidade.
  - Repository: App\Repository\SetorRepository criado.

- Lote (App\Entity\Lote)
  - Adicionado: `setor` (ManyToOne|null) para suportar estrutura de assentos.

Observação: Necessário gerar e executar migrações do Doctrine para refletir o schema.

## 3) Implementações e Ajustes de Código

- Controllers/Services
  - EventoService::publicarEvento: usa EventoStatus enum e mantém regra "não publicar sem lotes".
  - EventoRepository: consultas com comparação usando `EventoStatus::PUBLICADO`.

- Formulários
  - EventoFormType: inclui `tipoEstrutura` (EnumType) e mantém apenas Passo 1 (sem coleção de lotes).

- Templates
  - vendedor/dashboard/index.html.twig: CTA "Criar Novo Evento" adicionado.
  - vendedor/evento/novo.html.twig: formulário de criação/edição em card.
  - evento/index.html.twig: exibição de `status` usando `evento.status.value`.

## 4) Próximos Passos (Planejamento)

- DASH (RF-V.DASH.03): implementar serviço de estatísticas (receita total, ingressos 24h) e cards na UI.
- EVT (RF-V.EVT.08): adicionar ação/rota "Desativar" com transição de estado para DESATIVADO.
- LOTE Passo 2:
  - Pista: criar LoteController + templates (listar/adicionar/editar/desativar lotes).
  - Assentos: SetorController + LoteSetorController; UI para setores e lotes por setor.
  - Validação capacidade por setor: Constraint/Service para somatório de lotes do setor <= capacidade.
- MON: MonitoramentoController + services de relatório e exportação de compradores.
- FIN: FinanceiroController + UIs para extrato, payout e solicitação de saque.

## 5) Migrações

Execute as migrações após validar as mudanças:
- bin/console make:migration
- bin/console doctrine:migrations:migrate

Verifique também os formulários e as páginas afetadas no fluxo de criação e edição de eventos.

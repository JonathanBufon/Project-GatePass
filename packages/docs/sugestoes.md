# GatePass — Sugestões Técnicas e Próximos Passos

Data: 2025-11-12

## Melhorias de Arquitetura
- **Camada de DTOs/ViewModels**: padronizar objetos de transporte para formulários e respostas, reduzindo acoplamento Controller↔Entity.
- **Portas e Adaptadores (Hexagonal)**: isolar Pagamento e Email em portas (interfaces) com adaptadores. Facilita mocks e troca de provedores.
- **Políticas de Domínio**: extrair regras mais complexas (ex.: estoque por lote, janelas de venda) para serviços de domínio dedicados.

## Performance e Escalabilidade
- **Consultas otimizadas**: usar `SELECT partial`/hydration adequada para listagens, índices em chaves de busca (evento.status, lote.evento_id).
- **Cache**: HTTP caching para listagem/Detalhe de eventos publicados e cache de catálogos com invalidação por publicação.
- **Fila/Assíncrono**: confirmação de pagamento e envio de e-mails via Messenger/Transport assíncrono.

## Segurança e Confiabilidade
- **Rate-limiting e Captcha** para login/registro.
- **Políticas de autorização refinadas** com Voters para ownership em vez de checks diretos nos Controllers.
- **Logs e Auditoria**: eventos de domínio (pedido criado, publicado, pago) e trilhas de auditoria.

## Qualidade e DX
- **Testes**: unitários para Services; funcionais para fluxos principais; fixtures com dados de exemplo.
- **CI/CD**: pipeline com lint (PHP-CS-Fixer/PHPCS), PHPStan, PHPUnit e integração com container (docker compose). 
- **Documentação técnica**: ADRs para decisões arquiteturais relevantes (ex.: duplicidade Carrinho vs Pedido Pendente e seus casos de uso).

## Frontend e UX
- **Fidelidade aos templates**: revisar Twig para seguir fielmente os exemplos do Bootstrap 5.2 (classes utilitárias, grid, componentes).
- **Componentização**: extrair partials reutilizáveis (cards de evento, headers) para `_partials/`.
- **Acessibilidade**: aria-labels, foco, contraste, mensagens de erro claras.

## Próximos Passos Recomendados
- Implementar Voters para checagens de ownership de `Pedido` e `Evento`.
- Adicionar estado de estoque por `Lote` (reservas temporárias com expiração no checkout).
- Abstrair `PagamentoService` por interface e suportar múltiplos gateways (sandboxed).
- Criar seeds/fixtures e comandos `bin/console` para carga de dados demo.
- Adicionar página "Meus Pedidos" como landing do cliente autenticado com filtros e paginação.

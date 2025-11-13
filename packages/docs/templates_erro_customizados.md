# Páginas de Erro Personalizadas (Twig)

## Estrutura de diretório
- `templates/bundles/TwigBundle/Exception/`
  - `error.html.twig` (base genérico)
  - `error404.html.twig`
  - `error403.html.twig`
  - `error500.html.twig`
  - `error503.html.twig`

## Descrição dos templates
- `error.html.twig`
  - Estende `base.html.twig` para herdar tema Dark/Purple (CSS/app.css, navbar, footer)
  - Exibe `status_code` e `status_text`
  - Link para `app_home`
  - Bloco `error_message` para mensagem amigável

- `error404.html.twig`
  - Extende `@Twig/Exception/error.html.twig`
  - Mensagem: "Erro 404: Página não encontrada. O recurso que você procurou não existe ou pode ter sido movido."

- `error403.html.twig`
  - Extende `@Twig/Exception/error.html.twig`
  - Mensagem: "Erro 403: Acesso Negado. Você não tem as permissões necessárias para visualizar esta página ou executar esta ação."

- `error500.html.twig`
  - Extende `@Twig/Exception/error.html.twig`
  - Mensagem: "Erro 500: Erro Interno. Algo deu errado em nossos servidores. Nossa equipe já foi notificada e estamos trabalhando para corrigir. Por favor, tente novamente mais tarde."

- `error503.html.twig`
  - Extende `@Twig/Exception/error.html.twig`
  - Mensagem: "Erro 503: Serviço Indisponível. O site está temporariamente em manutenção ou sobrecarregado. Por favor, tente novamente em alguns minutos."

## Observações
- O layout centraliza o conteúdo e destaca o código/texto do erro.
- Para testar em dev, habilite `framework: error_controller` padrão do Twig e force códigos via rotas de teste ou exceptions.

# GatePass — Refatoração CPF/CNPJ (Cadastro de Vendedor)

Data: 2025-11-12

## Objetivo
Permitir que Vendedores se cadastrem com CPF (PF) ou CNPJ (PJ), com validação adequada, unicidade e máscara de entrada no frontend.

## Alterações de Schema (Entidade)
- Arquivo: `src/Entity/Vendedor.php`
  - Removido: `cnpj` (string, length=18)
  - Adicionado: `documento` (string, length=255, `unique=true`)
  - Adicionado: `tipoDocumento` (enum `TipoDocumento`) — valores: `cpf` | `cnpj`
  - Constraint: `#[UniqueEntity(fields: ['documento'])]`
- Enum:
  - `src/Enum/TipoDocumento.php`

Observação: É necessário gerar e executar migrações.
- Gerar: `bin/console make:migration`
- Executar: `bin/console doctrine:migrations:migrate -n`

## Formulário (FormType)
- Arquivo: `src/Form/RegistroVendedorFormType.php`
  - Novo campo `tipoDocumento` (`ChoiceType`):
    - "Pessoa Física (CPF)" → `cpf`
    - "Pessoa Jurídica (CNPJ)" → `cnpj`
  - Novo campo `documento` (`TextType`) com validações:
    - `NotBlank`, `Length(min=11, max=18)`
    - `Callback` condicional:
      - Se `tipoDocumento == cpf`: valida CPF (algoritmo de dígitos verificadores)
      - Se `tipoDocumento == cnpj`: valida CNPJ (algoritmo de dígitos verificadores)
  - Atributos para máscara dinâmica: `data-mask-selector="tipo"` e `data-mask="document"`.

## DTO e Controller
- DTO: `src/Dto/RegistroVendedorDto.php`
  - Campos: `email`, `nomeFantasia`, `documento`, `tipoDocumento`.
- Controller: `src/Controller/Autenticacao/RegistroVendedorController.php`
  - Passa os campos do DTO para o serviço: `email`, `plainPassword`, `nomeFantasia`, `documento`, `tipoDocumento`.

## Serviço
- Arquivo: `src/Service/UsuarioService.php`
  - Método `registrarVendedor(...)` atualizado para receber `(documento, tipoDocumento)`.
  - Sanitização: `somenteDigitos()` remove formatação do documento antes de persistir.
  - Define `TipoDocumento::CPF` ou `TipoDocumento::CNPJ` conforme a escolha do usuário.

## Repositório
- `src/Repository/VendedorRepository.php`: nenhum método específico era utilizado; buscas por documento podem ser feitas via `findOneBy(['documento' => ...])`. Caso necessário, adicionar `findOneByDocumento(string $doc): ?Vendedor`.

## Frontend (Twig + JS)
- Template: `templates/autenticacao/registro_vendedor.html.twig`
  - Inclui campos `tipoDocumento` (select) e `documento` (input text) com `form-floating` e mensagens de erro.
- Máscara dinâmica: `public/assets/js/ui.js`
  - Observa o select `tipoDocumento` e o input `documento` via data-attributes.
  - Aplica máscara de CPF (000.000.000-00) ou CNPJ (00.000.000/0001-00) conforme a seleção.

## Testes Manuais (Sugestão)
1. Acessar `/auth/registro-vendedor`.
2. Selecionar `Pessoa Física (CPF)`, informar um CPF válido (com/sem máscara) e preencher demais campos.
3. Tentar com CPF inválido para checar a mensagem de erro.
4. Selecionar `Pessoa Jurídica (CNPJ)` e repetir o teste com um CNPJ válido/ inválido.
5. Tentar cadastrar duas vezes o mesmo documento para validar a unicidade.

## Considerações
- A validação no FormType usa um `Callback` condicional simples; pode ser substituída por `CustomConstraint` dedicado se necessário.
- Unicidade é garantida por índice único e `UniqueEntity`.
- As migrações são obrigatórias para refletir o novo schema no banco.

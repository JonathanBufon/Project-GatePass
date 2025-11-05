# Projeto GatePass 

 Atualizado em (29/10/2025) por Jonathan Bufon.


**O GatePass é uma plataforma de venda de ingressos construída com Symfony 6+ e PHP 8+, seguindo uma arquitetura estrita de 5 camadas e aderindo aos princípios de engenharia de software SOLID, GRASP e PSR.**

Este projeto foi desenvolvido com foco na manutenibilidade, testabilidade e separação clara de responsabilidades (SoC), utilizando o Symfony não apenas como um *framework*, mas como uma ferramenta para implementar padrões de design robustos.

## 1\. Princípios de Arquitetura

O núcleo do projeto é uma arquitetura MVC expandida em 5 camadas lógicas:

1.  **Camada 1: Entidade (Entity)**

    * Local: `src/Entity/`
    * Responsabilidade: Define a estrutura de dados e o mapeamento (ORM) com o banco de dados. Contém as regras de negócio mais fundamentais (ex: `Pedido::recalcularTotal()`).
    * Exemplos: `Evento`, `Lote`, `Usuario`, `Cliente`, `Vendedor`, `Pedido`, `Ingresso`.

2.  **Camada 2: Repositório (Repository)**

    * Local: `src/Repository/`
    * Responsabilidade: Atua como uma coleção de dados (Data Access Layer). É a *única* camada que deve construir e executar consultas (DQL, QueryBuilder).
    * Exemplos: `PedidoRepository::findPendentePorCliente()`.

3.  **Camada 3: Serviço (Service)**

    * Local: `src/Service/`
    * Responsabilidade: O cérebro da aplicação ("Fat Service"). Contém toda a lógica de negócio, orquestração e regras de domínio que não pertencem à Entidade.
    * Exemplos: `UsuarioService::registrarCliente()`, `PagamentoService::processarPagamento()`.

4.  **Camada 4: Controlador (Controller)**

    * Local: `src/Controller/`
    * Responsabilidade: "Thin Controller". Sua única função é orquestrar o fluxo de requisição:
        1.  Processar o `Request` HTTP.
        2.  Validar DTOs (Formulários).
        3.  Delegar a lógica de negócio ao Serviço (Camada 3).
        4.  Renderizar a `View` (Camada 5) ou retornar um `Response` JSON.
    * Exemplos: `CheckoutController`, `Vendedor/DashboardController`.

5.  **Camada 5: Visão (View)**

    * Local: `templates/`
    * Responsabilidade: Renderizar o estado da aplicação em HTML (Twig), utilizando os *templates* do Bootstrap 5.2 definidos.

## 2\. Configuração e Instalação do Ambiente

O ambiente de desenvolvimento foi configurado para garantir consistência e facilidade de uso.

### 2.1. Requisitos

* Symfony CLI
* Docker e Docker Compose

### 2.2. Passos de Instalação

1.  **Criação do Projeto:**
    O projeto foi iniciado com o *skeleton* orientado à web:

    ```bash
    symfony new gatepass --webapp
    ```

2.  **Configuração do Banco de Dados (Docker):**
    O `docker-compose.yml` padrão foi modificado para substituir o PostgreSQL pelo **MariaDB (versão 10.11)**, que é o banco de dados de produção definido.

3.  **Configuração do Ambiente (Symfony):**
    O arquivo `.env` foi configurado para se conectar ao serviço MariaDB do Docker, utilizando a porta `3311` do *host*:

    ```dotenv
    DATABASE_URL="mysql://gatepass_user:gatepass_pass@127.0.0.1:3311/gatepass_db?serverVersion=mariadb-10.11"
    ```

4.  **Execução do Ambiente:**

    ```bash
    # Subir os contêineres (MariaDB, Mailpit, etc.)
    docker-compose up -d --build
    ```

5.  **Setup do Banco de Dados:**

    ```bash
    # Criar o banco de dados 'gatepass_db'
    symfony console doctrine:database:create

    # Executar todas as migrações de entidade
    symfony console doctrine:migrations:migrate
    ```

## 3\. Funcionalidades Implementadas

O projeto foi estruturado em dois módulos de ator principais: o **Fluxo do Cliente** (compra) e o **Fluxo do Vendedor** (administração).

### 3.1. Módulo de Autenticação (Comum)

* **Registro de Cliente (`/auth/registro`):**

    * **Controller:** `Autenticacao/RegistroController`
    * **DTO:** `RegistroFormType`
    * **Serviço:** `UsuarioService::registrarCliente()`
    * **Regra:** Cria um `Usuario` (com `ROLE_USER`) e um `Cliente` (perfil) associado (OneToOne).

* **Registro de Vendedor (`/auth/registro-vendedor`):**

    * **Controller:** `Autenticacao/RegistroVendedorController`
    * **DTO:** `RegistroVendedorFormType`
    * **Serviço:** `UsuarioService::registrarVendedor()`
    * **Regra:** Cria um `Usuario` (com `ROLE_USER` e `ROLE_VENDEDOR`) e um `Vendedor` (perfil) associado.

* **Login e Logout (`/auth/login`, `/auth/logout`):**

    * **Controller:** `Autenticacao/LoginController`
    * **Segurança:** O `config/packages/security.yaml` está configurado com `form_login` e `logout`, interceptando as rotas e gerenciando a autenticação através do `SecurityBundle`.

### 3.2. Fluxo do Cliente (Compra)

Este fluxo implementa o ciclo de vida completo da compra de um ingresso.

* **1. Listagem de Eventos (Homepage - Template "Album")**

    * **Rota:** `/` (`app_home`)
    * **Controller:** `EventoController::index()`
    * **Serviço:** `EventoService::getEventosPublicados()`
    * **Regra:** O serviço delega ao repositório a busca, mas impõe a regra de `status = 'PUBLICADO'`.

* **2. Detalhe do Evento (Template "Product")**

    * **Rota:** `/evento/{id}` (`app_evento_detalhe`)
    * **Controller:** `EventoController::detalhe()`
    * **Serviço:** `EventoService::findEventoPublicado()`
    * **Regra:** O Controller lança um `404 Not Found` se o evento não for encontrado OU se o seu status não for 'PUBLICADO'.

* **3. Adicionar ao Pedido (Lógica de Serviço)**

    * **Rota:** `/pedido/adicionar/{id}` (`app_pedido_adicionar`)
    * **Controller:** `PedidoController::adicionar()`
    * **Serviço:** `PedidoService::adicionarLoteAoPedido()`
    * **Regra:** O serviço encontra (via `PedidoRepository::findPendentePorCliente`) ou cria um `Pedido` com status 'PENDENTE'. Em seguida, cria um `Ingresso` e o associa ao pedido, recalculando o total (`Pedido::recalcularTotal()`).

* **4. Checkout (Template "Checkout")**

    * **Rota:** `/checkout/{id}` (`app_checkout`) [GET, POST]
    * **Controller:** `CheckoutController::checkout()`
    * **DTO:** `PagamentoFormType`
    * **Regra (GET):** O Controller valida se o `Pedido` pertence ao `Cliente` logado e se o status ainda é 'PENDENTE'.
    * **Regra (POST):** O Controller delega o DTO (Form) ao `PagamentoService`.

* **5. Processamento do Pagamento (Simulado)**

    * **Serviço:** `PagamentoService::processarPagamento()`
    * **Regra:** Simula um pagamento, atualiza o `Pedido` para 'APROVADO' e os `Ingressos` para 'DISPONIVEL'.

* **6. Confirmação da Compra (Template "Jumbotron")**

    * **Rota:** `/checkout/confirmacao/{id}` (`app_compra_confirmada`)
    * **Controller:** `CheckoutController::confirmacao()`
    * **Regra:** Valida se o `Pedido` pertence ao `Cliente` logado e se o status é 'APROVADO' (impedindo o acesso direto à URL).

### 3.3. Fluxo do Vendedor (Administração)

Este fluxo implementa a área administrativa para gerenciamento de eventos.

* **Segurança (Configuração):**

    * O `security.yaml` foi atualizado com `access_control` para proteger todas as rotas `/vendedor/*`, exigindo `ROLE_VENDEDOR`.

* **1. Dashboard do Vendedor (Template "Dashboard")**

    * **Rota:** `/vendedor/dashboard` (`app_vendedor_dashboard`)
    * **Controller:** `Vendedor/DashboardController::index()`
    * **Serviço:** `EventoService::getEventosPorVendedor()`
    * **Regra:** O Controller obtém o `Vendedor` a partir do `Usuario` logado. O `EventoService` usa o `EventoRepository::findByVendedor()` para filtrar apenas os eventos que pertencem àquele Vendedor, garantindo o isolamento dos dados.

## 4\. Próximos Passos (Pendentes)

O projeto está com os fluxos de "Registro" e "Compra" completos, e o "Dashboard" do Vendedor iniciado. Os próximos passos lógicos são:

1.  **Implementar o CRUD de Eventos:**
    * Criar o `EventoFormType` (DTO).
    * Criar o `Vendedor/EventoController` (Camada 4) para as rotas `novo`, `editar`.
    * Expandir o `EventoService` (Camada 3) para incluir os métodos `criarNovoEvento()` e `atualizarEvento()`, garantindo que o `Vendedor` logado só possa editar os seus próprios eventos.
2.  **Implementar o CRUD de Lotes:**
    * Permitir que o Vendedor adicione e edite `Lotes` (preços, estoque) associados a um `Evento` existente.


---

## Testes e Execução Local da Aplicação

### Subindo o ambiente local

Para iniciar o ambiente completo (Docker + servidor Symfony) em modo de desenvolvimento, execute:

```bash
docker-compose up -d --build && symfony server:start
```

Se preferir iniciar os serviços separadamente:

```bash
docker-compose up -d --build
```

Em seguida, inicie o servidor Symfony:

```bash
symfony server:start
```

---

### Logins de Teste (Ambiente de Desenvolvimento)

Atualizado em (04/11/2025) por Jonathan Bufon.


Use as credenciais abaixo para acessar o sistema em ambiente local:

| Perfil    | E-mail                                          | Senha    |
| --------- | ----------------------------------------------- | -------- |
| Comprador | [jonathan@teste.com](mailto:jonathan@teste.com) | 12345678 |
| Vendedor  | [jonathan@admin.com](mailto:jonathan@admin.com) | 12345678 |

---

### Observações

* Certifique-se de que o Docker e o Symfony CLI estão instalados e configurados corretamente antes de executar os comandos.
* Caso encontre problemas de cache, execute:

  ```bash
  symfony console cache:clear
  ```

---


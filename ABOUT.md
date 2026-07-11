<p align="center">
  <img src="https://raw.githubusercontent.com/ArthurTavaresKss/Finance-Control/main/public/assets/img/logo.png" alt="Finance Control" width="160">
</p>

<h1 align="center">Finance Control — Documentação Técnica</h1>

<p align="center"><a href="README.md">Voltar ao README</a></p>

---

## Índice

- [Visão geral](#visão-geral)
- [Fluxo do sistema](#fluxo-do-sistema)
- [Arquitetura](#arquitetura)
- [Estrutura de diretórios](#estrutura-de-diretórios)
- [Banco de dados](#banco-de-dados)

## Visão geral

O Finance Control é um gerenciador de finanças pessoais construído em PHP puro, com banco de dados MySQL/MariaDB e autenticação por sessão. O objetivo do sistema é oferecer um fluxo simples e direto para registro de receitas e despesas, sem dependências de frameworks externos.

## Fluxo do sistema

1. **Cadastro e login do usuário**
   O acesso ao sistema é feito pela área pública em `public/login.php`. Após o login, uma sessão é criada e o usuário passa a ter acesso às páginas internas.

2. **Gerenciamento de transações**
   O usuário pode cadastrar, editar, visualizar e excluir transações de entrada e saída. Cada transação é vinculada ao usuário autenticado, o que garante isolamento dos dados.

3. **Transações recorrentes**
   O sistema suporta despesas ou receitas recorrentes, como assinaturas, salários e contas fixas. Essas transações têm um dia de ocorrência e podem ter início e término definidos.

4. **Predefinições de transações**
   O usuário pode salvar modelos de transação — tipo, descrição, valor e categoria — para reutilização posterior. Ao cadastrar uma nova transação ou transação recorrente, é possível selecionar uma predefinição no topo do formulário, que preenche os campos automaticamente. Isso agiliza lançamentos repetitivos, como salário ou aluguel.

5. **Painel e indicadores**
   O dashboard apresenta indicadores mensais — entradas, saídas e total movimentado — além de gráficos e agrupamentos por categoria.

6. **Importação e exportação de dados**
   O sistema permite exportar transações, recorrentes e predefinições em CSV, além de importar dados de planilhas manuais no mesmo formato.

## Arquitetura

- O sistema utiliza **PHP com PDO** para comunicação com o banco de dados.
- A conexão é feita através do arquivo `config/db.php`.
- A autenticação é controlada por sessões, com verificação de tempo de inatividade em `includes/auth.php`.
- As consultas principais ficam centralizadas em `includes/functions.php`, incluindo:
  - cadastro e autenticação de usuários
  - CRUD de transações
  - CRUD de transações recorrentes
  - CRUD de predefinições de transações
  - cálculo de indicadores mensais e anuais
  - filtros e paginação

## Estrutura de diretórios

| Diretório       | Descrição                                                          |
|------------------|---------------------------------------------------------------------|
| `public/`        | Páginas acessíveis pelo navegador: login, dashboard, transações, recorrentes, predefinições e perfil |
| `includes/`      | Funções reutilizáveis, autenticação e lógica de negócio             |
| `config/`        | Configuração da conexão com o banco de dados                        |
| `production/`    | Arquivos de instalação e configuração para ambiente Docker/produção |
| `migrations/`    | Alterações, mudanças e atualizazções realizadas no banco de dados   |

## Banco de dados

O sistema utiliza as seguintes tabelas principais:

| Tabela                     | Descrição                                        |
|------------------------------|---------------------------------------------------|
| `usuarios`                  | Dados de perfil e autenticação                     |
| `transacoes`                | Movimentações financeiras (entrada/saída)          |
| `transacoes_recorrentes`    | Transações recorrentes, com dia e período de ocorrência |
| `predefinicoes`             | Modelos reutilizáveis de transação                 |


---

Arthur Tavares — [GitHub](https://github.com/ArthurTavaresKss)
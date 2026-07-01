# Finance Control

Sistema web de controle financeiro pessoal, desenvolvido em PHP puro com banco de dados MySQL/MariaDB e autenticação por sessão.

---

## 📖 Como o Sistema Funciona

O Finance Control foi pensado para ser um gerenciador simples e prático de finanças pessoais. O fluxo principal é o seguinte:

1. **Cadastro e login do usuário**
   - O acesso ao sistema é feito pela área pública em `public/login.php`.
   - Após o login, uma sessão é criada e o usuário passa a ter acesso às páginas internas.

2. **Gerenciamento de transações**
   - O usuário pode cadastrar, editar, visualizar e excluir transações de entrada e saída.
   - Cada transação é vinculada ao usuário autenticado, o que garante isolamento dos dados.

3. **Transações recorrentes**
   - O sistema também suporta despesas ou receitas recorrentes, como assinaturas, salários e contas fixas.
   - Essas transações tem início e podem ter término definidos, além de um dia de ocorrência.

4. **Painel e indicadores**
   - O dashboard apresenta indicadores mensais, como entradas, saídas e total movimentado.
   - Também são exibidos gráficos e agrupamentos por categoria para facilitar a análise financeira.

5. **Importação e exportação de dados**
   - O sistema permite exportar transações e recorrentes em CSV.
   - Também há suporte para importação de arquivos CSV, facilitando a migração de dados de planilhas manuais.

---

## 🛠️ Detalhes Técnicos

A estrutura do projeto é organizada de forma simples:

- **`public/`**: páginas acessíveis pelo navegador, como login, dashboard, transações, recorrentes e perfil.
- **`includes/`**: funções reutilizáveis, autenticação e lógica de negócio.
- **`config/`**: configuração da conexão com o banco de dados.
- **`production/`**: arquivos de instalação e configuração para ambiente Docker/produção.

### Arquitetura básica

- O sistema utiliza **PHP com PDO** para comunicação com o banco de dados.
- A conexão é feita por meio do arquivo `config/db.php`.
- A autenticação é controlada por sessões, com verificação de tempo de inatividade em `includes/auth.php`.
- As consultas principais ficam centralizadas em `includes/functions.php`, incluindo:
  - cadastro e autenticação de usuários;
  - CRUD de transações;
  - CRUD de transações recorrentes;
  - cálculo de indicadores mensais e anuais;
  - filtros e paginação.

### Banco de dados

O sistema trabalha com tabelas como:

- `usuarios`
- `transacoes`
- `transacoes_recorrentes`

Essas tabelas armazenam as informações de perfil, movimentações financeiras e recorrências.

---

## 📝 Resumo Conciso

O Finance Control é uma aplicação web local simples, leve e funcional para controlar finanças pessoais. Ele permite registrar receitas e despesas, organizar transações recorrentes, acompanhar indicadores financeiros e exportar ou importar dados em CSV, tudo com uma interface direta e fácil de usar.

---

**Desenvolvido por Arthur Tavares**
[GitHub](https://github.com/ArthurTavaresKss)
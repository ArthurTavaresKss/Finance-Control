<p align="center">
  <img src="https://raw.githubusercontent.com/ArthurTavaresKss/Finance-Control/main/public/assets/img/logo.png" alt="Finance Control" width="220">
</p>
<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License: MIT"></a>
</p>

<h1 align="center">Finance Control</h1>

<p align="center">
  Sistema web de controle financeiro pessoal, desenvolvido em PHP puro.
</p>

<p align="center">
  <a href="INSTALL.md">Instalação</a> ·
  <a href="ABOUT.md">Documentação</a> ·
  <a href="#licença">Licença</a>
</p>

---

## Sobre

Finance Control é uma aplicação web para gerenciamento de finanças pessoais. Permite o registro de transações, transações recorrentes e predefinições reutilizáveis, além de indicadores e relatórios visuais organizados por período e categoria.

Para uma descrição detalhada da arquitetura e do funcionamento interno do sistema, consulte [ABOUT.md](ABOUT.md).

## Funcionalidades

- Cadastro e gerenciamento de transações, com filtros e paginação
- Transações recorrentes (assinaturas, salários, contas fixas)
- Predefinições de transações: modelos reutilizáveis que preenchem o formulário automaticamente
- Painel com indicadores mensais e gráficos por categoria
- Exportação e importação de dados em CSV
- Autenticação por sessão e gerenciamento de perfil

## Tecnologias

| Camada           | Tecnologia                |
|------------------|----------------------------|
| Linguagem        | PHP 8+                     |
| Banco de dados   | MySQL / MariaDB            |
| Frontend         | HTML, CSS, JavaScript      |
| Infraestrutura   | Docker, Docker Compose     |

## Instalação

O sistema pode ser executado localmente com XAMPP, para desenvolvimento, ou via Docker, para produção.

Instruções completas em [INSTALL.md](INSTALL.md):

- [Instalação com XAMPP](INSTALL.md#opção-1-xampp-desenvolvimento-local)
- [Instalação com Docker](INSTALL.md#opção-2-docker-recomendado-para-produção)

## Documentação

| Documento                 | Conteúdo                                                        |
|----------------------------|-------------------------------------------------------------------|
| [ABOUT.md](ABOUT.md)       | Fluxo do sistema, arquitetura e estrutura do banco de dados       |
| [INSTALL.md](INSTALL.md)   | Instalação, deploy, migrations e backup/restauração               |

## Estrutura do projeto

```
Finance-Control/
├── config/          Configurações do sistema
├── includes/        Funções e autenticação
├── public/          Arquivos acessíveis via navegador
├── migrations/       Mudanças de schema do banco, aplicadas incrementalmente
└── production/       Scripts e arquivos de instalação/deploy
```

## Licença

Este projeto está licenciado sob a **MIT License** — veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## Autor

Arthur Tavares — [GitHub](https://github.com/ArthurTavaresKss)
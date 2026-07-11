<p align="center">
  <img src="https://raw.githubusercontent.com/ArthurTavaresKss/Finance-Control/main/public/assets/img/logo.png" alt="Finance Control" width="160">
</p>

<h1 align="center">Finance Control — Instalação</h1>

<p align="center"><a href="README.md">Voltar ao README</a></p>

---

## Índice

- [Requisitos](#requisitos)
- [Instalação](#instalação)
  - [Opção 1: XAMPP (Desenvolvimento Local)](#opção-1-xampp-desenvolvimento-local)
  - [Opção 2: Docker (Recomendado para Produção)](#opção-2-docker-recomendado-para-produção)
- [Auto-Deploy](#auto-deploy)
- [Migrations](#migrations)
- [Backup e restauração](#backup-e-restauração)
- [Comandos úteis](#comandos-úteis)
- [Segurança](#segurança)
- [Estrutura do projeto](#estrutura-do-projeto)

## Requisitos

**XAMPP (Desenvolvimento Local)**

- XAMPP (Apache + PHP + MySQL/MariaDB)
- PHP 8.0 ou superior
- Navegador moderno

**Docker (Produção)**

- Servidor Linux (Ubuntu recomendado)
- Docker + Docker Compose
- Git

## Instalação

### Opção 1: XAMPP (Desenvolvimento Local)

1. Clone o repositório para a raiz do htdocs:

   ```bash
   git clone https://github.com/ArthurTavaresKss/Finance-Control.git /CAMINHO_PARA_O_SEU_HTDOCS/Finance-Control
   ```

2. Configure o XAMPP:

   - Acesse o painel de Admin do XAMPP e clique em **Config** no Apache.
   - Abra o `httpd.conf` e procure por:

     ```
     DocumentRoot "C:/xampp/htdocs"
     <Directory "C:/xampp/htdocs">
     ```

   - Altere para:

     ```
     DocumentRoot "C:/xampp/htdocs/Finance-Control"
     <Directory "C:/xampp/htdocs/Finance-Control">
     ```

   - Salve o arquivo. Essa alteração é necessária por conta dos arquivos `.htaccess`, que dependem dessa estrutura de pastas.

3. Importe o banco de dados:

   - Abra o phpMyAdmin (`http://localhost/phpmyadmin`)
   - Crie um banco de dados chamado `financecontrol`
   - Importe o arquivo `banco.sql`, localizado na raiz do projeto

4. Configure a conexão com o banco em `config/db.php`:

   ```php
   $host = 'localhost';
   $db   = 'financecontrol';
   $user = 'root';
   $pass = '';           // Senha do root do XAMPP (geralmente vazia)
   $charset = 'utf8mb4';
   ```

5. Acesse o sistema em `http://localhost:8080/`.

### Opção 2: Docker (Recomendado para Produção)

Forma recomendada para executar o sistema de maneira isolada e reprodutível.

1. Clone o repositório para uma pasta temporária:

   ```bash
   git clone https://github.com/ArthurTavaresKss/Finance-Control.git ~/tmp/finance-install
   ```

   Use especificamente o caminho `~/tmp/finance-install`; o `install.sh` depende desse local.

2. Entre na pasta de instalação:

   ```bash
   cd ~/tmp/finance-install/production
   ```

3. Execute o instalador:

   ```bash
   chmod +x install.sh
   ./install.sh -rp "SuaSenhaRootForte" -p "SuaSenhaUserForte" -pt 3847
   ```

   **Parâmetros disponíveis**

   | Parâmetro                    | Descrição                                             |
   |-------------------------------|--------------------------------------------------------|
   | `-rp`, `--root-password`     | Senha do usuário `root` do MariaDB                     |
   | `-p`, `--password`           | Senha do usuário `financeAdmin`                         |
   | `-pt`, `--port`              | Porta de acesso da aplicação (padrão: `3847`)           |

   Exemplo completo:

   ```bash
   ./install.sh -rp "Root@Finance2026!" -p "User@Finance2026!" -pt 3847
   ```

4. Aguarde a instalação. O script irá:

   - Instalar Git e Docker, se necessário
   - Criar a pasta `~/finance-control`
   - Configurar o Docker Compose
   - Subir os containers
   - Importar o banco de dados
   - Configurar o Auto-Deploy

5. Acesse o sistema em `http://SEU_IP_DO_SERVIDOR:3847` (ou a porta configurada).

6. Remova a pasta temporária:

   ```bash
   rm -rf ~/tmp/finance-install
   ```

## Auto-Deploy

O sistema possui Auto-Deploy configurado. A cada minuto, o servidor verifica se há novas atualizações no GitHub. Caso exista uma atualização, o processo:

1. Faz backup automático do banco de dados
2. Executa `git pull` do código
3. Aplica migrations pendentes, se houver
4. Reinicia automaticamente o container da aplicação

Os deploys podem ser acompanhados em:

```bash
cat ~/finance-control/deploy.log
```

## Migrations

O arquivo `banco.sql` é utilizado apenas na instalação inicial. Qualquer mudança de schema posterior — nova tabela, nova coluna, etc. — deve ser feita através de uma migration, e não editando o `banco.sql` diretamente.

**Como funciona**

- Cada mudança gera um novo arquivo em `migrations/`, por exemplo `migrations/0002_adiciona_campo_x.sql`.
- O script `run-migrations.sh` controla, em uma tabela `_migrations` no próprio banco, quais migrations já foram aplicadas, executando apenas as pendentes.
- O processo ocorre automaticamente a cada deploy via Auto-Deploy, sem apagar dados existentes.

Para aplicar manualmente, sem esperar o cron:

```bash
cd ~/finance-control/app && git pull
cd ~/finance-control
./run-migrations.sh
```

## Backup e restauração

**Backup automático**

Todos os dias às 3h, e também antes de cada deploy que aplica migrations, o sistema gera um backup compactado do banco em:

```
~/finance-control/backups/financecontrol_AAAA-MM-DD_HH-MM-SS.sql.gz
```

Backups com mais de 14 dias são apagados automaticamente. Esses arquivos ficam fora da pasta `app/` e nunca são acessíveis pela web.

Para gerar um backup manualmente:

```bash
cd ~/finance-control
./backup-db.sh
```

**Listar backups disponíveis**

```bash
cd ~/finance-control
./restore-db.sh
```

Executar sem argumentos lista os arquivos disponíveis em `backups/`.

**Restaurar um backup**

```bash
cd ~/finance-control
./restore-db.sh financecontrol_AAAA-MM-DD_HH-MM-SS.sql.gz
```

O script solicita confirmação (`SIM`) antes de continuar, já que a restauração sobrescreve o banco atual. Antes de restaurar, é gerado automaticamente um backup de segurança do estado atual.

> Atenção: restaurar um backup antigo não desfaz migrations aplicadas posteriormente. Caso o backup restaurado seja anterior a uma migration já aplicada, execute `./run-migrations.sh` em seguida para atualizar o schema.

## Comandos úteis

```bash
# Ver status dos containers
docker compose ps

# Reiniciar apenas a aplicação
docker compose restart app

# Ver logs da aplicação
docker compose logs -f app

# Parar todos os containers
docker compose down

# Subir os containers novamente
docker compose up -d
```

## Segurança

- Utilize sempre senhas fortes na instalação via Docker.
- Recomenda-se alterar as senhas padrão após a instalação.

## Estrutura do projeto

```
Finance-Control/
├── config/               Configurações do sistema
├── includes/             Funções e autenticação
├── public/               Arquivos acessíveis via navegador
├── migrations/           Mudanças de schema do banco, aplicadas incrementalmente
├── production/
│   ├── install.sh        Script de instalação
│   └── files/             Arquivos para instalação com Docker
│       ├── auto-deploy.sh      Atualização automática via git pull
│       ├── run-migrations.sh   Aplica migrations pendentes
│       ├── backup-db.sh        Gera backup do banco
│       └── restore-db.sh       Restaura um backup
└── .gitignore
```

Após a instalação, `~/finance-control/backups/` armazena os backups gerados, fora do webroot e nunca acessível pela web.

---

Arthur Tavares — [GitHub](https://github.com/ArthurTavaresKss)
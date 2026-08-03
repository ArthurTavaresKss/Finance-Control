<p align="center">
  <img src="https://raw.githubusercontent.com/ArthurTavaresKss/Finance-Control/main/public/assets/img/logo.png" alt="Finance Control" width="160">
</p>
<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License: MIT"></a>
</p>

<h1 align="center">Finance Control — Instalação</h1>

<p align="center"><a href="README.md">Voltar ao README</a></p>

---

## Índice

- [Requisitos](#requisitos)
- [Instalação](#instalação)
  - [Opção 1: XAMPP (Desenvolvimento Local)](#opção-1-xampp-desenvolvimento-local)
  - [Opção 2: Docker (Recomendado para Produção)](#opção-2-docker-recomendado-para-produção)
    - [Onde fica tudo depois da instalação](#onde-fica-tudo-depois-da-instalação)
    - [Gerenciando o serviço](#gerenciando-o-serviço)
    - [Auto-Deploy](#auto-deploy)
    - [Migrations](#migrations)
    - [Backup e restauração](#backup-e-restauração)
    - [Comandos úteis](#comandos-úteis)
    - [Desinstalação](#desinstalação)
- [Segurança](#segurança)
- [Estrutura do projeto](#estrutura-do-projeto)

## Requisitos

**XAMPP (Desenvolvimento Local)**

- XAMPP (Apache + PHP + MySQL/MariaDB)
- PHP 8.0 ou superior
- Navegador moderno

**Docker (Produção)**

- Servidor Linux **baseado em Ubuntu** (Ubuntu Server 22.04/24.04, por exemplo)
- Docker + Docker Compose
- Git
- Acesso `sudo`

> ⚠️ **O `install.sh` foi desenvolvido e testado para distribuições baseadas em Ubuntu/Debian.** O script usa `apt` para instalar dependências e depende de convenções do `systemd` e do `useradd` do Ubuntu. Ele **não é compatível** com distribuições baseadas em RHEL (RHEL, CentOS, Fedora, Rocky Linux, AlmaLinux), que usam `dnf`/`yum` e podem ter políticas de SELinux diferentes. Um script equivalente para RHEL está nos planos, mas ainda não existe.

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

Forma recomendada para executar o sistema de maneira isolada, reprodutível e seguindo boas práticas de implantação: usuário de sistema dedicado, sem privilégios de login, e gerenciamento via `systemd`.

1. Clone o repositório para uma pasta temporária:

   ```bash
   git clone https://github.com/ArthurTavaresKss/Finance-Control.git /tmp/finance-install
   ```


2. Entre na pasta de instalação:

   ```bash
   cd /tmp/finance-install/production
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

4. Aguarde a instalação. O script executa 11 etapas:

   1. Cria o usuário de sistema `financecontrol` (sem shell de login, home em `/opt/financecontrol`)
   2. Verifica/instala Git e Docker, e adiciona o usuário `financecontrol` ao grupo `docker`
   3. Clona a aplicação para `/opt/financecontrol/app`
   4. Copia os arquivos de configuração do Docker
   5. Aplica a senha e a porta personalizadas
   6. Ajusta permissões e cria os arquivos de log
   7. Configura o Auto-Deploy e o backup diário no `cron` do usuário `financecontrol`
   8. Sobe os containers temporariamente, já como o usuário `financecontrol`
   9. Aguarda o MariaDB, importa o banco e aplica as migrations pendentes
   10. Para os containers (o `systemd` assume o controle a partir daqui)
   11. Cria, habilita e inicia o serviço `systemd`

5. Acesse o sistema em `http://SEU_IP_DO_SERVIDOR:3847` (ou a porta configurada).

6. Remova a pasta temporária:

   ```bash
   rm -rf /tmp/finance-install
   ```

## Onde fica tudo depois da instalação

| Item                          | Local                                          |
|--------------------------------|--------------------------------------------------|
| Aplicação e scripts            | `/opt/financecontrol`                            |
| Código da aplicação (git)      | `/opt/financecontrol/app`                        |
| Backups do banco               | `/opt/financecontrol/backups`                    |
| Usuário de sistema             | `financecontrol` (sem login, dono de todo o diretório acima) |
| Log do Auto-Deploy             | `/var/log/financecontrol/deploy.log`             |
| Log de backup/migrations/restore | `/var/log/financecontrol/database.log`         |
| Serviço systemd                | `financecontrol.service`                         |

Como `/opt/financecontrol` pertence ao usuário `financecontrol`, acessar ou editar arquivos ali como outro usuário exige `sudo`. Os exemplos deste documento já vêm com `sudo -u financecontrol` onde necessário.

## Gerenciando o serviço

Os containers são gerenciados pelo `systemd`, não precisam mais ser subidos manualmente com `docker compose up -d` após um reboot do servidor:

```bash
# Ver status do serviço
sudo systemctl status financecontrol

# Reiniciar os containers (equivalente a "docker compose restart")
sudo systemctl reload financecontrol

# Parar tudo e subir de novo (equivalente a "down" + "up -d")
sudo systemctl restart financecontrol

# Parar tudo
sudo systemctl stop financecontrol

# Subir novamente
sudo systemctl start financecontrol
```

O serviço já está habilitado (`systemctl enable`), então os containers sobem automaticamente se o servidor reiniciar.

## Auto-Deploy

O sistema possui Auto-Deploy configurado no `cron` do usuário `financecontrol`. A cada minuto, o servidor verifica se há novas atualizações no GitHub. Caso exista uma atualização, o processo:

1. Faz backup automático do banco de dados
2. Executa `git reset --hard` do código com a versão mais recente da branch
3. Aplica migrations pendentes, se houver
4. Reinicia automaticamente o container da aplicação (`docker compose restart app`)

Os deploys podem ser acompanhados em:

```bash
sudo -u financecontrol cat /var/log/financecontrol/deploy.log
```

## Migrations

O arquivo `banco.sql` é utilizado apenas na instalação inicial. Qualquer mudança de schema posterior — nova tabela, nova coluna, etc. — deve ser feita através de uma migration, e não editando o `banco.sql` diretamente.

**Como funciona**

- Cada mudança gera um novo arquivo em `migrations/`, por exemplo `migrations/0002_adiciona_campo_x.sql`.
- O script `run-migrations.sh` controla, em uma tabela `_migrations` no próprio banco, quais migrations já foram aplicadas, executando apenas as pendentes.
- O processo ocorre automaticamente a cada deploy via Auto-Deploy, sem apagar dados existentes.
- As execuções de `run-migrations.sh` (manuais ou automáticas) ficam registradas em `/var/log/financecontrol/database.log`.

Para aplicar manualmente, sem esperar o cron:

```bash
cd /opt/financecontrol/app && sudo -u financecontrol git pull
sudo -u financecontrol /opt/financecontrol/run-migrations.sh
```

## Backup e restauração

**Backup automático**

Todos os dias às 3h, e também antes de cada deploy que aplica migrations, o sistema gera um backup compactado do banco em:

```
/opt/financecontrol/backups/financecontrol_AAAA-MM-DD_HH-MM-SS.sql.gz
```

Backups com mais de 14 dias são apagados automaticamente. Esses arquivos ficam fora da pasta `app/` e nunca são acessíveis pela web. Cada backup (manual ou automático) fica registrado em `/var/log/financecontrol/database.log`.

Para gerar um backup manualmente:

```bash
sudo -u financecontrol /opt/financecontrol/backup-db.sh
```

**Listar backups disponíveis**

```bash
sudo -u financecontrol /opt/financecontrol/restore-db.sh
```

Executar sem argumentos lista os arquivos disponíveis em `backups/`.

**Restaurar um backup**

```bash
sudo -u financecontrol /opt/financecontrol/restore-db.sh financecontrol_AAAA-MM-DD_HH-MM-SS.sql.gz
```

O script solicita confirmação (`SIM`) antes de continuar, já que a restauração sobrescreve o banco atual. Antes de restaurar, é gerado automaticamente um backup de segurança do estado atual. A restauração também é registrada em `database.log`.

> Atenção: restaurar um backup antigo não desfaz migrations aplicadas posteriormente. Caso o backup restaurado seja anterior a uma migration já aplicada, execute `run-migrations.sh` em seguida para atualizar o schema.

## Comandos úteis

```bash
# Ver status do serviço (recomendado)
sudo systemctl status financecontrol

# Ver status dos containers diretamente
sudo -u financecontrol bash -c "cd /opt/financecontrol && docker compose ps"

# Ver logs da aplicação
sudo -u financecontrol bash -c "cd /opt/financecontrol && docker compose logs -f app"

# Reiniciar apenas o container da aplicação
sudo -u financecontrol bash -c "cd /opt/financecontrol && docker compose restart app"
```

## Desinstalação

Para remover completamente o Finance Control do servidor — containers, volumes do banco, usuário de sistema, serviço `systemd` e `cron` — use o `uninstall.sh` incluído no repositório:

```bash
sudo /opt/financecontrol/uninstall.sh
```

O script pede confirmação (`SIM`) antes de prosseguir e, antes de apagar qualquer coisa, preserva automaticamente uma cópia dos backups e dos logs em `~/financecontrol-uninstall-backup-AAAAMMDD_HHMMSS`, no diretório de quem executou o comando.

## Segurança

- Utilize sempre senhas fortes na instalação via Docker.
- Recomenda-se alterar as senhas padrão após a instalação.
- A aplicação roda sob um usuário de sistema dedicado (`financecontrol`), sem shell de login, seguindo o princípio de menor privilégio — mesmo padrão usado por serviços como `www-data` ou `postgres`.

## Estrutura do projeto

```
Finance-Control/
├── config/               Configurações do sistema
├── includes/             Funções e autenticação
├── public/               Arquivos acessíveis via navegador
├── migrations/           Mudanças de schema do banco, aplicadas incrementalmente
├── production/
│   ├── install.sh        Script de instalação (Ubuntu/Debian)
│   ├── uninstall.sh      Script de desinstalação completa
│   └── files/             Arquivos para instalação com Docker
│       ├── auto-deploy.sh          Atualização automática via git
│       ├── run-migrations.sh       Aplica migrations pendentes
│       ├── backup-db.sh            Gera backup do banco
│       ├── restore-db.sh           Restaura um backup
│       └── financecontrol.service  Unit file do systemd
└── .gitignore
```

Após a instalação, a aplicação roda inteiramente em `/opt/financecontrol`, sob o usuário de sistema `financecontrol`, gerenciada pelo serviço `financecontrol.service` do `systemd`.

---

Arthur Tavares — [GitHub](https://github.com/ArthurTavaresKss)

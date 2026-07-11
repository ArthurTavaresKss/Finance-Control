# Finance Control

Sistema de Controle Financeiro Web desenvolvido em PHP puro.

### **[⇽ Voltar](README.md)**

---

## 📋 Requisitos

### Para XAMPP (Desenvolvimento Local)
- XAMPP (Apache + PHP + MySQL/MariaDB)
- PHP 8.0 ou superior
- Navegador moderno

### Para Docker (Produção)
- Servidor Linux (Ubuntu recomendado)
- Docker + Docker Compose
- Git

---

## 🚀 Instalação

### Opção 1: XAMPP (Desenvolvimento Local)

1. **Clone o repositório para a raiz do htdocs**
   ```bash
   git clone https://github.com/ArthurTavaresKss/Finance-Control.git /CAMINHO_PARA_O_SEU_HTDOCS/Finance-Control
   ```

2. **Configurações do XAMPP**
   - Acesse o painel de Admin do XAMPP e clique em Config no Apache.
   - Abra o httpd.conf e procure por:
   ```bash
   DocumentRoot "C:/xampp/htdocs" 
   <Directory "C:/xampp/htdocs">
   ```
   - Altere para:
   ```bash
   DocumentRoot "C:/xampp/htdocs/Finance-Control" 
   <Directory "C:/xampp/htdocs/Finance-Control">
   ```
   - E salve o arquivo. **(Isso é necessário por conta dos arquivos .htaccess, que mudam a estrutura das pastas)**

3. **Importe o banco de dados**
   - Abra o phpMyAdmin (`http://localhost/phpmyadmin`)
   - Crie um banco de dados chamado `financecontrol`
   - Importe o arquivo `banco.sql` que está na raiz do projeto

4. **Configure a conexão com o banco**
   - Abra o arquivo `config/db.php`
   - Ajuste as credenciais conforme seu ambiente XAMPP:

   ```php
   $host = 'localhost';
   $db   = 'financecontrol';
   $user = 'root';
   $pass = '';           // Senha do root do XAMPP (geralmente vazia)
   $charset = 'utf8mb4';
   ```

5. **Acesse o sistema**
   - Acesse: `http://localhost:8080/`

---

### Opção 2: Docker (Recomendado para Produção)

Esta é a forma mais recomendada para rodar o sistema de forma isolada e profissional.

#### Passo a passo:

1. **Clone o repositório para uma pasta temporária**
   ```git
   git clone https://github.com/ArthurTavaresKss/Finance-Control.git ~/tmp/finance-install
   ```
   - *Use especificamente o caminho `~/tmp/finance-install` para a pasta temporária. O `install.sh` conta com isso.

2. **Entre na pasta de instalação**
   ```bash
   cd ~/tmp/finance-install/production
   ```

3. **Execute o instalador**
   ```bash
   chmod +x install.sh
   ./install.sh -rp "SuaSenhaRootForte" -p "SuaSenhaUserForte" -pt 3847
   ```

   **Parâmetros disponíveis:**
   - `-rp` ou `--root-password`: Define a senha do usuário **root** do MariaDB
   - `-p` ou `--password`: Define a senha do usuário **financeAdmin**
   - `-pt` ou `--port`: Define a porta de acesso da aplicação *(padrão: `3847`)*

   Exemplo completo:
   ```bash
   ./install.sh -rp "Root@Finance2026!" -p "User@Finance2026!" -pt 3847
   ```

5. **Aguarde a instalação**
   - O script vai:
     - Instalar Git e Docker (se necessário)
     - Criar a pasta `~/finance-control`
     - Configurar Docker Compose
     - Subir os containers
     - Importar o banco de dados
     - Configurar o Auto-Deploy

6. **Acesse o sistema**
   - Após a instalação, acesse:
     ```
     http://SEU_IP_DO_SERVIDOR:3847
     ```
   - Ou a porta que você adicionou

7. **Remova a pasta temporária**
   ```bash
   rm -rf ~/tmp/finance-install
   ```

---

## 🔄 Auto-Deploy (Atualização Automática)

O sistema possui **Auto-Deploy** configurado.

- A cada **1 minuto**, o servidor verifica se há novas atualizações no GitHub.
- Caso exista, ele:
  1. Faz backup automático do banco de dados
  2. Faz `git pull` do código
  3. Aplica migrations pendentes (mudanças de schema, se houver)
  4. Reinicia automaticamente o container da aplicação

Você pode acompanhar os deploys em:

```bash
cat ~/finance-control/deploy.log
```

---

## 🗄️ Migrations (mudanças no banco de dados)

O `banco.sql` só é usado na instalação inicial. Depois disso, qualquer mudança de schema (nova tabela, nova coluna, etc.) deve ser feita através de uma **migration**, não editando o `banco.sql`.

### Como funciona
- Toda mudança vira um arquivo novo em `migrations/`, por exemplo `migrations/0002_adiciona_campo_x.sql`.
- O script `run-migrations.sh` controla, numa tabela `_migrations` dentro do próprio banco, quais migrations já foram aplicadas — e roda só as que faltam.
- Isso acontece automaticamente a cada deploy (via Auto-Deploy), sem apagar dados existentes.

Para aplicar manualmente, sem esperar o cron:
```bash
cd ~/finance-control/app && git pull
cd ~/finance-control
./run-migrations.sh
```

---

## 💾 Backup e Restauração do Banco de Dados

### Backup automático
Todo dia às 3h da manhã, e também antes de cada deploy que aplica migrations, o sistema gera um backup compactado do banco em:

```
~/finance-control/backups/financecontrol_AAAA-MM-DD_HH-MM-SS.sql.gz
```

Backups com mais de 14 dias são apagados automaticamente. Esses arquivos ficam **fora** da pasta `app/`, então nunca são acessíveis pela web.

Para rodar um backup manualmente a qualquer momento:
```bash
cd ~/finance-control
./backup-db.sh
```

### Listar os backups disponíveis
```bash
cd ~/finance-control
./restore-db.sh
```
(rodar sem argumento lista os arquivos disponíveis em `backups/`)

### Restaurar um backup
```bash
cd ~/finance-control
./restore-db.sh financecontrol_AAAA-MM-DD_HH-MM-SS.sql.gz
```

O script pede uma confirmação (`SIM`) antes de continuar, já que a restauração **sobrescreve o banco atual**. Antes de restaurar, ele também tira um backup de segurança do estado atual — assim, mesmo que você restaure o backup errado, dá pra voltar atrás.

⚠️ **Atenção:** restaurar um backup antigo não desfaz migrations aplicadas depois dele. Se você restaurar um backup de antes de uma migration ter rodado, rode `./run-migrations.sh` em seguida para recolocar o schema em dia.

---

## 🛠️ Comandos Úteis (Docker)

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

---

## 🔐 Segurança

- Sempre use senhas fortes ao instalar via Docker.
- Recomenda-se alterar as senhas padrão após a instalação.

---

## 📁 Estrutura do Projeto

```
Finance-Control/
├── config/              # Configurações do sistema
├── includes/            # Funções e autenticação
├── public/              # Arquivos acessíveis via navegador
├── migrations/           # Mudanças de schema do banco, aplicadas incrementalmente
├── production/
│   ├── install.sh       # Script de instalação
│   └── files/           # Arquivos para Instalação com Docker
│       ├── auto-deploy.sh     # Atualização automática via git pull
│       ├── run-migrations.sh  # Aplica migrations pendentes
│       ├── backup-db.sh       # Gera backup do banco
│       └── restore-db.sh      # Restaura um backup
└── .gitignore
```

Depois de instalado, `~/finance-control/backups/` guarda os backups gerados (fora do webroot, nunca acessível pela web).

---

## 📝 Notas Finais

- O sistema foi desenvolvido para ser simples e funcional.
- Recomenda-se o uso de **Docker** em ambientes de produção.
- O Auto-Deploy facilita muito a manutenção e atualização do sistema.

---

**Desenvolvido por Arthur Tavares**  
[GitHub](https://github.com/ArthurTavaresKss)
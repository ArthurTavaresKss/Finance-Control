# Finance Control

Sistema de Controle Financeiro Web desenvolvido em PHP puro.

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

2. **Entre na pasta de instalação**
   ```bash
   cd ~/tmp/finance-install/main/production
   ```

3. **Execute o instalador**
   - **Execute com *sudo*, para poder usar o crontab, e automatizar o script de auto-deploy.sh.
   ```bash
   chmod +x install.sh
   sudo ./install.sh -rp "SuaSenhaRootForte" -p "SuaSenhaUserForte"
   ```

   **Parâmetros disponíveis:**
   - `-rp` ou `--root-password`: Define a senha do usuário **root** do MariaDB
   - `-p` ou `--password`: Define a senha do usuário **financeAdmin**

   Exemplo completo:
   ```bash
   ./install.sh -rp "Root@Finance2026!" -p "User@Finance2026!"
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
     http://SEU_IP_DO_SERVIDOR:8085
     ```

---

## 🔄 Auto-Deploy (Atualização Automática)

O sistema possui **Auto-Deploy** configurado.

- A cada **1 minuto**, o servidor verifica se há novas atualizações no GitHub.
- Caso exista, ele faz `git pull` e reinicia automaticamente o container da aplicação.

Você pode acompanhar os deploys em:

```bash
cat ~/finance-control/deploy.log
```

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
├── production/
│   ├── install.sh       # Script de instalação
│   └── files/           # Arquivos para Instalação com Docker
└── .gitignore
```

---

## 📝 Notas Finais

- O sistema foi desenvolvido para ser simples e funcional.
- Recomenda-se o uso de **Docker** em ambientes de produção.
- O Auto-Deploy facilita muito a manutenção e atualização do sistema.

---

**Desenvolvido por Arthur Tavares**  
Repositório: [Finance-Control](https://github.com/ArthurTavaresKss/Finance-Control)

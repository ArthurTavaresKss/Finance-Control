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

1. **Clone o repositório**
   ```bash
   git clone https://github.com/ArthurTavaresKss/Finance-Control.git
   ```

2. **Copie a pasta do projeto para o XAMPP**
   - Copie a pasta `Finance-Control` para dentro de `htdocs` do XAMPP.
   - Ou acesse diretamente: `http://localhost/Finance-Control/public`

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
   - Acesse: `http://localhost/Finance-Control/public/login.php`

---

### Opção 2: Docker (Recomendado para Produção)

Esta é a forma mais recomendada para rodar o sistema de forma isolada e profissional.

#### Passo a passo:

1. **Clone o repositório**
   ```bash
   git clone https://github.com/ArthurTavaresKss/Finance-Control.git /tmp/finance-install
   ```

2. **Entre na pasta de instalação**
   ```bash
   cd /tmp/finance-install/main/production
   ```

3. **Execute o instalador**
   ```bash
   chmod +x install.sh
   ./install.sh -rp "SuaSenhaRootForte" -p "SuaSenhaUserForte"
   ```

   **Parâmetros disponíveis:**
   - `-rp` ou `--root-password`: Define a senha do usuário **root** do MariaDB
   - `-p` ou `--password`: Define a senha do usuário **financeAdmin**

   Exemplo completo:
   ```bash
   ./install.sh -rp "Root@Finance2026!" -p "User@Finance2026!"
   ```

4. **Aguarde a instalação**
   - O script vai:
     - Instalar Git e Docker (se necessário)
     - Criar a pasta `~/finance-control`
     - Configurar Docker Compose
     - Subir os containers
     - Importar o banco de dados
     - Configurar o Auto-Deploy

5. **Acesse o sistema**
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
docker compose restart finance-app

# Ver logs da aplicação
docker compose logs -f finance-app

# Parar todos os containers
docker compose down

# Subir os containers novamente
docker compose up -d
```

---

## 🔐 Segurança

- **Nunca** commite o arquivo `config/config.php` com senhas reais.
- O arquivo já está configurado no `.gitignore`.
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
│   └── files/           # Arquivos para Docker
├── banco.sql            # Script do banco de dados
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
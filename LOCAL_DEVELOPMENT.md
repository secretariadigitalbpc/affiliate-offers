# Desenvolvimento Local com XAMPP

## Ambiente

Sistema operacional esperado inicialmente: Windows.

Servidor local:

```text
XAMPP
```

Componentes:

```text
Apache
PHP
MySQL/MariaDB
phpMyAdmin
```

## Pasta do projeto

Exemplo:

```text
C:\xampp\htdocs\afiliados\
```

Acesso:

```text
http://localhost/afiliados/
```

## Banco

Abrir:

```text
http://localhost/phpmyadmin/
```

Criar:

```text
affiliate_system
```

Charset:

```text
utf8mb4
```

## Estrutura inicial

```text
C:\xampp\htdocs\afiliados\
├── public\
├── admin\
├── app\
├── database\
├── storage\
├── tests\
├── docs\
├── .env
├── .env.example
└── README.md
```

## Primeira etapa técnica

Criar somente:

```text
configuração
conexão PDO
health.php
schema inicial mínimo
página inicial
```

Não criar o sistema inteiro antes de testar a base.

## Conexão PDO

Usar configuração centralizada.

Exemplo conceitual:

```php
new PDO(
    'mysql:host=127.0.0.1;dbname=affiliate_system;charset=utf8mb4',
    $user,
    $password,
    [...]
);
```

Não repetir conexão em cada arquivo.

## Variáveis

Nunca inserir senhas reais diretamente no Git.

Criar:

```text
.env.example
```

e manter:

```text
.env
```

fora do versionamento.

## Preparação Mercado Livre

Copie para o `.env` somente quando possuir uma aplicação oficial compatível:

```dotenv
ML_OAUTH_ENABLED=false
ML_CLIENT_ID=
ML_CLIENT_SECRET=
ML_REDIRECT_URI=http://localhost/whatsappAF/admin/mercado-livre-callback.php
```

Mantenha o OAuth desativado até a compatibilidade com o Programa de Afiliados ser confirmada. O callback ainda não existe e nenhuma senha do Mercado Livre deve ser informada ao sistema.

## Primeiro administrador

Após aplicar a migration `003_create_administrators.sql`, crie o primeiro administrador pelo terminal PowerShell, sem salvar a senha no código:

```powershell
$env:ADMIN_BOOTSTRAP_PASSWORD = Read-Host "Senha" -MaskInput
D:\xampp2\php\php.exe database\seeds\create_admin.php admin@exemplo.com
Remove-Item Env:ADMIN_BOOTSTRAP_PASSWORD
```

A senha deve ter pelo menos 12 caracteres. O script usa `password_hash()` e não grava a senha em texto puro.

Login local:

```text
http://localhost/whatsappAF/admin/login.php
```

## Git

Adicionar no `.gitignore`:

```text
.env
/storage/logs/*
/storage/imports/*
/storage/backups/*
/vendor/
```

## Backup local

```powershell
D:\xampp2\php\php.exe database\backup.php
D:\xampp2\php\php.exe database\verify_backup.php storage\backups\arquivo.sql
```

O segundo comando restaura exclusivamente em um banco temporário validado e o remove ao terminar. Consulte `BACKUP_RESTORE.md` antes de usar os arquivos fora do ambiente local.

## Critério para migrar ao Umbrel

Somente quando:

- CRUD principal funcionar;
- analytics funcionar;
- importação/registro de vendas funcionar;
- dashboard estiver funcional;
- E2E passar;
- backup MySQL/MariaDB estiver testado;
- `PROJECT_STATUS.md` marcar MVP local aprovado.

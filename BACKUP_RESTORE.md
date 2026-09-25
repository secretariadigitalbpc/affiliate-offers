# Backup e verificação do banco

## Objetivo

Gerar um dump consistente do banco local `affiliate_system` e provar sua restauração sem substituir nem alterar o banco original.

Os arquivos são gravados em `storage/backups/`, pasta ignorada pelo Git. Um backup contém dados da aplicação, inclusive hashes de senha administrativa, e deve ser tratado como confidencial.

## Dependências

- PHP CLI do projeto;
- `mysqldump` e `mysql` compatíveis com o MariaDB/MySQL usado pela aplicação;
- credenciais já configuradas no `.env`.

No XAMPP, as ferramentas são detectadas a partir da localização do executável PHP. Em outra instalação, configure opcionalmente:

```dotenv
MYSQL_BIN_DIR=C:\caminho\para\mysql\bin
```

Não inclua usuário ou senha nessa variável.

## Criar backup

Na raiz do projeto:

```powershell
D:\xampp2\php\php.exe database\backup.php
```

O comando:

- usa `mysqldump --single-transaction` e `--skip-lock-tables`;
- envia as credenciais por um arquivo temporário, nunca pela linha de comando;
- apaga o arquivo temporário de credenciais no bloco `finally`;
- grava um `.sql` sem credenciais;
- cria um manifesto `.sql.json` com SHA-256 e contagem de cada tabela;
- cancela e remove os arquivos se as contagens mudarem durante o dump.

## Verificar restauração

Use exatamente o caminho retornado pelo comando anterior:

```powershell
D:\xampp2\php\php.exe database\verify_backup.php storage\backups\affiliate_system_AAAAMMDD_HHMMSS_ID.sql
```

A verificação:

1. aceita somente arquivos `.sql` dentro de `storage/backups`;
2. compara o SHA-256 com o manifesto;
3. cria um banco aleatório com prefixo obrigatório `affiliate_verify_`;
4. restaura o dump nesse banco temporário;
5. compara todas as tabelas e contagens com o manifesto;
6. exige o histórico `schema_migrations`;
7. remove o banco temporário no bloco `finally`;
8. confirma no `information_schema` que ele deixou de existir.

O código recusa remover qualquer banco cujo nome não corresponda exatamente ao padrão temporário gerado. O comando não oferece opção para restaurar sobre `affiliate_system`.

## Último backup validado

Em 25/09/2026 foi validado:

```text
storage/backups/affiliate_system_20260925_123802_71975858.sql
SHA-256: c19aa9903b214ea2686a84b84c7fa9d342bcfd4b9ce4c2287f24a3044f38aecc
Tabelas: 9
Banco temporário: affiliate_verify_20260925_123807_797d8e04
Banco temporário removido: sim
Health check do banco original: HTTP 200
```

## Recuperação real

Uma recuperação real deve ser executada em manutenção planejada, com destino novo e vazio, depois de validar o checksum e manter uma cópia do banco existente. A etapa atual comprova o arquivo e o procedimento em ambiente isolado; ela não autoriza sobrescrever o banco de produção ou o banco local atual.

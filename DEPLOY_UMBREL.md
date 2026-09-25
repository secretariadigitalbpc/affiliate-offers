# Deploy Futuro no Umbrel

## Status

**PACOTE PRONTO PARA TESTE CONTROLADO NO UMBREL.**

O MVP local está aprovado. A estrutura do pacote foi criada em `deploy/umbrel/affiliate-offers`, a imagem multi-arquitetura está pública no GHCR e fixada por digest, e o linter oficial foi aprovado. A submissão ao App Store continua bloqueada até a validação real no Umbrel.

## Objetivo futuro

Migrar:

```text
PHP + Apache/Nginx + MySQL/MariaDB
```

para containers ou aplicação apropriada no Umbrel.

## Regra

Não reescrever o sistema para fazer deploy.

A migração deve manter:

- PHP;
- banco compatível;
- estrutura lógica;
- dados;
- endpoints;
- regras de negócio.

## Antes do deploy

```text
[x] MVP local aprovado
[x] banco documentado
[x] backup funcionando
[x] restore testado em banco temporário isolado
[x] dependências documentadas
[x] variáveis de ambiente documentadas
[x] paths de execução relativos auditados
[x] nenhum caminho absoluto de Windows no código de execução
[x] imagem própria publicada para amd64 e arm64
[x] imagem do Compose fixada pelo digest remoto
[x] website, repositório e suporte substituídos
[ ] marcador da futura submissão substituído
[x] linter oficial do App Store executado com verificação de imagens
[ ] instalação, reinício e persistência testados em umbrelOS
```

## Cuidado importante

Desde o desenvolvimento local, não codificar caminhos como:

```text
C:\xampp\htdocs\...
```

dentro da aplicação.

Usar caminhos relativos ou baseados em `__DIR__`.

Isso facilitará a migração para Linux/Umbrel.

## Referência oficial consultada

Verificação realizada em 25/09/2026 no repositório oficial [getumbrel/umbrel-apps](https://github.com/getumbrel/umbrel-apps).

O pacote segue os requisitos atuais relevantes:

- manifesto `umbrel-app.yml` com `manifestVersion: 1`;
- `docker-compose.yml` com `app_proxy`;
- dados persistentes sob `${APP_DATA_DIR}/data`;
- imagens fixadas por digest e suporte esperado a `linux/amd64` e `linux/arm64`;
- segredos locais derivados em `exports.sh`;
- arquivo `*.template` para configuração renderizada;
- nenhuma montagem do Docker socket ou privilégio de host.

## Imagens-base verificadas

```text
php:8.2.29-apache-bookworm
digest: sha256:c2408ddfc8988020e521f5a666cf23e11cba795f03fc1dbc4b1c233337ca7d5f
plataformas confirmadas: linux/amd64 e linux/arm64/v8

mariadb:11.4.8
digest: sha256:bc474f00629f0123c10f9e1bca193a45d18af15a274cf0656acda64f1086c3b6
plataformas confirmadas: linux/amd64 e linux/arm64/v8
```

O digest do MariaDB já está aplicado. A aplicação foi publicada em `ghcr.io/secretariadigitalbpc/affiliate-offers:0.1.1` para `linux/amd64` e `linux/arm64`, sob o digest remoto `sha256:ff64e6f104e7631382a5271261ab8da3bff827bc3e79d591ceffb21172672be3`, já aplicado ao Compose.

A lista OCI local anterior tem digest `sha256:1572ba08fc01041f9fc07f0805f4e96d07c539a3fc2366a21b18669a060b097c`. Ela permanece apenas como evidência do build local; o pacote usa corretamente o digest remoto do GHCR.

O ambiente de build fica na distribuição dedicada `DockerBuild`, armazenada em `F:\WSL\DockerBuild`. O artefato local está em `F:\DockerBuild\affiliate-offers-0.1.0.oci.tar`.

## Variáveis no Umbrel

O arquivo `app.env.template` gera a configuração de produção com:

```text
APP_ENV=production
APP_DEBUG=false
DB_HOST=db
DB_PORT=3306
DB_NAME=affiliate_system
DB_USER=affiliate
DB_PASS=<derivada pelo Umbrel>
ADMIN_BOOTSTRAP_EMAIL=admin@umbrel.local
ADMIN_BOOTSTRAP_PASSWORD=<APP_PASSWORD do Umbrel>
ML_OAUTH_ENABLED=false
```

Client ID e Client Secret do Mercado Livre continuam vazios. Eles não devem ser incluídos na imagem ou no manifesto.

## Instalação planejada

1. adicionar `https://github.com/secretariadigitalbpc/affiliate-offers` em **App Store > Community App Stores**;
2. instalar **Ofertas de Afiliados** pela loja `Secretaria Digital BPC`;
3. Umbrel renderiza `app.env.template` usando os segredos derivados;
4. MariaDB cria o banco e o usuário no volume persistente;
5. o container PHP aguarda o banco ficar disponível;
6. `database/migrate.php` aplica/verifica as migrations sob lock;
7. `ensure_admin.php` cria o administrador apenas se ainda não existir;
8. Apache inicia e o `app_proxy` abre `/public/`.

## Atualização e rollback planejados

- antes de atualizar, gerar backup validado do banco;
- manter a tag e o digest da imagem anterior registrados;
- atualizar a imagem somente depois de testar suas migrations;
- em falha, parar o pacote, restaurar o Compose/digest anterior e reiniciar;
- restaurar banco somente quando uma migration incompatível exigir, sempre em destino novo validado antes da troca;
- nunca usar rollback de imagem como substituto automático de rollback de schema.

## Backup no Umbrel

Os volumes do banco e de `storage/` permanecem sob `${APP_DATA_DIR}/data` e entram no backup padrão do Umbrel. Nenhum `backupIgnore` foi definido porque banco, importações e backups locais são dados relevantes para recuperação.

## Bloqueios para concluir a etapa

- instalação pelo ciclo de vida do Umbrel disponível em `http://umbrel.local`;
- teste real de atualização e rollback no umbrelOS.
- criação do pull request de submissão somente depois dos testes, para então substituir `REPLACE_WITH_PR`.

O runtime local já confirmou a imagem da aplicação em `amd64`, a execução de uma imagem oficial em `arm64`, login, fluxo E2E, reinício e persistência com MariaDB. Isso não substitui a validação final pelo `app_proxy` e pelos mecanismos de instalação/atualização do Umbrel.

O linter oficial também foi executado em uma cópia descartável de `getumbrel/umbrel-apps` com `npm run lint:apps -- affiliate-offers --check-images`, retornando `No issues found`. O `git diff --check` do pacote terminou sem erros.

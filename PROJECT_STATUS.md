# Status do Projeto

## Projeto

Sistema de Ofertas/Afiliados — Mercado Livre + Shopee.

## Ambiente atual

**LOCAL — XAMPP**

Stack:

```text
Apache
PHP
MySQL/MariaDB
HTML
CSS
JavaScript
```

## Deploy futuro

Umbrel, somente após o MVP local estar aprovado.

## Etapa atual

**ETAPA 17 — publicar imagem e validar o pacote em umbrelOS**

Status: EM ANDAMENTO — IMAGEM E LINTER APROVADOS; CICLO DE VIDA NO UMBREL PENDENTE

## Concluído

- [x] objetivo geral definido;
- [x] desenvolvimento local definido;
- [x] XAMPP escolhido;
- [x] PHP escolhido como backend;
- [x] HTML/CSS/JavaScript escolhidos para frontend;
- [x] MySQL/MariaDB escolhido como banco;
- [x] PDO escolhido para acesso ao banco;
- [x] analytics de views e cliques planejado;
- [x] vendas/comissões planejadas;
- [x] integrações separadas por marketplace;
- [x] Umbrel adiado para pós-MVP;
- [x] automação de WhatsApp adiada para fase futura.
- [x] estrutura inicial de pastas criada;
- [x] configuração local por `.env` criada;
- [x] conexão centralizada com PDO criada;
- [x] banco `affiliate_system` criado com `utf8mb4`;
- [x] controle inicial de migrations criado;
- [x] endpoint `GET /public/api/health.php` criado;
- [x] página inicial criada;
- [x] scaffold anterior de FastAPI/Next.js/Docker removido.
- [x] migration da tabela `products` criada e aplicada;
- [x] model, repository, service e controller de produtos criados;
- [x] validação server-side de produtos criada;
- [x] endpoints de criar, consultar, listar e editar produtos criados;
- [x] tela administrativa mínima de produtos criada;
- [x] proteção CSRF e sessão segura adicionadas às alterações de produtos;
- [x] unicidade por marketplace e identificador externo validada.
- [x] migration da tabela `administrators` criada e aplicada;
- [x] senhas protegidas com `password_hash()` e verificadas com `password_verify()`;
- [x] criação segura de administrador por script CLI implementada;
- [x] login e logout administrativos implementados;
- [x] ID da sessão regenerado após login;
- [x] tela e endpoints de produtos protegidos por autenticação;
- [x] logout protegido por CSRF e destruição de sessão.
- [x] migration da tabela `affiliate_links` criada e aplicada;
- [x] chave estrangeira entre links e produtos criada;
- [x] model, repository, validator e controller de links criados;
- [x] validação de URL HTTP/HTTPS e compatibilidade de marketplace criada;
- [x] endpoints protegidos de criar, listar e editar links criados;
- [x] tela administrativa de links de afiliado criada;
- [x] operações de escrita protegidas por autenticação e CSRF.
- [x] migration da tabela `offers` criada e aplicada;
- [x] preços armazenados como `DECIMAL`, sem uso de `FLOAT`;
- [x] cálculo server-side do percentual de desconto implementado;
- [x] status e período de validade das ofertas validados;
- [x] coerência entre produto e link de afiliado validada;
- [x] model, repository, validator e controller de ofertas criados;
- [x] endpoints protegidos de criar, listar e editar ofertas criados;
- [x] tela administrativa de ofertas criada.
- [x] consulta pública isolada criada;
- [x] somente ofertas publicadas, ativas e dentro da validade são exibidas;
- [x] vitrine responsiva de ofertas criada;
- [x] página pública de detalhes do produto/oferta criada;
- [x] link oficial de afiliado aberto diretamente com atributos seguros;
- [x] escaping de conteúdo dinâmico e estado vazio implementados;
- [x] páginas de ofertas indisponíveis retornam HTTP 404.
- [x] migration da tabela `campaigns` criada e aplicada;
- [x] model, repository, validator e controller de campanhas criados;
- [x] cadastro, listagem e edição administrativos criados;
- [x] origem, mídia, slug e estado ativo implementados;
- [x] unicidade de slug aplicada no banco;
- [x] endpoints de campanhas protegidos por autenticação e CSRF;
- [x] normalização compartilhada de slugs acentuados corrigida para Windows.
- [x] migrations das tabelas `page_views` e `clicks` criadas e aplicadas;
- [x] eventos vinculados no servidor à oferta, produto e link de afiliado;
- [x] atribuição opcional por campanha ativa, origem e sessão anônima implementada;
- [x] endpoints públicos de visualização e clique criados;
- [x] registro de visualização integrado à página de detalhes;
- [x] registro não bloqueante de clique integrado ao link de afiliado;
- [x] família genérica do navegador armazenada sem IP ou user-agent completo;
- [x] parâmetros de campanha e origem preservados entre vitrine e detalhes;
- [x] teste transacional e teste HTTP controlado de analytics concluídos.
- [x] consultas agregadas de visualizações e cliques implementadas;
- [x] cálculo de CTR geral e por oferta implementado;
- [x] filtros inclusivos por período e campanha implementados;
- [x] endpoint administrativo de resumo protegido por autenticação criado;
- [x] dashboard administrativo responsivo criado;
- [x] tabela com desempenho das 20 principais ofertas criada;
- [x] navegação para o dashboard adicionada às telas administrativas;
- [x] validação server-side dos filtros implementada;
- [x] teste controlado de totais, CTR e campanha concluído.
- [x] migration da tabela `sales` criada e aplicada;
- [x] valores bruto e de comissão armazenados em `DECIMAL(12,2)`;
- [x] cadastro manual e listagem administrativa de vendas criados;
- [x] status pendente, aprovada, cancelada e reembolsada implementados;
- [x] vínculos opcionais com produto e campanha implementados;
- [x] compatibilidade entre marketplace da venda e do produto validada;
- [x] referência externa única por marketplace implementada;
- [x] endpoints de vendas protegidos por autenticação e CSRF;
- [x] tela administrativa de vendas integrada à navegação;
- [x] teste transacional e fluxo HTTP autenticado concluídos.
- [x] total de vendas aprovadas integrado ao resumo de analytics;
- [x] valores bruto e de comissão aprovados agregados por período;
- [x] conversão de clique para venda calculada no servidor;
- [x] filtros por período e campanha aplicados também às vendas;
- [x] vendas canceladas, pendentes e reembolsadas excluídas das métricas;
- [x] novos indicadores monetários adicionados ao dashboard responsivo;
- [x] contrato do endpoint ampliado sem quebrar as métricas anteriores;
- [x] cenário controlado de conversão e comissão concluído.
- [x] formato CSV de vendas documentado e exemplo criado;
- [x] upload administrativo protegido por autenticação e CSRF;
- [x] validação de extensão, tamanho, UTF-8, cabeçalhos e limite de linhas;
- [x] suporte a CSV delimitado por vírgula ou ponto e vírgula;
- [x] pré-validação de todas as linhas antes da persistência;
- [x] linhas inválidas relatadas com número e campos problemáticos;
- [x] lote válido persistido em transação com rollback em falha de banco;
- [x] idempotência por marketplace e referência externa;
- [x] duplicatas existentes e internas ao arquivo ignoradas com segurança;
- [x] relatório de importadas, ignoradas e inválidas exibido na interface;
- [x] teste transacional e upload HTTP multipart concluídos.
- [x] documentação pública oficial do Mercado Livre verificada;
- [x] OAuth geral documentado com Authorization Code, `state`, redirect URI exata e PKCE S256 opcional;
- [x] ausência de API pública específica de afiliados registrada como limitação, sem assumir incompatibilidade definitiva;
- [x] variáveis Mercado Livre adicionadas ao `.env.example`, sem credenciais reais;
- [x] OAuth mantido desativado por padrão;
- [x] configuração e construtor de URL OAuth isolados em `app/Integrations/MercadoLivre/`;
- [x] status administrativo criado sem exposição do Client Secret;
- [x] tela de diagnóstico da integração adicionada ao dashboard;
- [x] importação CSV mantida como alternativa funcional;
- [x] nenhuma senha, token ou credencial de usuário solicitada ou armazenada;
- [x] smoke test da configuração e proteção HTTP anônima concluídos.
- [x] teste E2E automatizado usando Apache e endpoints HTTP reais criado;
- [x] isolamento da API administrativa com HTTP 401 confirmado;
- [x] redirecionamento anônimo do dashboard com HTTP 302 confirmado;
- [x] produto Mercado Livre, campanha, link afiliado e oferta publicados pelo fluxo autenticado;
- [x] oferta e atribuição de campanha confirmadas na vitrine pública;
- [x] detalhe público e atributos seguros do link afiliado confirmados;
- [x] visualização e clique públicos registrados com HTTP 202;
- [x] venda aprovada registrada pelo fluxo administrativo;
- [x] dashboard confirmou 1 view, 1 clique, 1 venda, R$ 100 bruto e R$ 10 de comissão;
- [x] CTR e conversão de 100% confirmados no cenário controlado;
- [x] administrador, sessão e dados E2E removidos após o teste;
- [x] contagens residuais E2E verificadas como zero.
- [x] pasta local e ignorada `storage/backups` criada;
- [x] detecção das ferramentas MySQL/MariaDB do XAMPP implementada;
- [x] caminho alternativo `MYSQL_BIN_DIR` documentado;
- [x] backup transacional com `mysqldump` implementado por CLI;
- [x] credenciais fornecidas por arquivo temporário e removidas no `finally`;
- [x] dump gerado sem credenciais na linha de comando ou no manifesto;
- [x] manifesto com SHA-256 e contagens por tabela criado;
- [x] verificação aceita somente `.sql` dentro de `storage/backups`;
- [x] restauração isolada em banco aleatório `affiliate_verify_*` implementada;
- [x] nome do banco temporário validado antes de criar e remover;
- [x] 9 tabelas e respectivas contagens comparadas com o manifesto;
- [x] histórico de migrations confirmado na restauração;
- [x] banco temporário removido e ausência confirmada no `information_schema`;
- [x] banco original preservado e health check HTTP 200 confirmado;
- [x] procedimento de backup e recuperação documentado.
- [x] formato atual do App Store verificado no repositório oficial do Umbrel;
- [x] caminhos absolutos do Windows restritos a documentação e testes locais;
- [x] código de execução auditado sem dependência de XAMPP;
- [x] imagem-base PHP 8.2/Apache fixada por digest;
- [x] suporte amd64 e arm64 da imagem-base PHP confirmado no registry;
- [x] MariaDB 11.4 LTS fixado por digest no Compose;
- [x] suporte amd64 e arm64 da imagem MariaDB confirmado no registry;
- [x] extensão PDO MySQL e cliente MariaDB incluídos na imagem planejada;
- [x] configuração Apache restringindo acesso web a `public` e `admin` criada;
- [x] executor idempotente de migrations com lock de banco criado;
- [x] oito migrations verificadas sem duplicar o histórico;
- [x] bootstrap idempotente do administrador inicial criado;
- [x] segunda execução do bootstrap preserva a senha existente;
- [x] administrador temporário do teste removido;
- [x] pacote Umbrel `affiliate-offers` estruturado no formato oficial;
- [x] `app_proxy` configurado sem publicar porta bruta;
- [x] autenticação padrão do Umbrel mantida;
- [x] banco e `storage` mapeados sob `${APP_DATA_DIR}/data`;
- [x] segredos de usuário e root do banco derivados com rótulos separados;
- [x] primeiro login preparado com senha determinística do Umbrel;
- [x] OAuth Mercado Livre permanece desativado e sem credenciais na imagem;
- [x] Docker socket, host network e modo privilegiado não utilizados;
- [x] sintaxe shell do entrypoint validada;
- [x] teste estático do pacote concluído;
- [x] instalação, atualização, backup e rollback planejados e documentados;
- [x] marcador obrigatório impediu instalação antes da publicação da imagem.
- [x] distribuição WSL dedicada `DockerBuild` instalada em `F:\WSL\DockerBuild`;
- [x] Docker Engine 29.1.3, Buildx 0.30.1 e Compose 2.40.3 instalados no ambiente do F:;
- [x] execução real de imagem `linux/arm64` via QEMU/binfmt confirmada como `aarch64`;
- [x] builder multi-arquitetura confirmou suporte a `linux/amd64` e `linux/arm64`;
- [x] imagem própria construída para amd64 e arm64 em artefato OCI local;
- [x] lista OCI local gerada com digest `sha256:1572ba08fc01041f9fc07f0805f4e96d07c539a3fc2366a21b18669a060b097c`;
- [x] imagem AMD64 executada com MariaDB 11.4.8 fixado por digest;
- [x] migrations, bootstrap administrativo e health check HTTP 200 validados em containers reais;
- [x] login HTTP com cookie e CSRF, dashboard e fluxo E2E completo aprovados no container;
- [x] reinício do banco e da aplicação preservou administrador e volume de `storage`;
- [x] recursos temporários do teste removidos após a validação;
- [x] teste E2E passou a aceitar `E2E_BASE_URL`, mantendo XAMPP como padrão;
- [x] `.codex-tmp` excluído do contexto de imagens futuras;
- [x] GitHub CLI autenticado como `secretariadigitalbpc`;
- [x] repositório público `secretariadigitalbpc/affiliate-offers` criado;
- [x] workflow GitHub Actions criado para publicar amd64/arm64 no GHCR sem token persistente adicional;
- [x] proprietário, website, repositório e suporte preenchidos no pacote Umbrel.
- [x] primeiro commit publicado na branch `main` do repositório público;
- [x] workflow GitHub Actions executado com sucesso para `linux/amd64` e `linux/arm64`;
- [x] imagem pública `ghcr.io/secretariadigitalbpc/affiliate-offers:0.1.0` publicada;
- [x] digest remoto `sha256:39db3742ca8f013f5518deebd49b05505c646a0276d5460d138caaea25767f83` confirmado sem autenticação;
- [x] digest remoto aplicado ao `docker-compose.yml` do pacote Umbrel;
- [x] pacote copiado para uma cópia descartável do repositório oficial `getumbrel/umbrel-apps`;
- [x] linter oficial executado com `--check-images` e resultado `No issues found`;
- [x] `git diff --check` do pacote executado sem erros;
- [x] repositório preparado como Community App Store com o ID `secretaria-digital-bpc`;
- [x] variante comunitária `secretaria-digital-bpc-affiliate-offers` validada contra o pacote-fonte por teste automatizado.

## Removido da arquitetura local

- FastAPI;
- Python;
- Next.js;
- PostgreSQL;
- Docker como requisito de desenvolvimento local.

## Trabalho restante da etapa atual

Executar com a imagem pública já fixada:

```text
instalar pelo ciclo de vida do Umbrel
validar login, fluxo principal, reinício e persistência
testar atualização e rollback controlados
criar a submissão e substituir REPLACE_WITH_PR somente após aprovação
```

## Último teste

```text
Documentação oficial atual do Umbrel consultada = OK
PHP 8.2.29 Apache: amd64 + arm64 e digest confirmados = OK
MariaDB 11.4.8: amd64 + arm64 e digest confirmados = OK
Sintaxe PHP de migrate, ensure_admin e teste Umbrel = OK
8 migrations reaplicadas de forma idempotente = OK
schema_migrations permanece com 8 registros = OK
Bootstrap do administrador: criação = OK
Bootstrap repetido: senha preservada = OK
Administrador temporário removido = OK
Sintaxe de entrypoint.sh = OK
Estrutura, proxy, persistência e segredos do pacote = OK
Portas brutas, Docker socket, host network e privileged ausentes = OK
Caminhos absolutos do Windows no código de execução = 0
Health check local após auditoria = 200
Docker Engine 29.1.3 em WSL dedicado no F: = OK
Buildx 0.30.1 e Compose 2.40.3 = OK
Execução de container linux/arm64 via QEMU/binfmt = OK
Contexto real enviado ao builder = 276,09 kB
Build OCI linux/amd64 + linux/arm64 = OK
Digest da lista OCI local = sha256:1572ba08fc01041f9fc07f0805f4e96d07c539a3fc2366a21b18669a060b097c
Artefato OCI = F:\DockerBuild\affiliate-offers-0.1.0.oci.tar
SHA-256 do arquivo OCI = a1f7ac221d56131d7e2d682d88086c386354135a62bee3d9ad53837c8b77dec4
MariaDB fixado por digest e aplicação em containers reais = OK
Migrations e bootstrap no entrypoint = OK
Health check HTTP em container = 200
Login HTTP com CSRF e dashboard autenticado = OK
MVP E2E dentro do container = OK
Reinício e persistência de banco/storage = OK
GitHub autenticado como secretariadigitalbpc = OK
Repositório público criado = OK
Workflow de publicação GHCR = APROVADO
Imagem pública GHCR amd64 + arm64 = OK
Digest remoto fixado no Compose = sha256:39db3742ca8f013f5518deebd49b05505c646a0276d5460d138caaea25767f83
Linter oficial do App Store com --check-images = OK (No issues found)
Teste em umbrelOS = PENDENTE INTENCIONAL
```

## Modelo recomendado

**GPT-5.6 Sol High**

Motivo:

A continuação envolve o ciclo de vida real do Umbrel e rollback. High continua recomendado por envolver infraestrutura externa e risco aos dados persistentes.

## Arquivos alterados

```text
.dockerignore
.gitignore
.github/workflows/publish-image.yml
database/migrate.php
database/seeds/ensure_admin.php
deploy/container/Dockerfile
deploy/container/apache-affiliate.conf
deploy/container/entrypoint.sh
deploy/umbrel/README.md
deploy/umbrel/affiliate-offers/umbrel-app.yml
deploy/umbrel/affiliate-offers/docker-compose.yml
deploy/umbrel/affiliate-offers/exports.sh
deploy/umbrel/affiliate-offers/app.env.template
deploy/umbrel/affiliate-offers/data/mysql/.gitkeep
deploy/umbrel/affiliate-offers/data/storage/logs/.gitkeep
deploy/umbrel/affiliate-offers/data/storage/imports/.gitkeep
deploy/umbrel/affiliate-offers/data/storage/backups/.gitkeep
tests/umbrel_package_smoke.php
tests/mvp_e2e.php
ARCHITECTURE.md
DEPLOY_UMBREL.md
README.md
TESTING.md
CHANGELOG.md
PROJECT_STATUS.md
```

## Problemas pendentes

- nenhum problema bloqueante na preparação estática da ETAPA 16;
- o usuário ainda deve criar o primeiro administrador real pelo script documentado;
- o fluxo principal do MVP local foi validado ponta a ponta;
- backup e restauração isolada do banco foram validados;
- o backup validado permanece somente em `storage/backups`, fora do Git, e deve ser tratado como confidencial;
- Docker Desktop não foi instalado; o runtime validado é um Docker Engine dentro da distribuição WSL dedicada no F:;
- a imagem própria foi publicada para amd64 e arm64 e fixada pelo digest remoto;
- o único marcador restante é `REPLACE_WITH_PR`, que depende de uma submissão futura após os testes;
- o repositório público, o commit inicial e o workflow de publicação foram concluídos;
- `umbrel.local` resolve para `192.168.2.109` e respondeu HTTP 200, mas o pacote ainda não foi instalado nem validado pelo ciclo de vida do umbrelOS;
- a instalação pelo `app_proxy`, a atualização e o rollback reais permanecem pendentes;
- a documentação pública consultada não confirma endpoints do Programa de Afiliados para vendas, comissões ou links;
- callback, troca e armazenamento de tokens não foram implementados sem essa confirmação;
- a vinculação real dependerá de uma aplicação oficial, credenciais no `.env` e documentação/permissão compatível do Mercado Livre;
- a importação CSV e o cadastro manual de links continuam sendo os fluxos suportados;
- nenhuma senha da conta Mercado Livre deve ser fornecida ao sistema.

## Último checkpoint

```text
Ambiente alvo atual: XAMPP LOCAL
Stack: PHP + MySQL/MariaDB + HTML/CSS/JavaScript
Deploy alvo futuro: Umbrel
ETAPA 17 em andamento; build e runtime locais aprovados.
Docker Engine e dados de build ficam na distribuição WSL dedicada em F:\WSL\DockerBuild.
Imagem OCI amd64/arm64 construída localmente e publicada no GHCR.
Digest remoto público fixado: sha256:39db3742ca8f013f5518deebd49b05505c646a0276d5460d138caaea25767f83.
Containers temporários confirmaram health, login, fluxo E2E, reinício e persistência e foram removidos.
Linter oficial com verificação de imagens aprovado: No issues found.
Community App Store pública preparada no próprio repositório.
Variante de stack para Portainer implantada após solicitação do usuário; o primeiro diagnóstico detectou a exigência indevida de `.env` no contêiner.
Correção aplicada para aceitar as variáveis fornecidas diretamente pelo Portainer, com teste de regressão dedicado.
Próxima ação: publicar a imagem corrigida e repetir a validação do runtime; o ciclo nativo do Umbrel continuará pendente.
```

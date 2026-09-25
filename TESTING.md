# Estratégia de Testes

## Objetivo

Validar o necessário sem criar testes excessivos.

## 1. Smoke test do XAMPP

Confirmar:

```text
Apache iniciado
MySQL iniciado
http://localhost/afiliados abre
PHP executa
banco conecta
```

## 2. Health check

```text
GET /afiliados/public/api/health.php
```

Esperado:

```json
{"status":"ok"}
```

## 3. Banco

Testar:

- conexão PDO;
- INSERT;
- SELECT;
- UPDATE;
- constraints principais;
- caracteres acentuados;
- DECIMAL para preços.

## 4. CRUD

Testar:

```text
produto
link afiliado
oferta
campanha
```

Teste implementado para produtos:

```text
php tests/products_smoke.php
```

O teste abre uma transação, cria um produto com caracteres acentuados, consulta, edita, valida entradas inválidas e executa rollback ao terminar.

## Autenticação administrativa

Teste implementado:

```text
php tests/auth_smoke.php
```

O teste confirma `password_hash()`, `password_verify()`, rejeição de senha incorreta, criação da sessão, identificação do administrador e logout. O teste HTTP complementar valida redirecionamento, HTTP 401, regeneração da sessão e proteção pós-logout.

Teste implementado para links de afiliado:

```text
php tests/affiliate_links_smoke.php
```

O teste cria produto e link dentro de uma transação, consulta, lista, edita, valida URL e marketplace, confirma a chave estrangeira e executa rollback ao terminar.

Teste implementado para ofertas:

```text
php tests/offers_smoke.php
```

O teste cria produto, link e oferta dentro de uma transação, confirma normalização monetária, cálculo de desconto, edição, status, período e coerência entre relacionamentos, executando rollback ao terminar.

Teste implementado para a vitrine pública:

```text
php tests/public_offers_smoke.php
```

O teste confirma que somente ofertas publicadas, ativas e vigentes aparecem, enquanto rascunhos, ofertas futuras e expiradas permanecem ocultas. O teste HTTP complementar valida escaping, detalhes, respostas 404 e atributos seguros do link oficial.

Teste implementado para campanhas:

```text
php tests/campaigns_smoke.php
```

O teste cria, consulta e edita campanhas dentro de uma transação, valida geração de slug com acentos, normalização de origem/mídia, dados obrigatórios e unicidade do slug, executando rollback ao terminar.

## 5. Analytics

Teste implementado:

```text
php tests/analytics_smoke.php
```

O teste cria produto, link, oferta publicada e campanha dentro de uma transação, registra uma visualização e um clique, confirma os relacionamentos derivados no servidor, a atribuição e a família do navegador, valida entrada inválida e executa rollback ao terminar.

O teste HTTP complementar confirma respostas 202, rejeição de GET com 405, oferta ausente com 404, propagação da atribuição na vitrine, ativação do JavaScript na página de detalhes e ausência de dados temporários ao final.

Teste implementado para o dashboard:

```text
php tests/analytics_summary_smoke.php
```

O teste insere 10 visualizações e 2 cliques dentro de uma transação e confirma CTR de 20%. Ao filtrar a campanha, confirma 6 visualizações, 2 cliques e CTR de 33,33%, além de validar o período e executar rollback.

O teste HTTP complementar confirma proteção anônima com HTTP 401/302, acesso autenticado ao endpoint e à página, resposta HTTP 422 para período invertido e carregamento da interface administrativa.

Teste controlado:

1. abrir página de um produto;
2. confirmar `page_views`;
3. clicar no botão;
4. confirmar `clicks`;
5. confirmar abertura do link correto.

## 6. Venda

Teste implementado:

```text
php tests/sales_smoke.php
```

O teste cria produto, campanha e vendas dentro de uma transação, valida normalização monetária, quantidade, status, relacionamentos opcionais, compatibilidade de marketplace, comissão máxima e referência externa única, executando rollback ao terminar.

O teste HTTP complementar confirma proteção anônima, tela autenticada, criação HTTP 201, listagem, duplicidade 409, dados inválidos 422 e CSRF inválido 403.

Teste implementado para importação CSV:

```text
php tests/sales_import_smoke.php
```

O teste importa duas linhas válidas, ignora uma duplicada, relata uma inválida e confirma `imported_at`. O reenvio não cria duplicatas, e um arquivo sem cabeçalhos obrigatórios é rejeitado antes da persistência.

O teste HTTP complementar envia o CSV de exemplo como multipart, confirma idempotência no segundo envio, proteção anônima, arquivo ausente e CSRF inválido.

## Integração Mercado Livre

Teste implementado:

```text
php tests/mercado_livre_integration_smoke.php
```

O teste confirma configuração ausente por padrão, configuração válida quando explicitamente habilitada, ausência do Client Secret no status público e geração da URL OAuth com `state` e PKCE S256. O teste HTTP complementar confirma que o status da integração rejeita acesso anônimo.

Criar dados conhecidos:

```text
100 views
20 clicks
4 vendas
R$ 40 de comissão
```

Esperado:

```text
CTR = 20%
Conversão click → venda = 20%
Comissão = R$ 40
```

Teste implementado para essas métricas:

```text
php tests/dashboard_sales_smoke.php
```

O teste reproduz exatamente 100 visualizações, 20 cliques, 4 vendas aprovadas e R$ 40 de comissão. Confirma CTR e conversão de 20%, valor bruto de R$ 400, filtro por campanha e exclusão de uma venda cancelada, executando rollback ao terminar.

## 7. E2E

```text
cadastrar produto
→ link afiliado
→ criar oferta
→ abrir página pública
→ registrar view
→ clicar
→ registrar click
→ cadastrar/importar venda
→ visualizar dashboard
```

Teste automatizado implementado:

```text
php tests/mvp_e2e.php
```

O teste usa Apache e o banco local pelos endpoints reais. Ele cria um administrador temporário, confirma o bloqueio de acessos anônimos, cadastra produto, campanha, link afiliado e oferta, valida vitrine e detalhes públicos, registra uma visualização e um clique, registra uma venda aprovada e confirma no dashboard: 1 view, 1 clique, CTR de 100%, 1 venda, conversão de 100%, R$ 100 de valor bruto e R$ 10 de comissão.

Todos os registros e a sessão temporária são removidos no bloco de limpeza. Ao final, as contagens de administradores, produtos, campanhas e vendas identificados como E2E devem ser zero.

## 8. Backup e restauração

Comandos testados:

```text
php database/backup.php
php database/verify_backup.php storage/backups/arquivo.sql
```

O primeiro comando gera dump, manifesto, SHA-256 e contagens. O segundo verifica o checksum, restaura em banco `affiliate_verify_*`, compara as 9 tabelas e contagens, confirma `schema_migrations` e remove o banco temporário. Depois do teste, o banco original deve continuar respondendo no health check e nenhuma base `affiliate_verify_*` pode permanecer.

Também foi confirmado que um arquivo fora de `storage/backups` é recusado, nenhum arquivo temporário de credenciais permanece e o dump não contém parâmetros de conexão.

## 9. Preparação Umbrel

Teste estático implementado:

```text
php tests/umbrel_package_smoke.php
```

O teste verifica estrutura do pacote, imagem-base PHP fixada, PDO MySQL, `app_proxy`, ausência de portas brutas e privilégios, persistência do banco e de `storage`, manifesto, senha determinística, segredos derivados separadamente, produção sem debug e ausência de caminhos absolutos do Windows no código de execução.

Também foram executados:

```text
php database/migrate.php
sh -n deploy/container/entrypoint.sh
```

As oito migrations foram verificadas de forma idempotente. O bootstrap administrativo foi executado duas vezes: na segunda execução preservou a senha existente, e o administrador temporário foi removido.

### Runtime de container da ETAPA 17

Foi criada uma distribuição WSL dedicada em `F:\WSL\DockerBuild`, sem mover ou alterar o Ubuntu já existente. Nela foram validados Docker Engine 29.1.3, Buildx 0.30.1, Compose 2.40.3 e QEMU/binfmt.

Testes concluídos:

```text
container oficial linux/arm64 executado e retornou aarch64 = OK
builder reconheceu linux/amd64 e linux/arm64 = OK
build OCI multi-arquitetura da aplicação = OK
MariaDB 11.4.8 fixado por digest = healthy
entrypoint aplicou/verificou 8 migrations = OK
bootstrap criou o administrador inicial = OK
GET /public/api/health.php = HTTP 200
login HTTP com cookie e CSRF = OK
dashboard autenticado = HTTP 200
MVP E2E dentro do container = OK
reinício preservou administrador e arquivo no volume storage = OK
```

O teste E2E aceita `E2E_BASE_URL`; quando ausente, continua usando `http://localhost/whatsappAF` para preservar o fluxo XAMPP.

Artefato local gerado:

```text
F:\DockerBuild\affiliate-offers-0.1.0.oci.tar
manifest list: sha256:1572ba08fc01041f9fc07f0805f4e96d07c539a3fc2366a21b18669a060b097c
plataformas: linux/amd64 e linux/arm64
```

A publicação no registry, o linter oficial e o ciclo de vida real do Umbrel continuam pendentes. Os containers, rede, volumes e credenciais temporários do teste foram removidos.

## Não testar desnecessariamente

Evitar:

- testes repetidos da mesma regra;
- E2E para detalhes visuais simples;
- suíte completa após mudança mínima;
- integrações reais quando mock/dados de exemplo bastam.

## Critério de conclusão

Uma etapa está concluída somente quando:

```text
implementada
+ testada
+ documentação atualizada
```

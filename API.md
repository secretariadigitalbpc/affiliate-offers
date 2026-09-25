# Endpoints Internos

Mesmo usando PHP tradicional, manter endpoints organizados para JavaScript e futuras integrações.

Prefixo sugerido:

```text
/api/
```

## Health

```http
GET /api/health.php
```

Resposta:

```json
{
  "status": "ok"
}
```

Implementado em:

```text
public/api/health.php
```

O endpoint valida a conexão PDO com `SELECT 1`, retorna HTTP 200 quando o banco está disponível e HTTP 503 com uma resposta genérica quando a verificação falha.

## Products

```http
GET  /api/products/list.php
GET  /api/products/get.php?id=123
POST /api/products/create.php
POST /api/products/update.php
```

Implementados em `public/api/products/`.

Contratos atuais:

- `list.php`: retorna a lista em `data.products`;
- `get.php?id=123`: retorna o produto em `data.product`;
- `create.php`: exige `POST` e token CSRF, retornando HTTP 201;
- `update.php`: exige `POST`, `id` e token CSRF;
- dados inválidos retornam HTTP 422;
- produto inexistente retorna HTTP 404;
- duplicidade de marketplace + ID externo retorna HTTP 409;
- token CSRF inválido retorna HTTP 403.

Todos os endpoints de produtos agora exigem uma sessão administrativa válida. Requisições anônimas retornam HTTP 401 com o código `AUTH_REQUIRED`.

## Autenticação administrativa

Páginas:

```http
GET|POST /admin/login.php
POST     /admin/logout.php
```

- login usa `password_verify()` e resposta genérica para credenciais inválidas;
- a sessão é regenerada após autenticação;
- logout exige token CSRF e destrói a sessão;
- páginas administrativas redirecionam usuários anônimos para o login;
- endpoints administrativos retornam HTTP 401 para usuários anônimos.

## Offers

```http
GET  /api/offers/list.php
POST /api/offers/create.php
POST /api/offers/update.php
```

Implementados em `public/api/offers/`.

- todos exigem uma sessão administrativa válida;
- criação e edição exigem token CSRF;
- `list.php` retorna as ofertas em `data.offers`;
- `create.php` retorna HTTP 201;
- preços aceitam ponto ou vírgula e são normalizados para duas casas;
- percentual de desconto é calculado no servidor;
- status aceitos: `draft`, `approved`, `published`, `expired`, `rejected`;
- expiração deve ser posterior ao início;
- o link de afiliado deve pertencer ao produto selecionado;
- dados inválidos retornam HTTP 422;
- oferta inexistente na edição retorna HTTP 404;
- acesso anônimo retorna HTTP 401.

## Affiliate links

```http
GET  /api/affiliate-links/list.php
POST /api/affiliate-links/create.php
POST /api/affiliate-links/update.php
```

Implementados em `public/api/affiliate-links/`.

- todos exigem uma sessão administrativa válida;
- criação e edição exigem token CSRF;
- `list.php` retorna os links em `data.affiliate_links`;
- `create.php` retorna HTTP 201;
- URL deve usar HTTP ou HTTPS;
- marketplace deve coincidir com o marketplace do produto;
- dados inválidos retornam HTTP 422;
- link inexistente na edição retorna HTTP 404;
- acesso anônimo retorna HTTP 401.

## Analytics

```http
POST /api/events/view.php
POST /api/events/click.php
GET  /api/analytics/summary.php
```

Os endpoints `view.php` e `click.php` estão implementados em `public/api/events/`.

- são públicos, aceitam somente `POST` e retornam HTTP 202;
- exigem `offer_id` de uma oferta publicada, ativa e vigente;
- aceitam opcionalmente `campaign`, `source` e `session_id`;
- campanha informada deve existir e estar ativa;
- produto e link de afiliado são derivados no servidor, não aceitos do cliente;
- `view.php` armazena somente a família genérica do navegador;
- não armazenam IP nem o user-agent completo;
- dados inválidos retornam HTTP 422;
- oferta indisponível retorna HTTP 404;
- outros métodos retornam HTTP 405;
- falhas do rastreamento no navegador não bloqueiam o acesso ao link oficial.

O endpoint administrativo `summary.php` está implementado em `public/api/analytics/`.

- exige sessão administrativa e aceita somente `GET`;
- usa por padrão os últimos 30 dias, incluindo o dia atual;
- aceita `from` e `to` no formato `YYYY-MM-DD`;
- aceita `campaign_id` opcional, inclusive para campanhas inativas com histórico;
- retorna totais de visualizações, cliques e CTR;
- retorna vendas aprovadas, valor bruto, comissão e conversão clique → venda;
- exclui vendas pendentes, canceladas e reembolsadas das métricas financeiras;
- retorna desempenho agregado das 20 principais ofertas;
- período ou campanha inválidos retornam HTTP 422;
- acesso anônimo retorna HTTP 401.

## Campaigns

```http
GET  /api/campaigns/list.php
POST /api/campaigns/create.php
POST /api/campaigns/update.php
```

Implementados em `public/api/campaigns/`.

- todos exigem sessão administrativa;
- criação e edição exigem token CSRF;
- slug vazio é gerado a partir do nome;
- slug duplicado retorna HTTP 409;
- dados inválidos retornam HTTP 422;
- campanha inexistente na edição retorna HTTP 404;
- acesso anônimo retorna HTTP 401.

## Sales

```http
GET  /api/sales/list.php
POST /api/sales/manual.php
POST /api/sales/import.php
```

Os endpoints `list.php` e `manual.php` estão implementados em `public/api/sales/`.

- ambos exigem sessão administrativa;
- `manual.php` aceita somente `POST`, exige CSRF e retorna HTTP 201;
- `list.php` aceita somente `GET` e retorna as vendas em `data.sales`;
- marketplaces aceitos: `mercado_livre` e `shopee`;
- status aceitos: `pending`, `approved`, `cancelled` e `refunded`;
- valores são normalizados para duas casas decimais;
- comissão não pode superar o valor bruto;
- produto e campanha são opcionais;
- produto vinculado deve pertencer ao marketplace da venda;
- referência externa duplicada no mesmo marketplace retorna HTTP 409;
- dados inválidos retornam HTTP 422;
- acesso anônimo retorna HTTP 401.

O endpoint `import.php` está implementado em `public/api/sales/`.

- aceita somente `POST multipart/form-data`;
- exige sessão administrativa e token CSRF;
- recebe o arquivo no campo `sales_file`;
- aceita somente extensão `.csv`, até 2 MB e 5.000 linhas;
- valida UTF-8, cabeçalhos, valores e relacionamentos antes de persistir;
- importa as linhas válidas em uma transação;
- ignora referências já existentes ou repetidas no arquivo;
- retorna contagens em `data.import`: `total_rows`, `imported`, `ignored` e `invalid`;
- retorna detalhes das linhas inválidas em `data.import.errors`;
- arquivo inválido retorna HTTP 422.

O formato completo está documentado em `CSV_IMPORT.md`.

## Integrações

```http
GET /api/integrations/mercado-livre-status.php
```

O endpoint exige sessão administrativa e retorna somente o estado público da configuração Mercado Livre:

- informa se Client ID e Client Secret foram configurados, sem retornar seus valores;
- informa a redirect URI e campos ausentes;
- expõe somente os endpoints OAuth oficiais e públicos;
- mantém a sincronização automática desativada enquanto a API de afiliados não estiver confirmada;
- indica a importação CSV como alternativa disponível;
- acesso anônimo retorna HTTP 401.

As limitações e fontes oficiais estão em `MERCADO_LIVRE_INTEGRATION.md`.

## JSON

Quando endpoint for consumido por JavaScript:

```php
header('Content-Type: application/json; charset=utf-8');
```

Formato de erro:

```json
{
  "success": false,
  "error": {
    "code": "PRODUCT_NOT_FOUND",
    "message": "Produto não encontrado."
  }
}
```

Formato de sucesso:

```json
{
  "success": true,
  "data": {}
}
```

## Segurança

- validar método HTTP;
- validar entrada no servidor;
- usar PDO prepared statements;
- restringir endpoints administrativos;
- usar CSRF em operações autenticadas quando apropriado;
- nunca retornar credenciais ou stack traces ao usuário.

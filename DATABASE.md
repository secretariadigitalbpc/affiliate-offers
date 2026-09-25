# Banco de Dados — MySQL/MariaDB

## Banco sugerido

```text
affiliate_system
```

Charset recomendado:

```text
utf8mb4
```

Collation:

```text
utf8mb4_unicode_ci
```

## Tabelas

### products

```text
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
marketplace VARCHAR(30)
marketplace_product_id VARCHAR(100)
title VARCHAR(255)
slug VARCHAR(255)
category_id BIGINT UNSIGNED NULL
image_url TEXT NULL
seller_name VARCHAR(255) NULL
rating DECIMAL(3,2) NULL
sales_count INT UNSIGNED NULL
active TINYINT(1) DEFAULT 1
created_at DATETIME
updated_at DATETIME
last_checked_at DATETIME NULL
```

Índice único:

```text
UNIQUE (marketplace, marketplace_product_id)
```

### categories

```text
id
name
slug
active
created_at
```

### price_history

```text
id
product_id
price DECIMAL(12,2)
old_price DECIMAL(12,2) NULL
captured_at DATETIME
```

### affiliate_links

```text
id
product_id
marketplace
affiliate_url TEXT
campaign_id NULL
tag VARCHAR(100) NULL
active
created_at
updated_at
```

### offers

```text
id
product_id
affiliate_link_id
price
old_price
discount_percentage
coupon_text
shipping_text
status
starts_at
expires_at
created_at
updated_at
```

Status:

```text
draft
approved
published
expired
rejected
```

### campaigns

```text
id
name
slug
source
medium
active
created_at
```

### page_views

```text
id
product_id
offer_id NULL
campaign_id NULL
source VARCHAR(100) NULL
session_id VARCHAR(128) NULL
user_agent_family VARCHAR(100) NULL
created_at
```

### clicks

```text
id
product_id
offer_id NULL
affiliate_link_id
campaign_id NULL
source VARCHAR(100) NULL
session_id VARCHAR(128) NULL
created_at
```

### sales

```text
id
marketplace
external_sale_reference VARCHAR(255) NULL
product_id NULL
campaign_id NULL
quantity
gross_value DECIMAL(12,2)
commission_value DECIMAL(12,2)
status VARCHAR(20)
sale_date DATETIME
imported_at DATETIME NULL
created_at DATETIME
updated_at DATETIME
```

Status:

```text
pending
approved
cancelled
refunded
```

### publications

```text
id
offer_id
channel
channel_reference NULL
status
scheduled_at NULL
published_at NULL
created_at
```

### sync_logs

```text
id
integration
operation
status
message
started_at
finished_at
```

### system_settings

```text
id
setting_key
value_json
updated_at
```

## Regras

- usar InnoDB;
- usar foreign keys onde apropriado;
- valores monetários sempre `DECIMAL`, nunca FLOAT;
- operações sensíveis devem usar transaction;
- importação de vendas deve ser idempotente;
- não armazenar dados pessoais desnecessários.

## Migrações

Como o projeto é PHP/XAMPP simples, manter scripts SQL numerados:

```text
database/migrations/
001_initial_schema.sql
002_add_campaigns.sql
003_add_sales_index.sql
```

Cada migration deve ser executada uma única vez e registrada numa tabela `schema_migrations`.

Evitar editar migration já aplicada. Criar uma nova.

## Estado implementado

A migration `001_initial_schema.sql` foi aplicada e contém:

- criação do banco `affiliate_system` com `utf8mb4_unicode_ci`;
- tabela `schema_migrations` usando InnoDB;
- registro da versão `001_initial_schema`.

As tabelas de domínio descritas acima serão criadas gradualmente nas etapas correspondentes, sem antecipar todo o MVP.

A migration `002_create_products.sql` também foi aplicada e contém:

- tabela `products` usando InnoDB e `utf8mb4_unicode_ci`;
- colunas previstas para identificação, apresentação e estado do produto;
- valores de avaliação em `DECIMAL(3,2)`;
- índice único em `(marketplace, marketplace_product_id)`;
- índices auxiliares para `active` e `title`;
- registro da versão `002_create_products`.

A migration `003_create_administrators.sql` foi aplicada e contém:

- tabela `administrators` usando InnoDB e `utf8mb4_unicode_ci`;
- e-mail único por administrador;
- armazenamento exclusivo do hash da senha em `password_hash`;
- estado ativo e data do último login;
- registro da versão `003_create_administrators`.

A migration `004_create_affiliate_links.sql` foi aplicada e contém:

- tabela `affiliate_links` usando InnoDB e `utf8mb4_unicode_ci`;
- chave estrangeira obrigatória para `products` com exclusão restrita;
- URL oficial do afiliado, marketplace, tag e estado ativo;
- `campaign_id` opcional, reservado para a etapa de campanhas;
- índices por produto e por marketplace/estado;
- registro da versão `004_create_affiliate_links`.

A migration `005_create_offers.sql` foi aplicada e contém:

- tabela `offers` usando InnoDB e `utf8mb4_unicode_ci`;
- chaves estrangeiras para produto e link de afiliado;
- preços atual e anterior em `DECIMAL(12,2)`;
- percentual de desconto em `DECIMAL(5,2)`;
- status controlados por constraint;
- datas opcionais de início e expiração;
- constraints para preço positivo e preço anterior coerente;
- registro da versão `005_create_offers`.

A migration `006_create_campaigns.sql` foi aplicada e contém:

- tabela `campaigns` usando InnoDB e `utf8mb4_unicode_ci`;
- nome, slug único, origem, mídia e estado ativo;
- índice por origem/estado;
- registro da versão `006_create_campaigns`.

A migration `007_create_analytics_events.sql` foi aplicada e contém:

- tabelas `page_views` e `clicks` usando InnoDB e `utf8mb4_unicode_ci`;
- chaves estrangeiras para produto, oferta, link de afiliado e campanha;
- índices por entidade e data de criação para as futuras consultas agregadas;
- atribuição opcional por campanha, origem e sessão anônima;
- família genérica do navegador somente nas visualizações;
- nenhum campo de IP ou user-agent completo;
- registro da versão `007_create_analytics_events`.

A migration `008_create_sales.sql` foi aplicada e contém:

- tabela `sales` usando InnoDB e `utf8mb4_unicode_ci`;
- marketplace, referência externa opcional e data da venda;
- quantidade, valor bruto e comissão em tipos numéricos apropriados;
- vínculos opcionais com produto e campanha, com exclusão restrita;
- status `pending`, `approved`, `cancelled` e `refunded`;
- unicidade de referência externa por marketplace para evitar duplicação;
- `imported_at` reservado para futuras importações, permanecendo nulo no cadastro manual;
- registro da versão `008_create_sales`.

## Backup e restauração

O backup local usa `mysqldump` transacional, gera SHA-256 e manifesto com contagens por tabela. A verificação restaura somente em um banco temporário com prefixo controlado, compara o conteúdo e remove esse banco no final.

Comandos e regras de segurança estão em `BACKUP_RESTORE.md`. O banco `affiliate_system` nunca é usado como destino da verificação.

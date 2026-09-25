# Importação de Vendas por CSV

## Formato

O arquivo deve estar em UTF-8, ter extensão `.csv`, no máximo 2 MB e até 5.000 linhas de dados. Vírgula e ponto e vírgula são aceitos como delimitadores; para valores brasileiros com vírgula decimal, prefira ponto e vírgula.

Cabeçalhos obrigatórios:

```text
marketplace
external_sale_reference
product_id
campaign_id
quantity
gross_value
commission_value
status
sale_date
```

Exemplo:

```csv
marketplace;external_sale_reference;product_id;campaign_id;quantity;gross_value;commission_value;status;sale_date
mercado_livre;ML-001;;;1;199,90;24,50;approved;2026-09-25 10:30:00
```

Um arquivo pronto para preenchimento está em `public/assets/examples/sales-import-example.csv`.

## Regras

- `marketplace`: `mercado_livre` ou `shopee`;
- `external_sale_reference`: obrigatória na importação e preservada como texto;
- `product_id`: opcional; quando informado, deve existir e pertencer ao marketplace;
- `campaign_id`: opcional; quando informado, deve existir;
- `quantity`: inteiro positivo;
- `gross_value`: valor positivo com até duas casas decimais;
- `commission_value`: valor não negativo e não superior ao bruto;
- `status`: `pending`, `approved`, `cancelled` ou `refunded`;
- `sale_date`: `YYYY-MM-DD HH:MM:SS` ou `YYYY-MM-DDTHH:MM`.

## Comportamento

Todas as linhas são validadas antes da persistência. Linhas válidas são gravadas em uma única transação; uma falha de banco desfaz todo o lote válido. Linhas semanticamente inválidas são relatadas e não são gravadas.

A combinação de marketplace e referência externa garante idempotência. Registros já existentes e duplicatas dentro do mesmo arquivo são ignorados sem criar uma segunda venda.

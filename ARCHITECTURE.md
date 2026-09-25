# Arquitetura do Sistema

## Ambiente local

```text
Navegador
    │
    ↓
Apache / XAMPP
    │
    ├── PHP
    │
    ├── HTML/CSS/JavaScript
    │
    ↓
MySQL/MariaDB
```

## Responsabilidades

### PHP

Responsável por:

- autenticação;
- CRUD;
- validação server-side;
- regras de negócio;
- acesso ao banco;
- endpoints internos;
- integrações externas;
- importação de relatórios;
- analytics.

### HTML/CSS

Responsáveis pela interface pública e administrativa.

### JavaScript

Usado para:

- interações da interface;
- Fetch API/AJAX;
- registro rápido de eventos;
- gráficos;
- filtros;
- melhorias de experiência.

O sistema não deve depender de JavaScript para regras críticas de negócio.

### MySQL/MariaDB

Fonte principal de verdade.

## Organização lógica

```text
Interface
   ↓
Controller
   ↓
Service
   ↓
Repository
   ↓
PDO
   ↓
MySQL/MariaDB
```

Evitar SQL espalhado em arquivos de interface.

## Marketplaces

Isolar integrações:

```text
app/Integrations/
├── MercadoLivre/
└── Shopee/
```

Uma alteração na Shopee não deve quebrar Mercado Livre.

A preparação atual do Mercado Livre fica isolada em `app/Integrations/MercadoLivre/`. Ela conhece a configuração e os endpoints OAuth oficiais, mas não executa autorização nem sincronização: a documentação pública ainda não confirma recursos do Programa de Afiliados. O CSV permanece como adaptador de entrada de vendas e comissões.

## Analytics

Fluxo:

```text
usuário abre produto
     ↓
registra page_view
     ↓
usuário clica
     ↓
JavaScript envia evento
     ↓
abre link oficial de afiliado
```

Evitar tornar um redirecionador próprio requisito do sistema.

Na implementação atual, o navegador envia apenas a oferta e a atribuição opcional. O servidor resolve produto e link de afiliado a partir de uma oferta pública válida. O JavaScript usa `fetch`/`sendBeacon` sem impedir a navegação, e a persistência não inclui IP nem user-agent completo.

O dashboard considera como venda somente cada registro com status `approved`. A conversão é calculada por `vendas aprovadas ÷ cliques`, e os valores bruto e de comissão também ignoram vendas pendentes, canceladas ou reembolsadas.

## Futuro Umbrel

A migração para Umbrel deverá preservar:

- PHP;
- MySQL/MariaDB quando viável;
- estrutura de arquivos;
- banco;
- rotas;
- regras.

A fase Umbrel não deve virar uma reescrita.

A preparação atual usa dois serviços internos: Apache/PHP e MariaDB. O `app_proxy` do Umbrel é a única entrada web; banco e armazenamento permanecem em `${APP_DATA_DIR}`. O mesmo código PHP e as mesmas migrations são usados no XAMPP e no container, sem caminhos absolutos de Windows no runtime.

# Sistema de Ofertas de Afiliados

Projeto para gerenciar ofertas de produtos de afiliados do Mercado Livre e Shopee, publicar páginas públicas de ofertas, medir acessos e cliques, registrar vendas/comissões quando os marketplaces fornecerem dados compatíveis e, futuramente, integrar canais como WhatsApp.

## Estratégia de desenvolvimento

O sistema será desenvolvido e validado **primeiro na máquina local usando XAMPP**.

Ambiente inicial:

- Apache
- PHP
- MySQL/MariaDB
- HTML5
- CSS3
- JavaScript
- Bootstrap opcional para interface
- Composer quando alguma biblioteca PHP justificar seu uso

Somente depois do MVP local estar funcionando será preparada a instalação no Umbrel.

## Estado atual

O MVP local foi validado ponta a ponta no Apache/XAMPP: cadastro administrativo, publicação da oferta, vitrine, analytics, venda e comissão no dashboard. O backup foi restaurado em banco isolado, e a estrutura de empacotamento para Umbrel foi preparada. A imagem `linux/amd64` + `linux/arm64` também foi construída e testada localmente com Docker Engine em uma distribuição WSL dedicada no F:. Ainda faltam publicar a imagem no registry, fixar o digest remoto, executar o linter oficial e validar o ciclo de vida no umbrelOS.

## Fluxo

```text
Desenvolvimento local com XAMPP
        ↓
Testes locais
        ↓
MVP funcional
        ↓
Teste ponta a ponta
        ↓
Backup/documentação
        ↓
Preparação para Umbrel
        ↓
Build e runtime de containers
        ↓
Publicação e validação no umbrelOS
```

## Objetivo do MVP

O MVP deve permitir:

1. cadastrar produtos;
2. cadastrar links de afiliado;
3. cadastrar ofertas;
4. exibir página pública de produto/oferta;
5. registrar visualizações;
6. registrar cliques;
7. identificar origem/campanha;
8. registrar/importar vendas e comissões;
9. visualizar métricas em painel administrativo;
10. evitar duplicação básica de ofertas.

## Fora do MVP inicial

Não implementar cedo:

- IA para selecionar ofertas;
- scraping agressivo;
- automação não oficial do WhatsApp;
- Redis;
- filas complexas;
- microserviços;
- frameworks pesados sem necessidade;
- arquitetura específica de Umbrel.

## Regra para agentes de IA

Antes de programar, consultar:

- `AI_INSTRUCTIONS.md`
- `ARCHITECTURE.md`
- `DATABASE.md`
- `API.md`
- `TESTING.md`
- `LOCAL_DEVELOPMENT.md`
- `PROJECT_STATUS.md`

Não alterar o stack principal sem autorização explícita.

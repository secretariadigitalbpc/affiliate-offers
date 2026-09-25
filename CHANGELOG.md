# Changelog

## Unreleased

### Changed

- ambiente local alterado para XAMPP;
- backend alterado de FastAPI/Python para PHP;
- frontend alterado de Next.js para HTML/CSS/JavaScript;
- banco alterado de PostgreSQL para MySQL/MariaDB;
- Docker removido como requisito do desenvolvimento local;
- Umbrel mantido apenas como destino de deploy futuro;
- PDO definido como camada padrão de acesso ao banco.

### Added

- diretrizes de segurança PHP;
- estrutura de desenvolvimento XAMPP;
- estratégia de migrations SQL simples;
- orientação para evitar caminhos absolutos do Windows.
- estrutura inicial da aplicação PHP;
- carregamento centralizado de variáveis do arquivo `.env`;
- conexão MySQL/MariaDB por PDO com exceções e prepared statements nativos;
- migration `001_initial_schema.sql` com banco `affiliate_system` e controle de versões;
- endpoint de saúde em `public/api/health.php`;
- página inicial e estilos mínimos para o ambiente local.
- migration da tabela `products` com unicidade por marketplace e ID externo;
- model, repository, validator e controller de produtos;
- endpoints de criação, consulta, listagem e edição de produtos;
- interface administrativa mínima para produtos;
- sessão segura e proteção CSRF nas operações de escrita;
- teste transacional específico do fluxo de produtos.
- tabela de administradores com e-mail único e hash de senha;
- script CLI seguro para criação do primeiro administrador;
- login e logout administrativos;
- regeneração e destruição segura da sessão;
- proteção autenticada da tela e dos endpoints de produtos;
- teste específico do fluxo completo de autenticação.
- tabela `affiliate_links` relacionada a produtos;
- validação de URLs HTTP/HTTPS e compatibilidade de marketplace;
- model, repository, validator e controller de links de afiliado;
- endpoints autenticados para criar, listar e editar links;
- interface administrativa de links de afiliado;
- teste transacional específico de links e integridade referencial.
- tabela `offers` relacionada a produtos e links de afiliado;
- preços monetários em `DECIMAL(12,2)`;
- cálculo server-side do percentual de desconto;
- validação de status, período e coerência dos relacionamentos;
- endpoints autenticados para criar, listar e editar ofertas;
- interface administrativa de ofertas;
- teste transacional específico do fluxo de ofertas.
- consulta pública isolada para ofertas disponíveis;
- vitrine responsiva de ofertas;
- página pública de detalhes de produto/oferta;
- filtragem de rascunhos, ofertas futuras e expiradas;
- escaping de conteúdo dinâmico e resposta 404 para ofertas indisponíveis;
- acesso direto ao link oficial com `rel="sponsored noopener noreferrer"`.
- tabela `campaigns` com slug único, origem, mídia e estado;
- model, repository, validator e controller de campanhas;
- endpoints autenticados e interface administrativa de campanhas;
- helper compartilhado para slugs com normalização consistente de acentos no Windows.
- tabelas `page_views` e `clicks` com índices e integridade referencial;
- endpoints públicos para registro de visualizações e cliques;
- serviço de analytics que deriva produto e link afiliado da oferta publicada;
- atribuição opcional por campanha ativa, origem e sessão anônima;
- detecção e armazenamento somente da família genérica do navegador;
- rastreamento JavaScript não bloqueante na página pública de detalhes;
- propagação segura de campanha e origem entre a vitrine e os detalhes;
- teste transacional e teste HTTP controlado para eventos públicos.
- repositório de consultas agregadas de visualizações e cliques;
- cálculo de CTR geral e por oferta;
- filtros de analytics por período inclusivo e campanha;
- endpoint administrativo protegido para resumo de analytics;
- dashboard responsivo com indicadores e desempenho por oferta;
- validação server-side dos filtros do dashboard;
- teste controlado dos totais, CTR e filtros.
- tabela `sales` com valores monetários, status e relacionamentos opcionais;
- unicidade de referência externa por marketplace;
- model, repository, validator e controller de vendas;
- endpoints administrativos protegidos para cadastro manual e listagem;
- tela administrativa de vendas e comissões;
- validação de marketplace, quantidade, valores, status e data da venda;
- teste transacional e fluxo HTTP autenticado de vendas.
- totais de vendas aprovadas, valor bruto e comissão no resumo de analytics;
- cálculo de conversão entre cliques e vendas aprovadas;
- aplicação dos filtros de período e campanha às métricas financeiras;
- indicadores financeiros responsivos no dashboard;
- teste com 100 visualizações, 20 cliques, 4 vendas e R$ 40 de comissão.
- importação administrativa de vendas por CSV;
- suporte a delimitadores vírgula e ponto e vírgula, UTF-8 e BOM;
- pré-validação de cabeçalhos, linhas, valores, datas e relacionamentos;
- persistência transacional de lotes válidos;
- idempotência por marketplace e referência externa;
- relatório de linhas importadas, ignoradas e inválidas;
- CSV de exemplo e documentação do formato;
- teste controlado e upload HTTP multipart da importação.
- pesquisa documentada das APIs oficiais e limitações públicas do Programa de Afiliados do Mercado Livre;
- configuração segura e desativada por padrão para Client ID, Client Secret e redirect URI;
- componentes isolados para status e construção da URL OAuth oficial com `state` e PKCE S256;
- tela administrativa de diagnóstico da integração Mercado Livre;
- endpoint administrativo de status que nunca retorna o Client Secret;
- importação CSV mantida como alternativa à sincronização automática não confirmada.
- teste E2E do MVP usando os endpoints HTTP reais no Apache/XAMPP;
- cenário completo de produto, campanha, link afiliado, oferta, vitrine, analytics, venda e dashboard;
- validação do isolamento administrativo para acessos anônimos;
- limpeza automática e verificada de todos os registros e da sessão temporária do E2E.
- serviço CLI de backup transacional com `mysqldump`;
- arquivo temporário de credenciais removido após cada operação;
- manifesto de backup com SHA-256 e contagens por tabela;
- verificação de restauração em banco temporário com nome estritamente validado;
- comparação integral das tabelas e remoção confirmada do banco de verificação;
- documentação operacional de backup e recuperação isolada.
- Dockerfile de produção baseado em PHP 8.2/Apache e fixado por digest;
- configuração Apache que restringe acesso web a `public` e `admin`;
- executor idempotente de migrations protegido por lock de banco;
- bootstrap idempotente do primeiro administrador para instalação Umbrel;
- estrutura de pacote Umbrel com manifesto, app proxy, template e persistência;
- segredos distintos do banco derivados por instalação;
- MariaDB LTS fixado por digest e volumes persistentes;
- teste estático de portabilidade e segurança do pacote;
- documentação de instalação, atualização, backup e rollback planejados.
- ambiente Docker Engine dedicado no WSL, armazenado no F: para build de produção;
- build OCI da aplicação para `linux/amd64` e `linux/arm64`;
- validação real de migrations, bootstrap, health check, login, fluxo E2E, reinício e persistência em containers;
- URL-base configurável no teste E2E para reutilização em XAMPP e containers;
- exclusão de `.codex-tmp` do contexto de build.
- workflow GitHub Actions para publicar a imagem amd64/arm64 no GHCR com `GITHUB_TOKEN`.
- imagem pública `0.1.0` publicada no GHCR e fixada no pacote pelo digest remoto multi-arquitetura.

### Fixed

- transliteração de caracteres acentuados que inseria hífens incorretos em slugs no ambiente Windows.

### Removed

- scaffold anterior de FastAPI, Next.js, PostgreSQL e Docker, incompatível com a arquitetura XAMPP vigente.

### Pending

- integração oficial com os marketplaces quando houver credenciais e APIs compatíveis;
- teste real de instalação, atualização e rollback do pacote em umbrelOS.

### Validated

- linter oficial do App Store com `--check-images`, retornando `No issues found`;
- `git diff --check` do pacote na cópia descartável do repositório oficial.

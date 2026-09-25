# Pacote Umbrel — preparação

## Estado

**NÃO INSTALAR AINDA.** A estrutura segue o formato atual do App Store do Umbrel, mas contém marcadores `REPLACE_WITH_*` porque a imagem própria da aplicação ainda não foi publicada em um registry.

Pacote preparado:

```text
affiliate-offers/
├── umbrel-app.yml
├── docker-compose.yml
├── exports.sh
├── app.env.template
└── data/
    ├── mysql/
    └── storage/
        ├── backups/
        ├── imports/
        └── logs/
```

## Antes de instalar

1. publicar a imagem criada por `deploy/container/Dockerfile` para `linux/amd64` e `linux/arm64`;
2. fixar no Compose o nome, a versão e o digest multi-arquitetura da imagem;
3. substituir website, repositório, suporte e URL de submissão no manifesto;
4. confirmar que a porta `8347` continua livre no App Store de destino;
5. executar o linter oficial do repositório `getumbrel/umbrel-apps`;
6. testar instalação, abertura pelo `app_proxy`, reinício e persistência em um Umbrel real ou ambiente umbrelOS descartável.

## Arquitetura preparada

- `app_proxy` mantém a autenticação padrão do Umbrel;
- Apache/PHP serve `/public/` e `/admin/`, negando acesso web ao restante do código e ao `.env`;
- MariaDB usa volume persistente próprio;
- logs, importações e backups usam persistência sob `${APP_DATA_DIR}`;
- senhas do banco são derivadas de forma determinística com rótulos separados;
- o primeiro administrador usa `admin@umbrel.local` e `${APP_PASSWORD}`;
- reinícios não substituem a senha caso o administrador já exista;
- migrations são idempotentes e protegidas por lock no banco;
- nenhuma porta bruta, Docker socket, host network ou modo privilegiado é solicitado.

## Validação realizada e limite atual

A imagem foi construída localmente para `linux/amd64` e `linux/arm64` com Docker Engine/Buildx em uma distribuição WSL dedicada no F:. Em containers reais foram aprovados migrations, bootstrap, health check, login, fluxo E2E, reinício e persistência com MariaDB.

Ainda não foram executados a publicação no registry, o linter oficial, o `app_proxy` e o ciclo de instalação/atualização/rollback do umbrelOS. Os marcadores `REPLACE_WITH_*` continuam obrigatórios até existir um digest remoto publicado.

# Pacote Umbrel — preparação

## Estado

**PRONTO PARA TESTE CONTROLADO NO UMBREL; AINDA NÃO SUBMETER AO APP STORE.** A imagem está pública no GHCR e fixada por digest. O único marcador restante é a URL da futura submissão, que só existirá depois da validação real.

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

1. confirmar que a porta `8347` continua livre no App Store de destino;
2. adicionar `https://github.com/secretariadigitalbpc/affiliate-offers` em **App Store > Community App Stores**;
3. instalar **Ofertas de Afiliados** pela loja `Secretaria Digital BPC`;
4. testar abertura pelo `app_proxy`, reinício e persistência no Umbrel;
5. testar atualização e rollback controlados;
6. somente depois, criar a submissão e substituir `REPLACE_WITH_PR`.

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

A imagem foi construída para `linux/amd64` e `linux/arm64`, publicada como `ghcr.io/secretariadigitalbpc/affiliate-offers:0.1.1` e fixada pelo digest `sha256:ff64e6f104e7631382a5271261ab8da3bff827bc3e79d591ceffb21172672be3`. Em containers reais foram aprovados migrations, bootstrap, health check, login, fluxo E2E, reinício e persistência com MariaDB.

O linter oficial de `getumbrel/umbrel-apps` foi aprovado com `--check-images` (`No issues found`), e `git diff --check` terminou sem erros. Ainda não foram executados o teste pelo `app_proxy` e o ciclo de instalação/atualização/rollback do umbrelOS. O marcador da submissão permanece até existir um pull request real.

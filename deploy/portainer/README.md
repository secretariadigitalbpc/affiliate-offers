# Implantação pelo Portainer

Esta variante executa a mesma imagem publicada para `linux/amd64` e `linux/arm64`, mas não usa `app_proxy`, senha determinística ou ciclo de vida da App Store do Umbrel.

## Stack

No Portainer, crie uma stack chamada `affiliate-offers` usando `docker-compose.yml` e defina:

- `DB_PASSWORD`: senha aleatória exclusiva do usuário do banco;
- `DB_ROOT_PASSWORD`: outra senha aleatória exclusiva para o root do MariaDB;
- `ADMIN_EMAIL`: e-mail do primeiro administrador;
- `ADMIN_PASSWORD`: senha do primeiro administrador, com pelo menos 12 caracteres.

A aplicação fica disponível em `http://IP_DO_UMBREL:8347/public/`. O banco não publica a porta 3306. Banco e `storage` usam volumes Docker nomeados.

Como os pools automáticos desse Docker já estão esgotados por outras stacks, aplicação e banco compartilham o mesmo namespace de rede sobre a bridge padrão. O MariaDB fica vinculado somente a `127.0.0.1`; apenas a porta web 8347 é publicada no host.

## Limite da validação

Um teste aprovado pelo Portainer confirma imagem, banco, health check, login, reinício e persistência dos volumes. Ele não substitui a validação do pacote pela App Store e pelo `app_proxy` do Umbrel.

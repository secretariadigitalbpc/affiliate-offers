# Instruções para a IA de Programação

## Objetivo

Construir um sistema de afiliados para Mercado Livre e Shopee utilizando inicialmente:

- XAMPP;
- Apache;
- PHP;
- MySQL/MariaDB;
- HTML;
- CSS;
- JavaScript.

O projeto será executado localmente primeiro e migrado para o Umbrel somente após o MVP estar estável.

## REGRA OBRIGATÓRIA SOBRE MODELO DE IA

ANTES DE COMEÇAR QUALQUER ETAPA, NÃO PROGRAME AINDA.

Primeiro informe:

**MODELO RECOMENDADO PARA ESTA ETAPA:**
- GPT-5.6 Sol Instant
- GPT-5.6 Sol Medium
- GPT-5.6 Sol High

Explique em no máximo 2 frases por que esse modelo é adequado.

Depois informe:

**MODELO ATUAL ADEQUADO?**
- SIM — pode continuar.
ou
- NÃO — altere para [modelo recomendado] antes de continuar.

Se for necessário trocar de modelo, pare e espere o usuário trocar.

## Critério de escolha

### GPT-5.6 Sol Instant

Para:

- ajustes simples em HTML/CSS;
- textos;
- pequenas alterações visuais;
- renomeações;
- correções mecânicas já identificadas.

### GPT-5.6 Sol Medium

Padrão para:

- PHP;
- JavaScript;
- CRUD;
- MySQL/MariaDB;
- SQL;
- sessões;
- autenticação comum;
- formulários;
- AJAX/fetch;
- dashboard;
- criação de tabelas;
- testes normais.

### GPT-5.6 Sol High

Somente para:

- debugging difícil;
- segurança crítica;
- integrações externas;
- Mercado Livre;
- Shopee;
- WhatsApp;
- importações complexas;
- problemas de concorrência;
- alterações perigosas no banco;
- arquitetura crítica.

Não usar High por padrão.

## Ambiente

O projeto será desenvolvido dentro da pasta pública do XAMPP.

Exemplo no Windows:

```text
C:\xampp\htdocs\afiliados\
```

URL local esperada:

```text
http://localhost/afiliados/
```

Banco:

```text
MySQL/MariaDB do XAMPP
```

Ferramenta de administração:

```text
phpMyAdmin
```

## Stack principal

Não trocar sem autorização:

- Backend: PHP
- Frontend: HTML5 + CSS3 + JavaScript
- Banco: MySQL/MariaDB
- Servidor local: Apache via XAMPP
- Acesso ao banco: PDO
- Requisições assíncronas: Fetch API quando necessário
- Dependências PHP: Composer somente se justificadas

Framework PHP não é obrigatório.

Começar preferencialmente com PHP organizado em camadas simples.

## Estrutura esperada

```text
afiliados/
├── public/
│   ├── index.php
│   ├── produto.php
│   ├── ofertas.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── api/
├── admin/
├── app/
│   ├── Config/
│   ├── Controllers/
│   ├── Models/
│   ├── Repositories/
│   ├── Services/
│   ├── Integrations/
│   │   ├── MercadoLivre/
│   │   └── Shopee/
│   └── Helpers/
├── database/
│   ├── migrations/
│   └── seeds/
├── storage/
│   ├── logs/
│   └── imports/
├── tests/
├── docs/
├── .env
├── .env.example
└── composer.json
```

A estrutura poderá ser simplificada quando apropriado.

## Banco

Usar PDO com prepared statements.

Nunca montar SQL diretamente com valores fornecidos pelo usuário.

Obrigatório:

```php
$stmt = $pdo->prepare(...);
$stmt->execute([...]);
```

Nunca:

```php
$sql = "SELECT ... WHERE id = " . $_GET['id'];
```

## Regra contra retrabalho

Antes de implementar:

1. leia `PROJECT_STATUS.md`;
2. identifique a etapa atual;
3. leia os documentos relevantes;
4. implemente somente o escopo da etapa;
5. execute apenas os testes necessários;
6. atualize `PROJECT_STATUS.md`;
7. atualize `CHANGELOG.md` quando necessário.

## Segurança básica

Desde o início:

- PDO prepared statements;
- `password_hash()` para senhas;
- `password_verify()` para login;
- sessões PHP corretamente configuradas;
- validação server-side;
- escaping de HTML com `htmlspecialchars`;
- CSRF para ações administrativas importantes;
- segredos fora do código;
- nenhuma credencial real versionada.

## Não adicionar prematuramente

Não adicionar sem necessidade:

- Laravel;
- Symfony;
- Node.js como backend;
- React;
- Next.js;
- Python;
- PostgreSQL;
- Docker no ambiente local;
- Redis;
- Kafka;
- RabbitMQ;
- microserviços;
- automações não oficiais de WhatsApp.

Bibliotecas podem ser adicionadas se resolverem uma necessidade clara.

## Comunicação da IA

Antes de implementar uma etapa, informar:

```text
ETAPA ATUAL:
OBJETIVO:
MODELO RECOMENDADO:
ARQUIVOS A CRIAR/ALTERAR:
TESTE DA ETAPA:
O QUE NÃO SERÁ FEITO AINDA:
```

Ao terminar:

```text
CHECKPOINT DO PROJETO

Etapa:
Status:

Implementado:
✓

Testado:
✓

Pendente:

Próximo passo:

Modelo recomendado para o próximo passo:
```

## Regra de testes

Não considerar algo concluído porque o código parece correto.

Diferenciar sempre:

```text
IMPLEMENTADO
```

de:

```text
TESTADO E APROVADO
```

## Primeira ação em uma nova conversa

Leia todos os arquivos `.md`.

Depois responda inicialmente:

```text
ETAPA ATUAL:
...

MODELO RECOMENDADO:
...

MOTIVO:
...

MODELO ATUAL ADEQUADO:
SIM/NÃO

PRÓXIMO PASSO:
...
```

Não programe antes de realizar essa análise.

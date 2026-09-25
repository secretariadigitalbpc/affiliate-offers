# Prompt Mestre para a IA de Programação

Você será a IA responsável por programar este projeto.

Antes de escrever qualquer código:

1. Leia todos os arquivos `.md`.
2. Priorize:
   - `AI_INSTRUCTIONS.md`
   - `PROJECT_STATUS.md`
   - `ARCHITECTURE.md`
   - `DATABASE.md`
   - `API.md`
   - `TESTING.md`
   - `LOCAL_DEVELOPMENT.md`
   - `CHANGELOG.md`
3. Identifique exatamente onde o projeto parou.

## MODELO DE IA

ANTES DE PROGRAMAR, informe obrigatoriamente:

```text
ETAPA ATUAL:
...

MODELO RECOMENDADO:
GPT-5.6 Sol Instant / Medium / High

MOTIVO:
...

MODELO ATUAL ADEQUADO:
SIM/NÃO

PRÓXIMO PASSO:
...
```

Se o modelo atual não for adequado, pare e espere o usuário trocar.

### Instant

Usar para:

- pequenas alterações;
- HTML/CSS simples;
- textos;
- correções mecânicas.

### Medium

Usar como padrão para:

- PHP;
- JavaScript;
- SQL;
- MySQL/MariaDB;
- PDO;
- CRUD;
- dashboard;
- formulários;
- AJAX/fetch;
- testes comuns.

### High

Usar somente para:

- integrações Mercado Livre/Shopee;
- WhatsApp;
- segurança crítica;
- debugging complexo;
- problemas difíceis de banco;
- arquitetura crítica;
- falhas persistentes.

## STACK OBRIGATÓRIO

Ambiente inicial:

```text
XAMPP
Apache
PHP
MySQL/MariaDB
HTML5
CSS3
JavaScript
```

Acesso ao banco:

```text
PDO + prepared statements
```

Não substituir esse stack sem autorização explícita.

Não introduzir:

```text
Python
FastAPI
Next.js
PostgreSQL
Docker local
Laravel
Symfony
React
Node.js backend
```

a menos que exista uma necessidade concreta e o usuário aprove antes.

## LOCAL PRIMEIRO

O projeto será construído dentro do XAMPP.

Exemplo:

```text
C:\xampp\htdocs\afiliados\
```

Depois que o MVP estiver funcionando, haverá uma etapa separada para migrar ao Umbrel.

Não faça configurações específicas do Umbrel agora.

## FLUXO DE IMPLEMENTAÇÃO

Sempre:

```text
ler documentação
↓
identificar etapa
↓
indicar modelo
↓
implementar parte mínima
↓
testar
↓
corrigir
↓
atualizar documentação
↓
checkpoint
↓
próxima etapa
```

Não desenvolver várias etapas adiantadas.

## ANTES DE CADA ETAPA

Informe:

```text
ETAPA:
OBJETIVO:
ARQUIVOS QUE SERÃO CRIADOS/ALTERADOS:
TESTE DE VALIDAÇÃO:
FORA DO ESCOPO DESTA ETAPA:
```

## SEGURANÇA

Obrigatório:

- PDO prepared statements;
- `password_hash()`;
- `password_verify()`;
- `htmlspecialchars()`;
- validação server-side;
- proteção CSRF para ações sensíveis;
- segredos fora do código;
- nunca expor stack traces ao usuário em produção.

## TESTES

Execute o menor teste capaz de provar a alteração.

Não rode testes desnecessários.

Uma funcionalidade só pode ser marcada:

```text
TESTADA E APROVADA
```

quando o teste correspondente realmente tiver sido executado.

## DOCUMENTAÇÃO

Ao terminar uma etapa:

- atualizar `PROJECT_STATUS.md`;
- atualizar `CHANGELOG.md`;
- atualizar documentação técnica afetada.

## CHECKPOINT FINAL

Sempre terminar uma etapa com:

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

## PRIMEIRA AÇÃO AGORA

Leia os `.md`.

Não programe imediatamente.

Primeiro informe qual etapa está atual, qual modelo deve ser usado e o próximo passo.

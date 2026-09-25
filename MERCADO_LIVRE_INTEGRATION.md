# Integração oficial com Mercado Livre

## Resultado da verificação

Verificação realizada em 25/09/2026, somente em páginas públicas oficiais.

O Mercado Livre documenta uma plataforma geral de APIs e um fluxo OAuth 2.0 para recursos privados. Entretanto, o catálogo público consultado não apresenta uma API específica do Programa de Afiliados para obter vendas, comissões ou criar links, e a rota pública de documentação `/pt_br/afiliados` retorna 404.

Portanto, o sistema **não considera o OAuth geral como prova de acesso ao Hub de Afiliados** e não ativa sincronização automática. Essa conclusão significa apenas “não confirmado na documentação pública consultada”; não afirma que uma API privada ou futura não possa existir.

## Fontes oficiais

- [Autenticação e Autorização](https://developers.mercadolivre.com.br/pt_br/autenticacao-e-autorizacao), atualizada pelo Mercado Livre em 29/12/2025;
- [Crie uma aplicação no Mercado Livre](https://developers.mercadolivre.com.br/pt_br/crie-uma-aplicacao-no-mercado-livre);
- [Catálogo público de API Docs](https://developers.mercadolivre.com.br/pt_br/api-docs-pt-br);
- [Hub de Afiliados](https://www.mercadolivre.com.br/afiliados/hub), área da conta do usuário.

## OAuth oficial documentado

O fluxo geral usa Authorization Code no servidor:

```text
GET https://auth.mercadolivre.com.br/authorization
POST https://api.mercadolibre.com/oauth/token
```

Requisitos relevantes:

- criar uma aplicação no DevCenter para obter `Client ID` e `Client Secret`;
- nunca compartilhar o `Client Secret` e nunca solicitar a senha da conta;
- usar uma `redirect_uri` estática e exatamente igual à registrada;
- gerar um `state` aleatório por tentativa e validá-lo no retorno;
- quando PKCE estiver habilitado na aplicação, usar `code_challenge` e `code_verifier`; o método preparado é `S256`;
- enviar o access token no header `Authorization: Bearer`;
- o access token documentado expira em 6 horas;
- cada refresh token é de uso único e a renovação devolve um novo refresh token.

O código atual apenas prepara configuração, endpoints oficiais e geração segura da URL de autorização. Não há botão de conexão, callback, troca de token nem armazenamento de tokens porque a compatibilidade com os recursos de afiliados ainda não foi confirmada.

## Variáveis de ambiente

```dotenv
ML_OAUTH_ENABLED=false
ML_CLIENT_ID=
ML_CLIENT_SECRET=
ML_REDIRECT_URI=http://localhost/whatsappAF/admin/mercado-livre-callback.php
```

Regras:

- manter `ML_OAUTH_ENABLED=false` até obter confirmação oficial dos recursos necessários;
- preencher credenciais reais somente no `.env`, que não deve ser versionado;
- o status administrativo informa apenas se o segredo existe, nunca seu conteúdo;
- a URL de callback acima é uma reserva de configuração: o endpoint ainda não foi criado.

## Alternativa funcional

Enquanto não houver endpoint oficial compatível, o fluxo suportado permanece:

1. gerar/copiar o link no Hub de Afiliados;
2. cadastrá-lo na tela administrativa de links;
3. importar vendas e comissões pelo CSV documentado em `CSV_IMPORT.md`;
4. acompanhar os resultados no dashboard.

## Dependência para a próxima conexão real

Antes de implementar callback e armazenamento criptografado de tokens, é necessário obter do Mercado Livre uma referência oficial que confirme os endpoints e permissões do Programa de Afiliados para a aplicação do usuário. A senha da conta não será usada em nenhuma etapa.

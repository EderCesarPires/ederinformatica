# Eder Informática — Gerenciador de Serviços

Sistema em **PHP 8.3 + MySQL 8** com arquitetura **MVC** (sem framework) e padrões de projeto.

## Como rodar

```bash
docker compose up -d --build
```

- Aplicação: http://localhost:8080
- MySQL: `localhost:3307` (usuário `eder` / senha `eder123`, banco `eder_informatica`)

Crie sua conta em **/register** e faça login. O banco é criado automaticamente pelo `database/init.sql`
(com alguns serviços de exemplo). Para recriar o banco do zero: `docker compose down -v && docker compose up -d`.

## Funcionalidades

| Tela | O que faz |
|---|---|
| Cadastro / Login | Usuários salvos no MySQL, senha com **bcrypt** (`password_hash`), sessão protegida e CSRF em todos os formulários |
| Dashboard | Métricas do período (nº de OS, valor ganho, recebido, clientes atendidos), gráfico de **fluxo de clientes** (mês atual por padrão, filtrável por datas), lista de **tarefas do usuário** (criar / concluir / excluir) e últimas OS |
| Serviços | CRUD completo — nome, descrição e valor |
| Clientes | CRUD completo — nome, CPF/CNPJ (validado), CEP (endereço automático via ViaCEP) e complemento |
| Ordens de Serviço | CRUD completo — cliente, vários serviços com quantidade, status, **% de desconto** e **% de acréscimo** opcionais, total calculado ao vivo e recalculado no servidor, impressão |

## Estrutura MVC

```
src/
├── public/            # Front Controller (index.php), CSS e JS
├── config/            # config.php (lê variáveis do Docker) e routes.php
└── app/
    ├── Core/          # Router, Controller base, View, Session, Database
    ├── Controllers/   # C — recebem a requisição e coordenam Model/View
    ├── Models/        # M — Repositórios (todo o SQL fica aqui)
    ├── Pricing/       # Regras de cálculo de preço (Strategy + Factory)
    └── Views/         # V — templates PHP (layouts, telas, partials)
```

## Padrões de projeto utilizados

1. **Singleton** — `app/Core/Database.php`: uma única conexão PDO por requisição (construtor privado, `getInstance()`).
2. **Repository** — `app/Models/Repository.php` e filhos: isolam o acesso ao banco; controllers nunca escrevem SQL.
3. **Strategy** — `app/Pricing/*`: `PercentageDiscount`, `PercentageSurcharge` e `NoAdjustment` implementam
   `PriceAdjustmentStrategy`; o `PriceCalculator` aplica os ajustes sem conhecer suas regras.
4. **Factory** — `app/Pricing/AdjustmentFactory.php`: cria a estratégia correta a partir dos percentuais do formulário.
5. **Front Controller** — `public/index.php`: ponto único de entrada que delega ao `Router`.

## Regra de cálculo da OS

```
subtotal = Σ (quantidade × valor do serviço)
total    = subtotal − desconto%  →  depois + acréscimo% sobre o valor já com desconto
```

O preço de cada serviço é **congelado** no item da OS (`unit_price`), então alterar o valor de um serviço
não muda OS antigas. O "valor ganho" do dashboard soma as OS não canceladas; "recebido" soma apenas as concluídas.

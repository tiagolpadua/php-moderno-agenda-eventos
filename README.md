# Agenda de Eventos

Projeto-base da **Mentoria CREMESP · PHP Moderno** — trilha DevOps (semanas 1 a 6).

É uma aplicação PHP **propositalmente pequena** (sem framework) para cadastro e consulta de eventos.
O foco da trilha não é o código da aplicação, e sim tudo o que acontece em volta dele:
versionamento, containers, pipelines de CI/CD e NGINX. A cada semana você vai evoluir este repositório com uma nova tarefa.

> A estrutura de pastas imita a de um projeto Laravel (`public/index.php` como ponto de entrada,
> `composer.json`, `tests/`), então tudo o que construirmos aqui vale também para aplicações Laravel.

## Funcionalidades

| Rota | Método | O que faz |
|---|---|---|
| `/` | GET | Lista os eventos e mostra o formulário de cadastro |
| `/eventos` | POST | Cadastra um evento |
| `/api/eventos` | GET | Lista os eventos em JSON |
| `/api/eventos/{id}` | GET | Retorna um evento em JSON |
| `/health` | GET | Verificação de saúde (banco + nome da instância) |

Toda resposta traz o cabeçalho `X-App-Instance` com o nome da máquina/container que respondeu —
vai ser útil quando colocarmos várias réplicas atrás de um balanceador.

## Como executar

### Opção 1 — PHP instalado na máquina (8.3 ou superior)

```bash
composer install          # instala o PHPUnit (dependência de desenvolvimento)
composer serve            # sobe em http://localhost:8000
composer test             # roda os testes
```

> Sem Composer? Dá para só ver a aplicação funcionando com `php -S localhost:8000 -t public`.

### Opção 2 — GitHub Codespaces (nada para instalar)

No seu repositório: **Code → Codespaces → Create codespace on main** e, no terminal, rode os mesmos comandos da opção 1.

### Opção 3 — Docker

Chega na **semana 2** 🙂

## Banco de dados

Por padrão a aplicação usa **SQLite** (arquivo em `var/data/agenda.sqlite`, criado automaticamente com 3 eventos de exemplo).
Para usar outro banco, defina variáveis de ambiente:

| Variável | Exemplo |
|---|---|
| `DB_DSN` | `mysql:host=db;port=3306;dbname=agenda;charset=utf8mb4` |
| `DB_USER` | `agenda` |
| `DB_PASSWORD` | `segredo` |
| `MIGRAR_AO_INICIAR` | `false` para **não** criar as tabelas na primeira requisição (aí use `php bin/migrar.php`) |

## Estrutura

```
bin/             comandos de linha (migrar.php)
public/          ponto de entrada (index.php) e arquivos estáticos (css)
src/             código da aplicação (namespace App\)
templates/       HTML das páginas
tests/           testes automatizados (PHPUnit)
var/data/        banco SQLite local (ignorado pelo Git)
```

## Tarefas da mentoria

| Semana | Tema | Tarefa |
|---|---|---|
| 1 | GitHub | Fluxo issue → branch → PR, conflito, Dependabot e primeira Action |
| 2 | Docker | _em breve_ |
| 3 | Integração e entrega contínua | _em breve_ |
| 4 | GitHub Actions | _em breve_ |
| 5 | NGINX: proxy reverso e API gateway | _em breve_ |
| 6 | NGINX: FastCGI, cache e HTTPS | _em breve_ |

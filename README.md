# Agenda de Eventos

![CI](https://github.com/tiagolpadua/php-moderno-agenda-eventos/actions/workflows/ci.yml/badge.svg)

Projeto-base da **Mentoria CREMESP · PHP Moderno** — trilha DevOps (semanas 1 a 6).

É uma aplicação PHP **propositalmente pequena** (sem framework) para cadastro e consulta de eventos.
O foco da trilha não é o código da aplicação, e sim tudo o que acontece em volta dele:
versionamento, containers, pipelines de CI/CD e NGINX. A cada semana você vai evoluir este repositório com uma nova tarefa.

> A estrutura de pastas imita a de um projeto Laravel (`public/index.php` como ponto de entrada,
> `composer.json`, `tests/`), então tudo o que construirmos aqui vale também para aplicações Laravel.

> **Primeiro acesso?** Siga o [Guia de preparação do ambiente](https://gist.github.com/tiagolpadua/bf92e5c21947eb658bae03229cc753f2): o que instalar, como criar o seu repositório a partir deste template e como rodar o projeto.

## Autor

Tiago Pádua — repositório criado na semana 1 da mentoria (fluxo issue → branch → PR).

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

### Opção 3 — Docker (aplicação + MySQL)

```bash
cp .env.example .env                 # ajuste DOCKERHUB_USER e as senhas
docker compose up -d --build         # http://localhost:8000
docker compose ps                    # app "healthy", migracao "exited (0)"
docker compose down                  # para tudo, mantendo os dados no volume
```

Só a imagem, com SQLite dentro do container:

```bash
docker build -t agenda-eventos .
docker run --rm -p 8000:8000 agenda-eventos
```

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

Clique no tema para abrir as atividades da semana (publicadas após cada mentoria).

| Semana | Tema | Tarefa |
|---|---|---|
| 1 | [GitHub](https://gist.github.com/tiagolpadua/920dd0c9cec883bb9c816c879f4adfd9) | ✅ Fluxo issue → branch → PR, conflito, Dependabot e primeira Action |
| 2 | Docker | ✅ Dockerfile multi-stage, Compose com MySQL e volume, imagem no Docker Hub |
| 3 | Integração e entrega contínua | _em breve_ |
| 4 | GitHub Actions | _em breve_ |
| 5 | NGINX: proxy reverso e API gateway | _em breve_ |
| 6 | NGINX: FastCGI, cache e HTTPS | _em breve_ |

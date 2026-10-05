#!/usr/bin/env bash
#
# Verifica os papéis do NGINX: HTTPS, servidor web, FastCGI + load balancer,
# API gateway, cache e compressão.
#
# Uso: bin/verificar-nginx.sh [URL_HTTPS] [URL_HTTP]
#      (padrão: https://localhost:8443 e http://localhost:8000)
# O certificado autoassinado é aceito (-k): use só em ambiente de estudo.

set -euo pipefail

URL="${1:-https://localhost:8443}"
URL_HTTP="${2:-http://localhost:8000}"
CURL=(curl -sk)
falhas=0

ok()    { echo "✔ $1"; }
falha() { echo "✘ $1"; falhas=$((falhas + 1)); }
# cabecalho URL NOME [opções extras do curl] → valor do cabeçalho de resposta NOME
cabecalho() { "${CURL[@]}" -D - -o /dev/null "${@:3}" "$1" | grep -i "^$2:" | tr -d '\r' | cut -d' ' -f2- || true; }

# 1. HTTPS: HTTP redireciona para HTTPS, e o HTTPS responde com HTTP/2
destino=$("${CURL[@]}" -o /dev/null -w '%{http_code} %{redirect_url}' "${URL_HTTP}/eventos")
[[ "$destino" == 301\ https://* ]] && ok "HTTPS: HTTP redireciona (${destino})" || falha "HTTPS: esperado 301 para https://, veio '${destino}'"

versao=$("${CURL[@]}" -o /dev/null -w '%{http_version}' "${URL}/")
[[ "$versao" == "2" ]] && ok "HTTPS: HTTP/2 ativo" || falha "HTTPS: versão HTTP ${versao} (esperado 2)"

# 2. Servidor web: CSS vem do NGINX (sem passar pelo PHP), comprimido e com cache no navegador
[[ -z "$(cabecalho "${URL}/css/app.css" X-App-Instance)" ]] && ok "Estático: CSS servido pelo NGINX" || falha "Estático: CSS passou pela aplicação"
[[ "$(cabecalho "${URL}/css/app.css" Content-Encoding -H "Accept-Encoding: gzip")" == "gzip" ]] && ok "Performance: CSS com gzip" || falha "Performance: CSS sem gzip"
[[ -n "$(cabecalho "${URL}/css/app.css" Cache-Control)" ]] && ok "Performance: CSS com Cache-Control" || falha "Performance: CSS sem Cache-Control"

# 3. FastCGI + load balancer: várias réplicas PHP-FPM atendendo
instancias=$(for _ in $(seq 10); do cabecalho "${URL}/health" X-App-Instance; done | sort -u | grep -c . || true)
(( instancias >= 2 )) && ok "Load balancer: ${instancias} réplicas PHP-FPM atenderam" || falha "Load balancer: só ${instancias} réplica atendeu"

# 4. Cache: a segunda chamada à API vem do cache do NGINX
cabecalho "${URL}/api/v1/eventos" X-Cache-Status > /dev/null
cache=$(cabecalho "${URL}/api/v1/eventos" X-Cache-Status)
[[ "$cache" == "HIT" ]] && ok "Cache: /api/v1/eventos → HIT" || falha "Cache: esperado HIT, veio '${cache}'"

# 5. API gateway: rate limit devolve 429 em rajadas (por último, pois "gasta" o limite)
limitadas=$(for _ in $(seq 60); do "${CURL[@]}" -o /dev/null -w '%{http_code}\n' "${URL}/api/eventos"; done | grep -c '^429$' || true)
(( limitadas > 0 )) && ok "Gateway: rate limit ativo (${limitadas}/60 → 429)" || falha "Gateway: nenhuma requisição limitada"

if (( falhas > 0 )); then
    echo "Verificação do NGINX FALHOU (${falhas})."
    exit 1
fi
echo "Verificação do NGINX OK."

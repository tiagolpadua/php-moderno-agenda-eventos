#!/usr/bin/env bash
#
# Verifica os papéis do NGINX: servidor web, load balancer e API gateway.
# Uso: bin/verificar-nginx.sh [URL_BASE]      (padrão: http://localhost:8000)

set -euo pipefail

URL_BASE="${1:-${URL_BASE:-http://localhost:8000}}"
falhas=0

ok()    { echo "✔ $1"; }
falha() { echo "✘ $1"; falhas=$((falhas + 1)); }

# 1. Servidor web: o CSS vem do NGINX, sem passar pela aplicação PHP
if curl -s -D - -o /dev/null "${URL_BASE}/css/app.css" | grep -qi '^X-App-Instance'; then
    falha "Estático: /css/app.css passou pela aplicação (deveria vir direto do NGINX)"
else
    ok "Estático: /css/app.css servido pelo NGINX"
fi

# 2. Load balancer: requisições distribuídas entre réplicas diferentes
instancias=$(for _ in $(seq 10); do
    curl -s -D - -o /dev/null "${URL_BASE}/health" | grep -i '^X-App-Instance' | tr -d '\r' | cut -d' ' -f2
done | sort -u)
total=$(echo "$instancias" | grep -c . || true)
if (( total >= 2 )); then
    ok "Load balancer: ${total} réplicas atenderam ($(echo $instancias | tr '\n' ' '))"
else
    falha "Load balancer: só ${total} réplica atendeu (há mais de uma réplica rodando?)"
fi

# 3. API gateway: rota versionada
status=$(curl -s -o /dev/null -w '%{http_code}' "${URL_BASE}/api/v1/eventos")
[[ "$status" == "200" ]] && ok "Gateway: /api/v1/eventos → 200" || falha "Gateway: /api/v1/eventos → ${status}"

# 4. API gateway: rate limit devolve 429 em rajadas
codigos=$(for _ in $(seq 60); do curl -s -o /dev/null -w '%{http_code}\n' "${URL_BASE}/api/eventos"; done)
limitadas=$(echo "$codigos" | grep -c '^429$' || true)
(( limitadas > 0 )) && ok "Gateway: rate limit ativo (${limitadas} de 60 requisições → 429)" \
                     || falha "Gateway: nenhuma requisição limitada em uma rajada de 60"

if (( falhas > 0 )); then
    echo "Verificação do NGINX FALHOU (${falhas})."
    exit 1
fi
echo "Verificação do NGINX OK."

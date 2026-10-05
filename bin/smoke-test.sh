#!/usr/bin/env bash
#
# Smoke test: verificações rápidas de que a aplicação no ar está respondendo.
# Uso: bin/smoke-test.sh [URL_BASE]      (padrão: http://localhost:8000)
#
# É o "teste de fumaça" do pipeline: roda depois do deploy em um ambiente
# e falha (código de saída 1) se algo básico estiver quebrado.

set -euo pipefail

URL_BASE="${1:-${URL_BASE:-http://localhost:8000}}"
TENTATIVAS="${TENTATIVAS:-10}"
falhas=0

verificar() {
    local descricao="$1" caminho="$2" esperado="$3"
    local status
    status=$(curl -s -o /dev/null -w '%{http_code}' "${URL_BASE}${caminho}" || true)
    if [[ "$status" == "$esperado" ]]; then
        echo "✔ ${descricao} (${caminho} → ${status})"
    else
        echo "✘ ${descricao} (${caminho} → ${status}, esperado ${esperado})"
        falhas=$((falhas + 1))
    fi
}

echo "Aguardando ${URL_BASE}/health ..."
for ((i = 1; i <= TENTATIVAS; i++)); do
    if curl -sf "${URL_BASE}/health" > /dev/null; then
        break
    fi
    if (( i == TENTATIVAS )); then
        echo "✘ A aplicação não respondeu em ${URL_BASE}/health"
        exit 1
    fi
    sleep 3
done

verificar "Health check"          "/health"           200
verificar "Página inicial"        "/"                 200
verificar "API lista eventos"     "/api/eventos"      200
verificar "API evento 1"          "/api/eventos/1"    200
verificar "Rota inexistente → 404" "/rota-que-nao-existe" 404

if (( falhas > 0 )); then
    echo "Smoke test FALHOU: ${falhas} verificação(ões) com problema."
    exit 1
fi

echo "Smoke test OK."

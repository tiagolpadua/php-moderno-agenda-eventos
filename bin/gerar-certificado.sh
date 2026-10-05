#!/usr/bin/env bash
#
# Gera um certificado TLS AUTOASSINADO para localhost em nginx/certs/.
# Serve para estudo e desenvolvimento: o navegador vai mostrar um aviso de segurança.
# Para um certificado confiável localmente, use o mkcert (veja o material da semana 6).
# Em produção, use um certificado de uma autoridade certificadora (ex.: Let's Encrypt).

set -euo pipefail

PASTA="$(cd "$(dirname "$0")/.." && pwd)/nginx/certs"
mkdir -p "$PASTA"

if [[ -f "$PASTA/agenda.crt" && -f "$PASTA/agenda.key" ]]; then
    echo "Certificado já existe em nginx/certs/ (apague a pasta para gerar outro)."
    exit 0
fi

openssl req -x509 -newkey rsa:2048 -nodes -days 365 \
    -subj "/CN=localhost" \
    -addext "subjectAltName=DNS:localhost,IP:127.0.0.1" \
    -keyout "$PASTA/agenda.key" \
    -out "$PASTA/agenda.crt" 2>/dev/null

chmod 644 "$PASTA/agenda.crt" "$PASTA/agenda.key"
echo "Certificado gerado em nginx/certs/ (válido por 365 dias)."

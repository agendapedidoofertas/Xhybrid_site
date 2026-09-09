#!/usr/bin/env bash
# Serve o site com PHP (API + admin + estáticos).
# Uso: bash scripts/dev.sh
#      bash scripts/dev.sh --port 8000
PORT=8000
while [ $# -gt 0 ]; do
  case "$1" in
    --port) PORT="$2"; shift 2;;
    --port=*) PORT="${1#*=}"; shift;;
    *) shift;;
  esac
done
cd "$(dirname "$0")/.." || exit 1
exec php -S "0.0.0.0:${PORT}" router.php

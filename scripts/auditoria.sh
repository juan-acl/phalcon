#!/usr/bin/env bash
# Auditoría estática rápida (Phalcon). Desde la raíz del proyecto: bash scripts/auditoria.sh
cd "$(dirname "$0")/.." || exit 1
fail=0
check() { # $1 titulo  $2 resultado
  if [ -n "$2" ]; then echo "[!] $1"; echo "$2"; echo; fail=1; else echo "[OK] $1"; fi
}

check "Volt: {{ }} sin |e (posible XSS; revisar cada línea)" \
  "$(grep -rnE '\{\{[^}]*\}\}' app/views 2>/dev/null | grep -vE '\|\s*(e|escape|escape_attr|escape_css|escape_js)\b|url\(|security\.get|flashSession\.output|content\(\)|partial\(' )"

check "Código PHP dentro de vistas (<?php / <?=)" \
  "$(grep -rnE '<\?(php|=)' app/views 2>/dev/null)"

check "SQL con variables PHP incrustadas (usar :param: / bind)" \
  "$(grep -rnE '(SELECT|INSERT|UPDATE|DELETE|conditions|WHERE).*(\"[^\"]*\$[A-Za-z_]|\.\s*\$[A-Za-z_])' app --include=*.php 2>/dev/null)"

check "Superglobales directas (usar \$this->request)" \
  "$(grep -rnE '\$_(GET|POST|REQUEST|FILES|COOKIE)' app 2>/dev/null)"

check "Uso de ->assign() sin lista blanca (asignación masiva)" \
  "$(grep -rnE '->assign\(\s*\$[A-Za-z_>-]+\s*\)' app 2>/dev/null)"

sin_csrf=""
for f in $(grep -rliE 'method="(post)"' app/views 2>/dev/null); do
  grep -q 'getToken' "$f" || sin_csrf="$sin_csrf
$f"
done
check "Formularios POST sin token CSRF (getToken)" "$sin_csrf"

check "Rutas que modifican datos con GET o sin método (addGet/add)" \
  "$(grep -nE "(addGet|->add)\(.*(eliminar|borrar|crear|actualizar|delete)" app/config/routes.php 2>/dev/null)"

check "Debug activado por defecto" \
  "$(grep -nE "'debug' *=> *true" app/config/*.php 2>/dev/null)"

if [ -f app/config/config.local.php ]; then
  grep -qE "'username' *=> *'root'" app/config/config.local.php && echo "[!] Usuario 'root' de MySQL: crear un usuario con permisos solo sobre la base 'escuela'"
fi
[ -f public/uploads/.htaccess ] && echo "[OK] uploads/.htaccess presente" || echo "[!] Falta public/uploads/.htaccess"

if [ -d .git ]; then
  git ls-files --error-unmatch app/config/config.local.php >/dev/null 2>&1 \
    && echo "[!] config.local.php está versionado en git" || echo "[OK] config.local.php fuera de git"
fi

echo
[ $fail -eq 0 ] && echo "Resultado: sin hallazgos." || echo "Resultado: hay hallazgos para revisar."

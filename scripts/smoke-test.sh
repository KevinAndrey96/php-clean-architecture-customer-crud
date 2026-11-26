#!/bin/sh
# End-to-end smoke test against the running compose stack: ./scripts/smoke-test.sh [port]
set -eu
BASE="http://localhost:${1:-8080}"
JAR=$(mktemp)
fail() { echo "FAIL: $*"; exit 1; }

code=$(curl -s -o /tmp/crud_index.html -w '%{http_code}' -c "$JAR" "$BASE/")
[ "$code" = 200 ] || fail "index $code"
grep -q '4 cliente(s)' /tmp/crud_index.html || fail "seeds not listed"
grep -q '900.123.456-8' /tmp/crud_index.html || fail "formatted nit missing"
grep -q 'Andes Logística S.A.S.' /tmp/crud_index.html || fail "seed text is not utf-8"

curl -s -b "$JAR" -c "$JAR" "$BASE/customers/new" > /tmp/crud_new.html
TOKEN=$(sed -n 's/.*name="_token" value="\([a-f0-9]*\)".*/\1/p' /tmp/crud_new.html | head -1)
[ -n "$TOKEN" ] || fail "no csrf token"

code=$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" --data-urlencode "nit=800.197.268-4" \
  --data-urlencode "company_name=Dirección de Impuestos <b>" --data-urlencode "address=Carrera 8 # 6C-38" \
  --data-urlencode "phone=601 742 8973" --data-urlencode "city=Bogotá" --data-urlencode "credit_limit=0" \
  --data-urlencode "_token=$TOKEN" "$BASE/customers")
[ "$code" = 303 ] || fail "create $code"

code=$(curl -s -o /tmp/crud_bad.html -w '%{http_code}' -b "$JAR" --data-urlencode "nit=800197268-5" --data-urlencode "_token=$TOKEN" "$BASE/customers")
[ "$code" = 422 ] || fail "invalid create $code"
grep -q 'para 800197268 es 4' /tmp/crud_bad.html || fail "check digit message"

code=$(curl -s -o /dev/null -w '%{http_code}' --data "nit=1" "$BASE/customers")
[ "$code" = 403 ] || fail "csrf $code"

curl -s -b "$JAR" "$BASE/customers/800197268" | grep -q 'Dirección de Impuestos &lt;b&gt;' || fail "escaped show"

code=$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" --data-urlencode "nit=811.022.233-6" \
  --data-urlencode "company_name=Dirección de Impuestos" --data-urlencode "address=Carrera 8 # 6C-38" \
  --data-urlencode "phone=601 742 8973" --data-urlencode "city=Bogotá" --data-urlencode "credit_limit=0" \
  --data-urlencode "_token=$TOKEN" "$BASE/customers/800197268")
[ "$code" = 422 ] || fail "duplicate nit on update $code"

code=$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" --data-urlencode "_token=$TOKEN" "$BASE/customers/800197268/delete")
[ "$code" = 303 ] || fail "delete $code"
code=$(curl -s -o /dev/null -w '%{http_code}' "$BASE/customers/800197268")
[ "$code" = 404 ] || fail "deleted still visible $code"

curl -sI "$BASE/" | grep -qi "content-security-policy" || fail "csp header"
curl -sI "$BASE/" | grep -qi "x-powered-by" && fail "x-powered-by leaked"
rm -f "$JAR"
echo "smoke ok"

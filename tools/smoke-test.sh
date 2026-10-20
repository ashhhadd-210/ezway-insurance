#!/bin/bash
# Go-live check for the EZ Way website.
# Usage: tools/smoke-test.sh https://www.example.com [--send-quote]
#   --send-quote also submits one clearly-marked test quote (it shows up in the
#   dashboard and is emailed to the agency, which confirms email works).
set -u
BASE="${1%/}"
SEND="${2:-}"
pass=0; fail=0
ok()   { printf '  \033[32m✓\033[0m %s\n' "$1"; pass=$((pass + 1)); }
bad()  { printf '  \033[31m✗\033[0m %s\n' "$1"; fail=$((fail + 1)); }
code() { curl -s -o /dev/null -w '%{http_code}' "$BASE$1"; }
expect() { [ "$(code "$1")" = "$2" ] && ok "$1 → $2" || bad "$1 → $(code "$1") (expected $2)"; }

echo "Pages"
for p in / /auto-insurance /services /about /contact /quote /privacy; do expect "$p" 200; done
expect /this-page-does-not-exist 404
expect /coverage 301
expect /sitemap.xml 200
expect /robots.txt 200

echo "Security (these must be blocked)"
expect /app/config.php 403
expect /storage/ 403
expect /app/lib/db.php 403

echo "HTTPS"
if [[ "$BASE" == https://* ]]; then
  http="http://${BASE#https://}"
  r=$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "$http/")
  [[ "$r" == 30*https://* ]] && ok "http redirects to https" || bad "http does not redirect to https ($r). Enable the HTTPS lines in .htaccess once SSL is active."
else
  bad "site is not on https"
fi

echo "Features"
vin=$(curl -s "$BASE/api/vin.php?vin=1HGCV1F34LA012345")
[[ "$vin" == *'"make":"Honda"'* ]] && ok "VIN lookup works (2020 Honda Accord)" || bad "VIN lookup failed: ${vin:0:120}"
home=$(curl -s "$BASE/")
[[ "$home" == *'/assets/css/site.css?v='* ]] && ok "styles load with a cache-busting version" || bad "stylesheet link not found"
[[ "$home" == *'(512) 679-2682'* ]] && ok "phone number on the page" || bad "phone number missing"
[[ "$home" != *'ezwayuw@'* ]] && ok "agency email is not shown publicly" || bad "agency email is visible on the page"
a=$(code /admin/login.php); [[ "$a" = 200 || "$a" = 303 ]] && ok "dashboard sign-in page responds ($a)" || bad "dashboard sign-in page → $a"

if [ "$SEND" = "--send-quote" ]; then
  echo "Test quote"
  jar=$(mktemp)
  token=$(curl -s -c "$jar" "$BASE/quote" | grep -o 'data-token="[^"]*"' | head -1 | cut -d'"' -f2)
  echo "  waiting 10 seconds (spam protection rejects instant submissions)…"; sleep 10
  res=$(curl -s -b "$jar" -X POST -H 'Content-Type: application/json' -H "Origin: $BASE" "$BASE/api/quote.php" --data "{
    \"token\":\"$token\",\"lang\":\"en\",\"service\":\"bonds\",
    \"applicant\":{\"first\":\"Website\",\"last\":\"Test\",\"dob\":\"01/01/1980\",\"phone\":\"5126792682\",\"email\":\"test@example.com\",\"contact_pref\":\"email\"},
    \"address\":{\"zip\":\"78754\",\"city\":\"Austin\",\"state\":\"TX\"},
    \"details\":{\"bond_type\":\"permit\",\"bond_amount\":\"\$1\",\"notes\":\"TEST QUOTE from the go-live check. Safe to delete.\"},
    \"consent\":true}")
  rm -f "$jar"
  [[ "$res" == *'"ok":true'* ]] && ok "test quote saved: $(echo "$res" | grep -o 'Q-[A-Z0-9]*') (check the dashboard and the inbox)" || bad "test quote failed: $res"
fi

echo
echo "$pass passed, $fail failed"
[ "$fail" -eq 0 ]

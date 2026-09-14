# Deploy Hostgator — 8xd.com.br

Publicação Apache/cPanel dos projetos **xhybrid_site** (apex) e **crm_software** (subdomínio CRM).

## Layout de pastas (mesmo cPanel)

| Host | Pasta | Conteúdo |
|------|-------|----------|
| `https://8xd.com.br` | `~/public_html/` | árvore de `xhybrid_site` |
| `https://crm.8xd.com.br` | `~/crm/` | árvore de `crm_software` |

Não coloque o CRM em `8xd.com.br/admin` nem em `/crm` no apex (conflito com o admin do site).

## Pré-requisitos

- PHP 8+ com `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl` (`curl` recomendado para Places)
- SSL Let’s Encrypt em `8xd.com.br`, `www.8xd.com.br` e `crm.8xd.com.br`
- Canônico: HTTPS; `www` → apex (`8xd.com.br`)

## Upload

1. Envie o conteúdo de `xhybrid_site` para `public_html` (incluindo `.htaccess` e `.user.ini`).
2. Envie o conteúdo de `crm_software` para `~/crm` e aponte o Document Root do subdomain `crm` para essa pasta.
3. Permissões: pastas `data/` (e `assets/uploads/` no site) graváveis (`775` típico); configs `640` quando possível.

## Configs (gitignored — criar no servidor)

### CRM (`~/crm/data/`)

```sh
cp data/xhybrid_public_base.php.example data/xhybrid_public_base.php
# return 'https://8xd.com.br';

cp data/xhybrid_publish.php.example data/xhybrid_publish.php
# return '/home/USER/public_html/data/site.sqlite';

# Opcional Places:
cp data/places_api_key.php.example data/places_api_key.php
```

Fallbacks `../xhybrid_site` **não** funcionam com `~/public_html` + `~/crm` — use path absoluto.

### Xhybrid (`~/public_html/data/`)

```sh
cp data/crm_bridge.php.example data/crm_bridge.php
# return '/home/USER/crm';
```

## Primeiro acesso

1. Site admin: `https://8xd.com.br/admin/setup.php` (só com zero usuários)
2. CRM admin: `https://crm.8xd.com.br/admin/setup.php`
3. Ative um lead no CRM e abra `https://8xd.com.br/{slug}/{letra}{id}`

Se houver leads com slug reservado (`admin`, `api`, …):

```sh
cd ~/crm && php scripts/fix_reserved_slugs.php
php scripts/resync_plan_flags.php   # se flags de plano estiverem defasadas
```

## Segurança

- `.htaccess` raiz: HTTPS, bloqueio de `/data` `/lib` `/scripts`, rewrite → `router.php`, HSTS curto (`max-age=300`)
- `.user.ini`: `display_errors=Off`
- `data/.htaccess`: Deny
- Cookies de sessão usam `Secure` quando HTTPS / `X-Forwarded-Proto` está presente
- APIs same-origin; `api/payment_claim.php` tem rate limit (8 / 10 min)

## Checklist QA

**Infra**

- [ ] `https://8xd.com.br/` abre a vitrine
- [ ] HTTP → HTTPS; `www` → apex
- [ ] `/data/site.sqlite` → 403
- [ ] `https://crm.8xd.com.br/admin/login.php`

**Lifecycle**

- [ ] Ativar lead → URL com base `https://8xd.com.br` (formato `/{slug}/{letra}{id}`)
- [ ] Site do lead sem flash da vitrine (textos/aparência corretos)
- [ ] Planos basic / medium / pro (limites, seções, brand_edit)
- [ ] Editar no admin Xhybrid → reflete no CRM
- [ ] Inactive + claim; redirect legado `{id}{letra}` → `{letra}{id}`

**Frontend / segurança**

- [ ] CSS/JS/imagens e páginas `/sobre` `/galeria` `/contato` no path do lead
- [ ] Setup bloqueado após 1º user; cookie Secure; upload sem execução PHP

## Notas

- Deploy em **subpasta** do apex (ex. `/xhybrid_site`) quebra `<base href="/">` — este guia assume DocumentRoot na raiz.
- Domínio próprio por cliente (medium/pro) fica fora deste deploy; blurbs da UI falam em subcaminho 8xd.
- Nginx: ver [deploy-nginx.md](deploy-nginx.md).

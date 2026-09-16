# Deploy Hostgator — 8xd.com.br (layout final)

Site na **raiz do domínio**; CRM no **subdomínio**.

## Layout

| Host | Document Root (exemplo) | Conteúdo |
|------|-------------------------|----------|
| `https://8xd.com.br` | `/home2/sonawe03/8xd.com.br/xhybrid_site` | árvore do `xhybrid_site` |
| `https://crm.8xd.com.br` | `/home2/sonawe03/crm` | árvore do `crm_software` |

**URLs finais**

| Uso | URL |
|-----|-----|
| Vitrine | `https://8xd.com.br/` |
| Admin site | `https://8xd.com.br/admin/` |
| Lead | `https://8xd.com.br/{slug}/{letra}{id}` ex. `/eletricistaton/x22` |
| CRM | `https://crm.8xd.com.br/admin/` |

Não coloque o CRM em `8xd.com.br/admin` (conflito com o painel do site).

Kit espelhado: pasta irmã `deploy_hostgator/` (checklist + copies dos examples).

## Pré-requisitos

- PHP 8+ com `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl` (`curl` para Places)
- SSL em `8xd.com.br`, `www.8xd.com.br` e `crm.8xd.com.br`
- HTTPS; `www` → apex

## cPanel (ordem sugerida)

1. **Domínios** → `8xd.com.br` → Document Root = pasta do Xhybrid (a que tem `index.html`, `router.php`, `.htaccess`).
2. **Subdomínios** → criar `crm` → Document Root = pasta do CRM (fora do Document Root do site, se possível).
3. **SSL** Let’s Encrypt nos dois hosts.
4. Upload/atualize o código (incluindo `.htaccess` e `.user.ini`).
5. Permissões: `data/` (e `assets/uploads/` no site) graváveis (`775`).

## Configs no servidor (gitignored)

### Site (`…/xhybrid_site/data/` ou Document Root/`data/`)

```sh
# NÃO crie app_base_path.php — ou:
# return '';

cp data/crm_bridge.php.example data/crm_bridge.php
# return '/home2/sonawe03/crm';   # path REAL da pasta do CRM no File Manager
```

### CRM (`…/crm/data/`)

```sh
cp data/xhybrid_public_base.php.example data/xhybrid_public_base.php
# return 'https://8xd.com.br';

cp data/xhybrid_publish.php.example data/xhybrid_publish.php
# return '/home2/sonawe03/8xd.com.br/xhybrid_site/data/site.sqlite';

# Opcional Places:
cp data/places_api_key.php.example data/places_api_key.php
```

Localhost: **não** crie `app_base_path.php` nem os bridges Linux.

## Migração a partir de `/xhybrid_site` e `/crm_software`

1. Ajuste Document Roots (acima).
2. Remova `app_base_path.php` se existir com `'/xhybrid_site'`.
3. Atualize `xhybrid_public_base.php` no CRM para `https://8xd.com.br`.
4. Reative/sync um lead e teste `https://8xd.com.br/{slug}/…`.
5. Opcional: redirect 301 de `/xhybrid_site/...` → `/...` no `.htaccess` do apex.
6. Atualize bookmarks e `xhybrid_qa` (já aponta para as URLs novas).

## Primeiro acesso

1. Site: `https://8xd.com.br/admin/setup.php` (só com zero usuários)
2. CRM: `https://crm.8xd.com.br/admin/setup.php`
3. Ative um lead e abra `https://8xd.com.br/{slug}/{letra}{id}`

## Segurança

- `.htaccess`: HTTPS, bloqueio `/data` `/lib` `/scripts`, rewrite → `router.php`
- `.user.ini`: `display_errors=Off`
- `data/.htaccess`: Deny

## Checklist QA

- [ ] `https://8xd.com.br/` abre a vitrine Xhybrid
- [ ] `https://8xd.com.br/admin/login.php`
- [ ] Lead na raiz (sem `/xhybrid_site`)
- [ ] `/data/site.sqlite` → 403
- [ ] `https://crm.8xd.com.br/admin/login.php`
- [ ] Ativar lead no CRM → URL com base `https://8xd.com.br`

## Testes locais (pasta `xhybrid_qa`)

```powershell
cd C:\Users\Bosco\Documents\GitHub\xhybrid_qa
php php/smoke_http.php
cd playwright
npm.cmd run test:headed
```

## Notas

- Domínio próprio por cliente (medium/pro) continua via `public_host` / DNS addon.
- Nginx: [deploy-nginx.md](deploy-nginx.md).

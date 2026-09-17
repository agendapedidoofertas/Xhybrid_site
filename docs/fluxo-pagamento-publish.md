# Fluxo atual: pagamento, publish e grace

> Domínio próprio por cliente **ainda não é produto** — até a Fase 4 (hosts), a URL pública é path em `https://8xd.com.br/{slug}/{letra}{id}`.

## Peças

| Peça | Onde |
|------|------|
| CRM leads + status pagamento | `crm_software` SQLite `data/crm.sqlite` |
| Sites publicados | `xhybrid_site` tabela `published_sites` em `data/site.sqlite` |
| PIX estático (manual) | `xhybrid_site/lib/pix.php` + `data/pix_config.php` |
| Claim “Já paguei” | `api/payment_claim.php` → grace 72h |
| Confirmar pago (humano) | CRM `admin/lead.php` ação `confirm_paid` |
| Expirar grace | `crm_software/scripts/expire_payment_grace.php` (cron) + lazy no `router.php` |

## Fluxo feliz (manual hoje)

1. Operador ativa lead no CRM → `crm_activate_site` → upsert `published_sites` + `site_active=1`.
2. Cliente acessa `https://8xd.com.br/{slug}/{letra}{id}`.
3. Se inadimplente / inativo → `site-inactive.html` com PIX + botão “Já paguei”.
4. Claim → `payment_status=pending_confirm`, grace 72h, site provisório ativo.
5. Operador confirma PIX → `paid` + limpa grace.
6. Se grace vence sem confirm → cron/lazy → `overdue` + `site_active=0`.

## Ponte CRM ↔ Xhybrid

- CRM → Xhybrid: `data/xhybrid_publish.php` (path absoluto do `site.sqlite`) ou `XHYBRID_SITE_SQLITE`.
- Base pública de links: `data/xhybrid_public_base.php` → `https://8xd.com.br`.
- Xhybrid → CRM: `data/crm_bridge.php` (path da pasta CRM) para sync reverso e grace.

Fallbacks `../xhybrid_site` / `../crm_software` **não** funcionam no layout Hostgator apex + `~/crm`. Use `crm_bridge.php` e `xhybrid_publish.php` com paths absolutos (ver `deploy-hostgator.md`).

## Próximo (código)

- `PAYMENT_MODE=manual|stub|live` + Banco Inter (ver `crm_software/lib/payment/`).
- Hosts: Basic `{slug}.8xd.com.br`; Medium/Pro domínio próprio (`HTTP_HOST`).

## Runbook mínimo

Ver [runbook-producao.md](runbook-producao.md).

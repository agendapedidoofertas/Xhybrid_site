# Runbook de produção — 8xd / CRM

## Deploy
Ver [deploy-hostgator.md](deploy-hostgator.md). Fluxo pagamento/publish: [fluxo-pagamento-publish.md](fluxo-pagamento-publish.md).

## Cron (cPanel)
```
# Ajuste USER/paths ao File Manager (ex.: sonawe03)

# A cada hora — expirar grace PIX
php /home2/sonawe03/crm/scripts/expire_payment_grace.php

# Diário — backup CRM + site
php /home2/sonawe03/crm/scripts/backup_sqlite.php
php /home2/sonawe03/8xd.com.br/xhybrid_site/scripts/backup_sqlite.php

# Diário — sync assinaturas (quando PAYMENT_MODE=stub|live)
php /home2/sonawe03/crm/scripts/sync_subscriptions.php
```

## PAYMENT_MODE
1. `manual` (padrão): PIX + “Já paguei” + confirm humano no CRM.
2. `stub`: sem chave Asaas — gerar cobrança no lead → `payment_stub.php` simula pago/overdue.
3. `live`: preencher `crm/data/payment_gateway.php` (api_key + webhook_token) e apontar webhook Asaas para `https://crm.8xd.com.br/api/webhook_payment.php`.

Copiar: `data/payment_gateway.php.example` → `payment_gateway.php`.

## Hosts
- Basic: `{slug}.8xd.com.br` — wildcard DNS + subdomain no cPanel → mesmo `public_html`.
- Medium/Pro: domínio no lead → Verificar DNS → addon domain + AutoSSL no painel.
- Migrar ativos: `php scripts/migrate_lead_hosts.php` (na pasta CRM).

Se wildcard/SSL no shared falhar → VPS com Caddy/certbot (sem Cloudflare).

## Publish falhou
1. Conferir `data/xhybrid_publish.php` path absoluto.
2. Permissão de escrita no `site.sqlite`.
3. Ver Fila do dia / `ops_events` no CRM.
4. Reativar lead (site_on) ou “Gerar/atualizar host”.

## Restore backup
1. Parar tráfego se possível.
2. Copiar `data/backups/crm-….sqlite` → `data/crm.sqlite` (e equivalente no site).
3. Testar login admin + um lead.

## Segurança
Ver [security-gate.md](security-gate.md).

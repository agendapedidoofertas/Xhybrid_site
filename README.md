# Xhybrid

Site institucional da **Xhybrid** — agência de criação de sites, manutenção e tecnologia.

O site público é **HTML/CSS/JS**. Textos e metadados ficam no **SQLite**; **arquivos de mídia** ficam em disco (`assets/uploads/`). Painel em `/admin`.

## Requisitos

- PHP 8+ com extensão **pdo_sqlite**
- Navegador moderno
- Pasta `data/` e `assets/uploads/` graváveis pelo PHP

## Como rodar localmente

```sh
php -S localhost:8000 router.php
```

Abra:

- Site: http://localhost:8000/
- Admin: http://localhost:8000/admin/setup.php (primeira vez)

## Editar conteúdo

| O que mudar | Onde |
|-------------|------|
| Textos | `/admin/texts.php` |
| WhatsApp, e-mail, SMTP | `/admin/contact.php` |
| Marca, SEO por página, Analytics | `/admin/brand.php` |
| Imagens / upload / vídeos | `/admin` → Imagens |
| Ocultar páginas/seções | `/admin/sections.php` |
| Planos | `/admin/plan.php` |
| Presets de nicho | `/admin/preset.php` |
| Backup SQLite | `/admin/backup.php` ou `php scripts/backup_sqlite.php` |

## Subdomínios leves (1 cliente = 1 site)

Para `cliente.seudominio.com.br` **não** compartilhe o mesmo SQLite com vários clientes.

**Modelo recomendado:**

1. Uma cópia (ou deploy) do projeto por cliente **ou** código compartilhado + pastas privadas.
2. Cada instância tem o próprio `data/site.sqlite` (só textos, flags, paths).
3. Cada instância tem o próprio `assets/uploads/` (fotos/vídeos no disco).
4. Apague a pasta do cliente para remover tudo — sem “peso” no banco da vitrine.

Evite multi-tenant em um único SQLite (vários clientes no mesmo arquivo): cresce rápido, complica backup e isolamento.

### Checklist de entrega por subdomínio

- Aplicar preset + personalizar marca/WhatsApp
- Subir logo/hero (upload)
- Configurar SMTP se o formulário deve enviar e-mail
- Baixar backup após go-live
- Garantir que `data/` não é acessível via HTTP (já há `.htaccess` em `data/`)

## Publicar

Hospede em servidor com **PHP + SQLite**. Garanta escrita em `data/` e `assets/uploads/`. Bloqueie acesso HTTP a `data/`.

**Produção Hostgator (8xd.com.br):** [docs/deploy-hostgator.md](docs/deploy-hostgator.md) + kit irmão `deploy_hostgator/` (checklist cPanel + configs prontas). DocumentRoot = este projeto; CRM em `crm.8xd.com.br`.

URLs públicas de lead: path `/{slug}/{letra}{id}` (legado) e host `{slug}.8xd.com.br` / domínio próprio (quando provisionado).

- Fluxo PIX/publish: [docs/fluxo-pagamento-publish.md](docs/fluxo-pagamento-publish.md)
- Runbook: [docs/runbook-producao.md](docs/runbook-producao.md)
- Planos / checkout: `/planos.html`, `/checkout.html`

# Xhybrid

Site institucional da **Xhybrid** — agência de criação de sites, manutenção e tecnologia.

O site público é **HTML/CSS/JS**. Textos e imagens ficam no **SQLite** e são lidos pela API PHP. Há um painel admin em `/admin`.

## Requisitos

- PHP 8+ com extensão **pdo_sqlite**
- Navegador moderno

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
| Textos do site | `/admin/texts.php` |
| WhatsApp, e-mail, Instagram | `/admin/contact.php` |
| Imagens / portfólio | `/admin` |
| Defaults (fallback) | `js/data.js` e `lib/settings.php` |

## Publicar

Hospede em servidor com **PHP + SQLite**. Garanta escrita em `data/` e bloqueie acesso HTTP a essa pasta.

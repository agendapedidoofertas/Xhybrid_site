# Bloquear pasta data/ no Nginx

```nginx
location ^~ /data/ {
    deny all;
    return 404;
}
```

# Apache / Hostgator

Use o `.htaccess` na raiz do projeto (rewrite → `router.php` + deny de `data/`/`lib/`/`scripts`) e `data/.htaccess` (`Require all denied`).

Guia completo de produção 8xd.com.br: [deploy-hostgator.md](deploy-hostgator.md).

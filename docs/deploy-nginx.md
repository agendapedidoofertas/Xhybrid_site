# Bloquear pasta data/ no Nginx

location ^~ /data/ {
    deny all;
    return 404;
}

# Apache: use data/.htaccess (Require all denied) + php -S com router.php

# php-shortlink
Make shortlink for PHP

Example nginx configuration
```
server {
    listen 443 ssl http2;
    server_name hks.pw; # Fix domain

    root [directory];

    if (-f $request_filename) {
        break;
    }
    if (-d $request_filename) {
        break;
    }
    rewrite ^/(.+)$ /index.php?$1 last;
    error_page 404  = /index.php?$uri;

    include fastcgi.conf; # PHP Configuration
}
```

```
server {
    server_name media.dropjdid.com;
    root /var/www/media.dropjdid.com;

    # Allow Let's Encrypt SSL renewals
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    # Serve uploaded media files directly and add caching / CORS headers
    location / {
        try_files $uri $uri/ =404;

        add_header Access-Control-Allow-Origin "*" always;
        add_header Access-Control-Allow-Methods "GET, OPTIONS" always;

        expires 30d;
        add_header Cache-Control "public";
    }

    # Prevent access to hidden files
    location ~ /\. {
        deny all;
    }

    listen [::]:443 ssl ipv6only=on; # managed by Certbot
    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/media.dropjdid.com/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/media.dropjdid.com/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot
}

server {
    if ($host = media.dropjdid.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot

    listen 80;
    listen [::]:80;
    server_name media.dropjdid.com;
    return 404; # managed by Certbot
}

```
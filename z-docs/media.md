```bash
# 1. Create media folder
sudo mkdir -p /var/www/media.dropjdid.com
sudo chown -R www-data:www-data /var/www/media.dropjdid.com
sudo chmod -R 775 /var/www/media.dropjdid.com

# 2. Create nginx config
sudo nano /etc/nginx/sites-available/media.dropjdid.com
```

Paste this first:

```nginx
server {
    listen 80;
    server_name media.dropjdid.com;

    root /var/www/media.dropjdid.com;

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        try_files $uri $uri/ =404;
    }
}
```

Then enable it:

```bash
sudo ln -sf /etc/nginx/sites-available/media.dropjdid.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Generate SSL:

```bash
sudo certbot --nginx -d media.dropjdid.com
```

After certbot finishes, edit again:

```bash
sudo nano /etc/nginx/sites-available/media.dropjdid.com
```

Use this final config:

```nginx
server {
    listen 80;
    server_name media.dropjdid.com;

    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name media.dropjdid.com;

    root /var/www/media.dropjdid.com;

    ssl_certificate /etc/letsencrypt/live/media.dropjdid.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/media.dropjdid.com/privkey.pem;

    location / {
        try_files $uri $uri/ =404;

        add_header Access-Control-Allow-Origin "*" always;
        add_header Access-Control-Allow-Methods "GET, OPTIONS" always;

        expires 30d;
        add_header Cache-Control "public";
    }

    location ~ /\. {
        deny all;
    }
}
```

Then:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

In NestJS `.env`:

```env
STORAGE_PATH=/var/www/media.dropjdid.com
STORAGE_PUBLIC_URL=https://media.dropjdid.com
```

Then rebuild/restart:

```bash
cd /home/jervi/projects/dropjdid-api/media-service
pnpm run build
sudo supervisorctl restart media-service
```

This is enough: create folder, serve it with Nginx, add SSL with Certbot, then make NestJS write files there.

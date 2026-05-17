
### Step 3: Temporary Nginx Port 80 Block
To get the SSL certificate, Nginx must serve the domain over HTTP first so Certbot can verify ownership.

1. Write the port 80 portion of the configuration to Nginx:
   ```bash
   sudo nano /etc/nginx/sites-available/api.dropjdid.com
   ```
2. Paste the HTTP server block:
   ```nginx
server {
    listen 80;
    server_name api.dropjdid.com;

    # Allow Let's Encrypt SSL renewals but block other access
    location ^~ /.well-known/acme-challenge/ {
        allow all;
    }
    location ~* ^/.well-known/ {
        return 403;
    }

    location / {
        proxy_pass http://localhost:18000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        proxy_buffer_size 128k;
        proxy_buffers 4 256k;
        proxy_busy_buffers_size 256k;
    }
}
   ```

---
3. Enable the site and restart Nginx:
   ```bash
   sudo ln -sf /etc/nginx/sites-available/api.dropjdid.com /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

### Step 4: Issue SSL Certificates
Run Certbot to request and generate your SSL certificates automatically using Nginx authentication:
```bash
sudo certbot --nginx -d api.dropjdid.com
```

```bash
sudo nginx -t
sudo systemctl reload nginx
```

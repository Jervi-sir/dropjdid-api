### Step 3: Temporary Nginx Port 80 Block

To get the SSL certificate, Nginx must serve the domain over HTTP first so Certbot can verify ownership.

1. Write the port 80 portion of the configuration to Nginx:
   ```bash
   sudo nano /etc/nginx/sites-available/ws.dropjdid.com
   ```
2. Paste the HTTP server block with WebSocket proxy headers:

   ```nginx
   server {
       listen 80;
       server_name ws.dropjdid.com;

       # Allow Let's Encrypt SSL renewals but block other access
       location ^~ /.well-known/acme-challenge/ {
           allow all;
       }
       location ~* ^/.well-known/ {
           return 403;
       }

       location / {
           proxy_pass http://localhost:18001;
           proxy_http_version 1.1;
           proxy_set_header Upgrade $http_upgrade;
           proxy_set_header Connection "Upgrade";
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
           proxy_set_header X-Forwarded-Proto $scheme;

           proxy_read_timeout 60s;
           proxy_send_timeout 60s;
       }
   }
   ```

---

3. Enable the site and restart Nginx:
   ```bash
   sudo ln -sf /etc/nginx/sites-available/ws.dropjdid.com /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

### Step 4: Issue SSL Certificates

Run Certbot to request and generate your SSL certificates automatically using Nginx authentication:

```bash
sudo certbot --nginx -d ws.dropjdid.com
```

```bash
sudo nginx -t
sudo systemctl reload nginx
```

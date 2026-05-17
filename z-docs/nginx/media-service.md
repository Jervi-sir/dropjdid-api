
sudo nano /etc/nginx/sites-available/upload.dropjdid.com

```nginx

go in the nginx

```

---
3. Enable the site and restart Nginx:
   ```bash
   sudo ln -sf /etc/nginx/sites-available/upload.dropjdid.com /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

### Step 4: Issue SSL Certificates
Run Certbot to request and generate your SSL certificates automatically using Nginx authentication:
```bash
sudo certbot --nginx -d upload.dropjdid.com
```

```bash
sudo nginx -t
sudo systemctl reload nginx
```


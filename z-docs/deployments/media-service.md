# 📦 NestJS Media Service — Supervisor Deployment & Process Management

This documentation guides you through setting up **Supervisor** to manage, monitor, and run the **NestJS Media Service** on **Port 8001** in a production environment.

---

## 🏗️ Process Architecture Flow

Using Supervisor ensures that the NestJS Media Service starts automatically upon system reboot, handles crashes by immediately restarting, and manages console logs safely.

```mermaid
graph TD
    SystemBoot[🔌 Server Boot / Restart] --> Supervisor[⚙️ Supervisor Service]
    Supervisor -- Managed Process: Port 8001 --> NestApp[📦 Node.js / dist/main.js]
    NestApp -- Reads Config --> EnvFile[📄 .env File]
    Nginx[🔒 Nginx Reverse Proxy] -- Reverse Proxies to --> NestApp
    
    style Supervisor fill:#f9f,stroke:#333,stroke-width:2px
    style NestApp fill:#bfb,stroke:#333,stroke-width:2px
    style EnvFile fill:#ffb,stroke:#333,stroke-width:2px
```

---

## 📄 Supervisor Configuration

Below is the production-ready Supervisor configuration block for the Media Service.

> [!IMPORTANT]
> - Ensure you run `npm run build` or `pnpm build` first to compile the TypeScript code to JavaScript (`dist/main.js`).
> - Setting the `directory` option is critical! It ensures that the application executes in its root directory, which allows it to correctly find the `.env` file and mount files into the uploads directory (`/uploads`).

### `media-service.conf`

```ini
[program:media-service]
process_name=%(program_name)s_%(process_num)02d
command=node dist/main.js
directory=/var/www/dropjdid/servers/media-service
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/media-service-stdout.log
stderr_logfile=/var/log/supervisor/media-service-stderr.log
environment=NODE_ENV="production",PORT="8001"
stopasgroup=true
killasgroup=true
stopwaitsecs=10
```

---

## 🛠️ Step-by-Step Installation & Setup

Follow these steps to deploy and activate the Supervisor configuration on your target server.

### Step 1: Build the Application
On your production server, compile the NestJS codebase into production JavaScript:
```bash
cd /var/www/dropjdid/servers/media-service
# Install production dependencies
pnpm install --frozen-lockfile
# Compile TypeScript to JavaScript
pnpm run build
```

### Step 2: Configure Folder Permissions
The Media Service handles file uploads (storing files in `/uploads` or `/storage` depending on config). The executing user (e.g. `www-data`) must own these directories to avoid permission errors:
```bash
sudo chown -R www-data:www-data /var/www/dropjdid/servers/media-service
sudo chmod -R 775 /var/www/dropjdid/servers/media-service/uploads
```

### Step 3: Write Supervisor Config File
Create the Supervisor configuration file inside the `/etc/supervisor/conf.d/` directory:
```bash
sudo nano /etc/supervisor/conf.d/media-service.conf
```
Paste the content of **media-service.conf** shown above.

### Step 4: Create Log Files
Create the log files and assign ownership to the executing user:
```bash
sudo touch /var/log/supervisor/media-service-stdout.log
sudo touch /var/log/supervisor/media-service-stderr.log
sudo chown www-data:www-data /var/log/supervisor/media-service*.log
```

### Step 5: Read and Start the Configuration
Instruct Supervisor to scan for the new file, update configurations, and start the NestJS process:
```bash
sudo supervisorctl reread
sudo supervisorctl update
```

---

## 🔄 Management & Monitoring Commands

Use these commands to manage the NestJS Media Service:

| Command | Action |
|:---|:---|
| `sudo supervisorctl status` | View the status of all managed programs |
| `sudo supervisorctl restart media-service:*` | Restart the Media Service (Run after deploying new code!) |
| `sudo supervisorctl stop media-service:*` | Stop the NestJS application |
| `sudo supervisorctl start media-service:*` | Start the NestJS application |

> [!TIP]
> **Production Deployment Hook:** When setting up your CI/CD pipeline or runner, add the following script sequence for a zero-downtime or minimal-downtime release:
> ```bash
> pnpm install --frozen-lockfile
> pnpm run build
> sudo supervisorctl restart media-service:*
> ```

---

## 🔍 Troubleshooting

### 🛑 Issue: Node path not found
If Supervisor fails to start because it cannot find the `node` binary, find the absolute path of `node` using:
```bash
which node
# Output is usually: /usr/bin/node or /usr/local/bin/node
```
Then, update your `command` in `media-service.conf` to use the absolute path:
```ini
command=/usr/bin/node dist/main.js
```

### 🛑 Issue: Database Connection Failures
If the Media Service fails to boot with a database connection error, ensure that:
1. The Postgres database is running and accepting connections.
2. The environment connection strings in `/var/www/dropjdid/servers/media-service/.env` match the production database:
   ```ini
   DATABASE_URL="postgresql://user:password@127.0.0.1:5432/db_name"
   ```

### 🛑 Issue: Log Directory Permission Error
If you see a `permission denied` or `could not open log file` error in Supervisor logs:
```bash
sudo chown -R www-data:www-data /var/log/supervisor/
```

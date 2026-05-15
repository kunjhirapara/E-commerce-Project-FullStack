# Deploying ProjectStore with Portainer + Nginx Proxy Manager

End-to-end guide for deploying this PHP e-commerce app to a single Linux VPS using:

- **Portainer** — manages Docker stacks via web UI
- **Nginx Proxy Manager (NPM)** — terminates TLS, proxies traffic to the app
- **Cloudflare** — DNS for `projectstore.kunjdeveloper.me`

Only **three ports** are exposed publicly on the server: `80`, `443`, `81`.
Everything else (app, php-fpm, MySQL, phpMyAdmin) stays on a private Docker
network and is only reachable through NPM or an SSH tunnel.

```
                  Internet
                     │
                  Cloudflare (DNS, optional proxy)
                     │
        ┌────────────┴────────────┐
        │      Server (UFW)       │
        │   open: 80, 443, 81     │
        │                         │
        │   ┌─────────────────┐   │
        │   │ NPM container   │── ── 80/443 (HTTPS public)
        │   │                 │── ── 81     (admin UI)
        │   └────────┬────────┘   │
        │            │ proxy net  │
        │   ┌────────┴────────┐   │
        │   │ web (nginx)     │   │
        │   └────────┬────────┘   │
        │            │ internal   │
        │   ┌────────┴────────┐   │
        │   │ app (php-fpm)   │   │
        │   └────────┬────────┘   │
        │            │            │
        │   ┌────────┴────────┐   │
        │   │ db (mysql 8)    │   │ ← no host port, internal only
        │   └─────────────────┘   │
        │                         │
        │   phpMyAdmin → 127.0.0.1:8091 (SSH tunnel only)
        └─────────────────────────┘
```

---

## 1. Prerequisites

On a fresh Ubuntu/Debian server, you need:

- A non-root sudo user
- Docker Engine + Docker Compose plugin
- An SSH key configured for login (disable password auth)
- The DNS A record `projectstore.kunjdeveloper.me` → server's public IP
  (already done in Cloudflare)

### 1.1 Install Docker

```bash
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER
# Log out and back in for the group change to take effect
```

Verify:

```bash
docker --version
docker compose version
```

### 1.2 Lock down the firewall

We only want ports `22` (SSH), `80`, `443`, and `81` exposed.

```bash
sudo apt update && sudo apt install -y ufw
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp        # SSH
sudo ufw allow 80/tcp        # HTTP (NPM)
sudo ufw allow 443/tcp       # HTTPS (NPM)
sudo ufw allow 81/tcp        # NPM admin UI
sudo ufw enable
sudo ufw status verbose
```

> Note: Docker bypasses UFW by default. The compose file in this repo
> never publishes the DB port to `0.0.0.0`, and phpMyAdmin is bound to
> `127.0.0.1` only — so Docker's bypass does not expose them.

---

## 2. Cloudflare DNS

In your Cloudflare dashboard for `kunjdeveloper.me`:

| Type | Name           | Content (IPv4)   | Proxy status |
|------|----------------|------------------|--------------|
| A    | `projectstore` | `<server IP>`    | DNS only (gray cloud) initially |

**Important:** keep proxy **off (gray cloud)** until NPM has issued the
Let's Encrypt cert via HTTP-01. Once HTTPS works end-to-end, you can
turn the orange cloud on. If you do, set **SSL/TLS → Overview → Full
(strict)** in Cloudflare so it validates the NPM cert.

---

## 3. Create the shared Docker network

Both NPM and the app's `web` container must share a network so NPM can
reach `web` by its container name. We name it `proxy`.

```bash
docker network create proxy
```

---

## 4. Deploy Portainer

Run Portainer CE in its own stack. It exposes its UI on port `9443`,
which we'll only ever reach via SSH tunnel — **do not** open `9443`
in UFW.

```bash
docker volume create portainer_data

docker run -d \
  --name portainer \
  --restart=always \
  -p 127.0.0.1:9443:9443 \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v portainer_data:/data \
  portainer/portainer-ce:latest
```

Access it via SSH tunnel from your laptop:

```bash
ssh -L 9443:127.0.0.1:9443 youruser@yourserver
# Then open https://localhost:9443 in your browser
```

On first load, create the admin user and choose **"Get Started"** →
the local Docker environment.

---

## 5. Deploy Nginx Proxy Manager

NPM is the only thing on the host that listens on `80`/`443`/`81`.

In Portainer:

1. **Stacks → Add stack**
2. Name: `npm`
3. Web editor — paste:

```yaml
services:
  npm:
    image: jc21/nginx-proxy-manager:latest
    container_name: npm
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
      - "81:81"
    volumes:
      - npm_data:/data
      - npm_letsencrypt:/etc/letsencrypt
    networks:
      - proxy

volumes:
  npm_data:
  npm_letsencrypt:

networks:
  proxy:
    external: true
```

4. **Deploy the stack**

Open `http://<server-ip>:81` (or, better, tunnel it: `ssh -L 81:127.0.0.1:81 ...`).

Default credentials:

- Email: `admin@example.com`
- Password: `changeme`

NPM forces a password change on first login — pick a strong one.

> After confirming everything works you can remove `- "81:81"` from
> the ports list and reach the admin UI **only** via SSH tunnel. That
> drops your public surface to just `80` and `443`.

---

## 6. Deploy the application stack

### 6.1 Get the code on the server

```bash
sudo mkdir -p /opt/projectstore
sudo chown $USER:$USER /opt/projectstore
cd /opt/projectstore
git clone https://github.com/<your-user>/E-commerce-Project-FullStack.git .
```

### 6.2 Create the `.env` file

```bash
cp .env.example .env
# Generate strong, unique passwords:
openssl rand -base64 32   # use one for DB_PASSWORD
openssl rand -base64 32   # use a different one for MYSQL_ROOT_PASSWORD
nano .env
```

Make sure:

- `BASE_URL=https://projectstore.kunjdeveloper.me`
- `ADMIN_BASE_URL=https://projectstore.kunjdeveloper.me/admin`
- `DB_PASSWORD` and `MYSQL_ROOT_PASSWORD` are long, random, and **different**

Lock the file down so only your user can read it:

```bash
chmod 600 .env
```

### 6.3 Deploy via Portainer

In Portainer:

1. **Stacks → Add stack**
2. Name: `projectstore`
3. Build method: **Repository** *(recommended)* or **Upload**
   - If Repository — point it at your Git URL, set the compose path to
     `docker-compose.yml`, and tick **Authentication** if the repo is private.
   - If Upload — upload your local copy of the project files.
4. **Environment variables** — click **Load variables from .env file**
   and paste the contents of your `.env` (or add them one by one).
5. **Deploy the stack.**

Portainer will build the two images (`projectstore-app` from `Dockerfile`
and `projectstore-web` from `Dockerfile.nginx`), start MySQL with a
healthcheck, then bring up the app and web containers.

Check container logs in Portainer to confirm:

- `db` — `ready for connections`
- `app` — `NOTICE: ready to handle connections`
- `web` — `start worker processes`

---

## 7. Point NPM at the app

Back in NPM (`http://<server-ip>:81`):

1. **Hosts → Proxy Hosts → Add Proxy Host**
2. **Details** tab:
   - Domain Names: `projectstore.kunjdeveloper.me`
   - Scheme: `http`
   - Forward Hostname / IP: `projectstore-web`  *(the container name)*
   - Forward Port: `80`
   - Tick **Block Common Exploits**
   - Tick **Websockets Support**
3. **SSL** tab:
   - SSL Certificate: **Request a new SSL Certificate**
   - Tick **Force SSL**
   - Tick **HTTP/2 Support**
   - Tick **HSTS Enabled**
   - Email: yours
   - Agree to Let's Encrypt ToS
4. **Save**.

NPM will hit the Let's Encrypt HTTP-01 endpoint on port `80` and issue
the cert. If it fails: confirm Cloudflare proxy is **off** (gray cloud),
DNS has propagated (`dig projectstore.kunjdeveloper.me`), and UFW allows
`80/tcp`.

After it succeeds, open `https://projectstore.kunjdeveloper.me` — you
should see the site.

### 7.1 (Optional) Re-enable Cloudflare proxy

Once HTTPS works:

1. Cloudflare DNS → flip `projectstore` to **Proxied (orange cloud)**.
2. Cloudflare → SSL/TLS → Overview → **Full (strict)**.
3. Cloudflare → SSL/TLS → Edge Certificates → enable **Always Use HTTPS**.

For Let's Encrypt renewals to keep working through the CF proxy, NPM
needs the HTTP-01 endpoint reachable. Either:

- Leave CF proxy on — CF passes through `/.well-known/acme-challenge/*`
  to the origin, so this generally just works; or
- If renewals start failing, flip proxy off for an hour while NPM
  renews, then turn it back on.

---

## 8. Database hardening

### 8.1 What's already secure in this stack

- **No host port**: the `db` service has no `ports:` directive, so
  MySQL is only reachable from other containers on the `internal`
  Docker network. The internet cannot connect to port `3306` at all.
- **App user, not root**: the PHP app connects as `${DB_USER}`, which
  the `mysql:8.0` entrypoint provisions with privileges **only on
  `${DB_NAME}`** — it can't touch other databases.
- **Strong passwords**: enforced by your `.env` (use `openssl rand -base64 32`).
- **`expose_php=Off`**: set in the PHP image so `X-Powered-By` doesn't leak.

### 8.2 Recommended hardening after first deploy

Run these once the stack is up. They tighten the default MySQL install.

```bash
docker exec -it projectstore-db mysql -uroot -p
```

Then inside the MySQL prompt:

```sql
-- Remove the anonymous account if it exists
DELETE FROM mysql.user WHERE User='';

-- Disallow root login from anything except localhost
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost','127.0.0.1','::1');

-- Drop the test DB if present
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';

-- Verify the app user is scoped to its own DB only
SHOW GRANTS FOR 'ecommerce_user'@'%';

FLUSH PRIVILEGES;
```

### 8.3 Daily backups

Add a cron job on the host:

```bash
sudo mkdir -p /opt/backups/projectstore
sudo chown $USER:$USER /opt/backups/projectstore

crontab -e
```

Add:

```cron
0 3 * * * docker exec projectstore-db sh -c 'exec mysqldump --single-transaction -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' | gzip > /opt/backups/projectstore/db-$(date +\%F).sql.gz
0 4 * * 0 find /opt/backups/projectstore -name 'db-*.sql.gz' -mtime +30 -delete
```

The first line dumps the DB nightly at 03:00. The second deletes
backups older than 30 days every Sunday at 04:00.

---

## 9. Accessing phpMyAdmin (SSH tunnel)

phpMyAdmin is bound to `127.0.0.1:8091` on the server — **not** reachable
from the internet.

From your laptop:

```bash
ssh -L 8091:127.0.0.1:8091 youruser@yourserver
```

While that SSH session is open, browse to:

```
http://localhost:8091
```

Log in as `ecommerce_user` with your `DB_PASSWORD`, or as `root` with
`MYSQL_ROOT_PASSWORD` for full access.

> If you don't think you'll ever need phpMyAdmin, just delete the
> `phpmyadmin` service from `docker-compose.yml` — the host won't
> bind `8091` at all and you'll have one less attack surface.

---

## 10. Updates and re-deploys

When you push new code:

```bash
cd /opt/projectstore
git pull
```

In Portainer → **Stacks → projectstore → Editor → Update the stack**,
tick **Re-pull image and redeploy** (or **Force rebuild** if using
local Dockerfiles). Portainer will rebuild the `app` and `web` images
and restart only those containers — `db` keeps running so the data
volume is untouched.

For an even faster update, SSH in and run:

```bash
cd /opt/projectstore
git pull
docker compose --env-file .env up -d --build app web
```

---

## 11. Troubleshooting checklist

| Symptom | Check |
|---------|-------|
| Cert issuance fails | Cloudflare proxy is **off** during first issuance; `dig` resolves to server IP; UFW allows `80/tcp`; nothing else is bound to `80` on the host (`sudo ss -tlnp \| grep :80`) |
| 502 Bad Gateway from NPM | `projectstore-web` is on the `proxy` network: `docker network inspect proxy` |
| 502/504 from nginx in container | `app` container is healthy; `fastcgi_pass app:9000` in `docker/nginx/app.conf` resolves: `docker exec projectstore-web getent hosts app` |
| PHP can't connect to DB | `${DB_HOST}` is `db` (not `localhost`); db healthcheck is passing; `${DB_USER}` / `${DB_PASSWORD}` match `.env`; recreating the `db_data` volume is the only way to change `MYSQL_ROOT_PASSWORD` after the first run |
| Mixed-content / wrong base URL | `BASE_URL` in `.env` uses `https://`; NPM has **Force SSL** enabled so `X-Forwarded-Proto: https` reaches the app |
| Uploads fail | Volumes `uploads_data`, `user_uploads_data`, `admin_uploads_data` exist; `client_max_body_size 12M` in nginx; `upload_max_filesize=10M` in PHP |

---

## 12. Summary of what's exposed

| Port  | Bound to        | Purpose                                  |
|-------|-----------------|------------------------------------------|
| 22    | `0.0.0.0`       | SSH                                      |
| 80    | `0.0.0.0`       | NPM HTTP (redirects to 443 + ACME)       |
| 443   | `0.0.0.0`       | NPM HTTPS (public site)                  |
| 81    | `0.0.0.0`       | NPM admin UI (close after setup)         |
| 8091  | `127.0.0.1`     | phpMyAdmin (SSH tunnel only)             |
| 9443  | `127.0.0.1`     | Portainer (SSH tunnel only)              |
| 3306  | *(not bound)*   | MySQL — internal Docker network only     |
| 9000  | *(not bound)*   | php-fpm — internal Docker network only   |

After NPM is fully configured, remove the `81:81` host binding so port
`81` is also tunnel-only. You then have **only `22`, `80`, `443`**
publicly reachable.

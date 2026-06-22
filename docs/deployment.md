# Deployment

This project can be hosted as one Docker web service:

- PHP web app: `/`
- Flutter web app: `/mobile`
- PHP REST API: `/api`

## Database

The app uses MySQL. Create a hosted MySQL database first.
On first start, the Docker container automatically imports:

```text
database/food_ordering_startup.sql
```

The import runs only when the `users` table does not exist yet.

If the MySQL database is on Railway, do not use `mysql.railway.internal`
on Render. That is Railway's private network hostname and only works from
Railway services. In Railway, open the MySQL service networking/connect
settings and use the public TCP proxy host and port instead.

Use the hosted database values as environment variables:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
APP_KEY
ADMIN_NAME
ADMIN_EMAIL
ADMIN_PHONE
ADMIN_PASSWORD
```

- `APP_KEY` should be a long random secret used to sign mobile API bearer tokens.
- The optional `ADMIN_*` variables create the first active admin account on initial database bootstrap.
- The startup schema ships with blocked sample users for sample data only; it does not expose a public admin login.

## Render Deployment

1. Push this folder to GitHub, GitLab, or Bitbucket.
2. Open Render and create a new Blueprint from the repository.
3. Render will read `render.yaml` and create the Docker web service.
4. Fill the database, `APP_KEY`, and `ADMIN_*` environment variables in the Render Dashboard.
5. Deploy.

After deployment:

- Open `https://YOUR-SERVICE.onrender.com/` for the PHP web app.
- Open `https://YOUR-SERVICE.onrender.com/mobile/` for the Flutter web app.
- The mobile API is available at `https://YOUR-SERVICE.onrender.com/api/`.

## Mobile APK Build

For a production Android build, point Flutter to the hosted API:

```powershell
flutter build apk --release --dart-define=API_BASE_URL=https://YOUR-SERVICE.onrender.com/api
```

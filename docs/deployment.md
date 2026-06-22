# Deployment

This project can be hosted as one Docker web service:

- PHP web app: `/`
- Flutter web app: `/mobile`
- PHP REST API: `/api`

## Database

The app uses MySQL. Create a hosted MySQL database first, then import:

```text
database/food_ordering_app.sql
```

Use the hosted database values as environment variables:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

## Render Deployment

1. Push this folder to GitHub, GitLab, or Bitbucket.
2. Open Render and create a new Blueprint from the repository.
3. Render will read `render.yaml` and create the Docker web service.
4. Fill the database environment variables in the Render Dashboard.
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

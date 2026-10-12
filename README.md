Repo for course project.

E-Shop for renting physical servers. WIP.

### **Notes**



### ***TO DO:***

- [x] Provide a repeatable local development stack with Docker Compose.
- [ ] Expand automated coverage for registration, checkout, and admin workflows.

### Local email testing with Mailpit

Mailpit captures application emails locally instead of delivering them to real recipients.

1. Start Docker Desktop and run `docker compose up -d mailpit` from the project root.
2. Add the `MAILER_*` values from `.env.example` to the existing local `.env` file.
3. Open the Mailpit inbox at `http://localhost:8025`. The application connects to SMTP at `127.0.0.1:1025`.

Stop Mailpit with `docker compose stop mailpit`. The inbox is retained in a Docker volume.

### Run the full development stack with Docker

Docker Desktop must be running. From the project root, run:

```powershell
docker compose up --build
```

Open the application at `http://localhost:8080`, phpMyAdmin at `http://localhost:8081`, and Mailpit at `http://localhost:8025`. MySQL is available to host tools at `127.0.0.1:3307`; app and phpMyAdmin connect to the `db` service internally. Tailwind runs in watch mode and updates `public/css/tailwind-output.css` when source files change.

By default, the Docker app sends mail to Mailpit. To send through Gmail instead, set `DOCKER_MAILER_HOST=smtp.gmail.com`, `DOCKER_MAILER_PORT=587`, `DOCKER_MAILER_USERNAME` to the Gmail address, `DOCKER_MAILER_PASSWORD` to its app password, and `DOCKER_MAILER_ENCRYPTION=tls` in the ignored local `.env`, then recreate the app with `docker compose up -d app`. Gmail delivery bypasses the Mailpit inbox.

Run the PHP test suite inside the app container with `docker compose exec app composer test`. The MySQL order integration test is skipped unless a separate `server_eshop_test` database is configured.

MySQL imports `database/server_eshop.sql` with the sample catalog and accounts only when its data volume is first created. To stop the services without deleting database or inbox data, run `docker compose down`. Do not use `docker compose down -v` unless you intend to erase both data volumes.





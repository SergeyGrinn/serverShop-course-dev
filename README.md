Repo for course project.

E-Shop for renting physical servers. WIP.

### **Notes**



### ***TO DO:***

- [x] Provide a repeatable local development stack with Docker Compose.
- [ ] Expand automated coverage for registration, checkout, and admin workflows.
- [ ] Verify protected order/checkout pages and API calls cannot be used after logout via browser back navigation.
- [ ] Reproduce and identify the reported DevTools console error in a clean browser profile without extensions.

### Product behavior decisions

- Keep the cart after an order is cancelled so the customer can edit it and place a new order. Do not clear the cart automatically on cancellation.

### Local email testing with Mailpit

Mailpit captures application emails locally instead of delivering them to real recipients.

1. Start Docker Desktop and run `docker compose up -d mailpit` from the project root.
2. Add the `MAILER_*` values from `.env.example` to the existing local `.env` file.
3. Open the Mailpit inbox at `http://localhost:8026`. Host PHP connects to SMTP at `127.0.0.1:1026`; the Docker app connects internally at `mailpit:1025`.

Stop Mailpit with `docker compose stop mailpit`. The inbox is retained in a Docker volume.

### Run the full development stack with Docker

Docker Desktop must be running. From the project root, run:

```powershell
docker compose up -d --build
```

Open the application at `http://localhost:8080`, phpMyAdmin at `http://localhost:8081`, and Mailpit at `http://localhost:8026`. The app and tools are bound to localhost; MySQL is available to host tools at `127.0.0.1:3307`. The app sends to Mailpit at the internal address `mailpit:1025`; host tools such as XAMPP should use `127.0.0.1:1026`. The app and phpMyAdmin connect to the `db` service internally. Tailwind runs in watch mode and updates `public/css/tailwind-output.css` when source files change.

To demo on another computer, copy the project files there (do not copy `.env`), install and start Docker Desktop, then run `docker compose up -d --build` from the project root. No XAMPP, host PHP, Composer, Node.js, or MySQL installation is needed. Open `http://localhost:8080` on that computer. The sample database is initialized from `database/server_eshop.sql` on its first start.

By default, the Docker app sends mail to Mailpit. To send through Gmail instead, set `DOCKER_MAILER_HOST=smtp.gmail.com`, `DOCKER_MAILER_PORT=587`, `DOCKER_MAILER_USERNAME` to the Gmail address, `DOCKER_MAILER_PASSWORD` to its app password, and `DOCKER_MAILER_ENCRYPTION=tls` in the ignored local `.env`, then recreate the app with `docker compose up -d app`. Gmail delivery bypasses the Mailpit inbox.

Run the PHP test suite inside the app container with `docker compose exec app composer test`. The MySQL order integration test is skipped unless a separate `server_eshop_test` database is configured.

MySQL imports `database/server_eshop.sql` with the sample catalog and accounts only when its data volume is first created. To stop the services without deleting database or inbox data, run `docker compose down`. Do not use `docker compose down -v` unless you intend to erase both data volumes.





Repo for course project.

E-Shop for renting physical servers. WIP.

### **Notes**



### ***TO DO:***

- [ ] Expand automated coverage for registration, checkout, and admin workflows.
- [ ] Reproduce and identify the reported DevTools console error in a clean browser profile without extensions.

### Product behavior decisions

- Keep the cart after an order is cancelled so the customer can edit it and place a new order. Do not clear the cart automatically on cancellation.

### Local email testing with Mailpit

Mailpit is optional: the app and database run without `.env` or any Mailpit setup. Mailpit only captures outgoing application emails for local testing instead of delivering them to real recipients.

From the project root, start its Compose profile:

```powershell
docker compose --profile mail up -d mailpit
```

The Docker app already defaults to SMTP host `mailpit` on the internal port `1025`; no `.env` changes are needed. Open the inbox at `http://localhost:8026`. To test mail from host PHP/XAMPP, set `MAILER_HOST=127.0.0.1` and `MAILER_PORT=1026` in `.env`. Without Mailpit or a real SMTP provider, email-dependent actions cannot deliver mail, but the rest of the app remains usable.

Stop Mailpit with `docker compose stop mailpit`. Its inbox is retained in a Docker volume.

### Run the full development stack with Docker

Docker Desktop must be running. From the project root, run:

```powershell
docker compose up -d --build
```

Open the application at `http://127.0.0.1:8080` and phpMyAdmin at `http://127.0.0.1:8081`. The app and tools are bound to localhost; MySQL is available to host tools at `127.0.0.1:3307`. Mailpit is available separately through the optional `mail` profile at `http://localhost:8026`. Tailwind runs in watch mode and updates `public/css/tailwind-output.css` when source files change.

To demo on another computer, copy the project files there (do not copy `.env`), install and start Docker Desktop, then run `docker compose up -d --build` from the project root. No XAMPP, host PHP, Composer, Node.js, or MySQL installation is needed. Open `http://localhost:8080` on that computer. The sample database is initialized from `database/server_eshop.sql` on its first start.

By default, the Docker app sends mail to Mailpit. To send through Gmail instead, set `DOCKER_MAILER_HOST=smtp.gmail.com`, `DOCKER_MAILER_PORT=587`, `DOCKER_MAILER_USERNAME` to the Gmail address, `DOCKER_MAILER_PASSWORD` to its app password, and `DOCKER_MAILER_ENCRYPTION=tls` in the ignored local `.env`, then recreate the app with `docker compose up -d app`. Gmail delivery bypasses the Mailpit inbox.

Run the PHP test suite inside the app container with `docker compose exec app composer test`. The MySQL order integration test is skipped unless a separate `server_eshop_test` database is configured.

MySQL imports `database/server_eshop.sql` with the sample catalog and accounts only when its data volume is first created. To stop the services without deleting database or inbox data, run `docker compose down`. Do not use `docker compose down -v` unless you intend to erase both data volumes.



---

## Versija latviešu valodā

Kursa projekta repozitorijs.

E-veikals fizisku serveru īrei. Projekts tiek izstrādāts.

### Piezīmes



### ***TO DO:***

- [ ] Paplašināt automatizēto testu pārklājumu reģistrācijai, pasūtījuma noformēšanai un administratora darbplūsmām.
- [ ] Atkārtot un izpētīt DevTools konsoles kļūdu tīrā pārlūka profilā bez paplašinājumiem.

### Produkta darbības lēmumi

- Pēc pasūtījuma atcelšanas saglabāt grozu, lai klients varētu to rediģēt un veikt jaunu pasūtījumu. Atcelšanas laikā grozu automātiski neiztīrīt.

### Vietējā e-pasta testēšana ar Mailpit

Mailpit nav obligāts: lietotne un datubāze darbojas bez `.env` faila un Mailpit konfigurācijas. Mailpit ir paredzēts tikai lietotnes izejošo e-pastu pārtveršanai vietējās testēšanas laikā, nevis to piegādei īstiem adresātiem.

Projekta saknes mapē palaid tā Compose profilu:

```powershell
docker compose --profile mail up -d mailpit
```

Docker lietotne pēc noklusējuma izmanto SMTP resursdatoru `mailpit` un iekšējo portu `1025`; `.env` izmaiņas nav vajadzīgas. Iesūtni atver vietnē `http://localhost:8026`. Lai testētu e-pastu no resursdatora PHP/XAMPP, `.env` failā iestati `MAILER_HOST=127.0.0.1` un `MAILER_PORT=1026`. Bez Mailpit vai īsta SMTP pakalpojuma darbībām, kurām vajadzīga e-pasta piegāde, e-pasts netiks nosūtīts, taču pārējā lietotne būs izmantojama.

Mailpit apturi ar `docker compose stop mailpit`. Tā iesūtne tiek saglabāta Docker sējumā.

### Pilnas izstrādes vides palaišana ar Docker

Docker Desktop ir jādarbojas. Projekta saknes mapē palaid:

```powershell
docker compose up -d --build
```

Lietotni atver vietnē `http://127.0.0.1:8080`, bet phpMyAdmin — vietnē `http://127.0.0.1:8081`. Lietotne un rīki ir pieejami tikai lokālajā datorā; MySQL resursdatora rīkiem ir pieejams adresē `127.0.0.1:3307`. Mailpit var palaist atsevišķi ar neobligāto `mail` profilu; tā adrese ir `http://localhost:8026`. Tailwind darbojas novērošanas režīmā un, mainoties avota failiem, atjaunina `public/css/tailwind-output.css`.

Lai demonstrētu projektu citā datorā, pārkopē uz to projekta failus (nekopē `.env`), instalē un palaid Docker Desktop, pēc tam projekta saknes mapē izpildi `docker compose up -d --build`. XAMPP, PHP, Composer, Node.js vai MySQL instalācija datorā nav vajadzīga. Lietotni atver šajā datorā vietnē `http://localhost:8080`. Pirmajā palaišanas reizē piemēra datubāze tiek inicializēta no `database/server_eshop.sql`.

Pēc noklusējuma Docker lietotne sūta e-pastus uz Mailpit. Lai to vietā izmantotu Gmail, ignorētajā lokālajā `.env` failā iestati `DOCKER_MAILER_HOST=smtp.gmail.com`, `DOCKER_MAILER_PORT=587`, `DOCKER_MAILER_USERNAME` uz Gmail adresi, `DOCKER_MAILER_PASSWORD` uz lietotnes paroli un `DOCKER_MAILER_ENCRYPTION=tls`, pēc tam pārveido lietotnes konteineru ar `docker compose up -d app`. Izmantojot Gmail, e-pasti Mailpit iesūtnē nenonāks.

PHP testu kopu lietotnes konteinerā var palaist ar `docker compose exec app composer test`. MySQL integrācijas tests pasūtījumam tiek izlaists, ja nav konfigurēta atsevišķa `server_eshop_test` datubāze.

MySQL no `database/server_eshop.sql` ielādē piemēra katalogu un kontus tikai tad, kad pirmo reizi tiek izveidots datu sējums. Lai apturētu pakalpojumus, nedzēšot datubāzes vai iesūtnes datus, palaid `docker compose down`. Nelieto `docker compose down -v`, ja vien nevēlies dzēst abus datu sējumus.





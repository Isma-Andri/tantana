# Tantana

> Diplomatic dossier management platform for state organizations and international bodies.

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

---

## What is Tantana?

Tantana is a web application for managing diplomatic dossiers. It centralizes documents, deadlines, approvals, and discussions in one secure place. It is designed for ministries, embassies, and international organizations.

## Features

- **Dossier management** — Create, edit, and track diplomatic files
- **Approval workflow** — Draft → Review → Signed
- **File attachments** — Upload PDF, PPT, and other documents
- **Access control** — Share dossiers with specific users and roles
- **Comments** — Discuss directly inside a dossier
- **Actions** — Assign tasks to collaborators with deadlines
- **Activity log** — See who did what and when
- **PDF export** — Export a full dossier as a PDF
- **Admin panel** — Manage users and roles

## Tech Stack

| Layer      | Technology        |
|------------|-------------------|
| Backend    | PHP 8.1+ (native) |
| Database   | MySQL 8.0+        |
| Frontend   | HTML, Tailwind CSS, Vanilla JS |
| Fonts      | Source Serif 4, Inter (Google Fonts) |

## Getting Started

### Requirements

- PHP 8.1+
- MySQL 8.0+
- A local web server (Apache, Nginx, or `php -S`)

### Installation

1. **Clone the repo**
   ```bash
   git clone https://github.com/Isma-Andri/tantana.git
   cd tantana
   ```

2. **Set up the database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE tantana_new;"
   mysql -u root -p tantana_new < tantana_new.sql
   ```

3. **Configure the connection**

   Edit `config/database.php` and set your MySQL credentials.

4. **Start the server**
   ```bash
   php -S localhost:5000 -t public/
   ```

5. **Open the app**

   Go to `http://localhost:5000` in your browser.

## Project Structure

```
tantana/
├── config/          # Database config
├── controllers/     # Request handlers
├── models/          # Database models (PDO)
├── public/          # Entry point (index.php) + assets
│   └── img/         # Images
├── views/           # PHP templates
│   ├── admin/       # Admin panel
│   ├── auth/        # Login, register
│   ├── partials/    # Header, footer, navbar
│   └── projets/     # Dossier views
├── docs/            # Changelog and documentation
└── tantana_new.sql  # Database schema
```

## Contributing

1. Fork the repository
2. Create a branch: `git checkout -b feat/your-feature`
3. Commit your changes: `git commit -m "feat: add your feature"`
4. Push and open a pull request

## License

MIT — see [LICENSE](LICENSE) for details.

---

Built by [Ismaël Andrimalala](https://github.com/Isma-Andri).

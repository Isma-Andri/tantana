# Tantana

> Plateforme de gestion de dossiers diplomatiques pour les organisations étatiques et les organismes internationaux.

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)
![Licence](https://img.shields.io/badge/licence-MIT-green)

---

## C'est quoi Tantana ?

Tantana est une application web de gestion de dossiers diplomatiques. Elle centralise les documents, les échéances, les approbations et les discussions en un seul endroit sécurisé. Elle est conçue pour les ministères, les ambassades et les organisations internationales.

## Fonctionnalités

- **Gestion des dossiers** — Créer, modifier et suivre les dossiers diplomatiques
- **Workflow d'approbation** — Brouillon → Révision → Signé
- **Pièces jointes** — Déposer des fichiers PDF, PPT et autres documents
- **Contrôle d'accès** — Partager un dossier avec des utilisateurs et rôles précis
- **Commentaires** — Discuter directement dans un dossier
- **Actions** — Assigner des tâches à des collaborateurs avec des échéances
- **Fil d'activité** — Voir qui a fait quoi et quand
- **Export PDF** — Exporter un dossier complet en PDF
- **Panneau admin** — Gérer les utilisateurs et les rôles

## Stack technique

| Couche     | Technologie             |
|------------|-------------------------|
| Backend    | PHP 8.1+ (natif)        |
| Base de données | MySQL 8.0+         |
| Frontend   | HTML, Tailwind CSS, JS Vanilla |
| Polices    | Source Serif 4, Inter (Google Fonts) |

## Démarrage rapide

### Prérequis

- PHP 8.1+
- MySQL 8.0+
- Un serveur web local (Apache, Nginx, ou `php -S`)

### Installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/Isma-Andri/tantana.git
   cd tantana
   ```

2. **Créer la base de données**
   ```bash
   mysql -u root -p -e "CREATE DATABASE tantana_new;"
   mysql -u root -p tantana_new < tantana_new.sql
   ```

3. **Configurer la connexion**

   Modifier `config/database.php` et renseigner vos identifiants MySQL.

4. **Lancer le serveur**
   ```bash
   php -S localhost:5000 -t public/
   ```

5. **Ouvrir l'application**

   Aller sur `http://localhost:5000` dans votre navigateur.

## Structure du projet

```
tantana/
├── config/          # Configuration de la base de données
├── controllers/     # Contrôleurs des requêtes
├── models/          # Modèles de données (PDO)
├── public/          # Point d'entrée (index.php) + assets
│   └── img/         # Images
├── views/           # Templates PHP
│   ├── admin/       # Panneau d'administration
│   ├── auth/        # Connexion, inscription
│   ├── partials/    # En-tête, pied de page, barre de navigation
│   └── projets/     # Vues des dossiers
├── docs/            # Changelog et documentation
└── tantana_new.sql  # Schéma de la base de données
```

## Contribuer

1. Forker le dépôt
2. Créer une branche : `git checkout -b feat/ma-fonctionnalite`
3. Valider les changements : `git commit -m "feat: ajouter ma fonctionnalité"`
4. Pousser et ouvrir une pull request

## Licence

MIT — voir [LICENSE](LICENSE) pour plus de détails.

---

Développé par [Ismaël Andrimalala](https://github.com/Isma-Andri).

# WP Licence Manager

A Laravel-based WordPress plugin license management system built with Filament for managing products, licenses, and activations.

## 🌐 Live Application

[https://wplicence.jonakyds.com](https://wplicence.jonakyds.com)

## 🚀 Features

- **Product Management**: Create and manage WordPress plugins/themes
- **License Generation**: Generate and manage license keys
- **Activation Tracking**: Track license activations across domains
- **CSV Export**: Export license data to CSV and upload to SFTP
- **Admin Panel**: Built with Filament for a modern admin interface
- **API Integration**: RESTful API for license validation

## 📋 Requirements

- PHP 8.4+
- MySQL 8.0+
- Composer
- Node.js & npm

## 🛠️ Installation

1. Clone the repository:
```bash
git clone https://github.com/SourovCodes/WPLicenceManager.git
cd WPLicenceManager
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in `.env`

6. Run migrations:
```bash
php artisan migrate
```

7. Seed the database (optional):
```bash
php artisan db:seed
```

8. Build assets:
```bash
npm run build
```

9. Start the development server:
```bash
php artisan serve
```

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

## 🚢 Deployment

This project uses Deployer for automated deployments. Configure your deployment environment variables:

```bash
export DEPLOY_HOSTNAME="your-server.com"
export DEPLOY_PATH="/path/to/deployment"
export DEPLOY_SSH_PORT="22"
export DEPLOY_BRANCH="main"
```

Deploy to production:
```bash
vendor/bin/dep deploy
```

## 📁 Project Structure

- `app/Models/`: Eloquent models (Product, License, LicenseActivation, CsvUpload)
- `app/Filament/Resources/`: Filament admin resources
- `app/Jobs/`: Background jobs (CSV upload to SFTP)
- `database/migrations/`: Database schema migrations
- `database/factories/`: Model factories for testing
- `tests/`: Feature and unit tests

## 🔒 License

This project is proprietary software.

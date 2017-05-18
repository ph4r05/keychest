# Key Chest

Certificate monitor

## Development - setup

Edit `.env` file.

```
composer install
npm install
php artisan migrate --force
mkdir -p storage/logs/
chmod +w storage/logs/
```

### Resource compilation

```
npm run dev
npm run prod
```

### EC2 dep

Install NodeJS

```
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.32.0/install.sh | bash
. ~/.nvm/nvm.sh
nvm install 6
```


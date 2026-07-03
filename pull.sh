cd /var/www/service-desk
git pull origin master
npm run build
php artisan migrate
php artisan optimize:clear

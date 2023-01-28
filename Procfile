web: vendor/bin/heroku-php-nginx -C nginx_app.conf public/ & (cd /app/ && node echo-server.js) & wait -n
worker: supervisord -c /app/supervisor.conf -n

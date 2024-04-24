## Permissions

Please run:
`chown -R www-data:www-data storage`
`chown  www-data:www-data database`
`chown  www-data:www-data database/database.sqlite`

## Certbot

Runs:
`certbot certonly --webroot -w /var/www/letsencrypt -d "rates123.medeirostec.com.br" --agree-tos --email "contato@medeirostec.com.br" --non-interactive --text`

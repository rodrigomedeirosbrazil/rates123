#!/bin/sh

# echo "Changing Nginx default port to ${HTTP_NGINX_PORT}/${HTTPS_NGINX_PORT}"
# /usr/bin/envsubst '$HTTP_NGINX_PORT,$HTTPS_NGINX_PORT' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "Starting Supervisor..."
/usr/bin/supervisord -c /etc/supervisord.conf
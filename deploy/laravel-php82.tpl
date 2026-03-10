server {
    listen      %ip%:%proxy_port%;
    server_name %domain_idn% %alias_idn%;
    root        %docroot%/public;
    index       index.php index.html;
    access_log  /var/log/%web_system%/domains/%domain%.log combined;
    access_log  /var/log/%web_system%/domains/%domain%.bytes bytes;
    error_log   /var/log/%web_system%/domains/%domain%.error.log error;

    include /home/%user%/conf/web/%domain%/nginx.forcessl.conf*;

    location ~ /\.(?!well-known/) {
        deny all;
        return 404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php82-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_intercept_errors on;
        fastcgi_read_timeout 300;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot|webp|avif)$ {
        expires max;
        access_log off;
        add_header Cache-Control "public";
    }

    location /error/ {
        alias /home/%user%/web/%domain%/document_errors/;
    }

    include /home/%user%/conf/web/%domain%/nginx.conf_*;
}

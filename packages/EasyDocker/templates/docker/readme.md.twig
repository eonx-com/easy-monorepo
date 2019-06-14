# EasyDocker

## Dev Environment - Getting Started

Prerequisites:

- docker
- docker-compose

1. Build the containers:

You will need a valid `auth.json` file for connecting to **packagist.com**.
Please see your team lead.

Create your local `.env` file and then build the containers.

```
cp .env.example .env
./docker/d.sh build
```

*Hint:* `./docker/d.sh` is a shortcut for docker-compose.

2. Install Composer 3rd party libraries on the host.

```
docker container create --name api recroom-api
docker container cp api:/var/www/vendor .
docker container rm api
```

*Alternative:*

You can also use `./docker/dc.sh` as a shortcut for composer.  This will run composer inside docker and bind mount the correct directories back to the host.

```
./docker/dc.sh install --no-scripts -v
```

3. Run Database Migrations

```
./docker/dm.sh
```

4. Bring up stack

```
./docker/d.sh up -d
```

Confirm application is working:
```
curl http://localhost/ping
```


## Port Overrides

You can change the host port bindings for MySQL and Nginx by adding the following lines to the `.env` file.

```
RECROOM_NGINX_PORT=8081
RECROOM_MYSQL_PORT=33061
```
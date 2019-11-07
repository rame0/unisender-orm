# Docker
Docker is used for testing purpose.
Config in docker-compose.yml uses `./docker` context for building containers which excluded because it is symlink to my shared containers config dir.
You can make your own configs or get mine form: https://github.com/rame0/Docker_share

# Running examples
You can run examples by using local php:
 ```shell script
php ./examples/first.php
```
Or via `docker`:
```shell script
# build docker container before runing any examle script
docker-compose build

# run example
docker-compose run php-cli php ./examples/first.php
```
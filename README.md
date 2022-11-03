# Name

Desygner-Task

# Description

Symfony-Docker customized to implement simple api for upload images

# Installation

Run:

``` sudo docker compose build --pull --no-cache ```

And Then

``` sudo docker compose up -d ```

After all your containers were up successfully you can check by running:

``` sudo docker ps -a```

Then you must have 4 running containers including : php, database, pma, nginx

In order to work with application we need to navigate to php container so we use command below:

``` sudo docker compose exec php /bin/bash ```

then wehn we are in the php container we need to run:

``` composer install ```

App will be available at

``` localhost:8080 ```

Phpmyadmin will be available at

``` localhost:8081 ```

# Usage

First we need to create some users:

In the php container use command below:

``` symfony console create-user ```

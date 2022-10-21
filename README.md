# Name

Desygner-Task

# Description

Symfony-Docker customized to implement simple api for upload images

# Installation

Run:

``` docker compose build --pull --no-cache ```

And Then

``` docker compose up -d ```

Then navigate to php app container 

``` sudo docker compose exec php /bin/bash ```

Run:

``` composer install ```

# Usage

Create User:

In the app container use command below:

``` symfony console create-user ```
# API de Biblioteca

Trata-se de um teste que consiste em desenvolver uma API RESTful em Laravel, para simular o backend de um sistema de
gerenciamento de biblioteca.

### Especificações

<ul>
    <li>Laravel Framework 11.21.0</li>
    <li>PHP 8.3.8</li>
    <li>MySQL 8.0</li>
    <li>Docker 27.1.2</li>
</ul>

### Instruções

É necessário ter o Docker e o Docker Compose instalados.
<br>
Instruções para o Docker
<a href="https://docs.docker.com/engine/install/" target="_blank">aqui</a> e para
o Docker Compose
<a href="https://docs.docker.com/compose/install/" targe="_blank">aqui</a>.


<br>
Clona o repositório do projeto

```sh
git clone git@github.com:sharpeidev/library-management.git
```

```sh
cd library-management
```

Cria o .env
```sh
cp .env.example .env
```

Inicializa os containers Docker
```sh
docker compose up -d
```

Instala as dependências do Composer
```sh
docker exec -it library-php composer install
```

Executa as migrations
```sh
docker exec -it library-php php artisan migrate
```

Cria o super usuário administrador: "superadmin@email.com", senha "admin".
```sh
docker exec -it library-php php artisan db:seed
```

Inicia o processamento de jobs na fila. 
```sh
php artisan queue:work
```

Documentação da API:

```sh
http://localhost:8080/api/documentation
```

Para executar os testes:
```sh
docker exec -it library-php php artisan test
```

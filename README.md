# Symfony Service Order Test

Тестовое приложение на `PHP + Symfony` с запуском только через Docker.

## Стек
- PHP 8.4 (FPM)
- Symfony 8
- Nginx
- MySQL 8.4
- PHPUnit (WebTestCase)

## Запуск проекта
1. Собрать и поднять контейнеры:
```bash
make build
make up
```

2. Установить зависимости (если нужно):
```bash
make install
```

3. Создать БД и применить миграции (включая двух тестовых пользователей):
```bash
make db-create
make migrate
```

4. Открыть приложение:
- [http://localhost:8080](http://localhost:8080)
- форма логина: [http://localhost:8080/login](http://localhost:8080/login)
- форма заказа: [http://localhost:8080/order](http://localhost:8080/order)

## Тестовые пользователи
- `user1@example.com` / `password123`
- `user2@example.com` / `password456`

## Запуск тестов
```bash
make test
```

Команда автоматически:
1. применяет миграции в `APP_ENV=test`;
2. запускает `php bin/phpunit`.

## Полезные команды
- остановить контейнеры: `make down`
- сбросить БД: `make db-reset`
- зайти в php-контейнер: `make bash`

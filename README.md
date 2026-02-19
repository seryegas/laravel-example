# Laravel Booking Platform API

Демо-приложение на Laravel 12, демонстрирующее модульную архитектуру, сервисный слой, политики, события, очереди и другие лучшие практики фреймворка.

**Предметная область:** платформа бронирования услуг (записи на приём) с поддержкой оплаты и уведомлений.

## Стек технологий

| Компонент  | Версия         |
|------------|----------------|
| PHP        | 8.4-FPM        |
| Laravel    | 12              |
| PostgreSQL | 16 (Alpine)    |
| Redis      | Alpine          |
| Nginx      | Alpine          |
| Авторизация| Laravel Sanctum |

## Быстрый старт

### Требования

- Docker и Docker Compose
- Make

### Запуск

```bash
git clone <repo-url> && cd laravel-example

make up           # собрать контейнеры и запустить
make fresh        # миграции + наполнение демо-данными
```

API доступно по адресу `http://localhost:8088/api`.

### Демо-пользователи (после сидинга)

| Роль    | Email               | Пароль     |
|---------|---------------------|------------|
| Admin   | admin@example.com   | password   |
| Manager | (сгенерирован)      | password   |
| Client  | (сгенерирован)      | password   |

Сидер создаёт: 1 админ, 2 менеджера, 10 клиентов, 4 категории, ~17 услуг, ~1400 тайм-слотов, ~38 бронирований.

## Доступные Make-команды

| Команда        | Описание                                          |
|----------------|---------------------------------------------------|
| `make up`      | Скопировать `.env.prod` в `.env`, собрать и запустить контейнеры |
| `make down`    | Остановить и удалить контейнеры                   |
| `make restart` | Перезапустить все контейнеры                       |
| `make shell`   | Открыть bash внутри контейнера `app`              |
| `make migrate` | Выполнить миграции базы данных                    |
| `make seed`    | Запустить сидеры                                  |
| `make fresh`   | Удалить все таблицы, мигрировать и засидить заново|
| `make test`    | Запустить тесты PHPUnit                           |
| `make tinker`  | Открыть Laravel Tinker (REPL)                     |
| `make logs`    | Следить за логами контейнеров                     |

## Структура проекта

```
app/Modules/
├── Core/              # Модель User, роли, middleware, базовые трейты
│   ├── Models/        # User
│   ├── Enums/         # UserRole (admin, manager, client)
│   ├── Traits/        # HasActivityLog
│   ├── Http/Middleware/  # EnsureUserHasRole
│   └── Database/      # Миграции, фабрики, сидеры
│
├── Auth/              # Аутентификация (регистрация, вход, выход)
│   ├── Controllers/   # AuthController
│   ├── Services/      # AuthService
│   ├── Requests/      # RegisterRequest, LoginRequest
│   └── Routes/        # api.php
│
├── Service/           # Каталог услуг (категории, услуги, тайм-слоты)
│   ├── Models/        # Category, Service, TimeSlot
│   ├── Controllers/   # CategoryController, ServiceController, SlotController
│   ├── Services/      # CategoryService, ServiceService, SlotService
│   ├── Resources/     # ServiceResource, CategoryResource, SlotResource
│   ├── Enums/         # SlotStatus (available, booked, blocked)
│   └── Database/      # Миграции, фабрики, сидеры
│
├── Booking/           # Жизненный цикл бронирования
│   ├── Models/        # Booking
│   ├── Controllers/   # BookingController
│   ├── Services/      # BookingService
│   ├── DTOs/          # BookingData
│   ├── Enums/         # BookingStatus (pending, confirmed, completed, cancelled, no_show)
│   ├── Events/        # BookingCreated, BookingCancelled, BookingCompleted
│   ├── Listeners/     # ConfirmBookingOnPayment
│   ├── Jobs/          # CleanExpiredBookings
│   ├── Observers/     # BookingObserver
│   ├── Policies/      # BookingPolicy
│   ├── Resources/     # BookingResource
│   └── Database/      # Миграции, фабрики, сидеры
│
├── Payment/           # Обработка платежей
│   ├── Models/        # Payment
│   ├── Controllers/   # PaymentController
│   ├── Services/      # PaymentService
│   ├── Enums/         # PaymentStatus (pending, paid, refunded, failed)
│   ├── Events/        # PaymentProcessed
│   ├── Resources/     # PaymentResource
│   └── Database/      # Миграции, фабрики
│
└── Notification/      # Уведомления пользователей
    ├── Controllers/   # NotificationController
    ├── Services/      # NotificationService
    ├── Notifications/ # BookingConfirmedNotification и др.
    ├── Listeners/     # SendBookingConfirmedNotification и др.
    └── Routes/        # api.php
```

## Справочник API

Базовый URL: `http://localhost:8088/api/v1`

Все защищённые эндпоинты требуют заголовок:
```
Authorization: Bearer <token>
```

### Аутентификация

| Метод | Эндпоинт             | Авторизация | Описание                     |
|-------|----------------------|-------------|------------------------------|
| POST  | `/auth/register`     | --          | Регистрация нового пользователя |
| POST  | `/auth/login`        | --          | Вход, получение токена       |
| POST  | `/auth/logout`       | да          | Отзыв текущего токена        |
| GET   | `/auth/me`           | да          | Профиль текущего пользователя|

**POST /auth/register**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**POST /auth/login**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

---

### Категории (публичные)

| Метод | Эндпоинт                 | Авторизация | Описание                            |
|-------|--------------------------|-------------|-------------------------------------|
| GET   | `/categories`            | --          | Список активных категорий (с пагинацией) |
| GET   | `/categories/{id}`       | --          | Детали категории с услугами         |

---

### Услуги

| Метод  | Эндпоинт                      | Авторизация   | Описание                 |
|--------|--------------------------------|---------------|--------------------------|
| GET    | `/services`                    | --            | Список услуг (с пагинацией, фильтр по `category_id`) |
| GET    | `/services/{id}`               | --            | Детали услуги            |
| POST   | `/services`                    | admin/manager | Создать услугу           |
| PUT    | `/services/{id}`               | admin/manager | Обновить услугу          |
| DELETE | `/services/{id}`               | admin/manager | Удалить услугу           |

**POST /services**
```json
{
  "category_id": 1,
  "name": "Стрижка",
  "description": "Классическая стрижка",
  "duration_minutes": 30,
  "price": 25.00,
  "is_active": true
}
```

---

### Тайм-слоты

| Метод  | Эндпоинт                         | Авторизация   | Описание                 |
|--------|-----------------------------------|---------------|--------------------------|
| GET    | `/services/{id}/slots`            | --            | Список слотов (с фильтрацией) |
| POST   | `/services/{id}/slots`            | admin/manager | Создать слот             |
| DELETE | `/slots/{id}`                     | admin/manager | Удалить слот             |

**GET /services/{id}/slots?date_from=2026-03-01&date_to=2026-03-07&status=available**

---

### Бронирования

| Метод | Эндпоинт                         | Авторизация   | Описание                 |
|-------|-----------------------------------|---------------|--------------------------|
| GET   | `/bookings`                       | да            | Список бронирований (свои для клиентов, все для admin/manager) |
| POST  | `/bookings`                       | да            | Создать бронирование     |
| GET   | `/bookings/{id}`                  | да            | Детали бронирования      |
| PATCH | `/bookings/{id}/cancel`           | да            | Отменить бронирование    |
| PATCH | `/bookings/{id}/complete`         | admin/manager | Отметить как завершённое |

**POST /bookings**
```json
{
  "service_id": 1,
  "time_slot_id": 42,
  "notes": "Первый визит"
}
```

Жизненный цикл бронирования: `pending` -> `confirmed` (после оплаты) -> `completed` / `cancelled`

---

### Платежи

| Метод | Эндпоинт                         | Авторизация | Описание                 |
|-------|-----------------------------------|-------------|--------------------------|
| GET   | `/payments`                       | да          | Список платежей (свои для клиентов, все для admin) |
| POST  | `/bookings/{id}/pay`              | да          | Оплатить бронирование    |
| POST  | `/payments/{id}/refund`           | admin       | Возврат платежа          |

**POST /bookings/{id}/pay**
```json
{
  "payment_method": "card"
}
```

---

### Уведомления

| Метод | Эндпоинт                             | Авторизация | Описание                 |
|-------|---------------------------------------|-------------|--------------------------|
| GET   | `/notifications`                      | да          | Список уведомлений       |
| PATCH | `/notifications/{id}/read`            | да          | Отметить как прочитанное |
| PATCH | `/notifications/read-all`             | да          | Отметить все как прочитанные |

---

## Архитектурные особенности

- **Модульный монолит** -- каждый модуль самодостаточен: свои модели, контроллеры, сервисы, роуты, миграции и тесты
- **Сервисный слой** -- бизнес-логика консолидирована в сервисных классах, контроллеры тонкие
- **DTO** -- объекты передачи данных для структурированного обмена между слоями
- **Политики** -- авторизация через Laravel Policies (`BookingPolicy`)
- **События и слушатели** -- межмодульная коммуникация (например, `PaymentProcessed` запускает `ConfirmBookingOnPayment`)
- **Очереди** -- `CleanExpiredBookings` работает через очередь для отмены устаревших неоплаченных бронирований
- **Наблюдатели** -- `BookingObserver` для хуков жизненного цикла модели
- **API Resources** -- единообразное форматирование JSON-ответов
- **Form Requests** -- валидация вынесена из контроллеров
- **Enums** -- PHP 8.1+ backed enums для статусов и ролей
- **Транзакции** -- с пессимистичной блокировкой при создании бронирования (предотвращение гонки за слот)

## Запуск тестов

```bash
make test
```

15 тестов, покрывающих unit- и feature-сценарии по всем модулям.

## Порты

| Сервис     | Порт на хосте |
|------------|---------------|
| Nginx/API  | 8088          |
| PostgreSQL | 54322         |
| Redis      | 6379          |

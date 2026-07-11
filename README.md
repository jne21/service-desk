# Service Desk Demo

Навчально-демонстраційний Laravel-проєкт невеликої service desk / ticket-системи.

Проєкт створений як практична база для демонстрації роботи з Laravel, ролями користувачів, політиками доступу, API, імпортом заявок, чергами, Redis cache, Redis queue та базовою архітектурою backend-застосунку.

---

## Стек

- PHP 8.4
- Laravel 13
- Laravel Breeze
- Vue
- Inertia
- MariaDB
- Redis
- Laravel Sanctum
- Redis Queue
- Redis Cache
- Apache + PHP-FPM

---

## Реалізовано

### Користувачі, ролі та відділи

У системі є користувачі з ролями та прив’язкою до відділу.

Реалізовані ролі:

- Адміністратор
- Оператор
- Керівник

У ролі є `home_route`, який визначає, куди користувач потрапляє після входу.

Реалізовані відділи:

- Відділ Електриків
- Ремонтно-будівельний відділ
- Ліфтери
- Благоустрій

---

### Заявки

Основна сутність системи — заявка.

Поля заявки:

- джерело заявки
- зовнішній ID заявки
- заголовок
- опис
- статус
- користувач
- відділ
- дата створення
- дата оновлення

Заявки мають статуси:

- Нова
- В роботі
- Виконана
- Скасована

---

### Контроль доступу

Доступ до заявок обмежений через `TicketPolicy` та scope `visibleFor()`.

Логіка доступу:

- адміністратор бачить усі заявки
- користувач відділу бачить тільки заявки свого відділу
- користувач без відділу не бачить заявки

---

### Web-інтерфейс

Реалізовано базовий web-інтерфейс для роботи із заявками через Laravel Breeze, Vue та Inertia.

Є маршрути для:

- списку заявок
- створення заявки
- перегляду заявки
- оновлення заявки

---

### API для заявок користувача

Реалізовано API endpoint:

```http
GET /api/user/tickets
```

Авторизація виконується через Laravel Sanctum.

Підтримуються фільтри:

- `ticket_id`
- `date_from`
- `date_to`
- `per_page`

API повертає тільки заявки, доступні поточному користувачу згідно з його роллю та відділом.

---

### Джерела імпорту заявок

Реалізовано сутність `ticket_sources`.

Кожне джерело має:

- code
- name
- hashed API token
- active/inactive status

Авторизація import API виконується через middleware.

Токен можна передавати через:

```http
Authorization: Bearer <token>
```

або:

```http
X-Import-Token: <token>
```

---

### Імпорт заявок

Реалізовано import API для зовнішніх систем.

Синхронний імпорт:

```http
POST /api/tickets/import/sync
```

Асинхронний імпорт:

```http
POST /api/tickets/import
```

Приклад payload:

```json
{
  "tickets": [
    {
      "ticket_id": "1001",
      "title": "Тестова заявка API",
      "description": "Перевірка імпорту",
      "department_id": 1
    }
  ]
}
```

Поле `ticket_id` із зовнішньої системи зберігається у БД як `external_id`.

Для унікальності використовується пара:

```text
source_id + external_id
```

Якщо заявка з таким зовнішнім ID вже існує для цього джерела — вона оновлюється.

Якщо не існує — створюється.

---

### Асинхронна обробка через Redis Queue

Асинхронний імпорт працює через Laravel Queue з Redis backend.

Контролер створює запис імпорту зі статусом `queued`, після чого ставить у чергу job `ImportTicketsJob`.

Job викликає сервіс імпорту, який:

- переводить імпорт у статус `processing`
- створює або оновлює заявки
- рахує створені та оновлені заявки
- переводить імпорт у статус `finished`
- у разі помилки переводить імпорт у статус `failed`

Якщо queued job завершується помилкою після вичерпання спроб, Laravel записує його у таблицю `failed_jobs`.

Це технічний механізм Laravel Queue і він відрізняється від бізнес-статусу імпорту `ticket_imports.status_id = failed`.

- `ticket_imports.status_id = failed` показує, що конкретний імпорт завершився з помилкою
- `failed_jobs` показує, що Laravel job впав на рівні черги

Переглянути failed jobs можна командою:

```bash
php artisan queue:failed
```

---

### Scheduler для завислих імпортів

Для обробки імпортів, які зависли у статусі `processing`, додано artisan-команду:

```bash
php artisan ticket-imports:fail-stale --minutes=15
```
Для автоматичної роботи Laravel Scheduler на сервері потрібно додати cron-задачу:

```bash
* * * * * cd /var/www/service-desk && php artisan schedule:run >> /dev/null 2>&1
```

Перегляд scheduled-задач:

```bash
php artisan schedule:list
```

Ручний запуск scheduler:

```bash
php artisan schedule:run
```

Перевірка завислих імпортів вручну:

```bash
php artisan ticket-imports:fail-stale
```
Повторний запуск усіх failed jobs:
```bash
php artisan queue:retry all
```
Видалення всіх failed jobs:
```bash
php artisan queue:flush
```

---

### Запуск queue worker:

```bash
php artisan queue:work redis
```

Докладніше про постійний запуск queue worker через Supervisor:

```text
docs/supervisor.md
```

Приклад Supervisor-конфігурації:

```text
deploy/supervisor/service-desk-worker.conf.example
```
---

### Статуси імпорту

Реалізовано довідник статусів імпорту:

- `queued`
- `processing`
- `finished`
- `failed`
- `finished_with_errors`

---

### Діагностика імпорту

Реалізовано endpoint:

```http
GET /api/tickets/imports/{ticketImport}
```

Він повертає інформацію про конкретний імпорт:

- джерело
- статус
- кількість заявок
- кількість створених
- кількість оновлених
- кількість помилок
- текст помилки
- час старту
- час завершення

Доступ до імпорту обмежений джерелом: джерело може бачити тільки власні імпорти.

---

### Redis Lock

Для асинхронного імпорту додано lock по `source_id`.

Це не дозволяє одночасно обробляти кілька імпортів одного джерела.

Мета — уникнути race condition при паралельному оновленні одних і тих самих заявок.

---

### Rate Limit

Для import API додано rate limit.

Ліміт застосовується до конкретного `ticket_source`.

Fallback — IP address.

Rate limit підключено до:

```http
POST /api/tickets/import
POST /api/tickets/import/sync
```

---

### Redis Cache

Реалізовано кешування довідників через Redis cache.

Кешуються:

- статуси заявок
- статуси імпорту
- ролі
- відділи
- ID статусів імпорту за code

При зміні довідників кеш потрібно скидати.

Базова команда:

```bash
php artisan cache:clear
```

---

### Events та logging

Для імпорту реалізовано events:

- `TicketImportStarted`
- `TicketImportFinished`
- `TicketImportFailed`

Для них створено listeners, які пишуть лог імпорту.

Окремий log channel:

```text
ticket_import
```

Файл логів:

```text
storage/logs/ticket-import.log
```

---

### Уніфіковані API-відповіді

Реалізовано trait:

```php
App\Http\Controllers\Concerns\ApiResponses
```

Формат успішної відповіді:

```json
{
  "success": true
}
```

Формат помилки:

```json
{
  "success": false,
  "error": "Error message"
}
```

---

## Основні команди

Встановлення залежностей:

```bash
composer install
npm install
```

Міграції:

```bash
php artisan migrate
```

Сіди:

```bash
php artisan db:seed
```

Запуск frontend build:

```bash
npm run build
```

Запуск queue worker:

```bash
php artisan queue:work redis
```

Очистка кешів Laravel:

```bash
php artisan optimize:clear
```

Очистка application cache:

```bash
php artisan cache:clear
```

---

## Змінні оточення

Основні `.env` параметри:

```env
APP_NAME="Service Desk"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=service_desk
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```


---

## Поточний статус

Проєкт уже можна використовувати як demo backend для:

- Laravel roles / permissions
- Laravel policies
- API token middleware
- Sanctum API
- Redis queue
- Redis cache
- async jobs
- import diagnostics
- rate limiting
- event/listener logging
- scheduled tasks

---

### Історія змін заявок

Для заявок реалізовано збереження історії змін у таблиці `ticket_changes`.

Історія фіксує основні події життєвого циклу заявки:

- `created` — заявку створено
- `updated` — заявку оновлено
- `deleted` — заявку видалено через soft delete
- `restored` — заявку відновлено після повторного імпорту

Для кожного запису історії зберігається:

- заявка, до якої належить зміна
- користувач, який виконав дію через web-інтерфейс
- або джерело імпорту, якщо зміна прийшла через API import
- тип події
- JSON зі зміненими полями
- час створення запису історії

Формат `changes`:

    {
      "status_id": {
        "old": 1,
        "new": 2
      },
      "department_id": {
        "old": 1,
        "new": 3
      }
    }

Для запису історії використовується сервіс `App\Services\TicketChangeLogger`.

Контракт сервісу: `App\Services\Contracts\TicketChangeLoggerInterface`.

Контракт прив’язаний до реалізації через Laravel service container.

Історія змін записується для:

- ручного створення заявки
- ручного оновлення заявки
- soft delete заявки
- створення заявки через імпорт
- оновлення заявки через імпорт
- відновлення soft-deleted заявки при повторному імпорті

Для відображення історії використовується resource `App\Http\Resources\TicketChangeResource`.

Resource готує дані для frontend: розшифровує події, автора зміни, назви полів і значення довідників. Vue-компонент отримує вже підготовлену структуру і не містить хардкоду для розшифровки `ticket_changes`.

Історія показується на сторінці перегляду заявки.
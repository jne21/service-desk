# Service Desk Demo

Навчально-демонстраційний Laravel-проєкт невеликої service desk / ticket-системи.

Проєкт створений як практична база для демонстрації роботи з Laravel, ролями користувачів, політиками доступу, API, імпортом заявок, чергами, Redis cache, Redis queue, Laravel Broadcasting та базовою архітектурою backend-застосунку.

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
- Laravel Broadcasting
- Laravel Reverb
- Laravel Echo
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
- Відновлена
- В роботі
- Виконана
- Скасована

Заявки підтримують soft delete. Видалена заявка не зникає фізично з бази даних і може бути відновлена при повторному імпорті із зовнішнього джерела.

---

### Контроль доступу

Доступ до заявок обмежений через `TicketPolicy` та scope `visibleFor()`.

Логіка доступу:

- адміністратор бачить усі заявки
- користувач відділу бачить тільки заявки свого відділу
- користувач без відділу не бачить заявки

Видалення заявки також перевіряється через policy:

- адміністратор може видаляти заявки
- керівник може видаляти заявки свого відділу, якщо статус не фінальний
- інші користувачі не можуть видаляти заявки

---

### Web-інтерфейс

Реалізовано базовий web-інтерфейс для роботи із заявками через Laravel Breeze, Vue та Inertia.

Є маршрути для:

- списку заявок
- створення заявки
- перегляду заявки
- оновлення заявки
- видалення заявки через soft delete
- перегляду історії змін заявки

Сторінка списку заявок `/tickets` завантажує дані через API endpoint `GET /api/user/tickets`. Це дозволяє використовувати один формат даних для початкового завантаження списку і для подальших real-time оновлень.

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

```json
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
```

Для запису історії використовується сервіс:

```text
App\Services\TicketChangeLogger
```

Контракт сервісу:

```text
App\Services\Contracts\TicketChangeLoggerInterface
```

Контракт прив’язаний до реалізації через Laravel service container.

Історія змін записується для:

- ручного створення заявки
- ручного оновлення заявки
- soft delete заявки
- створення заявки через імпорт
- оновлення заявки через імпорт
- відновлення soft-deleted заявки при повторному імпорті

Для відображення історії використовується resource:

```text
App\Http\Resources\TicketChangeResource
```

Resource готує дані для frontend: розшифровує події, автора зміни, назви полів і значення довідників. Vue-компонент отримує вже підготовлену структуру і не містить хардкоду для розшифровки `ticket_changes`.

Історія показується на сторінці перегляду заявки.

---

### Real-time оновлення списку заявок

Для сторінки списку заявок реалізується real-time синхронізація через Laravel Broadcasting, Laravel Reverb та Laravel Echo.

Для передачі змін використовується broadcast event:

```text
App\Events\TicketChanged
```

Подія передається тільки у приватні канали відділів:

```text
private-ticket-list.departments.{departmentId}
```

Адміністратори не підписуються на real-time канал списку заявок. Для них список працює як звичайний API-список без live-оновлень, щоб не створювати зайвий потік повідомлень з усіх відділів.

Авторизація каналів описана в:

```text
routes/channels.php
```

Користувач може підписатися тільки на канал свого відділу. Користувачі без `department_id` та адміністратори не отримують real-time оновлення списку.

Основні типи подій:

- `created` — створено нову заявку
- `updated` — оновлено існуючу заявку
- `restored` — заявку відновлено після повторного імпорту
- `deleted` — заявку видалено через soft delete
- `removed_from_access` — заявка більше не доступна поточному відділу

Для централізованої відправки real-time подій використовується сервіс:

```text
App\Services\TicketRealtimeNotifier
```

Він відповідає за вибір потрібного каналу відділу та відправку події `TicketChanged`.

Real-time події відправляються при:

- ручному створенні заявки
- ручному оновленні заявки
- soft delete заявки
- створенні заявки через імпорт
- оновленні заявки через імпорт
- відновленні soft-deleted заявки через повторний імпорт

Frontend-логіка на сторінці `/tickets`:

- при `created` або `restored` показується сповіщення
- якщо користувач знаходиться на першій сторінці списку, нова або відновлена заявка додається на початок списку
- при `updated`, якщо заявка є на поточній сторінці, її рядок оновлюється
- оновлений рядок тимчасово підсвічується
- при `deleted` або `removed_from_access` заявка прибирається з поточного списку
- користувач отримує коротке повідомлення про зміну списку

Для ручних дій користувача real-time подія не має повертатися назад у той самий браузер, з якого була виконана дія. Для цього використовується механізм `toOthers()`. Події, що приходять з імпорту, відправляються всім підписаним користувачам відповідного відділу.

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

Якщо заявка раніше була видалена через soft delete — вона відновлюється і отримує статус `restored`.

---

### Асинхронна обробка через Redis Queue

Асинхронний імпорт працює через Laravel Queue з Redis backend.

Контролер створює запис імпорту зі статусом `queued`, після чого ставить у чергу job `ImportTicketsJob`.

Job викликає сервіс імпорту, який:

- переводить імпорт у статус `processing`
- створює, оновлює або відновлює заявки
- рахує створені, оновлені та відновлені заявки
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
- кількість відновлених
- кількість помилок
- текст помилки
- час старту
- час завершення

Доступ до імпорту обмежений джерелом: джерело може бачити тільки власні імпорти.

Також реалізовано endpoint для перегляду списку імпортів:

```http
GET /api/tickets/imports
```

Для списку імпортів використовуються обов’язкові параметри `date_from` та `date_to`, а максимальний дозволений інтервал обмежується конфігурацією.

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

Встановлення PHP-залежностей:

```bash
composer install
```

Встановлення Node-залежностей:

```bash
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

Запуск frontend dev server:

```bash
npm run dev
```

Запуск queue worker:

```bash
php artisan queue:work redis
```

Запуск Laravel Reverb:

```bash
php artisan reverb:start --debug
```

Очистка кешів Laravel:

```bash
php artisan optimize:clear
```

Очистка application cache:

```bash
php artisan cache:clear
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

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

---

## Поточний статус

Проєкт уже можна використовувати як demo backend / full-stack приклад для:

- Laravel roles / permissions
- Laravel policies
- API token middleware
- Sanctum API
- soft deletes
- ticket change history
- Redis queue
- Redis cache
- async jobs
- import diagnostics
- rate limiting
- event/listener logging
- scheduled tasks
- Laravel Broadcasting
- Laravel Reverb
- private broadcasting channels
- real-time оновлення списку заявок

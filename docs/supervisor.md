# Queue Worker через Supervisor

У цьому проєкті асинхронний імпорт заявок працює через Laravel Queue з Redis backend.

Коли зовнішня система викликає endpoint асинхронного імпорту:

```http
POST /api/tickets/import
```

застосунок створює запис у таблиці `ticket_imports`, ставить задачу `ImportTicketsJob` у Redis queue, а окремий queue worker забирає цю задачу з черги та виконує імпорт.

Для локальної розробки worker можна запускати вручну:

```bash
php artisan queue:work redis
```

Але для серверного середовища ручний запуск не підходить. Якщо worker запущений просто в SSH-сесії, він зупиниться після закриття терміналу, розриву з’єднання або перезавантаження сервера.

Для постійної роботи queue worker можна використовувати Supervisor.

---

## Приклад конфігурації

У проєкті передбачено приклад Supervisor-конфігурації:

```text
deploy/supervisor/service-desk-worker.conf.example
```

Вміст прикладу:

```ini
[program:service-desk-worker]
process_name=%(program_name)s_%(process_num)02d

command=/usr/bin/php /var/www/service-desk/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
directory=/var/www/service-desk

autostart=true
autorestart=true
stopasgroup=true
killasgroup=true

user=YOUR_USER
numprocs=1

redirect_stderr=true
stdout_logfile=/var/www/service-desk/storage/logs/queue-worker.log

stopwaitsecs=3600
```

Перед використанням потрібно замінити:

```ini
user=YOUR_USER
```

на Linux-користувача, від якого має працювати Laravel-застосунок.

Також потрібно перевірити шлях до проєкту:

```text
/var/www/service-desk
```

Якщо проєкт розгорнутий в іншій директорії, треба змінити шлях у трьох місцях:

```ini
command=/usr/bin/php /var/www/service-desk/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
directory=/var/www/service-desk
stdout_logfile=/var/www/service-desk/storage/logs/queue-worker.log
```

---

## Встановлення конфігу Supervisor

Приклад конфігу потрібно скопіювати в директорію Supervisor:

```bash
sudo cp deploy/supervisor/service-desk-worker.conf.example /etc/supervisor/conf.d/service-desk-worker.conf
```

Після цього слід відредагувати скопійований файл:

```bash
sudo nano /etc/supervisor/conf.d/service-desk-worker.conf
```

У файлі потрібно вказати правильного користувача замість `YOUR_USER` і перевірити шляхи до проєкту.

Після створення або зміни конфігурації потрібно перечитати конфіги Supervisor:

```bash
sudo supervisorctl reread
```

Застосувати зміни:

```bash
sudo supervisorctl update
```

Запустити worker:

```bash
sudo supervisorctl start service-desk-worker:*
```

Перевірити стан процесу:

```bash
sudo supervisorctl status
```

Перезапустити worker:

```bash
sudo supervisorctl restart service-desk-worker:*
```

---

## Пояснення параметрів конфігурації

Основна команда запуску:

```ini
command=/usr/bin/php /var/www/service-desk/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
```

Вона запускає Laravel queue worker для Redis queue.

Параметр:

```bash
--sleep=3
```

означає, що якщо черга порожня, worker чекатиме 3 секунди перед наступною перевіркою.

Параметр:

```bash
--tries=3
```

означає, що Laravel спробує виконати задачу не більше трьох разів. Якщо всі спроби завершаться помилкою, задача буде записана в таблицю `failed_jobs`.

Параметр:

```bash
--timeout=90
```

задає максимальний час виконання однієї job у секундах. Якщо job виконується довше, worker вважатиме її завислою.

Параметр:

```ini
autostart=true
```

означає, що Supervisor запускатиме worker автоматично.

Параметр:

```ini
autorestart=true
```

означає, що Supervisor перезапустить worker, якщо той несподівано завершиться.

Параметри:

```ini
stopasgroup=true
killasgroup=true
```

допомагають коректно завершувати не тільки головний процес, а й пов’язані дочірні процеси.

Параметр:

```ini
numprocs=1
```

означає, що буде запущено один worker-процес.

Для цього навчально-демонстраційного проєкту одного worker-а достатньо.

---

## Перезапуск worker після оновлення коду

Після deployment нового коду Laravel рекомендує перезапускати queue workers командою:

```bash
php artisan queue:restart
```

Ця команда не обриває поточну job посеред виконання. Worker завершує задачу, яку вже виконує, і після цього перезапускається.

Якщо worker запущений через Supervisor, команда `queue:restart` працює разом із ним коректно: Laravel повідомляє worker-у про необхідність завершитися, а Supervisor запускає його знову.

---

## Failed jobs

Laravel має окремий механізм для задач черги, які завершилися помилкою.

Якщо queued job впала після всіх дозволених спроб, Laravel записує її в таблицю:

```text
failed_jobs
```

Переглянути failed jobs можна командою:

```bash
php artisan queue:failed
```

Повторно поставити всі failed jobs у чергу:

```bash
php artisan queue:retry all
```

Очистити список failed jobs:

```bash
php artisan queue:flush
```

---

## Бізнес-статус імпорту і failed jobs

У проєкті є два різні рівні обробки помилок.

Перший рівень — бізнес-статус імпорту в таблиці `ticket_imports`.

Якщо імпорт завершився з помилкою з точки зору застосунку, він отримує статус:

```text
failed
```

Цей статус використовується API діагностики імпорту:

```http
GET /api/tickets/imports/{ticketImport}
```

Другий рівень — технічний статус Laravel Queue.

Якщо сама Laravel job впала після всіх спроб, вона потрапляє в таблицю:

```text
failed_jobs
```

Тобто:

```text
ticket_imports.status_id = failed
```

означає, що конкретний імпорт завершився з помилкою з точки зору бізнес-логіки.

А запис у:

```text
failed_jobs
```

означає, що Laravel Queue не змогла виконати job після всіх retry-спроб.

Обидва механізми корисні:

- `ticket_imports` використовується застосунком і зовнішнім API для діагностики імпорту;
- `failed_jobs` використовується розробником або адміністратором для технічної діагностики черги.

---

## Scheduler для завислих імпортів

Окремо від Supervisor у проєкті є artisan-команда для пошуку імпортів, які зависли у статусі `processing`:

```bash
php artisan ticket-imports:fail-stale --minutes=15
```

Вона переводить старі `processing`-імпорти у статус `failed`.

Ця команда має виконуватись через Laravel Scheduler. Для роботи Scheduler на сервері потрібно додати cron-задачу:

```cron
* * * * * cd /var/www/service-desk && php artisan schedule:run >> /dev/null 2>&1
```

Supervisor відповідає за постійний запуск queue worker-а.

Cron + Laravel Scheduler відповідає за періодичний запуск службових команд, зокрема перевірку завислих імпортів.

Це різні механізми, і в цьому проєкті використовуються обидва.

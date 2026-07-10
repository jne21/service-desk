### Приклад import API request

```bash
curl -X POST http://localhost/api/tickets/import \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-Import-Token: test-import-token-123" \
  -d '{
    "tickets": [
      {
        "ticket_id": "1001",
        "title": "Тестова заявка API",
        "description": "Перевірка імпорту",
        "department_id": 1
      }
    ]
  }'
```

---

### Список імпортів

Реалізовано endpoint для перегляду списку імпортів поточного джерела:

```http
GET /api/tickets/imports
```

Доступ до списку обмежений поточним `ticket_source`: джерело бачить тільки власні імпорти.

Endpoint призначений для діагностики останніх імпортів у межах короткого часового інтервалу, тому параметри `date_from` і `date_to` є обов’язковими.

Обов’язкові параметри:

- `date_from` — початок інтервалу у форматі `YYYY-MM-DD HH:MM:SS`
- `date_to` — кінець інтервалу у форматі `YYYY-MM-DD HH:MM:SS`

Опціональні параметри:

- `status` — фільтр за кодом статусу імпорту
- `per_page` — кількість записів на сторінку
- `cursor` — курсор для переходу до наступної сторінки

Максимальна тривалість інтервалу між `date_from` і `date_to` налаштовується через `.env`:

```env
TICKET_IMPORT_LIST_MAX_INTERVAL_MINUTES=360
```

За замовчуванням інтервал обмежено 360 хвилинами, тобто 6 годинами.

Для пагінації використовується cursor pagination, щоб не виконувати дорогий `COUNT(*)` по великій таблиці імпортів.

Приклад запиту:

```bash
curl -G "http://localhost/api/tickets/imports" \
  -H "Accept: application/json" \
  -H "X-Import-Token: test-import-token-123" \
  --data-urlencode "date_from=2026-07-10 10:00:00" \
  --data-urlencode "date_to=2026-07-10 16:00:00" \
  --data-urlencode "per_page=20"
```

Приклад запиту з фільтром за статусом:

```bash
curl -G "http://localhost/api/tickets/imports" \
  -H "Accept: application/json" \
  -H "X-Import-Token: test-import-token-123" \
  --data-urlencode "date_from=2026-07-10 10:00:00" \
  --data-urlencode "date_to=2026-07-10 16:00:00" \
  --data-urlencode "status=failed" \
  --data-urlencode "per_page=20"
```

У відповіді повертається список імпортів і дані cursor pagination:

```json
{
  "success": true,
  "imports": [],
  "pagination": {
    "perPage": 20,
    "nextCursor": null,
    "previousCursor": null,
    "hasMorePages": false
  }
}
```
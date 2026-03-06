# Student Works Plugin for Moodle

Плагин для управления студенческими работами (курсовые, практики, дипломы) в Moodle 5.1+

## Возможности

### Для студентов
- Загрузка работ в формате PDF
- Отслеживание статуса проверки
- Получение уведомлений о смене статуса
- Просмотр истории загруженных работ
- Современный интерфейс с тёмной темой

### Для преподавателей
- Просмотр всех работ студентов
- Фильтрация по типу, статусу, студенту
- Поиск по теме и дисциплине
- Загрузка рецензий
- Изменение статуса работ (AJAX)
- Экспорт данных в CSV/Excel
- Пагинация для больших списков

### Технические особенности
- Полная совместимость с Moodle 5.1.3
- Современный Hooks API (Moodle 5.x)
- Оптимизированные SQL-запросы (JOIN вместо N+1)
- Корпоративный UI 2025 (Glassmorphism, анимации)
- Accessibility (ARIA, keyboard navigation)
- Responsive design (mobile-first)
- Система уведомлений
- Cron задачи очистки

## Требования

- Moodle 5.1.3+ (2025041400)
- PHP 8.1+
- Поддержка JavaScript в браузере

## Установка

1. Скопируйте папку `local_studentworks` в директорию `local/` вашего Moodle
2. Войдите в систему как администратор
3. Перейдите в "Уведомления" для автоматической установки
4. Настройте права доступа:
   - `local/studentworks:viewown` - для студентов
   - `local/studentworks:viewall` - для преподавателей

## Обновление

Плагин содержит скрипт автоматического обновления (`db/upgrade.php`). 
При обновлении будут автоматически:
- Добавлены новые поля в БД
- Созданы индексы и внешние ключи
- Обновлены настройки

## Структура проекта

```
local_studentworks/
├── amd/src/studentworks.js      # JavaScript модуль
├── classes/
│   ├── export/exporter.php      # Экспорт данных
│   ├── form/                    # Формы (Moodle forms)
│   ├── hooks/navigation.php     # Hooks API
│   ├── manager/work_manager.php # Бизнес-логика
│   ├── notification/notifier.php# Уведомления
│   ├── output/                  # Renderable классы
│   └── task/cleanup_old_files.php # Cron задача
├── db/
│   ├── access.php               # Права доступа
│   ├── hooks.php                # Регистрация hooks
│   ├── install.xml              # Структура БД
│   ├── messages.php             # Message providers
│   ├── tasks.php                # Cron задачи
│   └── upgrade.php              # Скрипты обновления
├── lang/                        # Локализация (en, ru)
├── templates/                   # Mustache шаблоны
├── index.php                    # Главная страница (студент)
├── teacher.php                  # Панель преподавателя
├── upload.php                   # Загрузка работ
├── view.php                     # Просмотр работы
├── lib.php                      # Библиотечные функции
├── styles.css                   # CSS стили
└── version.php                  # Версия плагина
```

## Настройка

### Права доступа

Плагин использует две capability:
- `local/studentworks:viewown` - просмотр своих работ
- `local/studentworks:viewall` - просмотр всех работ (преподаватели)

### Уведомления

Для работы уведомлений убедитесь, что настроен cron Moodle:
```
* * * * * /usr/bin/php /path/to/moodle/admin/cli/cron.php
```

### Экспорт данных

Преподаватели могут экспортировать данные:
- CSV (совместим с Excel)
- Excel (если доступен модуль)
- JSON (для интеграций)

## API

### work_manager

```php
use local_studentworks\manager\work_manager;

// Получить работы с фильтрами
$works = work_manager::get_works([
    'userid' => $USER->id,
    'status' => 'reviewed'
], $page, $perpage);

// Обновить статус
work_manager::update_status($workid, 'reviewed', 'Отличная работа!');

// Получить статистику
$stats = work_manager::get_stats($userid);
```

## Лицензия

GPL v3

## Авторы

- Разработка: [Ваше имя]
- Дизайн: Corporate UI/UX 2025

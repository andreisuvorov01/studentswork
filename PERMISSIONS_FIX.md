# Исправление прав доступа для Student Works Plugin

## Проблема

Плагин использовал `CONTEXT_SYSTEM` для всех capabilities, но в Moodle 5.1+ роли студентов и преподавателей назначаются на уровне курсов (`CONTEXT_COURSE`). Это приводило к тому, что проверки `has_capability()` всегда возвращали `false`.

## Что было исправлено

### 1. db/access.php
- Изменен `contextlevel` с `CONTEXT_SYSTEM` на `CONTEXT_COURSE` для всех capabilities
- Удалены `clonepermissionsfrom` (не нужны)
- Добавлены teacher/editingteacher в archetypes для viewown/upload

### 2. lib.php
- Добавлена функция `local_studentworks_has_capability_in_any_course()` - проверяет capability во всех курсах пользователя
- Обновлены функции `local_studentworks_has_teacher_access()` и `local_studentworks_has_student_access()`
- Обновлена функция `local_studentworks_pluginfile()` для использования новой проверки

### 3. index.php, teacher.php, upload.php
- Заменены все вызовы `has_capability($cap, $context)` на `local_studentworks_has_capability_in_any_course($cap)`

### 4. version.php
- Увеличена версия до 2026030601

### 5. db/upgrade.php
- Добавлен upgrade step для версии 2026030601
- Автоматическое обновление contextlevel в БД
- Переназначение capabilities для ролей

## Как применить исправления

1. **Войдите в Moodle как администратор**

2. **Перейдите в "Site administration" → "Notifications"**
   - Moodle автоматически обнаружит новую версию плагина
   - Нажмите "Upgrade Moodle database now"

3. **Очистите кэш**
   ```
   Site administration → Development → Purge all caches
   ```

4. **Проверьте права доступа**
   - Зайдите как студент → должна быть доступна страница `/local/studentworks/index.php`
   - Зайдите как преподаватель → должна быть доступна страница `/local/studentworks/teacher.php`

## Альтернативный способ (через CLI)

```bash
# Обновление БД
php admin/cli/upgrade.php

# Очистка кэша
php admin/cli/purge_caches.php
```

## Проверка прав доступа

После обновления проверьте:

```
Site administration → Users → Permissions → Define roles
```

Для роли **Student**:
- ✅ local/studentworks:viewown = Allow
- ✅ local/studentworks:upload = Allow

Для роли **Teacher** / **Editing Teacher**:
- ✅ local/studentworks:viewown = Allow
- ✅ local/studentworks:upload = Allow
- ✅ local/studentworks:viewall = Allow
- ✅ local/studentworks:review = Allow

## Техническая информация

### Как работает новая проверка прав

```php
// Старый способ (не работал)
has_capability('local/studentworks:viewown', context_system::instance())

// Новый способ (работает)
local_studentworks_has_capability_in_any_course('local/studentworks:viewown')
```

Новая функция:
1. Проверяет capability в системном контексте (для админов)
2. Получает все курсы, где пользователь зарегистрирован
3. Проверяет capability в контексте каждого курса
4. Возвращает `true`, если найдена хотя бы в одном курсе

## Возможные проблемы

### Проблема: После обновления всё равно нет доступа

**Решение:**
1. Убедитесь, что пользователи зарегистрированы хотя бы в одном курсе
2. Проверьте роли пользователей в курсах
3. Очистите кэш повторно
4. Проверьте логи: `Site administration → Reports → Logs`

### Проблема: Ошибка при обновлении БД

**Решение:**
1. Проверьте логи PHP и Moodle
2. Убедитесь, что файл `db/upgrade.php` корректен
3. Попробуйте обновить вручную через CLI

## Контакты

При возникновении проблем проверьте:
- Логи Moodle: `Site administration → Reports → Logs`
- Логи PHP: обычно в `/var/log/apache2/error.log` или `/var/log/php-fpm/error.log`
- Версию Moodle: должна быть 5.1.3+

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Russian strings for Student Works plugin.
 *
 * @package    local_studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Студенческие работы';
$string['studentworks:viewown'] = 'Просматривать свои студенческие работы';
$string['studentworks:viewall'] = 'Просматривать все студенческие работы';
$string['studentworks:upload'] = 'Загружать студенческие работы';
$string['studentworks:review'] = 'Проверять студенческие работы (загружать рецензии)';

// Navigation.
$string['mystudentworks'] = 'Мои работы';
$string['allstudentworks'] = 'Все работы';

// Work types.
$string['worktype'] = 'Тип работы';
$string['coursework'] = 'Курсовая работа / Курсовой проект';
$string['practice'] = 'Отчёт по практике';
$string['vkr'] = 'ВКР';

// Work fields.
$string['topic'] = 'Тема';
$string['discipline'] = 'Дисциплина';
$string['author'] = 'Автор';
$string['timecreated'] = 'Дата создания';
$string['timemodified'] = 'Последнее изменение';
$string['status'] = 'Статус';

// Status values.
$string['submitted'] = 'Загружена';
$string['reviewed'] = 'Проверена';
$string['pending'] = 'На проверке';
$string['status_submitted'] = 'Загружена';
$string['status_under_review'] = 'На проверке';
$string['status_reviewed'] = 'Проверена';
$string['status_rejected'] = 'Отклонена';
$string['updatestatus'] = 'Обновить статус';
$string['statusupdatesuccess'] = 'Статус успешно обновлен';
$string['statusupdateerror'] = 'Ошибка обновления статуса';

// Actions.
$string['actions'] = 'Действия';
$string['view'] = 'Просмотр';
$string['back'] = 'Назад';
$string['download'] = 'Скачать';
$string['upload'] = 'Загрузить';
$string['search'] = 'Поиск';

// Upload and files.
$string['uploadwork'] = 'Загрузить работу';
$string['viewwork'] = 'Просмотр работы';
$string['workdetails'] = 'Детали работы';
$string['downloadwork'] = 'Скачать работу';
$string['downloadreview'] = 'Скачать рецензию';
$string['studentfile'] = 'Файл работы (PDF)';
$string['reviewfile'] = 'Файл рецензии (PDF)';
$string['uploadreview'] = 'Загрузить рецензию';
$string['updatereview'] = 'Обновить рецензию';
$string['allworks'] = 'Все студенческие работы';

// Grade.
$string['grade'] = 'Оценка';
$string['setgrade'] = 'Выставить оценку';
$string['nograde'] = 'Без оценки';
$string['checkwork'] = 'Проверить';

// Empty states.
$string['noworks'] = 'Работы ещё не загружены';
$string['noworksdesc'] = 'Управляйте вашими курсовыми, практиками и дипломами';
$string['noworksfound'] = 'Работы не найдены';
$string['noworksfounddesc'] = 'Попробуйте изменить параметры поиска или фильтры';
$string['uploadfirst'] = 'Загрузить первую работу';
$string['noworkfile'] = 'Файл работы отсутствует';

// Filters.
$string['filterbytype'] = 'Фильтр по типу';
$string['filterbystatus'] = 'Фильтр по статусу';
$string['filterbydate'] = 'Фильтр по дате';
$string['filterdatefrom'] = 'Дата с';
$string['filterdateto'] = 'Дата по';
$string['applyfilters'] = 'Применить фильтры';
$string['clearfilters'] = 'Сбросить фильтры';
$string['alltypes'] = 'Все типы';
$string['allstatuses'] = 'Все статусы';
$string['searchplaceholder'] = 'Поиск по теме, дисциплине, автору или email...';

// Dashboard and stats.
$string['myworksdesc'] = 'Управляйте вашими курсовыми, практиками и дипломами';
$string['dashboarddesc'] = 'Обзор всех студенческих работ';
$string['totalworks'] = 'Всего работ';
$string['students'] = 'Студенты';
$string['statistics'] = 'Статистика';
$string['filters'] = 'Фильтры';
$string['workslist'] = 'Список работ';
$string['empty'] = 'Пустое состояние';
$string['history'] = 'История';
$string['files'] = 'Файлы';
$string['breadcrumb'] = 'Навигация';
$string['pagination'] = 'Навигация по страницам';
$string['previous'] = 'Назад';
$string['next'] = 'Вперёд';
$string['page'] = 'Страница';

// History entries.
$string['worksubmitted'] = 'Работа загружена';
$string['workuploadedby'] = 'Работа загружена пользователем {$a}';
$string['reviewsubmitted'] = 'Рецензия загружена';

// Messages and notifications.
$string['duplicatework'] = 'Такая работа уже загружена по данной дисциплине';
$string['fileuploaderror'] = 'Ошибка загрузки файла';
$string['uploadsuccess'] = 'Работа успешно загружена';
$string['reviewsuccess'] = 'Рецензия успешно загружена';
$string['status_change_subject'] = 'Изменение статуса работы';
$string['status_change_body'] = 'Статус вашей работы "{$a->topic}" изменен на: {$a->status}';
$string['new_work_subject'] = 'Новая студенческая работа';
$string['new_work_body'] = 'Студент {$a->student} загрузил новую работу "{$a->topic}" ({$a->discipline})';

// Validation errors.
$string['err_topictoolshort'] = 'Тема должна содержать не менее 10 символов';
$string['err_disciplinetoolshort'] = 'Дисциплина должна содержать не менее 3 символов';
$string['err_invalidfiletype'] = 'Неверный тип файла. Разрешены только файлы {$a}';
$string['required'] = 'Обязательное поле';
$string['minimumchars'] = 'Минимум {$a} символов';
$string['nocourses'] = 'Нет записанных курсов';

// Accessibility.
$string['aria_workscount'] = 'Найдено работ: {$a}';
$string['aria_filterresults'] = 'Фильтровать результаты';

// Export.
$string['export'] = 'Экспорт';
$string['exportcsv'] = 'Экспорт CSV';
$string['exportexcel'] = 'Экспорт Excel';
$string['student'] = 'Студент';
$string['email'] = 'Email';
$string['date'] = 'Дата';

// Tasks.
$string['task_cleanup_old_files'] = 'Очистка старых отклоненных работ';

// AJAX loading.
$string['loading'] = 'Загрузка...';
$string['filtererror'] = 'Ошибка применения фильтров';

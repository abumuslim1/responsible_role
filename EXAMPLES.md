# Примеры использования модуля Responsible Role

## Примеры PHP кода

### 1. Получить ответственного сотрудника для задачи

```php
<?php

// Подключить класс помощника
require_once('/path/to/responsible_role/class.php');

// Получить ID ответственного сотрудника
$iTaskId = 123;
$iResponsibleId = ResponsibleRoleHelper::getResponsibleEmployee($iTaskId);

if ($iResponsibleId) {
    $arUser = ResponsibleRoleHelper::getUserInfo($iResponsibleId);
    echo "Ответственный: " . $arUser['NAME'] . " " . $arUser['LAST_NAME'];
} else {
    echo "Ответственный не назначен";
}
?>
```

### 2. Установить ответственного сотрудника для задачи

```php
<?php

require_once('/path/to/responsible_role/class.php');

$iTaskId = 123;
$iUserId = 456;

$result = ResponsibleRoleHelper::setResponsibleEmployee($iTaskId, $iUserId);

if ($result) {
    echo "Ответственный успешно установлен";
} else {
    echo "Ошибка при установке ответственного";
}
?>
```

### 3. Получить все задачи ответственного сотрудника

```php
<?php

require_once('/path/to/responsible_role/class.php');

$iUserId = 456;

// Получить все задачи
$aTaskIds = ResponsibleRoleHelper::getTasksByResponsibleEmployee($iUserId);

echo "Количество задач: " . count($aTaskIds);

foreach ($aTaskIds as $iTaskId) {
    echo "Задача ID: " . $iTaskId . "\n";
}
?>
```

### 4. Получить статистику по ответственному

```php
<?php

require_once('/path/to/responsible_role/class.php');

$iUserId = 456;

$arStats = ResponsibleRoleHelper::getResponsibleStats($iUserId);

echo "Всего задач: " . $arStats['TOTAL_TASKS'] . "\n";
echo "Активных: " . $arStats['ACTIVE_TASKS'] . "\n";
echo "Завершено: " . $arStats['COMPLETED_TASKS'] . "\n";
?>
```

### 5. Получить информацию о пользователе

```php
<?php

require_once('/path/to/responsible_role/class.php');

$iUserId = 456;

$arUser = ResponsibleRoleHelper::getUserInfo($iUserId);

echo "Имя: " . $arUser['NAME'] . "\n";
echo "Фамилия: " . $arUser['LAST_NAME'] . "\n";
echo "Email: " . $arUser['EMAIL'] . "\n";
?>
```

### 6. Получить список всех пользователей

```php
<?php

require_once('/path/to/responsible_role/class.php');

$aUsers = ResponsibleRoleHelper::getAllUsers();

foreach ($aUsers as $iUserId => $sUserName) {
    echo $sUserName . " (ID: " . $iUserId . ")\n";
}
?>
```

### 7. Удалить ответственного из задачи

```php
<?php

require_once('/path/to/responsible_role/class.php');

$iTaskId = 123;

$result = ResponsibleRoleHelper::deleteResponsibleEmployee($iTaskId);

if ($result) {
    echo "Ответственный успешно удален";
}
?>
```

## Примеры JavaScript кода

### 1. Инициализация компонента

```javascript
// Конфигурация компонента
var config = {
    taskId: 123,
    fieldName: 'UF_RESPONSIBLE_EMPLOYEE',
    users: {
        456: 'Иван Иванов',
        789: 'Петр Петров',
        101: 'Мария Сидорова'
    },
    currentUser: 456
};

// Инициализация
var responsibleRole = new ResponsibleRole(config);
```

### 2. Получить выбранного пользователя

```javascript
var responsibleRole = new ResponsibleRole(config);

// Получить ID выбранного пользователя
var selectedUserId = responsibleRole.getSelectedUser();
console.log('Выбран пользователь: ' + selectedUserId);
```

### 3. Установить выбранного пользователя

```javascript
var responsibleRole = new ResponsibleRole(config);

// Установить пользователя
responsibleRole.setSelectedUser(789);
```

### 4. Применить фильтр по ответственному

```javascript
var responsibleRole = new ResponsibleRole(config);

// Применить фильтр
responsibleRole.applyFilter(456);
```

### 5. Получить задачи по ответственному (AJAX)

```javascript
var responsibleRole = new ResponsibleRole(config);

// Получить задачи
responsibleRole.getTasksByResponsible(456, function(tasks) {
    console.log('Задачи:', tasks);
});
```

## Примеры REST API

### 1. Получить ответственного для задачи

```javascript
BX24.callMethod(
    'responsible_role.task.getResponsible',
    { taskId: 123 },
    function(result) {
        if (result.error()) {
            console.error(result.error());
        } else {
            var responsible = result.data();
            console.log('Ответственный: ' + responsible.name);
            console.log('Email: ' + responsible.email);
        }
    }
);
```

### 2. Установить ответственного для задачи

```javascript
BX24.callMethod(
    'responsible_role.task.setResponsible',
    { 
        taskId: 123, 
        userId: 456 
    },
    function(result) {
        if (result.error()) {
            console.error(result.error());
        } else {
            console.log('Ответственный установлен');
        }
    }
);
```

### 3. Получить все задачи ответственного

```javascript
BX24.callMethod(
    'responsible_role.task.getByResponsible',
    { userId: 456 },
    function(result) {
        if (result.error()) {
            console.error(result.error());
        } else {
            var tasks = result.data().tasks;
            console.log('Количество задач: ' + tasks.length);
            
            tasks.forEach(function(taskId) {
                console.log('Задача ID: ' + taskId);
            });
        }
    }
);
```

### 4. Получить статистику

```javascript
BX24.callMethod(
    'responsible_role.task.getStats',
    { userId: 456 },
    function(result) {
        if (result.error()) {
            console.error(result.error());
        } else {
            var stats = result.data().stats;
            console.log('Всего задач: ' + stats.TOTAL_TASKS);
            console.log('Активных: ' + stats.ACTIVE_TASKS);
            console.log('Завершено: ' + stats.COMPLETED_TASKS);
        }
    }
);
```

### 5. Получить список пользователей

```javascript
BX24.callMethod(
    'responsible_role.user.getList',
    {},
    function(result) {
        if (result.error()) {
            console.error(result.error());
        } else {
            var users = result.data().users;
            
            for (var userId in users) {
                if (users.hasOwnProperty(userId)) {
                    console.log(userId + ': ' + users[userId]);
                }
            }
        }
    }
);
```

### 6. Удалить ответственного

```javascript
BX24.callMethod(
    'responsible_role.task.deleteResponsible',
    { taskId: 123 },
    function(result) {
        if (result.error()) {
            console.error(result.error());
        } else {
            console.log('Ответственный удален');
        }
    }
);
```

## Примеры HTML интеграции

### 1. Встроить поле в форму задачи

```html
<form id="task-form">
    <div class="form-group">
        <label for="task-title">Название задачи:</label>
        <input type="text" id="task-title" name="title" required>
    </div>
    
    <div class="form-group" id="responsible-role-field">
        <!-- Поле будет отрендерено компонентом -->
    </div>
    
    <div class="form-group">
        <button type="submit">Сохранить</button>
    </div>
</form>

<script src="/path/to/responsible_role.js"></script>
<script>
    var config = {
        taskId: null,
        users: {
            456: 'Иван Иванов',
            789: 'Петр Петров'
        }
    };
    
    new ResponsibleRole(config);
</script>
```

### 2. Отобразить информацию об ответственном

```html
<div id="task-responsible-details">
    <!-- Информация будет отрендерена компонентом -->
</div>

<script>
    var taskData = {
        responsibleEmployee: {
            name: 'Иван Иванов',
            email: 'ivan@example.com'
        }
    };
    
    var responsibleRole = new ResponsibleRole({});
    responsibleRole.displayTaskDetails(taskData);
</script>
```

### 3. Добавить фильтр в список задач

```html
<div class="task-filter">
    <form id="task-filter-form">
        <label for="filter-responsible-employee">Ответственный:</label>
        <select id="filter-responsible-employee" name="filter_responsible">
            <option value="">-- Все --</option>
            <option value="456">Иван Иванов</option>
            <option value="789">Петр Петров</option>
        </select>
        <button type="submit">Применить фильтр</button>
    </form>
</div>

<script src="/path/to/responsible_role.js"></script>
<script>
    var responsibleRole = new ResponsibleRole({});
    
    document.getElementById('task-filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var userId = document.getElementById('filter-responsible-employee').value;
        if (userId) {
            responsibleRole.applyFilter(userId);
        }
    });
</script>
```

## Примеры обработки событий

### 1. Обработка события при добавлении задачи

```php
<?php

// В файле events.php или в обработчике события

use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

$eventManager->registerEventHandler(
    'tasks',
    'OnTaskAdd',
    'my_module',
    'MyTaskHandler',
    'onTaskAdd'
);

class MyTaskHandler {
    public static function onTaskAdd(&$arTask) {
        // Получить ответственного
        $iResponsibleId = $arTask['UF_RESPONSIBLE_EMPLOYEE'] ?? null;
        
        if ($iResponsibleId) {
            // Выполнить какие-то действия
            echo "Задача добавлена с ответственным ID: " . $iResponsibleId;
        }
    }
}
?>
```

### 2. Обработка события при обновлении задачи

```php
<?php

$eventManager->registerEventHandler(
    'tasks',
    'OnTaskUpdate',
    'my_module',
    'MyTaskHandler',
    'onTaskUpdate'
);

class MyTaskHandler {
    public static function onTaskUpdate(&$arTask) {
        // Проверить изменение ответственного
        $iNewResponsibleId = $arTask['UF_RESPONSIBLE_EMPLOYEE'] ?? null;
        
        if ($iNewResponsibleId) {
            // Отправить уведомление
            echo "Ответственный изменен на ID: " . $iNewResponsibleId;
        }
    }
}
?>
```

## Советы и рекомендации

1. **Проверка установки**: Убедитесь, что модуль правильно установлен перед использованием API

2. **Обработка ошибок**: Всегда проверяйте результаты операций и обрабатывайте ошибки

3. **Производительность**: При работе с большим количеством задач используйте фильтры и пагинацию

4. **Безопасность**: Всегда валидируйте входные данные перед использованием

5. **Локализация**: Используйте функции локализации для отображения текстов на разных языках

6. **Кэширование**: Рассмотрите использование кэширования для часто запрашиваемых данных

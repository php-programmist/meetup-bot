# 🤖 Telegram Meetup Bot in PHP

Бот позволяет организовывать ежедневные встречи определенного круга участников чата. Бот отправляет 4 вида сообщений:
1. Опрос с вариантами ответа для определения списка присутствующих (initial)
2. Сообщение с вопросом, содержащее упоминание участников, которые не ответили на опрос в первом сообщении (notification)
3. Сообщение с приглашением подключаться и списком участников встречи, которые ответили положительно на один из предыдущих вопросов о намерении присутствовать (resume)
4. Предложение оценить работу ведущего со ссылкой на форму оценки

## 📦 Требования

* PHP 7.3+

## 👩‍💻 Настройка переменных окружения

Создайте файл `.env` на основе `.env.dist` и заполните значения переменных окружения:

`DATABASE_URL` - настройки подключения БД

`TELEGRAM_BOT_TOKEN` - токен доступа бота. Нужно получить с помощью бота в Telegram - [@Botfather](https://t.me/botfather)

`ROUTER_HOST` - домен сайта, на котором будет размещен бот. Пример: `example.com`. Данные должны передаваться по протоколу HTTPS, поэтому для домена должен быть установлен SSL-сертификат.

`ROUTER_BASE_URL` - если приложение бота находится в корне сайта, то не заполняйте этот параметр. Если же приложение находится в суб-директории, то укажите относительный путь к ней. Пример: `/path/to/folder`

`TELEGRAM_WEBHOOK_TOKEN` - произвольный набор символов любой длины. Будет добавлен автоматически к адресу вебхука для повышения безопасности

`TELEGRAM_CHAT_ID` - ID чата, в котором будет работать бот. Чтобы узнать ID, нужно добавить в чат бота - [@RawDataBot](https://t.me/RawDataBot). Он отправит в чат JSON, из которого нужно взять значение поля `chat_id`

## Установка и настройка

### Установка зависимостей и БД:

```bash
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Добавьте созданного Вами бота в чат и запустите команду установки вебхука:
```bash
php bin/console app:telegram:setup
```
После этого в чат должно прийти сообщение 'Webhook set successful'

### Создайте список участников чата 
Укажите имена и логины в Telegram (без @) участников чата, которые должны быть приглашены на ежедневные встречи. Добавлять, удалять и просматривать список можно с помощью соответствующих команд:
```bash
php bin/console app:member:add
php bin/console app:member:remove
php bin/console app:member:show
```
### Создайте список выходных дней. 
По этим дням бот не будет рассылать приглашения в чат. Добавлять, удалять и просматривать список можно с помощью соответствующих команд:
```bash
php bin/console app:holiday:add
php bin/console app:holiday:remove
php bin/console app:holiday:show
```
### Добавьте задания в Cron
Для того, чтобы происходила ежедневная отправка, необходимо добавить 4 задания в планировщике Cron с указанием времени отправки.

Пример:
```bash
0 11 * * 1-5 php /path/to/folder/bin/console app:telegram:send initial
20 11 * * 1-5 php /path/to/folder/bin/console app:telegram:send notification
27 11 * * 1-5 php /path/to/folder/bin/console app:telegram:send resume
0 12 * * 1-5 php /path/to/folder/bin/console app:telegram:send questionnaire
0 8 * * 1 php /path/to/folder/bin/console app:master:change
```
В 11:00 будет отправлено сообщение с опросом

В 11:20 - напоминание о необходимости ответа, если кто-то из участников еще не дал ответ на этот момент

В 11:27 - итоговый список участников с порядком выступления

В 12:00 - ссылка на оценивание Scrum-мастера. А по пятницам будет также указываться следующий Scrum-мастер

В 8:00 по понедельникам будет происходить смена Scrum-мастера (новый мастер будет выбран по умолчанию в опроснике оценивания мастера)
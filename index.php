<?php
declare(strict_types=1);

session_start();
date_default_timezone_set('Europe/Moscow');

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function phone_href(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (strlen($digits) === 11 && $digits[0] === '8') {
        $digits = '7' . substr($digits, 1);
    }

    return '+' . $digits;
}

function price_rub(int $price): string
{
    return number_format($price, 0, ',', ' ') . ' ₽';
}

function post_value(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

function opening_status(array $schedule): array
{
    $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow'));
    $dayNames = [
        0 => 'воскресенье',
        1 => 'понедельник',
        2 => 'вторник',
        3 => 'среда',
        4 => 'четверг',
        5 => 'пятница',
        6 => 'суббота',
    ];
    $day = (int)$now->format('w');
    $today = $schedule[$day] ?? null;

    if ($today !== null) {
        $start = new DateTimeImmutable($now->format('Y-m-d') . ' ' . $today['from']);
        $end = new DateTimeImmutable($now->format('Y-m-d') . ' ' . $today['to']);

        if ($now >= $start && $now < $end) {
            return [
                'state' => 'open',
                'label' => 'Открыто до ' . $today['to'],
                'detail' => 'Сегодня, ' . $dayNames[$day] . ': ' . $today['from'] . '-' . $today['to'],
            ];
        }

        if ($now < $start) {
            return [
                'state' => 'soon',
                'label' => 'Откроется сегодня в ' . $today['from'],
                'detail' => 'Сегодня, ' . $dayNames[$day] . ': ' . $today['from'] . '-' . $today['to'],
            ];
        }
    }

    for ($offset = 1; $offset <= 7; $offset++) {
        $nextDay = ($day + $offset) % 7;
        if (($schedule[$nextDay] ?? null) !== null) {
            $next = $schedule[$nextDay];
            $nextDate = $now->modify('+' . $offset . ' day');

            return [
                'state' => 'closed',
                'label' => 'Сейчас закрыто',
                'detail' => 'Ближайшее открытие: ' . $dayNames[$nextDay] . ', ' . $nextDate->format('d.m') . ' в ' . $next['from'],
            ];
        }
    }

    return [
        'state' => 'closed',
        'label' => 'Сейчас закрыто',
        'detail' => 'Уточните график по телефону',
    ];
}

$business = [
    'name' => 'Тон+',
    'rating' => '5,0',
    'ratingValue' => '5.0',
    'ratingCount' => 144,
    'reviewCount' => 68,
    'address' => 'ул. Корнеева, 14, Домодедово',
    'mapUrl' => 'https://yandex.ru/maps/-/CPXuaCI9',
    'phones' => [
        '+7 (916) 218-21-63',
        '+7 (926) 556-44-70',
        '+7 (499) 707-13-30',
    ],
];

$schedule = [
    1 => ['label' => 'Понедельник', 'from' => '09:00', 'to' => '20:00'],
    2 => ['label' => 'Вторник', 'from' => '09:00', 'to' => '20:00'],
    3 => ['label' => 'Среда', 'from' => '09:00', 'to' => '20:00'],
    4 => ['label' => 'Четверг', 'from' => '09:00', 'to' => '20:00'],
    5 => ['label' => 'Пятница', 'from' => '09:00', 'to' => '20:00'],
    6 => ['label' => 'Суббота', 'from' => '09:00', 'to' => '17:00'],
    0 => null,
];

$services = [
    [
        'id' => 'tint-gost',
        'title' => 'Тонировка стёкол по ГОСТу',
        'price' => 5500,
        'image' => 'assets/service-tint.png',
        'imageAlt' => 'Нанесение тонировочной плёнки на боковое стекло автомобиля',
        'summary' => 'Подбор и установка плёнки с учётом требований к светопропусканию.',
        'clientValue' => 'Для тех, кто хочет затемнить авто без лишнего риска по правилам.',
        'proof' => 'В отзывах отмечают консультацию по качеству и свойствам плёнки.',
    ],
    [
        'id' => 'alarm',
        'title' => 'Установка сигнализаций и допоборудования',
        'price' => 5000,
        'image' => 'assets/service-alarm.png',
        'imageAlt' => 'Диагностика электрооборудования автомобиля в тёмном сервисном цехе',
        'summary' => 'Монтаж охранных систем, парктроников и другого автооборудования.',
        'clientValue' => 'Перед работой можно понять состав установки и итоговую стоимость.',
        'proof' => 'Клиенты пишут, что мастера объясняют работу оборудования при выдаче.',
    ],
    [
        'id' => 'alarm-autostart',
        'title' => 'Сигнализация с автозапуском',
        'price' => 10000,
        'image' => 'assets/service-alarm.png',
        'imageAlt' => 'Техническая диагностика и подключение автомобильной электроники',
        'summary' => 'Установка с сохранением заводской гарантии и сертификатом соответствия.',
        'clientValue' => 'Подходит, когда важны комфорт, безопасность и документы по установке.',
        'proof' => 'В карточке указаны сертификат соответствия и сохранение заводской гарантии.',
    ],
    [
        'id' => 'glass-film',
        'title' => 'Тонирование и бронирование стёкол',
        'price' => 2500,
        'image' => 'assets/service-tint.png',
        'imageAlt' => 'Плёнка на автомобильном стекле во время установки',
        'summary' => 'Плёнка для стёкол с защитной и визуальной задачей.',
        'clientValue' => 'Можно закрыть конкретную зону, а не оплачивать лишний объём работ.',
        'proof' => 'В отзывах часто упоминают аккуратность и скорость работ со стеклом.',
    ],
    [
        'id' => 'body-film',
        'title' => 'Защитная плёнка на кузов',
        'price' => 5000,
        'image' => 'assets/service-body-film.png',
        'imageAlt' => 'Защитная обработка кузова автомобиля в индустриальном цехе',
        'summary' => 'Нанесение защитной плёнки на элементы кузова.',
        'clientValue' => 'Помогает защитить зоны риска от мелких повреждений.',
        'proof' => 'Клиенты отмечают качество материалов и выполнения работ.',
    ],
    [
        'id' => 'windshield-repair',
        'title' => 'Ремонт сколов и трещин стекла',
        'price' => 1000,
        'image' => 'assets/service-glass.png',
        'imageAlt' => 'Ремонт скола на лобовом стекле с помощью смолы и УФ-лампы',
        'summary' => 'Ремонт лобовых стёкол, сколов и трещин.',
        'clientValue' => 'Быстрая диагностика повреждения до замены стекла.',
        'proof' => 'По отзывам, небольшие сколы закрывали быстро; итог зависит от состояния стекла.',
    ],
];

$serviceIndex = [];
foreach ($services as $service) {
    $serviceIndex[$service['id']] = $service;
}

$features = [
    ['title' => 'Предварительная запись', 'text' => 'Можно согласовать время до приезда и не ждать свободного окна на месте.'],
    ['title' => 'Постоплата', 'text' => 'В карточке указана постоплата; также доступны наличные, карта и СБП.'],
    ['title' => 'Гарантия', 'text' => 'Гарантия указана среди особенностей сервиса, а по сигнализациям есть сертификат соответствия.'],
    ['title' => 'Удобства на месте', 'text' => 'Есть парковка, Wi-Fi, туалет и доступность для посетителей на инвалидной коляске.'],
];

$reviews = [
    [
        'name' => 'Валерий Ташкинов',
        'date' => '21 марта',
        'text' => 'Позвонил, записался в этот же день. Мастер объяснил, что нужно сделать и сколько это будет стоить.',
        'tag' => 'Цена до работы',
    ],
    [
        'name' => 'Anastasiya K.',
        'date' => '26 ноября 2025',
        'text' => 'Делала тонировку: рассказали про качество и свойства плёнки, работали внимательно и спокойно.',
        'tag' => 'Консультация',
    ],
    [
        'name' => 'ААВ',
        'date' => '16 февраля 2025',
        'text' => 'Устанавливал парктроник и сигнализацию. Отмечает оперативность, грамотность и честность в оплате.',
        'tag' => 'Допоборудование',
    ],
    [
        'name' => 'Алексей Колесников',
        'date' => '23 мая 2024',
        'text' => 'Сигнализацию с автозапуском сделали день-в-день, комплект распаковали при клиенте, при выдаче всё объяснили.',
        'tag' => 'Сигнализация',
    ],
    [
        'name' => 'Дмитрий Владимиров',
        'date' => '3 октября 2021',
        'text' => 'Ремонтировал скол на стекле: в отзыве отдельно отметил быстрый приём по времени и аккуратный результат.',
        'tag' => 'Ремонт стекла',
    ],
];

$objections = [
    ['title' => 'Не хочется переплатить', 'text' => 'На сайте показаны стартовые цены из карточки. Итоговую стоимость мастер называет до начала работы, когда понятен объём.'],
    ['title' => 'Нужна уверенность в качестве', 'text' => 'Рейтинг 5,0, 144 оценки, 68 отзывов. В отзывах чаще всего повторяются: аккуратно, быстро, объяснили, не обманули в оплате.'],
    ['title' => 'Неясны сроки', 'text' => 'Запись идёт по времени. По отзывам часть работ делали в день обращения, но точный срок зависит от услуги, автомобиля и состояния стекла.'],
    ['title' => 'Нужно быстро связаться', 'text' => 'Основные телефоны, карта и форма заявки доступны с первого экрана и закреплены снизу на мобильном.'],
];

$status = opening_status($schedule);
$formErrors = [];
$formSuccess = false;
$posted = [
    'name' => '',
    'phone' => '',
    'service' => $services[0]['id'],
    'preferred_time' => '',
    'comment' => '',
];

$requestMethod = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($requestMethod === 'POST') {
    $posted = [
        'name' => post_value('name'),
        'phone' => post_value('phone'),
        'service' => post_value('service'),
        'preferred_time' => post_value('preferred_time'),
        'comment' => post_value('comment'),
    ];

    $token = (string)($_POST['csrf_token'] ?? '');
    $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
    $honeypot = post_value('website');

    if ($honeypot !== '') {
        $formSuccess = true;
    } elseif ($sessionToken === '' || !hash_equals($sessionToken, $token)) {
        $formErrors[] = 'Не удалось проверить форму. Обновите страницу и отправьте заявку ещё раз.';
    } else {
        if ($posted['phone'] === '') {
            $formErrors[] = 'Укажите телефон для обратного звонка.';
        } elseif (!preg_match('/^[0-9+\-\s()]{7,24}$/u', $posted['phone'])) {
            $formErrors[] = 'Проверьте формат телефона.';
        }

        if (!isset($serviceIndex[$posted['service']])) {
            $formErrors[] = 'Выберите услугу из списка.';
        }

        if ($posted['comment'] !== '' && mb_strlen($posted['comment']) > 600) {
            $formErrors[] = 'Комментарий слишком длинный. Оставьте до 600 символов.';
        }

        if ($formErrors === []) {
            $storageDir = __DIR__ . DIRECTORY_SEPARATOR . 'storage';
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0775, true);
            }

            $leadFile = $storageDir . DIRECTORY_SEPARATOR . 'leads.csv';
            $isNewFile = !is_file($leadFile);
            $handle = fopen($leadFile, 'ab');

            if ($handle === false) {
                $formErrors[] = 'Заявку не удалось сохранить. Пожалуйста, позвоните в сервис.';
            } else {
                if ($isNewFile) {
                    fputcsv($handle, ['created_at', 'name', 'phone', 'service', 'preferred_time', 'comment'], ';');
                }

                fputcsv($handle, [
                    (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
                    $posted['name'] !== '' ? $posted['name'] : 'Не указано',
                    $posted['phone'],
                    $serviceIndex[$posted['service']]['title'],
                    $posted['preferred_time'],
                    $posted['comment'],
                ], ';');
                fclose($handle);

                $formSuccess = true;
                $posted = [
                    'name' => '',
                    'phone' => '',
                    'service' => $services[0]['id'],
                    'preferred_time' => '',
                    'comment' => '',
                ];
            }
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(16));
$csrfToken = $_SESSION['csrf_token'];

$servicesForJs = [];
foreach ($services as $service) {
    $servicesForJs[$service['id']] = [
        'title' => $service['title'],
        'price' => price_rub($service['price']),
        'summary' => $service['summary'],
    ];
}

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'AutoRepair',
    'name' => $business['name'],
    'description' => 'Тонировка стёкол, установка сигнализаций, автозапуск, бронирование стёкол, защитная плёнка и ремонт сколов в Домодедово.',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'ул. Корнеева, 14',
        'addressLocality' => 'Домодедово',
        'addressCountry' => 'RU',
    ],
    'telephone' => $business['phones'],
    'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => $business['ratingValue'],
        'ratingCount' => $business['ratingCount'],
        'reviewCount' => $business['reviewCount'],
    ],
    'openingHoursSpecification' => [
        ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'opens' => '09:00', 'closes' => '20:00'],
        ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'Saturday', 'opens' => '09:00', 'closes' => '17:00'],
    ],
    'paymentAccepted' => 'Cash, Bank Card, SBP',
    'sameAs' => $business['mapUrl'],
    'hasOfferCatalog' => [
        '@type' => 'OfferCatalog',
        'name' => 'Услуги Тон+',
        'itemListElement' => array_map(static function (array $service): array {
            return [
                '@type' => 'Offer',
                'name' => $service['title'],
                'price' => (string)$service['price'],
                'priceCurrency' => 'RUB',
                'description' => $service['summary'],
            ];
        }, $services),
    ],
];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Тон+ Домодедово: тонировка, сигнализации, ремонт стекол</title>
    <meta name="description" content="Тон+ в Домодедово: тонировка стекол по ГОСТу, установка сигнализаций, автозапуск, защитная пленка и ремонт сколов. Рейтинг 5,0, 144 оценки, запись по телефону.">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Тон+ — тонировка, сигнализации и ремонт стекол в Домодедово">
    <meta property="og:description" content="Цены от 1 000 ₽, предварительная запись, постоплата, гарантия, 5,0 по 144 оценкам.">
    <meta property="og:image" content="assets/industrial-hero.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="preload" href="assets/industrial-hero.png" as="image">
    <link rel="stylesheet" href="assets/styles.css">
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
</head>
<body data-form-state="<?= $formSuccess || $formErrors !== [] ? 'submitted' : 'idle' ?>">
<a class="skip-link" href="#main">К содержанию</a>

<header class="site-header" data-header>
    <div class="container header-grid">
        <a class="brand magnetic-link" href="#top" aria-label="Тон+ на первый экран">
            <span class="brand-mark" aria-hidden="true"><span>Т+</span></span>
            <span>
                <strong>Тон+</strong>
                <small>автостекла и допоборудование</small>
            </span>
        </a>
        <nav class="nav-links" aria-label="Основная навигация">
            <a href="#services">Услуги</a>
            <a href="#process">Как работаем</a>
            <a href="#reviews">Отзывы</a>
            <a href="#contacts">Контакты</a>
        </nav>
        <div class="header-actions">
            <a class="header-phone magnetic-link" href="tel:<?= e(phone_href($business['phones'][0])) ?>"><?= e($business['phones'][0]) ?></a>
            <a class="button button--small" href="#booking">Записаться</a>
        </div>
    </div>
</header>

<main id="main">
    <section class="hero" id="top" aria-labelledby="hero-title">
        <div class="container hero-content">
            <p class="eyebrow">Домодедово, <?= e($business['address']) ?></p>
            <h1 id="hero-title">Тонировка, сигнализации и ремонт автостёкол без сюрпризов по цене</h1>
            <p class="hero-lead">Тон+ помогает быстро закрыть понятные задачи по автомобилю: затонировать стёкла, установить сигнализацию или автозапуск, защитить плёнкой кузов и отремонтировать скол на лобовом стекле.</p>
            <div class="hero-actions" aria-label="Основные действия">
                <a class="button button--accent" href="#booking">Выбрать услугу и записаться</a>
                <a class="button button--ghost" href="tel:<?= e(phone_href($business['phones'][0])) ?>">Позвонить сейчас</a>
            </div>
            <div class="hero-signal" aria-label="Профиль сервиса">
                <span>WINDOW FILM</span>
                <span>SECURITY SYSTEMS</span>
                <span>GLASS REPAIR</span>
            </div>
            <dl class="trust-strip" aria-label="Ключевые факты">
                <div>
                    <dt>Рейтинг</dt>
                    <dd><?= e($business['rating']) ?> / <?= e((string)$business['ratingCount']) ?> оценки</dd>
                </div>
                <div>
                    <dt>Сегодня</dt>
                    <dd data-work-status="label"><?= e($status['label']) ?></dd>
                </div>
                <div>
                    <dt>Цена</dt>
                    <dd>от 1 000 ₽ по карточке</dd>
                </div>
                <div>
                    <dt>Оплата</dt>
                    <dd>постоплата, карта, СБП</dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="section service-flow" id="services" aria-labelledby="services-title">
        <div class="container">
            <div class="section-intro">
                <span class="section-kicker">Сначала понятная услуга</span>
                <h2 id="services-title">Выберите, что нужно сделать с автомобилем</h2>
                <p>Каждая карточка показывает стартовую цену из Яндекс.Карт, что входит по смыслу и какой страх клиента она закрывает. Итоговую стоимость стоит подтвердить по телефону до приезда.</p>
            </div>

            <div class="service-layout">
                <div class="service-list" aria-label="Список услуг">
                    <?php foreach ($services as $service): ?>
                        <article class="service-card<?= $service['id'] === $posted['service'] ? ' is-active' : '' ?>" data-service-card="<?= e($service['id']) ?>">
                            <figure class="service-card__media">
                                <img src="<?= e($service['image']) ?>" alt="<?= e($service['imageAlt']) ?>" loading="lazy" width="640" height="480">
                            </figure>
                            <div class="service-card__top">
                                <h3><?= e($service['title']) ?></h3>
                                <strong>от <?= e(price_rub($service['price'])) ?></strong>
                            </div>
                            <p><?= e($service['summary']) ?></p>
                            <p class="service-card__value"><?= e($service['clientValue']) ?></p>
                            <small><?= e($service['proof']) ?></small>
                            <button class="text-button" type="button" data-choose-service="<?= e($service['id']) ?>">Выбрать</button>
                        </article>
                    <?php endforeach; ?>
                </div>

                <aside class="booking-card" id="booking" aria-labelledby="booking-title">
                    <span class="status-pill status-pill--<?= e($status['state']) ?>" data-work-status-pill data-work-status="label"><?= e($status['label']) ?></span>
                    <h2 id="booking-title">Запись без длинной переписки</h2>
                    <p class="booking-card__note"><span data-work-status="detail"><?= e($status['detail']) ?></span>. Оставьте телефон или позвоните напрямую.</p>

                    <?php if ($formSuccess): ?>
                        <div class="form-message form-message--success" role="status">
                            Заявка сохранена. Если вопрос срочный, лучше сразу позвонить по первому номеру.
                        </div>
                    <?php endif; ?>

                    <?php if ($formErrors !== []): ?>
                        <div class="form-message form-message--error" role="alert">
                            <strong>Проверьте форму:</strong>
                            <ul>
                                <?php foreach ($formErrors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form class="lead-form" method="post" action="#booking" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <label class="hp-field" for="website">Сайт</label>
                        <input class="hp-field" id="website" name="website" type="text" tabindex="-1" autocomplete="off">

                        <label for="service">Услуга</label>
                        <select id="service" name="service" required>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= e($service['id']) ?>"<?= $posted['service'] === $service['id'] ? ' selected' : '' ?>>
                                    <?= e($service['title']) ?> — от <?= e(price_rub($service['price'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="estimate" aria-live="polite">
                            <span>Ориентир по цене</span>
                            <strong data-price-output>от <?= e(price_rub($serviceIndex[$posted['service']]['price'] ?? $services[0]['price'])) ?></strong>
                            <p data-summary-output><?= e($serviceIndex[$posted['service']]['summary'] ?? $services[0]['summary']) ?></p>
                        </div>

                        <label for="phone">Телефон для записи</label>
                        <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+7 999 000-00-00" value="<?= e($posted['phone']) ?>" required>

                        <label for="name">Имя</label>
                        <input id="name" name="name" type="text" autocomplete="name" placeholder="Как к вам обращаться" value="<?= e($posted['name']) ?>">

                        <label for="preferred_time">Удобное время</label>
                        <input id="preferred_time" name="preferred_time" type="text" placeholder="Например: сегодня после 16:00" value="<?= e($posted['preferred_time']) ?>">

                        <label for="comment">Комментарий</label>
                        <textarea id="comment" name="comment" rows="4" placeholder="Марка авто, что нужно сделать, размер скола или вопрос по сигнализации"><?= e($posted['comment']) ?></textarea>

                        <button class="button button--accent button--wide" type="submit">Отправить заявку</button>
                        <p class="form-privacy">Нажимая кнопку, вы передаёте телефон только для обратного звонка по заявке.</p>
                    </form>
                </aside>
            </div>
        </div>
    </section>

    <section class="section objections-section" aria-labelledby="objections-title">
        <div class="container">
            <div class="section-intro section-intro--narrow">
                <span class="section-kicker">Что важно до записи</span>
                <h2 id="objections-title">Закрываем типичные сомнения заранее</h2>
            </div>
            <div class="objection-grid">
                <?php foreach ($objections as $item): ?>
                    <article class="objection-item">
                        <h3><?= e($item['title']) ?></h3>
                        <p><?= e($item['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section process-section" id="process" aria-labelledby="process-title">
        <div class="container">
            <div class="section-intro">
                <span class="section-kicker">Процесс без тумана</span>
                <h2 id="process-title">Как проходит обращение</h2>
                <p>Путь сделан коротким для мобильного пользователя: сначала услуга и звонок, потом уточнение объёма, работа по времени и оплата после результата.</p>
            </div>
            <ol class="process-list">
                <li>
                    <span>01</span>
                    <h3>Вы выбираете услугу</h3>
                    <p>На сайте сразу видны основные направления и стартовые цены.</p>
                </li>
                <li>
                    <span>02</span>
                    <h3>Мастер уточняет детали</h3>
                    <p>Марка авто, тип плёнки, сигнализация, состояние стекла и ожидаемый срок.</p>
                </li>
                <li>
                    <span>03</span>
                    <h3>Фиксируется время</h3>
                    <p>В карточке указана предварительная запись, поэтому визит проще планировать заранее.</p>
                </li>
                <li>
                    <span>04</span>
                    <h3>Работа и постоплата</h3>
                    <p>После выполнения можно оплатить наличными, картой или через СБП.</p>
                </li>
            </ol>
        </div>
    </section>

    <section class="section features-section" aria-labelledby="features-title">
        <div class="container">
            <div class="section-intro">
                <span class="section-kicker">Факты из карточки</span>
                <h2 id="features-title">Почему клиенту проще довериться</h2>
            </div>
            <div class="feature-grid">
                <?php foreach ($features as $feature): ?>
                    <article class="feature-card">
                        <h3><?= e($feature['title']) ?></h3>
                        <p><?= e($feature['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section reviews-section" id="reviews" aria-labelledby="reviews-title">
        <div class="container reviews-layout">
            <div class="reviews-summary">
                <span class="section-kicker">Социальное доказательство</span>
                <h2 id="reviews-title">5,0 по 144 оценкам</h2>
                <p>В карточке Яндекс.Карт указано 68 отзывов. Ниже вынесены повторяющиеся смыслы: объясняют цену, принимают по записи, работают аккуратно, помогают с тонировкой, сигнализациями и стеклом.</p>
                <a class="button button--outline" href="<?= e($business['mapUrl']) ?>" target="_blank" rel="noopener">Открыть карточку на Яндекс.Картах</a>
            </div>
            <div class="review-list" aria-label="Отзывы клиентов">
                <?php foreach ($reviews as $review): ?>
                    <article class="review-card">
                        <div>
                            <strong><?= e($review['name']) ?></strong>
                            <span><?= e($review['date']) ?></span>
                        </div>
                        <p><?= e($review['text']) ?></p>
                        <small><?= e($review['tag']) ?></small>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section caution-section" aria-labelledby="caution-title">
        <div class="container caution-box">
            <div>
                <span class="section-kicker">Честная рамка</span>
                <h2 id="caution-title">По сколам и трещинам сначала нужна оценка</h2>
            </div>
            <p>Ремонт стекла зависит от размера, направления трещины и давности повреждения. Чтобы не обещать лишнего, лучше описать повреждение по телефону или показать мастеру на месте; после этого понятны риск, срок и цена.</p>
        </div>
    </section>

    <section class="section contacts-section" id="contacts" aria-labelledby="contacts-title">
        <div class="container contacts-layout">
            <div>
                <span class="section-kicker">Контакты</span>
                <h2 id="contacts-title">Тон+ на ул. Корнеева, 14</h2>
                <p>Записывайтесь заранее: это снижает ожидание на месте и помогает мастеру подготовиться под конкретную работу.</p>
                <div class="contact-actions">
                    <?php foreach ($business['phones'] as $phone): ?>
                        <a class="contact-link" href="tel:<?= e(phone_href($phone)) ?>"><?= e($phone) ?></a>
                    <?php endforeach; ?>
                </div>
                <a class="button button--accent" href="<?= e($business['mapUrl']) ?>" target="_blank" rel="noopener">Построить маршрут</a>
            </div>
            <div class="contact-panel">
                <h3>График работы</h3>
                <ul class="schedule-list">
                    <?php foreach ([1, 2, 3, 4, 5, 6, 0] as $day): ?>
                        <li>
                            <span><?= e($schedule[$day]['label'] ?? 'Воскресенье') ?></span>
                            <strong><?= $schedule[$day] === null ? 'Выходной' : e($schedule[$day]['from'] . '-' . $schedule[$day]['to']) ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="address-line"><?= e($business['address']) ?></p>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <p>© <?= date('Y') ?> <?= e($business['name']) ?>. Данные лендинга собраны из карточки Яндекс.Карт и требуют проверки перед публикацией.</p>
        <a href="#top">Наверх</a>
    </div>
</footer>

<div class="mobile-cta" aria-label="Быстрые действия">
    <a href="#booking">Записаться</a>
    <a href="tel:<?= e(phone_href($business['phones'][0])) ?>">Позвонить</a>
</div>

<script type="application/json" id="services-data"><?= json_encode($servicesForJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="assets/app.js" defer></script>
</body>
</html>

(() => {
    const dataNode = document.getElementById('services-data');
    const serviceSelect = document.getElementById('service');
    const priceOutput = document.querySelector('[data-price-output]');
    const summaryOutput = document.querySelector('[data-summary-output]');
    const serviceCards = Array.from(document.querySelectorAll('[data-service-card]'));
    const header = document.querySelector('[data-header]');
    const phoneInput = document.getElementById('phone');
    const mobileCta = document.querySelector('.mobile-cta');
    const mobileQuery = window.matchMedia('(max-width: 760px)');
    const staticPreview = document.documentElement.dataset.staticPreview === 'true';

    if (!dataNode || !serviceSelect || !priceOutput || !summaryOutput) {
        return;
    }

    const services = JSON.parse(dataNode.textContent || '{}');

    const updateService = (id) => {
        const service = services[id];
        if (!service) {
            return;
        }

        serviceSelect.value = id;
        priceOutput.textContent = `от ${service.price}`;
        summaryOutput.textContent = service.summary;

        serviceCards.forEach((card) => {
            card.classList.toggle('is-active', card.dataset.serviceCard === id);
        });
    };

    serviceCards.forEach((card) => {
        const button = card.querySelector('[data-choose-service]');
        if (!button) {
            return;
        }

        button.addEventListener('click', () => {
            updateService(card.dataset.serviceCard);
            scrollToBooking();
            window.setTimeout(() => phoneInput?.focus({ preventScroll: true }), mobileQuery.matches ? 0 : 420);
        });
    });

    serviceSelect.addEventListener('change', () => updateService(serviceSelect.value));
    updateService(serviceSelect.value);

    const updateWorkStatus = () => {
        const parts = new Intl.DateTimeFormat('ru-RU', {
            timeZone: 'Europe/Moscow',
            weekday: 'long',
            day: '2-digit',
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }).formatToParts(new Date());

        const getPart = (type) => parts.find((part) => part.type === type)?.value || '';
        const weekday = getPart('weekday').toLowerCase();
        const day = getPart('day');
        const month = getPart('month');
        const minutes = Number(getPart('hour')) * 60 + Number(getPart('minute'));
        const schedule = {
            'понедельник': { from: 540, to: 1200, fromText: '09:00', toText: '20:00', label: 'понедельник' },
            'вторник': { from: 540, to: 1200, fromText: '09:00', toText: '20:00', label: 'вторник' },
            'среда': { from: 540, to: 1200, fromText: '09:00', toText: '20:00', label: 'среда' },
            'четверг': { from: 540, to: 1200, fromText: '09:00', toText: '20:00', label: 'четверг' },
            'пятница': { from: 540, to: 1200, fromText: '09:00', toText: '20:00', label: 'пятница' },
            'суббота': { from: 540, to: 1020, fromText: '09:00', toText: '17:00', label: 'суббота' },
        };
        const order = ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'];
        const today = schedule[weekday];
        let state = 'closed';
        let label = 'Сейчас закрыто';
        let detail = 'Уточните ближайшее время по телефону';

        if (today && minutes >= today.from && minutes < today.to) {
            state = 'open';
            label = `Открыто до ${today.toText}`;
            detail = `Сегодня, ${today.label}: ${today.fromText}-${today.toText}`;
        } else if (today && minutes < today.from) {
            state = 'soon';
            label = `Откроется сегодня в ${today.fromText}`;
            detail = `Сегодня, ${today.label}: ${today.fromText}-${today.toText}`;
        } else {
            const currentIndex = order.indexOf(weekday);
            for (let offset = 1; offset <= 7; offset += 1) {
                const nextName = order[(currentIndex + offset) % 7];
                const next = schedule[nextName];
                if (!next) continue;

                const nextDate = new Date();
                nextDate.setDate(nextDate.getDate() + offset);
                const nextParts = new Intl.DateTimeFormat('ru-RU', {
                    timeZone: 'Europe/Moscow',
                    day: '2-digit',
                    month: '2-digit',
                }).formatToParts(nextDate);
                const nextDay = nextParts.find((part) => part.type === 'day')?.value || day;
                const nextMonth = nextParts.find((part) => part.type === 'month')?.value || month;
                detail = `Ближайшее открытие: ${next.label}, ${nextDay}.${nextMonth} в ${next.fromText}`;
                break;
            }
        }

        document.querySelectorAll('[data-work-status="label"]').forEach((node) => {
            node.textContent = label;
        });
        document.querySelectorAll('[data-work-status="detail"]').forEach((node) => {
            node.textContent = detail;
        });
        document.querySelectorAll('[data-work-status-pill]').forEach((node) => {
            node.classList.remove('status-pill--open', 'status-pill--soon', 'status-pill--closed');
            node.classList.add(`status-pill--${state}`);
        });
    };

    updateWorkStatus();

    const formatPhone = (value) => {
        const digits = value.replace(/\D/g, '').slice(0, 11);
        const normalized = digits.startsWith('8') ? `7${digits.slice(1)}` : digits;

        if (!normalized) {
            return '';
        }

        if (!normalized.startsWith('7')) {
            return `+${normalized}`;
        }

        const parts = [
            normalized.slice(1, 4),
            normalized.slice(4, 7),
            normalized.slice(7, 9),
            normalized.slice(9, 11),
        ];

        let result = '+7';
        if (parts[0]) result += ` (${parts[0]}`;
        if (parts[0]?.length === 3) result += ')';
        if (parts[1]) result += ` ${parts[1]}`;
        if (parts[2]) result += `-${parts[2]}`;
        if (parts[3]) result += `-${parts[3]}`;

        return result;
    };

    phoneInput?.addEventListener('input', () => {
        const caretAtEnd = phoneInput.selectionStart === phoneInput.value.length;
        phoneInput.value = formatPhone(phoneInput.value);
        if (caretAtEnd) {
            phoneInput.selectionStart = phoneInput.value.length;
            phoneInput.selectionEnd = phoneInput.value.length;
        }
    });

    const updateHeader = () => {
        header?.classList.toggle('is-scrolled', window.scrollY > 8);
        mobileCta?.classList.toggle('is-visible', window.scrollY > Math.min(520, window.innerHeight * 0.65));
    };

    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    if (document.body.dataset.formState === 'submitted') {
        scrollToBooking();
    }

    if (staticPreview) {
        document.querySelector('.lead-form')?.addEventListener('submit', (event) => {
            event.preventDefault();
            const existing = document.querySelector('[data-static-form-message]');
            if (existing) {
                existing.remove();
            }
            const message = document.createElement('div');
            message.className = 'form-message form-message--success';
            message.dataset.staticFormMessage = 'true';
            message.setAttribute('role', 'status');
            message.textContent = 'Это статическая версия на GitHub Pages. Для записи позвоните в Тон+ по телефону на сайте.';
            document.querySelector('.lead-form')?.before(message);
            message.scrollIntoView({ behavior: mobileQuery.matches ? 'auto' : 'smooth', block: 'center' });
        });
    }
})();
    const scrollToBooking = () => {
        document.getElementById('booking')?.scrollIntoView({
            behavior: mobileQuery.matches ? 'auto' : 'smooth',
            block: 'start',
        });
    };

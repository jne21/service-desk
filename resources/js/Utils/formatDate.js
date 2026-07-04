function pad(value) {
    return String(value).padStart(2, '0');
}

export function formatDate(value) {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    const now = new Date();

    const day = pad(date.getDate());
    const month = pad(date.getMonth() + 1);
    const year = date.getFullYear();

    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());

    const isToday =
        date.getFullYear() === now.getFullYear() &&
        date.getMonth() === now.getMonth() &&
        date.getDate() === now.getDate();

    if (isToday) {
        return `${hours}:${minutes}`;
    }

    const isCurrentYear = year === now.getFullYear();

    if (isCurrentYear) {
        return `${day}.${month} ${hours}:${minutes}`;
    }

    return `${day}.${month}.${year} ${hours}:${minutes}`;
}

import dayjs from 'dayjs';

/**
 * Composable for formatting dates using Day.js.
 */
export function useDate() {
    const formatDate = (date) => {
        if (!date) return '';
        return dayjs(date).format('MMMM D, YYYY');
    };

    const formatShortDate = (date) => {
        if (!date) return '';
        return dayjs(date).format('MMM D, YYYY');
    };

    const formatMonthYear = (month, year) => {
        return dayjs().month(month - 1).year(year).format('MMMM YYYY');
    };

    const formatRelative = (date) => {
        if (!date) return '';
        const d = dayjs(date);
        const diff = d.diff(dayjs(), 'day');
        if (diff === 0) return 'Today';
        if (diff === 1) return 'Tomorrow';
        if (diff === -1) return 'Yesterday';
        if (diff > 0 && diff <= 7) return `In ${diff} days`;
        return formatShortDate(date);
    };

    return { formatDate, formatShortDate, formatMonthYear, formatRelative };
}

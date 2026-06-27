import dayjs from 'dayjs';

/**
 * Composable for formatting dates using Day.js.
 */
export function useDate() {
    const formatDate = (date: string | Date | null | undefined): string => {
        if (!date) return '';
        return dayjs(date).format('MMMM D, YYYY');
    };

    const formatShortDate = (date: string | Date | null | undefined): string => {
        if (!date) return '';
        return dayjs(date).format('MMM D, YYYY');
    };

    const formatMonthYear = (month: number, year: number): string => {
        return dayjs().month(month - 1).year(year).format('MMMM YYYY');
    };

    const formatRelative = (date: string | Date | null | undefined): string => {
        if (!date) return '';
        const today = dayjs().format('YYYY-MM-DD');
        const target = dayjs(date).format('YYYY-MM-DD');
        const diff = dayjs(target).diff(dayjs(today), 'day');
        if (diff === 0) return 'Today';
        if (diff === 1) return 'Tomorrow';
        if (diff === -1) return 'Yesterday';
        if (diff > 0 && diff <= 7) return `In ${diff} days`;
        return formatShortDate(date);
    };

    return { formatDate, formatShortDate, formatMonthYear, formatRelative };
}

/**
 * Composable for formatting Philippine Peso currency values.
 */
export function useCurrency() {
    /**
     * Format a number as Philippine Peso.
     * @param {number | string | null | undefined} amount
     * @returns {string} e.g. "₱ 1,234,567.89"
     */
    const formatPeso = (amount: number | string | null | undefined): string => {
        if (amount === null || amount === undefined) return '₱ 0.00';
        const num = typeof amount === 'string' ? parseFloat(amount) : amount;
        if (isNaN(num)) return '₱ 0.00';
        const formatted = Math.abs(num).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
        return num < 0 ? `-₱ ${formatted}` : `₱ ${formatted}`;
    };

    /**
     * Format a number as a short currency value.
     * @param {number | string} amount
     * @returns {string} e.g. "₱ 1.2K"
     */
    const formatShort = (amount: number | string): string => {
        const num = typeof amount === 'string' ? parseFloat(amount) : amount;
        if (isNaN(num)) return '₱ 0.00';
        if (Math.abs(num) >= 1_000_000) return `₱ ${(num / 1_000_000).toFixed(1)}M`;
        if (Math.abs(num) >= 1_000) return `₱ ${(num / 1_000).toFixed(1)}K`;
        return formatPeso(num);
    };

    return { formatPeso, formatShort };
}

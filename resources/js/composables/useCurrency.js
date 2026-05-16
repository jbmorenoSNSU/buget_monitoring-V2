/**
 * Composable for formatting Philippine Peso currency values.
 */
export function useCurrency() {
    /**
     * Format a number as Philippine Peso.
     * @param {number} amount
     * @returns {string} e.g. "₱ 1,234,567.89"
     */
    const formatPeso = (amount) => {
        if (amount === null || amount === undefined) return '₱ 0.00';
        const num = parseFloat(amount);
        const formatted = Math.abs(num).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
        return num < 0 ? `-₱ ${formatted}` : `₱ ${formatted}`;
    };

    /**
     * Format a number as a short currency value.
     * @param {number} amount
     * @returns {string} e.g. "₱ 1.2K"
     */
    const formatShort = (amount) => {
        const num = parseFloat(amount);
        if (Math.abs(num) >= 1_000_000) return `₱ ${(num / 1_000_000).toFixed(1)}M`;
        if (Math.abs(num) >= 1_000) return `₱ ${(num / 1_000).toFixed(1)}K`;
        return formatPeso(num);
    };

    return { formatPeso, formatShort };
}

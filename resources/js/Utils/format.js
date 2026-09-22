/**
 * Format a number or numeric string as Indonesian Rupiah (IDR) currency string.
 *
 * @param {number|string} price
 * @returns {string}
 */
export const formatPrice = (price) => {
    const num = Number(price) || 0;
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(num);
};

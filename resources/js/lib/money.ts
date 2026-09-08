export function currencyPrecision(currency: string): number {
    return new Intl.NumberFormat('en', { style: 'currency', currency }).resolvedOptions().maximumFractionDigits ?? 2;
}

export function decimalToMinor(value: string, precision: number): number | null {
    const match = /^(-?)(\d+)(?:\.(\d+))?$/.exec(value.trim());
    if (!match || (match[3]?.length ?? 0) > precision) {
        return null;
    }
    const minor = Number(`${match[1]}${match[2]}${(match[3] ?? '').padEnd(precision, '0')}`);
    return Number.isSafeInteger(minor) ? minor : null;
}

export function minorToDecimal(value: number, precision: number): string {
    const digits = String(Math.abs(value)).padStart(precision + 1, '0');
    const sign = value < 0 ? '-' : '';
    return precision === 0 ? `${sign}${digits}` : `${sign}${digits.slice(0, -precision)}.${digits.slice(-precision)}`;
}

import assert from 'node:assert/strict';
import { test } from 'node:test';
import { currencyPrecision, decimalToMinor, minorToDecimal } from '../../resources/js/lib/money.ts';

test('currency precision follows the currency, including zero and three decimal places', () => {
    assert.equal(currencyPrecision('RON'), 2);
    assert.equal(currencyPrecision('JPY'), 0);
    assert.equal(currencyPrecision('KWD'), 3);
});

test('opening balances parse exactly without floating point multiplication', () => {
    assert.equal(decimalToMinor('0.29', 2), 29);
    assert.equal(decimalToMinor('-123.45', 2), -12345);
    assert.equal(decimalToMinor('125', 0), 125);
    assert.equal(decimalToMinor('1.234', 3), 1234);
    assert.equal(decimalToMinor('90071992547409.91', 2), Number.MAX_SAFE_INTEGER);
    assert.equal(decimalToMinor('-90071992547409.91', 2), -Number.MAX_SAFE_INTEGER);
});

test('invalid and unsafe opening balances cannot be submitted as minor units', () => {
    for (const value of ['', '1.001', '1,000', '1e3', 'NaN', '90071992547409.92', '-90071992547409.92']) {
        assert.equal(decimalToMinor(value, 2), null, value);
    }
    assert.equal(decimalToMinor('1.1', 0), null);
});

test('edit fields preserve all minor units, including the supported limits', () => {
    assert.equal(minorToDecimal(29, 2), '0.29');
    assert.equal(minorToDecimal(-5, 2), '-0.05');
    assert.equal(minorToDecimal(0, 3), '0.000');
    assert.equal(minorToDecimal(125, 0), '125');
    assert.equal(minorToDecimal(Number.MAX_SAFE_INTEGER, 2), '90071992547409.91');
    assert.equal(minorToDecimal(-Number.MAX_SAFE_INTEGER, 2), '-90071992547409.91');
});

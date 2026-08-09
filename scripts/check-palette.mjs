import { readFileSync } from 'node:fs';

const css = readFileSync(new URL('../resources/css/app.css', import.meta.url), 'utf8');
const expected = ['#FD971F', '#A6E22E', '#F92672', '#66D9EF', '#272822', '#060606', '#F8F8F2', '#FFFFFF'];
const hexValues = [...css.matchAll(/#[0-9A-Fa-f]{6}/g)].map(([value]) => value.toUpperCase());

for (const color of expected) {
    if (! hexValues.includes(color)) {
        throw new Error(`Missing Apricode palette token ${color}.`);
    }
}

const unexpected = [...new Set(hexValues.filter((color) => ! expected.includes(color)))];
if (unexpected.length > 0) {
    throw new Error(`Unexpected hardcoded colors: ${unexpected.join(', ')}.`);
}

console.log('Apricode palette validation passed.');

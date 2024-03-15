/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

export const formatDate = (value, locales = 'en-US') => {
    const dateObject = new Date(value);

    if (isNaN(dateObject.getTime())) {
        return '-';
    }

    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    const formattedDate = new Date(value).toLocaleDateString(locales, options);
    return formattedDate.replace(/\//g, '.');
};

export const formatDateTime = (value, locales = 'en-US', withSeconds = false) => {
    const dateObject = new Date(value);

    if (isNaN(dateObject.getTime())) {
        return '-';
    }

    const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };

    if (withSeconds) {
        options.second = '2-digit';
    }

    const formattedDate = new Date(value).toLocaleDateString(locales, options);
    return formattedDate.replace(/\//g, '.');
};

export const numberFormat = (number, decimals, decPoint, thousandsSep) => {
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep,
        dec = (typeof decPoint === 'undefined') ? '.' : decPoint,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
};

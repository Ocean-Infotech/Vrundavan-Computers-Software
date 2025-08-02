function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

function isAlphanumeric(evt) {
    evt = evt || window.event;
    var charCode = evt.which || evt.keyCode;
    var charStr = String.fromCharCode(charCode);

    if (!charStr.match(/^[a-zA-Z0-9 ]$/)) {
        evt.preventDefault();
        return false;
    }
    return true;
}

function isDecimal(evt) {
    evt = evt || window.event;
    var charCode = evt.which || evt.keyCode;

    if ([8, 46, 37, 39].includes(charCode)) {
        return true;
    }

    if (charCode === 46) {
        if (evt.target.value.includes('.')) {
            return false;
        }
        return true;
    }

    if (charCode >= 48 && charCode <= 57) {
        return true;
    }
    return false;
}

// Utility function to format number as currency
function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2);
}

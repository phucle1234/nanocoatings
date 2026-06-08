(function () {
    const m = (name) => document.querySelector(`meta[name="${name}"]`);
    const val = (name) => (m(name) ? m(name).getAttribute('content') : null);

    const toastError = val('toast-error');
    const toastSuccess = val('toast-success');
    const toastInfo = val('toast-info');
    let toastErrors = [];
    try {
        toastErrors = JSON.parse(val('toast-errors-json') || '[]');
    } catch (e) {
        toastErrors = [];
    }

    if (typeof toastr === 'undefined') return;

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 10000,
        extendedTimeOut: 2000,
        tapToDismiss: false
    };

    if (toastError) toastr.error(toastError);
    if (toastSuccess) toastr.success(toastSuccess);
    if (toastInfo) toastr.info(toastInfo);
    if (Array.isArray(toastErrors)) {
        toastErrors.forEach((msg) => toastr.error(msg));
    }
})();
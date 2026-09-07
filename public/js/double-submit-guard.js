(function () {
    if (window.gasgoDoubleSubmitGuardLoaded) {
        return;
    }

    window.gasgoDoubleSubmitGuardLoaded = true;

    const LOCKED_CLASS = 'gasgo-submit-locked';

    function lockButton(button) {
        if (!button || button.dataset.doubleClickUnlocked === 'true') {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-disabled', 'true');
        button.classList.add(LOCKED_CLASS);

        if (!button.dataset.originalHtml) {
            button.dataset.originalHtml = button.innerHTML;
        }

        if (button.dataset.loadingText) {
            button.innerHTML = button.dataset.loadingText;
        }
    }

    window.gasgoLockForm = function (form) {
        if (!form || form.dataset.submitting === 'true') {
            return false;
        }

        form.dataset.submitting = 'true';

        const submitter = form.querySelector('[data-clicked-submit="true"]')
            || form.querySelector('button[type="submit"], input[type="submit"]');

        lockButton(submitter);

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(lockButton);

        return true;
    };

    document.addEventListener('click', function (event) {
        const submitter = event.target.closest('button[type="submit"], input[type="submit"]');
        if (!submitter || !submitter.form) {
            return;
        }

        submitter.form.querySelectorAll('[data-clicked-submit="true"]').forEach(button => {
            delete button.dataset.clickedSubmit;
        });
        submitter.dataset.clickedSubmit = 'true';

        if (submitter.form.dataset.submitting === 'true') {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    document.addEventListener('submit', function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || form.dataset.doubleSubmitUnlocked === 'true') {
            return;
        }

        if ((form.getAttribute('data-confirm') || form.dataset.confirm) && !form.dataset.confirmed) {
            return;
        }

        if (!window.gasgoLockForm(form)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);
})();

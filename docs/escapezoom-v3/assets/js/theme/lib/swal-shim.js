const DEFAULT_TOAST = {
  toast: false,
  position: 'center',
  showConfirmButton: true,
  timer: 0,
};

function buildButton(label, className, variant) {
  const button = document.createElement('button');
  button.type = 'button';
  button.textContent = label;
  button.className = className || 'btn btn-primary';
  if (variant === 'cancel') {
    button.classList.add('btn-ghost');
  }
  return button;
}

function fireDialog(options, defaults) {
  const settings = { ...DEFAULT_TOAST, ...defaults, ...options };

  if (settings.toast) {
    const toast = document.createElement('div');
    toast.className = 'ez-toast fixed z-[10000] rounded-xl bg-slate-900 px-4 py-3 text-sm text-white shadow-lg';
    toast.textContent = settings.title || settings.text || '';
    toast.style.bottom = '1.5rem';
    toast.style.left = '1.5rem';
    document.body.appendChild(toast);
    window.setTimeout(() => toast.remove(), settings.timer || 3000);
    return Promise.resolve({ isConfirmed: false, isDismissed: true });
  }

  return new Promise((resolve) => {
    const backdrop = document.createElement('div');
    backdrop.className = 'ez-dialog-backdrop fixed inset-0 z-[10000] flex items-center justify-center bg-black/45 p-4';

    const panel = document.createElement('div');
    panel.className = settings.customClass?.popup || 'rounded-2xl bg-white p-6 shadow-xl';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'true');

    if (settings.iconHtml) {
      const icon = document.createElement('div');
      icon.className = settings.customClass?.icon || 'mb-3 flex justify-center';
      icon.innerHTML = settings.iconHtml;
      panel.appendChild(icon);
    }

    if (settings.title) {
      const title = document.createElement('h2');
      title.className = settings.customClass?.title || 'text-lg font-semibold';
      title.textContent = settings.title;
      panel.appendChild(title);
    }

    if (settings.text) {
      const text = document.createElement('p');
      text.className = 'mt-2 text-sm text-slate-600';
      text.textContent = settings.text;
      panel.appendChild(text);
    }

    if (settings.html) {
      const html = document.createElement('div');
      html.className = 'mt-2';
      html.innerHTML = settings.html;
      panel.appendChild(html);
    }

    const actions = document.createElement('div');
    actions.className = settings.customClass?.actions || 'mt-5 flex gap-2';

    const close = (result) => {
      backdrop.remove();
      resolve(result);
    };

    if (settings.showCancelButton) {
      const cancel = buildButton(settings.cancelButtonText || 'انصراف', settings.customClass?.cancelButton, 'cancel');
      cancel.addEventListener('click', () => close({ isConfirmed: false, isDismissed: true }));
      actions.appendChild(cancel);
    }

    if (settings.showConfirmButton !== false) {
      const confirm = buildButton(settings.confirmButtonText || 'تایید', settings.customClass?.confirmButton);
      confirm.addEventListener('click', () => close({ isConfirmed: true, isDismissed: false }));
      actions.appendChild(confirm);
    }

    panel.appendChild(actions);
    backdrop.appendChild(panel);
    backdrop.addEventListener('click', (event) => {
      if (event.target === backdrop) {
        close({ isConfirmed: false, isDismissed: true });
      }
    });
    document.body.appendChild(backdrop);
  });
}

export const Swal = {
  fire(options) {
    return fireDialog(options, {});
  },
  mixin(defaults) {
    return {
      fire(options) {
        return fireDialog(options, defaults);
      },
    };
  },
};

window.Swal = Swal;

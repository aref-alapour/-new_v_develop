/**
 * مودال CRUD متمرکز برای صفحات ادمین EscapeZoom.
 * استفاده: x-data="window.EzCrudModal(config)" — config از PHP در window.ezCrudModalConfig قرار می‌گیرد.
 *
 * config: {
 *   dialogId, formId, ajaxUrl, saveAction, updateAction, saveNonce, updateNonce,
 *   refreshMode ('reload'|'htmx'), refreshEventName, addTitle, editTitle,
 *   fields: string[], checkboxFields: string[]
 * }
 */
(function () {
  'use strict';

  function buildInitialFormData(config) {
    var data = { id: '' };
    var fields = config.fields || [];
    var checkboxFields = (config.checkboxFields || []).reduce(function (acc, k) {
      acc[k] = true;
      return acc;
    }, {});
    fields.forEach(function (key) {
      data[key] = checkboxFields[key] ? true : '';
    });
    return data;
  }

  function formDataToBody(formData, config) {
    var body = {};
    var key;
    for (key in formData) {
      if (!formData.hasOwnProperty(key)) continue;
      var v = formData[key];
      if (v === true || v === '1') body[key] = 1;
      else if (v === false || v === '0') body[key] = 0;
      else body[key] = v == null ? '' : String(v);
    }
    return body;
  }

  window.EzCrudModal = function (config) {
    if (!config) config = {};
    var initial = buildInitialFormData(config);

    return {
      dialogOpen: false,
      formData: Object.assign({}, initial),
      submitting: false,
      errors: {},

      init: function () {
        this.config = config;
        this.initialFormData = buildInitialFormData(config);
      },

      resetForm: function () {
        this.formData = Object.assign({}, this.initialFormData || buildInitialFormData(config));
        this.errors = {};
      },

      open: function () {
        this.resetForm();
        this.dialogOpen = true;
      },

      openEdit: function (rowData) {
        var data = Object.assign({}, buildInitialFormData(config));
        var checkboxFields = (config.checkboxFields || []).reduce(function (acc, key) {
          acc[key] = true;
          return acc;
        }, {});
        var k;
        if (rowData && typeof rowData === 'object') {
          for (k in rowData) {
            if (rowData.hasOwnProperty(k)) {
              var val = rowData[k];
              if (checkboxFields[k]) {
                if (val === '1' || val === 1) data[k] = true;
                else if (val === '0' || val === 0) data[k] = false;
                else data[k] = !!val;
              } else {
                data[k] = val != null ? String(val) : '';
              }
            }
          }
        }
        this.formData = data;
        this.errors = {};
        this.dialogOpen = true;
      },

      close: function () {
        this.dialogOpen = false;
      },

      submitForm: function () {
        var ctx = this;
        var c = config;
        var formData = this.formData;
        var isEdit = !!(formData.id && String(formData.id).trim() !== '');
        var action = isEdit && c.updateAction ? c.updateAction : c.saveAction;
        var nonce = isEdit && c.updateNonce ? c.updateNonce : c.saveNonce;
        var body = formDataToBody(formData, c);
        body.action = action;
        body.nonce = nonce;

        this.submitting = true;
        this.errors = {};

        var ajaxUrl = c.ajaxUrl || (typeof ajaxurl !== 'undefined' ? ajaxurl : '');
        if (!ajaxUrl) {
          this.errors = { general: 'آدرس درخواست تعریف نشده است.' };
          this.submitting = false;
          return;
        }

        fetch(ajaxUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams(body),
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (result) {
            if (result && result.success) {
              ctx.dialogOpen = false;
              ctx.resetForm();
              if (c.refreshMode === 'htmx' && c.refreshEventName) {
                if (typeof window.ezShowTableSkeletonAndRefresh === 'function') {
                  window.ezShowTableSkeletonAndRefresh(c.refreshEventName);
                } else if (typeof window.htmx !== 'undefined') {
                  window.htmx.trigger(document.body, c.refreshEventName);
                }
              } else {
                window.location.reload();
              }
            } else {
              var err = (result && result.data) ? result.data : {};
              if (typeof err === 'string') err = { general: err };
              if (!err.general && err.message) err.general = err.message;
              ctx.errors = err;
            }
          })
          .catch(function () {
            ctx.errors = { general: 'خطا در ارتباط با سرور' };
          })
          .finally(function () {
            ctx.submitting = false;
          });
      },
    };
  };
})();

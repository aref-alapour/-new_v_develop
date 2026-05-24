jQuery(document).ready(function(e){var t;function n(){t=wp.media({title:`انتخاب تصویر برچسب`,button:{text:`انتخاب`},multiple:!1}).on(`select`,function(){var n=t.state().get(`selection`).first().toJSON();e(`#tag-image-id`).val(n.id),e(`#tag-image-wrapper`).html(`<img src="`+n.sizes.thumbnail.url+`" style="max-width:100px;">`)}).open()}e(`#tag_media_button`).on(`click`,function(e){e.preventDefault(),n()}),e(`#tag_media_remove`).on(`click`,function(){e(`#tag-image-id`).val(``),e(`#tag-image-wrapper`).html(``)})}),(function(e){let t=0,n=`header`,r=null;e(document).ready(function(){n=e(`#current-location`).val()||`header`,l()});function i(t){let n=e.extend({},{type:`info`,title:``,message:``,icon:``,buttons:[],onClose:null},t),i=n.icon||{success:`✓`,error:`✕`,warning:`⚠`,info:`ℹ`,question:`؟`}[n.type]||`ℹ`;e(`#custom-modal .modal-icon`).removeClass(`success error warning info question`).addClass(n.type).text(i),e(`#custom-modal .modal-title`).text(n.title),e(`#custom-modal .modal-message`).html(n.message);let a=e(`#custom-modal .modal-buttons`);a.empty(),n.buttons.forEach(function(t){let n=e(`<button>`).addClass(`modal-btn modal-btn-`+(t.type||`secondary`)).text(t.text).on(`click`,function(){c(),t.onClick&&t.onClick()});a.append(n)}),e(`#custom-modal`).fadeIn(300),setTimeout(function(){a.find(`.modal-btn`).first().focus()},350),n.onClose&&(r=n.onClose)}function a(e,t,n,r){i({type:`question`,title:e,message:t,buttons:[{text:`لغو`,type:`secondary`,onClick:r},{text:`تایید`,type:`danger`,onClick:n}]})}function o(e,t,n){i({type:n||`info`,title:e,message:t,buttons:[{text:`متوجه شدم`,type:`primary`}]})}function s(t,n,r,a,o){e(`#custom-modal .modal-input-container`).show(),e(`#custom-modal .modal-input`).val(``).attr(`placeholder`,r||``).removeClass(`error`),i({type:`question`,title:t,message:n,buttons:[{text:`لغو`,type:`secondary`,onClick:function(){e(`#custom-modal .modal-input-container`).hide()}},{text:`تایید`,type:`primary`,onClick:function(){let i=e(`#custom-modal .modal-input`).val().trim();if(o&&!o(i)){e(`#custom-modal .modal-input`).addClass(`error`),setTimeout(function(){s(t,n,r,a,o)},100);return}e(`#custom-modal .modal-input-container`).hide(),a&&a(i)}}]}),setTimeout(function(){e(`#custom-modal .modal-input`).focus()},350),e(`#custom-modal .modal-input`).off(`keypress`).on(`keypress`,function(t){t.which===13&&e(`#custom-modal .modal-btn-primary`).click()})}function c(){e(`#custom-modal`).fadeOut(300),r&&=(r(),null)}e(document).on(`click`,`.modal-overlay`,function(){c()}),e(document).on(`keydown`,function(t){t.key===`Escape`&&e(`#custom-modal`).is(`:visible`)&&c()});function l(){u(),d(),h(),g()}function u(){e(`#mega-menu-structure`).sortable({handle:`.drag-handle`,placeholder:`ui-sortable-placeholder`,tolerance:`pointer`,connectWith:`.sortable-children`,receive:function(e,t){let n=t.item;n.hasClass(`child-item`)&&m(n)},update:function(e,t){C()}})}function d(){e(`.sortable-children`).sortable({handle:`.child-drag-handle`,placeholder:`ui-sortable-placeholder`,tolerance:`pointer`,connectWith:`.sortable-children, #mega-menu-structure`,receive:function(e,t){let n=t.item;n.hasClass(`menu-item`)&&f(n)},update:function(e,t){console.log(`Child item moved or reordered`)}}),e(`#mega-menu-structure`).sortable(`option`,`connectWith`,`.sortable-children`)}function f(t){let n=t.find(`.child-item`).length>0,r=t.find(`.menu-title-preview`).text(),i=t.find(`.child-item`).length;if(n){a(`⚠️ هشدار: حذف فرزندان`,`منوی "<strong>${r}</strong>" دارای <strong>${i} آیتم فرزند</strong> است.<br><br>با تبدیل آن به فرزند، تمام فرزندانش حذف می‌شوند.<br><br>آیا ادامه می‌دهید؟`,function(){p(t)},function(){t.remove(),e(`#mega-menu-structure`).sortable(`cancel`),u(),d()});return}p(t)}function p(e){let t={id:e.data(`id`),title:e.find(`.menu-item-title`).val()||e.find(`.menu-title-preview`).text(),url:e.find(`.menu-item-url`).val()||`#`,item_visibility:e.find(`.item-visibility-radio:checked`).val()||`both`},n=x(t);e.replaceWith(n),d(),console.log(`Converted parent to child:`,t.title)}function m(e){let n={id:e.data(`id`),title:e.find(`.child-item-title`).val(),url:e.find(`.child-item-url`).val(),icon_type:`image`,icon_value:``,icon_visibility:`both`,item_visibility:e.find(`.child-item-visibility-radio:checked`).val()||`both`,children:[]},r=b(n,t);e.replaceWith(r),t++,u(),d(),console.log(`Converted child to parent:`,n.title)}function h(){e(document).on(`click`,`.section-title`,function(){let t=e(this);e(`#`+t.data(`toggle`)).slideToggle(300),t.toggleClass(`active`)}),e(document).on(`click`,`.add-custom-link`,function(){_()}),e(document).on(`keypress`,`#custom-link-title, #custom-link-url`,function(e){e.which===13&&(e.preventDefault(),_())}),e(document).on(`keydown`,`#custom-link-title`,function(t){t.which===9&&!t.shiftKey&&(t.preventDefault(),e(`#custom-link-url`).focus())}),e(document).on(`keydown`,`#custom-link-url`,function(t){t.which===9&&t.shiftKey&&(t.preventDefault(),e(`#custom-link-title`).focus())}),e(document).on(`click`,`.add-selected-items`,function(){let t=e(this).closest(`.section-content`),n=t.find(`input[type="checkbox"]:checked`);if(n.length===0){o(`آیتمی انتخاب نشده`,`لطفاً حداقل یک آیتم را انتخاب کنید.`,`warning`);return}n.each(function(){let t=e(this);v({id:`menu_`+Date.now()+`_`+Math.random().toString(36).substr(2,9),title:t.data(`title`),url:t.data(`url`),icon_type:`image`,icon_value:``,icon_visibility:`both`,item_visibility:`both`,children:[]})}),n.prop(`checked`,!1),t.slideUp(),t.prev(`.section-title`).removeClass(`active`)}),e(document).on(`change`,`.icon-type-radio`,function(){let t=e(this).closest(`.menu-item`);e(this).val()===`image`?(t.find(`.icon-image-field`).show(),t.find(`.icon-svg-field`).hide()):(t.find(`.icon-image-field`).hide(),t.find(`.icon-svg-field`).show())}),e(document).on(`click`,`.edit-item`,function(){e(this).closest(`.menu-item`).find(`.menu-item-content`).slideToggle(300)}),e(document).on(`click`,`.toggle-children`,function(){e(this).closest(`.menu-item`).find(`.menu-children-section`).slideToggle(300),e(this).find(`.dashicons`).toggleClass(`dashicons-arrow-down-alt2 dashicons-arrow-up-alt2`)}),e(document).on(`click`,`.close-item`,function(){e(this).closest(`.menu-item`).find(`.menu-item-content`).slideUp(300)}),e(document).on(`click`,`.delete-item`,function(){let t=e(this).closest(`.menu-item`);a(`حذف آیتم منو`,`آیا از حذف "<strong>${t.find(`.menu-title-preview`).text()}</strong>" اطمینان دارید؟`,function(){t.fadeOut(300,function(){e(this).remove(),w()})})}),e(document).on(`input`,`.menu-item-title`,function(){let t=e(this).closest(`.menu-item`),n=e(this).val();t.find(`.menu-title-preview`).text(n)}),e(document).on(`click`,`.select-icon-image`,function(t){t.preventDefault();let n=e(this),r=n.closest(`.menu-item`),i=r.find(`.menu-item-icon-value`),a=wp.media({title:`انتخاب آیکون`,button:{text:`انتخاب`},multiple:!1,library:{type:`image`}});a.on(`select`,function(){let e=a.state().get(`selection`).first().toJSON().url;i.val(e);let t=r.find(`.menu-icon-preview`);t.length===0||t.hasClass(`svg-icon`)||r.find(`.menu-icon-placeholder`).length?r.find(`.menu-icon-preview, .menu-icon-placeholder`).replaceWith(`<img src="`+e+`" class="menu-icon-preview" alt="">`):t.attr(`src`,e);let o=r.find(`.icon-image-field .icon-preview`);o.length===0?(n.after(`<button type="button" class="button remove-icon">حذف</button>`),n.parent().after(`<div class="icon-preview"><img src="`+e+`" alt="آیکون"></div>`)):o.find(`img`).attr(`src`,e)}),a.open()}),e(document).on(`input`,`.menu-item-icon-svg`,function(){let t=e(this).closest(`.menu-item`),n=e(this).val().trim();if(n){t.find(`.menu-icon-preview, .menu-icon-placeholder`).replaceWith(`<span class="menu-icon-preview svg-icon">`+n+`</span>`);let r=t.find(`.icon-svg-field .svg-preview`);r.length===0?e(this).after(`<div class="icon-preview svg-preview">`+n+`</div>`):r.html(n)}}),e(document).on(`click`,`.remove-icon`,function(){let t=e(this).closest(`.menu-item`),n=e(this).closest(`.icon-image-field, .icon-svg-field`);n.find(`.menu-item-icon-value, .menu-item-icon-svg`).val(``),t.find(`.menu-icon-preview`).replaceWith(`<span class="dashicons dashicons-admin-links menu-icon-placeholder"></span>`),n.find(`.icon-preview`).remove(),e(this).remove()}),e(document).on(`click`,`.add-child-item`,function(){y(e(this).closest(`.menu-item`))}),e(document).on(`keypress`,`.child-item-title, .child-item-url`,function(t){if(t.which===13){t.preventDefault();let n=e(this).closest(`.child-item`),r=n.closest(`.menu-item`),i=n.find(`.child-item-title`).val().trim(),a=n.find(`.child-item-url`).val().trim();i&&a?y(r,!0):!a&&i&&n.find(`.child-item-url`).focus()}}),e(document).on(`keydown`,`.child-item-title`,function(t){t.which===9&&!t.shiftKey&&(t.preventDefault(),e(this).closest(`.child-item`).find(`.child-item-url`).focus())}),e(document).on(`keydown`,`.child-item-url`,function(t){if(t.which===9){let n=e(this).closest(`.child-item`);if(t.shiftKey)t.preventDefault(),n.find(`.child-item-title`).focus();else{let e=n.next(`.child-item`);e.length&&(t.preventDefault(),e.find(`.child-item-title`).focus())}}}),e(document).on(`click`,`.edit-child`,function(){e(this).closest(`.child-item`).find(`.child-item-settings`).slideToggle(200)}),e(document).on(`click`,`.delete-child`,function(){let t=e(this).closest(`.child-item`);a(`حذف فرزند`,`آیا از حذف "<strong>${t.find(`.child-item-title`).val()||`این فرزند`}</strong>" اطمینان دارید؟`,function(){t.fadeOut(200,function(){e(this).remove()})})}),e(document).on(`click`,`.save-mega-menu`,function(){let t=e(this),r=e(`.save-status`),i=S();t.addClass(`loading`),r.text(`در حال ذخیره...`).removeClass(`success error`),e.ajax({url:megaMenuAdmin.ajaxUrl,type:`POST`,data:{action:`save_mega_menu`,nonce:megaMenuAdmin.nonce,location:n,menu_items:JSON.stringify(i)},success:function(e){t.removeClass(`loading`),e.success?(r.text(e.data.message).addClass(`success`),setTimeout(function(){r.text(``)},3e3)):r.text(e.data.message).addClass(`error`)},error:function(){t.removeClass(`loading`),r.text(`خطا در ذخیره سازی!`).addClass(`error`)}})}),e(document).on(`click`,`#add-new-location`,function(t){t.preventDefault(),s(`افزودن لوکیشن جدید`,`کلید لوکیشن را وارد کنید (فقط حروف کوچک انگلیسی، اعداد و خط تیره):<br><small style="color: #646970;">مثال: sidebar, mobile-menu, top-bar</small>`,`مثلاً: sidebar`,function(t){s(`نام نمایشی`,`نام نمایشی لوکیشن را وارد کنید:`,`مثلاً: منوی سایدبار`,function(n){e.ajax({url:megaMenuAdmin.ajaxUrl,type:`POST`,data:{action:`add_menu_location`,nonce:megaMenuAdmin.nonce,location_key:t,location_name:n},success:function(e){e.success?(o(`موفق!`,`لوکیشن "<strong>${n}</strong>" با موفقیت ایجاد شد.`,`success`),setTimeout(function(){window.location.href=`?page=mega-menu-settings&location=`+e.data.location_key},1500)):o(`خطا`,e.data.message,`error`)},error:function(){o(`خطا`,`خطا در افزودن لوکیشن!`,`error`)}})},function(e){return e.length>0})},function(e){return e?/^[a-z0-9-]+$/.test(e)?!0:(o(`کلید نامعتبر`,`کلید لوکیشن فقط باید شامل حروف کوچک انگلیسی، اعداد و خط تیره باشد.`,`error`),!1):!1})}),e(document).on(`click`,`.delete-location`,function(n){n.preventDefault(),n.stopPropagation();let r=e(this),i=r.data(`location`),o=r.data(`name`);a(`حذف لوکیشن`,`آیا از حذف لوکیشن "<strong>${o}</strong>" اطمینان دارید؟<br><br><span style="color: #d97706;">⚠️ تمام منوهای این لوکیشن حذف خواهند شد و این عملیات قابل بازگشت نیست!</span>`,function(){t(r,i,o)})});function t(t,n,r){t.addClass(`deleting`),e.ajax({url:megaMenuAdmin.ajaxUrl,type:`POST`,data:{action:`delete_menu_location`,nonce:megaMenuAdmin.nonce,location_key:n},success:function(e){e.success?(o(`حذف شد!`,`لوکیشن "<strong>${r}</strong>" با موفقیت حذف شد.`,`success`),setTimeout(function(){window.location.href=`?page=mega-menu-settings&location=header`},1500)):(o(`خطا`,e.data.message,`error`),t.removeClass(`deleting`))},error:function(){o(`خطا`,`خطا در حذف لوکیشن!`,`error`),t.removeClass(`deleting`)}})}e(document).on(`click`,`.show-drag-help`,function(t){t.preventDefault(),e(`#drag-drop-help-modal`).fadeIn(300)}),e(document).on(`click`,`.close-help, .help-overlay`,function(){e(`#drag-drop-help-modal`).fadeOut(300)}),e(document).on(`click`,`.help-content`,function(e){e.stopPropagation()}),e(document).on(`keydown`,function(t){t.key===`Escape`&&e(`#drag-drop-help-modal`).is(`:visible`)&&e(`#drag-drop-help-modal`).fadeOut(300)})}function g(){e(document).on(`input`,`.search-box`,function(){let t=e(this).val().toLowerCase();e(this).siblings(`.items-list`).find(`.item-checkbox`).each(function(){e(this).text().toLowerCase().indexOf(t)>-1?e(this).show():e(this).hide()})})}function _(){let t=e(`#custom-link-title`).val().trim(),n=e(`#custom-link-url`).val().trim();if(!t||!n){o(`فیلدهای خالی`,`لطفاً عنوان و آدرس را وارد کنید.`,`warning`),t?n||setTimeout(function(){e(`#custom-link-url`).focus()},400):setTimeout(function(){e(`#custom-link-title`).focus()},400);return}v({id:`menu_`+Date.now(),title:t,url:n,icon_type:`image`,icon_value:``,icon_visibility:`both`,item_visibility:`both`,children:[]},!1),e(`#custom-link-title, #custom-link-url`).val(``),setTimeout(function(){e(`#custom-link-title`).focus()},50)}function v(n,r){let i=e(`.empty-menu-message`);i.length&&i.remove();let a=b(n,t);e(`#mega-menu-structure`).append(a);let o=e(`#mega-menu-structure .menu-item`).last();d(),t++,o.find(`.menu-item-content`).slideDown(300,function(){r&&o.find(`.menu-item-title`).focus().select()})}function y(e,t){let n=e.find(`.children-list`),r=x({id:`child_`+Date.now(),title:``,url:``,item_visibility:`both`});if(n.append(r),d(),t){let e=n.find(`.child-item`).last();setTimeout(function(){e.find(`.child-item-title`).focus()},100)}}function b(e,t){let n;n=e.icon_value?e.icon_type===`svg`?`<span class="menu-icon-preview svg-icon">${e.icon_value}</span>`:`<img src="${e.icon_value}" class="menu-icon-preview" alt="">`:`<span class="dashicons dashicons-admin-links menu-icon-placeholder"></span>`;let r=e.icon_type===`image`?`checked`:``,i=e.icon_type===`svg`?`checked`:``,a=e.icon_type===`svg`?`display:none;`:``,o=e.icon_type===`image`?`display:none;`:``,s=e.icon_type===`image`?e.icon_value:``,c=e.icon_type===`svg`?T(e.icon_value):``,l=e.icon_value&&e.icon_type===`image`?`<div class="icon-preview"><img src="${e.icon_value}" alt="آیکون"></div>`:``,u=e.icon_value&&e.icon_type===`svg`?`<div class="icon-preview svg-preview">${e.icon_value}</div>`:``,d=e.icon_value&&e.icon_type===`image`?`<button type="button" class="button remove-icon">حذف</button>`:``,f=e.icon_visibility===`both`?`checked`:``,p=e.icon_visibility===`desktop`?`checked`:``,m=e.icon_visibility===`mobile`?`checked`:``,h=e.icon_visibility===`none`?`checked`:``,g=e.item_visibility===`both`?`checked`:``,_=e.item_visibility===`desktop`?`checked`:``,v=e.item_visibility===`mobile`?`checked`:``,y=e.item_visibility===`none`?`checked`:``,b=``;return e.children&&e.children.length>0&&e.children.forEach(function(e){b+=x(e)}),`
            <div class="menu-item" data-id="${e.id}" data-index="${t}">
                <div class="menu-item-header">
                    <span class="drag-handle">
                        <span class="dashicons dashicons-menu"></span>
                    </span>
                    <div class="menu-item-preview">
                        ${n}
                        <span class="menu-title-preview">${T(e.title)}</span>
                    </div>
                    <div class="menu-item-actions">
                        <button type="button" class="button-icon toggle-children" title="نمایش/مخفی کردن فرزندان">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                        <button type="button" class="button-icon edit-item" title="ویرایش">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button type="button" class="button-icon delete-item" title="حذف">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
                <div class="menu-item-content" style="display: none;">
                    <div class="menu-item-fields">
                        <div class="field-row">
                            <label>عنوان منو:</label>
                            <input type="text" class="menu-item-title" value="${T(e.title)}" placeholder="عنوان منو">
                        </div>
                        <div class="field-row">
                            <label>آدرس URL:</label>
                            <input type="text" class="menu-item-url" value="${T(e.url)}" placeholder="https://example.com">
                        </div>
                        
                        <div class="field-row">
                            <label>نوع آیکون:</label>
                            <div class="icon-type-selector">
                                <label style="margin-left: 20px;">
                                    <input type="radio" name="icon_type_${e.id}" value="image" class="icon-type-radio" ${r}>
                                    تصویر (PNG/JPG/SVG file)
                                </label>
                                <label>
                                    <input type="radio" name="icon_type_${e.id}" value="svg" class="icon-type-radio" ${i}>
                                    کد SVG
                                </label>
                            </div>
                        </div>

                        <div class="field-row icon-image-field" style="${a}">
                            <label>تصویر آیکون:</label>
                            <div class="icon-field">
                                <input type="text" class="menu-item-icon-value" value="${s}" placeholder="آدرس تصویر" readonly>
                                <button type="button" class="button select-icon-image">انتخاب تصویر</button>
                                ${d}
                            </div>
                            ${l}
                        </div>

                        <div class="field-row icon-svg-field" style="${o}">
                            <label>کد SVG:</label>
                            <textarea class="menu-item-icon-svg" placeholder="<svg>...</svg>" rows="5" style="width: 100%; font-family: monospace; direction: ltr; text-align: left;">${c}</textarea>
                            ${u}
                        </div>

                        <div class="field-row">
                            <label>نمایش آیکون در:</label>
                            <div class="visibility-options">
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="icon_visibility_${e.id}" value="both" class="icon-visibility-radio" ${f}>
                                    موبایل و دسکتاپ (هردو)
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="icon_visibility_${e.id}" value="desktop" class="icon-visibility-radio" ${p}>
                                    فقط دسکتاپ
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="icon_visibility_${e.id}" value="mobile" class="icon-visibility-radio" ${m}>
                                    فقط موبایل
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="icon_visibility_${e.id}" value="none" class="icon-visibility-radio" ${h}>
                                    نمایش داده نشود
                                </label>
                            </div>
                        </div>

                        <div class="field-row">
                            <label>نمایش این آیتم در:</label>
                            <div class="visibility-options">
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="item_visibility_${e.id}" value="both" class="item-visibility-radio" ${g}>
                                    موبایل و دسکتاپ (هردو)
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="item_visibility_${e.id}" value="desktop" class="item-visibility-radio" ${_}>
                                    فقط دسکتاپ
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="item_visibility_${e.id}" value="mobile" class="item-visibility-radio" ${v}>
                                    فقط موبایل
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="item_visibility_${e.id}" value="none" class="item-visibility-radio" ${y}>
                                    نمایش داده نشود (مخفی)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="menu-children-section">
                        <div class="children-header">
                            <h4>آیتم‌های فرزند</h4>
                            <button type="button" class="button add-child-item">
                                <span class="dashicons dashicons-plus-alt"></span>
                                افزودن فرزند
                            </button>
                        </div>
                        <div class="children-list sortable-children">
                            ${b}
                        </div>
                    </div>
                    <div class="item-actions">
                        <button type="button" class="button close-item">بستن</button>
                    </div>
                </div>
            </div>
        `}function x(e){let t=e.item_visibility===`both`?`checked`:``,n=e.item_visibility===`desktop`?`checked`:``,r=e.item_visibility===`mobile`?`checked`:``,i=e.item_visibility===`none`?`checked`:``;return`
            <div class="child-item" data-id="${e.id}">
                <span class="child-drag-handle">
                    <span class="dashicons dashicons-menu"></span>
                </span>
                <div class="child-item-fields">
                    <input type="text" class="child-item-title" value="${T(e.title)}" placeholder="عنوان فرزند">
                    <input type="text" class="child-item-url" value="${T(e.url)}" placeholder="آدرس URL">
                </div>
                <button type="button" class="button-icon edit-child" title="تنظیمات">
                    <span class="dashicons dashicons-admin-generic"></span>
                </button>
                <button type="button" class="button-icon delete-child" title="حذف">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                
                <div class="child-item-settings" style="display: none; margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">نمایش در:</label>
                    <div class="visibility-options">
                        <label style="display: block; margin: 3px 0;">
                            <input type="radio" name="child_visibility_${e.id}" value="both" class="child-item-visibility-radio" ${t}>
                            موبایل و دسکتاپ (هردو)
                        </label>
                        <label style="display: block; margin: 3px 0;">
                            <input type="radio" name="child_visibility_${e.id}" value="desktop" class="child-item-visibility-radio" ${n}>
                            فقط دسکتاپ
                        </label>
                        <label style="display: block; margin: 3px 0;">
                            <input type="radio" name="child_visibility_${e.id}" value="mobile" class="child-item-visibility-radio" ${r}>
                            فقط موبایل
                        </label>
                        <label style="display: block; margin: 3px 0;">
                            <input type="radio" name="child_visibility_${e.id}" value="none" class="child-item-visibility-radio" ${i}>
                            نمایش داده نشود (مخفی)
                        </label>
                    </div>
                </div>
            </div>
        `}function S(){let t=[];return e(`#mega-menu-structure .menu-item`).each(function(n){let r=e(this),i=r.find(`.icon-type-radio:checked`).val()||`image`,a=``;a=i===`image`?r.find(`.menu-item-icon-value`).val()||``:r.find(`.menu-item-icon-svg`).val()||``;let o={id:r.data(`id`),title:r.find(`.menu-item-title`).val(),url:r.find(`.menu-item-url`).val(),icon_type:i,icon_value:a,icon_visibility:r.find(`.icon-visibility-radio:checked`).val()||`both`,item_visibility:r.find(`.item-visibility-radio:checked`).val()||`both`,children:[]};r.find(`.child-item`).each(function(){let t=e(this),n={id:t.data(`id`),title:t.find(`.child-item-title`).val(),url:t.find(`.child-item-url`).val(),item_visibility:t.find(`.child-item-visibility-radio:checked`).val()||`both`};o.children.push(n)}),t.push(o)}),t}function C(){e(`#mega-menu-structure .menu-item`).each(function(t){e(this).attr(`data-index`,t)})}function w(){e(`#mega-menu-structure .menu-item`).length===0&&e(`#mega-menu-structure`).html(`
                <div class="empty-menu-message">
                    <span class="dashicons dashicons-menu-alt3"></span>
                    <p>هنوز آیتمی به منو اضافه نشده است.</p>
                    <p>از سایدبار سمت راست شروع کنید.</p>
                </div>
            `)}function T(e){if(!e)return``;let t={"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#039;`};return e.replace(/[&<>"']/g,function(e){return t[e]})}})(jQuery),(function(e){e(function(){e(`.ez-copy-shortlink`).on(`click`,function(n){n.preventDefault();var i=e(`#`+e(this).data(`target`)).val();if(!i){alert(`لینک کوتاه موجود نیست!`);return}var a=e(this),o=a.text();navigator.clipboard&&window.isSecureContext?navigator.clipboard.writeText(i).then(function(){r(a,o)}).catch(function(e){console.error(`Clipboard API failed:`,e),t(i,a,o)}):t(i,a,o)});function t(e,t,i){var a=document.createElement(`textarea`);a.value=e,a.style.position=`fixed`,a.style.left=`-999999px`,a.style.top=`-999999px`,document.body.appendChild(a),a.focus(),a.select();try{document.execCommand(`copy`)?r(t,i):n(e,t,i)}catch(r){console.error(`execCommand failed:`,r),n(e,t,i)}document.body.removeChild(a)}function n(e,t,n){var i=document.createElement(`input`);i.value=e,i.style.position=`fixed`,i.style.left=`-999999px`,i.style.top=`-999999px`,document.body.appendChild(i),i.focus(),i.select(),alert(`لینک انتخاب شد. لطفاً Ctrl+C (یا Cmd+C در مک) را فشار دهید تا کپی شود.`),document.body.removeChild(i),r(t,n)}function r(e,t){e.text(`کپی شد!`).addClass(`button-primary`),setTimeout(function(){e.text(t).removeClass(`button-primary`)},2e3)}})})(jQuery),(function(e){function t(t){t.find(`.ez-brand-team-pick-image`).off(`click`).on(`click`,function(t){t.preventDefault();var n=e(this).closest(`tr`),r=window.wp.media({title:`انتخاب تصویر عضو`,button:{text:`استفاده از تصویر`},multiple:!1});r.on(`select`,function(){var e=r.state().get(`selection`).first();if(e){var t=e.toJSON(),i=t.sizes&&t.sizes.thumbnail&&t.sizes.thumbnail.url?t.sizes.thumbnail.url:t.url||``;n.find(`.ez-brand-team-image-id`).val(t.id||0),n.find(`.ez-brand-team-thumb`).attr(`src`,i).removeClass(`is-hidden`).show()}}),r.open()})}function n(){var n=document.getElementById(`ez-brand-team-row-template`);if(!n||!n.content||!n.content.firstElementChild)return e();var r=e(document.importNode(n.content,!0).firstElementChild);return t(r),r}e(function(){var r=e(`#ez-brand-team-rows`);r.length&&(r.find(`tr`).each(function(){t(e(this))}),e.fn.sortable&&r.find(`tr`).length&&r.sortable({handle:`.ez-brand-team-sort-handle`,axis:`y`,items:`> tr`,opacity:.92,cursor:`grabbing`,tolerance:`pointer`,placeholder:`ez-brand-team-sort-placeholder`}),e(`#ez-brand-team-add`).on(`click`,function(t){t.preventDefault();var i=n();i.length&&(r.append(i),e.fn.sortable&&r.hasClass(`ui-sortable`)&&r.sortable(`refresh`))}),r.on(`click`,`.ez-brand-team-remove`,function(t){if(t.preventDefault(),r.find(`tr`).length<=1){e(this).closest(`tr`).find(`input[type="text"]`).val(``),e(this).closest(`tr`).find(`.ez-brand-team-image-id`).val(`0`),e(this).closest(`tr`).find(`.ez-brand-team-thumb`).attr(`src`,``).addClass(`is-hidden`).hide();return}e(this).closest(`tr`).remove(),e.fn.sortable&&r.hasClass(`ui-sortable`)&&r.sortable(`refresh`)}))})})(jQuery),(function(e){function t(){e(`#ez-brand-members-modal`).hide()}function n(e){var t=document.createElement(`div`);return t.textContent=e,t.innerHTML}function r(t){if(!t||!t.length)return`<p class="description ez-brand-members-modal__empty">`+n(`برای این برند اعضایی مشخص نشده است.`)+`</p>`;var r=`<ul class="ez-brand-members-modal__list">`;return e.each(t,function(e,t){var i=t.name||``,a=t.position||``,o=t.thumb||``;r+=`<li class="ez-brand-members-modal__item">`,o?r+=`<img class="ez-brand-members-modal__avatar" src="`+n(o)+`" alt="" width="48" height="48">`:r+=`<span class="dashicons dashicons-admin-users ez-brand-members-modal__placeholder-icon" aria-hidden="true"></span>`,r+=`<div><strong>`+n(i)+`</strong>`,a&&(r+=`<br><span class="ez-brand-members-modal__position">`+n(a)+`</span>`),r+=`</div></li>`}),r+=`</ul>`,r}e(function(){var n=e(`#ez-brand-members-modal`);n.length&&(e(document).on(`click`,`.ez-brand-view-members`,function(t){t.preventDefault();var i=e(this).attr(`data-members`)||`[]`,a;try{a=JSON.parse(i)}catch{a=[]}var o=r(Array.isArray(a)?a:[]);n.find(`.ez-brand-members-modal__body`).html(o),n.show()}),n.on(`click`,`.ez-brand-members-modal__backdrop, .ez-brand-members-modal__close`,function(e){e.preventDefault(),t()}),e(document).on(`keydown`,function(e){e.key===`Escape`&&n.is(`:visible`)&&t()}))})})(jQuery);
//# sourceMappingURL=admin.js.map
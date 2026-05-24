/**
 * Mega Menu Admin JavaScript - Enhanced Version
 */

(function($) {
    'use strict';

    let menuItemIndex = 0;
    let childItemIndex = 0;

    let currentLocation = 'header';
    let modalCallback = null;

    $(document).ready(function() {
        // Get current location
        currentLocation = $('#current-location').val() || 'header';
        
        initMegaMenu();
    });

    // Custom Modal System
    function showModal(options) {
        const defaults = {
            type: 'info', // success, error, warning, info, question
            title: '',
            message: '',
            icon: '',
            buttons: [],
            onClose: null
        };
        
        const settings = $.extend({}, defaults, options);
        
        // Set icon based on type
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ',
            question: '؟'
        };
        
        const icon = settings.icon || icons[settings.type] || 'ℹ';
        
        // Update modal content
        $('#custom-modal .modal-icon')
            .removeClass('success error warning info question')
            .addClass(settings.type)
            .text(icon);
        
        $('#custom-modal .modal-title').text(settings.title);
        $('#custom-modal .modal-message').html(settings.message);
        
        // Clear and add buttons
        const $buttonsContainer = $('#custom-modal .modal-buttons');
        $buttonsContainer.empty();
        
        settings.buttons.forEach(function(btn) {
            const $button = $('<button>')
                .addClass('modal-btn modal-btn-' + (btn.type || 'secondary'))
                .text(btn.text)
                .on('click', function() {
                    closeModal();
                    if (btn.onClick) {
                        btn.onClick();
                    }
                });
            $buttonsContainer.append($button);
        });
        
        // Show modal
        $('#custom-modal').fadeIn(300);
        
        // Focus first button
        setTimeout(function() {
            $buttonsContainer.find('.modal-btn').first().focus();
        }, 350);
        
        // Store callback
        if (settings.onClose) {
            modalCallback = settings.onClose;
        }
    }
    
    function showConfirm(title, message, onConfirm, onCancel) {
        showModal({
            type: 'question',
            title: title,
            message: message,
            buttons: [
                {
                    text: 'لغو',
                    type: 'secondary',
                    onClick: onCancel
                },
                {
                    text: 'تایید',
                    type: 'danger',
                    onClick: onConfirm
                }
            ]
        });
    }
    
    function showAlert(title, message, type) {
        showModal({
            type: type || 'info',
            title: title,
            message: message,
            buttons: [
                {
                    text: 'متوجه شدم',
                    type: 'primary'
                }
            ]
        });
    }
    
    function showPrompt(title, message, placeholder, onSubmit, validator) {
        // Show input
        $('#custom-modal .modal-input-container').show();
        $('#custom-modal .modal-input')
            .val('')
            .attr('placeholder', placeholder || '')
            .removeClass('error');
        
        showModal({
            type: 'question',
            title: title,
            message: message,
            buttons: [
                {
                    text: 'لغو',
                    type: 'secondary',
                    onClick: function() {
                        $('#custom-modal .modal-input-container').hide();
                    }
                },
                {
                    text: 'تایید',
                    type: 'primary',
                    onClick: function() {
                        const value = $('#custom-modal .modal-input').val().trim();
                        
                        // Validation
                        if (validator && !validator(value)) {
                            $('#custom-modal .modal-input').addClass('error');
                            // نمایش دوباره modal
                            setTimeout(function() {
                                showPrompt(title, message, placeholder, onSubmit, validator);
                            }, 100);
                            return;
                        }
                        
                        $('#custom-modal .modal-input-container').hide();
                        if (onSubmit) {
                            onSubmit(value);
                        }
                    }
                }
            ]
        });
        
        // Focus on input
        setTimeout(function() {
            $('#custom-modal .modal-input').focus();
        }, 350);
        
        // Submit with Enter
        $('#custom-modal .modal-input').off('keypress').on('keypress', function(e) {
            if (e.which === 13) {
                $('#custom-modal .modal-btn-primary').click();
            }
        });
    }
    
    function closeModal() {
        $('#custom-modal').fadeOut(300);
        if (modalCallback) {
            modalCallback();
            modalCallback = null;
        }
    }
    
    // Close modal on overlay click
    $(document).on('click', '.modal-overlay', function() {
        closeModal();
    });
    
    // Close modal with Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#custom-modal').is(':visible')) {
            closeModal();
        }
    });

    function initMegaMenu() {
        // Initialize sortable for main menu items
        initSortableMenuItems();
        
        // Initialize sortable for all children (with connectWith)
        initAllSortableChildren();
        
        // Initialize event handlers
        initEventHandlers();
        
        // Initialize search boxes
        initSearchBoxes();
    }

    // Make menu items sortable with ability to convert to children
    function initSortableMenuItems() {
        $('#mega-menu-structure').sortable({
            handle: '.drag-handle',
            placeholder: 'ui-sortable-placeholder',
            tolerance: 'pointer',
            connectWith: '.sortable-children', // اجازه می‌ده parent رو به child تبدیل کنیم
            receive: function(event, ui) {
                // یک child به parent تبدیل شد
                const $item = ui.item;
                
                // چک کنیم که این واقعاً یک child بود
                if ($item.hasClass('child-item')) {
                    convertChildToParent($item);
                }
            },
            update: function(event, ui) {
                updateMenuIndices();
            }
        });
    }

    // Make child items sortable with ability to move between parents
    function initSortableChildItems($parent) {
        $parent.find('.sortable-children').sortable({
            handle: '.child-drag-handle',
            placeholder: 'ui-sortable-placeholder',
            tolerance: 'pointer',
            connectWith: '.sortable-children, #mega-menu-structure', // اجازه می‌ده child رو به parent تبدیل کنیم
            receive: function(event, ui) {
                // یک parent به child تبدیل شد
                const $item = ui.item;
                
                // چک کنیم که این واقعاً یک parent بود
                if ($item.hasClass('menu-item')) {
                    convertParentToChild($item);
                }
            },
            update: function(event, ui) {
                console.log('Child item moved or reordered');
            }
        });
    }
    
    // Initialize all sortable children at once
    function initAllSortableChildren() {
        $('.sortable-children').sortable({
            handle: '.child-drag-handle',
            placeholder: 'ui-sortable-placeholder',
            tolerance: 'pointer',
            connectWith: '.sortable-children, #mega-menu-structure', // اجازه می‌ده child رو به parent تبدیل کنیم
            receive: function(event, ui) {
                // یک parent به child تبدیل شد
                const $item = ui.item;
                
                // چک کنیم که این واقعاً یک parent بود
                if ($item.hasClass('menu-item')) {
                    convertParentToChild($item);
                }
            },
            update: function(event, ui) {
                console.log('Child item moved or reordered');
            }
        });
        
        // Re-initialize main menu sortable to accept children
        $('#mega-menu-structure').sortable('option', 'connectWith', '.sortable-children');
    }
    
    // Convert a parent menu item to a child
    function convertParentToChild($menuItem) {
        // چک کنیم که آیا این parent خودش children داره
        const hasChildren = $menuItem.find('.child-item').length > 0;
        const title = $menuItem.find('.menu-title-preview').text();
        const childrenCount = $menuItem.find('.child-item').length;
        
        if (hasChildren) {
            showConfirm(
                '⚠️ هشدار: حذف فرزندان',
                `منوی "<strong>${title}</strong>" دارای <strong>${childrenCount} آیتم فرزند</strong> است.<br><br>با تبدیل آن به فرزند، تمام فرزندانش حذف می‌شوند.<br><br>آیا ادامه می‌دهید؟`,
                function() {
                    // تایید - ادامه تبدیل
                    performParentToChildConversion($menuItem);
                },
                function() {
                    // لغو - برگردون به جای اولش
                    $menuItem.remove();
                    $('#mega-menu-structure').sortable('cancel');
                    initSortableMenuItems();
                    initAllSortableChildren();
                }
            );
            return;
        }
        
        // اگر فرزند نداره، مستقیم تبدیل کن
        performParentToChildConversion($menuItem);
    }
    
    function performParentToChildConversion($menuItem) {
        
        const itemData = {
            id: $menuItem.data('id'),
            title: $menuItem.find('.menu-item-title').val() || $menuItem.find('.menu-title-preview').text(),
            url: $menuItem.find('.menu-item-url').val() || '#',
            item_visibility: $menuItem.find('.item-visibility-radio:checked').val() || 'both'
        };
        
        // ساخت child جدید
        const childHtml = createChildItemHtml(itemData);
        
        // جایگزین کردن parent با child
        $menuItem.replaceWith(childHtml);
        
        // Re-initialize sortable
        initAllSortableChildren();
        
        console.log('Converted parent to child:', itemData.title);
    }
    
    // Convert a child item to a parent menu item
    function convertChildToParent($childItem) {
        const childData = {
            id: $childItem.data('id'),
            title: $childItem.find('.child-item-title').val(),
            url: $childItem.find('.child-item-url').val(),
            icon_type: 'image',
            icon_value: '',
            icon_visibility: 'both',
            item_visibility: $childItem.find('.child-item-visibility-radio:checked').val() || 'both',
            children: []
        };
        
        // ساخت parent جدید
        const parentHtml = createMenuItemHtml(childData, menuItemIndex);
        
        // جایگزین کردن child با parent
        $childItem.replaceWith(parentHtml);
        
        menuItemIndex++;
        
        // Re-initialize sortable
        initSortableMenuItems();
        initAllSortableChildren();
        
        console.log('Converted child to parent:', childData.title);
    }

    // Initialize all event handlers
    function initEventHandlers() {
        // Toggle section content
        $(document).on('click', '.section-title', function() {
            const $this = $(this);
            const targetId = $this.data('toggle');
            const $content = $('#' + targetId);
            
            $content.slideToggle(300);
            $this.toggleClass('active');
        });

        // Add custom link
        $(document).on('click', '.add-custom-link', function() {
            addCustomLinkItem();
        });
        
        // Add custom link with Enter key
        $(document).on('keypress', '#custom-link-title, #custom-link-url', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                addCustomLinkItem();
            }
        });
        
        // Tab navigation for custom link inputs
        $(document).on('keydown', '#custom-link-title', function(e) {
            if (e.which === 9 && !e.shiftKey) { // Tab key (forward)
                e.preventDefault();
                $('#custom-link-url').focus();
            }
        });
        
        $(document).on('keydown', '#custom-link-url', function(e) {
            if (e.which === 9 && e.shiftKey) { // Shift+Tab (backward)
                e.preventDefault();
                $('#custom-link-title').focus();
            }
        });

        // Add selected items from lists
        $(document).on('click', '.add-selected-items', function() {
            const $section = $(this).closest('.section-content');
            const $checked = $section.find('input[type="checkbox"]:checked');
            
            if ($checked.length === 0) {
                showAlert(
                    'آیتمی انتخاب نشده',
                    'لطفاً حداقل یک آیتم را انتخاب کنید.',
                    'warning'
                );
                return;
            }
            
            $checked.each(function() {
                const $checkbox = $(this);
                const itemData = {
                    id: 'menu_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                    title: $checkbox.data('title'),
                    url: $checkbox.data('url'),
                    icon_type: 'image',
                    icon_value: '',
                    icon_visibility: 'both',
                    item_visibility: 'both',
                    children: []
                };
                
                addMenuItem(itemData);
            });
            
            // Uncheck all
            $checked.prop('checked', false);
            
            // Close section
            $section.slideUp();
            $section.prev('.section-title').removeClass('active');
        });

        // Toggle icon type (image/svg)
        $(document).on('change', '.icon-type-radio', function() {
            const $menuItem = $(this).closest('.menu-item');
            const type = $(this).val();
            
            if (type === 'image') {
                $menuItem.find('.icon-image-field').show();
                $menuItem.find('.icon-svg-field').hide();
            } else {
                $menuItem.find('.icon-image-field').hide();
                $menuItem.find('.icon-svg-field').show();
            }
        });

        // Toggle menu item content (edit)
        $(document).on('click', '.edit-item', function() {
            const $menuItem = $(this).closest('.menu-item');
            const $content = $menuItem.find('.menu-item-content');
            
            $content.slideToggle(300);
        });

        // Toggle children visibility
        $(document).on('click', '.toggle-children', function() {
            const $menuItem = $(this).closest('.menu-item');
            const $childrenSection = $menuItem.find('.menu-children-section');
            
            $childrenSection.slideToggle(300);
            $(this).find('.dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
        });

        // Close menu item content
        $(document).on('click', '.close-item', function() {
            const $menuItem = $(this).closest('.menu-item');
            $menuItem.find('.menu-item-content').slideUp(300);
        });

        // Delete menu item
        $(document).on('click', '.delete-item', function() {
            const $menuItem = $(this).closest('.menu-item');
            const title = $menuItem.find('.menu-title-preview').text();
            
            showConfirm(
                'حذف آیتم منو',
                `آیا از حذف "<strong>${title}</strong>" اطمینان دارید؟`,
                function() {
                    // تایید - حذف کن
                    $menuItem.fadeOut(300, function() {
                        $(this).remove();
                        checkEmptyMenu();
                    });
                }
            );
        });

        // Update menu item preview on input change
        $(document).on('input', '.menu-item-title', function() {
            const $menuItem = $(this).closest('.menu-item');
            const title = $(this).val();
            $menuItem.find('.menu-title-preview').text(title);
        });

        // Select icon image
        $(document).on('click', '.select-icon-image', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $menuItem = $button.closest('.menu-item');
            const $iconInput = $menuItem.find('.menu-item-icon-value');
            
            const mediaUploader = wp.media({
                title: 'انتخاب آیکون',
                button: {
                    text: 'انتخاب'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                const iconUrl = attachment.url;
                
                $iconInput.val(iconUrl);
                
                // Update preview in header
                let $preview = $menuItem.find('.menu-icon-preview');
                if ($preview.length === 0 || $preview.hasClass('svg-icon') || $menuItem.find('.menu-icon-placeholder').length) {
                    $menuItem.find('.menu-icon-preview, .menu-icon-placeholder').replaceWith(
                        '<img src="' + iconUrl + '" class="menu-icon-preview" alt="">'
                    );
                } else {
                    $preview.attr('src', iconUrl);
                }
                
                // Update icon preview in edit area
                let $iconPreview = $menuItem.find('.icon-image-field .icon-preview');
                if ($iconPreview.length === 0) {
                    $button.after('<button type="button" class="button remove-icon">حذف</button>');
                    $button.parent().after('<div class="icon-preview"><img src="' + iconUrl + '" alt="آیکون"></div>');
                } else {
                    $iconPreview.find('img').attr('src', iconUrl);
                }
            });
            
            mediaUploader.open();
        });

        // Update SVG preview on textarea change
        $(document).on('input', '.menu-item-icon-svg', function() {
            const $menuItem = $(this).closest('.menu-item');
            const svgCode = $(this).val().trim();
            
            if (svgCode) {
                // Update preview in header
                $menuItem.find('.menu-icon-preview, .menu-icon-placeholder').replaceWith(
                    '<span class="menu-icon-preview svg-icon">' + svgCode + '</span>'
                );
                
                // Update preview in edit area
                let $svgPreview = $menuItem.find('.icon-svg-field .svg-preview');
                if ($svgPreview.length === 0) {
                    $(this).after('<div class="icon-preview svg-preview">' + svgCode + '</div>');
                } else {
                    $svgPreview.html(svgCode);
                }
            }
        });

        // Remove icon
        $(document).on('click', '.remove-icon', function() {
            const $menuItem = $(this).closest('.menu-item');
            const $iconField = $(this).closest('.icon-image-field, .icon-svg-field');
            
            // Clear input
            $iconField.find('.menu-item-icon-value, .menu-item-icon-svg').val('');
            
            // Remove from header
            $menuItem.find('.menu-icon-preview').replaceWith(
                '<span class="dashicons dashicons-admin-links menu-icon-placeholder"></span>'
            );
            
            // Remove preview and button
            $iconField.find('.icon-preview').remove();
            $(this).remove();
        });

        // Add child item
        $(document).on('click', '.add-child-item', function() {
            const $menuItem = $(this).closest('.menu-item');
            addChildItemToParent($menuItem);
        });
        
        // Add child with Enter key on child fields
        $(document).on('keypress', '.child-item-title, .child-item-url', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                const $childItem = $(this).closest('.child-item');
                const $menuItem = $childItem.closest('.menu-item');
                
                // فقط اگر هر دو فیلد پر باشند، فرزند جدید اضافه کن
                const title = $childItem.find('.child-item-title').val().trim();
                const url = $childItem.find('.child-item-url').val().trim();
                
                if (title && url) {
                    addChildItemToParent($menuItem, true); // true = auto focus
                } else {
                    // اگر title خالیه، فوکوس روش بمونه
                    // اگر url خالیه، فوکوس بره روش
                    if (!url && title) {
                        $childItem.find('.child-item-url').focus();
                    }
                }
            }
        });
        
        // Tab navigation for child items
        $(document).on('keydown', '.child-item-title', function(e) {
            if (e.which === 9 && !e.shiftKey) { // Tab key (forward)
                e.preventDefault();
                $(this).closest('.child-item').find('.child-item-url').focus();
            }
        });
        
        $(document).on('keydown', '.child-item-url', function(e) {
            if (e.which === 9) {
                const $childItem = $(this).closest('.child-item');
                
                if (e.shiftKey) { // Shift+Tab (backward)
                    e.preventDefault();
                    $childItem.find('.child-item-title').focus();
                } else { // Tab (forward) - go to next child or add new
                    const $nextChild = $childItem.next('.child-item');
                    if ($nextChild.length) {
                        e.preventDefault();
                        $nextChild.find('.child-item-title').focus();
                    }
                    // اگر آخرین child باشه، به صورت طبیعی Tab کار کنه
                }
            }
        });

        // Toggle child settings
        $(document).on('click', '.edit-child', function() {
            const $childItem = $(this).closest('.child-item');
            $childItem.find('.child-item-settings').slideToggle(200);
        });

        // Delete child item
        $(document).on('click', '.delete-child', function() {
            const $childItem = $(this).closest('.child-item');
            const title = $childItem.find('.child-item-title').val() || 'این فرزند';
            
            showConfirm(
                'حذف فرزند',
                `آیا از حذف "<strong>${title}</strong>" اطمینان دارید؟`,
                function() {
                    // تایید - حذف کن
                    $childItem.fadeOut(200, function() {
                        $(this).remove();
                    });
                }
            );
        });

        // Save mega menu
        $(document).on('click', '.save-mega-menu', function() {
            const $button = $(this);
            const $status = $('.save-status');
            
            const menuData = collectMenuData();
            
            $button.addClass('loading');
            $status.text('در حال ذخیره...').removeClass('success error');
            
            $.ajax({
                url: megaMenuAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'save_mega_menu',
                    nonce: megaMenuAdmin.nonce,
                    location: currentLocation,
                    menu_items: JSON.stringify(menuData)
                },
                success: function(response) {
                    $button.removeClass('loading');
                    
                    if (response.success) {
                        $status.text(response.data.message).addClass('success');
                        setTimeout(function() {
                            $status.text('');
                        }, 3000);
                    } else {
                        $status.text(response.data.message).addClass('error');
                    }
                },
                error: function() {
                    $button.removeClass('loading');
                    $status.text('خطا در ذخیره سازی!').addClass('error');
                }
            });
        });
        
        // Add new location
        $(document).on('click', '#add-new-location', function(e) {
            e.preventDefault();
            
            showPrompt(
                'افزودن لوکیشن جدید',
                'کلید لوکیشن را وارد کنید (فقط حروف کوچک انگلیسی، اعداد و خط تیره):<br><small style="color: #646970;">مثال: sidebar, mobile-menu, top-bar</small>',
                'مثلاً: sidebar',
                function(locationKey) {
                    // مرحله 2: دریافت نام نمایشی
                    showPrompt(
                        'نام نمایشی',
                        'نام نمایشی لوکیشن را وارد کنید:',
                        'مثلاً: منوی سایدبار',
                        function(locationName) {
                            // ارسال درخواست
                            $.ajax({
                                url: megaMenuAdmin.ajaxUrl,
                                type: 'POST',
                                data: {
                                    action: 'add_menu_location',
                                    nonce: megaMenuAdmin.nonce,
                                    location_key: locationKey,
                                    location_name: locationName
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // نمایش موفقیت و redirect
                                        showAlert(
                                            'موفق!',
                                            `لوکیشن "<strong>${locationName}</strong>" با موفقیت ایجاد شد.`,
                                            'success'
                                        );
                                        setTimeout(function() {
                                            window.location.href = '?page=mega-menu-settings&location=' + response.data.location_key;
                                        }, 1500);
                                    } else {
                                        showAlert('خطا', response.data.message, 'error');
                                    }
                                },
                                error: function() {
                                    showAlert('خطا', 'خطا در افزودن لوکیشن!', 'error');
                                }
                            });
                        },
                        function(value) {
                            return value.length > 0;
                        }
                    );
                },
                function(value) {
                    // Validation برای کلید لوکیشن
                    if (!value) return false;
                    if (!/^[a-z0-9-]+$/.test(value)) {
                        showAlert(
                            'کلید نامعتبر',
                            'کلید لوکیشن فقط باید شامل حروف کوچک انگلیسی، اعداد و خط تیره باشد.',
                            'error'
                        );
                        return false;
                    }
                    return true;
                }
            );
        });
        
        // Delete location
        $(document).on('click', '.delete-location', function(e) {
            e.preventDefault();
            e.stopPropagation(); // جلوگیری از کلیک روی tab
            
            const $deleteBtn = $(this);
            const locationKey = $deleteBtn.data('location');
            const locationName = $deleteBtn.data('name');
            
            showConfirm(
                'حذف لوکیشن',
                `آیا از حذف لوکیشن "<strong>${locationName}</strong>" اطمینان دارید؟<br><br><span style="color: #d97706;">⚠️ تمام منوهای این لوکیشن حذف خواهند شد و این عملیات قابل بازگشت نیست!</span>`,
                function() {
                    // تایید - حذف کن
                    performLocationDeletion($deleteBtn, locationKey, locationName);
                }
            );
        });
        
        function performLocationDeletion($deleteBtn, locationKey, locationName) {
            
            $deleteBtn.addClass('deleting');
            
            $.ajax({
                url: megaMenuAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'delete_menu_location',
                    nonce: megaMenuAdmin.nonce,
                    location_key: locationKey
                },
                success: function(response) {
                    if (response.success) {
                        showAlert(
                            'حذف شد!',
                            `لوکیشن "<strong>${locationName}</strong>" با موفقیت حذف شد.`,
                            'success'
                        );
                        setTimeout(function() {
                            window.location.href = '?page=mega-menu-settings&location=header';
                        }, 1500);
                    } else {
                        showAlert('خطا', response.data.message, 'error');
                        $deleteBtn.removeClass('deleting');
                    }
                },
                error: function() {
                    showAlert('خطا', 'خطا در حذف لوکیشن!', 'error');
                    $deleteBtn.removeClass('deleting');
                }
            });
        }
        
        // Show drag & drop help
        $(document).on('click', '.show-drag-help', function(e) {
            e.preventDefault();
            $('#drag-drop-help-modal').fadeIn(300);
        });
        
        // Close help modal
        $(document).on('click', '.close-help, .help-overlay', function() {
            $('#drag-drop-help-modal').fadeOut(300);
        });
        
        // Prevent closing when clicking inside modal
        $(document).on('click', '.help-content', function(e) {
            e.stopPropagation();
        });
        
        // Close modal with Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#drag-drop-help-modal').is(':visible')) {
                $('#drag-drop-help-modal').fadeOut(300);
            }
        });
    }

    // Initialize search boxes
    function initSearchBoxes() {
        $(document).on('input', '.search-box', function() {
            const searchTerm = $(this).val().toLowerCase();
            const $list = $(this).siblings('.items-list');
            
            $list.find('.item-checkbox').each(function() {
                const text = $(this).text().toLowerCase();
                if (text.indexOf(searchTerm) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }

    // Add custom link function
    function addCustomLinkItem() {
        const title = $('#custom-link-title').val().trim();
        const url = $('#custom-link-url').val().trim();
        
        if (!title || !url) {
            showAlert(
                'فیلدهای خالی',
                'لطفاً عنوان و آدرس را وارد کنید.',
                'warning'
            );
            // Focus on empty field
            if (!title) {
                setTimeout(function() {
                    $('#custom-link-title').focus();
                }, 400);
            } else if (!url) {
                setTimeout(function() {
                    $('#custom-link-url').focus();
                }, 400);
            }
            return;
        }
        
        const itemData = {
            id: 'menu_' + Date.now(),
            title: title,
            url: url,
            icon_type: 'image',
            icon_value: '',
            icon_visibility: 'both',
            item_visibility: 'both',
            children: []
        };
        
        addMenuItem(itemData, false); // false = don't auto focus on the new menu item
        
        // Clear inputs
        $('#custom-link-title, #custom-link-url').val('');
        
        // Focus back to title input for quick adding
        setTimeout(function() {
            $('#custom-link-title').focus();
        }, 50);
    }
    
    // Add a new menu item to the structure
    function addMenuItem(itemData, autoFocus) {
        const $emptyMessage = $('.empty-menu-message');
        if ($emptyMessage.length) {
            $emptyMessage.remove();
        }
        
        const itemHtml = createMenuItemHtml(itemData, menuItemIndex);
        $('#mega-menu-structure').append(itemHtml);
        
        const $newItem = $('#mega-menu-structure .menu-item').last();
        
        // Re-initialize all sortable children to maintain connectWith
        initAllSortableChildren();
        
        menuItemIndex++;
        
        // Auto-open the new item for editing
        $newItem.find('.menu-item-content').slideDown(300, function() {
            // Focus on title input if autoFocus is enabled
            if (autoFocus) {
                $newItem.find('.menu-item-title').focus().select();
            }
        });
    }
    
    // Add child item to a parent
    function addChildItemToParent($menuItem, autoFocus) {
        const $childrenList = $menuItem.find('.children-list');
        
        const childData = {
            id: 'child_' + Date.now(),
            title: '',
            url: '',
            item_visibility: 'both'
        };
        
        const childHtml = createChildItemHtml(childData);
        $childrenList.append(childHtml);
        
        // Re-initialize all sortable children to maintain connectWith
        initAllSortableChildren();
        
        // Focus on the new child's title input
        if (autoFocus) {
            const $newChild = $childrenList.find('.child-item').last();
            setTimeout(function() {
                $newChild.find('.child-item-title').focus();
            }, 100);
        }
    }

    // Create HTML for a menu item
    function createMenuItemHtml(item, index) {
        let iconHtml;
        if (item.icon_value) {
            if (item.icon_type === 'svg') {
                iconHtml = `<span class="menu-icon-preview svg-icon">${item.icon_value}</span>`;
            } else {
                iconHtml = `<img src="${item.icon_value}" class="menu-icon-preview" alt="">`;
            }
        } else {
            iconHtml = `<span class="dashicons dashicons-admin-links menu-icon-placeholder"></span>`;
        }
        
        const iconTypeImageChecked = item.icon_type === 'image' ? 'checked' : '';
        const iconTypeSvgChecked = item.icon_type === 'svg' ? 'checked' : '';
        const iconImageDisplay = item.icon_type === 'svg' ? 'display:none;' : '';
        const iconSvgDisplay = item.icon_type === 'image' ? 'display:none;' : '';
        
        const iconImageValue = item.icon_type === 'image' ? item.icon_value : '';
        const iconSvgValue = item.icon_type === 'svg' ? escapeHtml(item.icon_value) : '';
        
        const iconImagePreview = item.icon_value && item.icon_type === 'image'
            ? `<div class="icon-preview"><img src="${item.icon_value}" alt="آیکون"></div>`
            : '';
        const iconSvgPreview = item.icon_value && item.icon_type === 'svg'
            ? `<div class="icon-preview svg-preview">${item.icon_value}</div>`
            : '';
        
        const iconImageRemoveBtn = item.icon_value && item.icon_type === 'image'
            ? `<button type="button" class="button remove-icon">حذف</button>`
            : '';
        
        // Visibility radios
        const iconVisibilityBoth = item.icon_visibility === 'both' ? 'checked' : '';
        const iconVisibilityDesktop = item.icon_visibility === 'desktop' ? 'checked' : '';
        const iconVisibilityMobile = item.icon_visibility === 'mobile' ? 'checked' : '';
        const iconVisibilityNone = item.icon_visibility === 'none' ? 'checked' : '';
        
        const itemVisibilityBoth = item.item_visibility === 'both' ? 'checked' : '';
        const itemVisibilityDesktop = item.item_visibility === 'desktop' ? 'checked' : '';
        const itemVisibilityMobile = item.item_visibility === 'mobile' ? 'checked' : '';
        const itemVisibilityNone = item.item_visibility === 'none' ? 'checked' : '';
        
        let childrenHtml = '';
        if (item.children && item.children.length > 0) {
            item.children.forEach(function(child) {
                childrenHtml += createChildItemHtml(child);
            });
        }
        
        return `
            <div class="menu-item" data-id="${item.id}" data-index="${index}">
                <div class="menu-item-header">
                    <span class="drag-handle">
                        <span class="dashicons dashicons-menu"></span>
                    </span>
                    <div class="menu-item-preview">
                        ${iconHtml}
                        <span class="menu-title-preview">${escapeHtml(item.title)}</span>
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
                            <input type="text" class="menu-item-title" value="${escapeHtml(item.title)}" placeholder="عنوان منو">
                        </div>
                        <div class="field-row">
                            <label>آدرس URL:</label>
                            <input type="text" class="menu-item-url" value="${escapeHtml(item.url)}" placeholder="https://example.com">
                        </div>
                        
                        <div class="field-row">
                            <label>نوع آیکون:</label>
                            <div class="icon-type-selector">
                                <label style="margin-left: 20px;">
                                    <input type="radio" name="icon_type_${item.id}" value="image" class="icon-type-radio" ${iconTypeImageChecked}>
                                    تصویر (PNG/JPG/SVG file)
                                </label>
                                <label>
                                    <input type="radio" name="icon_type_${item.id}" value="svg" class="icon-type-radio" ${iconTypeSvgChecked}>
                                    کد SVG
                                </label>
                            </div>
                        </div>

                        <div class="field-row icon-image-field" style="${iconImageDisplay}">
                            <label>تصویر آیکون:</label>
                            <div class="icon-field">
                                <input type="text" class="menu-item-icon-value" value="${iconImageValue}" placeholder="آدرس تصویر" readonly>
                                <button type="button" class="button select-icon-image">انتخاب تصویر</button>
                                ${iconImageRemoveBtn}
                            </div>
                            ${iconImagePreview}
                        </div>

                        <div class="field-row icon-svg-field" style="${iconSvgDisplay}">
                            <label>کد SVG:</label>
                            <textarea class="menu-item-icon-svg" placeholder="<svg>...</svg>" rows="5" style="width: 100%; font-family: monospace; direction: ltr; text-align: left;">${iconSvgValue}</textarea>
                            ${iconSvgPreview}
                        </div>

                        <div class="field-row">
                            <label>نمایش آیکون در:</label>
                            <div class="visibility-options">
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="icon_visibility_${item.id}" value="both" class="icon-visibility-radio" ${iconVisibilityBoth}>
                                    موبایل و دسکتاپ (هردو)
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="icon_visibility_${item.id}" value="desktop" class="icon-visibility-radio" ${iconVisibilityDesktop}>
                                    فقط دسکتاپ
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="icon_visibility_${item.id}" value="mobile" class="icon-visibility-radio" ${iconVisibilityMobile}>
                                    فقط موبایل
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="icon_visibility_${item.id}" value="none" class="icon-visibility-radio" ${iconVisibilityNone}>
                                    نمایش داده نشود
                                </label>
                            </div>
                        </div>

                        <div class="field-row">
                            <label>نمایش این آیتم در:</label>
                            <div class="visibility-options">
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="item_visibility_${item.id}" value="both" class="item-visibility-radio" ${itemVisibilityBoth}>
                                    موبایل و دسکتاپ (هردو)
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="item_visibility_${item.id}" value="desktop" class="item-visibility-radio" ${itemVisibilityDesktop}>
                                    فقط دسکتاپ
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="item_visibility_${item.id}" value="mobile" class="item-visibility-radio" ${itemVisibilityMobile}>
                                    فقط موبایل
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="item_visibility_${item.id}" value="none" class="item-visibility-radio" ${itemVisibilityNone}>
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
                            ${childrenHtml}
                        </div>
                    </div>
                    <div class="item-actions">
                        <button type="button" class="button close-item">بستن</button>
                    </div>
                </div>
            </div>
        `;
    }

    // Create HTML for a child item
    function createChildItemHtml(child) {
        const childVisibilityBoth = child.item_visibility === 'both' ? 'checked' : '';
        const childVisibilityDesktop = child.item_visibility === 'desktop' ? 'checked' : '';
        const childVisibilityMobile = child.item_visibility === 'mobile' ? 'checked' : '';
        const childVisibilityNone = child.item_visibility === 'none' ? 'checked' : '';
        
        return `
            <div class="child-item" data-id="${child.id}">
                <span class="child-drag-handle">
                    <span class="dashicons dashicons-menu"></span>
                </span>
                <div class="child-item-fields">
                    <input type="text" class="child-item-title" value="${escapeHtml(child.title)}" placeholder="عنوان فرزند">
                    <input type="text" class="child-item-url" value="${escapeHtml(child.url)}" placeholder="آدرس URL">
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
                            <input type="radio" name="child_visibility_${child.id}" value="both" class="child-item-visibility-radio" ${childVisibilityBoth}>
                            موبایل و دسکتاپ (هردو)
                        </label>
                        <label style="display: block; margin: 3px 0;">
                            <input type="radio" name="child_visibility_${child.id}" value="desktop" class="child-item-visibility-radio" ${childVisibilityDesktop}>
                            فقط دسکتاپ
                        </label>
                        <label style="display: block; margin: 3px 0;">
                            <input type="radio" name="child_visibility_${child.id}" value="mobile" class="child-item-visibility-radio" ${childVisibilityMobile}>
                            فقط موبایل
                        </label>
                        <label style="display: block; margin: 3px 0;">
                            <input type="radio" name="child_visibility_${child.id}" value="none" class="child-item-visibility-radio" ${childVisibilityNone}>
                            نمایش داده نشود (مخفی)
                        </label>
                    </div>
                </div>
            </div>
        `;
    }

    // Collect all menu data for saving
    function collectMenuData() {
        const menuData = [];
        
        $('#mega-menu-structure .menu-item').each(function(index) {
            const $item = $(this);
            const iconType = $item.find('.icon-type-radio:checked').val() || 'image';
            
            let iconValue = '';
            if (iconType === 'image') {
                iconValue = $item.find('.menu-item-icon-value').val() || '';
            } else {
                iconValue = $item.find('.menu-item-icon-svg').val() || '';
            }
            
            const itemData = {
                id: $item.data('id'),
                title: $item.find('.menu-item-title').val(),
                url: $item.find('.menu-item-url').val(),
                icon_type: iconType,
                icon_value: iconValue,
                icon_visibility: $item.find('.icon-visibility-radio:checked').val() || 'both',
                item_visibility: $item.find('.item-visibility-radio:checked').val() || 'both',
                children: []
            };
            
            // Collect children
            $item.find('.child-item').each(function() {
                const $child = $(this);
                
                const childData = {
                    id: $child.data('id'),
                    title: $child.find('.child-item-title').val(),
                    url: $child.find('.child-item-url').val(),
                    item_visibility: $child.find('.child-item-visibility-radio:checked').val() || 'both'
                };
                
                itemData.children.push(childData);
            });
            
            menuData.push(itemData);
        });
        
        return menuData;
    }

    // Update menu item indices after sorting
    function updateMenuIndices() {
        $('#mega-menu-structure .menu-item').each(function(index) {
            $(this).attr('data-index', index);
        });
    }

    // Check if menu is empty and show message
    function checkEmptyMenu() {
        if ($('#mega-menu-structure .menu-item').length === 0) {
            $('#mega-menu-structure').html(`
                <div class="empty-menu-message">
                    <span class="dashicons dashicons-menu-alt3"></span>
                    <p>هنوز آیتمی به منو اضافه نشده است.</p>
                    <p>از سایدبار سمت راست شروع کنید.</p>
                </div>
            `);
        }
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

})(jQuery);

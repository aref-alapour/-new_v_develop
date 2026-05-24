<?php
// دریافت قالب‌های پیامک از تنظیمات
$sms_templates = get_option('sms_templates_settings', []);

// اگر قالبی وجود ندارد، قالب پیش‌فرض اضافه کن
if (empty($sms_templates)) {
    $sms_templates = [
        1 => [
            'title' => 'پیام خوش‌آمدگویی',
            'content' => 'کاربر عزیز، 
درخواست شما دریافت شد و در حال بررسی است.
لطفاً کمی صبور باشید، به‌زودی پشتیبان با شما تماس خواهد گرفت.
اسکیپ زوم؛ مرجع بازیهای گروهی
لغو 11',
            'created_at' => current_time('mysql')
        ]
    ];
}

// بررسی نقش کاربر برای دسترسی به ارسال پیام دلخواه
$current_user = wp_get_current_user();
$allowed_custom_sms_roles = ['administrator', 'shopist', 'supervisor'];
$can_send_custom_sms = array_intersect($allowed_custom_sms_roles, $current_user->roles);
?>

<style>
    /* فونت آیکون‌ها */
    @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

    /* استایل موبایل و اجزای آن */
    body {
        font-family: "Open Sans", "Vazirmatn", "Yekan", sans-serif;
    }

    .iphone {
        border: solid 10px #000;
        width: 336px;
        height: 725px;
        margin: 0 auto;
        border-radius: 50px;
        background: #fff;
        box-shadow: 0 0 0 5px #131313;
        position: relative;
        overflow: hidden;
    }

    .silence-switch,
    .volume-rocker-top,
    .volume-rocker-bottom,
    .power-button {
        z-index: 2;
    }

    .silence-switch {
        position: absolute;
        margin-right: -19px;
        margin-top: 90px;
        width: 4px;
        height: 30px;
        border-bottom-right-radius: 10px;
        border-top-right-radius: 10px;
        background: linear-gradient(to left, rgba(76, 76, 76, 1) 0%, rgba(89, 89, 89, 1) 12%, rgba(102, 102, 102, 1) 25%, rgba(71, 71, 71, 1) 39%, rgba(44, 44, 44, 1) 50%, rgba(0, 0, 0, 1) 51%, rgba(17, 17, 17, 1) 60%, rgba(43, 43, 43, 1) 76%, rgba(28, 28, 28, 1) 91%, rgba(19, 19, 19, 1) 100%);
    }

    .volume-rocker-top {
        position: absolute;
        margin-right: -19px;
        margin-top: 140px;
        width: 4px;
        height: 50px;
        border-bottom-right-radius: 10px;
        border-top-right-radius: 10px;
        background: linear-gradient(to left, rgba(76, 76, 76, 1) 0%, rgba(89, 89, 89, 1) 12%, rgba(102, 102, 102, 1) 25%, rgba(71, 71, 71, 1) 39%, rgba(44, 44, 44, 1) 50%, rgba(0, 0, 0, 1) 51%, rgba(17, 17, 17, 1) 60%, rgba(43, 43, 43, 1) 76%, rgba(28, 28, 28, 1) 91%, rgba(19, 19, 19, 1) 100%);
    }

    .volume-rocker-bottom {
        position: absolute;
        margin-right: -19px;
        margin-top: 200px;
        width: 4px;
        height: 50px;
        border-bottom-right-radius: 10px;
        border-top-right-radius: 10px;
        background: linear-gradient(to left, rgba(76, 76, 76, 1) 0%, rgba(89, 89, 89, 1) 12%, rgba(102, 102, 102, 1) 25%, rgba(71, 71, 71, 1) 39%, rgba(44, 44, 44, 1) 50%, rgba(0, 0, 0, 1) 51%, rgba(17, 17, 17, 1) 60%, rgba(43, 43, 43, 1) 76%, rgba(28, 28, 28, 1) 91%, rgba(19, 19, 19, 1) 100%);
    }

    .power-button {
        position: absolute;
        margin-left: 350px;
        margin-top: 200px;
        width: 4px;
        height: 70px;
        border-bottom-left-radius: 10px;
        border-top-left-radius: 10px;
        background: linear-gradient(to right, rgba(19, 19, 19, 1) 0%, rgba(28, 28, 28, 1) 9%, rgba(43, 43, 43, 1) 24%, rgba(17, 17, 17, 1) 40%, rgba(0, 0, 0, 1) 49%, rgba(44, 44, 44, 1) 50%, rgba(71, 71, 71, 1) 61%, rgba(102, 102, 102, 1) 75%, rgba(89, 89, 89, 1) 88%, rgba(76, 76, 76, 1) 100%);
    }

    .top-section {
        background: #f6f6f7;
        height: 93px;
        margin: 0;
        border-top-right-radius: 40px;
        border-top-left-radius: 40px;
        border-bottom: 1px solid #e6e6e6;
        position: relative;
    }

    .top-section-time {
        position: absolute;
        margin-right: 20px;
        margin-top: 8px;
        font-size: 12px;
        font-weight: bold;
        color: #222;
    }

    .top-section-middle {
        margin: auto;
        width: 180px;
        background: black;
        height: 20px;
        border-bottom-left-radius: 20px;
        border-bottom-right-radius: 20px;
        padding-top: 5px;
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
    }

    .top-section-user-pic {
        margin: auto;
        margin-top: 10px;
        width: 30px;
        height: 40px;
        border-radius: 100%;
        background-color: none;
        font-size: 40px;
        color: #999999;
    }

    .top-section-user-name {
        margin: auto;
        margin-top: 15px;
        background: none;
        height: 10px;
        width: 100px;
        font-size: 10px;
        text-align: center;
        color: #2f2f2f;
        font-weight: 800;
    }

    .speaker {
        width: 60px;
        height: 8px;
        background: #2e2e2e;
        margin-right: 60px;
        border-radius: 10px;
        position: absolute;
        top: 10px;
    }

    .front-camera {
        height: 8px;
        width: 8px;
        background: #919191;
        border-radius: 100%;
        position: absolute;
        margin-right: 25px;
    }

    .top-section-symbols {
        position: absolute;
        left: 15px;
        margin-top: 10px;
        font-size: 12px;
        color: #222;
    }

    .messages-section {
        background: #fff;
        height: 270px;
        overflow: hidden;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .message {
        border-radius: 20px;
        margin: 0 15px 10px;
        padding: 10px 15px;
        position: relative;
        animation: fadeInOpacity 0.4s ease-in;
    }

    .message.to {
        background-color: #0088cc;
        color: #fff;
        margin-right: 80px;
        border-radius: 18px 18px 18px 4px;
        position: relative;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .message.to::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: -5px;
        width: 0;
        height: 0;
        border: 8px solid transparent;
        border-right-color: #0088cc;
        border-bottom: none;
        border-left: none;
    }

    .keyboard-section {
        background: #d1d3d9;
        height: 327px;
        border-bottom-left-radius: 40px;
        border-bottom-right-radius: 40px;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .keyboard-above {
        background: white;
        height: 40px;
        position: relative;
        display: flex;
        align-items: center;
    }

    .keyboardinput {
        border-radius: 35px;
        border: 1px solid #c4c4c6;
        padding: 8px;
        width: 200px;
        height: 28px;
        margin-right: 110px;
        margin-top: 9px;
        overflow: scroll;
        caret-color: #3478f6;
        font-size: 14px;
        font-family: inherit;
    }

    .app-store {
        background: #999999;
        margin-right: 20px;
        padding: 0 5px;
        border-radius: 20px;
        color: #fff;
    }

    .cursor {
        position: absolute;
        right: 0;
        top: 0;
    }

    .inside-input {
        height: 25px;
        position: absolute;
        margin-top: 10px;
        margin-right: 302px;
        opacity: 1;
        color: #3478f6;
        padding: 2px;
        font-size: 22px;
    }

    .inside-input:active {
        opacity: 0.8;
    }

    .keyboard-bottom-symbols {
        margin: 230px auto 0;
        width: 336px;
        font-size: 25px;
        color: #54585e;
    }

    .globe-symbol {
        position: absolute;
        padding-right: 20px;
    }

    .mic-symbol {
        position: relative;
        margin-right: 300px;
    }

    .home-screen-button {
        position: relative;
        margin: 15px auto 0;
        border-radius: 50px;
        width: 125px;
        height: 5px;
        background: #000;
    }

    /* Android Navigation Bar */
    .android-nav-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50px;
        background: rgba(0, 0, 0, 0.9);
        border-bottom-left-radius: 40px;
        border-bottom-right-radius: 40px;
        display: flex;
        justify-content: space-around;
        align-items: center;
        z-index: 10;
    }

    .android-nav-btn {
        width: 40px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #fff;
        font-size: 18px;
    }

    .android-nav-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: scale(1.1);
    }

    .android-nav-btn:active {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(0.95);
    }

    .android-nav-btn i {
        pointer-events: none;
    }

    @media only screen and (max-width: 600px) {
        .iphone {
            transform: scale(0.5);
            transform-origin: 0 0;
        }
    }

    @media only screen and (min-width: 600px) {
        .iphone {
            transform: scale(0.6);
            transform-origin: 0 0;
        }
    }

    @media only screen and (min-width: 768px) {
        .iphone {
            transform: scale(0.7);
            transform-origin: 0 0;
        }
    }

    @media only screen and (min-width: 992px) {
        .iphone {
            transform: scale(1.0);
            transform-origin: 0 0;
        }
    }

    @media only screen and (min-width: 1200px) {
        .iphone {
            transform: scale(1.1);
            transform-origin: 0 0;
        }
    }

    /* Loading animation for template send button */
    @keyframes rocketPulse {
        0% {
            transform: rotate(-45deg) scale(1);
            opacity: 1;
        }

        50% {
            transform: rotate(-45deg) scale(1.2);
            opacity: 0.7;
        }

        100% {
            transform: rotate(-45deg) scale(1);
            opacity: 1;
        }
    }

    .template-sending {
        animation: rocketPulse 1s ease-in-out infinite;
    }

    /* Drag and Drop Styles */
    .template-card {
        cursor: grab;
        transition: all 0.3s ease;
        position: relative;
    }

    .template-card:hover {
        transform: translateY(-2px);
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
    }

    .template-card.dragging {
        opacity: 0.8;
        transform: rotate(3deg) scale(1.05);
        cursor: grabbing;
        z-index: 1000;
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.3);
    }

    .template-card .drag-handle {
        position: absolute;
        top: 10px;
        left: 10px;
        color: #94a3b8;
        font-size: 16px;
        cursor: grab;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .template-card:hover .drag-handle {
        opacity: 1;
    }

    .template-card.dragging .drag-handle {
        cursor: grabbing;
    }

    .mobile-drop-zone {
        position: relative;
        transition: all 0.3s ease;
    }

    .mobile-drop-zone.drag-over {
        background: rgba(255, 105, 0, 0.1) !important;
        border: 3px dashed #FF6900 !important;
        transform: scale(1.02);
    }

    .mobile-drop-zone .drop-indicator {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 105, 0, 0.9);
        color: white;
        padding: 12px 20px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 600;
        pointer-events: none;
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 10;
        box-shadow: 0 4px 12px rgba(255, 105, 0, 0.3);
    }

    .mobile-drop-zone.drag-over .drop-indicator {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1.1);
    }

    .drag-preview {
        position: fixed;
        pointer-events: none;
        z-index: 9999;
        opacity: 0.9;
        transform: rotate(3deg);
        max-width: 300px;
        background: white;
        border: 2px solid #FF6900;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        font-size: 12px;
        line-height: 1.4;
    }
</style>
<div class="flex justify-between items-center mb-8">
    <h1 class="text-base font-extrabold lg:text-2xl">پیامک</h1>
    <?php if (current_user_can('manage_options')): ?>
        <a href="<?php echo admin_url('admin.php?page=sms-templates-settings'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-yekan-bold transition-colors" target="_blank">
            مدیریت قالب‌های پیامک
        </a>
    <?php endif; ?>
</div>

<div class="flex justify-between">
    <div>
        <h2 class="text-lg font-yekan-bold text-navyBlue mb-4">قالب های آماده</h2>

        <?php if (empty($sms_templates)): ?>
            <div class="text-center py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300">
                <div class="text-gray-500 mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-yekan-bold text-gray-900 mb-2">هنوز قالبی ایجاد نشده</h3>
                <p class="text-gray-600 mb-4">برای شروع، اولین قالب پیامک خود را ایجاد کنید</p>
                <?php if (current_user_can('manage_options')): ?>
                    <a href="<?php echo admin_url('admin.php?page=sms-templates-settings'); ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-yekan-bold transition-colors" target="_blank">
                        ایجاد قالب جدید
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 gap-x-12.5 gap-y-10">
                <?php foreach ($sms_templates as $template_id => $template_data) : ?>
                    <div class="mobile-form template-card flex flex-col justify-between w-[350px] h-full min-w-[356px] p-7 border border-[#E8EDF1] rounded-2.5xl bg-[#F1F5F9]"
                        style="box-shadow: 0px 2px 0px 0px #E2E8F0;"
                        draggable="true"
                        data-template-title="<?php echo esc_attr($template_data['title']); ?>"
                        data-template-content="<?php echo esc_attr($template_data['content']); ?>">
                        <div class="drag-handle">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-yekan-bold text-gray-600 mb-2"><?php echo esc_html($template_data['title']); ?></h3>
                            <p class="template-text text-base font-yekan-bold text-navyBlue leading-[38px]"><?php echo nl2br(esc_html($template_data['content'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="sticky top-10">
        <div style="display: flex; justify-content: center;">
            <div class="iphone" style="direction: rtl;">
                <div class="silence-switch outer-button"></div>
                <div class="volume-rocker-top outer-button"></div>
                <div class="volume-rocker-bottom outer-button"></div>
                <div class="power-button outer-button-reversed"></div>
                <div class="top-section">
                    <i class="arrow left"></i>
                    <div class="top-section-time" id="custom-iphone-time">--:--</div>
                    <div class="top-section-symbols">
                        <i class="fas fa-signal"></i>
                        <i class="fas fa-wifi"></i>
                        <i class="fas fa-battery-three-quarters"></i>
                    </div>
                    <div class="top-section-middle">
                        <div class="speaker">
                            <div class="front-camera"></div>
                        </div>
                        <div class="top-section-user-pic selectDisable"><i class="fas fa-user-circle" style="margin-right: -5px;"></i></div>
                        <div class="top-section-user-name">ارسال پیام</div>
                    </div>
                </div>
                <div class="messages-section" style="display: flex; flex-direction: column; justify-content: flex-end;">
                    <div class="message to" id="custom-message-placeholder">متن پیام خود را اینجا بنویسید...</div>
                </div>
                <div class="keyboard-section">
                    <?php if (!empty($can_send_custom_sms)): ?>
                        <!-- Phone Input Section - Only for authorized roles -->
                        <div class="phone-input-section" style="background: #f8f9fa; padding: 12px 20px; border-bottom: 1px solid #e0e0e0; position: relative;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="display: flex; align-items: center; background: white; border-radius: 12px; border: 2px solid #E4EBF0; padding: 8px 12px; flex: 1; transition: all 0.3s ease;" id="phone-input-container">
                                    <i class="fas fa-phone" style="color: #666; margin-left: 8px; font-size: 14px;"></i>
                                    <input
                                        id="custom-mobile-input"
                                        class="mobile-input"
                                        style="border: none; outline: none; width: 100%; font-size: 14px; font-family: inherit; direction: ltr; background: transparent;"
                                        placeholder="09xxxxxxxxx"
                                        inputmode="numeric"
                                        pattern="[0-9+]*"
                                        maxlength="14" />
                                </div>
                                <div id="phone-validation-icon" style="width: 24px; height: 24px; display: none; align-items: center; justify-content: center;">
                                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 18px;"></i>
                                </div>
                            </div>
                            <div id="phone-error-message" style="color: #ef4444; font-size: 12px; margin-top: 6px; display: none; text-align: right;"></div>
                        </div>

                        <div class="message-input-section mobile-drop-zone" style="background: white; padding: 15px 20px; flex: 1; display: flex; flex-direction: column;">
                            <div class="drop-indicator">
                                <i class="fas fa-download" style="margin-left: 8px;"></i>
                                قالب را اینجا رها کنید
                            </div>
                            <textarea
                                id="custom-message-text"
                                class="message-textarea"
                                placeholder="متن پیام خود را بنویسید یا قالبی را از سمت چپ بکشید و اینجا رها کنید..."
                                maxlength="160"
                                style="
                                width: 100%; 
                                height: 120px; 
                                border: 2px solid #E4EBF0; 
                                border-radius: 12px; 
                                padding: 12px; 
                                font-size: 14px; 
                                font-family: inherit; 
                                direction: rtl; 
                                resize: none; 
                                outline: none;
                                transition: border-color 0.3s ease;
                                background: #f8f9fa;
                            "
                                onfocus="this.style.borderColor='#FF6900'; this.style.background='white';"
                                onblur="this.style.borderColor='#E4EBF0'; this.style.background='#f8f9fa';"></textarea>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
                                <button
                                    id="send-custom-sms-btn"
                                    class="send-message-btn"
                                    style="
                                    background: rgba(255, 105, 0, 0.3);
                                    color: rgba(255, 105, 0, 0.6);
                                    border: none;
                                    border-radius: 25px;
                                    padding: 12px 20px;
                                    font-size: 14px;
                                    font-weight: 600;
                                    font-family: inherit;
                                    cursor: not-allowed;
                                    display: flex;
                                    align-items: center;
                                    gap: 8px;
                                    transition: all 0.3s ease;
                                    min-width: 120px;
                                    justify-content: center;
                                "
                                    disabled>
                                    <i class="fas fa-paper-plane" id="send-btn-icon"></i>
                                    <span id="send-btn-text">ارسال پیام</span>
                                </button>
                                <span id="custom-message-count" style="font-size: 12px; color: #888; font-weight: 500;">0/160</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Template-only section for non-authorized roles -->
                        <div class="template-only-section" style="background: white; padding: 15px 20px; flex: 1; display: flex; flex-direction: column;">
                            <!-- Phone Input for Templates -->
                            <div class="template-phone-section" style="background: #f8f9fa; padding: 12px; border-radius: 12px; border: 2px solid #E4EBF0; margin-bottom: 15px; display: none; position: relative;" id="template-phone-section">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="display: flex; align-items: center; background: white; border-radius: 8px; border: 1px solid #E4EBF0; padding: 6px 10px; flex: 1; position: relative;" id="template-phone-input-container">
                                        <i class="fas fa-phone" style="color: #666; margin-left: 8px; font-size: 12px;"></i>
                                        <input
                                            id="template-mobile-input"
                                            style="border: none; outline: none; width: 100%; font-size: 13px; font-family: inherit; direction: ltr; background: transparent;"
                                            placeholder="09xxxxxxxxx"
                                            inputmode="numeric"
                                            pattern="[0-9+]*"
                                            maxlength="14" />
                                        <div id="template-phone-error" style="color: #ef4444; font-size: 11px; display: none; position: absolute; bottom: -18px; right: 0; font-weight: 600;"></div>
                                    </div>
                                    <button
                                        id="send-template-sms-btn"
                                        style="
                                        background: rgba(255, 105, 0, 0.3);
                                        color: rgba(255, 105, 0, 0.6);
                                        border: none;
                                        border-radius: 8px;
                                        padding: 8px 16px;
                                        font-size: 12px;
                                        font-weight: 600;
                                        cursor: not-allowed;
                                        min-width: 80px;
                                        display: flex;
                                        align-items: center;
                                        gap: 6px;
                                        justify-content: center;
                                    "
                                        disabled>
                                        <i class="fas fa-paper-plane" id="template-send-btn-icon"></i>
                                        <span id="template-send-btn-text">ارسال</span>
                                    </button>
                                </div>
                            </div>

                            <div class="mobile-drop-zone" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 150px;">
                                <div class="drop-indicator">
                                    <i class="fas fa-download" style="margin-left: 8px;"></i>
                                    قالب را اینجا رها کنید
                                </div>
                                <div id="template-instructions" style="text-align: center; color: #64748b;">
                                    <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 12px; color: #94a3b8;"></i>
                                    <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600;">فقط از قالب‌های آماده استفاده کنید</h3>
                                    <p style="margin: 0; font-size: 14px; line-height: 1.5;">برای ارسال پیام، قالب مورد نظر را از سمت چپ بکشید و اینجا رها کنید</p>
                                </div>
                                <div id="template-message-preview" style="
                                width: 100%; 
                                min-height: 80px; 
                                border: 2px dashed #E4EBF0; 
                                border-radius: 12px; 
                                padding: 12px; 
                                margin-top: 20px;
                                font-size: 14px; 
                                font-family: inherit; 
                                direction: rtl; 
                                background: #f8f9fa;
                                color: #94a3b8;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                text-align: center;
                            ">متن قالب انتخابی اینجا نمایش داده می‌شود</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="keyboard-bottom-symbols">
                        <i class="fas fa-globe globe-symbol"></i>
                        <i class="fas fa-microphone mic-symbol"></i>
                    </div>
                    <div class="home-screen-button"></div>
                </div>

                <!-- Android Navigation Bar -->
                <div class="android-nav-bar">
                    <div class="android-nav-btn" id="android-back-btn" title="بازگشت">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div class="android-nav-btn" id="android-home-btn" title="پاک کردن">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="android-nav-btn" id="android-tabs-btn" title="خانه">
                        <i class="fas fa-square"></i>
                    </div>
                </div>

                <div class="iphone-shadow"></div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {

                // ساعت آیفون را از سیستم بگیر و هر 10 ثانیه آپدیت کن
                function updateIphoneTime() {
                    var now = new Date();
                    var h = now.getHours();
                    var m = now.getMinutes();
                    if (h < 10) h = '0' + h;
                    if (m < 10) m = '0' + m;
                    $('#custom-iphone-time').text(h + ':' + m);
                }
                updateIphoneTime();
                setInterval(updateIphoneTime, 10000);

                // اعتبارسنجی شماره موبایل و نمایش آیکون و خطا
                function validatePhoneInput() {
                    let $input = $('#custom-mobile-input');
                    if ($input.length === 0) return; // اگر element وجود نداشت، خروج

                    let v = $input.val();
                    if (!v) v = ''; // اگر undefined بود، string خالی قرار بده
                    v = v.replace(/[^0-9+]/g, '');
                    if (v.startsWith('+')) {
                        v = '+' + v.slice(1).replace(/[^0-9]/g, '');
                    } else {
                        v = v.replace(/[^0-9]/g, '');
                    }
                    $input.val(v);

                    let $container = $('#phone-input-container');
                    let $validationIcon = $('#phone-validation-icon');
                    let $errorMessage = $('#phone-error-message');

                    let isValidMobile = /^09\d{9}$/.test(v) ||
                        /^9\d{9}$/.test(v) ||
                        /^\+989\d{9}$/.test(v);

                    if (v.length === 0) {
                        $container.css('border-color', '#E4EBF0');
                        $validationIcon.hide();
                        $errorMessage.hide();
                    } else if (isValidMobile) {
                        $container.css('border-color', '#10b981');
                        $validationIcon.show().html('<i class="fas fa-check-circle" style="color: #10b981; font-size: 18px;"></i>');
                        $errorMessage.hide();
                    } else {
                        $container.css('border-color', '#ef4444');
                        $validationIcon.show().html('<i class="fas fa-times-circle" style="color: #ef4444; font-size: 18px;"></i>');
                        $errorMessage.show()
                            .css({
                                position: 'absolute',
                                fontWeight: '600',
                                bottom: '-10px'
                            })
                            .text('فرمت صحیح: 09xxxxxxxxx');
                    }
                    checkCustomSmsInputs();
                }

                // بروزرسانی پیش‌نمایش پیام
                function updateMessagePreview() {
                    let $messageText = $('#custom-message-text');
                    if ($messageText.length === 0) return; // اگر element وجود نداشت، خروج

                    let val = $messageText.val();
                    $('#custom-message-placeholder').text(val || 'متن پیام خود را اینجا بنویسید...');
                    checkCustomSmsInputs();
                }

                // بروزرسانی شمارنده کاراکتر
                function updateCharacterCount() {
                    let $messageText = $('#custom-message-text');
                    if ($messageText.length === 0) return; // اگر element وجود نداشت، خروج

                    let len = $messageText.val().length;
                    let $charCount = $('#custom-message-count');
                    $charCount.text(len + '/160');
                    if (len > 140) {
                        $charCount.css('color', '#ef4444');
                    } else if (len > 120) {
                        $charCount.css('color', '#f59e0b');
                    } else {
                        $charCount.css('color', '#888');
                    }
                }

                // فعال/غیرفعال کردن دکمه ارسال پیام دلخواه
                function checkCustomSmsInputs() {
                    let $messageText = $('#custom-message-text');
                    let $mobileInput = $('#custom-mobile-input');
                    let $btn = $('#send-custom-sms-btn');

                    if ($messageText.length === 0 || $mobileInput.length === 0 || $btn.length === 0) return;

                    let msg = $messageText.val().trim();
                    let mobile = $mobileInput.val().trim();
                    let isValidMobile = /^09\d{9}$/.test(mobile) ||
                        /^9\d{9}$/.test(mobile) ||
                        /^\+989\d{9}$/.test(mobile);

                    if (msg.length > 0 && isValidMobile) {
                        $btn.prop('disabled', false)
                            .css({
                                background: '#FF6900',
                                color: 'white',
                                cursor: 'pointer'
                            })
                            .attr('tabindex', '0');
                    } else {
                        $btn.prop('disabled', true)
                            .css({
                                background: 'rgba(255, 105, 0, 0.3)',
                                color: 'rgba(255, 105, 0, 0.6)',
                                cursor: 'not-allowed'
                            })
                            .attr('tabindex', '-1');
                    }
                }

                // ریست کردن فرم پیام دلخواه
                function resetSendForm() {
                    // Reset message textarea and make it editable again
                    $('#custom-message-text').val('').prop('readonly', false).css({
                        'background-color': '#f8f9fa',
                        'color': 'inherit',
                        'cursor': 'text'
                    });
                    $('#custom-mobile-input').val('');
                    $('#custom-message-placeholder').text('متن پیام خود را اینجا بنویسید...');
                    $('#custom-message-count').text('0/160').css('color', '#888');
                    $('#send-btn-icon').attr('class', 'fas fa-paper-plane').css('transform', 'none');
                    $('#send-btn-text').text('ارسال پیام');
                    $('#phone-input-container').css('border-color', '#E4EBF0');
                    $('#phone-validation-icon').hide();
                    $('#phone-error-message').hide();
                    checkCustomSmsInputs();
                }

                // نمایش toast موفقیت
                function showSuccessToast() {
                    let $toast = $(`
                    <div style="
                        position: fixed;
                        bottom: 20px;
                        left: 20px;
                        background: #10b981;
                        color: white;
                        padding: 12px 20px;
                        border-radius: 8px;
                        font-family: inherit;
                        font-size: 14px;
                        font-weight: 600;
                        z-index: 9999;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        transform: translateX(-100%);
                        transition: transform 0.3s ease;
                    ">
                        <i class="fas fa-check-circle" style="margin-left: 8px;"></i>پیام با موفقیت ارسال شد
                    </div>
                `);
                    $('body').append($toast);
                    setTimeout(() => {
                        $toast.css('transform', 'translateX(0)');
                    }, 100);
                    setTimeout(() => {
                        $toast.css('transform', 'translateX(-100%)');
                        setTimeout(() => {
                            $toast.remove();
                        }, 300);
                    }, 3000);
                }

                // نمایش toast خطا
                function showErrorToast(message) {
                    let $toast = $(`
                    <div style="
                        position: fixed;
                        bottom: 20px;
                        left: 20px;
                        background: #ef4444;
                        color: white;
                        padding: 12px 20px;
                        border-radius: 8px;
                        font-family: inherit;
                        font-size: 14px;
                        font-weight: 600;
                        z-index: 9999;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        transform: translateX(-100%);
                        transition: transform 0.3s ease;
                    ">
                        <i class="fas fa-exclamation-circle" style="margin-left: 8px;"></i>${message}
                    </div>
                `);
                    $('body').append($toast);
                    setTimeout(() => {
                        $toast.css('transform', 'translateX(0)');
                    }, 100);
                    setTimeout(() => {
                        $toast.css('transform', 'translateX(-100%)');
                        setTimeout(() => {
                            $toast.remove();
                        }, 300);
                    }, 3000);
                }

                // رویدادهای پیام دلخواه
                $('#custom-message-text').on('input', function() {
                    let val = $(this).val();
                    if (val.length > 160) {
                        $(this).val(val.substring(0, 160));
                        val = $(this).val();
                    }
                    updateMessagePreview();
                    updateCharacterCount();
                    checkCustomSmsInputs();
                });

                $('#custom-mobile-input').on('input', function() {
                    validatePhoneInput();
                });

                // فقط اعداد و + اول مجاز باشد
                $('#custom-mobile-input').on('keypress', function(e) {
                    let char = String.fromCharCode(e.which);
                    if (!/[0-9]/.test(char)) {
                        if (char === '+' && this.selectionStart === 0 && this.value.indexOf('+') === -1) {
                            return true;
                        }
                        e.preventDefault();
                        return false;
                    }
                });

                // ارسال پیام دلخواه
                $('#send-custom-sms-btn').on('click', function(e) {
                    e.preventDefault();
                    let msg = $('#custom-message-text').val().trim();
                    let mobile = $('#custom-mobile-input').val().trim().replace(/^(\+98|0098|98|0)?9/, '09');
                    let $btn = $(this);
                    if ($btn.prop('disabled')) return;

                    // حالت لودینگ
                    $('#send-btn-icon').attr('class', 'fas fa-rocket').css('transform', 'rotate(-45deg)');
                    $('#send-btn-text').text('لطفا منتظر بمانید...');

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'sms_template_send',
                            'text': msg,
                            'phone': mobile,
                        },
                        beforeSend: function() {
                            $btn.prop('disabled', true)
                                .css({
                                    cursor: 'not-allowed',
                                    background: 'rgba(255, 105, 0, 0.3)',
                                    color: 'rgba(255, 105, 0, 0.6)'
                                })
                                .attr('tabindex', '-1');
                        },
                        success: function(data) {
                            resetSendForm();
                            showSuccessToast();
                        },
                        error: function() {
                            resetSendForm();
                            showErrorToast('خطا در ارسال پیامک. دوباره تلاش کنید.');
                        }
                    });
                });

                // دکمه های ناوبری اندروید
                $('#android-back-btn').on('click', function() {
                    if (window.history.length > 1) {
                        window.history.back();
                    } else {
                        let $btn = $(this);
                        $btn.css('background', 'rgba(255, 255, 255, 0.3)');
                        setTimeout(() => {
                            $btn.css('background', '');
                        }, 200);
                    }
                });

                $('#android-home-btn').on('click', function() {
                    // Reset message textarea and make it editable again
                    $('#custom-message-text').val('').prop('readonly', false).css({
                        'background-color': '#f8f9fa',
                        'color': 'inherit',
                        'cursor': 'text'
                    });
                    $('#custom-mobile-input').val('');
                    $('#custom-message-placeholder').text('متن پیام خود را اینجا بنویسید...');
                    $('#custom-message-count').text('0/160').css('color', '#888');

                    // Reset template section for non-authorized users
                    $('#template-mobile-input').val('');
                    $('#template-phone-section').hide().removeData('template-content');
                    $('#template-message-preview').html('متن قالب انتخابی اینجا نمایش داده می‌شود').css({
                        'color': '#94a3b8',
                        'border-color': '#E4EBF0',
                        'background': '#f8f9fa'
                    });
                    $('#template-instructions').show();

                    resetSendForm();
                    let $btn = $(this);
                    $btn.css('background', 'rgba(255, 255, 255, 0.3)');
                    setTimeout(() => {
                        $btn.css('background', '');
                    }, 200);
                });

                $('#android-tabs-btn').on('click', function() {
                    if (confirm('آیا می‌خواهید به صفحه اصلی بروید؟')) {
                        window.location.href = '/';
                    }
                });

                // قالب‌های آماده حالا فقط برای drag & drop استفاده می‌شوند

                // Template SMS functionality for non-authorized users
                function validateTemplatePhoneInput() {
                    let $input = $('#template-mobile-input');
                    let v = $input.val();
                    v = v.replace(/[^0-9+]/g, '');
                    if (v.startsWith('+')) {
                        v = '+' + v.slice(1).replace(/[^0-9]/g, '');
                    } else {
                        v = v.replace(/[^0-9]/g, '');
                    }
                    $input.val(v);

                    let $container = $('#template-phone-input-container');
                    let $errorMessage = $('#template-phone-error');

                    let isValidMobile = /^09\d{9}$/.test(v) ||
                        /^9\d{9}$/.test(v) ||
                        /^\+989\d{9}$/.test(v);

                    if (v.length === 0) {
                        $container.css('border-color', '#E4EBF0');
                        $errorMessage.hide();
                    } else if (isValidMobile) {
                        $container.css('border-color', '#10b981');
                        $errorMessage.hide();
                    } else {
                        $container.css('border-color', '#ef4444');
                        $errorMessage.show()
                            .css({
                                position: 'absolute',
                                fontWeight: '600',
                                bottom: '-18px'
                            })
                            .text('فرمت صحیح: 09xxxxxxxxx');
                    }
                    checkTemplateSmsInputs();
                }

                function checkTemplateSmsInputs() {
                    let mobile = $('#template-mobile-input').val().trim();
                    let templateContent = $('#template-phone-section').data('template-content');
                    let $btn = $('#send-template-sms-btn');

                    let isValidMobile = /^09\d{9}$/.test(mobile) ||
                        /^9\d{9}$/.test(mobile) ||
                        /^\+989\d{9}$/.test(mobile);

                    if (templateContent && isValidMobile) {
                        $btn.prop('disabled', false)
                            .css({
                                background: '#FF6900',
                                color: 'white',
                                cursor: 'pointer'
                            });
                    } else {
                        $btn.prop('disabled', true)
                            .css({
                                background: 'rgba(255, 105, 0, 0.3)',
                                color: 'rgba(255, 105, 0, 0.6)',
                                cursor: 'not-allowed'
                            });
                    }
                }

                // Template phone input events
                $('#template-mobile-input').on('input', function() {
                    validateTemplatePhoneInput();
                });

                $('#template-mobile-input').on('keypress', function(e) {
                    let char = String.fromCharCode(e.which);
                    if (!/[0-9]/.test(char)) {
                        if (char === '+' && this.selectionStart === 0 && this.value.indexOf('+') === -1) {
                            return true;
                        }
                        e.preventDefault();
                        return false;
                    }
                });

                // Send template SMS
                $('#send-template-sms-btn').on('click', function(e) {
                    e.preventDefault();
                    let mobile = $('#template-mobile-input').val().trim().replace(/^(\+98|0098|98|0)?9/, '09');
                    let templateContent = $('#template-phone-section').data('template-content');
                    let $btn = $(this);

                    if ($btn.prop('disabled') || !templateContent) return;

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'sms_template_send',
                            'text': templateContent,
                            'phone': mobile,
                            'is_template': 'true'
                        },
                        beforeSend: function() {
                            $('#template-send-btn-icon').attr('class', 'fas fa-rocket template-sending').css('transform', '');
                        },
                        success: function(data) {
                            showSuccessToast();
                            // Reset template section
                            $('#template-mobile-input').val('');
                            $('#template-phone-section').hide().removeData('template-content');
                            $('#template-message-preview').html('متن قالب انتخابی اینجا نمایش داده می‌شود').css({
                                'color': '#94a3b8',
                                'border-color': '#E4EBF0',
                                'background': '#f8f9fa'
                            });
                            // Show instruction box again
                            $('#template-instructions').show();
                            $('#custom-message-placeholder').text('متن پیام خود را اینجا بنویسید...');
                            // Reset button icons
                            $('#template-send-btn-icon').attr('class', 'fas fa-paper-plane').css('transform', 'none');
                            checkTemplateSmsInputs();
                        },
                        error: function(xhr) {
                            let errorMsg = 'خطا در ارسال پیامک. دوباره تلاش کنید.';
                            if (xhr.responseJSON && xhr.responseJSON.data) {
                                errorMsg = xhr.responseJSON.data;
                            }
                            showErrorToast(errorMsg);
                            // Reset button icons
                            $('#template-send-btn-icon').attr('class', 'fas fa-paper-plane').css('transform', 'none');
                        }
                    });
                });

                // رویدادهای اولیه
                validatePhoneInput();
                updateMessagePreview();
                updateCharacterCount();
                checkCustomSmsInputs();

                // =================
                // Drag and Drop Functionality
                // =================

                let draggedTemplate = null;
                let dragPreview = null;

                // Template card drag events
                $('.template-card').on('dragstart', function(e) {
                    draggedTemplate = {
                        title: $(this).data('template-title'),
                        content: $(this).data('template-content')
                    };

                    $(this).addClass('dragging');

                    // Create custom drag preview
                    createDragPreview(draggedTemplate.content, e.originalEvent);

                    // Set drag data
                    e.originalEvent.dataTransfer.setData('text/plain', draggedTemplate.content);
                    e.originalEvent.dataTransfer.effectAllowed = 'copy';
                });

                $('.template-card').on('dragend', function(e) {
                    $(this).removeClass('dragging');
                    removeDragPreview();
                    draggedTemplate = null;
                });

                // Mobile drop zone events
                $('.mobile-drop-zone').on('dragover', function(e) {
                    e.preventDefault();
                    e.originalEvent.dataTransfer.dropEffect = 'copy';
                    $(this).addClass('drag-over');
                });

                $('.mobile-drop-zone').on('dragleave', function(e) {
                    // Only remove drag-over if we're actually leaving the drop zone
                    if (!$(this)[0].contains(e.relatedTarget)) {
                        $(this).removeClass('drag-over');
                    }
                });

                $('.mobile-drop-zone').on('drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('drag-over');

                    if (draggedTemplate) {
                        <?php if (!empty($can_send_custom_sms)): ?>
                            // For authorized users: Fill the message textarea with template content and make it readonly
                            $('#custom-message-text').val(draggedTemplate.content).prop('readonly', true).css({
                                'background-color': '#f1f5f9',
                                'color': '#64748b',
                                'cursor': 'not-allowed'
                            });
                            updateMessagePreview();
                            updateCharacterCount();
                            checkCustomSmsInputs();
                        <?php else: ?>
                            // For non-authorized users: Show template content and phone input
                            $('#template-message-preview').html(draggedTemplate.content).css({
                                'color': '#374151',
                                'border-color': '#10b981',
                                'background': 'rgba(16, 185, 129, 0.05)'
                            });
                            $('#template-phone-section').show();
                            // Hide instruction box when template is selected
                            $('#template-instructions').hide();
                            // Update mobile message preview
                            $('#custom-message-placeholder').text(draggedTemplate.content);
                            checkTemplateSmsInputs();
                            // Store template content for sending
                            $('#template-phone-section').data('template-content', draggedTemplate.content);
                        <?php endif; ?>

                        // Show success animation
                        showTemplateDropSuccess(draggedTemplate.title);
                    }
                });

                // Create drag preview element
                function createDragPreview(content, event) {
                    removeDragPreview(); // Remove any existing preview

                    dragPreview = $('<div class="drag-preview"></div>');
                    dragPreview.html('<div style="font-weight: 600; margin-bottom: 8px; color: #FF6900;">قالب پیامک</div>' +
                        '<div style="color: #64748b;">' + content.substring(0, 100) + (content.length > 100 ? '...' : '') + '</div>');

                    $('body').append(dragPreview);

                    // Position the preview
                    updateDragPreviewPosition(event);
                }

                // Update drag preview position
                function updateDragPreviewPosition(event) {
                    if (dragPreview) {
                        dragPreview.css({
                            left: event.clientX + 15 + 'px',
                            top: event.clientY - 30 + 'px'
                        });
                    }
                }

                // Remove drag preview
                function removeDragPreview() {
                    if (dragPreview) {
                        dragPreview.remove();
                        dragPreview = null;
                    }
                }

                // Track mouse movement for drag preview
                $(document).on('dragover', function(e) {
                    if (dragPreview) {
                        updateDragPreviewPosition(e.originalEvent);
                    }
                });

                // Show template drop success animation
                function showTemplateDropSuccess(templateTitle) {
                    let $successToast = $(`
                        <div style="
                            position: fixed;
                            bottom: 20px;
                            right: 20px;
                            background: #10b981;
                            color: white;
                            padding: 12px 20px;
                            border-radius: 8px;
                            font-family: inherit;
                            font-size: 14px;
                            font-weight: 600;
                            z-index: 9999;
                            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                            transform: translateX(100%);
                            transition: transform 0.3s ease;
                            direction: rtl;
                        ">
                            <i class="fas fa-check-circle" style="margin-right: 8px;"></i>قالب "${templateTitle}" با موفقیت اضافه شد
                        </div>
                    `);

                    $('body').append($successToast);

                    setTimeout(() => {
                        $successToast.css('transform', 'translateX(0)');
                    }, 100);

                    setTimeout(() => {
                        $successToast.css('transform', 'translateX(100%)');
                        setTimeout(() => {
                            $successToast.remove();
                        }, 300);
                    }, 3000);

                    // Add subtle animation to the message preview
                    let $messagePreview = $('#custom-message-placeholder');
                    $messagePreview.css({
                        'animation': 'pulse 0.5s ease-in-out',
                        'background': 'rgba(16, 185, 129, 0.1)'
                    });

                    setTimeout(() => {
                        $messagePreview.css({
                            'animation': '',
                            'background': ''
                        });
                    }, 500);
                }

                // Add CSS animation for pulse effect
                if (!$('#drag-drop-styles').length) {
                    $('<style id="drag-drop-styles">@keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }</style>').appendTo('head');
                }

            });
        </script>
<?php
// اضافه کردن meta tags nofollow و noindex
add_action('wp_head', function() {
    echo '<meta name="robots" content="noindex, nofollow">' . "\n";
}, 1);

// Enqueue کردن فایل qrcode.js
add_action('wp_enqueue_scripts', function() {
    if (is_page_template('page-kheiri2.php')) {
        wp_enqueue_script('qrcode-js', ez_theme_theme_uri('assets/js/lib/qrcode/qrcode.js'), [], '1.0', true);
    }
}, 20);

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .kheiri2-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .auth-form {
            text-align: center;
        }

        .auth-form h2 {
            margin-bottom: 30px;
            color: #333;
            font-size: 24px;
        }

        .auth-form input {
            width: 100%;
            max-width: 300px;
            padding: 15px;
            margin: 10px auto;
            border: 2px solid #ddd;
            border-radius: 8px;
            display: block;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .auth-form input:focus {
            outline: none;
            border-color: #fd7013;
        }

        .auth-form button {
            padding: 15px 40px;
            background: #fd7013;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 15px;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .auth-form button:hover {
            background: #e55f0f;
        }

        .qr-generator {
            display: none;
        }

        .qr-generator.active {
            display: block;
        }

        .qr-generator h2 {
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
            text-align: center;
        }

        .qr-input-section {
            margin-bottom: 30px;
        }

        .qr-input-section textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            min-height: 120px;
            font-family: inherit;
            font-size: 16px;
            resize: vertical;
            transition: border-color 0.3s;
        }

        .qr-input-section textarea:focus {
            outline: none;
            border-color: #fd7013;
        }

        .qr-input-section button {
            padding: 15px 40px;
            background: #fd7013;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 15px;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .qr-input-section button:hover {
            background: #e55f0f;
        }

        .error-message {
            color: #d32f2f;
            margin-top: 15px;
            padding: 12px;
            background: #ffebee;
            border-radius: 8px;
            text-align: center;
        }

        /* Lightbox Styles */
        .qr-lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .qr-lightbox.active {
            display: flex;
        }

        .qr-lightbox-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 512px;
            height: 512px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .qr-lightbox-close {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #f44336;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .qr-lightbox-close:hover {
            background: #d32f2f;
        }

        .qr-lightbox canvas {
            max-width: 100%;
            max-height: 100%;
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 10px;
            background: white;
        }

        .qr-lightbox-download {
            margin-top: 20px;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .qr-lightbox-download:hover {
            background: #218838;
        }

        /* Shortlink Styles */
        .shortlink-section {
            margin-top: 40px;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 15px;
            border: 2px solid #e0e0e0;
        }

        .shortlink-section h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
            text-align: center;
        }

        .shortlink-input-group {
            margin-bottom: 15px;
        }

        .shortlink-input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }

        .shortlink-input-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .shortlink-input-group input:focus {
            outline: none;
            border-color: #fd7013;
        }

        .shortlink-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .shortlink-actions button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-create-shortlink {
            background: #fd7013;
            color: white;
        }

        .btn-create-shortlink:hover {
            background: #e55f0f;
        }

        .btn-copy-shortlink {
            background: #2196F3;
            color: white;
            display: none;
        }

        .btn-copy-shortlink:hover {
            background: #1976D2;
        }

        .btn-convert-to-qr {
            background: #9C27B0;
            color: white;
            display: none;
        }

        .btn-convert-to-qr:hover {
            background: #7B1FA2;
        }

        .shortlink-result {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 8px;
            border: 2px solid #4caf50;
            display: none;
        }

        .shortlink-result.show {
            display: block;
        }

        .shortlink-result input {
            width: 100%;
            padding: 10px;
            border: 1px solid #4caf50;
            border-radius: 5px;
            background: white;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .shortlink-loading {
            text-align: center;
            padding: 20px;
            color: #666;
            display: none;
        }

        .shortlink-loading.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="kheiri2-container">
        <!-- فرم احراز هویت -->
        <div class="auth-form" id="authForm">
            <h2>ورود به سیستم QR Code</h2>
            <form id="loginForm">
                <input type="text" id="username" placeholder="نام کاربری" required autocomplete="username">
                <input type="password" id="password" placeholder="رمز عبور" required autocomplete="current-password">
                <button type="submit">ورود</button>
                <div id="authError" class="error-message" style="display: none;"></div>
            </form>
        </div>

        <!-- بخش تولید QR Code -->
        <div class="qr-generator" id="qrGenerator">
            <h2>تولید QR Code</h2>
            <div class="qr-input-section">
                <textarea id="qrContent" placeholder="لینک یا محتوای متنی را وارد کنید..."></textarea>
                <button type="button" id="generateQrBtn">تولید QR Code</button>
            </div>

            <!-- بخش ساخت Shortlink -->
            <div class="shortlink-section">
                <h3>ساخت لینک کوتاه</h3>
                <div class="shortlink-input-group">
                    <label for="originalLink">لینک اصلی:</label>
                    <input type="url" id="originalLink" placeholder="https://example.com/very-long-url...">
                </div>
                <div class="shortlink-actions">
                    <button type="button" id="createShortlinkBtn" class="btn-create-shortlink">ساخت لینک کوتاه</button>
                    <button type="button" id="copyShortlinkBtn" class="btn-copy-shortlink">کپی لینک کوتاه</button>
                    <button type="button" id="convertToQrBtn" class="btn-convert-to-qr">تبدیل به QR Code</button>
                </div>
                <div class="shortlink-loading" id="shortlinkLoading">
                    در حال ساخت لینک کوتاه...
                </div>
                <div class="shortlink-result" id="shortlinkResult">
                    <label>لینک کوتاه ساخته شده:</label>
                    <input type="text" id="shortlinkOutput" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox برای نمایش QR Code -->
    <div class="qr-lightbox" id="qrLightbox">
        <div class="qr-lightbox-content">
            <button class="qr-lightbox-close" id="closeLightbox">×</button>
            <canvas id="qrcode"></canvas>
            <button class="qr-lightbox-download" id="downloadBtn">دانلود QR Code</button>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        const authForm = $('#authForm');
        const qrGenerator = $('#qrGenerator');
        const loginForm = $('#loginForm');
        const authError = $('#authError');
        const generateQrBtn = $('#generateQrBtn');
        const qrContent = $('#qrContent');
        const qrLightbox = $('#qrLightbox');
        const qrcodeCanvas = document.getElementById('qrcode');
        const downloadBtn = $('#downloadBtn');
        const closeLightbox = $('#closeLightbox');
        
        // Shortlink elements
        const originalLink = $('#originalLink');
        const createShortlinkBtn = $('#createShortlinkBtn');
        const copyShortlinkBtn = $('#copyShortlinkBtn');
        const convertToQrBtn = $('#convertToQrBtn');
        const shortlinkResult = $('#shortlinkResult');
        const shortlinkOutput = $('#shortlinkOutput');
        const shortlinkLoading = $('#shortlinkLoading');
        
        const ajaxUrl = "<?php echo esc_url_raw( get_template_directory_uri() . '/inc/http/func/kheiri2-ajax.php' ); ?>";

        // بررسی اینکه آیا کاربر قبلاً وارد شده است
        if (sessionStorage.getItem('kheiri2_authenticated') === 'true') {
            authForm.hide();
            qrGenerator.addClass('active');
        }

        // مدیریت فرم ورود
        loginForm.on('submit', function(e) {
            e.preventDefault();
            
            const username = $('#username').val();
            const password = $('#password').val();

            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                dataType: 'json',
                data: {
                    'action': 'auth',
                    'username': username,
                    'password': password
                },
                success: function(response) {
                    if (response && response.success) {
                        sessionStorage.setItem('kheiri2_authenticated', 'true');
                        authForm.hide();
                        qrGenerator.addClass('active');
                        authError.hide();
                    } else {
                        // ریدایرکت به صفحه اصلی در صورت خطا
                        window.location.href = "<?php echo home_url(); ?>";
                    }
                },
                error: function() {
                    // ریدایرکت به صفحه اصلی در صورت خطا
                    window.location.href = "<?php echo home_url(); ?>";
                }
            });
        });

        // تابع برای استفاده از API خارجی
        function generateQRWithAPI(content) {
            const apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=512x512&data=' + encodeURIComponent(content);
            
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                // تنظیم اندازه canvas
                qrcodeCanvas.width = 512;
                qrcodeCanvas.height = 512;
                
                const ctx = qrcodeCanvas.getContext('2d');
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, 512, 512);
                
                // کشیدن QR Code در وسط canvas
                const scale = Math.min(450 / img.width, 450 / img.height);
                const scaledWidth = img.width * scale;
                const scaledHeight = img.height * scale;
                const x = (512 - scaledWidth) / 2;
                const y = (512 - scaledHeight) / 2;
                
                ctx.drawImage(img, x, y, scaledWidth, scaledHeight);
                
                // نمایش lightbox
                qrLightbox.addClass('active');
            };
            img.onerror = function() {
                alert('خطا در تولید QR Code: لینک شما خیلی طولانی است. لطفاً از لینک کوتاه‌تر استفاده کنید.');
            };
            img.src = apiUrl;
        }

        // تولید QR Code
        generateQrBtn.on('click', function() {
            const content = qrContent.val().trim();
            
            if (!content) {
                alert('لطفاً محتوا را وارد کنید');
                return;
            }

            // برای لینک‌های خیلی طولانی، مستقیماً از API استفاده می‌کنیم
            if (content.length > 1200) {
                generateQRWithAPI(content);
                return;
            }

            // پاک کردن QR Code قبلی
            const qrContainer = document.createElement('div');
            qrContainer.style.display = 'none';
            document.body.appendChild(qrContainer);

            // تولید QR Code جدید
            let qr = null;
            let qrError = false;
            
            try {
                // محاسبه typeNumber بر اساس طول محتوا
                let typeNumber = 4; // پیش‌فرض
                const contentLength = content.length;
                
                // محاسبه typeNumber مناسب بر اساس طول محتوا
                if (contentLength > 1000) {
                    typeNumber = 40; // حداکثر ظرفیت
                } else if (contentLength > 800) {
                    typeNumber = 30;
                } else if (contentLength > 600) {
                    typeNumber = 20;
                } else if (contentLength > 400) {
                    typeNumber = 15;
                } else if (contentLength > 200) {
                    typeNumber = 10;
                } else if (contentLength > 100) {
                    typeNumber = 6;
                }
                
                qr = new QRCode(qrContainer, {
                    text: content,
                    width: 450,
                    height: 450,
                    typeNumber: typeNumber,
                    colorDark: '#000000',
                    colorLight: '#FFFFFF',
                    correctLevel: QRCode.CorrectLevel.L // استفاده از L برای ظرفیت بیشتر
                });

                // پیدا کردن canvas یا img که QRCode ساخته
                setTimeout(function() {
                    // بررسی خطا در console
                    const qrImg = qrContainer.querySelector('img') || qrContainer.querySelector('canvas') || qrContainer.querySelector('table');
                    
                    if (!qrImg || qrContainer.innerHTML.trim() === '') {
                        // اگر QR Code ساخته نشد، از API استفاده می‌کنیم
                        if (qrContainer.parentNode) {
                            document.body.removeChild(qrContainer);
                        }
                        generateQRWithAPI(content);
                        return;
                    }
                    
                    // تنظیم اندازه canvas
                    qrcodeCanvas.width = 512;
                    qrcodeCanvas.height = 512;
                    
                    const ctx = qrcodeCanvas.getContext('2d');
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, 512, 512);
                    
                    // کشیدن QR Code در وسط canvas
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    
                    if (qrImg.tagName === 'CANVAS') {
                        img.src = qrImg.toDataURL();
                    } else if (qrImg.tagName === 'IMG') {
                        img.src = qrImg.src;
                    } else if (qrImg.tagName === 'TABLE') {
                        // اگر table است، QRCode خودش canvas یا img می‌سازد
                        const generatedImg = qrContainer.querySelector('img') || qrContainer.querySelector('canvas');
                        if (generatedImg) {
                            if (generatedImg.tagName === 'CANVAS') {
                                img.src = generatedImg.toDataURL();
                            } else {
                                img.src = generatedImg.src;
                            }
                        } else {
                            // اگر نتوانست تولید کند، از API استفاده می‌کنیم
                            if (qrContainer.parentNode) {
                                document.body.removeChild(qrContainer);
                            }
                            generateQRWithAPI(content);
                            return;
                        }
                    } else {
                        // فرمت نامعتبر، از API استفاده می‌کنیم
                        if (qrContainer.parentNode) {
                            document.body.removeChild(qrContainer);
                        }
                        generateQRWithAPI(content);
                        return;
                    }
                    
                    img.onload = function() {
                        // محاسبه موقعیت برای قرار دادن در وسط
                        const scale = Math.min(450 / img.width, 450 / img.height);
                        const scaledWidth = img.width * scale;
                        const scaledHeight = img.height * scale;
                        const x = (512 - scaledWidth) / 2;
                        const y = (512 - scaledHeight) / 2;
                        
                        ctx.drawImage(img, x, y, scaledWidth, scaledHeight);
                        
                        // نمایش lightbox
                        qrLightbox.addClass('active');
                        
                        // حذف container موقت
                        if (qrContainer.parentNode) {
                            document.body.removeChild(qrContainer);
                        }
                    };
                    
                    img.onerror = function() {
                        // اگر خطا در بارگذاری داشت، از API استفاده می‌کنیم
                        if (qrContainer.parentNode) {
                            document.body.removeChild(qrContainer);
                        }
                        generateQRWithAPI(content);
                    };
                }, 300);
            } catch (error) {
                // اگر خطای overflow یا هر خطای دیگری داشت، از API خارجی استفاده می‌کنیم
                if (qrContainer.parentNode) {
                    document.body.removeChild(qrContainer);
                }
                
                if (error.message && error.message.includes('overflow')) {
                    generateQRWithAPI(content);
                } else {
                    // برای سایر خطاها هم از API استفاده می‌کنیم
                    generateQRWithAPI(content);
                }
            }
        });

        // بستن lightbox
        closeLightbox.on('click', function() {
            qrLightbox.removeClass('active');
        });

        // بستن lightbox با کلیک روی پس‌زمینه
        qrLightbox.on('click', function(e) {
            if (e.target === qrLightbox[0]) {
                qrLightbox.removeClass('active');
            }
        });

        // دانلود QR Code
        downloadBtn.on('click', function() {
            const link = document.createElement('a');
            link.download = 'qrcode-' + Date.now() + '.png';
            link.href = qrcodeCanvas.toDataURL('image/png');
            link.click();
        });

        // ساخت Shortlink
        createShortlinkBtn.on('click', function() {
            const originalUrl = originalLink.val().trim();
            
            if (!originalUrl) {
                alert('لطفاً لینک اصلی را وارد کنید');
                return;
            }

            // اعتبارسنجی URL
            try {
                new URL(originalUrl);
            } catch (e) {
                alert('لطفاً یک لینک معتبر وارد کنید');
                return;
            }

            // نمایش loading
            shortlinkLoading.addClass('show');
            shortlinkResult.removeClass('show');
            createShortlinkBtn.prop('disabled', true);

            // فراخوانی API
            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                dataType: 'json',
                data: {
                    'action': 'create_shortlink',
                    'original_link': originalUrl,
                    'type': 'custom'
                },
                success: function(response) {
                    shortlinkLoading.removeClass('show');
                    createShortlinkBtn.prop('disabled', false);

                    if (response && response.success && response.data && response.data.shortlink) {
                        shortlinkOutput.val(response.data.shortlink);
                        shortlinkResult.addClass('show');
                        copyShortlinkBtn.show();
                        convertToQrBtn.show();
                    } else {
                        alert('خطا در ساخت لینک کوتاه: ' + (response.data || 'خطای نامشخص'));
                    }
                },
                error: function(xhr, status, error) {
                    shortlinkLoading.removeClass('show');
                    createShortlinkBtn.prop('disabled', false);
                    alert('خطا در ارتباط با سرور: ' + error);
                }
            });
        });

        // کپی لینک کوتاه
        copyShortlinkBtn.on('click', function() {
            const shortlink = shortlinkOutput.val();
            if (shortlink) {
                navigator.clipboard.writeText(shortlink).then(function() {
                    copyShortlinkBtn.text('کپی شد!');
                    setTimeout(function() {
                        copyShortlinkBtn.text('کپی لینک کوتاه');
                    }, 2000);
                }).catch(function() {
                    // Fallback برای مرورگرهای قدیمی
                    shortlinkOutput.select();
                    document.execCommand('copy');
                    copyShortlinkBtn.text('کپی شد!');
                    setTimeout(function() {
                        copyShortlinkBtn.text('کپی لینک کوتاه');
                    }, 2000);
                });
            }
        });

        // تبدیل به QR Code
        convertToQrBtn.on('click', function() {
            const shortlink = shortlinkOutput.val();
            if (shortlink) {
                // قرار دادن لینک کوتاه در باکس QR Code
                qrContent.val(shortlink);
                
                // خودکار کلیک روی دکمه تولید QR Code
                setTimeout(function() {
                    generateQrBtn.click();
                }, 100);
            }
        });
    });
    </script>

    <?php wp_footer(); ?>
</body>
</html>

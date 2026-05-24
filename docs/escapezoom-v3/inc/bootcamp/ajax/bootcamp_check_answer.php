<?php
// Security check - skip if called from bootcamp_rest_api.php
if (!defined('BOOTCAMP_SKIP_SECURITY_CHECK')) {
    // Security check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'v2-ajax-nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }
}

// Only process puzzle logic if not called from bootcamp_rest_api.php
if (!defined('BOOTCAMP_SKIP_SECURITY_CHECK')) {
    $puzzle_num = $_POST['puzzle_num'] ?? 0;
    if ($puzzle_num == '4Final' || $puzzle_num == 'bootcamp_form') {
        // Handle string puzzle number
    } else {
        $puzzle_num = intval($puzzle_num);
    }

    $answer = sanitize_text_field($_POST['answer'] ?? '');

    // Handle bootcamp form submission
    if ($puzzle_num == 'bootcamp_form') {
    $name = sanitize_text_field($_POST['name'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $student_id = sanitize_text_field($_POST['student_id'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    
    if (empty($name) || empty($phone)) {
        wp_send_json_error(['message' => 'لطفا تمام فیلدها را پر کنید']);
        exit;
    }
    
    // Split name into first and last name
    $name_parts = explode(' ', trim($name), 2);
    $first_name = $name_parts[0] ?? '';
    $last_name = $name_parts[1] ?? '';
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bootcamp';
    
    // Check if phone already exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE phone = %s",
        $phone
    ));
    
    if ($existing) {
        wp_send_json_error(['message' => 'این شماره تماس قبلا ثبت شده است']);
        exit;
    }
    
    // Insert into database
    $result = $wpdb->insert(
        $table_name,
        [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'duration' => $duration,
            'created_at' => current_time('mysql')
        ],
        ['%s', '%s', '%s', '%d', '%s']
    );
    
    if ($result === false) {
        wp_send_json_error(['message' => 'خطا در ثبت اطلاعات']);
        exit;
    }
    
    wp_send_json_success(['message' => 'اطلاعات با موفقیت ثبت شد']);
    exit;
    }
}

// Only process puzzle logic if not called from bootcamp_rest_api.php
if (!defined('BOOTCAMP_SKIP_SECURITY_CHECK')) {
    if (empty($puzzle_num)) {
        wp_send_json_error(['message' => 'داده‌های ناقص']);
        exit;
    }

    // For puzzle 4, answer is optional (we check boxes instead)
    if ($puzzle_num != 4 && empty($answer)) {
        wp_send_json_error(['message' => 'داده‌های ناقص']);
        exit;
    }

    // Define correct answers
    $correct_answers = [
        1 => 'بازی',
        2 => 'رزرو',
        3 => 'خاطره سازی',
        4 => ['رزرو', 'بازی', 'خاطره سازی'], // Order: box1, box2, box3
        '4Final' => 'المپیک',
    ];

    // Check answer
    $is_correct = false;

    if ($puzzle_num == 4) {
        // For puzzle 4, we need to check 3 boxes
        $box1 = trim(sanitize_text_field($_POST['box1'] ?? ''));
        $box2 = trim(sanitize_text_field($_POST['box2'] ?? ''));
        $box3 = trim(sanitize_text_field($_POST['box3'] ?? ''));
        
        if ($box1 === $correct_answers[4][0] && 
            $box2 === $correct_answers[4][1] && 
            $box3 === $correct_answers[4][2]) {
            $is_correct = true;
        }
    } else if ($puzzle_num == '4Final') {
        $is_correct = (trim($answer) === $correct_answers['4Final']);
    } else {
        // Trim answer for all puzzles, especially important for puzzle 3 (mobile input)
        $is_correct = (trim($answer) === $correct_answers[$puzzle_num]);
    }

    if ($is_correct) {
        // Return next puzzle HTML based on solved puzzle
        $next_html = '';
        
        if ($puzzle_num == 1) {
            // Return puzzle 2 HTML
            $next_html = get_puzzle_2_html();
        } else if ($puzzle_num == 2) {
            // Return puzzle 3 HTML
            $next_html = get_puzzle_3_html();
        } else if ($puzzle_num == 3) {
            // Return both success message and puzzle 4 HTML in the same response
            $next_html = get_puzzle_3_success_html();
            $puzzle_4_html = get_puzzle_4_html();
        }
        
        $response_data = [
            'message' => 'جواب صحیح است',
            'next_html' => $next_html
        ];
        
        // Always include puzzle 4 HTML when puzzle 3 is solved
        if ($puzzle_num == 3) {
            $response_data['puzzle_4_html'] = $puzzle_4_html;
        }
        
        wp_send_json_success($response_data);
    } else {
        wp_send_json_error(['message' => 'جواب اشتباه است']);
    }
}

// Only exit if not called from bootcamp_rest_api.php
if (!defined('BOOTCAMP_SKIP_SECURITY_CHECK')) {
    exit;
}

// Function to get puzzle 2 HTML
function get_puzzle_2_html() {
    $theme_asset_url = get_template_directory_uri() . '/assets/';
    
    return '
    <div id="puzzle-2" class="puzzle-section mt-8 max-lg:mb-d30">
        <div class="flex flex-col lg:justify-center relative w-full h-full rounded-4xl bg-surface-sunken mt-d82 pt-8 pb-11 px-5">
            <div class="absolute right-7 -top-d30">
                <svg width="31" height="44" viewBox="0 0 31 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.7114 26.1795C17.3848 26.1795 17.1048 26.1795 16.8714 26.1795C16.6381 26.1795 16.4048 26.1329 16.1714 26.0395V43.1195H3.50145V26.1795C3.50145 23.9862 3.43145 21.9795 3.29145 20.1595C3.15145 18.2929 2.91811 16.4962 2.59145 14.7695C2.31145 13.0429 1.93811 11.3395 1.47145 9.65953C1.05145 7.93286 0.561445 6.11286 0.00144537 4.19953L12.3214 -0.000472188L15.0514 11.5495C15.2381 12.4362 15.6348 12.8795 16.2414 12.8795C16.9414 12.8795 17.2914 12.4362 17.2914 11.5495C17.2914 11.3162 17.1748 10.4529 16.9414 8.95953C16.7548 7.41953 16.3814 5.20286 15.8214 2.30953L28.4914 -0.000472188C29.0981 3.07953 29.4948 5.69286 29.6814 7.83953C29.9148 9.9862 30.0314 11.7362 30.0314 13.0895C30.0314 14.9095 29.7514 16.6362 29.1914 18.2695C28.6314 19.8562 27.8148 21.2329 26.7414 22.3995C25.7148 23.5662 24.4314 24.4995 22.8914 25.1995C21.3514 25.8529 19.6248 26.1795 17.7114 26.1795Z" fill="url(#paint0_linear_58135_5467)"/>
                    <defs>
                        <linearGradient id="paint0_linear_58135_5467" x1="15.3984" y1="3.02953" x2="15.3984" y2="74.5295" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FD7013"/>
                            <stop offset="1" stop-color="#FD7013" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <h3 class="text-center text-base lg:text-xl font-bold lg:mt-9">ابتدای راه مثل یه کلید جادویی برات عمل می‌کنه و انتهای راه رو برات هموار می کنه.
                <br />
                با نگاه دقیق به جدول زیر، نقطۀ شروع این ماجراجویی رو پیدا کن!
            </h3>

            <div class="flex justify-center items-center gap-5 mt-7">
                <div class="lg:hidden max-lg:flex flex-col justify-between items-center h-d235">
                    <p class="text-2xl font-bold h-12 flex items-center text-danger-hot">1</p>
                    <p class="text-2xl font-bold h-12 flex items-center text-danger-hot">2</p>
                    <p class="text-2xl font-bold h-12 flex items-center text-danger-hot">3</p>
                    <p class="text-2xl font-bold h-12 flex items-center text-danger-hot">4</p>
                </div>
                 
                <div class="grid grid-cols-4 gap-px text-white child:text-center child:flex child:items-center child:justify-center child:text-2xl child:font-bold">
                    <div class="w-16 h-16 bg-danger-hot rounded-tr-3xl">ر</div>
                    <div class="w-16 h-16 bg-danger-hot">ق</div>
                    <div class="w-16 h-16 bg-danger-hot">ک</div>
                    <div class="w-16 h-16 bg-danger-hot rounded-tl-3xl">ف</div>
                    <div class="w-16 h-16 bg-danger-hot">ش</div>
                    <div class="w-16 h-16 bg-danger-hot">ف</div>
                    <div class="w-16 h-16 bg-danger-hot">ر</div>
                    <div class="w-16 h-16 bg-danger-hot">ق</div>
                    <div class="w-16 h-16 bg-danger-hot">ک</div>
                    <div class="w-16 h-16 bg-danger-hot">گ</div>
                    <div class="w-16 h-16 bg-danger-hot">ل</div>
                    <div class="w-16 h-16 bg-danger-hot">و</div>
                    <div class="w-16 h-16 bg-danger-hot rounded-br-3xl">ل</div>
                    <div class="w-16 h-16 bg-danger-hot">ز</div>
                    <div class="w-16 h-16 bg-danger-hot">ش</div>
                    <div class="w-16 h-16 bg-danger-hot rounded-bl-3xl">ک</div>
                </div>

                <div class="flex flex-col max-lg:hidden">
                    <p class="text-lg font-medium text-steel">حروفی که در ردیف‌های دیگر صحیح نبودند، در این ردیف هم صحیح نیستند.</p>
                    <hr class="border-t border-slate-120 my-4" />
                    <p class="text-lg font-medium text-steel">حرفی که با بقیه متفاوت است، حرف صحیح است.</p>
                    <hr class="border-t border-slate-120 my-4" />
                    <p class="text-lg font-medium text-steel">حروفی که ترتیب را رعایت کرده‌اند، حروف صحیحی نیستند.</p>
                    <hr class="border-t border-slate-120 my-4" />
                    <p class="text-lg font-medium text-steel">تنها حرف خط نخورده در جدول، حرف صحیح است.</p>
                </div>
            </div>

            <div class="lg:hidden max-lg:flex flex-col items-start mx-auto gap-5 mt-8">
                <div class="flex items-center gap-6">
                    <div class="rounded-full shrink-0 bg-steel text-center text-white text-base font-bold w-d18 h-d18 flex items-center justify-center">1</div>
                    <p class="text-sm font-bold text-steel">حروفی که در ردیف‌های دیگر صحیح نبودند، در این ردیف هم صحیح نیستند.</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="rounded-full shrink-0 bg-steel text-center text-white text-base font-bold w-d18 h-d18 flex items-center justify-center">2</div>
                    <p class="text-sm font-bold text-steel">کلمه‌ای که با بقیه متفاوت است، کلمۀ صحیح است.</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="rounded-full shrink-0 bg-steel text-center text-white text-base font-bold w-d18 h-d18 flex items-center justify-center">3</div>
                    <p class="text-sm font-bold text-steel">حروفی که ترتیب را رعایت کرده‌اند، حروف صحیحی نیستند.</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="rounded-full shrink-0 bg-steel text-center text-white text-base font-bold w-d18 h-d18 flex items-center justify-center">4</div>
                    <p class="text-sm font-bold text-steel">تنها حرف منحصربه‌فرد در جدول، کلمۀ صحیح است.</p>
                </div>
            </div>

            <div class="flex lg:justify-center max-lg:flex-col items-center lg:gap-6 lg:mt-d58">
                <p class="text-steel text-center max-lg:mt-d78 text-base font-bold max-lg:mb-3">کلمه ای که پیدا کردی رو وارد کن.</p>

                <div class="flex items-center justify-center gap-4 lg:mt-3">
                    <div class="flex justify-center items-center z-1" id="puzzle-2-inputs">
                        <input type="text" maxlength="1" class="puzzle-2-input w-d38 lg:w-12 h-d38 lg:h-12 border border-slate-120 outline-none pr-4 text-lg rounded-tr-lg rounded-br-lg" data-index="0" />
                        <input type="text" maxlength="1" class="puzzle-2-input w-d38 lg:w-12 h-d38 lg:h-12 border border-slate-120 outline-none pr-4 text-lg" data-index="1" />
                        <input type="text" maxlength="1" class="puzzle-2-input w-d38 lg:w-12 h-d38 lg:h-12 border border-slate-120 outline-none pr-4 text-lg" data-index="2" />
                        <input type="text" maxlength="1" class="puzzle-2-input w-d38 lg:w-12 h-d38 lg:h-12 border border-slate-120 outline-none pr-4 text-lg rounded-bl-lg rounded-tl-lg" data-index="3" />
                    </div>

                    <button id="submit-puzzle-2" class="cursor-pointer z-1 w-d38 h-d38 lg:w-12 lg:h-12 rounded-lg bg-focus-blue flex items-center justify-center transition-all hover:bg-blue disabled:opacity-50 disabled:cursor-not-allowed relative">
                        <svg id="puzzle-2-check" width="18" height="13" viewBox="0 0 18 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-white absolute">
                            <path d="M15.9961 2L7.35366 10.6286C7.15835 10.8236 6.84199 10.8234 6.64684 10.6283L2.00011 5.98155" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                        <svg id="puzzle-2-spinner" class="animate-spin h-5 w-5 text-white hidden absolute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="puzzle-2-error" class="absolute top-full right-0 mt-2 text-red-600 text-sm font-medium hidden">کلمه اشتباه است</div>
        </div>
    </div>
    ';
}

// Function to get puzzle 3 HTML
function get_puzzle_3_html() {
    $theme_asset_url = get_template_directory_uri() . '/assets/';
    
    return '
    <div id="puzzle-3" class="puzzle-section mt-8 max-lg:mb-d30">
        <div class="flex flex-col justify-center relative w-full h-full max-lg:h-d344 rounded-4xl bg-surface-sunken mt-d82 px-5 py-8">
            <div class="absolute right-4 lg:right-7 -top-d30">
                <p class="text-70 font-black leading-70 text-transparent bg-clip-text bg-gradient-to-b from-primary-2 to-primary-2/10">3</p>
            </div>

            <h3 class="text-center text-base lg:text-xl font-bold">
                ابتدای راه مثل یه کلید جادویی برات عمل می‌کنه و انتهای راه رو برات هموار می حالا وقتش رسیده انتهای راه رو طی کنی...
                <br />
                کلمۀ سوم، در جایی پنهان شده که ما خودمون رو دقیقا با سه واژه توصیف کرده‌ایم. اون توصیف رو پیدا کن، تا راهت کامل بشه!
            </h3>

            <p class="text-steel text-center mt-2 mb-3">کلمه ای که پیدا کردی رو وارد کن.</p>

            <!-- Single input for both desktop and mobile -->
            <div class="flex justify-center items-center w-full z-1 gap-4">
                <input type="text" id="puzzle-3-input" class="puzzle-3-input w-full max-w-d200 lg:max-w-d300 h-12 border border-slate-120 outline-none pr-4 text-lg rounded-lg" placeholder="کلمه را وارد کنید" />

                <button id="submit-puzzle-3" class="cursor-pointer z-1 w-12 h-12 bg-focus-blue rounded-lg flex items-center justify-center transition-all hover:bg-blue disabled:opacity-50 disabled:cursor-not-allowed relative">
                    <svg id="puzzle-3-check" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-white absolute">
                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg id="puzzle-3-spinner" class="animate-spin h-5 w-5 text-white hidden absolute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>

            <div id="puzzle-3-error" class="absolute bottom-2 right-1/2 translate-x-1/2 mt-2 text-red-600 text-sm font-medium hidden">کلمه اشتباه است</div>
        </div>
    </div>
    ';
}

// Function to get puzzle 3 success HTML
function get_puzzle_3_success_html() {
    return '
    <div id="puzzle-3-success" class="my-8 relative max-lg:mb-d30">
        <div class="text-center relative py-16">
            <div id="success-shapes-container" class="absolute inset-0 pointer-events-none"></div>
            <h2 id="success-text" class="text-4xl lg:text-6xl font-black text-primary-2 relative z-10 inline-block" style="opacity: 0; transform: scale(0.8);">رزرو، بازی، خاطره سازی</h2>
        </div>
    </div>
    ';
}

// Function to get puzzle 4 HTML
function get_puzzle_4_html() {
    return '
    <div id="puzzle-4" class="puzzle-section mt-8 max-lg:mb-d30 w-full max-w-full">
        <div class="flex flex-col justify-center relative w-full h-full rounded-4xl bg-surface-sunken mt-d82 px-5 py-8">
            <div class="absolute right-4 lg:right-7 -top-d30">
                <p class="text-70 font-black leading-70 text-transparent bg-clip-text bg-gradient-to-b from-primary-2 to-primary-2/10">4</p>
            </div>

            <h3 class="text-center text-base lg:text-xl font-bold mb-8">
                حالا که از پیچ‌وخم‌های این راه گذر کردی، وقتشه با استفاده از 3 رمزی که در هر مرحله به دست آوردی، سه قفل زیر رو باز کنی!
            </h3>

            <!-- Three Envelopes -->
            <div class="flex flex-col lg:flex-row justify-center items-center gap-8 lg:gap-12 mb-6 w-full max-w-full" id="puzzle-4-envelopes">
                <!-- Envelope 1 -->
                <div class="envelope-container" data-envelope="1">
                    <div class="envelope-wrapper">
                        <!-- Envelope Bottom -->
                        <div class="envelope-back"></div>
                        
                        <!-- Envelope Top Flap -->
                        <div class="envelope-flap"></div>
                        
                        <!-- Envelope Content (Input) -->
                        <div class="envelope-content relative">
                            <div class="absolute top-d115 w-full flex items-center justify-center max-w-d200">
                                <input type="text" id="puzzle-4-input-1" class="envelope-input w-full h-11 border-2 border-focus-blue outline-none px-2 text-center text-base rounded-lg bg-white/95 shadow-md focus:border-focus-blue-deep focus:ring-2 focus:ring-focus-blue/20 transition-all" placeholder="کلمه اول" />
                            </div>
                        </div>
                        
                        <!-- Envelope Card (Paper - smaller, slides out from inside) -->
                        <div class="envelope-card">
                            <div class="text-center h-full flex flex-col justify-center">
                                <div class="mb-3">
                                    <div class="w-16 h-16 mx-auto mb-3 bg-gradient-to-br from-primary-2/10 to-primary-2/5 rounded-full flex items-center justify-center border-2 border-primary-2/20">
                                        <svg class="w-8 h-8 text-primary-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">شهر آغازکننده:</p>
                                <p class="text-2xl font-black text-primary-2 drop-shadow-sm">آتن</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Envelope 2 -->
                <div class="envelope-container" data-envelope="2">
                    <div class="envelope-wrapper">
                        <!-- Envelope Bottom -->
                        <div class="envelope-back"></div>
                        
                        <!-- Envelope Top Flap -->
                        <div class="envelope-flap"></div>
                        
                        <!-- Envelope Content -->
                        <div class="envelope-content relative">
                            <div class="absolute top-d115 w-full flex items-center justify-center max-w-d200">
                                <input type="text" id="puzzle-4-input-2" class="envelope-input w-full h-11 border-2 border-focus-blue outline-none px-2 text-center text-base rounded-lg bg-white/95 shadow-md focus:border-focus-blue-deep focus:ring-2 focus:ring-focus-blue/20 transition-all" placeholder="کلمه دوم" />
                            </div>
                        </div>
                        
                        <!-- Envelope Card -->
                        <div class="envelope-card">
                            <div class="text-center h-full flex flex-col justify-center">
                                <div class="mb-3">
                                    <div class="w-16 h-16 mx-auto mb-3 bg-gradient-to-br from-primary-2/10 to-primary-2/5 rounded-full flex items-center justify-center border-2 border-primary-2/20">
                                        <svg class="w-8 h-8 text-primary-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">مسابقه اصلی:</p>
                                <p class="text-2xl font-black text-primary-2 drop-shadow-sm">دوومیدانی</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Envelope 3 -->
                <div class="envelope-container" data-envelope="3">
                    <div class="envelope-wrapper">
                        <!-- Envelope Bottom -->
                        <div class="envelope-back"></div>
                        
                        <!-- Envelope Top Flap -->
                        <div class="envelope-flap"></div>
                        
                        <!-- Envelope Content -->
                        <div class="envelope-content relative">
                            <div class="absolute top-d115 w-full flex items-center justify-center max-w-d200">
                                <input type="text" id="puzzle-4-input-3" class="envelope-input w-full h-11 border-2 border-focus-blue outline-none px-2 text-center text-base rounded-lg bg-white/95 shadow-md focus:border-focus-blue-deep focus:ring-2 focus:ring-focus-blue/20 transition-all" placeholder="کلمه سوم" />
                            </div>
                        </div>
                        
                        <!-- Envelope Card -->
                        <div class="envelope-card">
                            <div class="text-center h-full flex flex-col justify-center">
                                <div class="mb-3">
                                    <div class="w-16 h-16 mx-auto mb-3 bg-gradient-to-br from-primary-2/10 to-primary-2/5 rounded-full flex items-center justify-center border-2 border-primary-2/20">
                                        <svg class="w-8 h-8 text-primary-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-xs font-bold text-gray-600 mb-2 uppercase tracking-wide">نماد:</p>
                                <p class="text-2xl font-black text-primary-2 drop-shadow-sm">حلقه</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button for all envelopes -->
            <div class="flex justify-center mb-8">
                <button id="submit-puzzle-4-all" class="bg-focus-blue text-white rounded-lg font-bold text-lg flex items-center justify-center hover:bg-blue disabled:opacity-50 disabled:cursor-not-allowed transition-all relative w-10 h-10">
                    <svg id="puzzle-4-all-check" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-white absolute">
                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg id="puzzle-4-all-spinner" class="animate-spin h-5 w-5 text-white hidden absolute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>

            <!-- Typing animation text (hidden initially) -->
            <div id="puzzle-4-typing-text" class="text-center text-base lg:text-xl font-bold text-steel hidden">
                <p id="typing-content"></p>
            </div>

            <!-- Final input for Olympic (hidden initially) -->
            <div id="puzzle-4-final-input-container" class="flex flex-col items-center gap-4" style="display: none;">
                <p id="puzzle-4-final-text" class="text-center text-base lg:text-xl font-bold text-steel mb-2 min-h-d60">
                </p>
                <div class="flex items-center gap-4">
                    <input type="text" id="puzzle-4-final-input" class="w-full max-w-d200 lg:max-w-d300 h-12 border border-slate-120 outline-none pr-4 text-lg rounded-lg" placeholder="نام را وارد کنید" />
                    <button id="submit-puzzle-4-final" class="cursor-pointer z-1 w-12 h-12 bg-focus-blue rounded-lg flex items-center justify-center transition-all hover:bg-blue disabled:opacity-50 disabled:cursor-not-allowed relative">
                        <svg id="puzzle-4-final-check" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-white absolute">
                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg id="puzzle-4-final-spinner" class="animate-spin h-5 w-5 text-white hidden absolute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
                <div id="puzzle-4-final-error" class="text-red-600 text-sm font-medium hidden">کلمه اشتباه است</div>
            </div>

            <!-- Registration form (hidden initially) - Separate from puzzle 4 -->
            <div id="puzzle-4-form-container" class="hidden mt-8">
                <div class="flex flex-col justify-center relative w-full h-full rounded-4xl bg-surface-sunken px-5 py-8">
                    <div class="flex flex-col">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-center mt-2">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-gray-700 whitespace-nowrap">نام و نام خانوادگی:</p>
                                <input id="bootcamp-name" placeholder="نام و نام خانوادگی" type="text" class="border border-slate-120 rounded-lg p-2 flex-1 min-w-0" pattern="[\u0600-\u06FF\s]+" />
                            </div>

                            <div class="flex items-center max-lg:gap-9 gap-2">
                                <p class="text-sm font-bold text-gray-700 whitespace-nowrap">شماره تماس: </p>
                                <input id="bootcamp-phone" placeholder="09120000000" type="tel" pattern="^(09\d{9}|(\+98)?9\d{9})$" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="11" class="border border-slate-120 rounded-lg p-2 flex-1 min-w-0" dir="ltr" />
                            </div>

                            <div class="flex items-center max-lg:gap-3 gap-2">
                                <p class="text-sm font-bold text-gray-700 whitespace-nowrap">شماره دانشجویی: </p>
                                <input id="bootcamp-student-id" placeholder="" type="text" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" class="border border-slate-120 rounded-lg p-2 flex-1 min-w-0" />
                            </div>
                        </div>

                        <button id="submit-bootcamp-form" class="lg:w-d100 h-10 lg:mx-auto w-full cursor-pointer bg-focus-blue text-white rounded-lg p-2 mt-4 flex items-center justify-center relative">
                            <span id="submit-form-text">ارسال</span>
                            <svg id="submit-form-spinner" class="animate-spin h-5 w-5 text-white hidden absolute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                        <div id="form-success" class="text-green-600 text-sm font-medium mt-2 text-center hidden"></div>
                        <div id="form-error" class="text-red-600 text-sm font-medium mt-2 text-center hidden"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    ';
}

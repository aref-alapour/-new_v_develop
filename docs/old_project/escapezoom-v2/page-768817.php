<?php get_header(); ?>

<div class="container mx-auto py-8 max-lg:mb-25 overflow-x-hidden scrollbar-hide" id="bootcamp-container" style="overflow-x: hidden !important; overflow-y: visible;">
    <!-- Puzzle 1 -->
    <div id="puzzle-1" class="puzzle-section max-lg:mb-[30px]">
        <div class="flex max-lg:flex-col justify-between relative w-full h-full rounded-4xl bg-[#F1F5F9] lg:pr-[100px] lg:pl-12 mt-[74px] pb-7 max-lg:h-[170px]">
            <div class="flex justify-between w-full">
                <div class="absolute right-4 lg:right-7 top-[-30px]">
                    <p class="text-[70px] font-black leading-[70px] text-transparent bg-clip-text bg-gradient-to-b from-[#FD7013] to-[#FD7013]/10">1</p>
                </div>

                <div class="flex flex-col pt-6 pb-7 max-lg:pr-8 max-lg:pt-8">
                    <p class="text-base lg:text-xl font-bold flex-wrap max-lg:max-w-[169px]">کلمه ای که توی معمای اول پیدا کردی رو وارد کن.</p>

                    <div class="flex items-center max-h-12 mt-10 z-1 gap-4 max-lg:absolute max-lg:bottom-5 max-lg:right-10">
                        <div class="flex items-center" id="puzzle-1-inputs">
                            <input type="text" maxlength="1" class="puzzle-input w-12 h-12 border border-[#CAD5E2] outline-none pr-4 text-lg rounded-tr-lg rounded-br-lg" data-index="0" />
                            <input type="text" maxlength="1" class="puzzle-input w-12 h-12 border border-[#CAD5E2] outline-none pr-4 text-lg" data-index="1" />
                            <input type="text" maxlength="1" class="puzzle-input w-12 h-12 border border-[#CAD5E2] outline-none pr-4 text-lg" data-index="2" />
                            <input type="text" maxlength="1" class="puzzle-input w-12 h-12 border border-[#CAD5E2] outline-none pr-4 text-lg rounded-bl-lg rounded-tl-lg" data-index="3" />
                        </div>

                        <button id="submit-puzzle-1" class="w-12 h-12 bg-[#2B7FFF] rounded-lg flex items-center justify-center cursor-pointer transition-all hover:bg-[#2563EB] disabled:opacity-50 disabled:cursor-not-allowed relative">
                            <svg id="puzzle-1-check" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-white absolute">
                                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <svg id="puzzle-1-spinner" class="animate-spin h-5 w-5 text-white hidden absolute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <img src="<?php echo Theme_ASSET_URL; ?>images/game-header.avif" alt="" class="max-lg:w-40 lg:w-80">
            </div>
            <!-- Error message -->
            <div id="puzzle-1-error" class="absolute top-full right-0 mt-2 text-red-600 text-sm font-medium hidden">کلمه اشتباه است</div>

           
        </div>
    </div>

    <!-- Puzzles and success messages will be added here step by step -->
    <div id="puzzles-container"></div>
</div>

<style>
    /* Envelope 3D Styles - Enhanced */
    /* Envelope Container - Based on provided design */
    .envelope-container {
        position: relative;
        width: 100%;
        max-width: 280px;
        margin: 0 auto;
        flex-shrink: 0;
    }
    
    .envelope-wrapper {
        position: relative;
        overflow: hidden;
        padding-top: 110%;
        background: rgb(255, 255, 255);
        background: linear-gradient(0deg, #c7c2c5 0%, #c7c2c5 55%, rgba(255, 255, 255, 0) 55%, rgba(255, 255, 255, 0) 100%);
        width: 100%;
        max-width: 100%;
        filter: drop-shadow(0px 6px 3px rgba(50, 50, 0, 0.1));
    }
    
    /* Envelope Bottom */
    .envelope-back {
        height: 55%;
        width: 100%;
        z-index: 2;
        bottom: 0;
        position: absolute;
        filter: drop-shadow(0px -6px 3px rgba(50, 50, 0, 0.1));
    }
    
    .envelope-back::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        clip-path: polygon(50% 50%, 100% 0, 100% 100%, 0 100%, 0 0);
        background: white;
    }
    
    .envelope-back::after {
        content: '';
        position: absolute;
        background: #f8f6f7;
        width: 50%;
        height: 100%;
        right: 0;
        clip-path: polygon(0 50%, 100% 0, 100% 100%);
    }
    
    /* Envelope Top Flap */
    .envelope-flap {
        filter: drop-shadow(0px 6px 3px rgba(50, 50, 0, 0.1));
        position: absolute;
        width: 100%;
        height: 33%;
        top: 45%;
        z-index: 99;
        transition: all 0.2s ease-in-out;
        transform-origin: top;
    }
    
    .envelope-flap::before {
        content: '';
        position: absolute;
        transform-origin: top;
        width: 100%;
        height: 100%;
        background: white;
        clip-path: polygon(50% 100%, 0 0, 100% 0);
        transition: all 0.2s ease-in-out;
    }
    
    .envelope-flap.opening {
        transform: rotateX(-180deg);
        z-index: 1;
        transition-delay: 0s;
    }
    
    /* Envelope Content (Input) - Always visible on envelope, centered */
    .envelope-content {
        width: 100%;
        left: 0;
        padding: 0;
        position: absolute;
        height: 100%;
        z-index: 10; /* Higher than card to be on top */
        transition: all 0.4s ease-in-out;
        top: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: auto;
    }
    
    .envelope-content > div {
        position: relative;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .envelope-content.hiding {
        opacity: 0;
        pointer-events: none;
    }
    
    /* Envelope Card (Paper) - Smaller to not overlap text */
    .envelope-card {
        position: absolute;
        width: 85%;
        height: 70%;
        top: 15%;
        left: 7.5%;
        z-index: 3; /* Lower than input content */
        background: linear-gradient(145deg, #FFFFFF 0%, #FAFAFA 100%);
        box-shadow: 
            0 20px 40px rgba(0, 0, 0, 0.25),
            0 8px 16px rgba(0, 0, 0, 0.15);
        border: 1px solid #E5E5E5;
        border-radius: 8px;
        transform: translateY(100%) scale(0.9);
        opacity: 0;
        transition: all 1s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .envelope-card.showing {
        transform: translateY(-20px) scale(1) !important;
        opacity: 1 !important;
    }
    
    .envelope-card.bouncing {
        animation: cardBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    
    @keyframes cardBounce {
        0% { transform: translateY(-20px) scale(1); }
        50% { transform: translateY(-30px) scale(1.05); }
        100% { transform: translateY(-20px) scale(1); }
    }
    
    .envelope-input {
        transition: all 0.3s ease;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .envelope-input:focus {
        transform: scale(1.03);
        box-shadow: 0 6px 16px rgba(43, 127, 255, 0.25);
    }
    
    .envelope-input.correct {
        border-color: #10B981 !important;
        background-color: #F0FDF4 !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2) !important;
    }
    
    .envelope-input.wrong {
        border-color: #EF4444 !important;
        background-color: #FEF2F2 !important;
        animation: shake 0.5s;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    /* Timer Styles - Bottom Right */
    #bootcamp-timer {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 2px solid #E5E7EB;
    }
    
    @media (max-width: 1024px) {
        #bootcamp-timer {
            bottom: 10px;
            right: 10px;
            padding: 8px 12px;
            font-size: 14px;
        }
        
        #timer-display {
            font-size: 16px;
        }
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Use REST API for faster requests
    // Direct AJAX file for speed (bypass REST API)
    const bootcampAjaxUrl = '<?php echo esc_url_raw(get_template_directory_uri() . '/template/bootcamp/ajax/bootcamp_rest_api.php'); ?>';
    // Simple nonce - matches backend hash
    const nonce = '<?php echo hash('sha256', 'v2-ajax-nonce' . 'v2-ajax-secret-key'); ?>';

    // Function to scroll to new puzzle with 80px offset from top
    function scrollToNewPuzzle() {
        setTimeout(() => {
            const puzzlesContainer = $('#puzzles-container');
            if (puzzlesContainer.length) {
                const lastPuzzle = puzzlesContainer.children().last();
                if (lastPuzzle.length) {
                    const puzzleTop = lastPuzzle.offset().top;
                    const scrollPosition = puzzleTop - 80;
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500);
                }
            }
        }, 100);
    }

    // Puzzle 1 handlers
    function initPuzzle1() {
        const inputs = $('#puzzle-1 .puzzle-input[data-index]');
        const submitBtn = $('#submit-puzzle-1');
        const checkIcon = $('#puzzle-1-check');
        const spinner = $('#puzzle-1-spinner');
        const errorMsg = $('#puzzle-1-error');

        if (inputs.length === 0) {
            console.error('Puzzle 1 inputs not found');
            return;
        }

        if (submitBtn.length === 0) {
            console.error('Puzzle 1 button not found');
            return;
        }

        // Remove existing handlers to prevent duplicates
        inputs.off('input keydown keypress');
        submitBtn.off('click');

        // Input navigation
        inputs.on('input', function() {
            const $this = $(this);
            const index = parseInt($this.data('index'));
            const val = $this.val();
            if (val.length === 1 && index < 3) {
                setTimeout(function() {
                    inputs.eq(index + 1).focus();
                }, 10);
            }
        });

        inputs.on('keydown', function(e) {
            const $this = $(this);
            const index = parseInt($this.data('index'));
            if (e.key === 'Backspace' && !$this.val() && index > 0) {
                e.preventDefault();
                inputs.eq(index - 1).focus();
            }
        });

        function submitPuzzle1() {
            const answer = inputs.map(function() { return $(this).val(); }).get().join('');
            
            console.log('Submit puzzle 1 called, answer:', answer);
            
            if (answer.length !== 4) {
                console.log('Answer length is not 4:', answer.length);
                return;
            }

            // Hide check icon, show spinner
            checkIcon.addClass('hidden');
            spinner.removeClass('hidden');
            errorMsg.addClass('hidden');
            inputs.prop('disabled', true);
            submitBtn.prop('disabled', true);

            $.ajax({
                url: bootcampAjaxUrl,
                type: 'POST',
                data: {
                    nonce: nonce,
                    action_type: 'check_answer',
                    puzzle_num: 1,
                    answer: answer
                },
                success: function(response) {
                    if (response.success) {
                        // Correct answer - show green check and disable
                        spinner.addClass('hidden');
                        checkIcon.removeClass('hidden').css('color', 'white');
                        submitBtn.css('background-color', '#10B981').prop('disabled', true);
                        inputs.prop('disabled', true);
                        
                        // Add next puzzle HTML from AJAX response
                        if (response.data && response.data.next_html) {
                            setTimeout(() => {
                                $('#puzzles-container').append(response.data.next_html);
                                // Scroll to new puzzle
                                scrollToNewPuzzle();
                                // Initialize handlers for new puzzle
                                if (response.data.next_html.includes('puzzle-2')) {
                                    setTimeout(() => {
                                        initPuzzle2();
                                    }, 100);
                                } else if (response.data.next_html.includes('puzzle-3-success')) {
                                    setTimeout(() => {
                                        initPuzzle3SuccessAnimation();
                                    }, 100);
                                }
                            }, 500);
                        }
                    } else {
                        // Wrong answer - reset button and show error
                        spinner.addClass('hidden');
                        checkIcon.removeClass('hidden').css('color', 'white');
                        submitBtn.css('background-color', '#2B7FFF').prop('disabled', false);
                        errorMsg.removeClass('hidden');
                        inputs.prop('disabled', false);
                        inputs.val('').first().focus();
                    }
                },
                error: function() {
                    // Reset on error
                    spinner.addClass('hidden');
                    checkIcon.removeClass('hidden').css('color', 'white');
                    submitBtn.css('background-color', '#2B7FFF').prop('disabled', false);
                    inputs.prop('disabled', false);
                    alert('خطا در ارتباط با سرور');
                }
            });
        }

        submitBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Puzzle 1 button clicked');
            submitPuzzle1();
        });
        inputs.on('keypress', function(e) {
            if (e.which === 13 || e.keyCode === 13) {
                e.preventDefault();
                console.log('Enter pressed');
                submitPuzzle1();
            }
        });
        
        console.log('Puzzle 1 initialized:', {
            inputs: inputs.length,
            submitBtn: submitBtn.length
        });
    }

    // Initialize puzzle 2 handlers
    function initPuzzle2() {
        const inputs = $('.puzzle-2-input[data-index]');
        const submitBtn = $('#submit-puzzle-2');
        const checkIcon = $('#puzzle-2-check');
        const spinner = $('#puzzle-2-spinner');
        const errorMsg = $('#puzzle-2-error');

        // Input navigation
        inputs.on('input', function() {
            const index = parseInt($(this).data('index'));
            if ($(this).val().length === 1 && index < 3) {
                inputs.eq(index + 1).focus();
            }
        });

        inputs.on('keydown', function(e) {
            const index = parseInt($(this).data('index'));
            if (e.key === 'Backspace' && !$(this).val() && index > 0) {
                inputs.eq(index - 1).focus();
            }
        });

        function submitPuzzle2() {
            const answer = inputs.map(function() { return $(this).val(); }).get().join('');
            
            if (answer.length !== 4) {
                return;
            }

            // Hide check icon, show spinner
            checkIcon.addClass('hidden');
            spinner.removeClass('hidden');
            errorMsg.addClass('hidden');
            inputs.prop('disabled', true);
            submitBtn.prop('disabled', true);

            $.ajax({
                url: bootcampAjaxUrl,
                type: 'POST',
                data: {
                    nonce: nonce,
                    action_type: 'check_answer',
                    puzzle_num: 2,
                    answer: answer
                },
                success: function(response) {
                    if (response.success) {
                        // Correct answer - show green check and disable
                        spinner.addClass('hidden');
                        checkIcon.removeClass('hidden').css('color', 'white');
                        submitBtn.css('background-color', '#10B981').prop('disabled', true);
                        inputs.prop('disabled', true);
                        
                        // Add next puzzle HTML from AJAX response
                        if (response.data && response.data.next_html) {
                            setTimeout(() => {
                                $('#puzzles-container').append(response.data.next_html);
                                // Scroll to new puzzle
                                scrollToNewPuzzle();
                                // Initialize handlers for new content
                                if (response.data.next_html.includes('puzzle-3')) {
                                    setTimeout(() => {
                                        initPuzzle3();
                                    }, 100);
                                } else if (response.data.next_html.includes('puzzle-3-success')) {
                                    setTimeout(() => {
                                        initPuzzle3SuccessAnimation();
                                    }, 100);
                                }
                            }, 500);
                        }
                    } else {
                        // Wrong answer - reset button and show error
                        spinner.addClass('hidden');
                        checkIcon.removeClass('hidden').css('color', 'white');
                        submitBtn.css('background-color', '#2B7FFF').prop('disabled', false);
                        errorMsg.removeClass('hidden');
                        inputs.prop('disabled', false);
                        inputs.val('').first().focus();
                    }
                },
                error: function() {
                    // Reset on error
                    spinner.addClass('hidden');
                    checkIcon.removeClass('hidden').css('color', 'white');
                    submitBtn.css('background-color', '#2B7FFF').prop('disabled', false);
                    inputs.prop('disabled', false);
                    alert('خطا در ارتباط با سرور');
                }
            });
        }

        submitBtn.on('click', submitPuzzle2);
        inputs.on('keypress', function(e) {
            if (e.which === 13) {
                submitPuzzle2();
            }
        });
    }
    
    // Initialize puzzle 3 handlers
    function initPuzzle3() {
        const input = $('#puzzle-3-input');
        const submitBtn = $('#submit-puzzle-3');
        const checkIcon = $('#puzzle-3-check');
        const spinner = $('#puzzle-3-spinner');
        const errorMsg = $('#puzzle-3-error');

        if (input.length === 0) {
            console.error('Puzzle 3 input not found');
            return;
        }

        if (submitBtn.length === 0) {
            console.error('Puzzle 3 button not found');
            return;
        }

        // Remove existing handlers to prevent duplicates
        input.off('keypress');
        submitBtn.off('click');

        function submitPuzzle3() {
            // Get and trim the answer
            const answer = input.val().trim();
            
            console.log('Submit puzzle 3 called, answer:', answer);
            
            if (!answer) {
                return;
            }
            
            // Hide check icon, show spinner
            checkIcon.addClass('hidden');
            spinner.removeClass('hidden');
            errorMsg.addClass('hidden');
            input.prop('disabled', true);
            submitBtn.prop('disabled', true);

            $.ajax({
                url: bootcampAjaxUrl,
                type: 'POST',
                data: {
                    nonce: nonce,
                    action_type: 'check_answer',
                    puzzle_num: 3,
                    answer: answer
                },
                success: function(response) {
                    if (response.success) {
                        // Correct answer - show green check and disable
                        spinner.addClass('hidden');
                        checkIcon.removeClass('hidden').css('color', 'white');
                        submitBtn.css('background-color', '#10B981').prop('disabled', true);
                        input.prop('disabled', true);
                        
                        // Add next puzzle HTML from AJAX response
                        if (response.data && response.data.next_html) {
                            setTimeout(() => {
                                $('#puzzles-container').append(response.data.next_html);
                                // Scroll to new puzzle
                                scrollToNewPuzzle();
                                // Initialize handlers for new content
                                if (response.data.next_html.includes('puzzle-3-success')) {
                                    // Store puzzle 4 HTML if available (should always be present)
                                    if (response.data.puzzle_4_html) {
                                        window.puzzle4Html = response.data.puzzle_4_html;
                                        console.log('Puzzle 4 HTML stored successfully');
                                    } else {
                                        console.error('Puzzle 4 HTML not found in response!', response.data);
                                    }
                                    setTimeout(() => {
                                        initPuzzle3SuccessAnimation();
                                    }, 100);
                                } else if (response.data.next_html.includes('puzzle-4')) {
                                    setTimeout(() => {
                                        initPuzzle4();
                                    }, 100);
                                }
                            }, 500);
                        }
                    } else {
                        // Wrong answer - reset button and show error
                        spinner.addClass('hidden');
                        checkIcon.removeClass('hidden').css('color', 'white');
                        submitBtn.css('background-color', '#2B7FFF').prop('disabled', false);
                        errorMsg.removeClass('hidden');
                        input.prop('disabled', false);
                        input.val('').focus();
                    }
                },
                error: function() {
                    // Reset on error
                    spinner.addClass('hidden');
                    checkIcon.removeClass('hidden').css('color', 'white');
                    submitBtn.css('background-color', '#2B7FFF').prop('disabled', false);
                    input.prop('disabled', false);
                    alert('خطا در ارتباط با سرور');
                }
            });
        }

        submitBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Puzzle 3 button clicked');
            submitPuzzle3();
        });
        input.on('keypress', function(e) {
            if (e.which === 13 || e.keyCode === 13) {
                e.preventDefault();
                console.log('Enter pressed in puzzle 3');
                submitPuzzle3();
            }
        });
        
        console.log('Puzzle 3 initialized:', {
            input: input.length,
            submitBtn: submitBtn.length
        });
    }
    
    // Initialize puzzle 3 success animation
    function initPuzzle3SuccessAnimation() {
        console.log('initPuzzle3SuccessAnimation called');
        function initAnimation() {
            if (typeof gsap === 'undefined') {
                console.log('GSAP not loaded, retrying...');
                setTimeout(initAnimation, 100);
                return;
            }
            
            const $container = $('#puzzle-3-success');
            const $text = $('#success-text');
            const container = $('#success-shapes-container')[0];
            
            console.log('Container found:', $container.length);
            console.log('Text found:', $text.length);
            console.log('Shapes container found:', !!container);
            
            if (!$text.length || !container) {
                console.error('Text or container not found');
                return;
            }
            
            console.log('Fading in container...');
            $container.fadeIn(500);
            
            setTimeout(() => {
                const textRect = $text[0].getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const centerX = textRect.left + textRect.width / 2 - containerRect.left;
                const centerY = textRect.top + textRect.height / 2 - containerRect.top;
                
                gsap.to($text[0], {
                    opacity: 1,
                    scale: 1,
                    duration: 0.6,
                    ease: 'back.out(1.7)',
                    onComplete: function() {
                        createParticlesWithGSAP(container, centerX, centerY);
                    }
                });
                
                // Load puzzle 4 exactly 3 seconds after text appears
                console.log('Setting timer for puzzle 4 (3 seconds)...');
                setTimeout(() => {
                    console.log('Timer fired! Loading puzzle 4...');
                    loadPuzzle4AfterSuccess();
                }, 3000);
            }, 100);
        }
        
        initAnimation();
    }
    
    // Load puzzle 4 after success animation
    function loadPuzzle4AfterSuccess() {
        console.log('Loading puzzle 4 from stored HTML...');
        
        // Use stored HTML from AJAX response (should always be available)
        if (window.puzzle4Html) {
            console.log('Using stored puzzle 4 HTML');
            $('#puzzles-container').append(window.puzzle4Html);
            scrollToNewPuzzle();
            setTimeout(() => {
                initPuzzle4();
            }, 100);
            // Clear stored HTML
            window.puzzle4Html = null;
        } else {
            console.error('ERROR: Puzzle 4 HTML not stored! This should not happen.');
            alert('خطا در بارگذاری معمای 4. لطفا صفحه را رفرش کنید.');
        }
    }
    
    function createParticlesWithGSAP(container, centerX, centerY) {
        const colors = ['#87CEEB', '#FFB347', '#FFD700', '#DDA0DD', '#FFA07A', '#98D8C8'];
        const shapes = ['circle', 'triangle', 'dash'];
        const count = 100;
        
        for (let i = 0; i < count; i++) {
            createParticle(container, colors, shapes, centerX, centerY, i);
        }
    }
    
    function createParticle(container, colors, shapes, centerX, centerY, index) {
        const particle = document.createElement('div');
        const color = colors[Math.floor(Math.random() * colors.length)];
        const shapeType = shapes[Math.floor(Math.random() * shapes.length)];
        const size = Math.random() * 8 + 4;
        
        const angle = Math.random() * Math.PI * 2;
        const distance = Math.random() * 600 + 300;
        const x = Math.cos(angle) * distance;
        const y = Math.sin(angle) * distance;
        const rotation = Math.random() * 1080 - 540;
        
        if (shapeType === 'circle') {
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.borderRadius = '50%';
            particle.style.backgroundColor = color;
        } else if (shapeType === 'triangle') {
            particle.style.width = '0';
            particle.style.height = '0';
            particle.style.borderLeft = (size/2) + 'px solid transparent';
            particle.style.borderRight = (size/2) + 'px solid transparent';
            particle.style.borderBottom = size + 'px solid ' + color;
        } else {
            particle.style.width = (size * 1.5) + 'px';
            particle.style.height = (size * 0.4) + 'px';
            particle.style.borderRadius = '2px';
            particle.style.backgroundColor = color;
        }
        
        particle.style.position = 'absolute';
        particle.style.pointerEvents = 'none';
        particle.style.transformOrigin = 'center center';
        particle.style.left = centerX + 'px';
        particle.style.top = centerY + 'px';
        particle.style.marginLeft = '-' + (size/2) + 'px';
        particle.style.marginTop = '-' + (size/2) + 'px';
        
        container.appendChild(particle);
        
        gsap.set(particle, {
            rotation: 0,
            scale: 1,
            opacity: 1
        });
        
        gsap.to(particle, {
            x: x,
            y: y,
            rotation: rotation,
            scale: 0.3,
            opacity: 0,
            duration: 2.5 + Math.random() * 1,
            ease: 'power1.out',
            delay: index * 0.005,
            onComplete: function() {
                if (particle.parentNode) {
                    particle.parentNode.removeChild(particle);
                }
            }
        });
    }

    // Start timer when page loads
    let bootcampStartTime = Date.now();
    window.bootcampStartTime = bootcampStartTime;
    
    // Puzzle 4 handlers
    function initPuzzle4() {
        // Create and show timer (bottom right)
        if ($('#bootcamp-timer').length === 0) {
            $('body').append(`
                <div id="bootcamp-timer" class="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 z-50">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#2B7FFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-lg font-bold text-gray-800" id="timer-display">00:00:00</span>
                    </div>
                </div>
            `);
        }
        
        const timerDisplay = $('#timer-display');
        
        function updateTimer() {
            if (!timerDisplay.length) return;
            const elapsed = Math.floor((Date.now() - window.bootcampStartTime) / 1000);
            const hours = Math.floor(elapsed / 3600);
            const minutes = Math.floor((elapsed % 3600) / 60);
            const seconds = elapsed % 60;
            timerDisplay.text(
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0')
            );
        }
        
        const timerInterval = setInterval(updateTimer, 1000);
        updateTimer();
        
        // Store timer interval globally for form submission
        window.bootcampTimerInterval = timerInterval;
        
        // Handle submit for all envelopes at once
        function handleAllEnvelopesSubmit() {
            const input1 = $('#puzzle-4-input-1');
            const input2 = $('#puzzle-4-input-2');
            const input3 = $('#puzzle-4-input-3');
            const submitBtn = $('#submit-puzzle-4-all');
            const checkIcon = $('#puzzle-4-all-check');
            const spinner = $('#puzzle-4-all-spinner');
            const textSpan = $('#puzzle-4-all-text');
            
            const answer1 = input1.val().trim();
            const answer2 = input2.val().trim();
            const answer3 = input3.val().trim();
            
            if (!answer1 || !answer2 || !answer3) {
                alert('لطفا همه رمزها را وارد کنید');
                return;
            }
            
            // Show spinner, hide check
            checkIcon.addClass('hidden');
            spinner.removeClass('hidden');
            textSpan.addClass('hidden');
            submitBtn.prop('disabled', true);
            input1.prop('disabled', true);
            input2.prop('disabled', true);
            input3.prop('disabled', true);
            
            // Check all answers via AJAX
            $.ajax({
                url: bootcampAjaxUrl,
                type: 'POST',
                data: {
                    nonce: nonce,
                    action_type: 'check_envelopes_all',
                    answer1: answer1,
                    answer2: answer2,
                    answer3: answer3
                },
                success: function(response) {
                    if (response.success && response.data.correct) {
                        // All correct - show check, hide spinner
                        spinner.addClass('hidden');
                        checkIcon.removeClass('hidden');
                        textSpan.addClass('hidden');
                        submitBtn.css('background-color', '#10B981').prop('disabled', true);
                        input1.removeClass('wrong').addClass('correct').css('border-color', '#10B981');
                        input2.removeClass('wrong').addClass('correct').css('border-color', '#10B981');
                        input3.removeClass('wrong').addClass('correct').css('border-color', '#10B981');
                        
                        // Open envelopes after a short delay
                        setTimeout(() => {
                            openEnvelopes();
                        }, 500);
                    } else {
                        // Some or all wrong
                        spinner.addClass('hidden');
                        checkIcon.addClass('hidden');
                        textSpan.addClass('hidden');
                        submitBtn.css('background-color', '#2B7FFF').prop('disabled', false);
                        
                        const results = response.data.results || {};
                        
                        // Update input styles based on results
                        if (results.envelope1) {
                            input1.removeClass('wrong').addClass('correct').css('border-color', '#10B981');
                        } else {
                            input1.removeClass('correct').addClass('wrong').css('border-color', '#EF4444');
                        }
                        
                        if (results.envelope2) {
                            input2.removeClass('wrong').addClass('correct').css('border-color', '#10B981');
                        } else {
                            input2.removeClass('correct').addClass('wrong').css('border-color', '#EF4444');
                        }
                        
                        if (results.envelope3) {
                            input3.removeClass('wrong').addClass('correct').css('border-color', '#10B981');
                        } else {
                            input3.removeClass('correct').addClass('wrong').css('border-color', '#EF4444');
                        }
                        
                        input1.prop('disabled', false);
                        input2.prop('disabled', false);
                        input3.prop('disabled', false);
                    }
                },
                error: function() {
                    // Reset on error
                    spinner.addClass('hidden');
                    checkIcon.addClass('hidden');
                    textSpan.addClass('hidden');
                    submitBtn.css('background-color', '#2B7FFF').prop('disabled', false);
                    input1.prop('disabled', false);
                    input2.prop('disabled', false);
                    input3.prop('disabled', false);
                    alert('خطا در ارتباط با سرور');
                }
            });
        }
        
        // Submit button handler
        $('#submit-puzzle-4-all').off('click').on('click', function() {
            handleAllEnvelopesSubmit();
        });
        
        // Enter key handlers for all inputs
        $('#puzzle-4-input-1, #puzzle-4-input-2, #puzzle-4-input-3').off('keypress').on('keypress', function(e) {
            if (e.which === 13) {
                handleAllEnvelopesSubmit();
            }
        });
        
        function openEnvelopes() {
            $('.envelope-container').each(function(index) {
                const envelope = $(this);
                const flap = envelope.find('.envelope-flap');
                const card = envelope.find('.envelope-card');
                const content = envelope.find('.envelope-content');
                
                setTimeout(() => {
                    // Step 1: Open the flap (like code sample)
                    flap.addClass('opening');
                    
                    // Step 2: Remove input content completely after flap starts opening
                    setTimeout(() => {
                        content.remove(); // Remove input completely, not just hide
                    }, 200);
                    
                    // Step 3: Paper slides out from inside envelope (like code sample)
                    setTimeout(() => {
                        card.addClass('showing');
                        
                        // Step 4: Add bounce effect after paper appears
                        setTimeout(() => {
                            card.addClass('bouncing');
                            setTimeout(() => {
                                card.removeClass('bouncing');
                            }, 600);
                        }, 1000);
                    }, 400);
                }, index * 500);
            });
            
            // Show typing animation after all cards are shown
            setTimeout(() => {
                showTypingAnimation();
                // Scroll to typing text after cards are shown
                setTimeout(() => {
                    const typingElement = $('#puzzle-4-typing-text');
                    if (typingElement.length) {
                        $('html, body').animate({
                            scrollTop: typingElement.offset().top - 80
                        }, 800);
                    }
                }, 1000);
            }, 2000);
        }
        
        function showTypingAnimation() {
            const text = 'حالا تو، سه تا سرنخ داری که هر سرنخ، کمک‌کننده برای به دست آوردن معمای نهایی این راهه...';
            const typingContainer = $('#puzzle-4-typing-text');
            const typingContent = $('#typing-content');
            
            typingContainer.removeClass('hidden');
            typingContent.text('');
            
            let index = 0;
            function typeChar() {
                if (index < text.length) {
                    typingContent.text(typingContent.text() + text[index]);
                    index++;
                    setTimeout(typeChar, 50);
                } else {
                    // Show final input with typing animation for the instruction text
                    setTimeout(() => {
                        $('#puzzle-4-final-input-container').css('display', 'flex');
                        typeFinalInstruction();
                    }, 500);
                }
            }
            
            typeChar();
        }
        
        function typeFinalInstruction() {
            const text = 'با استفاده از سرنخ‌هایی که به دست آوردی، اسم نهایی این ماجراجویی رو حدس بزن.';
            const typingElement = $('#puzzle-4-final-text');
            
            typingElement.text('');
            
            let index = 0;
            function typeChar() {
                if (index < text.length) {
                    typingElement.text(typingElement.text() + text[index]);
                    index++;
                    setTimeout(typeChar, 30);
                } else {
                    // Focus input after typing is done
                    setTimeout(() => {
                        $('#puzzle-4-final-input').focus();
                    }, 300);
                }
            }
            
            typeChar();
        }
        
        // Handle final input submission
        $('#submit-puzzle-4-final').on('click', function() {
            const input = $('#puzzle-4-final-input');
            const answer = input.val().trim();
            const submitBtn = $(this);
            const checkIcon = $('#puzzle-4-final-check');
            const spinner = $('#puzzle-4-final-spinner');
            const errorMsg = $('#puzzle-4-final-error');
            
            if (answer === '') {
                return;
            }
            
            checkIcon.addClass('hidden');
            spinner.removeClass('hidden');
            submitBtn.prop('disabled', true);
            input.prop('disabled', true);
            errorMsg.addClass('hidden');
            
            $.ajax({
                url: bootcampAjaxUrl,
                type: 'POST',
                data: {
                    nonce: nonce,
                    action_type: 'check_answer',
                    puzzle_num: '4Final',
                    answer: answer
                },
                success: function(response) {
                    // Always accept the answer for 4Final (no error message)
                    spinner.addClass('hidden');
                    checkIcon.removeClass('hidden').css('color', 'white');
                    submitBtn.css('background-color', '#10B981').prop('disabled', true);
                    input.prop('disabled', true);
                    
                    // Store the guessed answer for form submission
                    window.bootcampFinalAnswer = answer;
                    
                    // Show form after a delay (no result message)
                    setTimeout(() => {
                        $('#puzzle-4-form-container').removeClass('hidden');
                        scrollToNewPuzzle();
                    }, 1000);
                },
                error: function() {
                    // Even on error, accept the answer for 4Final
                    spinner.addClass('hidden');
                    checkIcon.removeClass('hidden').css('color', 'white');
                    submitBtn.css('background-color', '#10B981').prop('disabled', true);
                    input.prop('disabled', true);
                    
                    // Store the guessed answer for form submission
                    window.bootcampFinalAnswer = answer;
                    
                    // Show form after a delay
                    setTimeout(() => {
                        $('#puzzle-4-form-container').removeClass('hidden');
                        scrollToNewPuzzle();
                    }, 1000);
                }
            });
        });
        
        // Handle Enter key on final input
        $('#puzzle-4-final-input').on('keypress', function(e) {
            if (e.which === 13) {
                $('#submit-puzzle-4-final').click();
            }
        });
        
        // Handle form submission
        $('#submit-bootcamp-form').on('click', function() {
            const name = $('#bootcamp-name').val().trim();
            const phone = $('#bootcamp-phone').val().trim();
            const studentId = $('#bootcamp-student-id').val().trim();
            const submitBtn = $(this);
            const submitText = $('#submit-form-text');
            const submitSpinner = $('#submit-form-spinner');
            const formSuccess = $('#form-success');
            const formError = $('#form-error');
            
            if (!name || !phone) {
                formError.removeClass('hidden').text('لطفا نام و شماره تماس را وارد کنید');
                formSuccess.addClass('hidden');
                return;
            }
            
            // Calculate duration
            const duration = Math.floor((Date.now() - window.bootcampStartTime) / 1000);
            
            // Show spinner, hide text
            submitText.addClass('hidden');
            submitSpinner.removeClass('hidden');
            submitBtn.prop('disabled', true);
            formError.addClass('hidden');
            formSuccess.addClass('hidden');
            
            $.ajax({
                url: bootcampAjaxUrl,
                type: 'POST',
                data: {
                    nonce: nonce,
                    action_type: 'submit_form',
                    name: name,
                    phone: phone,
                    student_id: studentId,
                    duration: duration,
                    final_answer: window.bootcampFinalAnswer || ''
                },
                success: function(response) {
                    if (response.success) {
                        // Hide spinner and button, show success message
                        submitSpinner.addClass('hidden');
                        submitText.addClass('hidden');
                        submitBtn.addClass('hidden');
                        formError.addClass('hidden');
                        
                        // Show success message with new text
                         const successText = 'این چالش مخصوص دانشجویان دومین المپیک بازی‌های فکری است و برندگان تنها از همین جمع انتخاب خواهند شد.\nنتیجه روز پنجشنبه 18 دی ماه به شماره تماس شما ارسال خواهد شد.';
                        formSuccess.removeClass('hidden').html(successText.replace(/\n/g, '<br>'));
                        
                        // Stop timer
                        if (window.bootcampTimerInterval) {
                            clearInterval(window.bootcampTimerInterval);
                        }
                        
                        // Redirect to home page after 5 seconds
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 5000);
                    } else {
                        // Show error
                        submitSpinner.addClass('hidden');
                        submitText.removeClass('hidden');
                        formError.removeClass('hidden').text(response.data?.message || 'خطا در ثبت اطلاعات');
                        formSuccess.addClass('hidden');
                        submitBtn.prop('disabled', false);
                    }
                },
                error: function() {
                    // Show error
                    submitSpinner.addClass('hidden');
                    submitText.removeClass('hidden');
                    formError.removeClass('hidden').text('خطا در ارتباط با سرور');
                    formSuccess.addClass('hidden');
                    submitBtn.prop('disabled', false);
                }
            });
        });
    }
    
    // Initialize puzzle 1
    initPuzzle1();
    
    // Persian text validation for all text inputs (except phone and student ID)
    function allowPersianOnly(e) {
        const char = String.fromCharCode(e.which || e.keyCode);
        // Allow Persian characters (U+0600 to U+06FF), space, and backspace/delete
        const persianRegex = /[\u0600-\u06FF\s]/;
        if (e.which === 8 || e.which === 0 || e.which === 46) {
            return true; // Allow backspace, delete
        }
        if (!persianRegex.test(char)) {
            e.preventDefault();
            return false;
        }
    }
    
    // Apply Persian validation to puzzle inputs
    $(document).on('keypress', '.puzzle-input, .puzzle-2-input, .puzzle-3-input, .envelope-input, #bootcamp-name', allowPersianOnly);
    
    // Numeric only for student ID
    $(document).on('keypress', '#bootcamp-student-id', function(e) {
        if (e.which === 8 || e.which === 0 || e.which === 46) {
            return true; // Allow backspace, delete
        }
        if (e.which < 48 || e.which > 57) {
            e.preventDefault();
            return false;
        }
    });
    
    // Phone number validation (already handled by pattern, but add extra validation)
    $(document).on('input', '#bootcamp-phone', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        $(this).val(value);
    });
});
</script>

<?php get_footer(); ?>

$(document).ready(function () {
    $('#add-player').click(function () {
        let playerMaxCount = parseInt($('#other-players').attr('data-count')) - 1;
        console.log(playerMaxCount)
        let playerCurrentCount = $('#other-players').children().length;
        if (playerCurrentCount < playerMaxCount) {
            $('#other-players').append(`
         <div class="mt-5">
                    <div class="lg:hidden font-medium">نفر ${parseInt(playerCurrentCount) + 2}</div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="relative bg-white border border-border-1 rounded-xl shadow-13 h-12 px-2 has-[input:placeholder-shown]:!border-border-1 has-[input:valid]:border-green-500 has-[input:invalid]:border-[#FD7013]">
                            <input class="peer focus:outline-0 w-full h-full bg-transparent" type="text" name="player-${parseInt(playerCurrentCount) + 2}-full-name" min="5" max="30" placeholder="" pattern="(?=.*[\u0600-\u06FF])[\u0600-\u06FF\\s]{5,}" minlength="5">
                            <div class="hidden peer-placeholder-shown:block absolute top-1/2 -translate-y-1/2 right-2 pointer-events-none">
                                <span class="font-medium text-[#AAAAAA]">نام و نام خانوادگی</span>
                            </div>
                            <p class="hidden peer-invalid:block peer-placeholder-shown:!hidden absolute -bottom-6 text-xs text-red-600">*لطفا نام و نام خانوادگی بازیکن را به فارسی وارد کنید.(حداقل 5 کاراکتر)</p>
                        </div>
                        <div class="relative bg-white border border-border-1 rounded-xl shadow-13 h-12 px-2 has-[input:placeholder-shown]:!border-border-1 has-[input:valid]:border-green-500 has-[input:invalid]:border-[#FD7013]">
                            <input class="peer focus:outline-0 w-full h-full bg-transparent" type="tel" name="player-${parseInt(playerCurrentCount) + 2}-phone-number" pattern="^(09\\d{9}|(\\+98)?9\\d{9})$" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" placeholder="" min="12" max="12">
                            <div class="hidden peer-placeholder-shown:block absolute top-1/2 -translate-y-1/2 right-2 pointer-events-none">
                                <span class="font-medium text-[#AAAAAA]">تلفن همراه</span>
                            </div>
                            <p class="hidden peer-invalid:block peer-placeholder-shown:!hidden absolute -bottom-6 text-xs text-red-600">*لطفا شماره موبایل بازیکن را صحیح (09121111111) وارد کنید.</p>
                        </div>
                    </div>
                </div>
        `);
            if (playerCurrentCount === (playerMaxCount) - 1) {
                $(this).parent().remove();
            }
        }
    })
    $('#order-off-code-btn').click(function () {
        let btnStatus = $(this).attr('data-btn-status');
        let codeInput = $('#order-off-code');
        if (btnStatus == 'submit-code'){
            if (codeInput.val() == '') {
                $('#offerCode-message').attr('data-offerCode','error');
                $('#offerCode-message').text('* لطفا کد صحیح وارد نمائید').fadeIn().delay(2000).fadeOut();
            } else {
                codeInput.attr('readonly', true);
                $('#offerCode-message').attr('data-offerCode','success');
                $('#offerCode-message').text('✔ کد تخفیف با موفقیت ثبت شد.').fadeIn().delay(2000).fadeOut();
                $(this).attr('data-btn-status','edit-code');
                $(this).text('ویرایش')
            }
        }
        if (btnStatus == 'edit-code'){
            codeInput.attr('readonly', false);
            $(this).attr('data-btn-status','submit-code');
            $(this).text('ثبت کد')
        }
    })
    // Event callbacks
    const handleInput = ({target}) => {
        if (!target.value.length) { return target.value = null; }

        const inputLength = target.value.length;
        let currentIndex = Number(target.dataset.numberCodeInput);

        if (inputLength > 1) {
            const inputValues = target.value.split('');

            inputValues.forEach((value, valueIndex) => {
                const nextValueIndex = currentIndex + valueIndex;

                if (nextValueIndex >= numberCodeInputs.length) { return; }

                numberCodeInputs[nextValueIndex].value = value;
            });

            currentIndex += inputValues.length - 2;
        }

        const nextIndex = currentIndex + 1;

        if(nextIndex < numberCodeInputs.length) {
            numberCodeInputs[nextIndex].focus();
        }
    }

    const handleKeyDown = e => {
        const {code, target} = e;

        const currentIndex = Number(target.dataset.numberCodeInput);
        const previousIndex = currentIndex - 1;
        const nextIndex = currentIndex + 1;

        const hasPreviousIndex = previousIndex >= 0;
        const hasNextIndex = nextIndex <= numberCodeInputs.length - 1

        switch(code) {
            case 'ArrowLeft':
            case 'ArrowUp':
                if (hasPreviousIndex) {
                    numberCodeInputs[previousIndex].focus();
                }
                e.preventDefault();
                break;

            case 'ArrowRight':
            case 'ArrowDown':
                if (hasNextIndex) {
                    numberCodeInputs[nextIndex].focus();
                }
                e.preventDefault();
                break;
            case 'Backspace':
                if (!e.target.value.length && hasPreviousIndex) {
                    numberCodeInputs[previousIndex].value = null;
                    numberCodeInputs[previousIndex].focus();
                }
                break;
            default:
                break;
        }
    }

// Event listeners
    numberCodeForm.addEventListener('input', handleInput);
    numberCodeForm.addEventListener('keydown', handleKeyDown);
    function startTimer() {
        let $resetTime = $('#otp-reset-time');
        let count = parseInt($resetTime.attr('data-count'));
        let interval = setInterval(function() {
            if (count > 0) {
                count--;
                $resetTime.text(count);
            } else {
                clearInterval(interval);
                $('#reset-btn-container').empty().append(`  
                    <button id="otp-reset" class="bg-primary-500 hover:bg-primary-600 text-white h-12 rounded-lg mx-auto w-30 flex items-center justify-center">  
                        ارسال مجدد کد  
                    </button>  
                `);
            }
        }, 1000);
    }

    $(document).on('click', '#otp-reset', function() {
        let $container = $('#reset-btn-container');
        $('#otp-reset').empty().append(`  
            <span>  
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">  
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>  
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>  
                </svg>  
            </span>  
        `);

        setTimeout(function() {
            $container.empty().append(`  
                <div class="text-tgreen-1 text-sm text-center font-extrabold mb-3">✔ کد با موفقیت برای شما ارسال شد</div>  
            `);
            setTimeout(function (){
                $container.empty().append(`  
                <div class="text-[#889BAD] text-center font-extrabold mb-3">زمان باقیمانده برای درخواست مجدد</div>  
                <div class="text-center text-primary-500">  
                    <span id="otp-reset-time" data-count="10">10</span>  
                    <span>ثانیه</span>  
                </div>  
            `);
                startTimer();
            },2000)
        }, 3000);
    });

    // شروع تایمر هنگام بارگذاری صفحه
    startTimer();
});
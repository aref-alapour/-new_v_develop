<?php get_header(); ?>

    <!-- start-section-1 --------------------------------------------->
      <section
              class="container mx-auto mb-d70 px-4 sm:px-6 md:px-8 lg:mt-d80 h-full" >
        <div
                class="relative flex flex-col lg:flex-row items-center justify-center lg:justify-around pb-d50 lg-[35px] px-d26 lg:gap-x-20"
                style="
            border-radius: 0px 0px 50px 50px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.86) 32.96%, #F5F5F5 83.33%);
            box-shadow: 3px -30px 30px 0px rgba(0, 0, 0, 0.01) inset;
          ">

          <div  class="flex flex-col  justify-start max-w-d521 shrink-0">
            <h2 class="text-4xl lg:text-5xl text-salte-700 mb-5 font-fat-yekanbakh !leading-17 text-center">
              زمین بازی عوض شد...!
            </h2>
            <p class="text-lg font-bold leading-10 text-justify">
              اسکیپ‌زوم جدید از راه رسید! با ویژگی‌هایی که تجربه‌ت از بازی‌های گروهی رو شیرین‌تر و مهیج‌تر می‌کنه. تیمت رو بساز، از تخفیف‌ها لذت ببر و یه ماجراجویی بی‌نظیر داشته باش. حالا وقتشه که وارد زمین بازی جدیدت بشی!
            </p>
          </div>
          <div class="lg:h-d340 mt-d30 lg:mt-d0 lg:w-1/2 aspect-[6/4]">
            <div class="rounded-2xl relative overflow-hidden  h-full">
              <img src="<?= Theme_URL ?>assets/images/banners/home-banners/01-lg.avif" alt="" class="w-full h-full object-cover object-right">
              <button id="hero-video-play" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                <svg width="83" height="83" viewBox="0 0 83 83" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="41.4615" cy="41.4615" r="41.4615" fill="#F21543" fill-opacity="0.36"/>
                  <circle cx="41.4616" cy="41.4605" r="33.0855" fill="#F21543"/>
                  <g filter="url(#filter0_d_23590_9180)">
                    <path d="M52.8252 38.9226C53.42 39.2431 53.9176 39.7216 54.2645 40.3067C54.6115 40.8918 54.7948 41.5616 54.7948 42.2442C54.7948 42.9268 54.6115 43.5966 54.2645 44.1817C53.9176 44.7668 53.42 45.2453 52.8252 45.5658L36.9586 54.3083C34.4038 55.7175 31.2656 53.8854 31.2656 50.988V33.5017C31.2656 30.603 34.4038 28.7721 36.9586 30.1788L52.8252 38.9226Z" fill="white"/>
                  </g>
                  <defs>
                    <filter id="filter0_d_23590_9180" x="31.2656" y="29.6953" width="23.5292" height="26.1016" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                      <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                      <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                      <feOffset dy="1"/>
                      <feComposite in2="hardAlpha" operator="out"/>
                      <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                      <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_23590_9180"/>
                      <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_23590_9180" result="shape"/>
                    </filter>
                  </defs>
                </svg>

              </button>
            </div>
            <div id="hero-video" class="max-w-[80%] shadow h-[80%] fixed z-[200] aspect-[3/5] top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 hidden">
              <button type="button" id="hero-video-close" class="absolute left-0 text-primary-500 bg-white w-6 h-6 rounded-full flex items-center justify-center text-3xl leading-16 cursor-pointer z-10"><span class="mt-1">×</span></button>
              <video id="hero-video-media" class="rounded-2xl" src="https://escapezoom.ir/videos/showup/Moarefi.mp4" type="video/mp4" controls />
            </div>
          </div>

          <div class="absolute -bottom-8 -z-10 md:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" width="324" height="103" viewBox="0 0 324 103" fill="none">
              <g filter="url(#filter0_f_704_2)">
                <path d="M78.6457 89.423C49.3683 98.3269 10 76.7771 10 46.1757C10 26.1964 26.1964 10 46.1757 10L277.001 10C297.435 10 314 26.5648 314 46.9986C314 77.8498 275.278 99.3395 245.802 90.2309C222.494 83.0283 193.768 77.5 160.5 77.5C128.488 77.5 101.018 82.619 78.6457 89.423Z" fill="url(#paint0_linear_704_2)" fill-opacity="0.15"/>
              </g>
              <defs>
                <filter id="filter0_f_704_2" x="0" y="0" width="324" height="102.341" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                  <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                  <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                  <feGaussianBlur stdDeviation="5" result="effect1_foregroundBlur_704_2"/>
                </filter>
                <linearGradient id="paint0_linear_704_2" x1="159.678" y1="76.8048" x2="158.67" y2="169.548" gradientUnits="userSpaceOnUse">
                  <stop/>
                  <stop offset="1" stop-opacity="0"/>
                </linearGradient>
              </defs>
            </svg>
          </div>
          <div class="absolute -bottom-12 -z-10 max-md:hidden w-full flex">
            <svg xmlns="http://www.w3.org/2000/svg" width="1198" height="124" viewBox="0 0 1198 124" fill="none">
              <g filter="url(#filter0_f_76_38)">
                <path d="M69.0643 109.064C40.1413 112.694 14 90.2157 14 61.0659C14 35.0721 35.0721 14 61.0659 14L1136.88 14C1162.9 14 1184 35.0961 1184 61.1195C1184 90.263 1157.9 112.744 1128.98 109.141C1042.35 98.3496 856.935 80 597.628 80C338.936 80 155.145 98.2627 69.0643 109.064Z" fill="url(#paint0_linear_76_38)" fill-opacity="0.15"/>
              </g>
              <defs>
                <filter id="filter0_f_76_38" x="0" y="0" width="1198" height="123.521" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                  <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                  <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                  <feGaussianBlur stdDeviation="7" result="effect1_foregroundBlur_76_38"/>
                </filter>
                <linearGradient id="paint0_linear_76_38" x1="590.063" y1="74.7868" x2="589.846" y2="159.184" gradientUnits="userSpaceOnUse">
                  <stop/>
                  <stop offset="1" stop-opacity="0"/>
                </linearGradient>
              </defs>
            </svg>
          </div>
        </div>
      </section>
      <!-- end-section-1 ---------------------------------------------->

      <!-- start-section-2--------------------------------------------->
      <section class="container mx-auto mb-d70 px-4 sm:px-6 md:px-8">
        <h2 class="text-4xl lg:text-5xl text-salte-700 mb-5 font-fat-yekanbakh !leading-17 text-center">
          مهم ترین ویژگی‌های جدید
        </h2>

        <div class="flex flex-col lg:flex-row justify-around  lg:items-center mt-d60 gap-d37 px-d15">

          <div class="flex flex-col justify-center items-start gap-d37 lg:gap-d59 ">
            <div class="flex gap-d18">
              <img src="<?= Theme_URL ?>assets/images/ShowUp/tick.png" alt="tick" class="w-d31 h-d26"/>
              <p class="text-19 lg:text-2xl font-black">نقشه‌بازی‌ایران</p>
            </div>

            <div class="flex gap-d18">
              <img src="<?= Theme_URL ?>assets/images/ShowUp/tick.png" alt="tick" class="w-d31 h-d26"/>
              <p class="text-19 lg:text-2xl font-black">ساخت کالکشن پیشنهادی بازی</p>
            </div>
          </div>

          <div class="flex flex-col justify-center items-start gap-d37 lg:gap-d59">
            <div class="flex gap-d18">
              <img src="<?= Theme_URL ?>assets/images/ShowUp/tick.png" alt="tick" class="w-d31 h-d26"/>
              <p class="text-19 lg:text-2xl font-black">سطح بندی‌ و‌ قدرت متفاوت کاربران</p>
            </div>

            <div class="flex gap-d18">
              <img src="<?= Theme_URL ?>assets/images/ShowUp/tick.png" alt="tick" class="w-d31 h-d26"/>
              <p class="text-19 lg:text-2xl font-black">سانس یاب حرفه‌ای</p>
            </div>
          </div>

          <div class="flex flex-col justify-center items-start gap-d37 lg:gap-d59">
            <div class="flex gap-d18">
              <img src="<?= Theme_URL ?>assets/images/ShowUp/tick.png" alt="tick" class="w-d31 h-d26"/>
              <p class="text-19 lg:text-2xl font-black">دعوت از پلیرها برای بازی</p>
            </div>

            <div class="flex gap-d18">
              <img src="<?= Theme_URL ?>assets/images/ShowUp/tick.png" alt="tick" class="w-d31 h-d26"/>
              <p class="text-19 lg:text-2xl font-black">پنل کاربری پیشرفته</p>
            </div>
          </div>

        </div>


      </section>

      <section class="container mx-auto mb-d70 px-4 sm:px-6 md:px-8">
        <div class="flex flex-col lg:flex-row items-center lg:justify-between mt-d50 lg:mt-d111">
          <p class="text-lg lg:text-2xl font-bold text-center">بازی از همین جا شروع شده، آماده ای؟!</p>

          <button
                  class="rounded-2xl text-nowrap text-center bg-primary-500 text-slate-100 border border-primary-500 h-d52 w-d250 lg:w-d169 px-3 mt-d14 lg:mt-d0 md:px-5 flex justify-center items-center text-xl"
                  style="box-shadow: 0px 2px 0px 0px #CA5608;"
          >
            <a href="https://escapezoom.ir/panel/" class="flex justify-center items-center gap-2.5 font-fat-yekanbakh">
              بزن بریم
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="10" viewBox="0 0 17 10" fill="none">
                <path
                        d="M8 1.49235L8 8.50765C8.00043 8.78461 7.9164 9.05602 7.75763 9.29046C7.59886 9.5249 7.37185 9.7128 7.10287 9.83239C6.78372 9.97239 6.42864 10.0263 6.0781 9.98801C5.72756 9.94971 5.39565 9.82073 5.12021 9.61577L0.544835 6.10812C0.373814 5.97046 0.236656 5.80028 0.142653 5.60909C0.0486498 5.4179 -2.0937e-07 5.21018 -2.18557e-07 5C-2.27744e-07 4.78982 0.0486497 4.5821 0.142653 4.39091C0.236655 4.19972 0.373814 4.02954 0.544834 3.89188L5.12021 0.38423C5.39565 0.179266 5.72756 0.0502841 6.0781 0.0119836C6.42864 -0.026316 6.78372 0.0276076 7.10287 0.167608C7.37185 0.287203 7.59886 0.475096 7.75763 0.70954C7.9164 0.943985 8.00043 1.21539 8 1.49235Z"
                        fill="white"
                />
                <rect x="9" y="3.5" width="8" height="3" fill="white" />
              </svg>
            </a>
          </button>
        </div>
      </section>
      <!-- end-section-2 ---------------------------------------------->

      <!-- start-section-3--------------------------------------------->
      <section class="lg:container mx-auto mb-d70 mt-d100 sm:px-6 md:px-8">
        <div class="lg:mx-d200">
          <div class="swiper mySwiperShowUp relative">

            <button class="hidden lg:flex absolute left-0 top-1/2 -translate-y-1/2 cursor-pointer touch-manipulation appearance-none z-10 swiper-next" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="110" viewBox="0 0 30 110" fill="none">
                <path d="M0.0204732 2.48141C0.0201149 2.38872 0.020115 2.29596 0.0204734 2.20312L0.0204732 2.48141C0.0498197 10.0726 2.48342 17.1339 7.35969 23.1342C9.301 25.5233 11.4569 27.7233 13.7383 29.813C15.5574 31.4804 17.4063 33.1193 19.2353 34.7772C20.9885 36.3689 22.7152 37.9921 24.2834 39.754C25.4224 41.0337 26.469 42.3795 27.3604 43.8325C28.4995 45.6953 29.315 47.6778 29.7243 49.8021C29.9654 51.0503 30.0182 52.311 29.9951 53.5781C29.9224 57.2468 28.7471 60.5941 26.7926 63.7176C25.5347 65.7317 24.049 67.585 22.4048 69.3153C20.7772 71.0268 19.1099 72.7004 17.4261 74.3583C15.0853 76.6655 12.7148 78.9411 10.5292 81.3838C8.17187 84.0156 6.04898 86.8018 4.34208 89.8655C2.55925 93.0646 1.31787 96.4465 0.627844 100.008C0.38353 101.275 0.225058 102.552 0.106203 103.838C0.0630606 104.295 0.0362771 104.75 0.0203995 105.205L0.0203971 108.588C0.0251887 109.057 0.0271745 109.526 0.0203961 109.997L0.0203971 108.588C0.00883619 107.459 -0.0190582 106.335 0.0203995 105.205L0.0204732 2.48141Z" fill="#BFCBD9"/>
                <path d="M1.41734 10.1386C0.426873 6.79128 0.0271109 3.4702 0.0205078 0V103.066C0.0205078 100.775 0.162752 98.2962 0.542429 96.0331C1.22915 91.9357 2.62241 88.0747 4.7486 84.4532C6.2376 81.9191 8.03033 79.5898 9.99805 77.3804C11.7809 75.379 13.6991 73.4941 15.5942 71.5904C16.9973 70.1815 18.3906 68.7601 19.7541 67.3134C21.0483 65.9391 22.2303 64.4735 23.2835 62.9165C25.2776 59.9695 26.5718 56.7861 26.9218 53.2718C27.1892 50.6022 26.9812 47.9798 26.1657 45.4079C25.4097 43.0093 24.1881 40.8314 22.7024 38.7795C21.5733 37.2194 20.3253 35.7411 18.9882 34.3354C17.2218 32.479 15.4324 30.6477 13.6496 28.8102C11.2757 26.3643 8.97788 23.8586 6.97384 21.1197C4.50428 17.7409 2.59269 14.1099 1.41734 10.1386Z" fill="white"/>
                <path d="M12.6658 56L10.174 52.59C9.91715 52.2386 9.91715 51.7614 10.174 51.41L12.6658 48" stroke="#889BAD" stroke-width="3" stroke-linecap="round"/>
              </svg>
            </button>

            <div class="swiper-wrapper">
                <div class="swiper-slide">
                <!-- <img src="./assets/images/ShowUp/slider-2.png" alt="Slide 3" class="w-d300 h-d533 rounded-20" />
                <img src="./assets/images/ShowUp/icon-player.svg" alt="Play Icon" class="absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2" style="width: 83px; height: 83px;" /> -->
                <video controls style="border-radius:20px" >
                  <source src="https://escapezoom.ir/videos/showup/takhfif ok.mp4" type="video/mp4">
                </video>
              </div>
                <div class="swiper-slide">
                <!-- <img src="./assets/images/ShowUp/slider-2.png" alt="Slide 4" class="w-d300 h-d533 rounded-20" />
                <img src="./assets/images/ShowUp/icon-player.svg" alt="Play Icon" class="absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2" style="width: 83px; height: 83px;" /> -->
                <video controls style="border-radius:20px" >
                  <source src="https://escapezoom.ir/videos/showup/map ok.mp4" type="video/mp4">
                </video>
              </div>
              <div class="swiper-slide">
                <!-- <img src="./assets/images/ShowUp/slider-1.png" alt="Slide 1" class="w-d300 h-d533 rounded-20" />
                <img src="./assets/images/ShowUp/icon-player.svg" alt="Play Icon" class="absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2" style="width: 83px; height: 83px;" /> -->
                <video controls style="border-radius:20px" >
                  <source src="https://escapezoom.ir/videos/showup/TV ok.mp4" type="video/mp4">
                </video>
              </div>
              <div class="swiper-slide">
                <!-- <img src="./assets/images/ShowUp/slider3.png" alt="Slide 2" class="w-d300 h-d533 rounded-20" />
                <img src="./assets/images/ShowUp/icon-player.svg" alt="Play Icon" class="absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2" style="width: 83px; height: 83px;" /> -->
                <video controls style="border-radius:20px" >
                  <source src="https://escapezoom.ir/videos/showup/davat kon2_2.mp4" type="video/mp4">
                </video>
              </div>
              <div class="swiper-slide">
                <!-- <img src="./assets/images/ShowUp/slider3.png" alt="Slide 2" class="w-d300 h-d533 rounded-20" />
                <img src="./assets/images/ShowUp/icon-player.svg" alt="Play Icon" class="absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2" style="width: 83px; height: 83px;" /> -->
                <video controls style="border-radius:20px" >
                  <source src="https://escapezoom.ir/videos/showup/sathe karbari+profile.mp4" type="video/mp4">
                </video>
              </div>
             
            </div>

            <button class="hidden lg:flex absolute right-0 top-1/2 -translate-y-1/2 cursor-pointer touch-manipulation appearance-none z-10 swiper-prev" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="110" viewBox="0 0 30 110" fill="none">
                <path d="M29.9795 2.48141C29.9799 2.38872 29.9799 2.29596 29.9795 2.20312L29.9795 2.48141C29.9502 10.0726 27.5166 17.1339 22.6403 23.1342C20.699 25.5233 18.5431 27.7233 16.2617 29.813C14.4426 31.4804 12.5937 33.1193 10.7647 34.7772C9.01154 36.3689 7.28484 37.9921 5.7166 39.754C4.57757 41.0337 3.53098 42.3795 2.63956 43.8325C1.50053 45.6953 0.685049 47.6778 0.275658 49.8021C0.0346451 51.0503 -0.018177 52.311 0.0049324 53.5781C0.0775681 57.2468 1.25292 60.5941 3.20743 63.7176C4.46532 65.7317 5.95101 67.585 7.59518 69.3153C9.22284 71.0268 10.8901 72.7004 12.5739 74.3583C14.9147 76.6655 17.2852 78.9411 19.4708 81.3838C21.8281 84.0156 23.951 86.8018 25.6579 89.8655C27.4408 93.0646 28.6821 96.4465 29.3722 100.008C29.6165 101.275 29.7749 102.552 29.8938 103.838C29.9369 104.295 29.9637 104.75 29.9796 105.205L29.9796 108.588C29.9748 109.057 29.9728 109.526 29.9796 109.997L29.9796 108.588C29.9912 107.459 30.0191 106.335 29.9796 105.205L29.9795 2.48141Z" fill="#BFCBD9"/>
                <path d="M28.5827 10.1386C29.5731 6.79128 29.9729 3.4702 29.9795 0V103.066C29.9795 100.775 29.8372 98.2962 29.4576 96.0331C28.7708 91.9357 27.3776 88.0747 25.2514 84.4532C23.7624 81.9191 21.9697 79.5898 20.0019 77.3804C18.2191 75.379 16.3009 73.4941 14.4058 71.5904C13.0027 70.1815 11.6094 68.7601 10.2459 67.3134C8.95168 65.9391 7.76972 64.4735 6.71653 62.9165C4.7224 59.9695 3.42821 56.7861 3.07824 53.2718C2.81082 50.6022 3.01881 47.9798 3.83429 45.4079C4.59034 43.0093 5.81191 40.8314 7.29761 38.7795C8.42673 37.2194 9.67472 35.7411 11.0118 34.3354C12.7782 32.479 14.5676 30.6477 16.3504 28.8102C18.7243 26.3643 21.0221 23.8586 23.0262 21.1197C25.4957 17.7409 27.4073 14.1099 28.5827 10.1386Z" fill="white"/>
                <path d="M17.334 56L19.8258 52.59C20.0826 52.2386 20.0826 51.7614 19.8258 51.41L17.334 48" stroke="#889BAD" stroke-width="3" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
        </div>
      </section>
      <!-- end-section-3 ---------------------------------------------->

      <!-- start-section-4--------------------------------------------->
     <!-- <section class="container mx-auto mb-d40 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-center lg:mx-d200 mb-d70">
          <p class="text-4xl lg:text-5xl text-salte-700 mb-5 font-fat-yekanbakh !leading-17 text-center mt-d40 mb-d20">
            شکار تخفیف‌های هیجان انگیز
          </p>

          <p class="text-base lg:text-2xl font-bold text-center px-d20" style="line-height: 50px;">

            کد تخفیف 500 هزار تومنی برای 70 نفر
            <br/>
            برات یه مسابقه طراحی کردیم تا بازی توی سایت جدید رو با تخفیف شروع کنی!
            <br/>
            هر روز یه کد تخفیف رو توی سایت مخفی می‌کنیم و باید بگردی، پیداش کنی و جزو 10 نفر اولی باشی که کد رو توی قسمت «اعتبار تخفیف» حساب کاربری وارد ‌می‌کنن تا ثبت بشه. برای پیدا کردن سریع‌تر کدها، از صفحه اینستاگرام اسکیپ زوم هینت بگیر. روزی 10 برنده داریم و این کار تا 7 روز و برنده شدن 70 نفر ادامه داره!

          </p>
        </div>
        <div class="mt-auto flex items-center justify-center" dir="ltr">
          <div class="flex items-center justify-between gap-1">
            <span class="product-timer hours-tens flex h-d48 lg:h-d100 w-d42 lg:w-d86 items-center justify-center rounded-lg bg-secondary-600 text-40 lg:text-60 font-black text-white shadow-5">5</span>
            <span class="product-timer hours-units flex h-d48 lg:h-d100 w-d42 lg:w-d86 items-center justify-center rounded-lg bg-secondary-600 text-40 lg:text-60 font-black text-white shadow-5">2</span>
          </div>
          <span class="px-1 text-2xl lg:text-60 text-slate-150">:</span>
          <div class="flex items-center justify-between gap-1">
            <span class="product-timer minutes-tens flex h-d48 lg:h-d100 w-d42 lg:w-d86 items-center justify-center rounded-lg bg-secondary-600 text-40 lg:text-60 font-black text-white shadow-5">5</span>
            <span class="product-timer minutes-units flex h-d48 lg:h-d100 w-d42 lg:w-d86 items-center justify-center rounded-lg bg-secondary-600 text-40 lg:text-60 font-black text-white shadow-5">2</span>
          </div>
          <span class="px-1 text-2xl lg:text-60 text-slate-150">:</span>
          <div class="flex items-center justify-between gap-1">
            <span class="product-timer seconds-tens flex h-d48 lg:h-d100 w-d42 lg:w-d86 items-center justify-center rounded-lg bg-secondary-600 text-40 lg:text-60 font-black text-white shadow-5">5</span>
            <span class="product-timer seconds-units flex h-d48 lg:h-d100 w-d42 lg:w-d86 items-center justify-center rounded-lg bg-secondary-600 text-40 lg:text-60 font-black text-white shadow-5">2</span>
          </div>
        </div>
        <h2 class="text-4xl lg:text-5xl text-salte-700 mb-5 font-fat-yekanbakh !leading-17 text-center mt-d40">
          مانده تا شروع مسابقه
        </h2>
        <div style="max-width: 400px;margin: 0 auto;">
            <video controls style="border-radius:20px" >
                  <source src="https://escapezoom.ir/videos/showup/story mosabegheh.mp4" type="video/mp4">
            </video>
        </div>
      </section>-->
      <!-- end-section-4 ---------------------------------------------->
    <!-- end-section-5----------------------------------------------->
    <section class="container mx-auto pb-d70 px-4 sm:px-6 md:px-8">
        <a href="https://www.instagram.com/escapezoom.ir">
            <img src="<?= Theme_URL ?>assets/images/ShowUp/banner-mobile.png" class="flex lg:hidden"/>
            <img src="<?= Theme_URL ?>assets/images/ShowUp/banner-desktop.png" class="hidden lg:flex"/>
        </a>
    </section>
<div id="overlay" class="bg-black/20 fixed top-0 right-0 left-0 bottom-0 z-[110] transition-all"
     style="display: none;"></div>

<?php get_footer(); ?>
<script>
    jQuery(document).ready(function ($) {
        // swiper-video-shoup-----------------------------------
        const swiper4 = new Swiper(".mySwiperShowUp", {
            loop: true,
            slidesPerView: 3,
            spaceBetween: "30px",
            centeredSlides: true,

            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },

            on: {
                init: function () {
                    const activeSlide = this.slides[this.activeIndex];
                    activeSlide.style.transform = "scale(1)";
                    activeSlide.style.opacity = "1";
                },
                slideChange: function () {
                    this.slides.forEach((slide) => {
                        slide.style.transform = "scale(0.8)";
                        slide.style.opacity = "0.7";
                    });
                    const activeSlide = this.slides[this.activeIndex];
                    activeSlide.style.transform = "scale(1)";
                    activeSlide.style.opacity = "1";
                },
            },

            breakpoints: {
                320: {
                    slidesPerView: 1.5,
                    spaceBetween: "-80px",
                },
                550: {
                    slidesPerView: 2.5,
                    spaceBetween: "-60px",
                },
                1025: {
                    slidesPerView: 2.8,
                    spaceBetween: "30px"
                },
            },

            navigation: {
                nextEl: ".swiper-next",
                prevEl: ".swiper-prev",
            },
        });

        $('#hero-video-play').on('click',function (){
        $("#overlay").show().animate({scale:'1'},300);
        $('#hero-video').toggleClass('hidden');
      })
    $('#hero-video-close').on('click',function (){
      $("#overlay").animate({scale: 0}, 300, function(){$(this).hide();});
      $('#hero-video-media')[0].pause();
      $('#hero-video').toggleClass('hidden');
    })
    $('#overlay').on('click',function (){
      $("#overlay").animate({scale: 0}, 300, function(){$(this).hide();});
      $('#hero-video-media')[0].pause();
      $('#hero-video').toggleClass('hidden');
    })
    // تاریخ لانچ سایت (۸ مارس ۲۰۲۵ ساعت ۱۲:۰۰:۰۰)
  const launchDate = new Date("2025-03-10T18:00:00").getTime();


  // مدت زمان فرصت (۴۸ ساعت به میلی‌ثانیه)
  const opportunityDuration = 42 * 60 * 60 * 1000;

  // زمان پایان فرصت
  const endTime = launchDate + opportunityDuration;

  // تابع برای به‌روزرسانی تایمر
  function updateTimer() {
    // زمان فعلی
    const now = new Date().getTime();

    // زمان باقی‌مانده تا پایان فرصت
    let remainingTime = endTime - now;

    // اگر زمان باقی‌مانده منفی باشد، تایمر به پایان رسیده است
    if (remainingTime <= 0) {
      clearInterval(timerInterval);
      $(".hours-tens").text(0);
      $(".hours-units").text(0);
      $(".minutes-tens").text(0);
      $(".minutes-units").text(0);
      $(".seconds-tens").text(0);
      $(".seconds-units").text(0);
      return;
    }

    // تبدیل زمان باقی‌مانده به ساعت، دقیقه و ثانیه
    const hours = Math.floor(remainingTime / (1000 * 60 * 60));
    const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

    // تبدیل اعداد به فرمت دو رقمی (مثلاً 05 به جای 5)
    const hoursTens = Math.floor(hours / 10);
    const hoursUnits = hours % 10;
    const minutesTens = Math.floor(minutes / 10);
    const minutesUnits = minutes % 10;
    const secondsTens = Math.floor(seconds / 10);
    const secondsUnits = seconds % 10;

    // به‌روزرسانی مقادیر در صفحه
    $(".hours-tens").text(hoursTens);
    $(".hours-units").text(hoursUnits);
    $(".minutes-tens").text(minutesTens);
    $(".minutes-units").text(minutesUnits);
    $(".seconds-tens").text(secondsTens);
    $(".seconds-units").text(secondsUnits);
  }

  // تنظیم تایمر برای به‌روزرسانی هر ثانیه
  let timerInterval = setInterval(updateTimer, 1000);

  // اجرای تابع updateTimer بلافاصله پس از لود صفحه
  updateTimer();
  const videos = document.querySelectorAll('.swiper-slide video');

// برای هر ویدئو یک event listener اضافه کنید
videos.forEach(video => {
    video.addEventListener('play', () => {
        // وقتی یک ویدئو پخش شد، بقیه ویدئو‌ها را متوقف کنید
        videos.forEach(otherVideo => {
            if (otherVideo !== video && !otherVideo.paused) {
                otherVideo.pause();
            }
        });
    });
});
    })
</script>

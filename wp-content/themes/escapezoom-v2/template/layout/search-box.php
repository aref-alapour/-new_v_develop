<div class="flex flex-wrap items-center justify-between gap-2.5">

    <!-- Search -->
    <div class="max-xl:hidden 2xl:ml-2.5">
        <div id="lg-search-container" class="relative">
            <form id="lg-search-form">
                <div class="relative">
                    <div class="relative">
                        <input id="lg-search" class="text-gray-900 block w-full border bg-white shadow-[0px_4px_4px_0px_rgba(0,0,0,.1)] p-1.5 text-sm shadow-13 outline-none placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 py-2 px-6 pl-12 h-11.5 rounded-lg shadow-none placeholder:text-2xs 2xl:min-w-62 4xl:min-w-75" placeholder="نام بازی خود را وارد نمایید ..." data-path="search" type="text" value="" name="s">
                        <div class="absolute left-0 top-0 flex h-full items-center pl-6">
                            <button type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="18" viewBox="0 0 24 24">
                                    <circle cx="11.767" cy="11.767" r="8.989" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
                                    <path d="M18.018 18.485L21.542 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Search -->

    <a class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-[#ccc] disabled:cursor-not-allowed disabled:shadow-none bg-white text-gray-900 border border-gray-100 hover:bg-button-gradient focus-visible:bg-button-gradient p-2 shadow-wrapper h-11.5 min-w-12 rounded-lg hover:shadow-none" href="<?php echo get_permalink(wc_get_page_id('myaccount')) ?>">
        <span class="truncate">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="18" viewBox="0 0 24 24">
                <circle cx="11.579" cy="7.278" r="4.778" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
                <path clip-rule="evenodd" d="M4 18.701a2.215 2.215 0 01.22-.97c.457-.915 1.748-1.4 2.819-1.62a16.778 16.778 0 012.343-.33 25.04 25.04 0 014.385 0c.787.056 1.57.166 2.343.33 1.07.22 2.361.659 2.82 1.62a2.27 2.27 0 010 1.95c-.459.96-1.75 1.4-2.82 1.61-.772.172-1.555.286-2.343.34-1.188.1-2.38.118-3.57.054-.275 0-.54 0-.815-.055a15.417 15.417 0 01-2.334-.338c-1.08-.21-2.361-.65-2.828-1.611A2.28 2.28 0 014 18.7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                </path>
            </svg>
        </span>
    </a>

</div>
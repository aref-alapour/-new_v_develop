<div class="lg:col-span-8 2xl:col-span-9">
    <section class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none min-h-full py-10 max-lg:border-0 max-lg:py-0">
        <form class="w-full">
            <div class="flex w-full justify-between items-center">
                <h3 class="text-xl">تیکت های پشتیبانی</h3>
                <button type="submit" onclick="window.history.go(-1); return false;" class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-[#ccc] disabled:cursor-not-allowed disabled:shadow-none bg-breserve text-slate-200 shadow-13 border border-gray-50 h-12 min-w-16 px-9 py-2 rounded-xl">
                    <span class="truncate">بازگشت</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="19" viewBox="0 0 12 19" fill="none">
                        <path d="M0.800228 10.8572L6.98719 17.4633C7.44915 17.8738 8.06045 18.0975 8.69219 18.0871C9.32394 18.0767 9.92676 17.8331 10.3735 17.4076C10.8203 16.9821 11.0761 16.408 11.087 15.8064C11.098 15.2048 10.8631 14.6226 10.432 14.1827L5.9675 9.21689L10.432 4.81787C10.8631 4.37793 11.0979 3.79577 11.087 3.19413C11.0761 2.5925 10.8203 2.01841 10.3735 1.59292C9.92676 1.16744 9.32394 0.923825 8.69219 0.913432C8.06045 0.90304 7.44915 1.12668 6.98719 1.53721L0.800228 7.57656C0.343784 8.01179 0.087402 8.60176 0.087402 9.21689C0.087402 9.83201 0.343784 10.422 0.800228 10.8572Z" fill="#FD7013"/>
                    </svg>
                </button>
            </div>
            <div class="line my-8"></div>
            <div class="mb-4 flex gap-4 max-sm:flex-col sm:mb-8 sm:gap-8">
                <div class="w-full">
                    <div class="relative">
                        <input id="title" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl" placeholder="عنوان تیکت را وارد نمایید" data-path="title" type="text" value="" name="title">
                    </div>
                </div>
                <div class="w-full">
                    <select name="" class="select-box">
                        <option>دپارتمان را انتخاب نمایید</option>
                        <option value="مالی">مالی</option>
                        <option value="فنی">فنی</option>
                        <option value="شکایات">شکایات</option>
                        <option value="تبلیغات">تبلیغات</option>
                    </select>
                </div>
            </div>
            <div class="">
                <div class="relative">
                    <textarea id="body" name="body" rows="10" class="text-gray-900 block w-full rounded-2xl border-0 p-6 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500" placeholder="برای ادامه تیکت پیام جدید خود را اینجا بنویسید ..." data-path="body"></textarea>
                </div>
            </div>
            <div class="mt-4 flex justify-between gap-4 max-sm:flex-col sm:mt-8 sm:gap-8">
                <div class="max-w-full">
                    <div class="max-w-full">
                        <label for="id_0f4a2" class="text-gray-900 relative flex w-full items-center justify-between rounded-2xl border-0 p-1.5 shadow-13 outline-none ring-1 ring-inset ring-gray-100 hover:shadow-none hover:ring-2 hover:ring-inset hover:ring-primary-500 h-16 py-2 px-6">
                            <div class="flex items-center gap-5 sm:max-w-48">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="20" viewBox="0 0 12 20" fill="none">
                                    <path d="M1 6.59199V13.3337C1 14.6597 1.52678 15.9315 2.46447 16.8692C3.40215 17.8069 4.67392 18.3337 6 18.3337C7.32608 18.3337 8.59785 17.8069 9.53553 16.8692C10.4732 15.9315 11 14.6597 11 13.3337V5.00033C11 4.11627 10.6488 3.26842 10.0237 2.6433C9.39857 2.01818 8.55072 1.66699 7.66667 1.66699C6.78261 1.66699 5.93477 2.01818 5.30964 2.6433C4.68452 3.26842 4.33333 4.11627 4.33333 5.00033V12.652C4.33333 12.8709 4.37644 13.0876 4.4602 13.2898C4.54396 13.492 4.66672 13.6757 4.82149 13.8305C4.97625 13.9853 5.15999 14.108 5.36219 14.1918C5.5644 14.2755 5.78113 14.3187 6 14.3187C6.44203 14.3187 6.86595 14.1431 7.17851 13.8305C7.49107 13.5179 7.66667 13.094 7.66667 12.652V6.66699" stroke="#FD7013" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="truncate text-slate-200">فایل ضمیمه خود را اینجا انتخاب
                                    کنید
                                </div>
                            </div>
                        </label>
                        <input id="id_0f4a2" class="hidden" accept=".jpg, .jpeg, .png, .svg, .pdf, .webp" multiple="" data-path="attachment" type="file" value="" name="attachment">
                    </div>
                </div>
                <button type="submit" class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-[#ccc] disabled:cursor-not-allowed disabled:shadow-none bg-primaryColor text-white shadow-13 shadow-primary-3 h-16 min-w-16 px-9 py-2 rounded-xl">
                    <span class="truncate">ارسال</span>
                </button>
            </div>
        </form>
    </section>
</div>
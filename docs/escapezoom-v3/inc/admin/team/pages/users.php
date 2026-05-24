<?php
// User Management Page
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-base font-extrabold lg:text-2xl">مدیریت کاربران</h1>
    <button id="addUserBtn" class="bg-primary-2 hover:bg-primary-deep text-white px-6 py-3 rounded-lg text-base font-yekan-bold cursor-pointer">
        افزودن کاربر
    </button>
</div>
<!-- Search and Filter -->
<div class="flex items-center gap-4 mb-6">
    <div class="relative w-full max-w-md">
        <input id="userSearch" type="text" placeholder="جستجو کاربر (شماره موبایل، نام، نام خانوادگی، آیدی، نام بازی)"
            class="w-full h-d58 border border-slate-105 bg-white rounded-xl outline-none px-6 py-5 text-xs font-bold text-navyBlue" />
        <svg id="searchIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="absolute top-5 left-6 cursor-pointer hover:opacity-70 transition-opacity">
            <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#0F172B" />
        </svg>
        <div id="searchLoading" class="absolute top-5 right-6 hidden">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-2"></div>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <select id="roleFilter" class="h-d58 px-4 py-2 rounded-lg text-xs font-bold border border-slate-105 bg-white text-navyBlue outline-none">
            <option value="">همه نقش‌ها</option>
            <option value="customer">مشتری</option>
            <option value="sans_manager">مدیر سانس</option>
            <option value="poshtiban">پشتیبان</option>
            <option value="shopist">شاپ منیجر</option>
            <option value="compiler">مجموعه‌دار</option>
        </select>
        <select id="levelFilter" class="h-d58 px-4 py-2 rounded-lg text-xs font-bold border border-slate-105 bg-white text-navyBlue outline-none">
            <option value="">همه سطوح</option>
            <option value="1">تازه وارد</option>
            <option value="2">نوپا</option>
            <option value="3">با تجربه</option>
            <option value="4">کارکشته</option>
        </select>
        <select id="itemsPerPage" class="h-d58 px-4 py-2 rounded-lg text-xs font-bold border border-slate-105 bg-white text-navyBlue outline-none">
            <option value="20">20 در صفحه</option>
            <option value="50" selected>50 در صفحه</option>
            <option value="100">100 در صفحه</option>
        </select>
    </div>
</div>
<!-- Total Users Count -->
<div class="mb-4">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="text-sm font-yekan-bold text-blue-800">کل کاربران: <span id="totalUsersCount">-</span></span>
            </div>
            <div class="text-sm text-blue-600">
                <span id="currentPageInfo">صفحه 1 از 1</span>
            </div>
        </div>
    </div>
</div>
<!-- Users Table -->
<div class="bg-white rounded-xl border border-slate-105 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-white">
                <tr>
                    <th class="px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue">شماره موبایل</th>
                    <th class="px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue">نام و نام خانوادگی</th>
                    <th class="px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue">سطح کاربر</th>
                    <th class="px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue">نقش</th>
                    <th class="px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue">پروفایل</th>
                    <th class="px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue">شماره شبا</th>
                    <th class="px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue">بازی‌های مدیریتی</th>
                    <th class="px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue">عملیات</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                <!-- Loading skeleton -->
                <?php for ($i = 0; $i < 10; $i++) { ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Pagination -->
<div id="pagination" class="flex justify-center items-center gap-2 mt-6">
    <!-- Pagination will be loaded here -->
</div>
<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 z-50 backdrop-blur-sm bg-white/30" style="display: none;">
    <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-xl absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-yekan-bold text-navyBlue">افزودن کاربر جدید</h2>
            <button id="closeAddUserModal" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="addUserForm">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-yekan-bold text-navyBlue mb-2">شماره موبایل *</label>
                    <input type="tel" id="userPhone" name="phone" required
                        class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                        placeholder="09123456789">
                </div>
                <div>
                    <label class="block text-sm font-yekan-bold text-navyBlue mb-2">نام *</label>
                    <input type="text" id="userFirstName" name="first_name" required
                        class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                        placeholder="نام">
                </div>
                <div>
                    <label class="block text-sm font-yekan-bold text-navyBlue mb-2">نام خانوادگی *</label>
                    <input type="text" id="userLastName" name="last_name" required
                        class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                        placeholder="نام خانوادگی">
                </div>
                <div>
                    <label class="block text-sm font-yekan-bold text-navyBlue mb-2">نقش *</label>
                    <select id="userRole" name="role" required
                        class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2">
                        <option value="">انتخاب نقش</option>
                        <option value="customer">مشتری</option>
                        <option value="sans_manager">مدیر سانس</option>
                        <option value="poshtiban">پشتیبان</option>
                        <option value="shopist">شاپ منیجر</option>
                        <option value="compiler">مجموعه‌دار</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 flex items-center justify-center gap-x-2 bg-primary-2 hover:bg-primary-deep text-white py-3 rounded-lg text-base font-yekan-bold">
                    افزودن کاربر
                </button>
                <button type="button" id="cancelAddUser" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 rounded-lg text-base font-yekan-bold">
                    انصراف
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Edit User Modal -->
<div id="editUserModal" class="fixed inset-0 z-50 backdrop-blur-sm bg-white/30" style="display: none;">
    <div class="bg-white rounded-lg w-full max-w-2xl p-6 shadow-xl absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 max-h-modal overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-yekan-bold text-navyBlue">ویرایش کاربر</h2>
            <button id="closeEditUserModal" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Tabs (only for accounting and administrator) -->
        <div id="editUserTabs" class="mb-6 border-b border-gray-200" style="display: none;">
            <div class="flex gap-4">
                <button class="edit-tab-btn active px-4 py-2 text-sm font-yekan-bold border-b-2 border-primary-2 text-primary-2" data-tab="user">
                    ویرایش کاربر
                </button>
                <button class="edit-tab-btn px-4 py-2 text-sm font-yekan-bold text-gray-500 hover:text-gray-700" data-tab="games">
                    ویرایش بازی
                </button>
            </div>
        </div>

        <!-- Tab Content: Edit User -->
        <div id="editUserTabContent" class="tab-content">
            <form id="editUserForm">
                <input type="hidden" id="editUserId" name="user_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-yekan-bold text-navyBlue mb-2">شماره موبایل *</label>
                        <input type="tel" id="editUserPhone" name="phone" required
                            class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                            placeholder="09123456789">
                    </div>
                    <div>
                        <label class="block text-sm font-yekan-bold text-navyBlue mb-2">نام *</label>
                        <input type="text" id="editUserFirstName" name="first_name" required
                            class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                            placeholder="نام">
                    </div>
                    <div>
                        <label class="block text-sm font-yekan-bold text-navyBlue mb-2">نام خانوادگی *</label>
                        <input type="text" id="editUserLastName" name="last_name" required
                            class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                            placeholder="نام خانوادگی">
                    </div>
                    <div>
                        <label class="block text-sm font-yekan-bold text-navyBlue mb-2">نقش *</label>
                        <select id="editUserRole" name="role" required
                            class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2">
                            <option value="">انتخاب نقش</option>
                            <option value="customer">مشتری</option>
                            <option value="sans_manager">مدیر سانس</option>
                            <option value="poshtiban">پشتیبان</option>
                            <option value="shopist">شاپ منیجر</option>
                            <option value="compiler">مجموعه‌دار</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="flex-1 flex items-center justify-center gap-x-2 bg-primary-2 hover:bg-primary-deep text-white py-3 rounded-lg text-base font-yekan-bold">
                        به‌روزرسانی کاربر
                    </button>
                    <button type="button" id="cancelEditUser" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 rounded-lg text-base font-yekan-bold">
                        انصراف
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab Content: Edit Games -->
        <div id="editGamesTabContent" class="tab-content" style="display: none;">
            <div class="space-y-6">
                <!-- Owner Games Section -->
                <div id="ownerGamesSection" class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-base font-yekan-bold text-navyBlue mb-4">بازی‌هایی که کاربر مالک است (مجموعه‌دار)</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-yekan-bold text-navyBlue mb-2">جستجوی بازی</label>
                            <div class="relative">
                                <input type="text" id="ownerGameSearchInput" 
                                    class="w-full h-12 border border-edge rounded-xl p-3 pr-10 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                                    placeholder="نام بازی را جستجو کنید...">
                                <div id="ownerGameSearchLoading" class="absolute left-3 top-1/2 transform -translate-y-1/2 hidden">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-2"></div>
                                </div>
                            </div>
                            <div id="ownerGameSearchResults" class="mt-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg bg-white hidden" style="z-index: 10; position: relative;"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-yekan-bold text-navyBlue mb-2">بازی‌های انتخاب شده</label>
                            <div id="selectedOwnerGames" class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 min-h-d100">
                                <div class="text-sm text-gray-400 text-center py-4">هیچ بازی‌ای انتخاب نشده است</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sans Manager Games Section -->
                <div id="sansManagerGamesSection" class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-base font-yekan-bold text-navyBlue mb-4">بازی‌هایی که کاربر مدیر سانس است</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-yekan-bold text-navyBlue mb-2">جستجوی بازی</label>
                            <div class="relative">
                                <input type="text" id="sansGameSearchInput" 
                                    class="w-full h-12 border border-edge rounded-xl p-3 pr-10 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                                    placeholder="نام بازی را جستجو کنید...">
                                <div id="sansGameSearchLoading" class="absolute left-3 top-1/2 transform -translate-y-1/2 hidden">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-2"></div>
                                </div>
                            </div>
                            <div id="sansGameSearchResults" class="mt-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg bg-white hidden" style="z-index: 10; position: relative;"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-yekan-bold text-navyBlue mb-2">بازی‌های انتخاب شده</label>
                            <div id="selectedSansGames" class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 min-h-d100">
                                <div class="text-sm text-gray-400 text-center py-4">هیچ بازی‌ای انتخاب نشده است</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" id="saveGamesConnection" class="flex-1 flex items-center justify-center gap-x-2 bg-primary-2 hover:bg-primary-deep text-white py-3 rounded-lg text-base font-yekan-bold">
                        ذخیره تغییرات بازی‌ها
                    </button>
                    <button type="button" id="cancelEditGames" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 rounded-lg text-base font-yekan-bold">
                        انصراف
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Banking Information Modal -->
<div id="bankingInfoModal" class="fixed inset-0 z-50 backdrop-blur-sm bg-white/30" style="display: none;">
    <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-xl absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-yekan-bold text-navyBlue" id="bankingModalTitle">اطلاعات بانکی کاربر</h2>
            <button id="closeBankingModal" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="bankingInfoForm">
            <input type="hidden" id="bankingUserId" name="user_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-yekan-bold text-navyBlue mb-2">کد ملی صاحب حساب *</label>
                    <input type="text" id="bankingIdentityCard" name="identity_card" required
                        class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                        placeholder="کد ملی صاحب حساب" maxlength="10" pattern="[0-9]{10}" title="کد ملی باید 10 رقم باشد">
                </div>
                <div>
                    <label class="block text-sm font-yekan-bold text-navyBlue mb-2">نام و نام خانوادگی صاحب حساب *</label>
                    <input type="text" id="bankingOwnerName" name="owner_name" required
                        class="w-full h-12 border border-edge rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                        placeholder="نام و نام خانوادگی صاحب حساب">
                </div>
                <div>
                    <label class="block text-sm font-yekan-bold text-navyBlue mb-2">شماره شبا *</label>
                    <div class="relative">
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-sm font-yekan-bold text-gray-500">IR</span>
                        <input type="text" id="bankingShaba" name="shaba" required maxlength="24"
                            class="w-full h-12 border border-edge rounded-xl p-3 pr-8 text-sm outline-none focus:ring-2 focus:ring-primary-2"
                            placeholder="شماره شبا (بدون IR)" pattern="[0-9]{24}" title="شماره شبا باید 24 رقم باشد">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">شماره شبا باید 24 رقم باشد (بدون IR)</p>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 flex items-center justify-center gap-x-2 bg-primary-2 hover:bg-primary-deep text-white py-3 rounded-lg text-base font-yekan-bold">
                    به‌روزرسانی اطلاعات بانکی
                </button>
                <button type="button" id="cancelBanking" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 rounded-lg text-base font-yekan-bold">
                    انصراف
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Game Selection Confirmation Modal -->
<div id="gameSelectionModal" class="fixed inset-0 z-50 backdrop-blur-sm bg-white/30" style="display: none;">
    <div class="bg-white rounded-lg w-full max-w-2xl p-6 shadow-xl absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 max-h-[80vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-yekan-bold text-navyBlue" id="gameSelectionModalTitle">تأیید تغییر بازی‌ها</h2>
            <button id="closeGameSelectionModal" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-4" id="gameSelectionModalMessage">
                برخی از بازی‌های انتخاب شده قبلاً مالک یا مدیر سانس دارند. لطفاً بازی‌هایی که می‌خواهید تغییر دهید را انتخاب کنید:
            </p>
            <div id="gameSelectionList" class="space-y-2 max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-3">
                <!-- Games with checkboxes will be added here -->
            </div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="button" id="confirmGameSelection" class="flex-1 flex items-center justify-center gap-x-2 bg-primary-2 hover:bg-primary-deep text-white py-3 rounded-lg text-base font-yekan-bold">
                تأیید و ادامه
            </button>
            <button type="button" id="cancelGameSelection" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 rounded-lg text-base font-yekan-bold">
                انصراف
            </button>
        </div>
    </div>
</div>

<!-- Create Password Modal -->
<div id="createPasswordModal" class="fixed inset-0 z-50 backdrop-blur-sm bg-white/30" style="display: none;">
    <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-xl absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-yekan-bold text-navyBlue">ایجاد رمز ثابت</h2>
            <button id="closeCreatePasswordModal" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-2">آیا مطمئن هستید که می‌خواهید برای این کاربر رمز ثابت ایجاد کنید؟</p>
            <p class="text-sm text-gray-800 font-bold mb-4">شماره موبایل: <span id="modalUserPhone" class="text-primary-2"></span></p>
            <p class="text-xs text-gray-500 bg-yellow-50 p-3 rounded border border-yellow-200">
                رمز عبور برابر با شماره موبایل کاربر بدون صفر اول خواهد بود (مثال: 09123456789 -> 9123456789).
            </p>
        </div>
        <form id="createPasswordForm">
            <input type="hidden" id="createPasswordUserId" name="user_id">
            <input type="hidden" id="createPasswordPhone" name="phone">
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 flex items-center justify-center gap-x-2 bg-primary-2 hover:bg-primary-deep text-white py-3 rounded-lg text-base font-yekan-bold">
                    ایجاد رمز
                </button>
                <button type="button" id="cancelCreatePassword" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 rounded-lg text-base font-yekan-bold">
                    انصراف
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        let currentPage = 1;
        let searchTerm = '';
        let roleFilter = '';
        let levelFilter = '';
        let itemsPerPage = 50;
        // Load users data
        function loadUsers(page = 1) {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'users_get',
                    'page': page,
                    'search': searchTerm,
                    'role': roleFilter,
                    'level': levelFilter,
                    'items_per_page': itemsPerPage
                },
                beforeSend: function() {
                    $("#usersTableBody").html(function() {
                        let out = '';
                        for (let i = 0; i < 10; i++) {
                            out += '<tr><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td></tr>';
                        }
                        return out;
                    });
                },
                success: function(response) {
                    // Hide loading indicator
                    $("#searchLoading").hide();
                    $("#searchIcon").show();
                    if (response.success) {
                        $("#usersTableBody").html(response.data.html);
                        $("#totalUsersCount").text(response.data.total_users);
                        $("#currentPageInfo").text(`صفحه ${response.data.current_page} از ${response.data.total_pages}`);
                        $("#pagination").html(response.data.pagination);
                    } else {
                        $("#usersTableBody").html('<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">خطا در بارگذاری داده‌ها</td></tr>');
                    }
                },
                error: function() {
                    // Hide loading indicator on error
                    $("#searchLoading").hide();
                    $("#searchIcon").show();
                    $("#usersTableBody").html('<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">خطا در بارگذاری داده‌ها</td></tr>');
                }
            });
        }
        // Initial load
        loadUsers();
        // Search functionality - only on click or enter
        function performSearch() {
            searchTerm = $('#userSearch').val();
            currentPage = 1;
            // Show loading indicator
            $("#searchIcon").hide();
            $("#searchLoading").show();
            $("#usersTableBody").html(function() {
                let out = '';
                for (let i = 0; i < 5; i++) {
                    out += '<tr><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td><td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td></tr>';
                }
                return out;
            });
            loadUsers(currentPage);
        }
        // Search on click of search icon
        $('#searchIcon').on('click', function() {
            performSearch();
        });
        // Search on Enter key press
        $('#userSearch').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                performSearch();
            }
        });
        // Clear search on empty input
        $('#userSearch').on('input', function() {
            if ($(this).val().trim() === '') {
                searchTerm = '';
                currentPage = 1;
                loadUsers(currentPage);
            }
        });
        // Filter functionality
        $('#roleFilter, #levelFilter, #itemsPerPage').on('change', function() {
            roleFilter = $('#roleFilter').val();
            levelFilter = $('#levelFilter').val();
            itemsPerPage = parseInt($('#itemsPerPage').val());
            currentPage = 1;
            loadUsers(currentPage);
        });
        // Pagination
        $(document).on('click', '.pagination-link', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            const page = href.split('?page=')[1];
            if (page) {
                currentPage = parseInt(page);
                loadUsers(currentPage);
            }
        });
        // Add User Modal
        $('#addUserBtn').on('click', function() {
            // Check if current user can create users
            const currentUserRole = "<?php echo !empty(wp_get_current_user()->roles) ? wp_get_current_user()->roles[0] : 'subscriber'; ?>";
            const canCreateUser = ['administrator', 'poshtiban', 'supervisor', 'accounting'].includes(currentUserRole);
            if (!canCreateUser) {
                Swal.fire({
                    title: 'دسترسی محدود',
                    text: 'فقط نقش‌های پشتیبان، مدیر و شاپ منیجر و حسابدار می‌توانند کاربر جدید ایجاد کنند.',
                    icon: 'warning',
                    confirmButtonText: 'باشه'
                });
                return;
            }
            // Update role dropdown with allowed roles based on current user role
            let allowedRoles = [];
            if (currentUserRole === 'accounting') {
                allowedRoles = [{
                        value: 'customer',
                        text: 'مشتری'
                    },
                    {
                        value: 'sans_manager',
                        text: 'مدیر سانس'
                    },
                    {
                        value: 'poshtiban',
                        text: 'پشتیبان'
                    },
                    {
                        value: 'supervisor',
                        text: 'شاپ منیجر'
                    },
                    {
                        value: 'compiler',
                        text: 'مجموعه‌دار'
                    },
                    {
                        value: 'accounting',
                        text: 'حسابدار'
                    }
                ];
            } else {
                allowedRoles = [{
                        value: 'customer',
                        text: 'مشتری'
                    },
                    {
                        value: 'sans_manager',
                        text: 'مدیر سانس'
                    },
                    {
                        value: 'poshtiban',
                        text: 'پشتیبان'
                    },
                    {
                        value: 'supervisor',
                        text: 'شاپ منیجر'
                    },
                    {
                        value: 'compiler',
                        text: 'مجموعه‌دار'
                    }
                ];
            }
            const roleSelect = $('#userRole');
            roleSelect.empty();
            roleSelect.append('<option value="">انتخاب نقش</option>');
            allowedRoles.forEach(function(role) {
                roleSelect.append('<option value="' + role.value + '">' + role.text + '</option>');
            });
            $('#addUserModal').show();
        });
        $('#closeAddUserModal, #cancelAddUser').on('click', function() {
            $('#addUserModal').hide();
            $('#addUserForm')[0].reset();
        });
        // Add User Form
        $('#addUserForm').on('submit', function(e) {
            e.preventDefault();
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.text();
            // Validate phone number format
            const phone = $('#userPhone').val();
            if (!/^09\d{9}$/.test(phone)) {
                Swal.fire({
                    title: 'خطا!',
                    text: 'شماره موبایل باید با 09 شروع شده و 11 رقم باشد.',
                    icon: 'error',
                    confirmButtonText: 'باشه'
                });
                return;
            }
            // Show loading state
            $submitBtn.prop('disabled', true);
            $submitBtn.html('<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>منتظر بمانید...');
            const formData = {
                'action': 'team_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                'callback': 'users_add',
                'phone': $('#userPhone').val(),
                'first_name': $('#userFirstName').val(),
                'last_name': $('#userLastName').val(),
                'role': $('#userRole').val()
            };
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: formData,
                success: function(response) {
                    // Reset button state
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text(originalText);
                    if (response.success) {
                        Swal.fire({
                            title: 'موفق!',
                            text: 'کاربر با موفقیت افزوده شد.',
                            icon: 'success',
                            confirmButtonText: 'باشه'
                        });
                        $('#addUserModal').hide();
                        $('#addUserForm')[0].reset();
                        loadUsers(currentPage);
                    } else {
                        Swal.fire({
                            title: 'خطا!',
                            text: response.data || 'خطایی رخ داده است.',
                            icon: 'error',
                            confirmButtonText: 'باشه'
                        });
                    }
                },
                error: function() {
                    // Reset button state on error
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text(originalText);
                    Swal.fire({
                        title: 'خطا!',
                        text: 'خطایی رخ داده است.',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            });
        });
        // Edit User Modal
        $('#closeEditUserModal, #cancelEditUser, #cancelEditGames').on('click', function() {
            $('#editUserModal').hide();
            $('#editUserForm')[0].reset();
            // Reset tabs
            switchEditTab('user');
            // Clear games tab
            resetGamesSelection();
            $('#ownerGameSearchInput').val('');
            $('#ownerGameSearchResults').addClass('hidden').empty();
            $('#sansGameSearchInput').val('');
            $('#sansGameSearchResults').addClass('hidden').empty();
        });
        
        // Save games connection
        $('#saveGamesConnection').on('click', function() {
            const userId = $('#editUserId').val();
            const userRole = $('#editUserRole').val();
            
            if (!userId) return;
            
            // Validate role restrictions
            if (selectedOwnerGames.length > 0 && userRole !== 'compiler') {
                Swal.fire({
                    title: 'خطا!',
                    text: 'فقط کاربرانی با نقش مجموعه‌دار می‌توانند به عنوان مالک بازی انتخاب شوند.',
                    icon: 'error',
                    confirmButtonText: 'باشه'
                });
                return;
            }
            
            if (selectedSansGames.length > 0 && !['sans_manager', 'compiler'].includes(userRole)) {
                Swal.fire({
                    title: 'خطا!',
                    text: 'فقط کاربرانی با نقش مدیر سانس یا مجموعه‌دار می‌توانند به عنوان مدیر سانس بازی انتخاب شوند.',
                    icon: 'error',
                    confirmButtonText: 'باشه'
                });
                return;
            }
            
            // Find games that need confirmation
            const gamesNeedingConfirmation = [];
            
            selectedOwnerGames.forEach(function(game) {
                if (game.current_owner_id && game.current_owner_id != userId) {
                    gamesNeedingConfirmation.push({
                        game_id: game.id,
                        game_title: game.title,
                        type: 'owner',
                        current_user_id: game.current_owner_id,
                        current_user_name: game.current_owner_name
                    });
                }
            });
            
            selectedSansGames.forEach(function(game) {
                if (game.current_sans_manager_id && game.current_sans_manager_id != userId) {
                    gamesNeedingConfirmation.push({
                        game_id: game.id,
                        game_title: game.title,
                        type: 'sans',
                        current_user_id: game.current_sans_manager_id,
                        current_user_name: game.current_sans_manager_name
                    });
                }
            });
            
            if (gamesNeedingConfirmation.length > 0) {
                // Show confirmation modal
                showGameSelectionModal(gamesNeedingConfirmation, function(confirmedGames) {
                    saveGamesChanges(userId, confirmedGames);
                });
            } else {
                // No confirmation needed, save directly
                saveGamesChanges(userId, []);
            }
        });
        
        // Show game selection confirmation modal
        function showGameSelectionModal(games, callback) {
            let html = '<div class="space-y-2">';
            games.forEach(function(game) {
                const roleName = game.type === 'owner' ? 'مالک' : 'مدیر سانس';
                html += '<div class="flex items-center p-3 border border-gray-200 rounded">';
                html += '<input type="checkbox" class="game-confirmation-checkbox mr-3" data-game-id="' + game.game_id + '" data-type="' + game.type + '" checked>';
                html += '<div class="flex-1">';
                html += '<div class="font-bold text-sm">' + escapeHtml(game.game_title) + '</div>';
                html += '<div class="text-xs text-gray-600">' + roleName + ' فعلی: ' + escapeHtml(game.current_user_name) + '</div>';
                html += '</div>';
                html += '</div>';
            });
            html += '</div>';
            
            $('#gameSelectionList').html(html);
            $('#gameSelectionModal').show();
            
            // Store callback
            $('#gameSelectionModal').data('callback', callback);
        }
        
        // Confirm game selection
        $('#confirmGameSelection').on('click', function() {
            const callback = $('#gameSelectionModal').data('callback');
            const confirmedGames = [];
            
            $('.game-confirmation-checkbox:checked').each(function() {
                confirmedGames.push({
                    game_id: parseInt($(this).data('game-id')),
                    type: $(this).data('type')
                });
            });
            
            $('#gameSelectionModal').hide();
            if (callback) {
                callback(confirmedGames);
            }
        });
        
        $('#closeGameSelectionModal, #cancelGameSelection').on('click', function() {
            $('#gameSelectionModal').hide();
        });
        
        // Save games changes
        function saveGamesChanges(userId, confirmedGames) {
            const ownerGameIds = selectedOwnerGames.map(g => g.id);
            const sansGameIds = selectedSansGames.map(g => g.id);
            
            const $saveBtn = $('#saveGamesConnection');
            const originalText = $saveBtn.html();
            
            // Show loading spinner
            $saveBtn.prop('disabled', true);
            $saveBtn.html('<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>در حال ذخیره...');
            
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'user_games_batch_update',
                    'user_id': userId,
                    'owner_games': JSON.stringify(ownerGameIds),
                    'sans_games': JSON.stringify(sansGameIds),
                    'confirmed_games': JSON.stringify(confirmedGames)
                },
                success: function(response) {
                    // Reset button state
                    $saveBtn.prop('disabled', false);
                    $saveBtn.html(originalText);
                    
                    if (response.success) {
                        Swal.fire({
                            title: 'موفق!',
                            text: 'تغییرات با موفقیت ذخیره شد.',
                            icon: 'success',
                            confirmButtonText: 'باشه'
                        }).then(() => {
                            loadUsers(currentPage);
                            $('#editUserModal').hide();
                            resetGamesSelection();
                        });
                    } else {
                        Swal.fire({
                            title: 'خطا!',
                            text: response.data || 'خطایی رخ داده است.',
                            icon: 'error',
                            confirmButtonText: 'باشه'
                        });
                    }
                },
                error: function() {
                    // Reset button state
                    $saveBtn.prop('disabled', false);
                    $saveBtn.html(originalText);
                    
                    Swal.fire({
                        title: 'خطا!',
                        text: 'خطایی در ارتباط با سرور رخ داده است.',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            });
        }
        // Function to get allowed roles for current user (for editing)
        function getAllowedRoles() {
            const currentUserRole = "<?php echo !empty(wp_get_current_user()->roles) ? wp_get_current_user()->roles[0] : 'subscriber'; ?>";
            let allowedRoles = [];
            if (currentUserRole === 'administrator') {
                allowedRoles = [{
                        value: 'poshtiban',
                        text: 'پشتیبان'
                    },
                    {
                        value: 'shopist',
                        text: 'شاپ منیجر'
                    },
                    {
                        value: 'accounting',
                        text: 'حسابدار'
                    },
                    {
                        value: 'contentist',
                        text: 'محتواگذار'
                    },
                    {
                        value: 'subscriber',
                        text: 'مشترک'
                    },
                    {
                        value: 'customer',
                        text: 'مشتری'
                    },
                    {
                        value: 'compiler',
                        text: 'مجموعه‌دار'
                    },
                    {
                        value: 'sans_manager',
                        text: 'مدیر سانس'
                    },
                    {
                        value: 'supervisor',
                        text: 'ویرایشگر سئو'
                    },
                    {
                        value: 'wpseo_manager',
                        text: 'مدیر سئو'
                    },
                    {
                        value: 'commentchi',
                        text: 'کامنتچی'
                    },
                    {
                        value: 'translator',
                        text: 'مترجم'
                    },
                    {
                        value: 'seller',
                        text: 'فروشنده'
                    },
                    {
                        value: 'shop_manager',
                        text: 'مدیر فروشگاه'
                    },
                    {
                        value: 'contributor',
                        text: 'مشارکت‌کننده'
                    },
                    {
                        value: 'author',
                        text: 'نویسنده'
                    },
                    {
                        value: 'editor',
                        text: 'ویرایشگر'
                    }
                ];
            } else if (currentUserRole === 'supervisor') {
                allowedRoles = [{
                        value: 'customer',
                        text: 'مشتری'
                    },
                    {
                        value: 'poshtiban',
                        text: 'پشتیبان'
                    },
                    {
                        value: 'compiler',
                        text: 'مجموعه‌دار'
                    },
                    {
                        value: 'sans_manager',
                        text: 'مدیر سانس'
                    }
                ];
            } else if (currentUserRole === 'poshtiban') {
                allowedRoles = [{
                        value: 'customer',
                        text: 'مشتری'
                    },
                    {
                        value: 'poshtiban',
                        text: 'پشتیبان'
                    },
                    {
                        value: 'sans_manager',
                        text: 'مدیر سانس'
                    }
                ];
            }else if (currentUserRole === 'accounting') {
                allowedRoles = [{
                        value: 'customer',
                        text: 'مشتری'
                    },
                    {
                        value: 'accounting',
                        text: 'حسابدار'
                    },
                    {
                        value: 'compiler',
                        text: 'مجموعه‌دار'
                    },
                    {
                        value: 'sans_manager',
                        text: 'مدیر سانس'
                    },
                    {
                        value: 'poshtiban',
                        text: 'پشتیبان'
                    },
                    {
                        value: 'supervisor',
                        text: 'شاپ منیجر'
                    }
                ];
            }
            return allowedRoles;
        }
        // Edit User Button Click
        $(document).on('click', '.edit-user-btn', function() {
            const $button = $(this);
            const originalText = $button.text();
            // Get user data from button attributes
            const userId = $button.data('user-id');
            const userPhone = $button.data('user-phone');
            const userFirstName = $button.data('user-first-name') || '';
            const userLastName = $button.data('user-last-name') || '';
            const userRole = $button.data('user-role');
            // Show loading state
            $button.prop('disabled', true);
            $button.html('<svg class="animate-spin h-4 w-4 text-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');
            // Simulate a small delay for better UX
            setTimeout(function() {
                // Reset button state
                $button.prop('disabled', false);
                $button.text(originalText);
                // Fill form with user data
                $('#editUserId').val(userId);
                $('#editUserPhone').val(userPhone);
                $('#editUserFirstName').val(userFirstName);
                $('#editUserLastName').val(userLastName);
                // Update role dropdown with allowed roles
                const allowedRoles = getAllowedRoles();
                const roleSelect = $('#editUserRole');
                roleSelect.empty();
                roleSelect.append('<option value="">انتخاب نقش</option>');
                allowedRoles.forEach(function(role) {
                    roleSelect.append('<option value="' + role.value + '">' + role.text + '</option>');
                });
                $('#editUserRole').val(userRole);
                
                // Check if user is accounting or administrator - show tabs
                const currentUserRole = "<?php echo !empty(wp_get_current_user()->roles) ? wp_get_current_user()->roles[0] : 'subscriber'; ?>";
                if (['accounting', 'administrator'].includes(currentUserRole)) {
                    // Update games section visibility based on user role
                    updateGamesSectionVisibility(userRole);
                    
                    // Show tabs if user has compiler or sans_manager role
                    if (userRole === 'compiler' || userRole === 'sans_manager') {
                        $('#editUserTabs').show();
                    } else {
                        $('#editUserTabs').hide();
                        $('#editUserTabContent').show();
                        $('#editGamesTabContent').hide();
                    }
                    
                    // Switch to user tab by default
                    switchEditTab('user');
                    // Reset games selection
                    resetGamesSelection();
                    currentEditingUserId = userId;
                    // Load connected games only if games tab is visible
                    if (userRole === 'compiler' || userRole === 'sans_manager') {
                        loadUserGames(userId);
                    }
                } else {
                    $('#editUserTabs').hide();
                    $('#editUserTabContent').show();
                    $('#editGamesTabContent').hide();
                }
                
                $('#editUserModal').show();
            }, 300);
        });
        
        // Update games section visibility based on user role
        function updateGamesSectionVisibility(userRole) {
            if (userRole === 'compiler') {
                // Compiler can have both owner and sans_manager games
                $('#ownerGamesSection').show();
                $('#sansManagerGamesSection').show();
                // Show games tab
                $('.edit-tab-btn[data-tab="games"]').show();
            } else if (userRole === 'sans_manager') {
                // Sans manager can only have sans_manager games
                $('#ownerGamesSection').hide();
                $('#sansManagerGamesSection').show();
                // Show games tab
                $('.edit-tab-btn[data-tab="games"]').show();
            } else {
                // Other roles: hide games tab completely
                $('#ownerGamesSection').hide();
                $('#sansManagerGamesSection').hide();
                // Hide games tab button
                $('.edit-tab-btn[data-tab="games"]').hide();
                // If currently on games tab, switch to user tab
                if ($('#editGamesTabContent').is(':visible')) {
                    switchEditTab('user');
                }
            }
        }
        
        // Tab switching
        function switchEditTab(tab) {
            $('.edit-tab-btn').removeClass('active border-primary-2 text-primary-2').addClass('text-gray-500');
            $('.edit-tab-btn[data-tab="' + tab + '"]').addClass('active border-primary-2 text-primary-2').removeClass('text-gray-500');
            
            if (tab === 'user') {
                $('#editUserTabContent').show();
                $('#editGamesTabContent').hide();
            } else {
                $('#editUserTabContent').hide();
                $('#editGamesTabContent').show();
            }
        }
        
        // Update games section when role changes in dropdown
        $('#editUserRole').on('change', function() {
            const selectedRole = $(this).val();
            updateGamesSectionVisibility(selectedRole);
            
            // If role changed to non-compiler/non-sans_manager, clear selected games
            if (selectedRole !== 'compiler' && selectedRole !== 'sans_manager') {
                resetGamesSelection();
                // Hide games tab if role doesn't support games
                if (!$('.edit-tab-btn[data-tab="games"]').is(':visible')) {
                    $('#editUserTabs').hide();
                    $('#editUserTabContent').show();
                    $('#editGamesTabContent').hide();
                }
            } else {
                // Show tabs if role supports games
                $('#editUserTabs').show();
            }
        });
        
        $(document).on('click', '.edit-tab-btn', function() {
            const tab = $(this).data('tab');
            switchEditTab(tab);
        });
        
        // Store selected games
        let selectedOwnerGames = [];
        let selectedSansGames = [];
        let currentEditingUserId = null;
        
        // Reset games selection
        function resetGamesSelection() {
            selectedOwnerGames = [];
            selectedSansGames = [];
            updateSelectedGamesDisplay();
        }
        
        // Update selected games display
        function updateSelectedGamesDisplay() {
            // Owner games
            const ownerContainer = $('#selectedOwnerGames');
            if (selectedOwnerGames.length === 0) {
                ownerContainer.html('<div class="text-sm text-gray-400 text-center py-4">هیچ بازی‌ای انتخاب نشده است</div>');
            } else {
                let html = '<div class="space-y-2">';
                selectedOwnerGames.forEach(function(game) {
                    html += '<div class="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200" data-game-id="' + game.id + '">';
                    html += '<div class="flex-1">';
                    html += '<div class="font-bold text-sm">' + escapeHtml(game.title) + '</div>';
                    if (game.current_owner_name) {
                        html += '<div class="text-xs text-orange-600">مالک فعلی: ' + escapeHtml(game.current_owner_name) + '</div>';
                    }
                    html += '</div>';
                    html += '<button class="remove-selected-game text-red-600 hover:text-red-800 text-sm font-yekan-bold px-2 py-1" data-game-id="' + game.id + '" data-type="owner">حذف</button>';
                    html += '</div>';
                });
                html += '</div>';
                ownerContainer.html(html);
            }
            
            // Sans manager games
            const sansContainer = $('#selectedSansGames');
            if (selectedSansGames.length === 0) {
                sansContainer.html('<div class="text-sm text-gray-400 text-center py-4">هیچ بازی‌ای انتخاب نشده است</div>');
            } else {
                let html = '<div class="space-y-2">';
                selectedSansGames.forEach(function(game) {
                    html += '<div class="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200" data-game-id="' + game.id + '">';
                    html += '<div class="flex-1">';
                    html += '<div class="font-bold text-sm">' + escapeHtml(game.title) + '</div>';
                    if (game.current_sans_manager_name) {
                        html += '<div class="text-xs text-orange-600">مدیر سانس فعلی: ' + escapeHtml(game.current_sans_manager_name) + '</div>';
                    }
                    html += '</div>';
                    html += '<button class="remove-selected-game text-red-600 hover:text-red-800 text-sm font-yekan-bold px-2 py-1" data-game-id="' + game.id + '" data-type="sans">حذف</button>';
                    html += '</div>';
                });
                html += '</div>';
                sansContainer.html(html);
            }
        }
        
        // Remove selected game
        $(document).on('click', '.remove-selected-game', function() {
            const gameId = parseInt($(this).data('game-id'));
            const type = $(this).data('type');
            
            if (type === 'owner') {
                selectedOwnerGames = selectedOwnerGames.filter(g => g.id !== gameId);
            } else {
                selectedSansGames = selectedSansGames.filter(g => g.id !== gameId);
            }
            updateSelectedGamesDisplay();
        });
        
        // Owner game search functionality
        let ownerGameSearchTimeout;
        $('#ownerGameSearchInput').on('input', function() {
            const searchTerm = $(this).val().trim();
            
            clearTimeout(ownerGameSearchTimeout);
            
            if (searchTerm.length < 2) {
                $('#ownerGameSearchResults').addClass('hidden').empty();
                return;
            }
            
            ownerGameSearchTimeout = setTimeout(function() {
                $('#ownerGameSearchLoading').removeClass('hidden');
                
                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'team_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                        'callback': 'games_search',
                        'search': searchTerm,
                        'limit': 10
                    },
                    success: function(response) {
                        $('#ownerGameSearchLoading').addClass('hidden');
                        
                        if (response.success && response.data && response.data.length > 0) {
                            let html = '<div class="p-2 space-y-1">';
                            let hasResults = false;
                            response.data.forEach(function(game) {
                                // Check if already selected
                                const isSelected = selectedOwnerGames.some(g => g.id === game.id);
                                if (isSelected) return;
                                
                                hasResults = true;
                                let warningText = '';
                                if (game.current_owner_name) {
                                    warningText += 'مالک فعلی: ' + game.current_owner_name;
                                }
                                
                                html += '<div class="p-2 hover:bg-gray-100 rounded cursor-pointer owner-game-search-item border-b border-gray-100" data-game-id="' + game.id + '" data-game-title="' + escapeHtml(game.title) + '" data-current-owner-id="' + (game.current_owner_id || '') + '" data-current-owner-name="' + escapeHtml(game.current_owner_name || '') + '">';
                                html += '<div class="font-bold text-sm">' + escapeHtml(game.title) + '</div>';
                                if (warningText) {
                                    html += '<div class="text-xs text-orange-600 mt-1">' + warningText + '</div>';
                                }
                                html += '</div>';
                            });
                            html += '</div>';
                            
                            if (hasResults) {
                                $('#ownerGameSearchResults').html(html).removeClass('hidden').show();
                            } else {
                                $('#ownerGameSearchResults').html('<div class="p-4 text-center text-sm text-gray-500">همه بازی‌های یافت شده قبلاً انتخاب شده‌اند</div>').removeClass('hidden').show();
                            }
                        } else {
                            $('#ownerGameSearchResults').html('<div class="p-4 text-center text-sm text-gray-500">نتیجه‌ای یافت نشد</div>').removeClass('hidden').show();
                        }
                    },
                    error: function() {
                        $('#ownerGameSearchLoading').addClass('hidden');
                        $('#ownerGameSearchResults').html('<div class="p-4 text-center text-sm text-red-500">خطا در جستجو</div>').removeClass('hidden');
                    }
                });
            }, 500);
        });
        
        // Sans manager game search functionality
        let sansGameSearchTimeout;
        $('#sansGameSearchInput').on('input', function() {
            const searchTerm = $(this).val().trim();
            
            clearTimeout(sansGameSearchTimeout);
            
            if (searchTerm.length < 2) {
                $('#sansGameSearchResults').addClass('hidden').empty();
                return;
            }
            
            sansGameSearchTimeout = setTimeout(function() {
                $('#sansGameSearchLoading').removeClass('hidden');
                
                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'team_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                        'callback': 'games_search',
                        'search': searchTerm,
                        'limit': 10
                    },
                    success: function(response) {
                        $('#sansGameSearchLoading').addClass('hidden');
                        
                        if (response.success && response.data && response.data.length > 0) {
                            let html = '<div class="p-2 space-y-1">';
                            let hasResults = false;
                            response.data.forEach(function(game) {
                                // Check if already selected
                                const isSelected = selectedSansGames.some(g => g.id === game.id);
                                if (isSelected) return;
                                
                                hasResults = true;
                                let warningText = '';
                                if (game.current_sans_manager_name) {
                                    warningText += 'مدیر سانس فعلی: ' + game.current_sans_manager_name;
                                }
                                
                                html += '<div class="p-2 hover:bg-gray-100 rounded cursor-pointer sans-game-search-item border-b border-gray-100" data-game-id="' + game.id + '" data-game-title="' + escapeHtml(game.title) + '" data-current-sans-id="' + (game.current_sans_manager_id || '') + '" data-current-sans-name="' + escapeHtml(game.current_sans_manager_name || '') + '">';
                                html += '<div class="font-bold text-sm">' + escapeHtml(game.title) + '</div>';
                                if (warningText) {
                                    html += '<div class="text-xs text-orange-600 mt-1">' + warningText + '</div>';
                                }
                                html += '</div>';
                            });
                            html += '</div>';
                            
                            if (hasResults) {
                                $('#sansGameSearchResults').html(html).removeClass('hidden').show();
                            } else {
                                $('#sansGameSearchResults').html('<div class="p-4 text-center text-sm text-gray-500">همه بازی‌های یافت شده قبلاً انتخاب شده‌اند</div>').removeClass('hidden').show();
                            }
                        } else {
                            $('#sansGameSearchResults').html('<div class="p-4 text-center text-sm text-gray-500">نتیجه‌ای یافت نشد</div>').removeClass('hidden').show();
                        }
                    },
                    error: function() {
                        $('#sansGameSearchLoading').addClass('hidden');
                        $('#sansGameSearchResults').html('<div class="p-4 text-center text-sm text-red-500">خطا در جستجو</div>').removeClass('hidden');
                    }
                });
            }, 500);
        });
        
        // Select owner game from search results
        $(document).on('click', '.owner-game-search-item', function() {
            const gameId = parseInt($(this).data('game-id'));
            const gameTitle = $(this).data('game-title');
            const currentOwnerId = $(this).data('current-owner-id');
            const currentOwnerName = $(this).data('current-owner-name') || '';
            
            // Check if already selected
            if (selectedOwnerGames.some(g => g.id === gameId)) {
                return;
            }
            
            // Add to selected list
            selectedOwnerGames.push({
                id: gameId,
                title: gameTitle,
                current_owner_id: currentOwnerId,
                current_owner_name: currentOwnerName
            });
            
            updateSelectedGamesDisplay();
            
            // Clear search
            $('#ownerGameSearchInput').val('');
            $('#ownerGameSearchResults').addClass('hidden').empty();
        });
        
        // Select sans manager game from search results
        $(document).on('click', '.sans-game-search-item', function() {
            const gameId = parseInt($(this).data('game-id'));
            const gameTitle = $(this).data('game-title');
            const currentSansId = $(this).data('current-sans-id');
            const currentSansName = $(this).data('current-sans-name') || '';
            
            // Check if already selected
            if (selectedSansGames.some(g => g.id === gameId)) {
                return;
            }
            
            // Add to selected list
            selectedSansGames.push({
                id: gameId,
                title: gameTitle,
                current_sans_manager_id: currentSansId,
                current_sans_manager_name: currentSansName
            });
            
            updateSelectedGamesDisplay();
            
            // Clear search
            $('#sansGameSearchInput').val('');
            $('#sansGameSearchResults').addClass('hidden').empty();
        });
        
        // Add game to user's connected games list
        function addGameToUser(userId, gameId, connectionType, gameTitle) {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'user_games_update',
                    'user_id': userId,
                    'game_id': gameId,
                    'connection_type': connectionType,
                    'action': 'add',
                    'force': 'true' // Skip warning check
                },
                success: function(response) {
                    if (response.success) {
                        // Reload connected games list
                        loadUserGames(userId);
                        Swal.fire({
                            title: 'موفق!',
                            text: 'بازی با موفقیت به کاربر متصل شد.',
                            icon: 'success',
                            confirmButtonText: 'باشه',
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            title: 'خطا!',
                            text: response.data || 'خطایی رخ داده است.',
                            icon: 'error',
                            confirmButtonText: 'باشه'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'خطا!',
                        text: 'خطایی در ارتباط با سرور رخ داده است.',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            });
        }
        
        // Load user's connected games
        function loadUserGames(userId) {
            // Show loading animation
            $('#selectedOwnerGames').html('<div class="text-sm text-gray-500 text-center py-4"><div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-primary-2"></div><div class="mt-2">در حال بارگذاری بازی‌ها...</div></div>');
            $('#selectedSansGames').html('<div class="text-sm text-gray-500 text-center py-4"><div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-primary-2"></div><div class="mt-2">در حال بارگذاری بازی‌ها...</div></div>');
            
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'user_games_get',
                    'user_id': userId
                },
                success: function(response) {
                    if (response.success) {
                        // Initialize selected games with current games
                        selectedOwnerGames = (response.data.owner_games || []).map(g => ({
                            id: g.id,
                            title: g.title,
                            current_owner_id: null,
                            current_owner_name: ''
                        }));
                        selectedSansGames = (response.data.sans_manager_games || []).map(g => ({
                            id: g.id,
                            title: g.title,
                            current_sans_manager_id: null,
                            current_sans_manager_name: ''
                        }));
                        updateSelectedGamesDisplay();
                    } else {
                        // Show error message
                        $('#selectedOwnerGames').html('<div class="text-sm text-red-500 text-center py-4">خطا در بارگذاری بازی‌ها</div>');
                        $('#selectedSansGames').html('<div class="text-sm text-red-500 text-center py-4">خطا در بارگذاری بازی‌ها</div>');
                    }
                },
                error: function() {
                    console.error('Error loading user games');
                    // Show error message
                    $('#selectedOwnerGames').html('<div class="text-sm text-red-500 text-center py-4">خطا در بارگذاری بازی‌ها</div>');
                    $('#selectedSansGames').html('<div class="text-sm text-red-500 text-center py-4">خطا در بارگذاری بازی‌ها</div>');
                }
            });
        }
        
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        // Disabled Edit User Button Click
        $(document).on('click', '.edit-user-btn-disabled', function() {
            Swal.fire({
                title: 'دسترسی محدود',
                text: 'شما دسترسی به ویرایش این کاربر ندارید.',
                icon: 'warning',
                confirmButtonText: 'باشه'
            });
        });
        // Edit User Form
        $('#editUserForm').on('submit', function(e) {
            e.preventDefault();
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.text();
            // Show loading state
            $submitBtn.prop('disabled', true);
            $submitBtn.html('<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>منتظر بمانید...');
            const formData = {
                'action': 'team_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                'callback': 'users_edit',
                'user_id': $('#editUserId').val(),
                'phone': $('#editUserPhone').val(),
                'first_name': $('#editUserFirstName').val(),
                'last_name': $('#editUserLastName').val(),
                'role': $('#editUserRole').val()
            };
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: formData,
                success: function(response) {
                    // Reset button state
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text(originalText);
                    if (response.success) {
                        Swal.fire({
                            title: 'موفق!',
                            text: 'اطلاعات کاربر با موفقیت به‌روزرسانی شد.',
                            icon: 'success',
                            confirmButtonText: 'باشه'
                        });
                        $('#editUserModal').hide();
                        $('#editUserForm')[0].reset();
                        loadUsers(currentPage);
                    } else {
                        Swal.fire({
                            title: 'خطا!',
                            text: response.data || 'خطایی رخ داده است.',
                            icon: 'error',
                            confirmButtonText: 'باشه'
                        });
                    }
                },
                error: function() {
                    // Reset button state on error
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text(originalText);
                    Swal.fire({
                        title: 'خطا!',
                        text: 'خطایی رخ داده است.',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            });
        });
        // Delete User Button Click
        $(document).on('click', '.delete-user-btn', function() {
            const userId = $(this).data('user-id');
            const $button = $(this);
            const originalText = $button.text();
            Swal.fire({
                title: 'تأیید حذف',
                text: 'آیا مطمئن هستید که می‌خواهید این کاربر را حذف کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    $button.prop('disabled', true);
                    $button.html('<svg class="animate-spin h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'users_delete',
                            'user_id': userId
                        },
                        success: function(response) {
                            // Reset button state
                            $button.prop('disabled', false);
                            $button.text(originalText);
                            if (response.success) {
                                Swal.fire({
                                    title: 'حذف شد!',
                                    text: 'کاربر با موفقیت حذف شد.',
                                    icon: 'success',
                                    confirmButtonText: 'باشه'
                                });
                                loadUsers(currentPage);
                            } else {
                                Swal.fire({
                                    title: 'خطا!',
                                    text: response.data || 'خطایی رخ داده است.',
                                    icon: 'error',
                                    confirmButtonText: 'باشه'
                                });
                            }
                        },
                        error: function() {
                            // Reset button state on error
                            $button.prop.prop('disabled', false);
                            $button.text(originalText);
                            Swal.fire({
                                title: 'خطا!',
                                text: 'خطایی رخ داده است.',
                                icon: 'error',
                                confirmButtonText: 'باشه'
                            });
                        }
                    });
                }
            });
        });
        // Disabled Delete User Button Click
        $(document).on('click', '.delete-user-btn-disabled', function() {
            Swal.fire({
                title: 'دسترسی محدود',
                text: 'شما دسترسی به حذف کاربر ندارید.',
                icon: 'warning',
                confirmButtonText: 'باشه'
            });
        });

        // Banking Info Modal
        $('#closeBankingModal, #cancelBanking').on('click', function() {
            $('#bankingInfoModal').hide();
            $('#bankingInfoForm')[0].reset();
        });
        // View Banking Info Button Click
        $(document).on('click', '.view-banking-btn', function() {
            const userId = $(this).data('user-id');
            const $button = $(this);
            const originalHtml = $button.html();
            // Show loading state
            $button.prop('disabled', true);
            $button.html('<svg class="animate-spin h-4 w-4 text-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');
            // Load banking information
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'banking_info_get',
                    'user_id': userId
                },
                success: function(response) {
                    // Reset button state
                    $button.prop('disabled', false);
                    $button.html(originalHtml);
                    if (response.success) {
                        // Fill form with banking data
                        $('#bankingUserId').val(response.data.user_id);
                        $('#bankingIdentityCard').val(response.data.identity_card);
                        $('#bankingOwnerName').val(response.data.owner_name);
                        $('#bankingShaba').val(response.data.shaba);
                        // Set modal title and button text for edit mode
                        $('#bankingModalTitle').text('ویرایش اطلاعات بانکی');
                        $('#bankingInfoForm button[type="submit"]').text('به‌روزرسانی اطلاعات بانکی');
                        // Show modal
                        $('#bankingInfoModal').show();
                    } else {
                        Swal.fire({
                            title: 'خطا!',
                            text: response.data || 'خطایی رخ داده است.',
                            icon: 'error',
                            confirmButtonText: 'باشه'
                        });
                    }
                },
                error: function() {
                    // Reset button state on error
                    $button.prop('disabled', false);
                    $button.html(originalHtml);
                    Swal.fire({
                        title: 'خطا!',
                        text: 'خطایی رخ داده است.',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            });
        });
        // Add Banking Info Button Click
        $(document).on('click', '.add-banking-btn', function() {
            const userId = $(this).data('user-id');
            // Clear form
            $('#bankingInfoForm')[0].reset();
            $('#bankingUserId').val(userId);
            // Set modal title and button text for add mode
            $('#bankingModalTitle').text('افزودن اطلاعات بانکی');
            $('#bankingInfoForm button[type="submit"]').text('افزودن اطلاعات بانکی');
            // Show modal
            $('#bankingInfoModal').show();
        });
        // Banking Info Form
        $('#bankingInfoForm').on('submit', function(e) {
            e.preventDefault();
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.text();
            // Validate identity card format
            const identityCard = $('#bankingIdentityCard').val();
            if (!/^[0-9]{10}$/.test(identityCard)) {
                Swal.fire({
                    title: 'خطا!',
                    text: 'کد ملی باید دقیقاً 10 رقم باشد.',
                    icon: 'error',
                    confirmButtonText: 'باشه'
                });
                return;
            }
            // Validate shaba format
            const shaba = $('#bankingShaba').val();
            if (!/^[0-9]{24}$/.test(shaba)) {
                Swal.fire({
                    title: 'خطا!',
                    text: 'شماره شبا باید دقیقاً 24 رقم باشد.',
                    icon: 'error',
                    confirmButtonText: 'باشه'
                });
                return;
            }
            // Show loading state
            $submitBtn.prop('disabled', true);
            $submitBtn.html('<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>منتظر بمانید...');
            const formData = {
                'action': 'team_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                'callback': 'banking_info_update',
                'user_id': $('#bankingUserId').val(),
                'identity_card': $('#bankingIdentityCard').val(),
                'owner_name': $('#bankingOwnerName').val(),
                'shaba': $('#bankingShaba').val()
            };
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: formData,
                success: function(response) {
                    // Reset button state
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text(originalText);
                    if (response.success) {
                        const isEditMode = $('#bankingModalTitle').text().includes('ویرایش');
                        const successMessage = isEditMode ? 'اطلاعات بانکی با موفقیت به‌روزرسانی شد.' : 'اطلاعات بانکی با موفقیت افزوده شد.';
                        Swal.fire({
                            title: 'موفق!',
                            text: successMessage,
                            icon: 'success',
                            confirmButtonText: 'باشه'
                        });
                        $('#bankingInfoModal').hide();
                        $('#bankingInfoForm')[0].reset();
                        loadUsers(currentPage);
                    } else {
                        Swal.fire({
                            title: 'خطا!',
                            text: response.data || 'خطایی رخ داده است.',
                            icon: 'error',
                            confirmButtonText: 'باشه'
                        });
                    }
                },
                error: function() {
                    // Reset button state on error
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text(originalText);
                    Swal.fire({
                        title: 'خطا!',
                        text: 'خطایی رخ داده است.',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            });
        });
        // Banking form input validation
        $(document).on('input', '#bankingIdentityCard', function() {
            let value = $(this).val();
            // Remove any non-numeric characters
            value = value.replace(/[^0-9]/g, '');
            // Limit to 10 characters
            value = value.substring(0, 10);
            $(this).val(value);
        });
        $(document).on('input', '#bankingShaba', function() {
            let value = $(this).val();
            // Remove any non-numeric characters
            value = value.replace(/[^0-9]/g, '');
            // Limit to 24 characters
            value = value.substring(0, 24);
            $(this).val(value);
        });

        // --- Create Password Logic ---
        $('#closeCreatePasswordModal, #cancelCreatePassword').on('click', function() {
            $('#createPasswordModal').hide();
            $('#createPasswordForm')[0].reset();
        });

        // Create Password Button Click (Delegated event for dynamic rows)
        $(document).on('click', '.create-password-btn', function() {
            const userId = $(this).data('user-id');
            const userPhone = $(this).data('user-phone');
            
            // Fill modal data
            $('#createPasswordUserId').val(userId);
            $('#createPasswordPhone').val(userPhone);
            $('#modalUserPhone').text(userPhone);
            
            // Show modal
            $('#createPasswordModal').show();
        });

        // Disabled Create Password Button Click
        $(document).on('click', '.create-password-btn-disabled', function() {
            Swal.fire({
                title: 'دسترسی محدود',
                text: 'شما دسترسی به ایجاد رمز برای این کاربر را ندارید.',
                icon: 'warning',
                confirmButtonText: 'باشه'
            });
        });

        // Create Password Form Submit
        $('#createPasswordForm').on('submit', function(e) {
            e.preventDefault();
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.text();

            // Show loading state
            $submitBtn.prop('disabled', true);
            $submitBtn.html('<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>منتظر بمانید...');

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'users_create_password', // Ensure this matches your PHP handler
                    'user_id': $('#createPasswordUserId').val(),
                    'phone': $('#createPasswordPhone').val()
                },
                success: function(response) {
                    // Reset button state
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text(originalText);

                    if (response.success) {
                        Swal.fire({
                            title: 'موفق!',
                            text: response.data, // Displays the password returned from PHP
                            icon: 'success',
                            confirmButtonText: 'باشه'
                        });
                        $('#createPasswordModal').hide();
                        $('#createPasswordForm')[0].reset();
                        // Optional: Reload list if needed, though password creation doesn't change list data usually
                        // loadUsers(currentPage); 
                    } else {
                        Swal.fire({
                            title: 'خطا!',
                            text: response.data || 'خطایی رخ داده است.',
                            icon: 'error',
                            confirmButtonText: 'باشه'
                        });
                    }
                },
                error: function() {
                    // Reset button state on error
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text(originalText);
                    Swal.fire({
                        title: 'خطا!',
                        text: 'خطایی در ارتباط با سرور رخ داده است.',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            });
        });
    });
</script>
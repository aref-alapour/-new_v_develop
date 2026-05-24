<input type="hidden" id="current_user_id">
<div class="flex justify-between items-center gap-4">

    <h1 class="text-base font-extrabold lg:text-2xl">تراکنش‌ها</h1>

    <svg xmlns="http://www.w3.org/2000/svg" width="4" height="34" viewBox="0 0 4 34" fill="none" class="mx-6">
        <path d="M2 2L2 32" stroke="#FF6900" stroke-width="4" stroke-linecap="round" />
    </svg>

    <div id="user_info" class="flex items-center justify-between" style="width: 100%"></div>

    <div class="relative w-[304px] h-[58px]" style="z-index: 99;">
        <input id="trans_user_search" class="w-[304px] h-[58px] border border-[#E4EBF0] bg-[#FAFDFF] rounded-xl outline-none pr-12 pl-12 py-5 text-xs font-yekan-bold text-navyBlue placeholder:text-end" placeholder="جست و جو" dir="ltr" />
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="absolute top-5 left-6">
            <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#09192D" />
        </svg>
        <button id="trans_user_search_clear" class="absolute top-5 right-4 w-6 h-6 flex items-center justify-center cursor-pointer opacity-0 pointer-events-none transition-opacity" style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M13 1L1 13M1 1L13 13" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div id="search-result-list" class="max-h-75 divide-y divide-[#E4EBF0] overflow-y-auto px-4 pt-2 pb-3" style="background: #f3f3f3; display: none; position: absolute; top: 60px; right: 0; left: 0; border-radius: 0 0 12px 12px; box-shadow: 0 8px 16px rgba(15, 23, 42, 0.12);">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-yekan-bold text-[#64748B]">نتایج جستجو</span>
                <button id="search_result_list_close" class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-[#E2E8F0] transition-colors" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 14 14" fill="none">
                        <path d="M13 1L1 13M1 1L13 13" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

</div>

<?php
if (array_intersect(['administrator', 'accounting'], wp_get_current_user()->roles)) : ?>

    <div class="flex justify-between items-center py-3 px-6 mt-7" style="border-radius: 14px;border: 1px solid var(--Border-Default, #E2E8F0);background: var(--slate-50, #F8FAFC);box-shadow: 0px 2px 0px 0px #E2E8F0;">
        <div class="flex items-center gap-3">
            <p class="text-sm font-yekan-bold">مبلغ شارژ(تومان)<span class=" text-pinkk">*</span></p>
            <input id="trans_operation_amount" class="w-[156px] h-[34px] bg-white border border-[#E4EBF0] rounded-lg outline-none px-6 py-3 text-xs font-yekan-bold text-navyBlue text-end" style="width: 156px;height: 34px;direction: ltr;text-align: left;" placeholder="" />
        </div>

        <div class="flex items-center gap-3">
            <p class="text-sm font-yekan-bold">توضیح<span class=" text-pinkk">*</span></p>
            <input id="trans_operation_desc" class="w-[398px] h-[34px] bg-white border border-[#E4EBF0] rounded-lg outline-none text-xs font-yekan-bold text-navyBlue px-2" style="width: 398px; height: 34px;" placeholder="" />
        </div>

        <div class="relative dropdown">
            <select id="trans_operation_control" class="w-[156px] h-[34px] bg-white border border-[#E4EBF0] rounded-lg outline-none px-3 py-2 text-xs font-yekan-bold text-navyBlue appearance-none cursor-pointer hover:border-[#FF6900] focus:border-[#FF6900] transition-colors" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 4 5\'%3E%3Cpath fill=\'%23666\' d=\'M2 0L0 2h4zm0 5L0 3h4z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: left 8px center; background-size: 12px;">
                <option value="-1">انتخاب کنید...</option>
                <option value="p">اضافه شود</option>
                <option value="">کم شود</option>
            </select>
        </div>

        <button id="trans_operation_control_btn" class="w-[81px] h-[34px] flex justify-center items-center bg-orange-500 rounded-lg cursor-pointer text-white text-xs font-yekan-heavy hover:bg-orange-600 transition-colors" style="box-shadow: 0px 2px 0px 0px #CA5608; width: 81px; height: 34px;">اعمال</button>

    </div>
<?php
endif; ?>

<!-- Collection Owners Wallet Table -->
<div id="collections-owners-table-wrapper" class="relative bg-white rounded-xl overflow-hidden shadow-sm mt-6">
    <div class="flex justify-between items-center px-6 py-4 border-b border-[#E4EBF0]">
        <h2 class="text-base font-yekan-bold text-navyBlue">لیست موجودی کیف پول مجموعه‌دارها</h2>
        <button id="export-excel-collections" class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-yekan-bold hover:bg-green-600 transition-colors">
            خروجی اکسل
        </button>
    </div>
    <div id="collections-owners-table-container" class="overflow-x-auto">
        <table id="collections-owners-table" class="w-full">
            <thead class="bg-[#F1F5F9]">
                <tr>
                    <th class="px-6 py-4 text-sm font-yekan-bold text-[#64748B] text-center cursor-pointer hover:bg-[#E4EBF0] transition-colors" data-sort="row">
                        ردیف <span class="sort-icon">⇅</span>
                    </th>
                    <th class="px-6 py-4 text-sm font-yekan-bold text-[#64748B] text-center cursor-pointer hover:bg-[#E4EBF0] transition-colors" data-sort="full_name">
                        نام و نام خانوادگی <span class="sort-icon">⇅</span>
                    </th>
                    <th class="px-6 py-4 text-sm font-yekan-bold text-[#64748B] text-center cursor-pointer hover:bg-[#E4EBF0] transition-colors" data-sort="phone">
                        شماره تلفن <span class="sort-icon">⇅</span>
                    </th>
                    <th class="px-6 py-4 text-sm font-yekan-bold text-[#64748B] text-center cursor-pointer hover:bg-[#E4EBF0] transition-colors" data-sort="collection_id">
                        ID مجموعه <span class="sort-icon">⇅</span>
                    </th>
                    <th class="px-6 py-4 text-sm font-yekan-bold text-[#64748B] text-center cursor-pointer hover:bg-[#E4EBF0] transition-colors" data-sort="brand_name">
                        نام برند <span class="sort-icon">⇅</span>
                    </th>
                    <th class="px-6 py-4 text-sm font-yekan-bold text-[#64748B] text-center cursor-pointer hover:bg-[#E4EBF0] transition-colors" data-sort="games">
                        بازی‌ها <span class="sort-icon">⇅</span>
                    </th>
                    <th class="px-6 py-4 text-sm font-yekan-bold text-[#64748B] text-center cursor-pointer hover:bg-[#E4EBF0] transition-colors" data-sort="balance">
                        آخرین موجودی <span class="sort-icon">⇅</span>
                    </th>
                    <th class="px-6 py-4 text-sm font-yekan-bold text-[#64748B] text-center cursor-pointer hover:bg-[#E4EBF0] transition-colors" data-sort="last_change_date">
                        تاریخ آخرین تغییر کیف پول <span class="sort-icon">⇅</span>
                    </th>
                </tr>
            </thead>
            <tbody id="collections-owners-table-body">
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-grayy">در حال بارگذاری...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- User Transactions Table -->
<div class="relative bg-white rounded-xl overflow-hidden shadow-sm mt-6" id="data-list" style="display: none;"></div>

<script>
    jQuery(document).ready(function($) {
        
        // Collections owners data
        let collectionsOwnersData = [];
        let allCollectionsOwnersData = []; // Keep all data for export
        let currentSortColumn = null;
        let currentSortDirection = 'asc';
        let currentPage = 1;
        const itemsPerPage = 50;

        // Load collections owners data
        function loadCollectionsOwners() {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'collections_owners_wallet_get',
                },
                success: function(response) {
                    if (response.success && response.data) {
                        allCollectionsOwnersData = response.data;
                        collectionsOwnersData = response.data;
                        currentPage = 1;
                        renderCollectionsTable();
                    } else {
                        const errorMessage = response.data || 'خطا در بارگذاری داده‌ها';
                        $('#collections-owners-table-body').html('<tr><td colspan="8" class="px-6 py-8 text-center text-grayy">' + errorMessage + '</td></tr>');
                        
                        // Show error notification
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                position: "bottom-start",
                                icon: "info",
                                title: 'توجه',
                                text: errorMessage,
                                showConfirmButton: false,
                                timer: 3000,
                                width: '350px',
                                toast: true
                            });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $('#collections-owners-table-body').html('<tr><td colspan="8" class="px-6 py-8 text-center text-grayy">خطا در بارگذاری داده‌ها</td></tr>');
                }
            });
        }

        // Render collections table with pagination
        function renderCollectionsTable() {
            let html = '';
            if (collectionsOwnersData.length === 0) {
                html = '<tr><td colspan="8" class="px-6 py-8 text-center text-grayy">هیچ مجموعه‌داری یافت نشد</td></tr>';
            } else {
                // Calculate pagination
                const totalPages = Math.ceil(collectionsOwnersData.length / itemsPerPage);
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const pageData = collectionsOwnersData.slice(startIndex, endIndex);
                
                pageData.forEach((item, index) => {
                    const rowNumber = startIndex + index + 1;
                    const balance = parseInt(item.balance) || 0;
                    const balanceClass = balance < 0 ? 'text-red-600' : balance > 0 ? 'text-green-600' : '';
                    const balanceSign = balance > 0 ? '+' : balance < 0 ? '-' : '';
                    const lastChangeDate = item.last_change_date ? 
                        new Date(parseInt(item.last_change_date) * 1000).toLocaleDateString('fa-IR') : 
                        '---';
                    
                    html += `
                        <tr class="border-b border-[#E4EBF0] hover:bg-[#F8FAFC] transition-colors" style="background-color: ${index % 2 === 0 ? '#FFFFFF' : '#F8FAFC'};">
                            <td class="px-6 py-4 text-sm font-yekan-bold text-[#1E293B] text-center">${rowNumber}</td>
                            <td class="px-6 py-4 text-sm font-yekan-bold text-[#1E293B] text-center">${escapeHtml(item.full_name || '---')}</td>
                            <td class="px-6 py-4 text-sm font-yekan-bold text-[#1E293B] text-center">${escapeHtml(item.phone)}</td>
                            <td class="px-6 py-4 text-sm font-yekan-bold text-[#1E293B] text-center">${item.collection_id}</td>
                            <td class="px-6 py-4 text-sm font-yekan-bold text-[#1E293B] text-center">${item.brand_name || '---'}</td>
                            <td class="px-6 py-4 text-sm font-yekan-bold text-[#1E293B] text-center" style="max-width: 300px;">${item.games || '---'}</td>
                            <td class="px-6 py-4 text-sm font-yekan-bold ${balanceClass} text-center">${formatNumber(Math.abs(balance).toString())}${balanceSign}</td>
                            <td class="px-6 py-4 text-sm font-yekan-bold text-[#1E293B] text-center">${lastChangeDate}</td>
                        </tr>
                    `;
                });
                
                // Add pagination
                if (totalPages > 1) {
                    html += `<tr><td colspan="8" class="px-6 py-4">
                        <div class="flex justify-center items-center gap-2">
                            ${currentPage > 1 ? `<button onclick="goToPage(${currentPage - 1})" class="px-3 py-1 border border-[#E2E8F0] rounded-lg text-sm font-yekan-bold text-[#64748B] hover:bg-[#F1F5F9] transition-colors">قبلی</button>` : ''}
                            ${Array.from({length: totalPages}, (_, i) => i + 1).map(page => {
                                if (page === currentPage) {
                                    return `<span class="px-3 py-1 bg-[#FF6900] text-white rounded-lg text-sm font-yekan-bold">${page}</span>`;
                                } else if (page === 1 || page === totalPages || (page >= currentPage - 2 && page <= currentPage + 2)) {
                                    return `<button onclick="goToPage(${page})" class="px-3 py-1 border border-[#E2E8F0] rounded-lg text-sm font-yekan-bold text-[#64748B] hover:bg-[#F1F5F9] transition-colors">${page}</button>`;
                                } else if (page === currentPage - 3 || page === currentPage + 3) {
                                    return `<span class="px-3 py-1 text-[#64748B]">...</span>`;
                                }
                                return '';
                            }).join('')}
                            ${currentPage < totalPages ? `<button onclick="goToPage(${currentPage + 1})" class="px-3 py-1 border border-[#E2E8F0] rounded-lg text-sm font-yekan-bold text-[#64748B] hover:bg-[#F1F5F9] transition-colors">بعدی</button>` : ''}
                        </div>
                    </td></tr>`;
                }
            }
            $('#collections-owners-table-body').html(html);
        }
        
        // Go to page function (global for onclick)
        window.goToPage = function(page) {
            currentPage = page;
            renderCollectionsTable();
            // Scroll to top of table
            $('#collections-owners-table-wrapper')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        // Sort table
        function sortTable(column) {
            if (currentSortColumn === column) {
                currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortColumn = column;
                currentSortDirection = 'asc';
            }

            collectionsOwnersData.sort((a, b) => {
                let aVal, bVal;
                
                switch(column) {
                    case 'row':
                        return currentSortDirection === 'asc' ? 0 : 0; // Row number doesn't change
                    case 'full_name':
                        aVal = a.full_name || '';
                        bVal = b.full_name || '';
                        break;
                    case 'phone':
                        aVal = a.phone || '';
                        bVal = b.phone || '';
                        break;
                    case 'collection_id':
                        aVal = parseInt(a.collection_id) || 0;
                        bVal = parseInt(b.collection_id) || 0;
                        break;
                    case 'brand_name':
                        aVal = a.brand_name || '';
                        bVal = b.brand_name || '';
                        break;
                    case 'games':
                        aVal = a.games || '';
                        bVal = b.games || '';
                        break;
                    case 'balance':
                        aVal = parseInt(a.balance) || 0;
                        bVal = parseInt(b.balance) || 0;
                        // Sort negative to positive (asc) or positive to negative (desc)
                        if (currentSortDirection === 'asc') {
                            // Negative first, then positive
                            if (aVal < 0 && bVal >= 0) return -1;
                            if (aVal >= 0 && bVal < 0) return 1;
                            return aVal - bVal;
                        } else {
                            // Positive first, then negative
                            if (aVal > 0 && bVal <= 0) return -1;
                            if (aVal <= 0 && bVal > 0) return 1;
                            return bVal - aVal;
                        }
                    case 'last_change_date':
                        aVal = parseInt(a.last_change_date) || 0;
                        bVal = parseInt(b.last_change_date) || 0;
                        break;
                    default:
                        return 0;
                }

                if (typeof aVal === 'string') {
                    return currentSortDirection === 'asc' ? 
                        aVal.localeCompare(bVal, 'fa') : 
                        bVal.localeCompare(aVal, 'fa');
                } else {
                    return currentSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                }
            });

            // Reset to first page after sorting
            currentPage = 1;
            renderCollectionsTable();
            updateSortIcons();
        }

        // Update sort icons
        function updateSortIcons() {
            $('.sort-icon').html('⇅');
            if (currentSortColumn) {
                const icon = currentSortDirection === 'asc' ? '↑' : '↓';
                $(`th[data-sort="${currentSortColumn}"] .sort-icon`).html(icon);
            }
        }

        // Escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Strip HTML tags for Excel export
        function stripHtml(html) {
            if (!html) return '';
            const tmp = document.createElement('DIV');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        }

        // Excel export for collections (exports all data, not just current page)
        function exportCollectionsToExcel() {
            // Use allCollectionsOwnersData for export (all data, not paginated)
            const dataToExport = allCollectionsOwnersData.length > 0 ? allCollectionsOwnersData : collectionsOwnersData;
            
            if (dataToExport.length === 0) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'توجه',
                    text: 'داده‌ای برای خروجی وجود ندارد',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                return;
            }

            // Apply current sort to export data
            let sortedData = [...dataToExport];
            if (currentSortColumn) {
                sortedData.sort((a, b) => {
                    let aVal, bVal;
                    
                    switch(currentSortColumn) {
                        case 'full_name':
                            aVal = a.full_name || '';
                            bVal = b.full_name || '';
                            break;
                        case 'phone':
                            aVal = a.phone || '';
                            bVal = b.phone || '';
                            break;
                        case 'collection_id':
                            aVal = parseInt(a.collection_id) || 0;
                            bVal = parseInt(b.collection_id) || 0;
                            break;
                        case 'brand_name':
                            aVal = a.brand_name || '';
                            bVal = b.brand_name || '';
                            break;
                        case 'games':
                            aVal = a.games || '';
                            bVal = b.games || '';
                            break;
                        case 'balance':
                            aVal = parseInt(a.balance) || 0;
                            bVal = parseInt(b.balance) || 0;
                            if (currentSortDirection === 'asc') {
                                if (aVal < 0 && bVal >= 0) return -1;
                                if (aVal >= 0 && bVal < 0) return 1;
                                return aVal - bVal;
                            } else {
                                if (aVal > 0 && bVal <= 0) return -1;
                                if (aVal <= 0 && bVal > 0) return 1;
                                return bVal - aVal;
                            }
                        case 'last_change_date':
                            aVal = parseInt(a.last_change_date) || 0;
                            bVal = parseInt(b.last_change_date) || 0;
                            break;
                        default:
                            return 0;
                    }

                    if (typeof aVal === 'string') {
                        return currentSortDirection === 'asc' ? 
                            aVal.localeCompare(bVal, 'fa') : 
                            bVal.localeCompare(aVal, 'fa');
                    } else {
                        return currentSortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                    }
                });
            }

            let csv = '\uFEFF'; // BOM for UTF-8
            csv += 'ردیف,نام و نام خانوادگی,شماره تلفن,ID مجموعه,نام برند,بازی‌ها,آخرین موجودی,تاریخ آخرین تغییر کیف پول\n';
            
            sortedData.forEach((item, index) => {
                const rowNumber = index + 1;
                const balance = parseInt(item.balance) || 0;
                const lastChangeDate = item.last_change_date ? 
                    new Date(parseInt(item.last_change_date) * 1000).toLocaleDateString('fa-IR') : 
                    '---';
                
                // Strip HTML from brand_name and games for CSV export
                const brandNameText = stripHtml(item.brand_name || '---');
                const gamesText = stripHtml(item.games || '---');
                
                csv += `${rowNumber},"${item.full_name || '---'}","${item.phone}",${item.collection_id},"${brandNameText}","${gamesText}",${balance},"${lastChangeDate}"\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'موجودی_کیف_پول_مجموعه_دارها_' + new Date().toISOString().split('T')[0] + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Excel export for user transactions
        function exportUserTransactionsToExcel() {
            const user_id = $('#current_user_id').val();
            if (!user_id) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'توجه',
                    text: 'لطفا ابتدا یک کاربر را جست و جو کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                return;
            }

            Swal.fire({
                position: "bottom-start",
                icon: "info",
                title: 'در حال بارگذاری...',
                text: 'در حال دریافت تمام تراکنش‌ها',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Always fetch all transactions via AJAX to get complete data
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'transactions_get',
                    'user_id': user_id,
                    'data_type': 'user_trans',
                    'page': 1,
                    'export_all': 'true'
                },
                success: function(data) {
                    Swal.close();
                    // Parse the HTML response to extract transaction data
                    const tempDiv = $('<div>').html(data);
                    const rows = tempDiv.find('.data-row');
                    
                    if (rows.length === 0) {
                        Swal.fire({
                            position: "bottom-start",
                            icon: "warning",
                            title: 'توجه',
                            text: 'داده‌ای برای خروجی وجود ندارد',
                            showConfirmButton: false,
                            timer: 3000,
                            width: '350px',
                            toast: true
                        });
                        return;
                    }

                    let csv = '\uFEFF'; // BOM for UTF-8
                    csv += 'ردیف,شماره تراکنش,زمان درخواست,اضافه/کسر,مبلغ,موجودی قبلی,موجودی فعلی,بابت,وضعیت\n';

                    rows.each(function() {
                        const cols = $(this).find('.grid > div');
                        if (cols.length >= 9) {
                            const row = [];
                            cols.each(function() {
                                let text = $(this).text().trim();
                                text = text.replace(/"/g, '""'); // Escape quotes
                                row.push('"' + text + '"');
                            });
                            csv += row.join(',') + '\n';
                        }
                    });

                    downloadCSV(csv, user_id);
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        position: "bottom-start",
                        icon: "error",
                        title: 'خطا',
                        text: 'خطا در دریافت داده‌ها',
                        showConfirmButton: false,
                        timer: 3000,
                        width: '350px',
                        toast: true
                    });
                }
            });
        }

        // Helper function to download CSV
        function downloadCSV(csv, user_id) {
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'تراکنش_کاربر_' + user_id + '_' + new Date().toISOString().split('T')[0] + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Event handlers
        $(document).on('click', 'th[data-sort]', function() {
            const column = $(this).data('sort');
            sortTable(column);
        });

        $('#export-excel-collections').on('click', function() {
            exportCollectionsToExcel();
        });

        // Export Excel for user transactions
        $(document).on('click', '#export-excel-user-transactions', function() {
            exportUserTransactionsToExcel();
        });

        // Load collections owners on page load
        loadCollectionsOwners();

        // Format number with comma separator
        function formatNumber(num) {
            // Remove all non-digit characters
            let cleanNum = num.replace(/[^\d]/g, '');
            // Add comma separator every 3 digits from right
            return cleanNum.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Auto format amount input
        $('body').on('input', "#trans_operation_amount", function() {
            let cursorPosition = this.selectionStart;
            let oldValue = $(this).val();
            let oldLength = oldValue.length;

            // Format the number
            let formattedValue = formatNumber(oldValue);
            $(this).val(formattedValue);

            // Adjust cursor position after formatting
            let newLength = formattedValue.length;
            let diff = newLength - oldLength;
            this.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
        });

        // Show/hide clear button based on input value
        function toggleClearButton() {
            const searchValue = $('#trans_user_search').val().trim();
            const clearBtn = $('#trans_user_search_clear');
            if (searchValue.length > 0) {
                clearBtn.css({
                    'display': 'flex',
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
            } else {
                clearBtn.css({
                    'display': 'none',
                    'opacity': '0',
                    'pointer-events': 'none'
                });
            }
        }

        // Clear search input (پاک کردن متن جستجو)
        $('#trans_user_search_clear').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#trans_user_search').val('').trigger('input');
            $(this).focus();
        });

        // Close only the search result list (بدون پاک کردن متن جستجو)
        $('body').on('click', '#search_result_list_close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#search-result-list').hide().html('');
        });

        let debounceTimer;
        $('body').on('input', "#trans_user_search", function() {
            let input = this;
            let searchValue = $(input).val().trim();

            // Toggle clear button visibility
            toggleClearButton();

            // If search is cleared, show collections table again
            if (searchValue === '') {
                $('#collections-owners-table-wrapper').show();
                $('#data-list').hide();
                $('#user_info').html('');
                $('#current_user_id').val('');
                $('#search-result-list').hide();
                return;
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'team_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                        'callback': 'transactions_user_search',
                        'phone': searchValue,
                    },
                    success: function(data) {
                        $("#search-result-list").show().html(data);
                    },
                });
            }, 300);
        });

        $("body").on('click', ".team_trans_user_search_item", function(e) {
            e.preventDefault();

            let user_id = $(this).data('id');

            // Hide collections table and show user transactions
            $('#collections-owners-table-wrapper').hide();
            $('#data-list').show();
            
            load_user_trans_info(user_id);
        });

        function load_user_trans_info(user_id, page = 1) {
            $('#search-result-list').html('').hide();
            $('#current_user_id').val(user_id);
            
            // Hide collections table and show user transactions
            $('#collections-owners-table-wrapper').hide();
            $('#data-list').show();

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'transactions_get',
                    'user_id': user_id,
                    'data_type': 'user_info',
                },
                beforeSend: function() {
                    let out = '<div class="w-full h-12 rounded-xl mb-2 skeleton"></div>';
                    $("#user_info").html(out);
                },
                success: function(data) {
                    $("#user_info").html(data);
                    load_transactions_data(user_id, page);
                }
            });
        }

        function load_transactions_data(user_id, page = 1) {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'transactions_get',
                    'user_id': user_id,
                    'data_type': 'user_trans',
                    'page': page,
                },
                beforeSend: function() {
                    $("#data-list").html(function() {
                        let out = '<div class="w-full bg-[#F1F5F9] rounded-t-xl"><div class="grid grid-cols-[60px_180px_100px_120px_100px_120px_120px_120px_120px] gap-4 px-6 py-4 text-sm font-yekan-bold text-[#64748B]"><div class="text-center">ردیف</div><div class="text-center">شماره تراکنش</div><div class="text-center">زمان درخواست</div><div class="text-center">اضافه/کسر</div><div class="text-center">مبلغ</div><div class="text-center">موجودی قبلی</div><div class="text-center">موجودی فعلی</div><div class="text-center">بابت</div><div class="text-center">وضعیت</div></div></div>';
                        for (let i = 0; i < 15; i++) {
                            out += '<div class="w-full h-16 px-6 py-4"><div class="w-full h-8 rounded-lg skeleton"></div></div>';
                        }
                        return out;
                    });
                },
                success: function(data) {
                    $("#data-list").html(data);
                }
            });
        }

        const user_id_querystring = (new URLSearchParams(window.location.search)).get('user_id');
        if (user_id_querystring)
            load_user_trans_info(user_id_querystring);

        $("body").on('click', "#trans_operation_control_btn", function(e) {
            e.preventDefault();

            let amount = $('#trans_operation_amount').val().trim();
            // Remove commas for processing
            let cleanAmount = amount.replace(/,/g, '');
            let user_id = $('#current_user_id').val();
            let description = $('#trans_operation_desc').val().trim();
            let operation = $('#trans_operation_control').val();

            // Validation
            if (!user_id) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'توجه',
                    text: 'لطفا ابتدا یک کاربر را جست و جو کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                return;
            }

            if (!cleanAmount) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'مبلغ الزامی است',
                    text: 'لطفا مبلغ را وارد کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                $('#trans_operation_amount').focus();
                return;
            }

            if (isNaN(cleanAmount) || parseFloat(cleanAmount) <= 0) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "error",
                    title: 'مبلغ نامعتبر',
                    text: 'لطفا مبلغ معتبر وارد کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                $('#trans_operation_amount').focus();
                return;
            }

            if (!description) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'توضیح الزامی است',
                    text: 'لطفا توضیح را وارد کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                $('#trans_operation_desc').focus();
                return;
            }

            if (operation === '-1') {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'نوع عملیات الزامی است',
                    text: 'لطفا نوع عملیات (اضافه/کسر) را انتخاب کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                $('#trans_operation_control').focus();
                return;
            }

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'transactions_operations',
                    'user_id': user_id,
                    'amount': cleanAmount,
                    'description': description,
                    'operation': operation,
                },
                beforeSend: function() {
                    $('#trans_operation_control_btn').prop('disabled', true).text('در حال پردازش...');
                },
                success: function(response) {
                    console.log('Transaction operation response:', response);

                    let current_user = $('#current_user_id').val();

                    if (current_user) {
                        // Reload current page of transactions
                        let current_page = $('.pagination .current').text() || '1';
                        load_transactions_data(current_user, current_page);
                    }

                    $('#trans_operation_amount').val('');
                    $('#trans_operation_desc').val('');
                    $('#trans_operation_control').val('-1');

                    Swal.fire({
                        position: "bottom-start",
                        icon: "success",
                        title: 'موفق',
                        text: 'عملیات با موفقیت انجام شد',
                        showConfirmButton: false,
                        timer: 3000,
                        width: '350px',
                        toast: true
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Transaction operation error:', error);
                    Swal.fire({
                        position: "bottom-start",
                        icon: "error",
                        title: 'خطا',
                        text: 'خطا در انجام عملیات. لطفا دوباره تلاش کنید',
                        showConfirmButton: false,
                        timer: 3000,
                        width: '350px',
                        toast: true
                    });
                },
                complete: function() {
                    $('#trans_operation_control_btn').prop('disabled', false).text('اعمال');
                }
            });
        });

        $("body").on('click', ".pagination-link", function(e) {
            e.preventDefault();

            const page = $(this).data('page');
            const user_id = $('#current_user_id').val();

            console.log('Pagination clicked - Page:', page, 'User ID:', user_id);

            if (user_id && page) {
                load_transactions_data(user_id, page);
            }
        });

    });
</script>
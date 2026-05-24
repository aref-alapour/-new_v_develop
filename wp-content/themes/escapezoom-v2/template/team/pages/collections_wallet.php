<div class="flex justify-between items-center gap-4">

    <h1 class="text-base font-extrabold lg:text-2xl">کیف پول مجموعه‌داران</h1>

    <svg xmlns="http://www.w3.org/2000/svg" width="4" height="34" viewBox="0 0 4 34" fill="none" class="mx-6">
        <path d="M2 2L2 32" stroke="#FF6900" stroke-width="4" stroke-linecap="round" />
    </svg>

    <div class="flex-1"></div>

</div>

<!-- Collection Owners Wallet Table -->
<div id="collections-owners-table-wrapper" class="relative bg-white rounded-xl overflow-hidden shadow-sm mt-6">
    <div class="flex justify-between items-center px-6 py-4 border-b border-[#E4EBF0]">
        <h2 class="text-base font-yekan-bold text-navyBlue">لیست موجودی کیف پول مجموعه‌داران</h2>
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

<script>
    jQuery(document).ready(function($) {

        let collectionsOwnersData = [];
        let allCollectionsOwnersData = [];
        let currentSortColumn = null;
        let currentSortDirection = 'asc';
        let currentPage = 1;
        const itemsPerPage = 50;

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
                error: function() {
                    $('#collections-owners-table-body').html('<tr><td colspan="8" class="px-6 py-8 text-center text-grayy">خطا در بارگذاری داده‌ها</td></tr>');
                }
            });
        }

        function renderCollectionsTable() {
            let html = '';
            if (collectionsOwnersData.length === 0) {
                html = '<tr><td colspan="8" class="px-6 py-8 text-center text-grayy">هیچ مجموعه‌داری یافت نشد</td></tr>';
            } else {
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

        window.goToPage = function(page) {
            currentPage = page;
            renderCollectionsTable();
            $('#collections-owners-table-wrapper')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

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
                        return currentSortDirection === 'asc' ? 0 : 0;
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

            currentPage = 1;
            renderCollectionsTable();
            updateSortIcons();
        }

        function updateSortIcons() {
            $('.sort-icon').html('⇅');
            if (currentSortColumn) {
                const icon = currentSortDirection === 'asc' ? '↑' : '↓';
                $(`th[data-sort="${currentSortColumn}"] .sort-icon`).html(icon);
            }
        }

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

        function stripHtml(html) {
            if (!html) return '';
            const tmp = document.createElement('DIV');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        }

        function formatNumber(num) {
            let cleanNum = num.replace(/[^\d]/g, '');
            return cleanNum.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function exportCollectionsToExcel() {
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

            let csv = '\uFEFF';
            csv += 'ردیف,نام و نام خانوادگی,شماره تلفن,ID مجموعه,نام برند,بازی‌ها,آخرین موجودی,تاریخ آخرین تغییر کیف پول\n';

            sortedData.forEach((item, index) => {
                const rowNumber = index + 1;
                const balance = parseInt(item.balance) || 0;
                const lastChangeDate = item.last_change_date ?
                    new Date(parseInt(item.last_change_date) * 1000).toLocaleDateString('fa-IR') :
                    '---';

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

        $(document).on('click', 'th[data-sort]', function() {
            const column = $(this).data('sort');
            sortTable(column);
        });

        $('#export-excel-collections').on('click', function() {
            exportCollectionsToExcel();
        });

        loadCollectionsOwners();
    });
</script>

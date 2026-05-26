<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="flex justify-around mb-12 text-nowrap max-lg:gap-5.5 overflow-x-auto no-scrollbar">
    <?php for ($i = 0; $i < 7; $i++) { ?>
        <button type="button" data-tab="<?php echo $i; ?>" class="bg-[#F9FAFB] font-extrabold border border-[#E8EDF1] flex flex-col items-center justify-center rounded-xl p-4 leading-4 shadow-13 w-[80px] h-[120px] gap-1" <?php echo $i == 0 && wp_is_mobile() ? ' style="background: rgb(80, 145, 251); border-color: transparent; color: rgb(255, 255, 255);"' : '' ?>>
            <?php
            $timezone = new DateTimeZone('Asia/Tehran');
            $today = new DateTime('now', $timezone);
            $today->setTime(0, 0, 0);
            if ($time == $today->getTimestamp() && $i == 0) { ?>
                <span class="text-16">╪º┘à╪▒┘ê╪▓</span>
                <span class="text-26">
                    <?php echo jdate("d", $time + (60 * 60 * 24 * $i)); ?>
                </span>
            <?php } else { ?>
                <span class="text-14">
                    <?php echo jdate("l", $time + (60 * 60 * 24 * $i)); ?>
                </span>
                <span class="text-26">
                    <?php echo jdate("d", $time + (60 * 60 * 24 * $i)); ?>
                </span>
            <?php } ?>
        </button>
    <?php } ?>
</div>

<div class="flex justify-between mb-12">
    <?php foreach ($days as $index => $day) { ?>
        <div id="tab-<?php echo $index; ?>" class="tabs flex flex-col max-lg:px-0 px-4 w-full lg:border-l last-of-type:border-l-0 gap-4<?php echo $index !== 0 ? ' max-lg:hidden' : '' ?>">

            <?php foreach ($day as $item) {
                switch ($item->status) {
                    case "reservable": ?>

                        <?php if ($item->off_price > 0) { ?>
                            <div data-item-timestamp="<?php echo $item->time; ?>" data-item-sell-price="<?php echo $item->off_price ?>" class="box open cursor-pointer off max-lg:h-12 h-[112px] rounded-lg w-full shadow-102 overflow-hidden relative">
                                <div class="back text-white bg-blue absolute w-full h-full flex lg:flex-col text-center justify-between">
                                    <span class="text-2xl drop-shadow-104 flex grow w-full lg:justify-center items-center max-lg:px-2 max-lg:text-30">
                                        <?php echo date('H:i', $item->time) ?>
                                    </span>
                                    <span class="text-md shrink-0 max-lg:flex items-center max-lg:px-2 drop-shadow-104 py-0.5 bg-black/5 line-through max-lg:text-16 max-lg:gap-1">
                                        <?php echo number_format($item->price) ?>
                                        ╪¬┘ê┘à╪º┘å
                                    </span>
                                    <span class="text-md shrink-0 max-lg:flex items-center justify-center max-lg:px-2 drop-shadow-104 py-0.5 bg-black/15 w-30 lg:w-full text-center max-lg:text-22 max-lg:gap-1">
                                        <?php echo number_format($item->off_price) ?>
                                        <span class="max-lg:text-12">╪¬┘ê┘à╪º┘å</span>
                                    </span>
                                </div>
                                <div class="front text-textColor bg-white px-1.5 absolute top-full w-full h-full flex text-center justify-between transition-all duration-150 items-center">
                                    <button type="button" data-action="plus" class="bg-accent-450 rounded p-2 aspect-square">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="17" viewBox="0 0 18 17" fill="none">
                                            <g filter="url(#filter0_d_5955_363)">
                                                <path d="M14.5862 6.09375H9.89728V1.40625C9.89728 1.03329 9.74908 0.675604 9.48527 0.411881C9.22147 0.148158 8.86367 0 8.49059 0C8.11752 0 7.75972 0.148158 7.49592 0.411881C7.23211 0.675604 7.08391 1.03329 7.08391 1.40625V6.09375H2.39497C2.02189 6.09375 1.66409 6.24191 1.40029 6.50563C1.13649 6.76935 0.988281 7.12704 0.988281 7.5C0.988281 7.87296 1.13649 8.23065 1.40029 8.49437C1.66409 8.75809 2.02189 8.90625 2.39497 8.90625H7.08391V13.5938C7.08391 13.9667 7.23211 14.3244 7.49592 14.5881C7.75972 14.8518 8.11752 15 8.49059 15C8.86367 15 9.22147 14.8518 9.48527 14.5881C9.74908 14.3244 9.89728 13.9667 9.89728 13.5938V8.90625H14.5862C14.9593 8.90625 15.3171 8.75809 15.5809 8.49437C15.8447 8.23065 15.9929 7.87296 15.9929 7.5C15.9929 7.12704 15.8447 6.76935 15.5809 6.50563C15.3171 6.24191 14.9593 6.09375 14.5862 6.09375Z" fill="white" />
                                            </g>
                                            <defs>
                                                <filter id="filter0_d_5955_363" x="0.988281" y="0" width="17.0039" height="17" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                                    <feOffset dx="1" dy="1" />
                                                    <feGaussianBlur stdDeviation="0.5" />
                                                    <feComposite in2="hardAlpha" operator="out" />
                                                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.2875 0 0 0 0 0.157534 0 0 0 0.5 0" />
                                                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5955_363" />
                                                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5955_363" result="shape" />
                                                </filter>
                                            </defs>
                                        </svg>
                                    </button>
                                    <span class="flex lg:flex-col max-lg:items-center max-lg:gap-x-4 text-center leading-4 text-xl">
                                        <strong class="text-4xl" data-min-max="<?php echo implode(',', $numbers) ?>">
                                            <?php echo $numbers[0]; ?>
                                        </strong>
                                        ┘å┘ü╪▒
                                    </span>
                                    <button type="button" data-action="minus" class="bg-gray-400 rounded p-2 aspect-square">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="5" viewBox="0 0 18 5" fill="none">
                                            <g filter="url(#filter0_d_5955_360)">
                                                <path d="M9.86603 0H14.555C14.9281 0 15.2858 0.158035 15.5496 0.43934C15.8135 0.720644 15.9617 1.10218 15.9617 1.5C15.9617 1.89783 15.8135 2.27936 15.5496 2.56066C15.2858 2.84197 14.9281 3 14.555 3H9.86603H7.05266H2.36371C1.99064 3 1.63284 2.84197 1.36904 2.56066C1.10524 2.27936 0.957031 1.89783 0.957031 1.5C0.957031 1.10218 1.10524 0.720644 1.36904 0.43934C1.63284 0.158035 1.99064 0 2.36371 0H7.05266H9.86603Z" fill="white" />
                                            </g>
                                            <defs>
                                                <filter id="filter0_d_5955_360" x="0.957031" y="0" width="17.0039" height="5" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                                    <feOffset dx="1" dy="1" />
                                                    <feGaussianBlur stdDeviation="0.5" />
                                                    <feComposite in2="hardAlpha" operator="out" />
                                                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.2 0" />
                                                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5955_360" />
                                                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5955_360" result="shape" />
                                                </filter>
                                            </defs>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div data-item-timestamp="<?php echo $item->time; ?>" data-item-sell-price="<?php echo $item->price ?>" class="box open cursor-pointer max-lg:h-12.5 h-[112px] rounded-lg w-full shadow-102 overflow-hidden relative">
                                <div class="back text-white bg-blue absolute w-full h-full flex lg:flex-col text-center justify-between ">
                                    <span class="text-2xl drop-shadow-104 flex grow w-full lg:justify-center items-center max-lg:px-2">
                                        <?php echo date('H:i', $item->time) ?>
                                    </span>
                                    <span class="text-md shrink-0 max-lg:flex items-center justify-center max-lg:px-2 drop-shadow-104 py-0.5 bg-black/15 w-30 lg:w-full text-center max-lg:text-22 max-lg:gap-1">
                                        <?php echo number_format($item->price) ?>
                                        <span class="max-lg:text-12">╪¬┘ê┘à╪º┘å</span>
                                    </span>
                                </div>
                                <div class="front text-textColor bg-white px-1.5 absolute top-full w-full h-full flex text-center justify-between transition-all duration-150 items-center">
                                    <button type="button" data-action="plus" class="bg-accent-450 rounded p-2 aspect-square">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="17" viewBox="0 0 18 17" fill="none">
                                            <g filter="url(#filter0_d_5955_363)">
                                                <path d="M14.5862 6.09375H9.89728V1.40625C9.89728 1.03329 9.74908 0.675604 9.48527 0.411881C9.22147 0.148158 8.86367 0 8.49059 0C8.11752 0 7.75972 0.148158 7.49592 0.411881C7.23211 0.675604 7.08391 1.03329 7.08391 1.40625V6.09375H2.39497C2.02189 6.09375 1.66409 6.24191 1.40029 6.50563C1.13649 6.76935 0.988281 7.12704 0.988281 7.5C0.988281 7.87296 1.13649 8.23065 1.40029 8.49437C1.66409 8.75809 2.02189 8.90625 2.39497 8.90625H7.08391V13.5938C7.08391 13.9667 7.23211 14.3244 7.49592 14.5881C7.75972 14.8518 8.11752 15 8.49059 15C8.86367 15 9.22147 14.8518 9.48527 14.5881C9.74908 14.3244 9.89728 13.9667 9.89728 13.5938V8.90625H14.5862C14.9593 8.90625 15.3171 8.75809 15.5809 8.49437C15.8447 8.23065 15.9929 7.87296 15.9929 7.5C15.9929 7.12704 15.8447 6.76935 15.5809 6.50563C15.3171 6.24191 14.9593 6.09375 14.5862 6.09375Z" fill="white" />
                                            </g>
                                            <defs>
                                                <filter id="filter0_d_5955_363" x="0.988281" y="0" width="17.0039" height="17" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                                    <feOffset dx="1" dy="1" />
                                                    <feGaussianBlur stdDeviation="0.5" />
                                                    <feComposite in2="hardAlpha" operator="out" />
                                                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.2875 0 0 0 0 0.157534 0 0 0 0.5 0" />
                                                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5955_363" />
                                                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5955_363" result="shape" />
                                                </filter>
                                            </defs>
                                        </svg>
                                    </button>
                                    <span class="flex lg:flex-col max-lg:items-center max-lg:gap-x-4 text-center leading-4 text-xl">
                                        <strong class="text-4xl"><?php echo $numbers[0]; ?></strong>
                                        ┘å┘ü╪▒
                                    </span>
                                    <button type="button" data-action="minus" class="bg-gray-400 rounded p-2 aspect-square">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="5" viewBox="0 0 18 5" fill="none">
                                            <g filter="url(#filter0_d_5955_360)">
                                                <path d="M9.86603 0H14.555C14.9281 0 15.2858 0.158035 15.5496 0.43934C15.8135 0.720644 15.9617 1.10218 15.9617 1.5C15.9617 1.89783 15.8135 2.27936 15.5496 2.56066C15.2858 2.84197 14.9281 3 14.555 3H9.86603H7.05266H2.36371C1.99064 3 1.63284 2.84197 1.36904 2.56066C1.10524 2.27936 0.957031 1.89783 0.957031 1.5C0.957031 1.10218 1.10524 0.720644 1.36904 0.43934C1.63284 0.158035 1.99064 0 2.36371 0H7.05266H9.86603Z" fill="white" />
                                            </g>
                                            <defs>
                                                <filter id="filter0_d_5955_360" x="0.957031" y="0" width="17.0039" height="5" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                                    <feOffset dx="1" dy="1" />
                                                    <feGaussianBlur stdDeviation="0.5" />
                                                    <feComposite in2="hardAlpha" operator="out" />
                                                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.2 0" />
                                                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5955_360" />
                                                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5955_360" result="shape" />
                                                </filter>
                                            </defs>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php } ?>

                    <?php break;
                    case "reserving": ?>
                        <div class="box reserving cursor-wait max-lg:h-12.5 h-[112px] rounded-lg w-full shadow-102 overflow-hidden relative">
                            <div class="back text-white bg-[#EDA10D] absolute w-full h-full flex lg:flex-col text-center justify-between">
                                <span class="text-2xl drop-shadow-104 flex grow w-full lg:justify-center items-center max-lg:px-2">
                                    <?php echo date('H:i', $item->time) ?>
                                </span>
                                <span class="text-md shrink-0 max-lg:flex items-center justify-center max-lg:px-2 drop-shadow-104 py-2 bg-black/15 w-30 lg:w-full max-lg:text-22">
                                    ╪»╪▒ ╪¡╪º┘ä ╪▒╪▓╪▒┘ê
                                </span>
                            </div>
                        </div>
                    <?php break;
                    case "reserved": ?>
                        <div class="box reserved cursor-not-allowed max-lg:h-12.5 h-[112px] rounded-lg w-full shadow-102 overflow-hidden relative">
                            <div class="back text-white bg-[#EF4E5D] absolute w-full h-full flex lg:flex-col text-center justify-between">
                                <span class="text-2xl drop-shadow-104 flex grow w-full lg:justify-center items-center max-lg:px-2">
                                    <?php echo date('H:i', $item->time) ?>
                                </span>
                                <span class="text-md shrink-0 max-lg:flex items-center justify-center max-lg:px-2 drop-shadow-104 py-2 bg-black/15 w-30 lg:w-full max-lg:text-22">
                                    ╪▒╪▓╪▒┘ê ╪┤╪»┘ç
                                </span>
                            </div>
                        </div>
                    <?php break;
                    case "non_reservable": ?>
                        <div class="box closed cursor-not-allowed max-lg:h-12.5 h-[112px] rounded-lg w-full shadow-102 overflow-hidden relative">
                            <div class="back text-textColor bg-[#F2F6FA] absolute w-full h-full flex lg:flex-col text-center justify-between">
                                <span class="text-2xl drop-shadow-104 flex grow w-full lg:justify-center items-center max-lg:px-2 blur-[1px]">
                                    <?php echo date('H:i', $item->time) ?>
                                </span>
                                <span class="text-md shrink-0 max-lg:flex items-center justify-center max-lg:px-2 drop-shadow-104 py-2 bg-black/15 w-30 lg:w-full max-lg:text-22">
                                    ╪¿╪│╪¬┘ç ╪┤╪»┘ç
                                </span>
                            </div>
                        </div>
            <?php break;
                }
            } ?>

        </div>
    <?php } ?>
</div>

<div class="reserve-result border max-lg:p-4 p-10 rounded-2xl text-lg hidden items-center max-lg:gap-0 gap-20 max-lg:flex-col">
    <div class="flex grow justify-between max-lg:flex-wrap max-lg:border max-lg:shadow-13 border-0 shadow-none max-lg:mb-4 max-lg:w-full max-lg:p-4 rounded-2xl">
        <span class="text-slate-130 max-lg:w-full">╪º┘å╪¬╪«╪º╪¿ ╪┤┘à╪º</span>
        <div class="selected-date flex gap-2"></div>
        <div class="ticket-count"></div>
    </div>
    <a href="#" class="bg-accent-420 flex rounded-xl overflow-hidden items-center justify-center text-white gap-3 max-lg:w-full max-lg:justify-between">
        <span class="flex items-center gap-3 py-3 px-16 max-lg:px-4">
            ┘╛╪▒╪»╪º╪«╪¬ ┘ê ╪½╪¿╪¬ ╪▒╪▓╪▒┘ê
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" viewBox="0 0 16 14" fill="none" class="mx-0">
                <path d="M0.509545 6.0725C0.252053 6.31859 0.107422 6.65219 0.107422 7C0.107422 7.34782 0.252053 7.68141 0.509545 7.9275L5.69421 12.8791C5.95216 13.1253 6.30201 13.2637 6.6668 13.2637C7.03158 13.2637 7.38143 13.1253 7.63938 12.8791C7.89732 12.6329 8.04224 12.299 8.04224 11.9508C8.04224 11.6025 7.89732 11.2686 7.63938 11.0224L4.80138 8.3125L13.9085 8.3125C14.2731 8.3125 14.6229 8.17422 14.8807 7.92808C15.1386 7.68194 15.2835 7.3481 15.2835 7C15.2835 6.6519 15.1386 6.31807 14.8807 6.07192C14.6229 5.82578 14.2731 5.6875 13.9085 5.6875L4.80138 5.6875L7.63938 2.9785C7.7671 2.85658 7.86841 2.71185 7.93754 2.55256C8.00666 2.39327 8.04224 2.22254 8.04224 2.05013C8.04224 1.87771 8.00666 1.70698 7.93754 1.54769C7.86841 1.3884 7.7671 1.24367 7.63938 1.12175C7.51166 0.999835 7.36003 0.903128 7.19315 0.837148C7.02628 0.771167 6.84742 0.737207 6.6668 0.737207C6.48617 0.737207 6.30731 0.771167 6.14044 0.837148C5.97356 0.903128 5.82193 0.999835 5.69421 1.12175L0.509545 6.0725Z" fill="white" />
            </svg>
        </span>
        <strong class="bg-accent-450 py-3 px-8 max-lg:px-4"></strong>
    </a>

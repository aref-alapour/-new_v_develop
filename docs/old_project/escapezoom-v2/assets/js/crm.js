jQuery(document).ready(function ($) {
    // Sidebar removed - menu items moved to header
    // start-happycall------------------------
    $(".toggle-button").click(function () {
        $(this).addClass("hidden");
        $(this).siblings(".tick-icon").removeClass("hidden");
    });

    $(".tick-icon").click(function () {
        $(this).addClass("hidden");
        $(this).siblings(".toggle-button").removeClass("hidden");
    });

    // ----------------------

    $(document).ready(function () {
        $(".openModal").on("click", function () {
            $("#modal").removeClass("hidden").addClass("flex");
        });

        $("#modal").on("click", function (e) {
            if ($(e.target).is("#modal")) {
                $("#modal").addClass("hidden").removeClass("flex");
            }
        });
    });

    // end-happycall------------------------

    // start-tabs-------------------------------------------
    $(document).ready(function () {
        $("#filterTabs .tab").click(function () {
            $("#filterTabs .tab")
                .removeClass("active text-navyBlue")
                .addClass("text-grayy");
            $(this).addClass("active text-navyBlue").removeClass("text-grayy");
        });
    });

    $(document).ready(function () {
        $("#filterTabs .taborder").click(function () {
            $("#filterTabs .taborder")
                .removeClass("active text-white")
                .addClass("text-grayy");
            $(this).addClass("active text-white").removeClass("text-grayy");
        });
    });
    // end-tabs-------------------------------------------

  

    //  -----modal-taeed-sanse--------------------------
    $(document).ready(function () {
        // نمایش مدال
        $("#openModalBtn").on("click", function () {
            $("#myModal").fadeIn(100);
        });

        // بستن مدال با دکمه "بستن"
        $(".closeModalBtn").on("click", function () {
            $("#myModal").fadeOut(300);
        });

        // بستن مدال با کلیک روی بک‌گراند سیاه
        $("#myModal").on("click", function (e) {
            if ($(e.target).is("#myModal")) {
                $(this).fadeOut(300);
            }
        });
    });

    $(document).ready(function () {
        $(document).click(function (event) {
            if (!$(event.target).closest(".dropdown").length) {
                $(".dropdown-content").hide();
            }
        });

        $(".dropdown > button").click(function (e) {
            e.stopPropagation();
            const dropdown = $(this).siblings(".dropdown-content");
            $(".dropdown-content").not(dropdown).hide();
            dropdown.toggle();
        });
    });

    //   -----modal-page-comments---------------------------------------
    $(document).ready(function () {
        $("#openModal").click(function () {
            $("#modalOverlay").removeClass("hidden");
            $("#deleteReasonBox").addClass("hidden");
        });

        $("#modalOverlay").click(function (e) {
            if (!$(e.target).closest("#modalContent").length) {
                $("#modalOverlay").addClass("hidden");
            }
        });

        $("#showDeleteReason").click(function () {
            $("#deleteReasonBox").slideDown(500);
        });
    });

    //  -----modalLevel-in-page-comment-----------------------

    $(document).ready(function () {
        $(".openModalLevel").click(function () {
            $("#modalOverlaylevel").removeClass("hidden");
        });

        $("#modalOverlaylevel").click(function (e) {
            if (!$(e.target).closest("#modalContentlevel").length) {
                $("#modalOverlaylevel").addClass("hidden");
            }
        });
    });

    //  ----close-all-sass-----------------------------

    let isOn = true;

    $("#toggleSwitch").click(function () {
        isOn = !isOn;

        if (isOn) {
            $(this).removeClass("bg-gray-300").addClass("bg-on");
            $("#knob").addClass("knob-on");
        } else {
            $(this).removeClass("bg-on").addClass("bg-gray-300");
            $("#knob").removeClass("knob-on");
        }
    });

    // ---modal-ezerv-sans-info------------------------

    $(document).ready(function () {
        $(".openModalInfo").click(function () {
            $("#modalOverlayInfo").removeClass("hidden");
        });

        $("#modalOverlayInfo").click(function (e) {
            if (!$(e.target).closest("#modalContentInfo").length) {
                $("#modalOverlayInfo").addClass("hidden");
            }
        });
    });

    // document.addEventListener('DOMContentLoaded', function () {
    const emblaNode = document.querySelector(".embla");
    const embla = EmblaCarousel(emblaNode, {
        loop: false,
        dragFree: true,
    });
    // });

    $(document).ready(function () {
        $(".toggle-btn").click(function () {
            let isOpen = $(this).text().trim() === "باز";

            if (isOpen) {
                $(this)
                    .text("بسته")
                    .removeClass("bg-[#04B968] text-white")
                    .addClass("bg-[#DBE2EA] text-black");
            } else {
                $(this)
                    .text("باز")
                    .removeClass("bg-[#DBE2EA] text-black")
                    .addClass("bg-[#04B968] text-white");
            }
        });
    });

    // -----game-finder-----------------------------------





 
  const formatNumber = (n) => n.toLocaleString('fa-IR');

  function updateSlider() {
    const $min = $('#min-range');
    const $max = $('#max-range');
    const $tooltipMin = $('#min-tooltip');
    const $tooltipMax = $('#max-tooltip');

    const minVal = parseInt($min.val());
    const maxVal = parseInt($max.val());

    const rangeMin = parseInt($min.attr('min'));
    const rangeMax = parseInt($min.attr('max'));

    const minPercent = ((minVal - rangeMin) / (rangeMax - rangeMin)) * 100;
    const maxPercent = ((maxVal - rangeMin) / (rangeMax - rangeMin)) * 100;

    // پر کردن میانه‌ی اسلایدر
    $('#range-fill').css({
      left: `${Math.min(minPercent, maxPercent)}%`,
      width: `${Math.abs(maxPercent - minPercent)}%`
    });

    // موقعیت عددهای بالا
    $tooltipMin
      .text(formatNumber(minVal))
      .css('left', `calc(${minPercent}% - 20px)`); // تقریباً وسط thumb

    $tooltipMax
      .text(formatNumber(maxVal))
      .css('left', `calc(${maxPercent}% - 20px)`);
  }

  $(document).ready(function () {
    $('#min-range, #max-range').on('input', updateSlider);
    updateSlider(); // بار اول
  });














});

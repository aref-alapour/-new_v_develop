document.addEventListener("DOMContentLoaded", () => {
  let citiesMenu = new Swiper(".cities", {
    slidesPerView: 3,
    slidesPerGroup: 3,
    grid: {
      rows: 2,
      fill: "row",
    },
    spaceBetween: 30,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });

  const carouselsNormal = document.querySelectorAll(".embla_normal");
  const carouselsFade = document.querySelectorAll(".embla_fade");
  const carouselsCommentMainLg = document.querySelector(
    ".embla_comments_main_lg"
  );
  const carouselsCommentMainMd = document.querySelector(
    ".embla_comments_main_md"
  );
  const carouselsCommentThumbs = document.querySelector(
    ".embla_comments_thumbs_md"
  );
  if (carouselsNormal.length > 0) {
    carouselsNormal.forEach((carousel) => {
      const viewportNode = carousel.querySelector(".embla__viewport");
      const prevBtn = carousel.querySelector(".embla__button--prev");
      const nextBtn = carousel.querySelector(".embla__button--next");
      const options = {
        axis: "x",
        dragFree: true,
        direction: "rtl",
        align: "center",
      };
      let embla = EmblaCarousel(viewportNode, options);
      const updateButtons = () => {
        const isWide = window.innerWidth > 720;
        if (prevBtn)
          prevBtn.style.display =
            isWide && embla.canScrollPrev() ? "block" : "none";
        if (nextBtn)
          nextBtn.style.display =
            isWide && embla.canScrollNext() ? "block" : "none";
      };
      embla.on("select", updateButtons);
      embla.on("reInit", updateButtons);
      updateButtons();
      if (prevBtn) prevBtn.addEventListener("click", () => embla.scrollPrev());
      if (nextBtn) nextBtn.addEventListener("click", () => embla.scrollNext());
      window.addEventListener("resize", () => {
        updateButtons();
      });
    });
  }
  if (carouselsCommentMainLg) {
    const viewportNode =
      carouselsCommentMainLg.querySelector(".embla__viewport");
    const options = {
      loop: true,
      direction: "rtl",
      dragFree: true,
      containScroll: "trimSnaps",
      align: "start",
      duration: 500,
      slidesToScroll: 1,
    };

    const autoscrollOptions = {
      direction: "forward",
      speed: 1,
      startDelay: 1000,
      active: true,
      playOnInit: true,
      stopOnFocusIn: true,
      stopOnInteraction: false,
      stopOnMouseEnter: true,
      rootNode: null,
    };

    const embla = EmblaCarousel(viewportNode, options, [
      EmblaCarouselAutoScroll(autoscrollOptions),
    ]);

    const autoscroll = embla.plugins().autoScroll;
    let isUserInteracting = false;
    let interactionTimeout;

    embla.on("select", () => {
      const slides = embla.slideNodes();
      const currentIndex = embla.selectedScrollSnap();

      // اگر به آخرین اسلاید رسیدیم
      if (currentIndex === slides.length - 1) {
        // توقف موقت اتوسکرول
        if (autoscroll) {
          autoscroll.stop();
        }

        // برگشت به ابتدا
        setTimeout(() => {
          embla.scrollTo(0, { duration: 500 });
          // شروع مجدد اتوسکرول بعد از برگشت به ابتدا
          setTimeout(() => {
            if (autoscroll && !isUserInteracting) {
              autoscroll.play();
            }
          }, 1000);
        }, 1000);
      }
    });

    // کنترل تعامل کاربر
    embla.on("pointerDown", () => {
      isUserInteracting = true;
      if (autoscroll) {
        autoscroll.stop();
      }
    });

    embla.on("pointerUp", () => {
      isUserInteracting = false;
      // پاک کردن تایمر قبلی
      clearTimeout(interactionTimeout);

      // شروع مجدد اتوسکرول بعد از ۲ ثانیه
      interactionTimeout = setTimeout(() => {
        if (autoscroll && !isUserInteracting) {
          autoscroll.play();
        }
      }, 2000);
    });

    // پاکسازی تایمرها
    embla.on("destroy", () => {
      clearTimeout(interactionTimeout);
    });
  }
  if (carouselsCommentMainMd) {
    const mainNode = carouselsCommentMainMd.querySelector(".embla__viewport");
    const thumbsNode = carouselsCommentThumbs.querySelector(".embla__viewport");

    const mainOptions = {
      axis: "y",
      loop: true,
      dragFree: false,
      containScroll: "trimSnaps",
    };

    const thumbsOptions = {
      axis: "y",
      containScroll: "trimSnaps",
      dragFree: false,
    };

    const mainCarousel = EmblaCarousel(mainNode, mainOptions, [
      EmblaCarouselAutoplay({ delay: 3000, stopOnInteraction: false }),
    ]);
    const thumbsCarousel = EmblaCarousel(thumbsNode, thumbsOptions);

    // Add click event listeners to thumbnails
    const thumbSlides = thumbsCarousel.slideNodes();
    thumbSlides.forEach((slide, index) => {
      slide.addEventListener("click", () => {
        mainCarousel.scrollTo(index);
      });
    });

    // Sync main carousel with thumbnails
    mainCarousel.on("select", () => {
      const selected = mainCarousel.selectedScrollSnap();
      thumbsCarousel.scrollTo(selected);
      updateThumbButtons();
    });

    // Sync thumbnails with main carousel
    thumbsCarousel.on("select", () => {
      const selected = thumbsCarousel.selectedScrollSnap();
      mainCarousel.scrollTo(selected);
    });

    // Update thumbnail buttons
    function updateThumbButtons() {
      const slides = thumbsCarousel.slideNodes();
      slides.forEach((slide, index) => {
        if (index === mainCarousel.selectedScrollSnap()) {
          slide.classList.add("embla-thumbs__slide--selected");
        } else {
          slide.classList.remove("embla-thumbs__slide--selected");
        }
      });
    }

    // Initialize thumbnails
    updateThumbButtons();
  }
  if (carouselsFade.length > 0) {
    carouselsFade.forEach((carousel) => {
      const viewportNode = carousel.querySelector(".embla__viewport");
      const prevBtn = carousel.querySelector(".embla__button--prev");
      const nextBtn = carousel.querySelector(".embla__button--next");
      const dotsNode = document.querySelector(".embla__dots");
      const options = {
        loop: true,
        duration: 20,
        direction: "rtl",
        align: "center",
      };
      const autoplayOptions = {
        delay: 3500,
        stopOnInteraction: false,
        stopOnMouseEnter: true,
      };

      const embla = EmblaCarousel(viewportNode, options, [
        EmblaCarouselFade(),
        EmblaCarouselAutoplay(autoplayOptions),
      ]);

      function updateSelectedSlide() {
        const slides = embla.slideNodes();
        const selectedIndex = embla.selectedScrollSnap();
        slides.forEach((slide, index) => {
          slide.classList.toggle("is-selected", index === selectedIndex);
        });
      }

      function createDots() {
        const dots = embla.slideNodes().map((_, index) => {
          const dot = document.createElement("button");
          dot.classList.add("embla__dot");
          dot.dataset.index = index;
          dot.addEventListener("click", () => {
            embla.scrollTo(index, true);
            updateSelectedSlide();
            updateDots();
            const autoplay = embla.plugins()["autoplay"];
            if (autoplay) {
              autoplay.reset();
            }
          });
          dotsNode.appendChild(dot);
          return dot;
        });
      }

      function updateDots() {
        const dots = dotsNode.querySelectorAll(".embla__dot");
        const selectedIndex = embla.selectedScrollSnap();
        dots.forEach((dot, index) => {
          dot.classList.toggle("is-selected", index === selectedIndex);
        });
      }

      embla.on("init", () => {
        createDots();
        updateSelectedSlide();
        updateDots();
      });

      embla.on("select", () => {
        updateSelectedSlide();
        updateDots();
      });
      prevBtn.addEventListener("click", () => {
        embla.scrollPrev(true);
        const autoplay = embla.plugins()["autoplay"];
        if (autoplay) {
          autoplay.reset();
        }
      });
      nextBtn.addEventListener("click", () => {
        embla.scrollNext(true);
        const autoplay = embla.plugins()["autoplay"];
        if (autoplay) {
          autoplay.reset();
        }
      });
    });
  }
});
jQuery(document).ready(function ($) {
  let baseUrlQuery =
    ""/wp-admin/admin-ajax.php?action=v2_ajax_handler&callback=queryable_search"";
  let baseUrlWebService =
    ""/wp-admin/admin-ajax.php?action=v2_ajax_handler"";
  if (location.hostname === "localhost") {
    baseUrlQuery =
      ""/wp-admin/admin-ajax.php?action=v2_ajax_handler&callback=queryable_search"";
    baseUrlWebService =
      ""/wp-admin/admin-ajax.php?action=v2_ajax_handler"";
  }
  $("body").on("click", ".mobile-hover", function () {
    let parent = $(this).parent();
    parent.next("a").css("display", "flex");
  });
  $("body").on("click", ".slider-event a", function () {
    let sliderTarget = $(this).closest(".slider-event").data("slider-event");
    let slideIndex = parseInt($(this).closest(".embla__slide").index()) + 1;
    let currentPage = window.location.href;
    let selectedSlide = $(this);
    let productSelect = {
      name: selectedSlide
        .closest(".embla__slide")
        .find('[name="title"]')
        .text()
        .trim(),
      url: selectedSlide.attr("href"),
      address: selectedSlide
        .closest(".embla__slide")
        .find('[name="address"]')
        .text()
        .trim(),
      genre: selectedSlide
        .closest(".embla__slide")
        .find('[name="genres"]')
        .text()
        .trim(),
      price: selectedSlide
        .closest(".embla__slide")
        .find('[name="price"]')
        .text()
        .trim(),
      rate: selectedSlide
        .closest(".embla__slide")
        .find('[name="rate"]')
        .text()
        .trim(),
      product_id: selectedSlide
        .closest(".embla__slide")
        .data("product-id")
        .toString(),
    };

    let otherProducts = [];
    $(this)
      .closest(".slider-event")
      .find(".embla__slide")
      .not(selectedSlide.closest(".embla__slide"))
      .each(function () {
        otherProducts.push({
          name: $(this).find('[name="title"]').text().trim(),
          url: $(this).find("a").first().attr("href"),
          address: $(this).find('[name="address"]').text().trim(),
          genre: $(this).find('[name="genres"]').text().trim(),
          price: $(this).find('[name="price"]').text().trim(),
          rate: $(this).find('[name="rate"]').text().trim(),
          product_id: $(this).data("product-id").toString(),
        });
      });

    let sliderData = {
      slide_index: slideIndex,
      selected_product: productSelect,
      other_products: otherProducts,
      current_page: currentPage,
    };
    zebline.event.track(sliderTarget, sliderData);
  });
  /*search-ajax*/
  $("#lg-search").on("keyup", function () {
    let searchValue = $(this).val();
    let valCount = searchValue.length;
    if (valCount >= 1) {
      setTimeout(function () {
        $.ajax({
          type: "POST",
          url: baseUrlQuery,
          data: {
            source: "home_header_search",
            term: searchValue,
          },
          dataType: "json",
          beforeSend: function () {
            $("#lg-search-form .relative #lg-search-alert").remove();
            $("#lg-search-form .relative").append(
              '<p id="lg-search-alert" class="text-3xs text-primary-500 absolute mt-1">در حال جستجو <span class="loading-dots"></span></p>'
            );
          },
          success: function (data) {
            $("#lg-search-form .relative #lg-search-alert").remove();
            $("#lg-search-container #lg-search-result").remove();
            let searchResult = `
                      <div id="lg-search-result" class="absolute z-10 mt-4 w-full rounded-lg border bg-white shadow-[0px_4px_4px_0px_rgba(0,0,0,.1)]">
                          <div class="relative">
                              <button id="lg-search-close" class="text-red-600 hover:text-primary-600 leading-[10px] transition absolute text-[25px] left-3 top-2">×</button>
                              <div id="lg-search-result-list" class="max-h-75 divide-y divide-[#E4EBF0] overflow-y-auto px-4 py-5">
                              </div>
                          </div>
                      </div>
                      `;
            $("#lg-search-container").append(searchResult);
            if (data) {
              setTimeout(function () {
                /*if (data.length > 10){
                                  $('#lg-search-container #lg-search-result .relative').append('<button id="lg-search-submit" type="button" class="mx-auto block w-fit py-2 text-primary-500">مشاهده همه</button>')
                              }*/
                data.forEach((element) => {
                  let url = location.origin + "/room/" + element.url;
                  /*let url = 'https://' + location.hostname + '/room/' + element.url*/
                  let resultItem = `  
                                  <a href="${url}" class="flex items-center gap-x-2 py-2">
                                      <img src="${element.image}" alt="اسکیپ‌زوم - ${element.title}" class="h-10 w-7.5 rounded">
                                      <span>${element.title}</span>
                                  </a>
                              `;
                  $(
                    "#lg-search-container #lg-search-result .relative #lg-search-result-list"
                  ).append(resultItem);
                });
              }, 1);
            } else {
              $(
                "#lg-search-container #lg-search-result .relative #lg-search-result-list"
              ).append("<p>سرگرمی یافت نشد</p>");
            }
          },
        });
      }, 1);
    } else {
      $("#lg-search-form .relative #lg-search-alert").remove();
      $("#lg-search-container #lg-search-result").remove();
    }
  });
  $("body").on("click", "#lg-search-close", function () {
    $("#lg-search-form .relative #lg-search-alert").remove();
    $("#lg-search-container #lg-search-result").remove();
    $("#lg-search").val("");
  });
  /*$('body').on('click', '#lg-search-submit', function() {
      $('#lg-search-form').submit();
  });*/
  $("body").on("submit", "#lg-search-form", function (e) {
    e.preventDefault();
  });
  /*$('#lg-search').on('keydown', function(event) {
      if (event.key === 'Enter') {
          event.preventDefault();
          $('#lg-search-form').submit();
      }
  });*/
  $("#sm-search").on("keyup", function () {
    let searchValue = $(this).val();
    let valCount = searchValue.length;
    if (valCount >= 1) {
      setTimeout(function () {
        $.ajax({
          type: "POST",
          url: baseUrlQuery,
          data: {
            source: "home_header_search",
            term: searchValue,
          },
          dataType: "json",
          beforeSend: function () {
            $("#sm-search-form .relative #sm-search-alert").remove();
            $("#sm-search-form .relative").append(
              '<p id="sm-search-alert" class="text-3xs text-primary-500 absolute mt-1">در حال جستجو <span class="loading-dots"></span></p>'
            );
          },
          success: function (data) {
            $("#sm-search-form .relative #sm-search-alert").remove();
            $("#sm-search-container #sm-search-result").remove();
            let searchResult = `
                      <div id="sm-search-result" class="absolute z-10 mt-4 w-full rounded-lg border bg-white shadow-[0px_4px_4px_0px_rgba(0,0,0,.1)]">
                          <div class="relative">
                              <button id="sm-search-close" class="text-red-600 hover:text-primary-600 leading-[10px] transition absolute text-[25px] left-3 top-2">×</button>
                              <div id="sm-search-result-list" class="max-h-75 divide-y divide-[#E4EBF0] overflow-y-auto px-4 py-5">
                              </div>
                          </div>
                      </div>
                      `;
            $("#sm-search-container").append(searchResult);
            if (data) {
              setTimeout(function () {
                /*if (data.length > 10){
                                  $('#sm-search-container #sm-search-result .relative').append('<button id="sm-search-submit" type="button" class="mx-auto block w-fit py-2 text-primary-500">مشاهده همه</button>')
                              }*/
                data.forEach((element) => {
                  let url = location.origin + "/room/" + element.url;
                  let resultItem = `  
                                  <a href="${url}" class="flex items-center gap-x-2 py-2">
                                      <img src="${element.image}" alt="اسکیپ‌زوم - ${element.title}" class="h-10 w-7.5 rounded">
                                      <span>${element.title}</span>
                                  </a>
                              `;
                  $(
                    "#sm-search-container #sm-search-result .relative #sm-search-result-list"
                  ).append(resultItem);
                });
              }, 1);
            } else {
              $(
                "#sm-search-container #sm-search-result .relative #sm-search-result-list"
              ).append("<p>سرگرمی یافت نشد</p>");
            }
          },
        });
      }, 1);
    } else {
      $("#sm-search-form .relative #sm-search-alert").remove();
      $("#sm-search-container #sm-search-result").remove();
    }
  });
  $("body").on("click", "#sm-search-close", function () {
    $("#sm-search-form .relative #sm-search-alert").remove();
    $("#sm-search-container #sm-search-result").remove();
    $("#sm-search").val("");
  });
  /*$('body').on('click', '#sm-search-submit', function() {
      $('#sm-search-form').submit();
  });*/
  $("body").on("submit", "#sm-search-form", function (e) {
    e.preventDefault();
  });
  /*$('#sm-search').on('keydown', function(event) {
      if (event.key === 'Enter') {
          event.preventDefault();
          $('#sm-search-form').submit();
      }
  });*/
  $(".scrollable").each(function () {
    const $scrollable = $(this);
    let isDown = false;
    let startX;
    let scrollLeft;

    $scrollable.on("mousedown", function (e) {
      isDown = true;
      $scrollable.addClass("active");
      startX = e.pageX - $scrollable.offset().left;
      scrollLeft = $scrollable.scrollLeft();
    });

    $scrollable.on("mouseleave mouseup", function () {
      isDown = false;
      $scrollable.removeClass("active");
    });

    $scrollable.on("mousemove", function (e) {
      if (!isDown) return; // اگر دکمه ماوس فشرده نشده، کاری انجام ندهید
      e.preventDefault();
      const x = e.pageX - $scrollable.offset().left;
      const walk = (x - startX) * 2; // سرعت scroll را تنظیم کنید
      $scrollable.scrollLeft(scrollLeft - walk);
    });
  });
  $(".dropdown-button").on("click", function () {
    $(this).next(".options").toggleClass("max-md:hidden"); // فقط دایو بعدی را هدف قرار می‌دهیم
  });

  $("#open-mobile-nav").on("click", function () {
    $(".mobile-menu-modal").toggleClass("hidden").toggleClass("flex");
  });
  $(".accordion-title").on("click", function () {
    $(this).next().slideToggle();
    $(this).find("svg:first-child").toggleClass("hidden");
    $(this).find("svg:last-child").toggleClass("hidden");
    return false;
  });
  $("#open-mobile-nav").on("click", function () {
    $(this).find("svg:first-child").toggleClass("hidden");
    $(this).find("svg:last-child").toggleClass("hidden");

    $(".mobile-navigation").toggleClass("scale-0 scale-100");
  });
  $(".accordion-item-title").on("click", function () {
    $(this).next(".accordion-item-content").slideToggle(300);
    $(this).find("svg:nth-of-type(2)").toggleClass("-rotate-90");
  });
  $("[data-hover-target]").hover(function () {
    let target = $(this).data("hover-target");

    $(`.submenu`).addClass("hidden").removeClass("grid");
    $(`#${target}`).removeClass("hidden").addClass("grid");
  });
  // انتخاب گزینه
  $(".option").on("click", function () {
    const selectedCity = $(this).text();
    const cityParams = $(this).data("params");
    // به‌روزرسانی متن دکمه با شهر انتخاب شده
    $(this)
      .closest(".dropdown-container")
      .find(".dropdown-button span")
      .text(selectedCity);
    // پنهان کردن گزینه‌ها
    $(this).closest(".options").addClass("max-md:hidden");
  });
  // پنهان کردن منوی کشویی اگر کلیکی خارج از آن صورت بگیرد
  $(document).on("click", function (e) {
    if (!$(e.target).closest(".dropdown-container").length) {
      $(".options").addClass("max-md:hidden");
    }
  });
  $("#open-mobile-nav").on("click", function () {
    $("#mobile-header").toggleClass("bg-[#EDF2F5] bg-white shadow-104");
    $(".mobile-menu-modal").toggleClass("hidden");
  });
  // slider-ajax
  $("body").on("click", ".filter-btn", function (e) {
    let parentDiv = $(this).parent();
    let inputName = $(this).attr("data-input");
    let filterButtons = parentDiv.find(".filter-btn");
    filterButtons
      .removeClass(
        "bg-primary-500 md:border-primary-500 text-slate-100 bg-white text-white"
      )
      .addClass("md:border-gray-50 bg-white text-slate-350");
    $(this)
      .removeClass("md:bg-white text-slate-350 md:border-gray-50 bg-white")
      .addClass("bg-primary-500 md:border-primary-500 text-slate-100");
    filterButtons.not(this).prop("disabled", false);
    $(this).prop("disabled", true);
    let params = $(this).attr("data-params");
    let keyParams = params.split(":");
    if (keyParams[0] === "city_id") {
      $(`#${inputName}-title`).text($(this).text().trim());
    }
    let actionType = null;
    const actionTypeMap = {
      city_id: "شهر",
      tag: "سبک بازی",
      sort_type: "مرتب‌ سازی",
    };
    actionType = actionTypeMap[keyParams[0]] || null;
    let currentPage = window.location.href;
    let zeblineData = {
      action_type: actionType,
      action_value: $(this).text(),
      current_page: currentPage,
    };
    zebline.event.track(inputName, zeblineData);
    let input = $(`#${inputName}`);
    let inputSource = input.attr("data-source");
    let currentParams = JSON.parse(input.attr("data-params"));
    $.each(currentParams, function (key, value) {
      if (key === keyParams[0]) {
        currentParams[key] = JSON.parse(keyParams[1]);
      }
    });
    let resultString = JSON.stringify(currentParams);
    input.attr("data-params", resultString);
    $.ajax({
      type: "POST",
      url: baseUrlWebService,
      data: {
        async: false,
        type: "sort_products_get",
        data: {
          source: inputSource,
          params: currentParams,
        },
      },
      dataType: "json",
      beforeSend: function () {
        $(`.${inputName}-btn`).hide();
        $(`#${inputName}-slider`)
          .empty()
          .append(
            '<div style="height:350px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;">لطفا منتظر باشید...</div>'
          );
      },
      success: function (data) {
        if (data.products) {
          setTimeout(function () {
            $(`#${inputName}-slider`).empty().append(data.products);
            if (window.innerWidth > 720) {
              $(`.${inputName}-btn`).show();
            }
          }, 1);
        } else {
          $(`#${inputName}-slider`)
            .empty()
            .append(
              '<div style="height:350px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;">سرگرمی یافت نشد</div>'
            );
        }
      },
    });
  });
  $(".expand-menu").on("click", function () {
    $(this).next(".submenu").slideToggle();
  });
  $(".footer-read-more-about").on("click", function () {
    $(this).prev().find("span").removeClass("hidden");
    $(this).remove();
  });
  // Add performance optimizations
  $(".swiper-slide").css({
    "will-change": "transform",
    transform: "translateZ(0)",
    "backface-visibility": "hidden",
  });

  $("input.option-sans-input:checked").each(function () {
    const optionParam = $(this).parent().text().trim(); // متن گزینه انتخاب شده
    $(this)
      .closest(".sans-dropdown-container")
      .find(".sans-dropdown-button span")
      .text(optionParam);
  });
  $('input[name="product_type"]').on("click", function () {
    let value = $(this).val();
    let baseUrl =
      "https://" +
      location.hostname +
      "/wp-content/themes/app/functions/helper/cities_type.php";
    if (location.hostname === "localhost") {
      baseUrl =
        "http://" +
        location.hostname +
        "/escapezoom_wp/wp-content/themes/app/functions/helper/cities_type.php";
    }
    $.ajax({
      type: "POST",
      url: baseUrl,
      data: {
        product_type: value,
      },
      dataType: "json",
      beforeSend: function () {
        $("#cities-box-list").empty();
        $("#cities-box-title")
          .empty()
          .append(
            '<span class="absolute text-nowrap line-clamp-1 text-xs p-0" style="top: -8px">لطفا منتظر باشید <span class="loading-dots"></span></span>'
          );
      },
      success: function (data) {
        $("#cities-box-title").empty().text("شهر");
        if (data) {
          $.each(data.data, function (index, data) {
            let city = `
                      <label class="option-sans block w-full hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2">
                                                  <input type="radio" name="city_id" value="${data["city_id"]}" class="hidden option-sans-input"/>${data["city_name"]}</label>
                      `;
            $("#cities-box-list").append(city);
          });
        } else {
          $("#cities-box-list").text("شهری یافت نشد");
        }
      },
    });
  });
  $(".sans-dropdown-button").on("click", function () {
    $(".sans-options").addClass("hidden");
    $(this).next(".sans-options").toggleClass("hidden");
  });

  // انتخاب گزینه
  $("body").on("click", ".option-sans", function () {
    const selectOption = $(this).text().trim();
    const optionParam = $(this).find("input").val();

    // به‌روزرسانی متن دکمه با شهر انتخاب شده
    $(this)
      .closest(".sans-dropdown-container")
      .find(".sans-dropdown-button span")
      .text(selectOption);

    // پنهان کردن گزینه‌ها
    $(this).closest(".sans-options").addClass("hidden");
  });

  // پنهان کردن منوی کشویی اگر کلیکی خارج از آن صورت بگیرد
  $(document).on("click", function (e) {
    if (!$(e.target).closest(".sans-dropdown-container").length) {
      $(".sans-options").addClass("hidden");
    }
  });
  // جستجو در شهرها
  $(".city-search").on("input", function () {
    const searchValue = $(this).val().toLowerCase();
    $(this)
      .closest(".sans-dropdown-container")
      .find(".option-sans")
      .each(function () {
        const cityName = $(this).text().toLowerCase();
        $(this).toggle(cityName.includes(searchValue));
      });
  });
  $(".share").on("click", async function () {
    let title = $(this).data("title"),
      text = $(this).data("content"),
      url = $(this).data("url");
    try {
      await navigator.share({
        title,
        text,
        url,
      });
    } catch (error) {}
  });

  // a tag
  $("body").on("click", "a", function () {
    let href = $(this).attr("href");
    if (href.startsWith("#")) {
      if ($(href).length > 0) {
        $("html, body").animate(
          { scrollTop: $(href).offset().top - 150 },
          1000
        );
      }
    }
  });

  $(".help").each((index, item) => {
    let content = $(item).data("help");
    tippy(item, {
      content: content,
      animation: "perspective-extreme",
    });
  });

  /************* Faghihi Codes *************/
  // show and hide the text----------------------------
  $("#text1").show();
  $("#text2").hide();
  $("#text3").hide();

  function showText(textId) {
    $("#text1, #text2, #text3").hide();
    $(textId).show();
  }

  $("#titr1").click(function () {
    showText("#text1");
  });

  $("#titr2").click(function () {
    showText("#text2");
  });

  $("#titr3").click(function () {
    showText("#text3");
  });

  $(".question-btn").on("click", function () {
    const targetContent = $(this).attr("data-target-content");
    const answerContent = $("#" + targetContent);
    if (answerContent.css("height") !== "0px") {
      answerContent.css("height", "0px");
      $(this).find("button").css("transform", "rotate(0deg)");
    } else {
      $(".answer-content").css("height", "0px");
      $(".question-btn button").css("transform", "rotate(0deg)");

      answerContent.css("height", answerContent.prop("scrollHeight") + "px");
      $(this).find("button").css("transform", "rotate(45deg)");
    }

    return false;
  });

  $(".question-btn-org").on("click", function () {
    const targetContent = $(this).attr("data-target-content");
    const answerContent = $("#" + targetContent);

    if (answerContent.css("height") !== "0px") {
      answerContent.css("height", "0px");
      $(this).find("button").css("transform", "rotate(0deg)");
    } else {
      $(".answer-content").css("height", "0px");
      $(".question-btn button").css("transform", "rotate(0deg)");

      answerContent.css("height", answerContent.prop("scrollHeight") + "px");
      $(this).find("button").css("transform", "rotate(180deg)");
    }
  });

  const items = document.querySelectorAll(".item");
  const texts = document.querySelectorAll('[id^="text"]');

  function initialize() {
    if (items.length > 0 && texts.length > 0) {
      items[0].classList.add("active");
      texts[0].classList.remove("hidden");
      texts[0].classList.add("visible");
    }
  }

  initialize();

  items.forEach((item, index) => {
    item.addEventListener("click", () => {
      items.forEach((i) => i.classList.remove("active"));
      texts.forEach((text) => {
        text.classList.add("hidden");
        text.classList.remove("visible");
      });
      item.classList.add("active");
      texts[index].classList.remove("hidden");
      texts[index].classList.add("visible");
    });
  });

  // swiper-videos-----------------------------------
  const swiper1 = new Swiper(".mySwiper", {
    loop: true,
    slidesPerView: 3,
    spaceBetween: "-30px",
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
      1025: {
        slidesPerView: 2.8,
        spaceBetween: "-80px",
      },
    },

    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
  });

  // swiper-comments-----------------------------------
  const swiper3 = new Swiper(".mySwiper-comments", {
    loop: true,
    slidesPerView: 3,
    spaceBetween: 30,
    centeredSlides: true,

    pagination: {
      el: ".swiper-pagination3",
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
      // 50: {
      //   slidesPerView: 1.2,
      //   spaceBetween: "-180px",
      // },
      320: {
        slidesPerView: 1.5,
        spaceBetween: "20",
      },
      900: {
        slidesPerView: 1.8,
        spaceBetween: 1,
      },
      1125: {
        slidesPerView: 2,
        spaceBetween: 1,
      },
    },
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
  });

  // swiper-events-----------------------------------
  var swiperevents = new Swiper(".mySwiperevents", {
    slidesPerView: "4",
    spaceBetween: 30,
    pagination: {
      el: ".swiper-pagination2",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },

    breakpoints: {
      320: {
        slidesPerView: 1.6,
        spaceBetween: 30,
      },
      1025: {
        slidesPerView: 4,
        spaceBetween: 40,
      },
    },
  });
  /************* Faghihi Codes *************/

  // Move search overlay to desktop container on desktop screens
  function moveSearchOverlay() {
    const searchOverlay = $("#search-overlay");
    const desktopContainer = $("#search-container-desktop");
    
    if (window.innerWidth >= 1024 && desktopContainer.length) {
      // Desktop: move to container
      if (searchOverlay.parent().attr("id") !== "search-container-desktop") {
        desktopContainer.append(searchOverlay);
      }
    } else {
      // Mobile: move back to body/main area
      if (searchOverlay.parent().attr("id") === "search-container-desktop") {
        $("nav.mobile-navbar").after(searchOverlay);
      }
    }
  }
  
  // Run on load and resize
  moveSearchOverlay();
  $(window).on("resize", moveSearchOverlay);

  // Search button toggle functionality (Mobile & Desktop)
  $("#search-btn, #search-btn-desktop").on("click", function (e) {
    e.stopPropagation();
    
    // Toggle active class on both buttons
    $("#search-btn, #search-btn-desktop").toggleClass("active");
    
    // Toggle icons for mobile
    $("#search-btn .icon-default").toggleClass("hidden");
    $("#search-btn .icon-active").toggleClass("hidden");
    
    // Toggle icons for desktop
    $("#search-btn-desktop .icon-default-desktop").toggleClass("hidden");
    $("#search-btn-desktop .icon-active-desktop").toggleClass("hidden");
    
    // Toggle overlay
    $("#search-overlay").toggleClass("hidden");
    
    // Close general overlay when opening search
    if (!$("#search-overlay").hasClass("hidden")) {
      $("#general-overlay").addClass("hidden");
      // Reset mobile nav icons
      $("#open-mobile-nav2 .burger-icon").removeClass("hidden");
      $("#open-mobile-nav2 .close-icon").addClass("hidden");
    }
  });

  // Close search overlay with close button
  $("#search-close-btn").on("click", function () {
    // Reset mobile button
    $("#search-btn").removeClass("active");
    $("#search-btn .icon-default").removeClass("hidden");
    $("#search-btn .icon-active").addClass("hidden");
    
    // Reset desktop button
    $("#search-btn-desktop").removeClass("active");
    $("#search-btn-desktop .icon-default-desktop").removeClass("hidden");
    $("#search-btn-desktop .icon-active-desktop").addClass("hidden");
    
    $("#search-overlay").addClass("hidden");
    
    // Reset search input and states
    $("#search-main-input").val("");
    $("#search-clear-btn").addClass("hidden");
    $("#search-icon-btn").removeClass("hidden");
    $("#recent-searches").show();
    $("#popular-searches").show();
    $("#search-results").hide();
    $("#searching-message").remove();
  });

  // Toggle search icons based on input value
  $("#search-main-input").on("input", function () {
    const inputValue = $(this).val();
    
    if (inputValue.length > 0) {
      // Show clear button, hide search icon
      $("#search-clear-btn").removeClass("hidden");
      $("#search-icon-btn").addClass("hidden");
      
      // Hide recent and popular searches and hr between them
      $("#recent-searches").hide();
      $("#recent-searches").next("hr").hide();
      $("#popular-searches").hide();
      
      // Show searching message or results based on character count
      if (inputValue.length < 2) {
        // Show searching message
        $("#search-results").hide();
        if ($("#searching-message").length === 0) {
          $("#search-overlay .flex-1.overflow-y-auto").append(
            '<div id="searching-message" class="text-center py-8 text-gray-500">در حال جستجو...</div>'
          );
        }
      } else {
        // Show results, hide searching message
        $("#searching-message").remove();
        $("#search-results").show();
      }
    } else {
      // Show search icon, hide clear button
      $("#search-clear-btn").addClass("hidden");
      $("#search-icon-btn").removeClass("hidden");
      
      // Show recent and popular searches and hr between them
      $("#recent-searches").show();
      $("#recent-searches").next("hr").show();
      $("#popular-searches").show();
      
      // Hide results and searching message
      $("#search-results").hide();
      $("#searching-message").remove();
    }
  });

  // Clear input when clear button is clicked
  $("#search-clear-btn").on("click", function () {
    $("#search-main-input").val("").focus();
    $(this).addClass("hidden");
    $("#search-icon-btn").removeClass("hidden");
    
    // Show recent and popular searches and hr again
    $("#recent-searches").show();
    $("#recent-searches").next("hr").show();
    $("#popular-searches").show();
    
    // Hide results and searching message
    $("#search-results").hide();
    $("#searching-message").remove();
  });

  // Clear recent searches with animation
  $("#clear-recent-searches").on("click", function () {
    const recentSearches = $("#recent-searches");
    const nextHr = recentSearches.next("hr");

    // Animate fade to right and remove
    recentSearches.css({
      transition: "all 0.4s ease-out",
      transform: "translateX(100%)",
      opacity: "0",
    });

    // Remove elements after animation completes
    setTimeout(function () {
      recentSearches.remove();
      if (nextHr.length) {
        nextHr.remove();
      }
    }, 400);
  });

  // Close search overlay when clicking outside
  $(document).on("click", function (e) {
    if (!$(e.target).closest("#search-overlay, #search-btn, #search-btn-desktop, #search-container-desktop").length) {
      if (!$("#search-overlay").hasClass("hidden")) {
        // Reset mobile button
        $("#search-btn").removeClass("active");
        $("#search-btn .icon-default").removeClass("hidden");
        $("#search-btn .icon-active").addClass("hidden");
        
        // Reset desktop button
        $("#search-btn-desktop").removeClass("active");
        $("#search-btn-desktop .icon-default-desktop").removeClass("hidden");
        $("#search-btn-desktop .icon-active-desktop").addClass("hidden");
        
        $("#search-overlay").addClass("hidden");
        
        // Reset search input and states
        $("#search-main-input").val("");
        $("#search-clear-btn").addClass("hidden");
        $("#search-icon-btn").removeClass("hidden");
        $("#recent-searches").show();
        $("#recent-searches").next("hr").show();
        $("#popular-searches").show();
        $("#search-results").hide();
        $("#searching-message").remove();
      }
    }
  });

  // Prevent closing when clicking inside overlay
  $("#search-overlay").on("click", function (e) {
    e.stopPropagation();
  });

  // Initialize mobile menu as hidden
  $("#mobile-menu").addClass("hidden");

  // Mobile nav toggle
  $("#open-mobile-nav2").on("click", function () {
    const mobileMenu = $("#mobile-menu");
    const generalOverlay = $("#general-overlay");
    const isMenuOpen = !mobileMenu.hasClass("hidden");

    if (isMenuOpen) {
      // Close menu
      closeMobileMenu();
    } else {
      // Open menu
      // Toggle icons
      $(this).find(".burger-icon").toggleClass("hidden");
      $(this).find(".close-icon").toggleClass("hidden");

      // Toggle overlay
      generalOverlay.removeClass("hidden");

      // Show mobile menu with bounce animation
      mobileMenu.removeClass("hidden");
      // Force reflow to ensure the hidden class is removed
      mobileMenu[0].offsetHeight;
      mobileMenu.removeClass("translate-x-full").addClass("mobile-menu-slide-in");
    }
  });

  // Close mobile menu function
  function closeMobileMenu() {
    const mobileMenu = $("#mobile-menu");
    const generalOverlay = $("#general-overlay");
    
    // Add slide out animation
    mobileMenu.removeClass("mobile-menu-slide-in").addClass("mobile-menu-slide-out");
    
    // Hide menu after animation completes
    setTimeout(() => {
      mobileMenu.addClass("hidden translate-x-full").removeClass("mobile-menu-slide-out");
      generalOverlay.addClass("hidden");
      
      // Reset mobile nav icons
      $("#open-mobile-nav2 .burger-icon").removeClass("hidden");
      $("#open-mobile-nav2 .close-icon").addClass("hidden");
      
      // Reset to main menu
      resetToMainMenu();
    }, 400);
  }

  // Reset to main menu function with animation
  function resetToMainMenu() {
    const mainMenu = $("#main-menu");
    const activeSubmenu = $("[id$='-submenu']:not(.hidden)").first();
    
    if (activeSubmenu.length > 0) {
      // Show main menu first (positioned absolutely, so no layout shift)
      mainMenu.removeClass("hidden").addClass("submenu-slide-in-left");
      
      // Add slide out animation to active submenu
      activeSubmenu.addClass("submenu-slide-out-right");
      
      // After animation completes, hide submenus and clean up classes
      setTimeout(() => {
        activeSubmenu.addClass("hidden").removeClass("submenu-slide-out-right");
        $("#escape-room-submenu").addClass("hidden");
        $("#cooperation-submenu").addClass("hidden");
        // Hide all genre submenus
        $("[id$='-submenu']").addClass("hidden");
        
        // Clean up main menu animation class
        mainMenu.removeClass("submenu-slide-in-left");
      }, 300);
    } else {
      // No active submenu, just show main menu
      mainMenu.removeClass("hidden");
      $("#escape-room-submenu").addClass("hidden");
      $("#cooperation-submenu").addClass("hidden");
      $("[id$='-submenu']").addClass("hidden");
    }
  }

  // Show submenu function with animation
  function showSubmenu(submenuId) {
    const mainMenu = $("#main-menu");
    const submenu = $(`#${submenuId}`);
    
    // Show submenu first (positioned absolutely, so no layout shift)
    submenu.removeClass("hidden").addClass("submenu-slide-in-right");
    
    // Add slide out animation to main menu
    mainMenu.addClass("submenu-slide-out-left");
    
    // After animation completes, hide main menu and clean up classes
    setTimeout(() => {
      mainMenu.addClass("hidden").removeClass("submenu-slide-out-left");
      submenu.removeClass("submenu-slide-in-right");
    }, 300);
  }

  // Show escape room genre submenu function with animation
  function showEscapeRoomGenreSubmenu(genreId) {
    const escapeRoomSubmenu = $("#escape-room-submenu");
    const genreSubmenu = $(`#${genreId}-submenu`);
    
    // Show genre submenu first (positioned absolutely, so no layout shift)
    genreSubmenu.removeClass("hidden").addClass("submenu-slide-in-right");
    
    // Add slide out animation to escape room submenu
    escapeRoomSubmenu.addClass("submenu-slide-out-left");
    
    // After animation completes, hide escape room submenu and clean up classes
    setTimeout(() => {
      escapeRoomSubmenu.addClass("hidden").removeClass("submenu-slide-out-left");
      genreSubmenu.removeClass("submenu-slide-in-right");
    }, 300);
  }

  // Back to escape room submenu function with animation
  function backToEscapeRoom() {
    const escapeRoomSubmenu = $("#escape-room-submenu");
    const activeGenreSubmenu = $("[id$='-submenu']:not(.hidden)").not("#escape-room-submenu, #cooperation-submenu").first();
    
    if (activeGenreSubmenu.length > 0) {
      // Show escape room submenu first (positioned absolutely, so no layout shift)
      escapeRoomSubmenu.removeClass("hidden").addClass("submenu-slide-in-left");
      
      // Add slide out animation to active genre submenu
      activeGenreSubmenu.addClass("submenu-slide-out-right");
      
      // After animation completes, hide genre submenus and clean up classes
      setTimeout(() => {
        activeGenreSubmenu.addClass("hidden").removeClass("submenu-slide-out-right");
        $("[id$='-submenu']").not("#escape-room-submenu, #cooperation-submenu").addClass("hidden");
        
        // Clean up escape room submenu animation class
        escapeRoomSubmenu.removeClass("submenu-slide-in-left");
      }, 300);
    } else {
      // No active genre submenu, just show escape room submenu
      escapeRoomSubmenu.removeClass("hidden");
      $("[id$='-submenu']").not("#escape-room-submenu, #cooperation-submenu").addClass("hidden");
    }
  }

  // Close mobile menu with close button
  $("#close-mobile-menu").on("click", function () {
    closeMobileMenu();
  });

  // Close overlay when clicking on it
  $("#general-overlay").on("click", function () {
    closeMobileMenu();
  });

  // Prevent mobile menu from closing when clicking inside it
  $("#mobile-menu").on("click", function (e) {
    e.stopPropagation();
  });

  // Menu item click handler for submenus
  $(".menu-item").on("click", function (e) {
    e.preventDefault();
    const submenuId = $(this).data("submenu");
    if (submenuId) {
      showSubmenu(`${submenuId}-submenu`);
    }
  });

  // Back to main menu button handlers
  $("#back-to-main-menu, .back-to-main-menu").on("click", function (e) {
    e.preventDefault();
    resetToMainMenu();
  });

  // Escape room genre item click handler
  $(".escape-genre-item").on("click", function (e) {
    e.preventDefault();
    const genre = $(this).data("genre");
    if (genre) {
      showEscapeRoomGenreSubmenu(genre);
    }
  });

  // Back to escape room button handlers
  $(".back-to-escape-room").on("click", function (e) {
    e.preventDefault();
    backToEscapeRoom();
  });

  // City selector button (can be extended later)
  $(".open-modal-btn").on("click", function () {
    $("#general-overlay").removeClass("hidden");
    // TODO: Show city selector modal
  });
});

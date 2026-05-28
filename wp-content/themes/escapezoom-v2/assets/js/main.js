// تنظیم jQuery AJAX برای جلوگیری از کش شدن
if (typeof jQuery !== 'undefined') {
    jQuery.ajaxSetup({
        cache: false,  // هیچ AJAX request نباید کش بشه
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {

    let citiesMenu = new Swiper(".cities", {
        slidesPerView: 3,
        slidesPerGroup: 3,
        grid: {
            rows: 2,
            fill: "row"
        },
        spaceBetween: 30,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    })

    const carouselsNormal = document.querySelectorAll('.embla_normal');
    const carouselsFade = document.querySelectorAll('.embla_fade');
    const carouselsCommentMainLg = document.querySelector('.embla_comments_main_lg');
    const carouselsCommentMainMd = document.querySelector('.embla_comments_main_md');
    const carouselsCommentThumbs = document.querySelector('.embla_comments_thumbs_md');
    if (carouselsNormal.length > 0) {
    carouselsNormal.forEach((carousel) => {
      const viewportNode = carousel.querySelector('.embla__viewport');
      const prevBtn = carousel.querySelector('.embla__button--prev');
      const nextBtn = carousel.querySelector('.embla__button--next');
      const options = {
        axis: 'x',
        dragFree: true,
        direction: 'rtl',
        align: 'center',
      };
      let embla = EmblaCarousel(viewportNode,options);
      const updateButtons = () => {
        const isWide = window.innerWidth > 720;
        if (prevBtn) prevBtn.style.display = isWide && embla.canScrollPrev() ? 'block' : 'none';
        if (nextBtn) nextBtn.style.display = isWide && embla.canScrollNext() ? 'block' : 'none';
      };
      embla.on('select', updateButtons);
      embla.on('reInit', updateButtons);
      updateButtons();
      if (prevBtn) prevBtn.addEventListener('click', () => embla.scrollPrev());
      if (nextBtn) nextBtn.addEventListener('click', () => embla.scrollNext());
      window.addEventListener('resize', () => {
        updateButtons();
      });
    });
    }
    if (carouselsCommentMainLg) {
        const viewportNode = carouselsCommentMainLg.querySelector('.embla__viewport');
          const options = {
            loop: true,
            direction: 'rtl',
            dragFree: true,
            containScroll: 'trimSnaps',
            align: 'start',
            duration: 500,
            slidesToScroll: 1
          };
          
          const autoscrollOptions = {
            direction: 'forward', 
            speed: 1,
            startDelay: 1000,
            active: true,
            playOnInit: true,
            stopOnFocusIn: true,
            stopOnInteraction: false,
            stopOnMouseEnter: true,
            rootNode: null
          };

         const embla = EmblaCarousel(viewportNode, options, [
                    EmblaCarouselAutoScroll(autoscrollOptions)
        ]);

        const autoscroll = embla.plugins().autoScroll;
        let isUserInteracting = false;
        let interactionTimeout;

          embla.on('select', () => {
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
                embla.on('pointerDown', () => {
                    isUserInteracting = true;
                    if (autoscroll) {
                        autoscroll.stop();
                    }
                });

                embla.on('pointerUp', () => {
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
                embla.on('destroy', () => {
                    clearTimeout(interactionTimeout);
                });
    }
    if (carouselsCommentMainMd) {
        const mainNode = carouselsCommentMainMd.querySelector('.embla__viewport')
        const thumbsNode = carouselsCommentThumbs.querySelector('.embla__viewport')

        const mainOptions = {
            axis: 'y',
            loop: true,
            dragFree: false,
            containScroll: 'trimSnaps'
        }

        const thumbsOptions = {
            axis: 'y',
            containScroll: 'trimSnaps',
            dragFree: false
        }

        const mainCarousel = EmblaCarousel(mainNode, mainOptions, [
            EmblaCarouselAutoplay({ delay: 3000, stopOnInteraction: false })
        ])
        const thumbsCarousel = EmblaCarousel(thumbsNode, thumbsOptions)

        // Add click event listeners to thumbnails
        const thumbSlides = thumbsCarousel.slideNodes()
        thumbSlides.forEach((slide, index) => {
            slide.addEventListener('click', () => {
                mainCarousel.scrollTo(index)
            })
        })

        // Sync main carousel with thumbnails
        mainCarousel.on('select', () => {
            const selected = mainCarousel.selectedScrollSnap()
            thumbsCarousel.scrollTo(selected)
            updateThumbButtons()
        })

        // Sync thumbnails with main carousel
        thumbsCarousel.on('select', () => {
            const selected = thumbsCarousel.selectedScrollSnap()
            mainCarousel.scrollTo(selected)
        })

        // Update thumbnail buttons
        function updateThumbButtons() {
            const slides = thumbsCarousel.slideNodes()
            slides.forEach((slide, index) => {
                if (index === mainCarousel.selectedScrollSnap()) {
                    slide.classList.add('embla-thumbs__slide--selected')
                } else {
                    slide.classList.remove('embla-thumbs__slide--selected')
                }
            })
        }

        // Initialize thumbnails
        updateThumbButtons()
    }
    if (carouselsFade.length > 0) {
      carouselsFade.forEach((carousel) => {
        const viewportNode = carousel.querySelector('.embla__viewport');
        const prevBtn = carousel.querySelector('.embla__button--prev');
        const nextBtn = carousel.querySelector('.embla__button--next');
        const dotsNode = carousel.querySelector('.embla__dots');
        
        const options = {
            loop: true,
            duration: 25,
            direction: 'rtl',
            align: 'start',
        }
        
        const autoplayOptions = {
            delay: 4000,
            stopOnInteraction: false,
            stopOnMouseEnter: true,
        }

        const embla = EmblaCarousel(viewportNode, options, [
            EmblaCarouselFade(),
            EmblaCarouselAutoplay(autoplayOptions)
        ])

        function createDots() {
            if (!dotsNode) return
            dotsNode.innerHTML = ''
            const dots = embla.slideNodes().map((_, index) => {
                const dot = document.createElement('button')
                dot.classList.add('embla__dot')
                dot.dataset.index = index
                dot.setAttribute('type', 'button')
                dot.setAttribute('aria-label', `Go to slide ${index + 1}`)
                dot.addEventListener('click', () => {
                    embla.scrollTo(index)
                    const autoplay = embla.plugins().autoplay
                    if (autoplay) autoplay.reset()
                })
                dotsNode.appendChild(dot)
                return dot
            })
        }

        function updateDots() {
            if (!dotsNode) return
            const dots = dotsNode.querySelectorAll('.embla__dot')
            const selectedIndex = embla.selectedScrollSnap()
            dots.forEach((dot, index) => {
                dot.classList.toggle('is-selected', index === selectedIndex)
            })
        }

        function updateButtons() {
            if (!prevBtn || !nextBtn) return
            const isDesktop = window.innerWidth >= 768
            prevBtn.style.display = isDesktop ? 'flex' : 'none'
            nextBtn.style.display = isDesktop ? 'flex' : 'none'
        }

        embla.on('init', () => {
            createDots()
            updateDots()
            updateButtons()
        })

        embla.on('select', () => {
            updateDots()
        })
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                embla.scrollPrev()
                const autoplay = embla.plugins().autoplay
                if (autoplay) autoplay.reset()
            })
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                embla.scrollNext()
                const autoplay = embla.plugins().autoplay
                if (autoplay) autoplay.reset()
            })
        }
        
        window.addEventListener('resize', updateButtons)
      });
    }

  });
jQuery(document).ready(function ($) {
    

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

  // ========================================
  // سیستم مدیریت متمرکز برای Mobile Menu و Search
  // ========================================

  // تابع بستن سرچ
  function closeSearchOverlay() {
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
    if ($("#recent-searches").next("hr").length) {
      $("#recent-searches").next("hr").show();
    }
    $("#popular-searches-section").show();
    $("#main-search-results").hide();
  }

  // تابع باز کردن سرچ
  function openSearchOverlay() {
    // بستن منوی موبایل (اگر باز بود)
    if (!$("#mobile-menu").hasClass("hidden")) {
      closeMobileMenu();
    }

    // Toggle active class on both buttons
    $("#search-btn, #search-btn-desktop").addClass("active");
    
    // Toggle icons for mobile
    $("#search-btn .icon-default").addClass("hidden");
    $("#search-btn .icon-active").removeClass("hidden");
    
    // Toggle icons for desktop
    $("#search-btn-desktop .icon-default-desktop").addClass("hidden");
    $("#search-btn-desktop .icon-active-desktop").removeClass("hidden");
    
    // Show overlay
    $("#search-overlay").removeClass("hidden");
  }

  // Search button toggle functionality (Mobile & Desktop)
  $("#search-btn, #search-btn-desktop").on("click", function (e) {
    e.stopPropagation();
    
    // بررسی وضعیت فعلی سرچ
    const isSearchOpen = !$("#search-overlay").hasClass("hidden");
    
    if (isSearchOpen) {
      closeSearchOverlay();
    } else {
      openSearchOverlay();
    }
  });

  // Close search overlay with close button
  $("#search-close-btn").on("click", function () {
    closeSearchOverlay();
  });

  // Toggle search icons based on input value
  $("#search-main-input").on("input", function () {
    const inputValue = $(this).val();
    
    if (inputValue.length > 0) {
      // Show clear button, hide search icon
      $("#search-clear-btn").removeClass("hidden");
      $("#search-icon-btn").addClass("hidden");
      
      // Hide recent and popular searches and hr
      $("#recent-searches").hide();
      if ($("#recent-searches").next("hr").length) {
        $("#recent-searches").next("hr").hide();
      }
      $("#popular-searches-section").hide();
      
      // Show results container (loading or results will be shown by keyup handler)
      $("#main-search-results").show();
    } else {
      // Show search icon, hide clear button
      $("#search-clear-btn").addClass("hidden");
      $("#search-icon-btn").removeClass("hidden");
      
      // Show recent and popular searches and hr
      $("#recent-searches").show();
      if ($("#recent-searches").next("hr").length) {
        $("#recent-searches").next("hr").show();
      }
      $("#popular-searches-section").show();
      
      // Hide results
      $("#main-search-results").hide();
    }
  });

  // Clear input when clear button is clicked
  $("#search-clear-btn").on("click", function () {
    $("#search-main-input").val("").focus();
    $(this).addClass("hidden");
    $("#search-icon-btn").removeClass("hidden");
    
    // Show recent and popular searches and hr again
    $("#recent-searches").show();
    if ($("#recent-searches").next("hr").length) {
      $("#recent-searches").next("hr").show();
    }
    $("#popular-searches-section").show();
    
    // Hide results
    $("#main-search-results").hide();
  });


  // Close search overlay when clicking outside
  $(document).on("click", function (e) {
    if (!$(e.target).closest("#search-overlay, #search-btn, #search-btn-desktop, #search-container-desktop").length) {
      if (!$("#search-overlay").hasClass("hidden")) {
        closeSearchOverlay();
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
      
      // بستن سرچ (اگر باز بود)
      if (!$("#search-overlay").hasClass("hidden")) {
        closeSearchOverlay();
      }

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



    // --------menu-listGames-------------------------------

    const gameListToggle = document.getElementById('game-list-toggle');
    const slideMenu = document.querySelector('.slide-menu');
    const menuOverlay = document.getElementById('menu-overlay');

    if (gameListToggle && slideMenu && menuOverlay) {
        gameListToggle.addEventListener('click', () => {
            const isOpen = slideMenu.classList.contains('open');
            slideMenu.classList.toggle('open', !isOpen);
            menuOverlay.classList.toggle('open', !isOpen);
            gameListToggle.classList.toggle('active', !isOpen);
        });

        menuOverlay.addEventListener('click', () => {
            slideMenu.classList.remove('open');
            menuOverlay.classList.remove('open');
            gameListToggle.classList.remove('active');
        });

        // Prevent menu from closing when clicking inside it
        slideMenu.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }
    // -------------modal-automatic-jeoLocation----------------
    $(function () {
        // انتخاب شهر
        $(".option").on("click", function () {
            let city = $(this).text().trim();
            $(".selected-city").text(city); // جایگزینی متن روی دکمه
            $(".sans-options").addClass("hidden"); // بستن منو
        });

        // بستن اورلی با کلیک بیرون از باکس
        $("#city-overlay").on("click", function (e) {
            if ($(e.target).is("#city-overlay")) {
                $("#city-overlay").fadeOut();
            }
        });

        // دکمه بستن
        $("#close-overlay").on("click", function () {
            $("#city-overlay").fadeOut();
        });
    });
    let baseUrlQuery = '"/wp-admin/admin-ajax.php?action=v2_ajax_handler&callback=queryable_search"';
    let baseUrlWebService = '"/wp-admin/admin-ajax.php?action=v2_ajax_handler"'
    if (location.hostname === 'localhost') {
        baseUrlQuery = '"/wp-admin/admin-ajax.php?action=v2_ajax_handler&callback=queryable_search"';
        baseUrlWebService = '"/wp-admin/admin-ajax.php?action=v2_ajax_handler"'
    }
    $('body').on('click', '.mobile-hover', function () {
        let parent = $(this).parent()
        parent.next('a').css('display', 'flex');
    })
    /*search-ajax*/
    $('#lg-search').on('keyup', function () {
        let searchValue = $(this).val();
        let valCount = searchValue.length;
        if (valCount >= 1) {
            setTimeout(function () {
                $.ajax({
                    type: 'POST',
                    url: baseUrlQuery,
                    data: {
                        "source": "home_header_search",
                        "term": searchValue
                    },
                    dataType: "json",
                    beforeSend: function () {
                        $('#lg-search-form .relative #lg-search-alert').remove()
                        $('#lg-search-form .relative').append('<p id="lg-search-alert" class="text-3xs text-primary-500 absolute mt-1">در حال جستجو <span class="loading-dots"></span></p>');
                    },
                    success: function (data) {
                        $('#lg-search-form .relative #lg-search-alert').remove()
                        $('#lg-search-container #lg-search-result').remove()
                        let searchResult = `
                        <div id="lg-search-result" class="absolute z-10 mt-4 w-full rounded-lg border bg-white shadow-[0px_4px_4px_0px_rgba(0,0,0,.1)]">
                            <div class="relative">
                                <button id="lg-search-close" class="text-red-600 hover:text-primary-600 leading-[10px] transition absolute text-[25px] left-3 top-2">×</button>
                                <div id="lg-search-result-list" class="max-h-75 divide-y divide-[#E4EBF0] overflow-y-auto px-4 py-5">
                                </div>
                            </div>
                        </div>
                        `
                        $('#lg-search-container').append(searchResult)
                        if (data) {
                            setTimeout(function () {
                                /*if (data.length > 10){
                                    $('#lg-search-container #lg-search-result .relative').append('<button id="lg-search-submit" type="button" class="mx-auto block w-fit py-2 text-primary-500">مشاهده همه</button>')
                                }*/
                                data.forEach(element => {
                                    let url = location.origin + '/room/' + element.url
                                    /*let url = 'https://' + location.hostname + '/room/' + element.url*/
                                    let resultItem = `  
                                    <a href="${url}" class="flex items-center gap-x-2 py-2">
                                        <img src="${element.image}" alt="اسکیپ‌زوم - ${element.title}" class="h-10 w-7.5 rounded">
                                        <span>${element.title}</span>
                                    </a>
                                `;
                                    $('#lg-search-container #lg-search-result .relative #lg-search-result-list').append(resultItem);
                                });
                            }, 1);
                        } else {
                            $('#lg-search-container #lg-search-result .relative #lg-search-result-list').append('<p>سرگرمی یافت نشد</p>');
                        }
                    },
                });
            }, 1)
        } else {
            $('#lg-search-form .relative #lg-search-alert').remove()
            $('#lg-search-container #lg-search-result').remove()
        }
    });
    $('body').on('click', '#lg-search-close', function () {
        $('#lg-search-form .relative #lg-search-alert').remove()
        $('#lg-search-container #lg-search-result').remove()
        $('#lg-search').val('')
    });
    /*$('body').on('click', '#lg-search-submit', function() {
        $('#lg-search-form').submit();
    });*/
    $('body').on('submit', '#lg-search-form', function (e) {
        e.preventDefault()
    })
    /*$('#lg-search').on('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            $('#lg-search-form').submit();
        }
    });*/
    $('#sm-search').on('keyup', function () {
        let searchValue = $(this).val();
        let valCount = searchValue.length;
        if (valCount >= 1) {
            setTimeout(function () {
                $.ajax({
                    type: 'POST',
                    url: baseUrlQuery,
                    data: {
                        "source": "home_header_search",
                        "term": searchValue
                    },
                    dataType: "json",
                    beforeSend: function () {
                        $('#sm-search-form .relative #sm-search-alert').remove()
                        $('#sm-search-form .relative').append('<p id="sm-search-alert" class="text-3xs text-primary-500 absolute mt-1">در حال جستجو <span class="loading-dots"></span></p>');
                    },
                    success: function (data) {
                        $('#sm-search-form .relative #sm-search-alert').remove()
                        $('#sm-search-container #sm-search-result').remove()
                        let searchResult = `
                        <div id="sm-search-result" class="absolute z-10 mt-4 w-full rounded-lg border bg-white shadow-[0px_4px_4px_0px_rgba(0,0,0,.1)]">
                            <div class="relative">
                                <button id="sm-search-close" class="text-red-600 hover:text-primary-600 leading-[10px] transition absolute text-[25px] left-3 top-2">×</button>
                                <div id="sm-search-result-list" class="max-h-75 divide-y divide-[#E4EBF0] overflow-y-auto px-4 py-5">
                                </div>
                            </div>
                        </div>
                        `
                        $('#sm-search-container').append(searchResult)
                        if (data) {
                            setTimeout(function () {
                                /*if (data.length > 10){
                                    $('#sm-search-container #sm-search-result .relative').append('<button id="sm-search-submit" type="button" class="mx-auto block w-fit py-2 text-primary-500">مشاهده همه</button>')
                                }*/
                                data.forEach(element => {
                                    let url = location.origin + '/room/' + element.url
                                    let resultItem = `  
                                    <a href="${url}" class="flex items-center gap-x-2 py-2">
                                        <img src="${element.image}" alt="اسکیپ‌زوم - ${element.title}" class="h-10 w-7.5 rounded">
                                        <span>${element.title}</span>
                                    </a>
                                `;
                                    $('#sm-search-container #sm-search-result .relative #sm-search-result-list').append(resultItem);
                                });
                            }, 1);
                        } else {
                            $('#sm-search-container #sm-search-result .relative #sm-search-result-list').append('<p>سرگرمی یافت نشد</p>');
                        }
                    },
                });
            }, 1)
        } else {
            $('#sm-search-form .relative #sm-search-alert').remove()
            $('#sm-search-container #sm-search-result').remove()
        }
    });
    $('body').on('click', '#sm-search-close', function () {
        $('#sm-search-form .relative #sm-search-alert').remove()
        $('#sm-search-container #sm-search-result').remove()
        $('#sm-search').val('')
    });
    /*$('body').on('click', '#sm-search-submit', function() {
        $('#sm-search-form').submit();
    });*/
    $('body').on('submit', '#sm-search-form', function (e) {
        e.preventDefault()
    });
    /*$('#sm-search').on('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            $('#sm-search-form').submit();
        }
    });*/
    $('.scrollable').each(function () {
        const $scrollable = $(this);
        let isDown = false;
        let startX;
        let scrollLeft;

        $scrollable.on('mousedown', function (e) {
            isDown = true;
            $scrollable.addClass('active');
            startX = e.pageX - $scrollable.offset().left;
            scrollLeft = $scrollable.scrollLeft();
        });

        $scrollable.on('mouseleave mouseup', function () {
            isDown = false;
            $scrollable.removeClass('active');
        });

        $scrollable.on('mousemove', function (e) {
            if (!isDown) return; // اگر دکمه ماوس فشرده نشده، کاری انجام ندهید
            e.preventDefault();
            const x = e.pageX - $scrollable.offset().left;
            const walk = (x - startX) * 2; // سرعت scroll را تنظیم کنید
            $scrollable.scrollLeft(scrollLeft - walk);
        });
    });
    $('.dropdown-button').on('click', function () {
        $(this).next('.options').toggleClass('max-md:hidden'); // فقط دایو بعدی را هدف قرار می‌دهیم
    });

    $("#open-mobile-nav").on('click', function () {
        $(".mobile-menu-modal").toggleClass('hidden')
            .toggleClass('flex')
    })
        $('.accordion-title').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        // جلوگیری از focus که ممکن است باعث scroll شود
        if (this.blur) {
            this.blur();
        }
        
        // ذخیره موقعیت scroll فعلی (فقط در موبایل)
        const isMobile = window.innerWidth <= 768;
        let scrollPosition = null;
        let $body = null;
        let bodyOverflow = null;
        let bodyPosition = null;
        let bodyTop = null;
        let scrollLocked = false;
        
        if (isMobile) {
            scrollPosition = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
            $body = $('body');
            bodyOverflow = $body.css('overflow');
            bodyPosition = $body.css('position');
            bodyTop = $body.css('top');
            scrollLocked = true;
            
            // قفل کردن scroll با استفاده از position fixed
            $body.css({
                'position': 'fixed',
                'top': '-' + scrollPosition + 'px',
                'width': '100%',
                'overflow': 'hidden'
            });
            
            // جلوگیری از scroll با event listener
            const preventScroll = function(e) {
                if (scrollLocked) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            };
            
            // اضافه کردن listener برای جلوگیری از scroll
            $(window).on('scroll.accordion-lock', function(e) {
                if (scrollLocked) {
                    window.scrollTo(0, scrollPosition);
                    return false;
                }
            });
            
            $(document).on('touchmove.accordion-lock', preventScroll);
        }
        
        const $content = $(this).next();
        const $title = $(this);
        
        // جلوگیری از scrollIntoView
        const originalScrollIntoView = Element.prototype.scrollIntoView;
        if (isMobile) {
            Element.prototype.scrollIntoView = function() {
                // جلوگیری از scrollIntoView در موبایل
                return false;
            };
        }
        
        $content.slideToggle({
            duration: 300,
            complete: function() {
                // بازگرداندن scrollIntoView
                if (isMobile) {
                    Element.prototype.scrollIntoView = originalScrollIntoView;
                    scrollLocked = false;
                }
                
                // بازگرداندن استایل body و موقعیت scroll (فقط در موبایل)
                if (isMobile && scrollPosition !== null && $body) {
                    // حذف event listeners
                    $(window).off('scroll.accordion-lock');
                    $(document).off('touchmove.accordion-lock');
                    
                    // بازگرداندن استایل body
                    $body.css({
                        'position': bodyPosition || '',
                        'top': bodyTop || '',
                        'width': '',
                        'overflow': bodyOverflow || ''
                    });
                    
                    // بازگرداندن موقعیت scroll
                    requestAnimationFrame(function() {
                        window.scrollTo(0, scrollPosition);
                        // چند بار برای اطمینان
                        setTimeout(function() {
                            window.scrollTo(0, scrollPosition);
                        }, 10);
                        setTimeout(function() {
                            window.scrollTo(0, scrollPosition);
                        }, 50);
                        setTimeout(function() {
                            window.scrollTo(0, scrollPosition);
                        }, 100);
                    });
                }
            }
        });
        
        $title.find('svg:first-child').toggleClass('hidden');
        $title.find('svg:last-child').toggleClass('hidden');
        
        return false;
    })
    $("#open-mobile-nav").on('click', function () {
        $(this).find('svg:first-child').toggleClass('hidden')
        $(this).find('svg:last-child').toggleClass('hidden')

        $(".mobile-navigation").toggleClass('scale-0 scale-100')
    })
    $(".accordion-item-title").on('click', function () {
        $(this).next('.accordion-item-content').slideToggle(300)
        $(this).find('svg:nth-of-type(2)').toggleClass('-rotate-90')
    })
    $("[data-hover-target]").hover(function () {
        let target = $(this).data('hover-target')

        $(`.submenu`).addClass('hidden').removeClass('grid')
        $(`#${target}`).removeClass('hidden').addClass('grid')
    })
    // انتخاب گزینه
    $('.option').on('click', function () {
        const selectedCity = $(this).text();
        const cityParams = $(this).data('params');
        // به‌روزرسانی متن دکمه با شهر انتخاب شده
        $(this).closest('.dropdown-container').find('.dropdown-button span').text(selectedCity);
        // پنهان کردن گزینه‌ها
        $(this).closest('.options').addClass('max-md:hidden');
    });
    // پنهان کردن منوی کشویی اگر کلیکی خارج از آن صورت بگیرد
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dropdown-container').length) {
            $('.options').addClass('max-md:hidden');
        }
    });
    $("#open-mobile-nav").on('click', function () {
        $('#mobile-header').toggleClass("bg-[#EDF2F5] bg-white shadow-104")
        $(".mobile-menu-modal").toggleClass('hidden')
    })
    // slider-ajax
    $('body').on('click', '.filter-btn', function (e) {
        let parentDiv = $(this).parent();
        let inputName = $(this).attr('data-input')
        let filterButtons = parentDiv.find('.filter-btn');
        filterButtons
            .removeClass('bg-primary-500 md:border-primary-500 text-slate-100 bg-white text-white')
            .addClass('md:border-gray-50 bg-white text-slate-350');
        $(this).removeClass('md:bg-white text-slate-350 md:border-gray-50 bg-white')
            .addClass('bg-primary-500 md:border-primary-500 text-slate-100');
        filterButtons.not(this).prop('disabled', false);
        $(this).prop('disabled', true);
        let params = $(this).attr('data-params');
        let keyParams = params.split(':');
        if (keyParams[0] === 'city_id') {
            $(`#${inputName}-title`).text(($(this).text()).trim())
        }
        let actionType = null;
        const actionTypeMap = {
            'city_id': 'شهر',
            'tag': 'سبک بازی', 
            'sort_type': 'مرتب‌ سازی'
        };
        actionType = actionTypeMap[keyParams[0]] || null;
        let currentPage = window.location.href;
        let input = $(`#${inputName}`);
        let inputSource = input.attr('data-source');
        let currentParams = JSON.parse(input.attr('data-params'));
        $.each(currentParams, function (key, value) {
            if (key === keyParams[0]) {
                currentParams[key] = JSON.parse(keyParams[1]);
            }
        });
        let resultString = JSON.stringify(currentParams);
        input.attr('data-params', resultString);
        $.ajax({
            type: 'POST',
            url: baseUrlWebService,
            data: {
                "async": false,
                "type": "sort_products_get",
                "data": {
                    "source": inputSource,
                    "params": currentParams
                }
            },
            dataType: "json",
            beforeSend: function () {
                $(`.${inputName}-btn`).hide();
                $(`#${inputName}-slider`).empty().append('<div style="height:350px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;">لطفا منتظر باشید...</div>')
            },
            success: function (data) {
                if (data.products) {
                    setTimeout(function () {
                        $(`#${inputName}-slider`).empty().append(data.products);
                        if (window.innerWidth > 720){
                        $(`.${inputName}-btn`).show();
                        }
                    }, 1);
                } else {
                    $(`#${inputName}-slider`).empty().append('<div style="height:350px;width:100%;text-align: center; display: flex;align-items: center;justify-content: center;">سرگرمی یافت نشد</div>')
                }
            },
        });
    });
    
    
    // بعد از لود صفحه یک بار با همان پارامترهای فعلی هر اسلایدر فیلتردار، درخواست مجدد بزن تا داده با «داغ‌ترین‌ها» و بقیه فیلترها درست نمایش داده شود (رفع ناهماهنگی درخواست اولیه PHP با فرانت)
    // function refetchFilterSlidersOnce() {
    //     $('input[id][data-source][data-params]').each(function() {
    //         var inputName = $(this).attr('id');
    //         var $slider = $('#' + inputName + '-slider');
    //         if (!$slider.length) return;
    //         var inputSource = $(this).attr('data-source');
    //         var paramsStr = $(this).attr('data-params');
    //         if (!inputSource || !paramsStr) return;
    //         var currentParams;
    //         try {
    //             currentParams = JSON.parse(paramsStr);
    //         } catch (e) { return; }
    //         (function (name, source, params) {
    //             $.ajax({
    //                 type: 'POST',
    //                 url: baseUrlWebService,
    //                 data: {
    //                     "async": false,
    //                     "type": "sort_products_get",
    //                     "data": {
    //                         "source": source,
    //                         "params": params
    //                     }
    //                 },
    //                 dataType: "json",
    //                 success: function (data) {
    //                     if (data && data.products) {
    //                         $('#' + name + '-slider').empty().append(data.products);
    //                         if (window.innerWidth > 720) {
    //                             $('.' + name + '-btn').show();
    //                         }
    //                     }
    //                 }
    //             });
    //         })(inputName, inputSource, currentParams);
    //     });
    // }
    // setTimeout(refetchFilterSlidersOnce, 180);

    
    $('.expand-menu').on('click', function () {
        $(this).next('.submenu').slideToggle()
    })
    $(".footer-read-more-about").on('click', function () {
        $(this).prev().find('span').removeClass('hidden')
        $(this).remove()
    })
    // Add performance optimizations
    $('.swiper-slide').css({
        'will-change': 'transform',
        'transform': 'translateZ(0)',
        'backface-visibility': 'hidden'
    });

    $('input.option-sans-input:checked').each(function () {
        const optionParam = $(this).parent().text().trim(); // متن گزینه انتخاب شده
        $(this).closest('.sans-dropdown-container').find('.sans-dropdown-button span').text(optionParam);
    });
    $('input[name="product_type"]').on('click',function (){
        let value = $(this).val()
        let baseUrl = 'https://' + location.hostname + '/wp-content/themes/app/functions/helper/cities_type.php';
        if (location.hostname === 'localhost') {
            baseUrl = 'http://' + location.hostname + '/escapezoom_wp/wp-content/themes/app/functions/helper/cities_type.php';
        }
        $.ajax({
            type: 'POST',
            url: baseUrl,
            data: {
                "product_type": value,
                "callback": "get_cities_by_product_type"
            },
            dataType: "json",
            beforeSend: function () {
                $('#cities-box-list').empty()
                $('#cities-box-title').empty().append('<span class="absolute text-nowrap line-clamp-1 text-xs p-0" style="top: -8px">لطفا منتظر باشید <span class="loading-dots"></span></span>')
            },
            success: function (data) {
                $('#cities-box-title').empty().text('شهر')
                if (data){
                    $.each(data.data, function(index, data) {
                        let city = `
                        <label class="option-sans block w-full hover:text-primary-500 hover:bg-gray-100 transition cursor-pointer px-4 py-2">
                                                    <input type="radio" name="city_id" value="${data['city_id']}" class="hidden option-sans-input"/>${data['city_name']}</label>
                        `;
                        $('#cities-box-list').append(city)
                    })
                } else {
                    $('#cities-box-list').text('شهری یافت نشد')
                }
            },
        });
    })
    $('.sans-dropdown-button').on('click', function () {
        $('.sans-options').addClass('hidden');
        $(this).next('.sans-options').toggleClass('hidden');
    });

    // انتخاب گزینه
    $("body").on('click', '.option-sans', function () {
        const selectOption = $(this).text().trim();
        const optionParam = $(this).find('input').val();

        // به‌روزرسانی متن دکمه با شهر انتخاب شده
        $(this).closest('.sans-dropdown-container').find('.sans-dropdown-button span').text(selectOption);

        // پنهان کردن گزینه‌ها
        $(this).closest('.sans-options').addClass('hidden');
    });

    // پنهان کردن منوی کشویی اگر کلیکی خارج از آن صورت بگیرد
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.sans-dropdown-container').length) {
            $('.sans-options').addClass('hidden');
        }
    });
    // جستجو در شهرها
    $('.city-search').on('input', function () {
        const searchValue = $(this).val().toLowerCase();
        $(this).closest('.sans-dropdown-container').find('.option-sans').each(function() {
            const cityName = $(this).text().toLowerCase();
            $(this).toggle(cityName.includes(searchValue));
        });
    });
    $(".share").on('click', async function () {
        let title = $(this).data('title'),
            text = $(this).data('content'),
            url = $(this).data('url')
        try {
            await navigator.share({
                title,
                text,
                url
            })
        } catch (error) {

        }

    })

    // a tag
    $("body").on('click', 'a', function () {
        let href = $(this).attr('href')
        if (href.startsWith("#")) {
            if ($(href).length > 0){
                $("html, body").animate({scrollTop: $(href).offset().top - 150}, 1000);
            }
        }
    })

    $(".help").each((index, item) => {
        let content = $(item).data('help')
        tippy(item, {
            content: content,
            animation: 'perspective-extreme',
        });
    })
    
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
;
        

        if (answerContent.css("height") !== "0px") {
            answerContent.css("height", "0px");
            $(this).find("button").css("transform", "rotate(0deg)");
        } else {
            $(".answer-content").css("height", "0px");
            $(".question-btn button").css("transform", "rotate(0deg)");

            answerContent.css(
                "height",
                answerContent.prop("scrollHeight") + "px"
            );
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

          answerContent.css(
              "height",
              answerContent.prop("scrollHeight") + "px"
          );
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
            spaceBetween: "-80px"
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
            slidesPerView:1.8, 
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
});

// Calendar conversion functions
window.CalendarConverter = {
    /**
     * Convert Gregorian date to Persian (Jalali) date
     * @param {number} gy - Gregorian year
     * @param {number} gm - Gregorian month (1-12)
     * @param {number} gd - Gregorian day
     * @returns {Object} Persian date object with jy, jm, jd properties
     */
    toJalali: function(gy, gm, gd) {
        var g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
        var jy = (gy <= 1600) ? 0 : 979;
        gy -= (gy <= 1600) ? 621 : 1600;
        var gy2 = (gm > 2) ? (gy + 1) : gy;
        var days = (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100) + Math.floor((gy2 + 399) / 400) - 80 + gd + g_d_m[gm - 1];
        jy += 33 * Math.floor(days / 12053);
        days %= 12053;
        jy += 4 * Math.floor(days / 1461);
        days %= 1461;
        if (days > 365) {
            jy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }
        var jm, jd;
        if (days < 186) {
            jm = 1 + Math.floor(days / 31);
            jd = 1 + (days % 31);
        } else {
            jm = 7 + Math.floor((days - 186) / 30);
            jd = 1 + ((days - 186) % 30);
        }
        return {jy: jy, jm: jm, jd: jd};
    },

    /**
     * Convert Persian (Jalali) date to Gregorian date
     * @param {number} jy - Persian year
     * @param {number} jm - Persian month (1-12)
     * @param {number} jd - Persian day
     * @returns {Object} Gregorian date object with gy, gm, gd properties
     */
    toGregorian: function(jy, jm, jd) {
        jy += 1595;
        var days = -355668 + (365 * jy) + Math.floor(jy / 33) * 8 + Math.floor((((jy % 33) + 3) / 4)) + jd + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
        var gy = 400 * Math.floor(days / 146097);
        days %= 146097;
        if (days > 36524) {
            gy += 100 * Math.floor(--days / 36524);
            days %= 36524;
            if (days >= 365) days++;
        }
        gy += 4 * Math.floor(days / 1461);
        days %= 1461;
        if (days > 365) {
            gy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }
        var gd = days + 1;
        var sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        for (var gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
        return {gy: gy, gm: gm, gd: gd};
    },

    /**
     * Format date with time from timestamp
     * @param {number} timestamp - Unix timestamp
     * @returns {Object} Formatted date object with gregorian and jalali strings
     */
    formatDateTime: function(timestamp) {
        if (!timestamp) return { gregorian: '', jalali: '' };
        
        const dateObj = new Date(timestamp * 1000);
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        const hours = String(dateObj.getHours()).padStart(2, '0');
        const minutes = String(dateObj.getMinutes()).padStart(2, '0');
        
        // Gregorian date
        const gregorian = `${year}.${month}.${day} ${hours}:${minutes}`;
        
        // Convert to Jalali
        const jalali = this.toJalali(year, parseInt(month), parseInt(day));
        const jalaliDate = `${jalali.jy}.${String(jalali.jm).padStart(2, '0')}.${String(jalali.jd).padStart(2, '0')} ${hours}:${minutes}`;
        
        return {
            gregorian: gregorian,
            jalali: jalaliDate,
            combined: `${gregorian} / ${jalaliDate}`
        };
    }
};

// Separate functions for easier use
/**
 * Convert Gregorian date to Persian (Jalali) date
 * @param {number} gy - Gregorian year
 * @param {number} gm - Gregorian month (1-12)
 * @param {number} gd - Gregorian day
 * @returns {Object} Persian date object with jy, jm, jd properties
 */
window.gregorianToJalali = function(gy, gm, gd) {
    return window.CalendarConverter.toJalali(gy, gm, gd);
};

/**
 * Convert Persian (Jalali) date to Gregorian date
 * @param {number} jy - Persian year
 * @param {number} jm - Persian month (1-12)
 * @param {number} jd - Persian day
 * @returns {Object} Gregorian date object with gy, gm, gd properties
 */
window.jalaliToGregorian = function(jy, jm, jd) {
    return window.CalendarConverter.toGregorian(jy, jm, jd);
};

/**
 * Format timestamp to Persian (Jalali) date with time
 * @param {number} timestamp - Unix timestamp
 * @returns {string} Formatted Persian date string
 */
window.formatToJalaliDateTime = function(timestamp) {
    if (!timestamp) return '';
    
    const dateObj = new Date(timestamp * 1000);
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    const hours = String(dateObj.getHours()).padStart(2, '0');
    const minutes = String(dateObj.getMinutes()).padStart(2, '0');
    
    // Convert to Jalali
    const jalali = window.CalendarConverter.toJalali(year, parseInt(month), parseInt(day));
    return `${jalali.jy}.${String(jalali.jm).padStart(2, '0')}.${String(jalali.jd).padStart(2, '0')}<span style="display:inline-block;width:10px"></span>${hours}:${minutes}`;
};

/**
 * Format phone number for tel: links
 * @param {string|number} phone - Phone number
 * @returns {string} Formatted phone number with +98 prefix
 */
window.formatPhoneForTel = function(phone) {
    if (!phone) return '';
    
    // Convert to string and trim
    let phoneStr = phone.toString().trim();
    
    // Remove any non-digit characters
    phoneStr = phoneStr.replace(/\D/g, '');
    
    // Remove leading zero if present
    if (phoneStr.startsWith('0')) {
        phoneStr = phoneStr.substring(1);
    }
    
    // Add +98 prefix
    return `+98${phoneStr}`;
};

// Custom Radio Checkbox Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle radio checkbox styling
    const radioInputs = document.querySelectorAll('input[type="radio"][name="reason_id"]');
    
    radioInputs.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove checked state from all radio checkboxes
            radioInputs.forEach(r => {
                const checkbox = r.nextElementSibling;
                if (checkbox && checkbox.classList.contains('radio-checkbox')) {
                    checkbox.classList.remove('bg-[#5091FB]', 'border-[#5091FB]');
                    const svg = checkbox.querySelector('svg');
                    if (svg) {
                        svg.style.opacity = '0';
                    }
                }
            });
            
            // Add checked state to selected radio checkbox
            if (this.checked) {
                const checkbox = this.nextElementSibling;
                if (checkbox && checkbox.classList.contains('radio-checkbox')) {
                    checkbox.classList.add('bg-[#5091FB]', 'border-[#5091FB]');
                    const svg = checkbox.querySelector('svg');
                    if (svg) {
                        svg.style.opacity = '1';
                    }
                }
            }
        });
        
        // Handle click on label to trigger radio selection
        const label = radio.closest('label');
        if (label) {
            label.addEventListener('click', function(e) {
                // Don't trigger if clicking directly on the radio input
                if (e.target === radio) return;
                
                // Trigger the radio input
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            });
        }
    });
});

// ================================
// PWA - Progressive Web App Setup (ثبت تنها در همین بلوک)
// ================================

function ezPwaBasePath() {
    var man = document.querySelector('link[rel="manifest"]');
    if (man && man.href) {
        try {
            var u = new URL(man.href);
            var path = u.pathname.replace(/\/?manifest\.json$/i, '') || '/';
            if (!path.endsWith('/')) {
                path += '/';
            }
            return path;
        } catch (e) {}
    }
    if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
        return '/escapezoom_wp/';
    }
    return '/';
}

if ('serviceWorker' in navigator) {
    var ezPwaReloading = false;
    var ezPwaRegistration = null;

    function ezPwaCheckForUpdate() {
        if (ezPwaRegistration) {
            ezPwaRegistration.update();
        }
    }

    function ezPwaActivateWaitingWorker(worker) {
        if (worker && worker.state === 'installed' && navigator.serviceWorker.controller) {
            worker.postMessage({ type: 'SKIP_WAITING' });
        }
    }

    window.addEventListener('load', () => {
        var origin = window.location.origin;
        var basePath = ezPwaBasePath();
        var swUrl = new URL('sw.js', origin + basePath).href;
        var swScope = new URL('./', origin + basePath).href;

        navigator.serviceWorker
            .register(swUrl, { scope: swScope })
            .then((registration) => {
                ezPwaRegistration = registration;
                registration.update();

                if (navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_CACHE' });
                }

                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (!newWorker) {
                        return;
                    }
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed') {
                            ezPwaActivateWaitingWorker(newWorker);
                        }
                    });
                });

                setInterval(ezPwaCheckForUpdate, 30 * 60 * 1000);
            })
            .catch(() => {});
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            ezPwaCheckForUpdate();
        }
    });
    window.addEventListener('focus', ezPwaCheckForUpdate);

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (ezPwaReloading) {
            return;
        }
        ezPwaReloading = true;
        window.location.reload();
    });
}

// نمایش نوتیفیکیشن بروزرسانی
function showUpdateNotification(newWorker) {
    // چک کردن اینکه آیا نوتیفیکیشن قبلاً نمایش داده شده یا نه
    if (document.getElementById('pwa-update-notification')) {
        return;
    }
    
    const notification = document.createElement('div');
    notification.id = 'pwa-update-notification';
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #fd7013;
        color: white;
        padding: 15px 25px;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 999999;
        display: flex;
        align-items: center;
        gap: 15px;
        font-family: inherit;
        animation: slideUp 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <span>🎉 نسخه جدید موجود است!</span>
        <button onclick="window.location.reload()" style="
            background: white;
            color: #fd7013;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            font-family: inherit;
        ">بروزرسانی</button>
        <button onclick="this.parentElement.remove()" style="
            background: transparent;
            color: white;
            border: 1px solid white;
            padding: 8px 15px;
            border-radius: 25px;
            cursor: pointer;
            font-family: inherit;
        ">بعداً</button>
    `;
    
    // اضافه کردن استایل انیمیشن
    if (!document.getElementById('pwa-update-animation')) {
        const style = document.createElement('style');
        style.id = 'pwa-update-animation';
        style.textContent = `
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(100px);
                }
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // حذف خودکار بعد از 30 ثانیه
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideUp 0.3s ease-out reverse';
            setTimeout(() => notification.remove(), 300);
        }
    }, 30000);
}

// مدیریت نصب PWA
let deferredPrompt;
let installButton;

window.addEventListener('beforeinstallprompt', (e) => {
    
    // جلوگیری از نمایش خودکار prompt
    e.preventDefault();
    deferredPrompt = e;
    
    // نمایش دکمه نصب سفارشی
    showInstallButton();
});

// نمایش دکمه نصب
function showInstallButton() {
    // چک کردن اینکه آیا دکمه قبلاً وجود دارد
    if (document.getElementById('pwa-install-button')) {
        return;
    }
    
    // ایجاد دکمه نصب
    installButton = document.createElement('button');
    installButton.id = 'pwa-install-button';
    installButton.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="margin-left: 8px;">
            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
        </svg>
        <span>نصب اپلیکیشن</span>
    `;
    installButton.style.cssText = `
        position: fixed;
        bottom: 80px;
        right: 20px;
        background: linear-gradient(135deg, #fd7013 0%, #ff9a56 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 50px;
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
        font-weight: bold;
        box-shadow: 0 4px 20px rgba(253, 112, 19, 0.4);
        z-index: 999998;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        animation: bounceIn 0.5s ease-out;
    `;
    
    // افکت hover
    installButton.onmouseenter = () => {
        installButton.style.transform = 'translateY(-2px)';
        installButton.style.boxShadow = '0 6px 25px rgba(253, 112, 19, 0.5)';
    };
    installButton.onmouseleave = () => {
        installButton.style.transform = 'translateY(0)';
        installButton.style.boxShadow = '0 4px 20px rgba(253, 112, 19, 0.4)';
    };
    
    // اضافه کردن انیمیشن
    if (!document.getElementById('pwa-install-animation')) {
        const style = document.createElement('style');
        style.id = 'pwa-install-animation';
        style.textContent = `
            @keyframes bounceIn {
                0% {
                    opacity: 0;
                    transform: scale(0.3) translateY(100px);
                }
                50% {
                    opacity: 1;
                    transform: scale(1.05) translateY(0);
                }
                70% {
                    transform: scale(0.9);
                }
                100% {
                    transform: scale(1);
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // کلیک روی دکمه نصب
    installButton.addEventListener('click', async () => {
        if (!deferredPrompt) {
            return;
        }
        
        // نمایش prompt نصب
        deferredPrompt.prompt();
        
        // منتظر انتخاب کاربر
        const { outcome } = await deferredPrompt.userChoice;
        
        
        if (outcome === 'accepted') {
        } else {
        }
        
        // پاک کردن prompt و دکمه
        deferredPrompt = null;
        installButton.remove();
    });
    
    document.body.appendChild(installButton);
    
    // حذف خودکار دکمه بعد از 1 دقیقه
    setTimeout(() => {
        if (installButton && installButton.parentElement) {
            installButton.style.animation = 'bounceIn 0.3s ease-out reverse';
            setTimeout(() => installButton.remove(), 300);
        }
    }, 60000);
}

// تشخیص نصب موفق
window.addEventListener('appinstalled', (evt) => {
    
    // حذف دکمه نصب اگر وجود داشته باشد
    if (installButton && installButton.parentElement) {
        installButton.remove();
    }
    
    // نمایش پیام تشکر (اختیاری)
    showThankYouMessage();
});

// نمایش پیام تشکر بعد از نصب
function showThankYouMessage() {
    const message = document.createElement('div');
    message.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #4CAF50;
        color: white;
        padding: 15px 30px;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 999999;
        font-family: inherit;
        animation: slideDown 0.3s ease-out;
    `;
    message.textContent = '🎉 متشکریم! اپلیکیشن با موفقیت نصب شد';
    
    if (!document.getElementById('pwa-thank-animation')) {
        const style = document.createElement('style');
        style.id = 'pwa-thank-animation';
        style.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(-100px);
                }
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(message);
    
    // حذف خودکار بعد از 5 ثانیه
    setTimeout(() => {
        message.style.animation = 'slideDown 0.3s ease-out reverse';
        setTimeout(() => message.remove(), 300);
    }, 5000);
}

// مدیریت وضعیت آنلاین/آفلاین
window.addEventListener('online', () => {
    showConnectionStatus('online');
});

window.addEventListener('offline', () => {
    showConnectionStatus('offline');
});

// نمایش وضعیت اتصال
function showConnectionStatus(status) {
    // حذف پیام قبلی اگر وجود دارد
    const existingMsg = document.getElementById('pwa-connection-status');
    if (existingMsg) {
        existingMsg.remove();
    }
    
    const message = document.createElement('div');
    message.id = 'pwa-connection-status';
    message.style.cssText = `
        position: fixed;    
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${status === 'online' ? '#4CAF50' : '#f44336'};
        color: white;
        padding: 12px 25px;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 999999;
        font-family: inherit;
        font-size: 14px;
        animation: slideDown 0.3s ease-out;
    `;
    
    message.textContent = status === 'online' 
        ? '🌐 اتصال اینترنت برقرار شد' 
        : '📵 اتصال اینترنت قطع است';
    
    document.body.appendChild(message);
    
    // حذف خودکار
    setTimeout(() => {
        message.style.animation = 'slideDown 0.3s ease-out reverse';
        setTimeout(() => message.remove(), 300);
    }, status === 'online' ? 3000 : 5000);
}

// تشخیص حالت standalone (نصب شده)
function isRunningStandalone() {
    return (window.matchMedia('(display-mode: standalone)').matches) ||
           (window.navigator.standalone) ||
           document.referrer.includes('android-app://');
}

// لاگ وضعیت PWA
if (isRunningStandalone()) {
} else {
}


// ========================================
// Main Search با AJAX - نسخه بهبود یافته
// ========================================
jQuery(document).ready(function($) {
    let searchTimeout = null;
    let currentAjaxRequest = null; // برای abort کردن درخواست قبلی
    let searchBaseUrl = 'https://' + location.hostname + '/wp-content/themes/escapezoom-v2/template/func/main-search-ajax.php';
    if (location.hostname === 'dev.escapezoom.local') {
        searchBaseUrl = 'http://' + location.hostname + '/wp-content/themes/escapezoom-v2/template/func/main-search-ajax.php';
    }

    // ثبت کلیک روی محبوب‌ترین جستجوها
    $('#popular-searches a').on('click', function(e) {
        let searchTitle = $(this).text().trim();
        let searchUrl = $(this).attr('href');
        
        $.ajax({
            url: searchBaseUrl.replace('main-search-ajax.php', 'save-popular-search.php'),
            type: 'POST',
            data: {
                search_value: searchTitle,
                url: searchUrl
            }
        });
    });

    // نمایش جستجوهای اخیر و محبوب در فوکوس
    $('#search-main-input').on('focus', function() {
        let searchTerm = $(this).val().trim();
        if (searchTerm === '') {
            $('#recent-searches').show();
            $('#recent-searches').next('hr').show();
            $('#popular-searches-section').show();
            $('#main-search-results').hide();
        }
    });

    // حذف جستجوهای اخیر
    $("#clear-recent-searches").on("click", function () {
        const userId = $(this).data('user-id');
        const ajaxUrl = searchBaseUrl.replace('main-search-ajax.php', 'delete-user-searches.php');
        const recentSearches = $('#recent-searches');
        const nextHr = recentSearches.next('hr');
        
        recentSearches.css({
            'transition': 'all 0.4s ease-out',
            'transform': 'translateX(100%)',
            'opacity': '0'
        });
        
        if (nextHr.length) {
            nextHr.css({
                'transition': 'all 0.4s ease-out',
                'opacity': '0'
            });
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: { 
                action: 'clear_all',
                user_id: userId
            },
            success: function(resp) {
                try {
                    const result = typeof resp === 'string' ? JSON.parse(resp) : resp;
                    if (result.success) {
                        setTimeout(function() {
                            recentSearches.remove();
                            if (nextHr.length) nextHr.remove();
                            showToast('جستجوهای اخیر شما با موفقیت حذف شد', 'success');
                        }, 400);
                    } else {
                        showToast('خطا: ' + result.message, 'error');
                    }
                } catch (e) {
                    showToast('خطا در پردازش پاسخ', 'error');
                }
            },
            error: function() {
                showToast('خطا در حذف جستجوها', 'error');
            }
        });
    });

    function showToast(message, type = 'success') {
        $('#ez-toast').remove();
        
        const bgColor = type === 'success' ? '#4CAF50' : '#f44336';
        const icon = type === 'success' ? '✅' : '❌';
        
        const toast = $('<div id="ez-toast"></div>').css({
            'position': 'fixed',
            'top': '20px',
            'right': '-400px',
            'background': bgColor,
            'color': 'white',
            'padding': '15px 20px',
            'border-radius': '8px',
            'box-shadow': '0 4px 12px rgba(0,0,0,0.3)',
            'z-index': '99999',
            'font-family': 'Tahoma, Arial',
            'font-size': '14px',
            'font-weight': 'bold',
            'transition': 'all 0.4s ease-out',
            'display': 'flex',
            'align-items': 'center',
            'gap': '10px'
        }).html(icon + ' ' + message);
        
        $('body').append(toast);
        
        setTimeout(() => toast.css('right', '20px'), 10);
        
        setTimeout(() => {
            toast.css({ 'right': '-400px', 'opacity': '0' });
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }

    // ===================== جستجوی اصلی با input event + debounce + abort =====================
    $('#search-main-input').on('input', function() {
        let searchTerm = $(this).val().trim();

        // پاک کردن تایمر قبلی
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // لغو درخواست AJAX قبلی
        if (currentAjaxRequest) {
            currentAjaxRequest.abort();
        }

        if (searchTerm === '' || searchTerm.length < 2) {
            $('#recent-searches').show();
            $('#recent-searches').next('hr').show();
            $('#popular-searches-section').show();
            $('#main-search-results').hide().empty();
            return;
        }

        // مخفی کردن بخش‌های غیرمرتبط
        $('#recent-searches').hide();
        $('#recent-searches').next('hr').hide();
        $('#popular-searches-section').hide();
        $('#main-search-results').show();

        // نمایش loading
        $('#main-search-results').html(`
            <div class="flex items-center justify-center py-8">
                <span class="text-gray-500 font-medium">در حال جستجو</span>
                <span class="loading-dots ml-1"></span>
            </div>
        `);

        // اضافه کردن انیمیشن نقطه‌ها (فقط یکبار)
        if (!$('#loading-dots-style').length) {
            $('<style id="loading-dots-style">' +
              '@keyframes dotAnimation {' +
              '  0%, 20% { content: "."; }' +
              '  40% { content: ".."; }' +
              '  60%, 100% { content: "..."; }' +
              '}' +
              '.loading-dots::after {' +
              '  content: ".";' +
              '  animation: dotAnimation 1.5s infinite;' +
              '  display: inline-block;' +
              '  width: 20px;' +
              '  text-align: left;' +
              '}' +
              '</style>').appendTo('head');
        }

        // debounce 350ms
        searchTimeout = setTimeout(function() {
            currentAjaxRequest = $.ajax({
                url: searchBaseUrl,
                type: 'POST',
                data: { term: searchTerm },
                dataType: 'json',
                success: function(response) {
                    function bindResultClicks() {
                        $('.ez-search-result').off('click').on('click', function() {
                            var searchValue = $(this).data('search-value');
                            var url = $(this).data('url');
                            if (!searchValue || !url) return;
                            $.ajax({
                                url: searchBaseUrl.replace('main-search-ajax.php', 'save-popular-search.php'),
                                type: 'POST',
                                data: { search_value: searchValue, url: url }
                            });
                            $.ajax({
                                url: searchBaseUrl.replace('main-search-ajax.php', 'save-user-search.php'),
                                type: 'POST',
                                data: { name: searchValue, url: url }
                            });
                        });
                    }

                    if (response.status === 'success' && response.has_results && response.data && response.data.length) {
                        var htmlBuilder = '<ul class="space-y-6">';
                        response.data.forEach(function(item) {
                            if (item.ui === 'link') {
                                var linkHref = $('<div>').text(item.url).html();
                                htmlBuilder += '<li>' +
                                    '<a href="' + linkHref + '" class="ez-search-result flex items-center justify-between" data-search-type="' + (item.type || 'category') + '" data-search-value="' + $('<div>').text(item.title).html() + '" data-url="' + linkHref + '">' +
                                    '<span class="flex items-center gap-x-4">' +
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">' +
                                    '<circle cx="5.56627" cy="5.30357" r="3.92857" stroke="#90A1B9" stroke-width="2"></circle>' +
                                    '<circle cx="5.76158" cy="15.7143" r="3.92857" stroke="#90A1B9" stroke-width="2"></circle>' +
                                    '<circle cx="16.5663" cy="5.30357" r="3.92857" stroke="#90A1B9" stroke-width="2"></circle>' +
                                    '<circle cx="16.4999" cy="15.7143" r="3.92857" stroke="#90A1B9" stroke-width="2"></circle>' +
                                    '</svg>' +
                                    '<span class="font-bold text-[#62748E]">' + $('<div>').text(item.title).html() + '</span>' +
                                    '</span></a></li>';
                            } else if (item.ui === 'card') {
                                var productType = item.product_type ? item.product_type : 'اتاق فرار';
                                var locationHtml = '';
                                if (item.city) locationHtml += '<span>' + $('<div>').text(item.city).html() + '</span>';
                                if (item.city && item.hood) {
                                    locationHtml += '<span><svg xmlns="http://www.w3.org/2000/svg" width="3" height="4" viewBox="0 0 3 4" fill="none"><circle cx="1.5" cy="2" r="1.5" fill="#90A1B9"></circle></svg></span>';
                                }
                                if (item.hood) locationHtml += '<span>' + $('<div>').text(item.hood).html() + '</span>';
                                var imgSrc = item.image ? $('<div>').text(item.image).html() : '';
                                htmlBuilder += '<li><a href="' + $('<div>').text(item.url).html() + '" class="ez-search-result flex items-center justify-between" data-search-type="product" data-search-value="' + $('<div>').text(item.title).html() + '" data-url="' + $('<div>').text(item.url).html() + '">' +
                                    '<span class="flex items-center gap-x-4">' +
                                    (imgSrc ? '<img src="' + imgSrc + '" alt="" class="w-7 h-8.5 rounded object-cover">' : '') +
                                    '<span class="space-x-1 space-x-reverse">' +
                                    '<span class="font-bold text-sm text-[#62748E] inline-block">' + $('<div>').text(productType).html() + '</span>' +
                                    '<span class="text-[#09192D] font-bold inline-block">' + $('<div>').text(item.title).html() + '</span>' +
                                    '</span></span>';
                                if (locationHtml) {
                                    htmlBuilder += '<span class="flex items-center gap-x-2">' +
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">' +
                                    '<path d="M6.99967 1.16675C4.43301 1.16675 2.33301 3.26675 2.33301 5.83341C2.33301 8.98341 6.99967 13.4167 6.99967 13.4167C6.99967 13.4167 11.6663 8.98341 11.6663 5.83341C11.6663 3.26675 9.56634 1.16675 6.99967 1.16675ZM6.99967 7.58341C6.03134 7.58341 5.24967 6.80175 5.24967 5.83341C5.24967 4.86508 6.03134 4.08341 6.99967 4.08341C7.96801 4.08341 8.74967 4.86508 8.74967 5.83341C8.74967 6.80175 7.96801 7.58341 6.99967 7.58341Z" fill="#90A1B9"></path>' +
                                    '</svg>' +
                                    '<span class="flex items-center gap-x-2 text-[#62748E] text-4xs">' + locationHtml + '</span></span>';
                                }
                                htmlBuilder += '</a></li>';
                            }
                        });
                        htmlBuilder += '</ul>';
                        $('#main-search-results').html(htmlBuilder);
                        bindResultClicks();
                    } else if (response.status === 'success' && response.has_results === false && response.html) {
                        $('#main-search-results').html(response.html);
                    } else if (response.status === 'empty' && response.message) {
                        $('#main-search-results').html('<p class="text-center text-gray-500 py-4">' + $('<div>').text(response.message).html() + '</p>');
                    } else if (response.status === 'error' && response.message) {
                        $('#main-search-results').html('<p class="text-center text-red-500 py-4">' + $('<div>').text(response.message).html() + '</p>');
                    } else {
                        $('#main-search-results').html('<p class="text-center text-gray-500 py-4">نتیجه‌ای یافت نشد</p>');
                    }
                },
                error: function(xhr, status) {
                    if (status === 'abort') return; // درخواست لغو شده، مشکلی نیست
                    $('#main-search-results').html('<p class="text-center text-red-500 py-4">خطا در جستجو</p>');
                }
            });
        }, 350);
    });
});
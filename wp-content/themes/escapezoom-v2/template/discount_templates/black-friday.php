<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth" dir="rtl" style="margin: 0 !important;">

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo esc_html(get_bloginfo('name')); ?> - Black Friday</title>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?> style="margin: 0 !important; padding: 0 !important;">

    <!-- Pre-campaign Black Friday Section -->
    <div class="relative w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] overflow-hidden bg-gradient-to-br from-red-900 via-black to-red-950 min-h-[100vh] flex items-center justify-center">
        <!-- Particles Canvas -->
        <canvas id="precampaign-particles" class="absolute inset-0 w-full h-full"></canvas>

        <!-- Content -->
        <div class="relative z-10 container mx-auto px-4 py-12 text-center">
            <!-- Black Friday Title -->
            <div class="mb-8">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-6 animate-pulse">
                    بلک فرایدی اسکیپ زوم
                </h1>
                <p class="text-xl md:text-2xl lg:text-3xl text-yellow-300 font-bold mb-6 subtitle-shadow-animation">
                    تخفیف های شگفت انگیز اتاق فرار، لیزرتگ، پینت بال، سینماترس
                </p>
                <p class="text-xl md:text-2xl lg:text-3xl text-yellow-400 font-semibold">
                    به زودی <span class="dots-animation"></span>
                </p>
            </div>

            <!-- Countdown Timer -->
            <div class="mb-12">
                <div id="precampaign-timer" class="flex items-center justify-center gap-3 md:gap-4 lg:gap-5">
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 md:w-20 md:h-20 lg:w-24 lg:h-24 flex items-center justify-center rounded-xl bg-white shadow-lg text-red-600 text-2xl md:text-3xl lg:text-4xl font-extrabold timer-second">00</div>
                        <span class="text-white text-sm md:text-base mt-2">ثانیه</span>
                    </div>
                    <div class="text-yellow-300 text-2xl md:text-3xl lg:text-4xl font-bold">:</div>
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 md:w-20 md:h-20 lg:w-24 lg:h-24 flex items-center justify-center rounded-xl bg-white shadow-lg text-red-600 text-2xl md:text-3xl lg:text-4xl font-extrabold timer-minute">00</div>
                        <span class="text-white text-sm md:text-base mt-2">دقیقه</span>
                    </div>
                    <div class="text-yellow-300 text-2xl md:text-3xl lg:text-4xl font-bold">:</div>
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 md:w-20 md:h-20 lg:w-24 lg:h-24 flex items-center justify-center rounded-xl bg-white shadow-lg text-red-600 text-2xl md:text-3xl lg:text-4xl font-extrabold timer-hour">00</div>
                        <span class="text-white text-sm md:text-base mt-2">ساعت</span>
                    </div>
                    <div class="text-yellow-300 text-2xl md:text-3xl lg:text-4xl font-bold">:</div>
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 md:w-20 md:h-20 lg:w-24 lg:h-24 flex items-center justify-center rounded-xl bg-white shadow-lg text-red-600 text-2xl md:text-3xl lg:text-4xl font-extrabold timer-day">00</div>
                        <span class="text-white text-sm md:text-base mt-2">روز</span>
                    </div>
                </div>
            </div>

            <!-- Mobile Number Input -->
            <div class="max-w-md mx-auto mb-8">
                <?php echo do_shortcode('[call_me_notify subject="Black Friday - پیش کمپین 1404" text_color="#ffffff" button_bg_color="#fbbf24" icon_color="#000000" text="اگه میخوای از تخفیف ها جا نمونی شماره تو بذار تا بهت خبر بدیم"]'); ?>
            </div>

            <!-- Back to Home Link -->
            <div class="mt-10">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-block text-white hover:text-yellow-300 transition-colors duration-300 text-lg md:text-xl font-medium underline decoration-2 underline-offset-4">
                    بازگشت به صفحه اصلی
                </a>
            </div>
        </div>
    </div>

    <style>
        #precampaign-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .precampaign-content {
            position: relative;
            z-index: 10;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            }

            50% {
                box-shadow: 0 0 40px rgba(255, 215, 0, 0.8), 0 0 60px rgba(255, 215, 0, 0.6);
            }
        }

        @keyframes subtitle-shadow {

            0%,
            100% {
                text-shadow: 0 0 10px rgba(255, 215, 0, 0.3), 0 0 20px rgba(255, 215, 0, 0.2);
            }

            50% {
                text-shadow: 0 0 15px rgba(255, 215, 0, 0.5), 0 0 30px rgba(255, 215, 0, 0.3);
            }
        }

        .subtitle-shadow-animation {
            animation: subtitle-shadow 3s ease-in-out infinite;
        }

        .dots-animation {
            display: inline-block;
            min-width: 1.5em;
            text-align: right;
        }

        #precampaign-timer>div>div {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        body {
            overflow-x: hidden;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            // Particles animation
            const canvas = document.getElementById('precampaign-particles');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;

                const particles = [];
                const particleCount = 50;

                class Particle {
                    constructor() {
                        this.x = Math.random() * canvas.width;
                        this.y = Math.random() * canvas.height;
                        this.size = Math.random() * 3 + 1;
                        this.speedX = Math.random() * 2 - 1;
                        this.speedY = Math.random() * 2 - 1;
                        this.opacity = Math.random() * 0.5 + 0.2;
                    }

                    update() {
                        this.x += this.speedX;
                        this.y += this.speedY;

                        if (this.x > canvas.width) this.x = 0;
                        if (this.x < 0) this.x = canvas.width;
                        if (this.y > canvas.height) this.y = 0;
                        if (this.y < 0) this.y = canvas.height;
                    }

                    draw() {
                        ctx.fillStyle = `rgba(255, 215, 0, ${this.opacity})`;
                        ctx.beginPath();
                        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }

                for (let i = 0; i < particleCount; i++) {
                    particles.push(new Particle());
                }

                function animate() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    particles.forEach(particle => {
                        particle.update();
                        particle.draw();
                    });
                    requestAnimationFrame(animate);
                }

                animate();

                window.addEventListener('resize', () => {
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                });
            }

            // Countdown timer to November 24, 2024 at 00:00 Tehran time (3 Azar 1403)
            function updatePrecampaignTimer() {
                // Target: November 24, 2024 00:00:00 Tehran time = November 23, 2024 20:30:00 UTC (Tehran is UTC+3:30)
                const targetUTC = new Date('2025-11-24T08:30:00.000Z').getTime();
                const nowUTC = new Date().getTime();
                const diff = targetUTC - nowUTC;

                if (diff <= 0) {
                    $('.timer-day').text('00');
                    $('.timer-hour').text('00');
                    $('.timer-minute').text('00');
                    $('.timer-second').text('00');
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                $('.timer-day').text(String(days).padStart(2, '0'));
                $('.timer-hour').text(String(hours).padStart(2, '0'));
                $('.timer-minute').text(String(minutes).padStart(2, '0'));
                $('.timer-second').text(String(seconds).padStart(2, '0'));
            }

            updatePrecampaignTimer();
            setInterval(updatePrecampaignTimer, 1000);

            // Animate dots: . and .. and ... and . and .. and ...
            const dotsElement = document.querySelector('.dots-animation');
            if (dotsElement) {
                let dotCount = 0;
                const dotsArray = ['.', '..', '...'];

                function animateDots() {
                    dotsElement.textContent = dotsArray[dotCount];
                    dotCount = (dotCount + 1) % dotsArray.length;
                }

                animateDots();
                setInterval(animateDots, 500);
            }
        });
    </script>

    <?php wp_footer(); ?>
</body>

</html>
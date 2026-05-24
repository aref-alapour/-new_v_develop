/** @type {import('tailwindcss').Config} */
module.exports = {
  // مسیر فایل‌هایی که باید استایل بگیرند
  // content: [
  //   "./app/**/*.{js,jsx,ts,tsx}",
  //   "./components/**/*.{js,jsx,ts,tsx}"
  // ],
  content: [
  "./app/**/*.{js,jsx,ts,tsx}", 
  "./components/**/*.{js,jsx,ts,tsx}",
  "./src/**/*.{js,jsx,ts,tsx}" // اگر فایلتان اینجاست حتما اضافه شود
],
  // این خط برای نسخه ۴ کاملاً ضروری است
  presets: [require("nativewind/preset")],
  theme: {
    extend: {
      fontFamily: {
        sans: ['YekanBakh-Regular'],
        medium: ['YekanBakh-Medium'],
        bold: ['YekanBakh-Bold'],
        heavy: ['YekanBakh-Heavy'],
      },
      colors: {
        Orange: {
          DEFAULT: '#FD7013', 
          dark: '#D85D0D',    
          light: '#FFA666',   
        },
        // --- رنگ‌های پیشنهادی جدید ---
        status: {
          error: '#EF4444',   // قرمز برای خطاها (Tailwind Red 500)
          success: '#10B981', // سبز برای موفقیت (Tailwind Emerald 500)
        },
        gray: {
          text: '#1F2937',    // متن اصلی تیره (Gray 800)
          muted: '#6B7280',   // متن فرعی و لیبل‌ها (Gray 500)
          border: '#D1D5DB',  // حاشیه ورودی‌ها در حالت عادی (Gray 300)
          bg: '#F3F4F6',      // پس‌زمینه ملایم برای کل صفحه اگر نیاز شد (Gray 100)
          hr: '#D9D9D9'     
        }
      },
    },
  },
  plugins: [],
}

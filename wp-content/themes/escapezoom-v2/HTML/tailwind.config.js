/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/*.html",
    "./public/*/*.html",
    "./public/*/*/*.html",
    "./public/assets/js/*.js",
    "./public/assets/js/*/*/*.js",
  ],
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        border: {
          1: "#DADADA",
        },
        text: {
          1: "#0A192B",
          2: "#AAAAAA",
        },
        tgreen: {
          1: "#00B350",
          2: "#009742",
        },
        transparent: "transparent",
        current: "currentColor",
        white: "#ffffff",
        black: "#000000",
        error: "#E33827",
        // errorDark: "#ff1800",
        info: "#008cff",
        success: "#3BB01E",
        warning: "#E39222",
        blue: "#5091FB",

        primaryColor: "#fd7013",
        primaryContrastText: "#ffffff",

        secondaryColor: "#f51c4a",
        secondaryContrastText: "#fcecf3",

        breserve: "#F9FAFB",

        accentColor: "#1b112e",
        accentContrastText: "#ffffff",

        textColor: "#09192d",
        darkerTextColor: "#1b112e",
        lighterTextColor: "#888f93",
        headingColor: "#1b112e",
        background: "#ffffff",

        primary: {
          100: "#fdcdac",
          200: "#fda76d",
          300: "#fd893b",
          400: "#fd893b",
          500: "#fd7013", //main
          600: "#f26b12",
          700: "#e66511",
          800: "#d15c0f",
          900: "#be540e",
          1: "#fd8d42",
          2: "#FD7013",
          3: "#ca5a0f",
        },

        secondary: {
          100: "#fcecf3",
          200: "#FDC9D4",
          300: "#FDC4D0",
          400: "#FB89A2",
          500: "#F84F73",
          600: "#f51c4a", // main
          700: "#df1943",
          800: "#cb173d",
          900: "#b91537",
          1: "#3c5662",
          2: "#0b2c3b",
          3: "#09232f",
        },

        accent: {
          20: "#f3f9f6",
          50: "#e9f3ec",
          100: "#dbfeed",
          200: "#c5e9d9",
          250: "#64af8c",
          260: "#8faeae",
          400: "#00ff8c",
          410: "#04f775",
          420: "#05d778",
          450: "#02c96f",
          500: "#00c86f",
          550: "#04b968",
          560: "#059a58",
          600: "#06b159",
          700: "#019a55",
          800: "#228358",
          900: "#076f35",
        },

        yellow: {
          300: "#dfaf39",
          350: "#d3a128",
          400: "#eec100",
          800: "#ffa200",
        },

        slate: {
          50: "#f0f3f6",
          60: "#f5f8fa",
          100: "#edf2f5",
          110: "#e6eaf0",
          120: "#dce3ea",
          130: "#d0dce2",
          140: "#b6bfcb",
          150: "#97a3b1",
          200: "#9aa8b5", // #a8b4c2 #b6bfcb
          240: "#748497",
          250: "#616e7e",
          260: "#566e8b",
          280: "#ced7e2",
          300: "#9AA8CF",
          310: "#96a3b3",
          330: "#8b99aa",
          350: "#98a5b5",
          700: "#09192d",
          800: "#1b112e",
          900: "#041121",
          950: "#021021",
        },

        gray: {
          20: "#fafcfd",
          50: "#f2f6fa",
          100: "#ecf2f7",
          200: "#dee7ed",
          600: "#888f93",
        },
      },
      borderRadius: {
        none: "0px",
        sm: "0.125rem", // 2px
        DEFAULT: "0.25rem", // 4px
        md: "0.375rem", // 6px
        lg: "0.5rem", // 8px
        xl: "0.75rem", // 12px
        xlh: "0.875rem", // 14px
        "2xl": "1rem", // 16px
        "3xl": "1.25rem", // 20px
        "4xl": "1.5rem", // 24px
        full: "9999px",
      },
      boxShadow: {
        1: "none",
        2: "0px 3px 3px 0 rgba(9, 25, 45, 0.14)",
        3: "0px 3px 4px 0 rgba(19, 19, 19, 0.44)",
        4: "0px 3px 12px 0 #fd7013",
        5: " 0px 5px 5px 0 rgba(190, 207, 217, 0.45)",
        6: "0px 5px 5px 0 rgba(9, 25, 45, 0.24)",
        7: "0 0 0 1px #1b112e",
        8: "0 15px 18px -12px rgba(4, 17, 33, 0.50)",
        9: "-6px 7px 30px -9px rgba(4, 17, 33, 0.2)",
        10: "-50px 50px 30px -54px rgba(9, 25, 45, 0.1)",
        11: "0 9px 12px -6px rgba(4, 17, 33, 0.50)",
        12: "0px 2.5px 0 0 rgba(117, 145, 161, 0.27)",
        13: "0px 1.5px 0 0 rgba(117, 145, 161, 0.27)",
        14: "0px 2.5px 0 0 #d15c0f", // rgba(253, 112, 19, 0.3)
        15: "0px 2.5px 0 0 #b91537", // rgba(245, 28, 74, 0.3)
        16: "0px 2.5px 0 0 #019a55", // rgba(4, 247, 117, 0.3)
        17: "0px 2.5px 0 0 #041121",
        18: "0px 2px 2px 0 rgba(132, 151, 164, 0.24)",
        19: "0px 5px 2px -3px rgba(9, 25, 45, 0.14)",
        20: "3px 3px 13px 0px rgba(9, 25, 45, 0.3)",
        21: "0px 1px 2px 0 rgba(104, 120, 152, 0.23)",
        22: "0px 3px 2px 0 #e3e9ed",
        23: "0px 5px 5px 0 rgba(9, 25, 45, 0.12)",
        24: "0px 3px 2px 0 rgba(9, 25, 43, 0.17)",
        94: "0px 1px 1px 0 rgba(153, 174, 177, 0.39)",
        95: "0px 2px 2px 0 rgba(153, 174, 177, 0.39))",
        96: "0px 3px 3px 0 rgba(82, 107, 139, 0.14)",
        97: "0px 1px 0 0 rgba(127, 146, 171, 0.05)",
        98: "0px 4px 10px 0 rgba(161, 172, 193, 0.23)",
        99: "0px 1px 2px 0 rgba(127, 146, 171, 0.18)",
        100: "0px 4px 14px 0px #00000066;",
        101: "0px 4px 4px 0px rgba(0, 0, 0, 0.25)",
        102: "0px 2px 10px 0px #5091FB66;",
        103: "1px 1px 2px 0px #00492880;",
      },
      // fontFamily: {
      //   yeckan: [yekanBakh.style.fontFamily, "sans-serif"],
      //   sans: [
      //     "ui-sans-serif",
      //     "system-ui",
      //     "sans-serif",
      //     '"Apple Color Emoji"',
      //     '"Segoe UI Emoji"',
      //     '"Segoe UI Symbol"',
      //     '"Noto Color Emoji"',
      //   ],
      // },
      fontSize: {
        "5xs": ["0.5625rem", { lineHeight: "0.7rem" }], // 9px
        "4xs": ["0.625rem", { lineHeight: "0.7rem" }], // 10px
        "3xs": ["0.6875rem", { lineHeight: "0.7rem" }], // 11px
        "2xs": ["0.75rem", { lineHeight: "1rem" }], // 12px
        xs: ["0.8125rem", { lineHeight: "1rem" }], // 13px
        sm: ["0.875rem", { lineHeight: "1.25rem" }], // 14px
        md: ["0.9375rem", { lineHeight: "1.4rem" }], // 15px
        base: ["1rem", { lineHeight: "1.5rem" }], // 16px
        lg: ["1.125rem", { lineHeight: "1.75rem" }], // 18px
        xl: ["1.25rem", { lineHeight: "1.75rem" }], // 20px
        "2xl": ["1.5rem", { lineHeight: "2rem" }], // 24px
        "3xl": ["1.875rem", { lineHeight: "2.25rem" }], // 30px
        "4xl": ["2.25rem", { lineHeight: "2.5rem" }], // 36px
        "5xl": ["2.75rem", { lineHeight: "1" }], // 44px
        "6xl": ["3rem", { lineHeight: "1" }], // 48px
        "7xl": ["3.75rem", { lineHeight: "1" }], // 60px
        "8xl": ["4.5rem", { lineHeight: "1" }], // 72px
        "9xl": ["6rem", { lineHeight: "1" }], // 96px
        "10xl": ["8rem", { lineHeight: "1" }], // 128px
        h6: ["1.5rem", { lineHeight: "2rem" }], // 24px
        h5: ["1.5625rem", { lineHeight: "2rem" }], // 25px
        h4: ["1.625rem", { lineHeight: "2rem" }], // 26px
        h3: ["1.6875rem", { lineHeight: "2.25rem" }], // 27px
        h2: ["1.75rem", { lineHeight: "2.25rem" }], // 28px
        h1: ["1.8125rem", { lineHeight: "2.25rem" }], // 29px
      },
      fontFamily: {
        "light-yekanbakh": "light-yekanbakh",
        "regular-yekanbakh": "regular-yekanbakh",
        "medium-yekanbakh": "medium-yekanbakh",
        "bold-yekanbakh": "bold-yekanbakh",
        "heavy-yekanbakh": "heavy-yekanbakh",
        "fat-yekanbakh": "fat-yekanbakh",
      },
      lineHeight: {
        none: "1",
        tight: "1.25",
        snug: "1.5",
        normal: "1.75",
        relaxed: "2.36",
        // loose: "2",
        // 3: ".75rem",
        // 4: "1rem",
        // 5: "1.25rem",
        // 6: "1.5rem",
        // 7: "1.75rem",
        // 8: "2rem",
        // 9: "2.25rem",
        // 10: "2.5rem",
      },
      screens: {
        xs:"320px",
          sm: "410px",
        // md: "768px",
        // lg: "1024px",
        // xl: "1280px",
        xl: "1100px",
        "2xl": "1280px",
        "3xl": "1360px",
        "4xl": "1440px",
        //'5xl': '1564px', // 1536px
      },
      container: {
        screens: {
          xs:"320px",
          sm: "410px",
          md: "768px",
          lg: "1024px",
          xl: "1100px",
          "2xl": "1280px",
          "3xl": "1360px",
          "4xl": "1440px",
          //'5xl': '1564px', // 1536px
        },
        // padding: {
        // //   DEFAULT: '1rem',
        // //   sm: '2rem',
        // //   lg: '4rem',
        // //   xl: '5rem',
        // //   '2xl': '6rem',
        // },
      },
      zIndex: {
        1: "1",
        100: "100",
        200: "200",
      },
      spacing: {
        px: "1px",
        0: "0px",
        0.5: "0.125rem", // 2px
        0.75: "0.1875rem", // 3px
        1: "0.25rem", // 4px
        1.5: "0.375rem", // 6px
        2: "0.5rem", // 8px
        2.25: "0.5625rem", // 9px
        2.5: "0.625rem", // 10px
        3: "0.75rem", // 12px
        3.5: "0.875rem", // 14px
        4: "1rem", // 16px
        4.5: "1.125rem", // 18px
        5: "1.25rem", // 20px
        5.5: "1.375rem", // 22px
        6: "1.5rem", // 24px
        6.5: "1.625rem", // 26px
        7: "1.75rem", // 28px
        7.25: "1.8125rem", // 29px
        7.5: "1.875rem", // 30px
        8: "2rem", // 32px
        8.25: "2.0625rem", // 33px
        8.5: "2.125rem", // 34px
        9: "2.25rem", // 36px
        9.5: "2.375rem", // 38px
        10: "2.5rem", // 40px
        10.5: "2.625rem", // 42px
        11: "2.75rem", // 44px
        11.5: "2.875rem", // 46px
        12: "3rem", // 48px
        12.5: "3.125rem", // 50px
        13: "3.375rem", // 54px
        14: "3.5rem", // 56px
        15: "3.75rem", // 60px
        15.5: "3.875rem", // 62px
        16: "4rem", // 64px
        16.5: "4.125rem", // 66px
        17: "4.375rem", // 68px
        18: "4.5rem", // 72px
        19: "4.75rem", // 76px
        20: "5rem", // 80px
        21: "5.25rem", // 84px
        22: "5.5rem", // 88px
        23: "5.75rem", // 92px
        24: "6rem", // 96px
        25: "6.25rem", // 100px
        25.5: "6.375rem", // 102px
        28: "7rem", // 112px
        29: "7.25rem", // 116px
        30: "7.5rem", // 120px
        31: "7.75rem", // 124px
        32: "8rem", // 128px
        33: "8.25rem", // 132px
        34: "8.5rem", // 138px
        36: "9rem", // 144px
        37: "9.25rem", // 148px
        37.5: "9.375rem", // 150px
        40: "10rem", // 160px
        42: "10.5rem", // 168px
        44: "11rem", // 176px
        45: "11.25rem", // 180px
        46: "11.5rem", // 184px
        47: "11.75rem", // 188px
        48: "12rem", // 192px
        49: "12.25rem", // 196px
        52: "13rem", // 208px
        54: "13.5rem", // 216px
        56: "14rem", // 224px
        57: "14.25rem", // 228px
        58: "14.5rem", // 232px
        60: "15rem", // 240px
        62: "15.5rem", // 248px
        64: "16rem", // 256px
        67.5: "16.875rem", // 270px
        68: "17rem", // 272px
        69: "17.25rem", // 276px
        70: "17.5rem", // 280px
        72: "18rem", // 288px
        75: "18.75rem", // 300px
        76: "19rem", // 304px
        76.5: "19.125rem", // 306px
        77: "19.25rem", // 308px
        78: "19.5rem", // 312px
        78.25: "19.5625rem", // 313px
        78.5: "19.625rem", // 314px
        79: "19.75rem", // 316px
        80: "20rem", // 320px
        82: "20.5rem", // 328px
        82.5: "20.625rem", // 330px
        84: "21rem", // 336px
        86.5: "21.625rem", // 346px
        88: "22rem", // 352px
        92: "23rem", // 368px
        96: "24rem", // 384px
        98: "24.5rem", // 392px
        108: "27rem", // 432px
        120: "30rem", // 480px
      },
      backgroundImage: {
        menu: "linear-gradient(30deg, rgba(255,255,255,1) 50%, rgba(237, 242, 246,0.4) 93% 100%)",
        "button-gradient":
          "linear-gradient(to bottom, #edf2f5, #edf2f5), linear-gradient(to top, #fff, #f2f6fa)",
        "dashed-vertical":
          "linear-gradient(to bottom, #b2bfc9 0%, #b2bfc9 6px, transparent 6px, transparent 12px)",
        "dashed-horizontal":
          "linear-gradient(to right, #b2bfc9 0%, #b2bfc9 6px, transparent 6px, transparent 12px)",
      },
      gradientColorStopPositions: {
        "200%": "200%",
      },
      strokeWidth: {
        3: "3px",
        4: "5px",
        5: "5px",
        6: "6px",
      },

      rotate: {
        225: "225deg",
      },

      gridTemplateColumns: {
        16: "repeat(16, minmax(0, 1fr))",
        24: "repeat(24, minmax(0, 1fr))",
        6: "repeat(6, minmax(0, 1fr))",
        "2auto": "repeat(2, minmax(0, auto))",
        "3auto": "repeat(3, minmax(0, auto))",
        "4auto": "repeat(4, minmax(0, auto))",
        "5auto": "repeat(5, minmax(0, auto))",
        "6auto": "repeat(6, minmax(0, auto))",
        "12auto": "repeat(12, minmax(0, auto))",
      },
      gridColumn: {
        "span-13": "span 13 / span 13",
        "span-14": "span 14 / span 14",
      },
      keyframes: {
        "accordion-down": {
          from: { height: "0" },
          to: { height: "100px" },
        },
        "accordion-up": {
          from: { height: "100px" },
          to: { height: "0" },
        },
      },
      animation: {
        "accordion-down": "accordion-down 0.2s ease-out",
        "accordion-up": "accordion-up 0.2s ease-out",
      },
    },
  },
  plugins: [
    function ({ addVariant }) {
      addVariant("child", "& > *");
      addVariant("child-hover", "& > *:hover");
      addVariant("child-second", "& > * > *");
      addVariant("child-third", "& > * > * > *");
      addVariant("child-third-a", "& > * > * > a");
      addVariant("child-third-ul", "& > * > * > ul");
    },
    require("tailwind-scrollbar-hide"),
    require("tailwindcss-animation-delay"),
  ],
};

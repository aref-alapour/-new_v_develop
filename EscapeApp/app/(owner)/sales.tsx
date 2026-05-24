import { useMemo, useRef, useState } from "react";
import { useLocalSearchParams } from "expo-router";
import {
  Animated,
  Easing,
  Pressable,
  ScrollView,
  Text,
  TextInput,
  View,
} from "react-native";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { ArrowBottom } from "@/src/components/icons/arrow-bottom";

// بخش‌های مالی از منوی تب مالی (query: section)
type FinanceSection = "my-sales" | "discount-code" | "affiliate";
// پیش‌فرض‌های بازه: هفته / ماه / سه ماه
type TimePreset = "week" | "month" | "threeMonths";
// کدام گزینه خط نارنجی دارد: برچسب «همه» / یک پیش‌فرض / فقط تاریخ دستی
type TimeHighlight = "label" | TimePreset | "custom";

// ساختار دادهٔ هر کارت فروش (نمونه؛ بعداً از API)
type SaleCardData = {
  id: string;
  roomLabel: string;
  reserveCode: string;
  ticketsLabel: string;
  gameDate: string;
  purchaseDate: string;
  totalAmount: string;
  prepaid: string;
  balance: string;
  status: "done" | "pending" | "cancelled";
};

// رنگ بوردر انتخاب بازه (هماهنگ با دکمه اصلی)
const ORANGE = "#FD7013";

/** تبدیل سال/ماه/روز شمسی به یک عدد برای مقایسهٔ بازه */
function jalaliToKey(y: number, m: number, d: number) {
  return y * 10000 + m * 100 + d;
}

/** پارس رشته تاریخ (با یا بدون ساعت) به همان کلید عددی */
function parseJalaliDateString(raw: string): number | null {
  const head = raw.trim().split(/\s+/)[0];
  const parts = head.split(".").map((p) => Number(p));
  if (parts.length !== 3 || parts.some((n) => !Number.isFinite(n))) return null;
  const [y, m, d] = parts;
  return jalaliToKey(y, m, d);
}

/** برگرداندن کلید عددی به رشتهٔ نمایشی برای TextInput */
function formatJalaliKey(k: number) {
  const y = Math.floor(k / 10000);
  const m = Math.floor((k % 10000) / 100);
  const d = k % 100;
  const pad = (n: number) => String(n).padStart(2, "0");
  return `${y}.${pad(m)}.${pad(d)}`;
}

/** کسر روز تقریبی برای نمایش بازه (دمو؛ بدون کتابخانه تقویم) */
function subtractApproxDays(key: number, days: number): number {
  let y = Math.floor(key / 10000);
  let m = Math.floor((key % 10000) / 100);
  let d = key % 100;
  let left = days;
  const daysInMonth = (yy: number, mm: number) => {
    if (mm <= 6) return 31;
    if (mm <= 11) return 30;
    return yy % 4 === 3 ? 30 : 29;
  };
  while (left > 0) {
    if (d > 1) {
      d -= 1;
      left -= 1;
      continue;
    }
    m -= 1;
    if (m < 1) {
      m = 12;
      y -= 1;
    }
    d = daysInMonth(y, m);
    left -= 1;
  }
  return jalaliToKey(y, m, d);
}

// انتهای بازهٔ نسبی برای پیش‌فرض‌ها (ثابت دمو)
const RANGE_ANCHOR_END = jalaliToKey(1405, 6, 28);

/** محاسبهٔ از/تا برای یک هفته / یک ماه / سه ماه نسبت به انتهای لنگر */
function rangeForPreset(preset: TimePreset): { from: number; to: number } {
  const to = RANGE_ANCHOR_END;
  if (preset === "week") return { from: subtractApproxDays(to, 7), to };
  if (preset === "month") return { from: subtractApproxDays(to, 30), to };
  return { from: subtractApproxDays(to, 90), to };
}

// دادهٔ آزمایشی؛ فیلتر روی gameDate انجام می‌شود
const MOCK_SALES: SaleCardData[] = [
  {
    id: "sale-1",
    roomLabel: "ایستگاه شهر یخ",
    reserveCode: "1234567",
    ticketsLabel: "5 بلیت",
    gameDate: "1405.06.25 23:30",
    purchaseDate: "1405.06.28",
    totalAmount: "2,550,000",
    prepaid: "895,000",
    balance: "2,550,000",
    status: "done",
  },
  {
    id: "sale-2",
    roomLabel: "ایستگاه شهر یخ",
    reserveCode: "1234567",
    ticketsLabel: "5 بلیت",
    gameDate: "1405.06.10 20:00",
    purchaseDate: "1405.06.12",
    totalAmount: "2,550,000",
    prepaid: "895,000",
    balance: "2,550,000",
    status: "pending",
  },
  {
    id: "sale-3",
    roomLabel: "ایستگاه شهر یخ",
    reserveCode: "1234567",
    ticketsLabel: "5 بلیت",
    gameDate: "1405.05.01 18:00",
    purchaseDate: "1405.05.02",
    totalAmount: "2,550,000",
    prepaid: "895,000",
    balance: "2,550,000",
    status: "cancelled",
  },
];
// حداکثر ارتفاع پنل جزییات در حالت باز
const DETAILS_MAX_HEIGHT = 116;

export default function SalesScreen() {
  // فاصله از لبه پایین (نوار تب شناور)
  const insets = useSafeAreaInsets();
  const { section } = useLocalSearchParams<{ section?: string }>();
  const activeSection = (section as FinanceSection) || "my-sales";

  // خط نارنجی زیر کدام گزینهٔ بازه است
  const [timeHighlight, setTimeHighlight] = useState<TimeHighlight>("label");
  // تاریخ شروع و پایان دستی (رشته)
  const [dateFromInput, setDateFromInput] = useState("");
  const [dateToInput, setDateToInput] = useState("");
  // null = نمایش همه؛ غیر null = فیلتر تاریخ بازی بین از و تا
  const [appliedRange, setAppliedRange] = useState<{ from: number; to: number } | null>(null);

  /** انتخاب یک هفته / ماه / سه ماه: پر کردن ورودی‌ها و اعمال فیلتر */
  const applyPreset = (preset: TimePreset) => {
    setTimeHighlight(preset);
    const r = rangeForPreset(preset);
    setDateFromInput(formatJalaliKey(r.from));
    setDateToInput(formatJalaliKey(r.to));
    setAppliedRange(r);
  };

  /** اعمال بازه از روی دو TextInput (دکمه مشاهده) */
  const applyCustomDates = () => {
    const a = parseJalaliDateString(dateFromInput);
    const b = parseJalaliDateString(dateToInput);
    if (a == null || b == null) return;
    setTimeHighlight("custom");
    setAppliedRange({ from: Math.min(a, b), to: Math.max(a, b) });
  };

  /** کلیک روی «بازه زمانی»: حالت همه + بدون فیلتر */
  const applyAllTimeRange = () => {
    setTimeHighlight("label");
    setAppliedRange(null);
    setDateFromInput("");
    setDateToInput("");
  };

  /** کارت‌ها بر اساس تاریخ بازی و appliedRange */
  const filteredSales = useMemo(() => {
    if (appliedRange === null) return MOCK_SALES;
    return MOCK_SALES.filter((s) => {
      const key = parseJalaliDateString(s.gameDate);
      if (key == null) return false;
      return key >= appliedRange.from && key <= appliedRange.to;
    });
  }, [appliedRange]);

  /** جمع آمار از همان کارت‌های فیلترشده */
  const summary = useMemo(() => {
    let tickets = 0;
    let revenue = 0;
    let prepaid = 0;
    let balance = 0;
    for (const s of filteredSales) {
      const t = s.ticketsLabel.match(/(\d+)/);
      if (t) tickets += Number(t[1]);
      revenue += Number(s.totalAmount.replace(/,/g, ""));
      prepaid += Number(s.prepaid.replace(/,/g, ""));
      balance += Number(s.balance.replace(/,/g, ""));
    }
    return { tickets, revenue, prepaid, balance };
  }, [filteredSales]);

  // وضعیت باز/بسته بودن هر کارت
  const [expandedCards, setExpandedCards] = useState<Record<string, boolean>>({});
  // مقدار انیمیشن مستقل برای هر کارت
  const cardAnimations = useRef<Record<string, Animated.Value>>({});

  // ساخت یا دریافت Animated.Value مربوط به همان کارت
  const getCardAnimation = (cardId: string) => {
    if (!cardAnimations.current[cardId]) {
      cardAnimations.current[cardId] = new Animated.Value(0);
    }
    return cardAnimations.current[cardId];
  };

  // تغییر وضعیت کارت + اجرای انیمیشن کشویی باز/بسته
  const toggleCardDetails = (cardId: string) => {
    const nextExpanded = !expandedCards[cardId];
    setExpandedCards((prev) => ({ ...prev, [cardId]: nextExpanded }));

    Animated.timing(getCardAnimation(cardId), {
      toValue: nextExpanded ? 1 : 0,
      duration: 800,
      easing: Easing.out(Easing.cubic),
      useNativeDriver: false,
    }).start();
  };

  /** رندر یک کارت فروش + جزئیات کشویی */
  const renderSaleCard = (sale: SaleCardData) => {
    // رنگ نوار وضعیت برگزار/در انتظار/لغو
    const statusUi =
      sale.status === "done"
        ? { bar: "bg-[#E6F4EE]", text: "text-[#049654]", label: "برگزار شد" }
        : sale.status === "pending"
          ? { bar: "bg-[#FFF1E7]", text: "text-[#FD7013]", label: "در راه بازی" }
          : { bar: "bg-[#FEE8EC]", text: "text-[#F21543]", label: "لغو شد" };

    return (
      <View
        key={sale.id}
        className="flex items-start justify-start w-full bg-white rounded-2xl py-4 border-t border-[#E2E8F0]"
      >
        <View className="flex flex-row justify-between w-full">
          <View className="flex flex-row items-center justify-between">
            <Text className="text-sm font-bold text-[#64748B]">اتاق فرار</Text>
            <Text className="text-base font-extrabold text-[#0F172B] text-right">{sale.roomLabel}</Text>
          </View>

          <View className="flex flex-row items-center justify-between">
            <Text className="text-sm font-bold text-[#64748B]">کد رزرو</Text>
            <Text className="text-base font-extrabold text-[#0F172B] text-right">{sale.reserveCode}</Text>
          </View>
        </View>

        <View className="flex flex-row items-center justify-between w-full mt-4">
          <Text className="text-base font-bold ">{sale.ticketsLabel}</Text>
          <View className="flex flex-row items-center justify-between gap-3">
            <Text className="text-base font-bold text-[#64748B]">تاریخ بازی</Text>
            <Text className="text-base font-extrabold text-[#0F172B] text-right">{sale.gameDate}</Text>
          </View>
        </View>

        <Pressable className={`${statusUi.bar} rounded-lg px-4 py-2 w-full mt-5`}>
          <Text className={`text-center text-sm font-bold ${statusUi.text}`}>{statusUi.label}</Text>
        </Pressable>

        <Pressable
          onPress={() => toggleCardDetails(sale.id)}
          className="flex flex-row items-center justify-center mx-auto mt-3"
        >
          <Text className="text-sm font-bold text-[#64748B]">
            {expandedCards[sale.id] ? "مشاهده کمتر" : "مشاهده جزییات بیشتر"}
          </Text>
          {/* چرخش فلش هنگام باز بودن جزئیات */}
          <Animated.View
            style={{
              transform: [
                {
                  rotate: getCardAnimation(sale.id).interpolate({
                    inputRange: [0, 1],
                    outputRange: ["0deg", "180deg"],
                  }),
                },
              ],
            }}
          >
            <ArrowBottom width={18} height={18} />
          </Animated.View>
        </Pressable>

        {/* انیمیشن ارتفاع پنل جزئیات */}
        <Animated.View
          className="w-full mt-3 overflow-hidden"
          style={{
            height: getCardAnimation(sale.id).interpolate({
              inputRange: [0, 1],
              outputRange: [0, DETAILS_MAX_HEIGHT],
            }),
            opacity: getCardAnimation(sale.id).interpolate({
              inputRange: [0, 1],
              outputRange: [0, 1],
            }),
            transform: [
              {
                translateY: getCardAnimation(sale.id).interpolate({
                  inputRange: [0, 1],
                  outputRange: [-8, 0],
                }),
              },
            ],
          }}
        >
          <View className="flex items-center justify-center w-full bg-[#F7FAFA] p-4 rounded-lg">
            <View className="flex flex-row items-center justify-between w-full">
              <View className="flex flex-row items-center justify-center gap-2">
                <Text className="text-sm font-bold text-[#64748B]">تاریخ خرید</Text>
                <Text className="text-sm font-bold">{sale.purchaseDate}</Text>
              </View>

              <View className="flex flex-row items-center justify-center gap-2">
                <Text className="text-sm font-bold text-[#64748B]">مبلغ کل(تومان)</Text>
                <Text className="text-sm font-bold">{sale.totalAmount}</Text>
              </View>
            </View>

            <View className="flex flex-row items-center justify-between w-full">
              <View className="flex flex-row items-center justify-center gap-2">
                <Text className="text-sm font-bold text-[#64748B]">پیش پرداخت</Text>
                <Text className="text-sm font-bold text-[#049654]">{sale.prepaid}</Text>
              </View>

              <View className="flex flex-row items-center justify-center gap-2 mt-4">
                <Text className="text-sm font-bold text-[#64748B]">مانده پرداخت</Text>
                <Text className="text-sm font-bold">{sale.balance}</Text>
              </View>
            </View>
          </View>
        </Animated.View>
      </View>
    );
  };

  /** تب «فروش‌های من»: فیلتر بازه + لیست کارت */
  const renderMySales = () => (
    <View className="w-full">
      {/* ردیف انتخاب بازه (همه / پیش‌فرض‌ها) + بوردر نارنجی زیر فعال */}
      <View className="flex flex-row items-end justify-between">
        <Pressable
          onPress={applyAllTimeRange}
          accessibilityRole="button"
          accessibilityLabel="نمایش همه فروش‌ها"
          style={{
            borderBottomWidth: timeHighlight === "label" ? 2 : 0,
            borderBottomColor: ORANGE,
            paddingBottom: 4,
          }}
        >
          <Text className="text-base font-extrabold text-[#0F172B] text-right">بازه زمانی</Text>
        </Pressable>
        
        <Pressable
          onPress={() => applyPreset("week")}
          style={{
            borderBottomWidth: timeHighlight === "week" ? 2 : 0,
            borderBottomColor: ORANGE,
            paddingBottom: 4,
          }}
        >
          <Text className="text-base font-extrabold text-[#0F172B] text-right">یک هفته</Text>
        </Pressable>
        <Pressable
          onPress={() => applyPreset("month")}
          style={{
            borderBottomWidth: timeHighlight === "month" ? 2 : 0,
            borderBottomColor: ORANGE,
            paddingBottom: 4,
          }}
        >
          <Text className="text-base font-extrabold text-[#0F172B] text-right">یک ماه</Text>
        </Pressable>
        <Pressable
          onPress={() => applyPreset("threeMonths")}
          style={{
            borderBottomWidth: timeHighlight === "threeMonths" ? 2 : 0,
            borderBottomColor: ORANGE,
            paddingBottom: 4,
          }}
        >
          <Text className="text-base font-extrabold text-[#0F172B] text-right">سه ماه</Text>
        </Pressable>
      </View>

      <View className="w-full h-[1px] bg-[#E2E8F0] my-5" />

      {/* بازه دستی: از / تا + اعمال */}
      <View className="flex flex-row gap-2">
        <TextInput
          className="w-full max-w-[136px] h-10 bg-[#F9F9F9] rounded-lg px-4 py-2 border border-[#E2E8F0]"
          placeholder="1405.01.01"
          value={dateFromInput}
          onChangeText={setDateFromInput}
        />
        <TextInput
          className="w-full max-w-[136px] h-10 bg-[#F9F9F9] rounded-lg px-4 py-2 border border-[#E2E8F0]"
          placeholder="1405.02.01"
          value={dateToInput}
          onChangeText={setDateToInput}
        />
        <Pressable className="bg-[#FD7013] rounded-lg px-4 py-2" onPress={applyCustomDates}>
          <Text className="text-center text-white text-sm font-bold">مشاهده</Text>
        </Pressable>
      </View>

      <View className="w-full h-[1px] bg-[#E2E8F0] my-5" />

      {/* خلاصه آمار مطابق لیست فعلی */}
      <View className="flex flex-row items-center justify-between">
        <View className="flex items-center justify-between">
          <Text className="text-xs font-bold text-[#91A0A7]">تیکت</Text>
          <Text className="text-lg font-bold">{summary.tickets}</Text>
        </View>

        <View className="flex items-center justify-between">
          <Text className="text-xs font-bold text-[#91A0A7]">درآمد کل</Text>
          <Text className="text-lg font-bold">{summary.revenue.toLocaleString("en-US")}</Text>
        </View>

        <View className="w-[1PX] h-full bg-[#DBE2EA]" />

        <View className="flex items-center justify-between">
          <Text className="text-xs font-bold text-[#91A0A7]">پیش پرداخت</Text>
          <Text className="text-lg font-bold">{summary.prepaid.toLocaleString("en-US")}</Text>
        </View>

        <View className="flex items-center justify-between">
          <Text className="text-xs font-bold text-[#91A0A7]">بستانکاری</Text>
          <Text className="text-lg font-bold">{summary.balance.toLocaleString("en-US")}</Text>
        </View>
      </View>

      {filteredSales.length === 0 ? (
        <Text className="text-sm font-bold text-[#64748B] text-center mt-6">
          در این بازه فروشی ثبت نشده است.
        </Text>
      ) : (
        // کارت‌های بعد از فیلتر بازه
        filteredSales.map(renderSaleCard)
      )}
    </View>
  );

  /** سکشن کد تخفیف (فعلاً متن راهنما) */
  const renderDiscountCode = () => (
    <View className="mt-5 bg-white rounded-2xl px-4 py-5 border border-[#E2E8F0]">
      <Text className="text-base font-bold text-[#0F172B] text-right">لیست کدهای تخفیف</Text>
      <Text className="text-sm font-bold text-[#64748B] text-right mt-2">
        این بخش را جداگانه توسعه بده: ساخت کد، درصد تخفیف، تاریخ انقضا و وضعیت فعال/غیرفعال.
      </Text>
    </View>
  );

  /** سکشن همکاری در فروش (فعلاً متن راهنما) */
  const renderAffiliate = () => (
    <View className="mt-5 bg-white rounded-2xl px-4 py-5 border border-[#E2E8F0]">
      <Text className="text-base font-bold text-[#0F172B] text-right">همکاری در فروش</Text>
      <Text className="text-sm font-bold text-[#64748B] text-right mt-2">
        این بخش را جداگانه توسعه بده: لینک دعوت، نرخ پورسانت و گزارش پرداخت‌ها.
      </Text>
    </View>
  );

  /** سوئیچ محتوا بر اساس پارامتر section از منوی مالی */
  const renderSectionContent = () => {
    if (activeSection === "my-sales") return renderMySales();
    if (activeSection === "discount-code") return renderDiscountCode();
    return renderAffiliate();
  };

  return (
    <ScrollView
      className="flex-1 bg-white"
      contentContainerStyle={{
        paddingHorizontal: 24,
        paddingTop: 24,
        // جلوگیری از پنهان شدن انتهای لیست زیر تب‌بار absolute
        paddingBottom: insets.bottom + 130,
      }}
      showsVerticalScrollIndicator={false}
      keyboardShouldPersistTaps="handled"
    >
      {renderSectionContent()}
    </ScrollView>
  );
}

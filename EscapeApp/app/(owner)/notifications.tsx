/**
 * صفحهٔ فهرست اطلاعیه‌های مالک.
 * — لیست از API می‌آید (فعلاً MOCK_NOTICES)
 * — «خوانده شده» در حافظهٔ دستگاه ذخیره می‌شود
 * — بازگشت با حفظ تب/مسیر قبلی (پارامهای returnPath از Header)
 */
import AsyncStorage from "@react-native-async-storage/async-storage";
import { Ionicons } from "@expo/vector-icons";
import { Href, useLocalSearchParams, useRouter } from "expo-router";
import { useCallback, useEffect, useState } from "react";
import { Pressable, ScrollView, Text, View } from "react-native";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import { CircleOrange } from "@/src/components/icons/circle-orange";
import { Trumpet } from "@/src/components/icons/Trumpet";
import { Collection } from "@/src/components/icons/collection";
import { Reserve } from "@/src/components/icons/reserve";
import { Comment } from "@/src/components/icons/coment";

// کلید ثابت برای ذخیرهٔ آرایهٔ id اعلان‌هایی که کاربر یک‌بار دیده (دیگر نقطهٔ نارنجی نمی‌گیرند)
const READ_NOTICE_IDS_KEY = "escape_owner_read_notice_ids";

// نوع آیکن سمت چپ کارت؛ با نوع رویداد از API هم‌خوان می‌شود
type NoticeKind = "trumpet" | "collection" | "reserve" | "comment";

// نمونه اطلاعیه؛ بعداً از API جایگزین شود
type NoticeItem = {
  id: string;
  kind: NoticeKind;
  title: string;
  body: string;
  date: string;
};

// دادهٔ آزمایشی تا اتصال backend
const MOCK_NOTICES: NoticeItem[] = [
  {
    id: "1",
    kind: "trumpet",
    title: "افزایش اعتبار تخفیف",
    body: "عارف عزیز 50,000 تومن به اعتبار تخفیف برای رزرو بعدی شما افزوده شد.",
    date: "1405.06.28",
  },
  {
    id: "2",
    kind: "collection",
    title: "کالکشن شما توسط کاربری پسندیده شد",
    body: "2 کاربر کالکشن اتاق فرار های خفناک شما را پسندیدند.لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد.",
    date: "1405.06.28",
  },
  {
    id: "3",
    kind: "reserve",
    title: "موفقیت رزرو",
    body: "رزرو سانس به شماره رزرو 5421256 برای اتاق فرار “ایستگاه شهر یخ” ثبت شد.",
    date: "1405.06.28",
  },
  {
    id: "4",
    kind: "comment",
    title: "پاسخ به تیکت شما",
    body: "تیکت شما با عنوان”رزرو نشدن سانس” توسط کارشناس پشتیبانی پاسخ داده شد.",
    date: "1405.06.28",
  },
];

/** خواندن لیست id های خوانده‌شده از حافظهٔ محلی (باز شدن اپ حفظ می‌شود) */
async function loadReadNoticeIds(): Promise<string[]> {
  try {
    const raw = await AsyncStorage.getItem(READ_NOTICE_IDS_KEY);
    if (!raw) return [];
    const parsed: unknown = JSON.parse(raw);
    return Array.isArray(parsed) ? (parsed as string[]) : [];
  } catch {
    return [];
  }
}

/** ذخیرهٔ لیست id های خوانده‌شده */
async function persistReadNoticeIds(ids: string[]): Promise<void> {
  try {
    await AsyncStorage.setItem(READ_NOTICE_IDS_KEY, JSON.stringify(ids));
  } catch {
    /* نادیده — ذخیره محلی اختیاری */
  }
}

/** نگاشت kind هر اعلان به کامپوننت آیکن ۴۴×۴۴ */
function NoticeKindIcon({ kind }: { kind: NoticeKind }) {
  const sizeProps = { width: 44, height: 44 } as const;
  switch (kind) {
    case "trumpet":
      return <Trumpet {...sizeProps} />;
    case "collection":
      return <Collection {...sizeProps} />;
    case "reserve":
      return <Reserve {...sizeProps} />;
    case "comment":
      return <Comment {...sizeProps} />;
  }
}

export default function NotificationsScreen() {
  const router = useRouter();
  // فاصلهٔ ایمن پایین برای تب‌بار و ناچ
  const insets = useSafeAreaInsets();

  // هنگام ورود از Header، مسیر قبلی و در صورت نیاز queryها (JSON) برای بازگشت درست به همان تب
  const { returnPath, returnParams } = useLocalSearchParams<{
    returnPath?: string;
    returnParams?: string;
  }>();

  // id اعلان‌هایی که کاربر روی کارت زده → «دیده شده» و نقطهٔ نارنجی حذف می‌شود
  const [readIds, setReadIds] = useState<string[]>([]);

  // یک‌بار بعد از mount: بارگذاری وضعیت خوانده‌شده از AsyncStorage
  useEffect(() => {
    let cancelled = false;
    void loadReadNoticeIds().then((ids) => {
      if (!cancelled) setReadIds(ids);
    });
    return () => {
      cancelled = true;
    };
  }, []);

  // لمس کارت: این اعلان را خوانده‌شده کن و روی دیسک بنویس
  const markNoticeSeen = useCallback((id: string) => {
    setReadIds((prev) => {
      if (prev.includes(id)) return prev;
      const next = [...prev, id];
      void persistReadNoticeIds(next);
      return next;
    });
  }, []);

  /**
   * بازگشت: چون notifications داخل Tabs است، router.back() گاهی به تب اشتباه می‌رود.
   * اگر returnPath از Header آمده باشد، همان مسیر (و params) را navigate می‌کنیم؛ وگرنه back معمولی.
   */
  const goBack = useCallback(() => {
    if (returnPath && typeof returnPath === "string" && returnPath.length > 0) {
      let parsedParams: Record<string, string> | undefined;
      if (returnParams && typeof returnParams === "string") {
        try {
          const raw: unknown = JSON.parse(returnParams);
          if (raw && typeof raw === "object" && !Array.isArray(raw)) {
            parsedParams = raw as Record<string, string>;
          }
        } catch {
          parsedParams = undefined;
        }
      }
      const hasParams = parsedParams && Object.keys(parsedParams).length > 0;
      router.navigate(
        (hasParams
          ? { pathname: returnPath, params: parsedParams }
          : returnPath) as Href,
      );
      return;
    }
    router.back();
  }, [returnPath, returnParams, router]);

  return (
    <View className="flex-1 bg-[#F2F6FA]">
      {/* ردیف جدا از هدر سراسری اپ: فقط دکمهٔ بازگشت (هدر تب هنوز بالاست) */}
      <View className="flex flex-row-reverse items-center justify-between px-4 py-2">
        <Pressable
          onPress={goBack}
          hitSlop={12}
          accessibilityRole="button"
          accessibilityLabel="بازگشت"
        >
          <Ionicons name="arrow-back" size={26} color="#0F172B" />
        </Pressable>
      </View>

      <ScrollView
        className="flex-1 px-4 pt-4"
        contentContainerStyle={{ paddingBottom: insets.bottom + 120 }}
        showsVerticalScrollIndicator={false}
      >
        {MOCK_NOTICES.length === 0 ? (
          <Text className="text-base font-bold text-[#64748B] text-center mt-10">
            اطلاعیه جدیدی ندارید.
          </Text>
        ) : (
          <View className="flex flex-col gap-5">
            {MOCK_NOTICES.map((item) => {
              const isUnread = !readIds.includes(item.id);
              return (
                <Pressable
                  key={item.id}
                  onPress={() => markNoticeSeen(item.id)}
                  className="w-full overflow-hidden bg-white rounded-xl px-4 py-3 border border-[#E2E8F0] active:opacity-90"
                >
                  <View className="w-full pt-1.5 overflow-hidden">
                    {/* فقط اعلان خوانده‌نشده: نشانگر نارنجی */}
                    {isUnread ? (
                      <View className="mb-1">
                        <CircleOrange width={10} height={10} />
                      </View>
                    ) : null}
                    <View className="w-full flex flex-row items-start gap-2">
                      {/* آیکن ثابت اندازه؛ shrink-0 تا متن کنارش جمع نشود */}
                      <View className="shrink-0">
                        <NoticeKindIcon kind={item.kind} />
                      </View>
                      {/* min-w-0 + flex-1 تا متن بلند از کارت بیرون نزند */}
                      <View className="min-w-0 w-full flex-1">
                        <Text className="text-base font-bold">{item.title}</Text>
                        <Text className="text-xs font-bold text-[#4E5C6D] mt-1 leading-5 text-justify">
                          {item.body}
                        </Text>
                        <Text className="text-xs font-bold text-[#889BAD] mt-2">
                          {item.date}
                        </Text>
                      </View>
                    </View>
                  </View>
                </Pressable>
              );
            })}
          </View>
        )}
      </ScrollView>
    </View>
  );
}

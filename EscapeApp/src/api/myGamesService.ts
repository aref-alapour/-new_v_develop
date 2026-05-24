// src/api/myGamesService.ts — لیست اتاق‌های مالک؛ فعلاً mock، آمادهٔ جایگزینی با fetch

export type OwnerGameDto = {
  id: string;
  title: string;
  averageRating: number;
  ratingsCount: number;
  completedSessionsCount: number;
  /** مبلغ به تومان (عدد خام از API) */
  totalRevenueTomans: number;
  upcomingSessionsCount: number;
  /** اگر بک‌اند بدهد؛ در غیر این صورت UI از تصویر پیش‌فرض SVG استفاده می‌کند */
  coverImageUrl?: string | null;
};

const MOCK_OWNER_GAMES: OwnerGameDto[] = [
  {
    id: "1",
    title: "ایستگاه شهر یخ",
    averageRating: 3.5,
    ratingsCount: 40,
    completedSessionsCount: 150,
    totalRevenueTomans: 94_560_000,
    upcomingSessionsCount: 14,
  },
  {
    id: "2",
    title: "فرار از زندان مرکزی",
    averageRating: 4.2,
    ratingsCount: 128,
    completedSessionsCount: 320,
    totalRevenueTomans: 201_000_000,
    upcomingSessionsCount: 8,
  },
  {
    id: "3",
    title: "خانهٔ وحشت",
    averageRating: 4.8,
    ratingsCount: 56,
    completedSessionsCount: 89,
    totalRevenueTomans: 45_200_000,
    upcomingSessionsCount: 22,
  },
];

/**
 * لیست بازی‌های مالک.
 * @param accessToken — برای درخواست واقعی در هدر Authorization
 */
export async function fetchOwnerGames(
  accessToken?: string | null,
): Promise<OwnerGameDto[]> {
  return new Promise((resolve) => {
    setTimeout(() => {
      void accessToken; // در نسخهٔ واقعی در هدر Authorization استفاده شود

      // TODO: جایگزین با API واقعی، مثال:
      // const base = process.env.EXPO_PUBLIC_API_URL;
      // if (!base) throw new Error("آدرس API تنظیم نشده");
      // const res = await fetch(`${base}/owner/games`, {
      //   headers: {
      //     Accept: "application/json",
      //     ...(accessToken ? { Authorization: `Bearer ${accessToken}` } : {}),
      //   },
      // });
      // if (!res.ok) throw new Error(await res.text() || res.statusText);
      // const data = (await res.json()) as OwnerGameDto[];
      // return Array.isArray(data) ? data : [];

      resolve([...MOCK_OWNER_GAMES]);
    }, 500);
  });
}

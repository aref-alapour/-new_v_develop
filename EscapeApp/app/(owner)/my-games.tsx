import { Ionicons } from "@expo/vector-icons";
import { Image } from "expo-image";
import { useFocusEffect, useRouter } from "expo-router";
import { useCallback, useState } from "react";
import {
  ActivityIndicator,
  Pressable,
  RefreshControl,
  ScrollView,
  Text,
  View,
} from "react-native";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import ImgGame from "../../assets/images/manage-sanse/img-game.svg";
import {
  fetchOwnerGames,
  type OwnerGameDto,
} from "../../src/api/myGamesService";
import { PlusIcon } from "../../src/components/icons/plusIcon";
import { QrCodeIcon } from "../../src/components/icons/QR-code";
import { useAuth } from "../../src/context/AuthContext";

function formatTomans(n: number): string {
  try {
    return n.toLocaleString("fa-IR");
  } catch {
    return String(n);
  }
}

function ratingText(r: number): string {
  if (Number.isInteger(r)) return String(r);
  return r.toFixed(1);
}

function OwnerGameCard({
  game,
  onQrPress,
  withTopDivider,
}: {
  game: OwnerGameDto;
  onQrPress: (game: OwnerGameDto) => void;
  withTopDivider: boolean;
}) {
  return (
    <View
      className={`flex flex-col py-4 ${withTopDivider ? "mt-8 border-t border-[#E4EBF0] pt-8" : ""}`}
    >
      <View className="flex flex-row items-center justify-between">
        <View className="flex flex-row items-center gap-5">
          {game.coverImageUrl ? (
            <Image
              source={{ uri: game.coverImageUrl }}
              style={{ width: 46, height: 56, borderRadius: 6 }}
              contentFit="cover"
              transition={200}
            />
          ) : (
            <ImgGame width={46} height={56} />
          )}
          <Text className="max-w-[48%] text-sm font-bold">{game.title}</Text>
        </View>

        <View className="flex items-end gap-2">
          <View
            className="h-5 min-w-[34px] flex-row items-center justify-center rounded-full px-3"
            style={{ backgroundColor: "#EFC101" }}
          >
            <Text className="text-sm font-bold leading-none text-[#0F172B]">
              {ratingText(game.averageRating)}
            </Text>
          </View>
          <Text className="text-sm font-bold">
            میانگین {game.ratingsCount.toLocaleString("fa-IR")} رای
          </Text>
        </View>
      </View>

      <View className="mt-6 flex flex-row items-center justify-between">
        <View className="flex flex-col gap-1">
          <Text className="text-sm font-bold text-[#889BAD]">
            بازی های انجام شده
          </Text>
          <Text className="text-sm font-bold">
            {game.completedSessionsCount.toLocaleString("fa-IR")}
          </Text>
        </View>

        <View className="flex flex-col gap-1">
          <Text className="text-sm font-bold text-[#889BAD]">
            درآمد تاکنون
          </Text>
          <Text className="text-sm font-bold">
            {formatTomans(game.totalRevenueTomans)}
          </Text>
        </View>

        <View className="flex flex-col gap-1">
          <Text className="text-sm font-bold text-[#889BAD]">
            بازی های در راه
          </Text>
          <Text className="text-sm font-bold">
            {game.upcomingSessionsCount.toLocaleString("fa-IR")}
          </Text>
        </View>
      </View>

      <Pressable
        onPress={() => onQrPress(game)}
        className="mt-6 flex w-full flex-row items-center justify-between gap-2 rounded-lg bg-[#F2F6FA] px-3 py-2"
        accessibilityRole="button"
        accessibilityLabel="دریافت QR کد این بازی"
      >
        <Text className="text-sm font-bold text-[#5091FB]">
          دریافت QR کد این بازی
        </Text>
        <QrCodeIcon size={24} color="#5091FB" />
      </Pressable>
    </View>
  );
}

export default function MyGamesScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const { userToken, isLoaded: authLoaded } = useAuth();

  const [games, setGames] = useState<OwnerGameDto[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(
    async (mode: "initial" | "refresh") => {
      if (mode === "refresh") setRefreshing(true);
      else setLoading(true);
      setError(null);
      try {
        const list = await fetchOwnerGames(authLoaded ? userToken : null);
        setGames(list);
      } catch (e) {
        setError(e instanceof Error ? e.message : "خطا در دریافت لیست بازی‌ها");
        setGames([]);
      } finally {
        if (mode === "refresh") setRefreshing(false);
        else setLoading(false);
      }
    },
    [authLoaded, userToken],
  );

  useFocusEffect(
    useCallback(() => {
      if (!authLoaded) return;
      void load("initial");
    }, [authLoaded, load]),
  );

  const onQrPress = useCallback((game: OwnerGameDto) => {
    void game.id;
  }, []);

  return (
    <View className="flex-1">
      <View className="flex flex-row-reverse items-center justify-between px-4 py-2">
        <Pressable
          onPress={() => router.back()}
          hitSlop={12}
          accessibilityRole="button"
          accessibilityLabel="بازگشت"
        >
          <Ionicons name="arrow-back" size={26} color="#0F172B" />
        </Pressable>
      </View>

      <ScrollView
        className="flex-1 px-7 pt-2"
        contentContainerStyle={{ paddingBottom: insets.bottom + 120 }}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => void load("refresh")}
          />
        }
      >
        <View className="flex flex-row items-center justify-between">
          <Text className="text-lg font-bold">بازی‌های من</Text>
          <Pressable
            className="flex flex-row items-center gap-2"
            accessibilityRole="button"
            accessibilityLabel="ثبت اتاق"
            onPress={() => {}}
          >
            <Text className="text-xs font-bold">ثبت اتاق</Text>
            <PlusIcon size={18} color="#0F172B" />
          </Pressable>
        </View>

        <View className="mt-8">
          {!authLoaded || loading ? (
            <View className="items-center py-12">
              <ActivityIndicator size="large" color="#5091FB" />
            </View>
          ) : error ? (
            <View className="items-center gap-3 py-8">
              <Text className="text-center text-sm text-[#64748B]">{error}</Text>
              <Pressable
                onPress={() => void load("initial")}
                className="rounded-lg bg-[#5091FB] px-4 py-2"
                accessibilityRole="button"
                accessibilityLabel="تلاش دوباره"
              >
                <Text className="text-sm font-bold text-white">تلاش دوباره</Text>
              </Pressable>
            </View>
          ) : games.length === 0 ? (
            <Text className="py-8 text-center text-sm text-[#64748B]">
              هنوز اتاقی ثبت نشده است.
            </Text>
          ) : (
            games.map((game, index) => (
              <OwnerGameCard
                key={game.id}
                game={game}
                onQrPress={onQrPress}
                withTopDivider={index > 0}
              />
            ))
          )}
        </View>
      </ScrollView>
    </View>
  );
}

import { Ionicons } from "@expo/vector-icons";
import { Tabs, useRouter } from "expo-router";
import React, { useMemo, useRef, useState } from "react";
import { Animated, Easing, Pressable, Text, View } from "react-native";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import Svg, { Path } from "react-native-svg";
import Header from "../../src/components/Header";

const SHEET_HEIGHT = 220;
type FinanceSection = "my-sales" | "discount-code" | "affiliate";

function OwnerTabBarBackground() {
  const tabBarPath =
    "M220 6C229.686 6 238.714 8.84044 246.316 13.7403C250.435 16.3956 255.075 18.3398 259.976 18.3398H415C426.046 18.3398 435 27.2941 435 38.3398V94.3398C435 105.386 426.046 114.34 415 114.34H25C13.9543 114.34 5 105.386 5 94.3398V38.3398C5 27.2941 13.9543 18.3398 25 18.3398H180.024C184.925 18.3398 189.565 16.3956 193.684 13.7403C201.286 8.84044 210.314 6 220 6Z";

  return (
    <View
      style={{
        flex: 1,
        shadowColor: "#000",
        shadowOffset: { width: 0, height: -4 },
        shadowOpacity: 0.14,
        shadowRadius: 10,
        elevation: 6,
      }}
      pointerEvents="none"
    >
      <Svg
        width="100%"
        height="100%"
        viewBox="0 0 440 119"
        preserveAspectRatio="none"
      >
        <Path
          d={tabBarPath}
          fill="none"
          stroke="rgba(0, 0, 0, 0.05)"
          strokeWidth={6}
        />
        <Path
          d={tabBarPath}
          fill="none"
          stroke="rgba(0, 0, 0, 0.03)"
          strokeWidth={3}
        />
        <Path d={tabBarPath} fill="white" />
      </Svg>
    </View>
  );
}

export default function OwnerLayout() {
  const insets = useSafeAreaInsets();
  const router = useRouter();
  const [isFinanceSheetOpen, setIsFinanceSheetOpen] = useState(false);
  const sheetTranslateY = useRef(new Animated.Value(SHEET_HEIGHT)).current;
  const overlayOpacity = useRef(new Animated.Value(0)).current;

  const financeItems = useMemo(
    () => [
      {
        key: "my-sales" as FinanceSection,
        label: "فروش های من",
        icon: "trending-up-outline" as const,
      },
      {
        key: "discount-code" as FinanceSection,
        label: "ایجاد کد تخفیف",
        icon: "pricetag-outline" as const,
      },
      {
        key: "affiliate" as FinanceSection,
        label: "همکاری در فروش",
        icon: "git-network-outline" as const,
      },
    ],
    [],
  );

  const closeFinanceSheet = () => {
    Animated.parallel([
      Animated.timing(sheetTranslateY, {
        toValue: SHEET_HEIGHT,
        duration: 220,
        easing: Easing.out(Easing.cubic),
        useNativeDriver: true,
      }),
      Animated.timing(overlayOpacity, {
        toValue: 0,
        duration: 180,
        easing: Easing.out(Easing.cubic),
        useNativeDriver: true,
      }),
    ]).start(({ finished }) => {
      if (finished) setIsFinanceSheetOpen(false);
    });
  };

  const openFinanceSheet = () => {
    if (!isFinanceSheetOpen) setIsFinanceSheetOpen(true);

    Animated.parallel([
      Animated.timing(sheetTranslateY, {
        toValue: 0,
        duration: 260,
        easing: Easing.out(Easing.cubic),
        useNativeDriver: true,
      }),
      Animated.timing(overlayOpacity, {
        toValue: 1,
        duration: 220,
        easing: Easing.out(Easing.cubic),
        useNativeDriver: true,
      }),
    ]).start();
  };

  const selectFinanceSection = (section: FinanceSection) => {
    closeFinanceSheet();
    router.push({ pathname: "/(owner)/sales", params: { section } });
  };

  return (
    <View style={{ flex: 1, backgroundColor: "#FFFFFF" }}>
      <Tabs
        screenOptions={{
          header: () => <Header />,
          sceneStyle: {
            backgroundColor: "#FFFFFF",
          },
          tabBarShowLabel: true,
          tabBarActiveTintColor: "#FC6F13",
          tabBarInactiveTintColor: "#8e8e93",
          tabBarStyle: {
            backgroundColor: "transparent",
            position: "absolute",
            height: 96 + insets.bottom,
            paddingTop: 10,
            paddingBottom: insets.bottom,
            borderTopWidth: 0,
            elevation: 0,
            zIndex: 1,
          },
          tabBarItemStyle: {
            justifyContent: "center",
            alignItems: "center",
            paddingTop: 18,
            paddingBottom: 2,
          },
          tabBarIconStyle: {
            marginBottom: 2,
          },
          tabBarLabelStyle: {
            color: "#8e8e93",
            fontSize: 11,
            marginTop: 0,
          },
          tabBarBackground: () => <OwnerTabBarBackground />,
        }}
      >
        <Tabs.Screen
          name="wallet"
          options={{
            title: "کیف پول",
            tabBarIcon: ({ color, size }) => (
              <Ionicons name="wallet-outline" size={size} color={color} />
            ),
          }}
        />
        <Tabs.Screen
          name="cancell"
          options={{
            title: "کنسلی ها",
            tabBarIcon: ({ color, size }) => (
              <Ionicons name="close-circle-outline" size={size} color={color} />
            ),
          }}
        />
        <Tabs.Screen
          name="index"
          options={{
            title: "مدیریت سانس",
            tabBarIcon: ({ color, size }) => (
              <Ionicons name="home-outline" size={size} color={color} />
            ),
          }}
        />
        <Tabs.Screen
          name="comments"
          options={{
            title: "کامنت ها",
            tabBarIcon: ({ color, size }) => (
              <Ionicons name="chatbubbles-outline" size={size} color={color} />
            ),
          }}
        />
        <Tabs.Screen
          name="sales"
          options={{
            title: "مالی",
            tabBarIcon: ({ color, size }) => (
              <Ionicons name="cash-outline" size={size} color={color} />
            ),
            tabBarButton: (props) => (
              <Pressable
                style={props.style}
                onPress={openFinanceSheet}
                accessibilityRole="button"
                accessibilityLabel="باز کردن منوی مالی"
              >
                {props.children}
              </Pressable>
            ),
          }}
        />
        {/* صفحه اطلاعیه‌ها با هدر مشترک؛ در تب‌بار دیده نمی‌شود (فقط push از Header) */}
        <Tabs.Screen
          name="notifications"
          options={{
            href: null,
          }}
        />
        <Tabs.Screen
          name="my-games"
          options={{
            href: null,
          }}
        />
      </Tabs>

      {isFinanceSheetOpen ? (
        <>
          <Pressable
            style={{
              position: "absolute",
              top: 0,
              right: 0,
              bottom: 0,
              left: 0,
            }}
            onPress={closeFinanceSheet}
          >
            <Animated.View
              style={{
                flex: 1,
                backgroundColor: "rgba(15, 23, 43, 0.35)",
                opacity: overlayOpacity,
              }}
            />
          </Pressable>

          <Animated.View
            style={{
              position: "absolute",
              right: 0,
              left: 0,
              bottom: 20,
              zIndex: 0,
              transform: [{ translateY: sheetTranslateY }],
              paddingBottom: insets.bottom + 18,
              paddingHorizontal: 20,
              paddingTop: 14,
              backgroundColor: "white",
              borderTopLeftRadius: 20,
              borderTopRightRadius: 20,
              shadowColor: "#000",
              shadowOffset: { width: 0, height: -3 },
              shadowOpacity: 0.12,
              shadowRadius: 8,
              elevation: 8,
              height: 230,
            }}
          >
            <View className="w-12 h-1 rounded-full bg-[#E2E8F0] self-center mb-4" />

            <View className="flex flex-row justify-between gap-3">
              {financeItems.map((item) => (
                <Pressable
                  key={item.key}
                  className="flex items-center justify-between gap-2  bg-[#F1F5F9] rounded-lg px-4 py-3"
                  onPress={() => selectFinanceSection(item.key)}
                  accessibilityRole="button"
                >
                  <Ionicons name={item.icon} size={20} color="#90A1B9" />
                  <Text className="text-base font-bold text-[#0F172B]">
                    {item.label}
                  </Text>
                </Pressable>
              ))}
            </View>
          </Animated.View>
        </>
      ) : null}
    </View>
  );
}

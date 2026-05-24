import React, { useState } from 'react';
import {
  Dimensions,
  Pressable,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
// فرض میکنیم از @expo/vector-icons استفاده میکنید
import { Ionicons } from '@expo/vector-icons';
import { useGlobalSearchParams, usePathname, useRouter } from 'expo-router';
import { BrandLogo } from './BrandLogo';
import { useAuth } from '../context/AuthContext';
import {
  ChevronRightIcon,
  LogoutIcon,
  MyGamesIcon,
  PanelArrowIcon,
  SupportTicketsIcon,
} from './icons';

const PROFILE_PANEL_MAX_W = 374;
const PROFILE_PANEL_H_PAD = 16;

function buildNotificationsReturnParams(
  pathname: string,
  globalParams: ReturnType<typeof useGlobalSearchParams>,
): { returnPath: string; returnParams?: string } {
  const extra: Record<string, string> = {};
  for (const [key, value] of Object.entries(globalParams)) {
    if (key === "returnPath" || key === "returnParams") continue;
    if (typeof value === "string") extra[key] = value;
    else if (Array.isArray(value) && value[0] != null) extra[key] = String(value[0]);
  }
  const returnParams =
    Object.keys(extra).length > 0 ? JSON.stringify(extra) : undefined;
  return { returnPath: pathname, returnParams };
}

export default function Header() {
  const { signOut } = useAuth();
  const insets = useSafeAreaInsets();
  const router = useRouter();
  const pathname = usePathname();
  const globalParams = useGlobalSearchParams();
  const [showProfileMenu, setShowProfileMenu] = useState(false);
  const screenW = Dimensions.get('window').width;
  const screenH = Dimensions.get('window').height;
  const profilePanelWidth = Math.min(
    PROFILE_PANEL_MAX_W,
    screenW - PROFILE_PANEL_H_PAD * 2
  );
  const headerAreaHeight = insets.top + 64;
  const footerAreaHeight = 96 + insets.bottom;

  return (
    <View className="relative z-50">
      <View
        className="bg-white dark:bg-gray-900 border-b border-[#e5e5e5] dark:border-gray-800 px-4 pb-3 flex-row justify-between items-end"
        style={{ paddingTop: insets.top + 10 }} // برای رعایت فضای امن ناچ گوشی
      >

      {/* سمت راست: رفتن به صفحه اطلاعیه‌ها */}
      <TouchableOpacity
        onPress={() => {
          if (pathname.includes("notifications")) return;
          const ret = buildNotificationsReturnParams(pathname, globalParams);
          router.push({
            pathname: "/(owner)/notifications",
            params: {
              returnPath: ret.returnPath,
              ...(ret.returnParams ? { returnParams: ret.returnParams } : {}),
            },
          });
        }}
        accessibilityRole="button"
        accessibilityLabel="مشاهده اطلاعیه‌ها"
      >
        <Ionicons name="notifications-outline" size={28} color="gray" />
      </TouchableOpacity>


      {/* وسط: لوگو */}
      <View>
        <BrandLogo width={126} height={42} />
      </View>


      {/* سمت چپ: پروفایل */}
      <View className="relative">
        <TouchableOpacity onPress={() => setShowProfileMenu((prev) => !prev)}>
          <Ionicons name="person-circle-outline" size={30} color="gray" />
        </TouchableOpacity>
      </View>
      </View>

      {showProfileMenu ? (
        <>
          <Pressable
            onPress={() => setShowProfileMenu(false)}
            accessibilityRole="button"
            accessibilityLabel="بستن منوی پروفایل" 
            style={{
              position: 'absolute',
              top: headerAreaHeight,
              left: 0,
              width: screenW,
              height: Math.max(0, screenH - headerAreaHeight ),
              backgroundColor: 'rgba(15, 23, 43, 0.45)',
              zIndex: 40,
            }}
          />
          <View
            pointerEvents="box-none"
            className="absolute left-0 right-0 top-full items-center"
            style={{ zIndex: 50 }}
          >
            <View
              className="bg-white px-4 py-5 rounded-b-3xl border border-gray-100"
              style={{ width: profilePanelWidth }}
            >

                    <View className='flex flex-row items-center justify-between'>
                      <Text className='text-base font-bold'>سید حمید فراری زادگان</Text>
                      <Text className='text-base font-bold text-[#62748E]'>09124527788</Text>
                    </View>

                    <View className='flex flex-row items-center justify-between rounded-lg px-3 py-2 bg-[#E2E8F0] mt-6'>
                      <Text className='text-base font-bold text-[#1447E6]'>رفتن به پنل کاربری</Text>
                      <PanelArrowIcon width={16} height={16} />
                    </View>

                    <View className='w-full h-[1px] bg-[#F1F5F9] my-6'></View>

                    <View className='gap-6'>
                      <Pressable
                        className='flex flex-row items-center justify-between'
                        onPress={() => {
                          setShowProfileMenu(false);
                          router.push('/(owner)/my-games');
                        }}
                        accessibilityRole='button'
                        accessibilityLabel='بازی‌های من'
                      >
                        <View className='flex flex-row items-center justify-center gap-2'>
                          <MyGamesIcon width={22} height={22} />
                          <Text className='text-base font-bold'>بازی‌های من</Text>
                        </View>
                        <ChevronRightIcon width={6} height={12} />
                      </Pressable>

                      <View className='flex flex-row items-center justify-between'>
                        <View className='flex flex-row items-center justify-center gap-2'>
                          <SupportTicketsIcon width={22} height={22} />
                          <Text className='text-base font-bold'>تیکت های پشتیبانی</Text>
                        </View>

                        <ChevronRightIcon width={6} height={12} />

                      </View>

                      <Pressable
                        className='flex flex-row items-center justify-between'
                        onPress={async () => {
                          setShowProfileMenu(false);
                          await signOut();
                        }}
                        accessibilityRole='button'
                        accessibilityLabel='خروج از حساب'
                      >
                        <View className='flex flex-row items-center justify-center gap-2'>
                          <LogoutIcon width={22} height={22} />
                          <Text className='text-base font-bold'>خروج</Text>
                        </View>
                        <ChevronRightIcon width={6} height={12} />
                      </Pressable>
                    </View>

            </View>
          </View>
        </>
      ) : null}
    </View>
  );
}











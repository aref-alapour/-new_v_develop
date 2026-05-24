// app/_layout.tsx
import { useFonts } from 'expo-font';
import { Slot, useRouter, useSegments } from 'expo-router';
import * as SplashScreen from 'expo-splash-screen';
import * as Updates from 'expo-updates';
import { useEffect } from 'react';
import { ActivityIndicator, DevSettings, I18nManager, View } from 'react-native';
import '../global.css';
import { AuthProvider, useAuth } from '../src/context/AuthContext';

void SplashScreen.preventAutoHideAsync();



// این کامپوننت داخلی است که از useAuth استفاده می‌کند و مسیرها را کنترل می‌کند
function RootLayoutNav() {
  const { isLoaded, userToken } = useAuth();
  const segments = useSegments();
  const router = useRouter();

  // فعال کردن حالت راست‌چین
  useEffect(() => {
    const setRTLAndReload = async () => {
      if (!I18nManager.isRTL) {
        try {
          I18nManager.allowRTL(true);
          I18nManager.forceRTL(true);
          
          // استفاده از روش متفاوت برای توسعه و بیلد نهایی
          if (__DEV__) {
            // در حالت توسعه (مثلا Expo Go)
            DevSettings.reload(); 
          } else {
            // در بیلد نهایی (APK/IPA)
            await Updates.reloadAsync(); 
          }
        } catch (e) {
          console.error("Failed to set RTL and reload:", e);
        }
      }
    };

    setRTLAndReload();
  }, []);



  useEffect(() => {
    if (!isLoaded) return;

    const inAuthGroup = segments[0] === '(auth)';
    const inOwnerGroup = segments[0] === '(owner)';

    if (!userToken) {
      if (!inAuthGroup) {
        router.replace('/(auth)/login');
      }
    } else if (inAuthGroup || !inOwnerGroup) {
      // توکن داریم ولی هنوز داخل گروه مالک نیستیم (مثلاً `app/index.tsx` یا هر مسیری غیر از `(owner)`)
      router.replace('/(owner)');
    }
  }, [isLoaded, userToken, segments]);

  if (!isLoaded) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#FFFFFF' }}>
        <ActivityIndicator size="large" color="#0000ff" />
      </View>
    );
  }

  // <Slot /> تمام صفحات شما (مثل index.tsx و login.tsx) را رندر می‌کند
  return (
    <View style={{ flex: 1, backgroundColor: '#FFFFFF' }}>
      <Slot />
    </View>
  );
}

// این کامپوننت اصلی است که به Expo داده می‌شود
export default function RootLayout() {
  const [fontsLoaded] = useFonts({
    'YekanBakh-Regular': require('../assets/fonts/yekan-bakh/yekan-bakh-regular.ttf'),
    'YekanBakh-Medium': require('../assets/fonts/yekan-bakh/yekan-bakh-medium.ttf'),
    'YekanBakh-Bold': require('../assets/fonts/yekan-bakh/yekan-bakh-bold.ttf'),
    'YekanBakh-Heavy': require('../assets/fonts/yekan-bakh/yekan-bakh-heavy.ttf'),
  });

  useEffect(() => {
    if (fontsLoaded) {
      SplashScreen.hideAsync();
    }
  }, [fontsLoaded]);

  if (!fontsLoaded) {
    return null;
  }

  return (
    // در اینجا کل اپلیکیشن را درون AuthProvider قرار می‌دهیم
    <AuthProvider>
      <RootLayoutNav />
    </AuthProvider>
  );
}

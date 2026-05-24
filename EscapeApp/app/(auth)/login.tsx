import {
  View,
  Text,
  TextInput,
  Pressable,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  ActivityIndicator,
} from "react-native";
import { useState } from "react";
import { useRouter } from "expo-router";
import { checkUserExists } from '../../src/api/authService'; // فراخوانی سرویس
import { BrandLogo } from "../../src/components/BrandLogo";

export default function PhoneInputScreen() {
  const [phoneNumber, setPhoneNumber] = useState("");
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false); // استیت لودینگ
  const [errorMessage, setErrorMessage] = useState(''); // استیت خطا

  // اعتبارسنجی ساده شماره موبایل (۱۱ رقم و شروع با 09)
  const isFormValid = phoneNumber.length === 11 && phoneNumber.startsWith('09');

  const handlePhoneSubmit = async () => {
    if (!isFormValid) return;

    setIsLoading(true);
    setErrorMessage('');

    try {
      // ارسال درخواست به سرور برای بررسی وضعیت کاربر
      const response: any = await checkUserExists(phoneNumber);
      
      if (response.exists) {
        // کاربر قدیمی است، برو به صفحه رمز عبور
        router.push({
          pathname: '/(auth)/password',
          params: { phoneNumber: phoneNumber }
        });
      } else {
        // کاربر جدید است، برو به صفحه OTP برای ثبت نام
        // (فعلا چون ما exists را همیشه true گذاشتیم، این بخش اجرا نمی‌شود تا زمانی که API واقعی بیاید)
        router.push({
          pathname: '/(auth)/otp',
          params: { phoneNumber: phoneNumber, isSignUp: 'true' }
        });
      }
    } catch (error: any) {
      // در صورت قطعی اینترنت یا خطای سرور
      setErrorMessage('خطا در ارتباط با سرور. لطفا مجددا تلاش کنید.');
    } finally {
      setIsLoading(false); // پایان لودینگ
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === "ios" ? "padding" : "padding"}
      className="flex-1 bg-slate-50"
      keyboardVerticalOffset={0}
    >
      <ScrollView
        contentContainerStyle={{ flexGrow: 1, justifyContent: "center" }}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
        keyboardDismissMode="on-drag"
      >
        <View className="px-6 pb-10">
          <View className="items-center mb-10">
            <View className="mb-6 items-center">
              <Text className="text-2xl font-extrabold text-Orange mb-2 tracking-tight">
                logo
              </Text>
              <BrandLogo width={126} height={42} />
            </View>
          </View>

          <View className="w-full gap-y-4">
            <View>
              <Text className="text-slate-700 mb-3 mr-1 font-medium">
                لطفا شماره موبایل خود را وارد کنید.
              </Text>
              <TextInput
                className="w-full bg-white border border-Orange rounded-2xl px-4 py-4 text-slate-900 text-base text-left"
                placeholder="09123456789"
                placeholderTextColor="#94a3b8"
                value={phoneNumber}
                onChangeText={(text) => {
                setPhoneNumber(text);
                setErrorMessage(''); // پاک کردن خطا با تغییر متن
                }}
                keyboardType="phone-pad"
                maxLength={11}
              />
            </View>

            {/* نمایش پیام خطا در صورت وجود */}
            {errorMessage ? (
            <Text className="text-red-500 text-right mb-4 font-sans">{errorMessage}</Text>
            ) : null}

            <Pressable
              onPress={handlePhoneSubmit}
              className={`w-full rounded-2xl py-4 mt-4 border-b-2 ${
                phoneNumber.length >= 10
                  ? "bg-Orange border-Orange active:bg-Orange]"
                  : "bg-slate-300 border-slate-400"
              }`}
              disabled={phoneNumber.length < 10}
            >
              {isLoading ? (
            <ActivityIndicator color="white" />
          ) : (
            <Text className="text-white font-bold text-lg font-sans text-center">تایید و ادامه</Text>
          )}
            </Pressable>
          </View>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

import React, { useState } from "react";
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  ActivityIndicator,
  Pressable,
} from "react-native";
import { useRouter, useLocalSearchParams } from "expo-router";
import { SafeAreaView } from "react-native-safe-area-context";
import { loginWithPassword } from "../../src/api/authService";
import { Ionicons } from "@expo/vector-icons";
import { useAuth } from "../../src/context/AuthContext";
import { BrandLogo } from "../../src/components/BrandLogo";

// رمز صحیح : 1234

export default function PasswordScreen() {
  const { signIn } = useAuth();
  const router = useRouter();
  // دریافت شماره موبایل از صفحه قبل
  const { phoneNumber } = useLocalSearchParams();
  const [password, setPassword] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [isPasswordVisible, setIsPasswordVisible] = useState(false);

  // دکمه ورود تنها زمانی فعال می‌شود که حداقل ۴ کاراکتر برای رمز وارد شده باشد
  const isFormValid = password.length >= 4;

  const handleLogin = async () => {
    // اگر فرم معتبر نیست کاری نکن
    if (!isFormValid) return;

    setIsLoading(true); // شروع لودینگ
    setErrorMessage(""); // پاک کردن خطاهای قبلی

    try {
      // فقط یک بار API را فراخوانی می‌کنیم
      const response: any = await loginWithPassword(
        phoneNumber as string,
        password
      );

      if (response.success && response.token) {
        await signIn(response.token);
      }
    } catch (error: any) {
      // اگر رمز اشتباه بود (مثلا چیزی غیر از $1234$ وارد شد)
      setErrorMessage(error.message || "رمز عبور اشتباه است.");
    } finally {
      setIsLoading(false); // پایان لودینگ در هر صورت
    }
  };

  const handleChangePhone = () => {
    // بازگشت به صفحه قبل (لاگین) برای تغییر شماره
    router.back();
  };

  const handleForgotPassword = () => {
    router.push({
      pathname: "/(auth)/otp",
      params: { phoneNumber: String(phoneNumber ?? ""), isReset: "true" },
    });
  };

  return (
    <SafeAreaView className="flex-1 bg-white">
      <KeyboardAvoidingView
        behavior={Platform.OS === "ios" ? "padding" : "padding"}
        className="flex-1"
      >
        <ScrollView
          contentContainerStyle={{
            flexGrow: 1,
            justifyContent: "center",
            padding: 24,
          }}
          keyboardShouldPersistTaps="handled"
          keyboardDismissMode="on-drag"
        >
          <View className="w-full max-w-sm mx-auto ">
            <View className="mb-8 items-center">
              <BrandLogo width={126} height={42} />
            </View>

            <View className="flex-row items-center justify-center mb-6">
              <Text className="text-base font-bold text-gray-800 font-sans">
                شماره موبایل:
              </Text>
              <Text
                className="text-base text-Orange font-bold mr-2"
                style={{ direction: "ltr" }}
              >
                {phoneNumber}
              </Text>
            </View>

            <Text className="text-slate-700 mb-3 mr-1 font-medium">
              لطفا رمز ثابت خود را وارد کنید.
            </Text>

            <View className="flex-row items-center border border-gray-300 rounded-xl px-4 bg-gray-50 mb-4 h-14">
              {/* دکمه آیکون چشم (سمت چپ قرار می‌گیرد) */}
              <Pressable
                onPress={() => setIsPasswordVisible(!isPasswordVisible)}
                className="p-2"
              >
                <Ionicons
                  name={isPasswordVisible ? "eye-outline" : "eye-off-outline"}
                  size={24}
                  color="gray"
                />
              </Pressable>

              {/* فیلد رمز عبور (سمت راست قرار می‌گیرد و با flex-1 بقیه فضا را پر می‌کند) */}
              <TextInput
                className="flex-1 font-sans text-base mr-2"
                placeholder="رمز عبور"
                placeholderTextColor="#94a3b8"
                secureTextEntry={!isPasswordVisible}
                value={password}
                textAlign="left"
                style={{ writingDirection: "ltr", textAlign: "left" }}
                onChangeText={(text) => {
                  setPassword(text);
                  setErrorMessage(""); // پاک کردن خطا هنگام تایپ
                }}
              />
            </View>

            {/* این بخش را قبل از دکمه ورود اضافه کنید */}
            {errorMessage ? (
              <Text className="text-red-500 text-right mb-4 font-sans">
                {errorMessage}
              </Text>
            ) : null}

            {/* دکمه ورود خودتان را پیدا کنید و شبیه به این ویرایش کنید */}
            <Pressable
              onPress={handleLogin}
              disabled={!isFormValid || isLoading} // غیرفعال شدن در زمان لودینگ
              className={`w-full py-4 rounded-xl items-center justify-center flex-row
            ${!isFormValid || isLoading ? "bg-gray-400" : "bg-Orange"}`}
            >
              {isLoading ? (
                <ActivityIndicator color="white" /> // نمایش چرخ‌دنده
              ) : (
                <Text className="text-white font-bold text-lg font-sans">
                  ورود
                </Text> // متن دکمه
              )}
            </Pressable>

            <View className="w-full flex-row justify-between items-center px-2 mt-3">
              {/* ۸. دکمه فراموشی رمز عبور */}
              <TouchableOpacity onPress={handleForgotPassword}>
                <Text className="text-sm text-gray-500 font-sans">
                  فراموشی رمز عبور
                </Text>
              </TouchableOpacity>

              {/* ۷. نوشته تغییر شماره موبایل */}
              <TouchableOpacity onPress={handleChangePhone}>
                <Text className="text-sm text-Orange font-bold font-sans">
                  تغییر شماره موبایل
                </Text>
              </TouchableOpacity>
            </View>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

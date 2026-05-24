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
import { useRouter, useLocalSearchParams } from "expo-router";
import { Ionicons } from "@expo/vector-icons";
import { useAuth } from "../../src/context/AuthContext";
import { BrandLogo } from "../../src/components/BrandLogo";
import { resetPassword } from "../../src/api/authService";

export default function ResetPasswordScreen() {
  const router = useRouter();
  const { signIn } = useAuth();
  const { phoneNumber } = useLocalSearchParams<{ phoneNumber?: string }>();
  const phone = phoneNumber ? String(phoneNumber) : "";

  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  const isFormValid =
    phone.length > 0 &&
    newPassword.length >= 4 &&
    newPassword === confirmPassword;

  const handleSubmit = async () => {
    if (!isFormValid) return;

    setIsLoading(true);
    setErrorMessage("");

    try {
      const response = await resetPassword(phone, newPassword);
      if (response.success && response.token) {
        await signIn(response.token);
      } else {
        setErrorMessage("خطا در ثبت رمز عبور جدید.");
      }
    } catch {
      setErrorMessage("خطا در ارتباط با سرور. لطفاً اینترنت خود را بررسی کنید.");
    } finally {
      setIsLoading(false);
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
        <View className="px-6">
          <View className="mb-10 items-center">
            <View className="mb-8">
              <BrandLogo width={126} height={42} />
            </View>
            <Text className="mb-2 text-2xl font-bold text-slate-800">
              تعیین رمز عبور جدید
            </Text>
            {phone ? (
              <Text className="text-center text-sm text-slate-500" style={{ direction: "ltr" }}>
                {phone}
              </Text>
            ) : (
              <Text className="text-center text-sm text-red-500">
                شماره موبایل نامعتبر است؛ از مسیر ورود دوباره تلاش کنید.
              </Text>
            )}
          </View>

          <View className="space-y-4">
            <Text className="mb-4 mr-1 text-sm text-slate-500">
              لطفاً رمز عبور جدید خود را وارد کنید.
            </Text>

            <View className="relative mb-4 justify-center">
              <TextInput
                className="w-full rounded-xl border border-Orange bg-white py-4 pl-12 pr-4 font-medium text-slate-900"
                placeholder="رمز عبور جدید"
                placeholderTextColor="#94a3b8"
                secureTextEntry={!showNewPassword}
                value={newPassword}
                textAlign="left"
                style={{ writingDirection: "ltr", textAlign: "left" }}
                onChangeText={(text) => {
                  setNewPassword(text);
                  setErrorMessage("");
                }}
                editable={!isLoading}
              />
              <Pressable
                onPress={() => setShowNewPassword(!showNewPassword)}
                className="absolute left-4"
                disabled={isLoading}
              >
                <Ionicons
                  name={showNewPassword ? "eye-off-outline" : "eye-outline"}
                  size={24}
                  color="#94a3b8"
                />
              </Pressable>
            </View>

            <View className="relative justify-center">
              <TextInput
                className="w-full rounded-xl border border-Orange bg-white py-4 pl-12 pr-4 font-medium text-slate-900"
                placeholder="تکرار رمز عبور جدید"
                placeholderTextColor="#94a3b8"
                secureTextEntry={!showConfirmPassword}
                value={confirmPassword}
                textAlign="left"
                style={{ writingDirection: "ltr", textAlign: "left" }}
                onChangeText={(text) => {
                  setConfirmPassword(text);
                  setErrorMessage("");
                }}
                editable={!isLoading}
              />
              <Pressable
                onPress={() => setShowConfirmPassword(!showConfirmPassword)}
                className="absolute left-4"
                disabled={isLoading}
              >
                <Ionicons
                  name={showConfirmPassword ? "eye-off-outline" : "eye-outline"}
                  size={24}
                  color="#94a3b8"
                />
              </Pressable>
            </View>

            {confirmPassword.length > 0 && newPassword !== confirmPassword ? (
              <Text className="mt-1 px-2 text-right text-xs text-red-500">
                رمز عبور و تکرار آن مطابقت ندارند.
              </Text>
            ) : null}

            {errorMessage ? (
              <Text className="mt-2 text-center text-sm font-medium text-red-500">
                {errorMessage}
              </Text>
            ) : null}

            <Pressable
              onPress={handleSubmit}
              className={`mt-6 w-full items-center rounded-xl py-4 ${
                isFormValid && !isLoading
                  ? "border-b-4 border-Orange bg-Orange"
                  : "bg-slate-300"
              }`}
              disabled={!isFormValid || isLoading}
            >
              {isLoading ? (
                <ActivityIndicator size="small" color="#ffffff" />
              ) : (
                <Text className="text-lg font-bold text-white">ثبت رمز عبور و ورود</Text>
              )}
            </Pressable>

            <Pressable
              onPress={() => router.replace("/(auth)/login")}
              className="mt-2 items-center py-4"
              disabled={isLoading}
            >
              <Text className="font-medium text-slate-500">انصراف و بازگشت به ورود</Text>
            </Pressable>
          </View>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

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
import { useState, useEffect, useRef } from "react";
import { useRouter, useLocalSearchParams } from "expo-router";
import { sendOtp, verifyOtp } from "../../src/api/authService";
import { useAuth } from "../../src/context/AuthContext";

const OTP_LENGTH = 4;
/** در mock فعلی کد صحیح: ۱۲۳۴ (مثل رمز نمونه) */

export default function OtpScreen() {
  const router = useRouter();
  const { signIn } = useAuth();
  const { phoneNumber, isReset, isSignUp } = useLocalSearchParams<{
    phoneNumber?: string;
    isReset?: string;
    isSignUp?: string;
  }>();

  const [otpCode, setOtpCode] = useState<string[]>(Array(OTP_LENGTH).fill(""));
  const [timer, setTimer] = useState(120);
  const [isLoading, setIsLoading] = useState(false);
  const [isResending, setIsResending] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");

  const inputRefs = useRef<Array<TextInput | null>>([]);
  const fullOtp = otpCode.join("");
  const phone = phoneNumber ? String(phoneNumber) : "";

  useEffect(() => {
    let interval: ReturnType<typeof setInterval>;
    if (timer > 0) {
      interval = setInterval(() => {
        setTimer((prev) => prev - 1);
      }, 1000);
    }
    return () => clearInterval(interval);
  }, [timer]);

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, "0")}:${secs.toString().padStart(2, "0")}`;
  };

  const handleChange = (text: string, index: number) => {
    const numericValue = text.replace(/[^0-9]/g, "");
    const newCode = [...otpCode];
    newCode[index] = numericValue;
    setOtpCode(newCode);

    if (errorMessage) setErrorMessage("");

    if (numericValue && index < OTP_LENGTH - 1) {
      inputRefs.current[index + 1]?.focus();
    }
  };

  const handleKeyPress = (e: { nativeEvent: { key: string } }, index: number) => {
    if (e.nativeEvent.key === "Backspace") {
      if (!otpCode[index] && index > 0) {
        const newCode = [...otpCode];
        newCode[index - 1] = "";
        setOtpCode(newCode);
        inputRefs.current[index - 1]?.focus();
      }
    }
  };

  const handleVerifyOtp = async () => {
    if (fullOtp.length !== OTP_LENGTH || !phone) return;

    setIsLoading(true);
    setErrorMessage("");

    try {
      const response = await verifyOtp(phone, fullOtp);

      if (response.success) {
        if (isReset === "true" || isSignUp === "true") {
          router.push({
            pathname: "/(auth)/reset-password",
            params: { phoneNumber: phone },
          });
        } else {
          await signIn(response.token);
        }
      } else {
        setErrorMessage(response.message);
        setOtpCode(Array(OTP_LENGTH).fill(""));
        inputRefs.current[0]?.focus();
      }
    } catch {
      setErrorMessage("خطا در ارتباط با سرور");
    } finally {
      setIsLoading(false);
    }
  };

  const handleResendCode = async () => {
    if (!phone) return;
    setIsResending(true);
    setErrorMessage("");

    try {
      await sendOtp(phone);
      setTimer(120);
      setOtpCode(Array(OTP_LENGTH).fill(""));
      inputRefs.current[0]?.focus();
    } catch {
      setErrorMessage("خطا در ارسال مجدد کد");
    } finally {
      setIsResending(false);
    }
  };

  const title =
    isReset === "true"
      ? "بازیابی رمز عبور"
      : isSignUp === "true"
        ? "تایید شماره برای ثبت‌نام"
        : "تایید شماره موبایل";

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
        <View className="px-6 ">
          <View className="mb-10 items-center">
            <Text className="mb-4 text-2xl font-bold text-slate-800">{title}</Text>
          </View>

          <View className="space-y-6">
            <Text className="pb-5 text-center text-sm text-slate-500">
              کد تایید پیامک‌شده به شماره{" "}
              <Text className="font-bold text-slate-700">{phone || "—"}</Text> را وارد کنید.
            </Text>

            <View
              className="flex-row items-center justify-center gap-4"
              style={{ direction: "ltr" }}
            >
              {otpCode.map((digit, index) => (
                <TextInput
                  key={index}
                  ref={(el) => {
                    inputRefs.current[index] = el;
                  }}
                  className={`aspect-square w-[15%] rounded-xl border bg-white text-center text-2xl font-bold text-slate-900 ${
                    errorMessage
                      ? "border-red-500"
                      : digit
                        ? "border-Orange"
                        : "border-slate-200"
                  }`}
                  keyboardType="number-pad"
                  maxLength={1}
                  value={digit}
                  onChangeText={(text) => handleChange(text, index)}
                  onKeyPress={(e) => handleKeyPress(e, index)}
                  textContentType="oneTimeCode"
                  editable={!isLoading}
                />
              ))}
            </View>

            {errorMessage ? (
              <Text className="mt-2 text-center text-sm font-medium text-red-500">
                {errorMessage}
              </Text>
            ) : null}

            <View className="mb-3 mt-6 flex-row items-center justify-center">
              {timer > 0 ? (
                <Text className="text-sm text-slate-500">
                  ارسال مجدد کد تا {formatTime(timer)} دیگر
                </Text>
              ) : (
                <Pressable onPress={handleResendCode} disabled={isResending}>
                  {isResending ? (
                    <ActivityIndicator size="small" color="#f97316" />
                  ) : (
                    <Text className="text-sm font-bold text-Orange">ارسال مجدد کد</Text>
                  )}
                </Pressable>
              )}
            </View>

            <Pressable
              onPress={handleVerifyOtp}
              className={`mt-4 w-full items-center rounded-xl py-4 ${
                fullOtp.length === OTP_LENGTH && !isLoading
                  ? "border-b-4 border-Orange bg-Orange"
                  : "bg-slate-300"
              }`}
              disabled={fullOtp.length !== OTP_LENGTH || isLoading}
            >
              {isLoading ? (
                <ActivityIndicator size="small" color="#ffffff" />
              ) : (
                <Text className="text-lg font-bold text-white">تایید و ادامه</Text>
              )}
            </Pressable>

            <Pressable
              onPress={() => router.back()}
              className="items-center py-4"
              disabled={isLoading}
            >
              <Text className="font-medium text-slate-500">تغییر شماره موبایل</Text>
            </Pressable>
          </View>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

import React, { useMemo } from "react";
import { Modal, Pressable, ScrollView, Text, TextInput, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import type { SessionCardData } from "./SessionCard";
import { CallIcon } from "./icons/call";
import { CloseIcon } from "./icons/close";
import { Eye } from "./icons/eye";
import { CloseRedCircle } from "./icons/red-circle-close";

type CancelStep = 1 | 2 | 3;

type CancelReservationModalProps = {
  visible: boolean;
  selectedSession: SessionCardData | null;
  step: CancelStep;
  cancelReason: string;
  onReasonChange: (value: string) => void;
  onClose: () => void;
  onNext: () => void;
  onBack: () => void;
  onConfirm: () => void;
};

const PRESET_REASONS = [
  "قطعی برق مجموعه",
  "نقص فنی غیرمنتظره",
  "حاضر نبودن اکتور ها در مجموعه",
  "بسته شدن موقت مجموعه توسط اماکن",
  "دلایلی دیگر",
];
const CUSTOM_REASON_PREFIX = "دلایلی دیگر:";

export default function CancelReservationModal({
  visible,
  selectedSession,
  step,
  cancelReason,
  onReasonChange,
  onClose,
  onNext,
  onBack,
  onConfirm,
}: CancelReservationModalProps) {
  const isCustomReason = cancelReason.startsWith(CUSTOM_REASON_PREFIX);
  const customReasonValue = isCustomReason
    ? cancelReason.replace(CUSTOM_REASON_PREFIX, "").trim()
    : "";
  const canContinueStep2 = useMemo(() => {
    if (!cancelReason.trim()) {
      return false;
    }

    if (isCustomReason) {
      return customReasonValue.length > 0;
    }

    return true;
  }, [cancelReason, customReasonValue, isCustomReason]);

  const passengerName =
    selectedSession?.subtitle ?? selectedSession?.title ?? "-";

  return (
    <Modal
      transparent
      visible={visible}
      animationType="fade"
      onRequestClose={onClose}
    >
      <SafeAreaView className="flex-1">
        <View className="flex-1 justify-center items-center px-5 bg-black/45">
          <Pressable className="absolute inset-0" onPress={onClose} />
          <View
            className="w-full rounded-3xl bg-white p-6 gap-5"
            style={{ maxWidth: 420 }}
          >
            {step === 1 ? (
              <>
                <View className="flex-row-reverse items-center justify-between">
                  <Text className="text-base font-extrabold text-[#62748E]">
                    09124447788
                  </Text>
                  <View className="flex-row items-center gap-2">
                    <Text className="text-sm font-bold text-[#889BAD]">
                      {passengerName}
                    </Text>
                    <View className="w-[51px] h-[22px] bg-[#FFE0CB] justify-center items-center rounded-lg">
                      <Text className="text-xs font-extrabold text-[#FD7013]">
                        کارکشته
                      </Text>
                    </View>
                  </View>
                </View>

                <View className="gap-4">
                  <View className="w-full flex-row-reverse items-center gap-2">
                    <Pressable className="flex-1 flex-row-reverse items-center justify-center gap-2 rounded-lg h-10 bg-[#02C96F] px-2">
                      <Text className="text-sm font-bold text-white">
                        تماس با پلیر
                      </Text>
                      <CallIcon width={22} height={22} />
                    </Pressable>
                    <Pressable className="flex-1 flex-row-reverse items-center justify-center gap-2 rounded-lg h-10 bg-[#F3F4F6] px-2">
                      <Text className="text-sm font-bold text-[#889BAD]">
                        مشاهده پروفایل
                      </Text>
                      <Eye width={22} height={22} />
                    </Pressable>
                  </View>

                  <View className="w-full h-[1px] bg-[#E4EBF0] my-3" />

                  <View className="flex-row items-center justify-between">
                    <View className="flex-row items-center gap-1">
                      <Text className="text-sm font-bold text-[#889BAD]">
                        کد رزرو
                      </Text>
                      <Text className="text-sm font-bold">1234567</Text>
                    </View>
                    <View className="flex-row items-center gap-1">
                      <Text className="text-sm font-bold text-[#889BAD]">
                        تعداد
                      </Text>
                      <Text className="text-sm font-bold">5 بلیت</Text>
                    </View>
                  </View>

                  <View className="flex flex-row items-center justify-start">
                    <View className="flex-row items-center gap-1">
                      <Text className="text-sm font-bold text-[#889BAD]">
                        تاریخ رزرو
                      </Text>
                      <Text className="text-sm font-bold">
                        1405.06.28 22:30
                      </Text>
                    </View>
                  </View>

                  <View className="w-full h-[1px] bg-[#E4EBF0] my-3" />

                  <View className="flex-row-reverse gap-3">
                    <Pressable
                      className="flex-1 flex-row items-center justify-center gap-2 h-8 rounded-xl bg-[#EDF2F5]"
                      onPress={onNext}
                      accessibilityRole="button"
                    >
                      <Text className="text-base font-extrabold text-[#F21543]">
                        لغو سانس
                      </Text>
                      <CloseIcon width={7} height={7} />
                    </Pressable>
                  </View>
                </View>
              </>
            ) : null}

            {step === 2 ? (
              <View className="items-center justify-center py-6 max-w-[234px] mx-auto">
                <CloseRedCircle width={37} height={37} />
                <Text className="text-base font-bold text-black text-center mt-4">
                  آیا از لغو رزرو برای این پلیر مطمئن هستید؟
                </Text>
                <View className="w-full flex-row justify-between gap-3 mt-4">
                  <Pressable
                    className="flex-1 h-10 rounded-xl border border-[#E8EDF1] justify-center items-center bg-[#EDF2F5]"
                    onPress={onNext}
                  >
                    <Text className="text-base font-bold text-[#4E5C6D]">
                      بله
                    </Text>
                  </Pressable>

                  <Pressable
                    className="flex-1 h-10 rounded-xl border border-[#E8EDF1] justify-center items-center bg-[#EDF2F5]"
                    onPress={onClose}
                  >
                    <Text className="text-base font-bold text-[#4E5C6D]">
                      خیر
                    </Text>
                  </Pressable>
                </View>
              </View>
            ) : null}

            {step === 3 ? (
              <ScrollView
                showsVerticalScrollIndicator={false}
                contentContainerStyle={{ paddingBottom: 8 }}
                className="max-h-[520px]"
              >
                <View className="flex-row-reverse items-center justify-between">
                  <Text className="text-base font-extrabold text-[#62748E]">
                    09124447788
                  </Text>
                  <View className="flex-row items-center gap-2">
                    <Text className="text-sm font-bold text-[#889BAD]">
                      {passengerName}
                    </Text>
                    <View className="w-[51px] h-[22px] bg-[#FFE0CB] justify-center items-center rounded-lg">
                      <Text className="text-xs font-extrabold text-[#FD7013]">
                        کارکشته
                      </Text>
                    </View>
                  </View>
                </View>

                <View className="gap-4 mt-3">
                  <View className="w-full flex-row-reverse items-center gap-2">
                    <Pressable className="flex-1 flex-row-reverse items-center justify-center gap-2 rounded-lg h-10 bg-[#02C96F] px-2">
                      <Text className="text-sm font-bold text-white">
                        تماس با پلیر
                      </Text>
                      <CallIcon width={22} height={22} />
                    </Pressable>
                    <Pressable className="flex-1 flex-row-reverse items-center justify-center gap-2 rounded-lg h-10 bg-[#F3F4F6] px-2">
                      <Text className="text-sm font-bold text-[#889BAD]">
                        مشاهده پروفایل
                      </Text>
                      <Eye width={22} height={22} />
                    </Pressable>
                  </View>

                  <View className="w-full h-[1px] bg-[#E4EBF0] my-3" />

                  <View className="flex-row items-center justify-between">
                    <View className="flex-row items-center gap-1">
                      <Text className="text-sm font-bold text-[#889BAD]">
                        کد رزرو
                      </Text>
                      <Text className="text-sm font-bold">1234567</Text>
                    </View>
                    <View className="flex-row items-center gap-1">
                      <Text className="text-sm font-bold text-[#889BAD]">
                        تعداد
                      </Text>
                      <Text className="text-sm font-bold">5 بلیت</Text>
                    </View>
                  </View>

                  <View className="flex-row items-center justify-start">
                    <View className="flex-row items-center gap-1">
                      <Text className="text-sm font-bold text-[#889BAD]">
                        تاریخ رزرو
                      </Text>
                      <Text className="text-sm font-bold">
                        1405.06.28 22:30
                      </Text>
                    </View>
                  </View>

                  <View className="w-full h-[1px] bg-[#E4EBF0] my-3" />

                  <View className="flex-row-reverse gap-3">
                    <Pressable
                      className="flex-1 flex-row items-center justify-center gap-2 h-8 rounded-xl bg-[#EDF2F5]"
                      onPress={onNext}
                      accessibilityRole="button"
                    >
                      <Text className="text-base font-extrabold text-[#F21543]">
                        لغو سانس
                      </Text>
                      <CloseIcon width={7} height={7} />
                    </Pressable>
                  </View>
                </View>

                <View className="gap-4 flex">
                  <Text className="text-base font-bold text-black ">
                    چرا می خواهید سانس را لغو کنید؟
                  </Text>

                  <Text className="text-base font-bold text-black">
                    لطفا یک گزینه را انتخاب کنید:
                  </Text>
                  <View className="gap-2">
                    {PRESET_REASONS.map((reason) => {
                      const isSelected =
                        reason === "دلایلی دیگر"
                          ? isCustomReason
                          : cancelReason.trim() === reason;
                      return (
                        <Pressable
                          key={reason}
                          className={`h-11 rounded-xl border px-4 flex-row items-center justify-between ${
                            isSelected
                              ? "border-[#FD7013] bg-[#FFF3EB]"
                              : "border-[#E8EDF1] bg-white"
                          }`}
                          onPress={() =>
                            onReasonChange(
                              reason === "دلایلی دیگر"
                                ? CUSTOM_REASON_PREFIX
                                : reason,
                            )
                          }
                          accessibilityRole="button"
                        >
                          <Text
                            className={`text-sm font-bold ${isSelected ? "text-[#FD7013]" : "text-black"}`}
                          >
                            {reason}
                          </Text>
                          {isSelected ? (
                            <View className="w-2 h-2 rounded-full bg-[#FD7013]" />
                          ) : null}
                        </Pressable>
                      );
                    })}
                  </View>
                  {isCustomReason ? (
                    <TextInput
                      className="h-24 rounded-xl border border-[#E8EDF1] p-3 text-right"
                      multiline
                      textAlignVertical="top"
                      placeholder="دلیل را وارد کنید..."
                      placeholderTextColor="#889BAD"
                      value={customReasonValue}
                      onChangeText={(value) =>
                        onReasonChange(`${CUSTOM_REASON_PREFIX} ${value}`)
                      }
                    />
                  ) : null}

                  <View className="flex flex-row items-start justify-start gap-2"></View>
                  <View className="flex-row-reverse gap-3">
                    <Pressable
                      className={`flex-1 h-12 rounded-xl justify-center items-center ${
                        canContinueStep2 ? "bg-[#FD7013]" : "bg-[#FFD7BE]"
                      }`}
                      onPress={onConfirm}
                      disabled={!canContinueStep2}
                      accessibilityRole="button"
                    >
                      <Text className="text-base font-extrabold text-white">
                        ثبت‌ درخواست
                      </Text>
                    </Pressable>
                  </View>
                </View>
              </ScrollView>
            ) : null}
          </View>
        </View>
      </SafeAreaView>
    </Modal>
  );
}

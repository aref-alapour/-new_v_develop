import React from "react";
import { Modal, Pressable, Text, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";
import type { SessionCardData } from "./SessionCard";
import { CallIcon } from "./icons/call";
import { CloseIcon } from "./icons/close";
import { Eye } from "./icons/eye";

type CancelRequestDecisionModalProps = {
  visible: boolean;
  session: SessionCardData | null;
  onClose: () => void;
  onApprove: () => void;
  onReject: () => void;
};

export default function CancelRequestDecisionModal({
  visible,
  session,
  onClose,
  onApprove,
  onReject,
}: CancelRequestDecisionModalProps) {
  const playerName = session?.subtitle ?? session?.title ?? "-";

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
            <View className="flex-row-reverse items-center justify-between">
              <Text className="text-base font-extrabold text-[#62748E]">
                09124447788
              </Text>
              <View className="flex-row items-center gap-2">
                <Text className="text-sm font-bold text-[#889BAD]">
                  {playerName}
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
                  <Text className="text-sm font-bold">1405.06.28 22:30</Text>
                </View>
              </View>

              <View className="w-full h-[1px] bg-[#E4EBF0] my-3" />

              <Text className="text-base font-extrabold text-[#69737F] text-center">
                پلیر درخواست لغو این سانس را ثبت کرده است.{"\n"}
                آیا با لغو موافقت می‌کنید؟
              </Text>
            </View>

            <View className="flex-row-reverse gap-3">
              <Pressable
                className="flex-1 h-12 rounded-xl bg-[#FF6900] justify-center items-center"
                onPress={onApprove}
                accessibilityRole="button"
              >
                <Text className="text-sm font-extrabold text-white">
                  تایید و لغو سانس
                </Text>
              </Pressable>

              <Pressable
                className="flex-1 h-12 rounded-xl border border-[#E8EDF1] justify-center items-center bg-[#F1F5F9]"
                onPress={onReject}
                accessibilityRole="button"
              >
                <Text className="text-sm font-bold text-[#4E5C6D]">
                  رد کردن
                </Text>
              </Pressable>
            </View>
          </View>
        </View>
      </SafeAreaView>
    </Modal>
  );
}

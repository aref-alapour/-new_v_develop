import React, { useState, useMemo } from "react";
import { View, Pressable, Text, FlatList, TextInput, ScrollView } from "react-native";
import {
  OwnerChevronLeftIcon,
  OwnerChevronRightIcon,
  OwnerSettingsIcon,
  Calender,
  HandLikeIcon,
  HandDislikeIcon,
} from "../../src/components/icons";
import DateItem, { DateItemData } from "../../src/components/DateItem";
import ImgGame from "../../assets/images/manage-sanse/img-game.svg";
import ImgGame2 from "../../assets/images/manage-sanse/img-sample-2.svg";
import SessionCard, { SessionCardData } from "../../src/components/SessionCard";
import CancelReservationModal from "../../src/components/CancelReservationModal";
import CancelRequestDecisionModal from "../../src/components/CancelRequestDecisionModal";

const dayFormatter = new Intl.DateTimeFormat("fa-IR-u-ca-persian-nu-latn", {
  day: "numeric",
});

const weekdayFormatter = new Intl.DateTimeFormat("fa-IR-u-ca-persian", {
  weekday: "long",
});

const SESSION_ROWS: SessionCardData[][] = [
  [
    { id: "10", time: "10:00", variant: "closed", title: "بسته" },
    { id: "12", time: "12:00", variant: "open", title: "باز" },
  ],
  [
    {
      id: "14",
      time: "14:00",
      variant: "reserved",
      title: "سید حمید فراری زادگان",
    },
    {
      id: "16",
      time: "16:00",
      variant: "cancel-request",
      title: "درخواست لغو سانس دارد",
      subtitle: "سید حمید فراری زادگان",
    },
  ],
];

export default function Index() {
  const [selectedSession, setSelectedSession] = useState<SessionCardData | null>(null);
  const [isCancelModalOpen, setIsCancelModalOpen] = useState(false);
  const [cancelStep, setCancelStep] = useState<1 | 2 | 3>(1);
  const [cancelReason, setCancelReason] = useState("");
  const [selectedCancelRequestSession, setSelectedCancelRequestSession] =
    useState<SessionCardData | null>(null);
  const [isCancelRequestDecisionModalOpen, setIsCancelRequestDecisionModalOpen] =
    useState(false);

  const dates = useMemo<DateItemData[]>(() => {
    const baseDate = new Date();

    return Array.from({ length: 15 }, (_, index) => {
      const nextDate = new Date(baseDate);
      nextDate.setDate(baseDate.getDate() + index + 1);

      return {
        id: nextDate.toISOString(),
        day: dayFormatter.format(nextDate),
        label: weekdayFormatter.format(nextDate),
      };
    });
  }, []);

  const handleReservedSessionPress = (item: SessionCardData) => {
    if (item.variant !== "reserved") {
      return;
    }

    setSelectedSession(item);
    setCancelReason("");
    setCancelStep(1);
    setIsCancelModalOpen(true);
  };

  const handleCloseCancelModal = () => {
    setIsCancelModalOpen(false);
    setCancelStep(1);
    setCancelReason("");
    setSelectedSession(null);
  };

  const handleCancelRequestSessionPress = (item: SessionCardData) => {
    if (item.variant !== "cancel-request") {
      return;
    }

    setSelectedCancelRequestSession(item);
    setIsCancelRequestDecisionModalOpen(true);
  };

  const handleCloseCancelRequestDecisionModal = () => {
    setIsCancelRequestDecisionModalOpen(false);
    setSelectedCancelRequestSession(null);
  };

  const handleNextStep = () => {
    setCancelStep((prevStep) => {
      if (prevStep === 3 && !cancelReason.trim()) {
        return prevStep;
      }

      return (Math.min(prevStep + 1, 3) as 1 | 2 | 3);
    });
  };

  const handlePrevStep = () => {
    setCancelStep((prevStep) => Math.max(prevStep - 1, 1) as 1 | 2 | 3);
  };

  const handleConfirmCancel = () => {
    if (!selectedSession) {
      return;
    }

    console.log("Reservation cancellation payload:", {
      sessionId: selectedSession.id,
      time: selectedSession.time,
      title: selectedSession.title,
      subtitle: selectedSession.subtitle ?? null,
      reason: cancelReason,
    });

    handleCloseCancelModal();
  };

  const handleApproveCancelRequest = () => {
    if (!selectedCancelRequestSession) {
      return;
    }

    console.log("Cancel request approved:", {
      sessionId: selectedCancelRequestSession.id,
      time: selectedCancelRequestSession.time,
      title: selectedCancelRequestSession.title,
      subtitle: selectedCancelRequestSession.subtitle ?? null,
    });

    handleCloseCancelRequestDecisionModal();
  };

  const handleRejectCancelRequest = () => {
    if (!selectedCancelRequestSession) {
      return;
    }

    console.log("Cancel request rejected:", {
      sessionId: selectedCancelRequestSession.id,
      time: selectedCancelRequestSession.time,
      title: selectedCancelRequestSession.title,
      subtitle: selectedCancelRequestSession.subtitle ?? null,
    });

    handleCloseCancelRequestDecisionModal();
  };

  return (
    <ScrollView
      className="flex-1 bg-white"
      contentContainerStyle={{ flexGrow: 1, paddingHorizontal: 28, paddingBottom: 120 }}
      showsVerticalScrollIndicator={false}
      keyboardShouldPersistTaps="handled"
    >
      <View className="w-full flex justify-center items-center mb-8">
      <View className="w-full flex flex-row items-center justify-between mt-8">
        <View className="flex flex-row items-center justify-between gap-4">
          <ImgGame width={61} height={76} />

          <View className="flex flex-col justify-center items-center gap-4">
            <Text className="text-base font-extrabold">ایستگاه شهر یخ</Text>

            <View className="flex flex-row items-center justify-between gap-4">
              <Pressable className="w-6 h-6 border border-[#E8EDF1] flex justify-center items-center rounded-md">
                <OwnerChevronRightIcon width={7} height={12} />
              </Pressable>

              <Pressable className="w-6 h-6 border border-[#E8EDF1] flex justify-center items-center rounded-md">
                <OwnerChevronLeftIcon width={7} height={12} />
              </Pressable>
            </View>
          </View>
        </View>

        <Pressable className="w-10 h-10 border border-[#E8EDF1] flex justify-center items-center rounded-md p-2">
          <OwnerSettingsIcon width={22} height={22} />
        </Pressable>
      </View>

      <View className="w-full h-[1px] bg-gray-hr my-8" />

      <View className="w-full flex-row items-center gap-3">
        <View className="w-[52px] h-[56px] border border-[#E8EDF1] flex justify-center items-center rounded-[10px] p-2">
          <Calender width={7} height={12} />
        </View>

        <View
          className="w-[52px] h-[56px] border border-[#F75A13] bg-[#FC6F13] flex justify-center items-center rounded-[10px] p-2"
          style={{
            shadowColor: "#F75A13",
            shadowOffset: { width: 0, height: 1 },
            shadowOpacity: 0.35,
            shadowRadius: 1,
            elevation: 1,
          }}
        >
          <Text className="text-xl font-extrabold text-white">امروز</Text>
        </View>

        <View className="flex-1">
          <FlatList
            horizontal
            showsHorizontalScrollIndicator={false}
            data={dates}
            keyExtractor={(item) => item.id}
            contentContainerStyle={{ alignItems: "center", gap: 12, paddingRight: 8 }}
            renderItem={({ item }) => <DateItem item={item} />}
          />
        </View>
      </View>

      <View className=" flex items-center mt-[30px] gap-7">
        {SESSION_ROWS.map((row, rowIndex) => (
          <View
            key={`row-${rowIndex}`}
            className="w-full flex flex-row items-center justify-between gap-4"
          >
            {row.map((session) => (
              <SessionCard
                key={session.id}
                item={session}
                onPress={(item) => {
                  if (item.variant === "reserved") {
                    handleReservedSessionPress(item);
                    return;
                  }

                  if (item.variant === "cancel-request") {
                    handleCancelRequestSessionPress(item);
                  }
                }}
              />
            ))}
          </View>
        ))}
      </View>


      <View className="w-full h-[1px] bg-gray-hr my-8" />


      <View className="flex items-center justify-between w-full">
        <View className="flex flex-row items-center justify-between w-full">

          <View className="flex flex-row items-center gap-5">
            <ImgGame2 width={34} height={42} />
            <Text className="text-base font-bold text-[#889BAD]">
              اتاق فرار
              <Text className="text-base font-bold text-black">مدوزا</Text>
            </Text>
          </View>

          <View className="flex flex-row items-center bg-[#E6FAF1] rounded-lg w-[86px] h-[30px] flex justify-center items-center">
            <Text className="text-base font-extrabold text-[#049654]">در حال بازی</Text>
          </View>

        </View>

        <View  className="w-full  flex flex-row items-center justify-between mt-4">
          <View className="flex flex-row items-center">
            <Text className="text-base font-bold text-[#889BAD] mr-3">
              توسط 
              <Text className="text-base font-bold text-black ">
                سیدحمید فراری زادگان
              </Text>
            </Text>
             

          </View>
          <Text className="text-base font-bold ">5 بلیت</Text>
        </View>

        <View className="w-full h-[1px] bg-[#E4EBF0] my-4"></View>


        <TextInput
          className="w-full h-[100px] bg-[#F6F7F9] rounded-lg p-4 text-right border border-[#E8EDF1]"
          placeholder="دیدگاه خود را در مورد این پلیر بنویسید."
          placeholderTextColor="#889BAD"
          multiline
          textAlignVertical="top"
        />


        <View className="w-full flex flex-row items-center justify-between mt-4 mb-2 px-[75px]">

          <Pressable className="flex flex-row items-center gap-3">
            <HandLikeIcon width={43} height={46} />
            <Text className="text-base font-bold">راضی</Text>
          </Pressable>

          <Pressable className="flex flex-row items-center gap-3">
            <HandDislikeIcon width={43} height={46} />
            <Text className="text-base font-bold text-[#889BAD]">ناراضی</Text>
          </Pressable> 
        
        </View>

        <Pressable className="w-full h-12 bg-[#02C96F] rounded-lg flex justify-center items-center mt-2">
          <Text className="text-base font-extrabold text-white">ارسال دیدگاه</Text>
        </Pressable>



      </View>


      </View>

      <CancelReservationModal
        visible={isCancelModalOpen}
        selectedSession={selectedSession}
        step={cancelStep}
        cancelReason={cancelReason}
        onReasonChange={setCancelReason}
        onClose={handleCloseCancelModal}
        onNext={handleNextStep}
        onBack={handlePrevStep}
        onConfirm={handleConfirmCancel}
      />
      <CancelRequestDecisionModal
        visible={isCancelRequestDecisionModalOpen}
        session={selectedCancelRequestSession}
        onClose={handleCloseCancelRequestDecisionModal}
        onApprove={handleApproveCancelRequest}
        onReject={handleRejectCancelRequest}
      />
    </ScrollView>
  );
}


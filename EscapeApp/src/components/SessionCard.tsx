import React from "react";
import { Pressable, Text, View } from "react-native";
import { Eye, EyePink } from "./icons";

export type SessionCardVariant = "closed" | "open" | "reserved" | "cancel-request";

export type SessionCardData = {
  id: string;
  time: string;
  variant: SessionCardVariant;
  title: string;
  subtitle?: string;
};

type SessionCardProps = {
  item: SessionCardData;
  onPress?: (item: SessionCardData) => void;
};

function getCardStyles(variant: SessionCardVariant) {
  switch (variant) {
    case "reserved":
      return {
        containerClassName: "border-[#FD7013] bg-[#F6F7F9]",
        timeTextClassName: "text-xl font-extrabold text-black",
        actionClassName:
          "w-[157px] h-[40px] bg-white flex flex-row justify-between items-center rounded-[10px] px-2",
        titleTextClassName: "text-xs font-bold text-black",
        subtitleTextClassName: "",
      };
    case "cancel-request":
      return {
        containerClassName: "border-[#FD7013] bg-[#F21543]",
        timeTextClassName: "text-xl font-extrabold text-white",
        actionClassName:
          "w-[157px] h-[40px] bg-[#C70036] flex flex-row justify-between items-center rounded-[10px] px-2",
        titleTextClassName: "text-xs font-extrabold text-white",
        subtitleTextClassName: "text-[10px] font-bold text-white",
      };
    case "open":
      return {
        containerClassName: "border-[#E8EDF1] bg-white",
        timeTextClassName: "text-xl font-extrabold text-black",
        actionClassName:
          "w-[157px] h-[40px] bg-[#04B968] flex justify-center items-center rounded-[10px]",
        titleTextClassName: "text-xl font-extrabold text-white",
        subtitleTextClassName: "",
      };
    default:
      return {
        containerClassName: "border-[#E8EDF1] bg-white",
        timeTextClassName: "text-xl font-extrabold text-black",
        actionClassName:
          "w-[157px] h-[40px] bg-[#DBE2EA] flex justify-center items-center rounded-[10px]",
        titleTextClassName: "text-xl font-extrabold text-black",
        subtitleTextClassName: "",
      };
  }
}

export default function SessionCard({ item, onPress }: SessionCardProps) {
  const styles = getCardStyles(item.variant);
  const hasTrailingIcon = item.variant === "reserved" || item.variant === "cancel-request";

  return (
    <View
      className={`w-[178px] gap-1 border flex justify-center items-center rounded-[10px] p-2 ${styles.containerClassName}`}
      style={
        item.variant === "reserved"
          ? {
              shadowColor: "#FD7013",
              shadowOffset: { width: 0, height: 1 },
              shadowOpacity: 1,
              shadowRadius: 0,
              elevation: 1,
            }
          : undefined
      }
    >
      <Text className={styles.timeTextClassName}>{item.time}</Text>

      <Pressable
        className={styles.actionClassName}
        onPress={
          item.variant === "reserved" || item.variant === "cancel-request"
            ? () => onPress?.(item)
            : undefined
        }
      >
        {hasTrailingIcon ? (
          <>
            <View className="flex-1">
              <Text className={styles.titleTextClassName}>{item.title}</Text>
              {item.subtitle ? <Text className={styles.subtitleTextClassName}>{item.subtitle}</Text> : null}
            </View>
            {item.variant === "reserved" ? <Eye width={18} height={18} /> : null}
            {item.variant === "cancel-request" ? <EyePink width={18} height={18} /> : null}
          </>
        ) : (
          <Text className={styles.titleTextClassName}>{item.title}</Text>
        )}
      </Pressable>
    </View>
  );
}

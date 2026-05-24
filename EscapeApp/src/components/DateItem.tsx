import React, { memo } from "react";
import { Text, View } from "react-native";

export type DateItemData = {
  id: string;
  day: string;
  label: string;
};

type DateItemProps = {
  item: DateItemData;
};

const DateItem = memo(function DateItem({ item }: DateItemProps) {
  return (
    <View
      className="w-[52px] h-[56px] border border-[#E8EDF1] flex justify-center items-center rounded-[10px] p-2"
      style={{
        shadowColor: "#E8EDF1",
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 1,
        shadowRadius: 0,
        elevation: 1,
      }}
    >
      <Text className="text-xl font-extrabold">{item.day}</Text>
      <Text className="text-xs font-bold">{item.label}</Text>
    </View>
  );
});

export default DateItem;

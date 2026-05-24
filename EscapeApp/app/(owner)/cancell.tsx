import { AttentionIcon } from '@/src/components/icons/attention';
import { TimeIcon } from '@/src/components/icons/time';
import { View, Text, Pressable } from 'react-native';
import { Hourglass } from '@/src/components/icons/hourglass'; 
import { ArrowBottom } from '@/src/components/icons/arrow-bottom';

export default function WalletScreen() {
  return (
    <View className='flex flex-col justify-start items-center px-7'>

      <View className='flex flex-row items-center justify-between w-full mt-8'>
        <Text className='text-xl font-bold text-[#62748E]'>درخواست ها</Text>
        <Pressable className='flex flex-row justify-center gap-2 bg-[#F1F5F9] px-3 py-1 rounded-lg w-[155px]'>
          <Text className='text-base font-bold text-[#0F172B]'>تاریخچه لغو</Text>
          <TimeIcon width={20} height={20} />
        </Pressable>
      </View>

      <View className='flex flex-row items-start justify-start mt-5 gap-2'>
        <AttentionIcon width={20} height={20} />
        <Text className='text-sm font-bold text-[#BF9A00]'>از بخش "تاریخچه لغو" می‌توانید تمام سوابق لغو و درخواست‌ها را مشاهده کنید.</Text>
      </View>

      <View className='flex flex-row justify-between w-full rounded-lg bg-[#F1F5F9] h-[50px]'>
        <Text className='text-base font-bold bg-[#FD7013] text-white px-2 py-1 w-[90px] text-center'>همه</Text>
        <Text className='text-base font-bold text-[#889BAD]'>فوری</Text>
        <Text className='text-base font-bold text-[#889BAD]'>موعد بررسی گذشته</Text>
      </View>


      <View className="mt-8 overflow-hidden rounded-lg bg-[#EDF4FF]">
        <View className="flex flex-col items-start justify-start p-5">
        <View className='flex flex-row justify-between w-full '>
          <View className='flex flex-row items-center gap-3'>
            <Text className='text-base font-extrabold text-[#F21543]'>لغو زیر 12 ساعت</Text>
            <View className='w-[43px] h-5 bg-[#F21543] rounded-lg flex items-center justify-center'>
              <Text className='text-sm font-extrabold text-white'>فوری</Text>
            </View>
          </View>
          <View className='flex flex-row items-center bg-white rounded-lg px-2 py-1'>
            <Hourglass width={20} height={20} />
            <Text className='text-base font-bold text-[#889BAD]'>19ساعت پیش </Text>
          </View>
        </View>
        <Text className='text-base font-bold text-[#09192D] mt-3'>درخواست لغو
          <Text className='text-[#889BAD]'><Text>سانس</Text>پنج شنبه<Text className='text-[#FD7013]'>29 </Text>شهریور-17:40</Text>
        </Text>
        <Text className='text-base font-bold text-[#09192D] mt-3'><Text className='text-[#889BAD]'>اتاق فرار</Text>موزه وارانسی(بازگشت)</Text>

        <View className='flex flex-row items-center justify-between w-full mt-3'>
          <Pressable className='flex flex-row items-center justify-center gap-2 rounded-lg px-2 py-1 w-[94px] h-[40px] bg-white'>
            <Text className='text-base font-bold text-[#9AA8B7]'>رد کردن</Text>
          </Pressable>
          <Pressable className='flex flex-row items-center justify-center gap-2 rounded-lg px-2 py-1 w-[224px] h-[40px] bg-[#02C96F]'>
            <Text className='text-base font-bold text-white'>تایید و لغو سانس</Text>
          </Pressable>
        </View>

        <Pressable
          className="flex w-full flex-row items-center justify-center gap-2 border-t border-[#E4EBF0] py-3.5"
          style={{ backgroundColor: "#FFFFFF" }}
          accessibilityRole="button"
          accessibilityLabel="مشاهده جزییات"
        >
          <Text className="text-base font-bold text-[#0F172B]">مشاهده جزییات</Text>
          <ArrowBottom width={18} height={18} />
        </Pressable>

        <View className='w-full h-[1px] bg-[#E4EBF0] my-3'></View>

        <View className='flex flex-col justify-start items-start mt-3'>
          <View className='flex flex-row items-center justify-between w-full mb-10'>
            <View className='flex flex-row items-center justify-center gap-2'>
            <Text className='text-base font-bold text-[#889BAD]'>کد رزرو</Text>
            <Text className='text-base font-bold'>1234567</Text>
            </View>

            <View className='flex flex-row items-center justify-center gap-2'>
            <Text className='text-base font-bold text-[#889BAD]'>تاریخ رزرو</Text>
            <Text className='text-base font-bold'>1405.02.06 22:45</Text>
            </View>
          </View>

          <View className='flex flex-row items-center justify-between w-full mb-4'>
            <View className='flex flex-row items-center justify-center gap-2'>
            <Text className='text-base font-bold'>علیرضا فراری زاده</Text>
            </View>

            <View className='flex flex-row items-center justify-center gap-2'>
            <Text className='text-base font-bold text-[#889BAD]'>تعداد</Text>
            <Text className='text-base font-bold'>5بلیت</Text>
            </View>
          </View>

          <View className='flex flex-row items-center justify-between w-full'>          
            <Text className='text-base font-bold'>09124447788</Text>
          </View>  

        </View>

        </View> 

        
      </View>

    </View>
  );
} 
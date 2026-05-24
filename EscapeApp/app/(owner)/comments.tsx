import { useEffect, useMemo, useState } from 'react';
import {
  View,
  Text,
  useWindowDimensions,
  StyleSheet,
  Pressable,
  TextInput,
} from 'react-native';
import Animated, {
  LinearTransition,
  interpolate,
  useAnimatedStyle,
  useSharedValue,
  withTiming,
} from 'react-native-reanimated';
import { Pooker } from '../../src/components/icons/pooker';
import { ArrowBlueLeft } from '../../src/components/icons/arrow-blue-left';
import { Roport } from '../../src/components/icons/roport';
import { Forward } from '../../src/components/icons/forward';
import { CloseBlue } from '../../src/components/icons/close-blue';
import { Happy } from '../../src/components/icons/happy';

/** آمادهٔ اتصال به بک‌اند — همان شکلی که معمولاً از API برمی‌گردد */
export type CommentMood = 'neutral' | 'happy';

export type OwnerCommentDto = {
  id: string;
  authorName: string;
  /** تاریخ ثبت دیدگاه (رشتهٔ نمایشی یا از API) */
  commentDate: string;
  roomTypeLabel: string;
  gameTitle: string;
  sessionDateTime: string;
  body: string;
  mood: CommentMood;
  moodLabel: string;
};

/** نمونه؛ بعداً با `fetch` / React Query جایگزین کن */
const MOCK_OWNER_COMMENTS: OwnerCommentDto[] = [
  {
    id: '1',
    authorName: 'سیدحمید فراری زادگان',
    commentDate: '1405.02.20',
    roomTypeLabel: 'اتاق فرار',
    gameTitle: 'مدوزا',
    sessionDateTime: '1405.06.09 22:30',
    body: 'فضا تمیز بود؛ داستان متوسط و زمان سانس مناسب.',
    mood: 'neutral',
    moodLabel: 'معمولی بود',
  },
  {
    id: '2',
    authorName: 'مریم احمدی',
    commentDate: '1405.02.21',
    roomTypeLabel: 'اتاق فرار',
    gameTitle: 'نجات از زندان',
    sessionDateTime: '1405.06.10 19:00',
    body: 'بازی جذاب بود، حتماً دوباره می‌آیم.',
    mood: 'happy',
    moodLabel: 'عالی بود',
  },
];

/** بدون `numberOfLines` روی والد؛ وگرنه «بیشتر» داخل همان بریدگی می‌افتد و فقط … دیده می‌شود. */
function truncateForPreview(text: string, maxLen: number): {
  preview: string;
  isTruncated: boolean;
} {
  if (text.length <= maxLen) {
    return { preview: text, isTruncated: false };
  }
  const slice = text.slice(0, maxLen);
  const lastSpace = slice.lastIndexOf(' ');
  const end = lastSpace > maxLen * 0.55 ? lastSpace : maxLen;
  return {
    preview: `${text.slice(0, end).trimEnd()}…`,
    isTruncated: true,
  };
}

const replyInputStyles = StyleSheet.create({
  input: {
    textAlignVertical: 'top',
    shadowColor: '#09192D',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 2,
  },
});

const REPLY_PANEL_MAX_H = 280;

function CommentMoodRow({ mood, label }: { mood: CommentMood; label: string }) {
  if (mood === 'happy') {
    return (
      <View className='flex flex-row items-center gap-2'>
        <Happy color='#049654' />
        <Text className='text-sm font-medium text-[#049654]'>{label}</Text>
      </View>
    );
  }
  return (
    <View className='flex flex-row items-center gap-2'>
      <Pooker color='#BF9A00' />
      <Text className='text-sm font-medium text-[#BF9A00]'>{label}</Text>
    </View>
  );
}

type CommentCardProps = {
  comment: OwnerCommentDto;
  textPreviewBudget: number;
  onSubmitReply?: (commentId: string, text: string) => void;
  onReport?: (commentId: string) => void;
};

function CommentCard({
  comment,
  textPreviewBudget,
  onSubmitReply,
  onReport,
}: CommentCardProps) {
  const [bodyExpanded, setBodyExpanded] = useState(false);
  const [replyOpen, setReplyOpen] = useState(false);
  const [replyDraft, setReplyDraft] = useState('');
  const replyOpenProgress = useSharedValue(0);

  useEffect(() => {
    replyOpenProgress.value = withTiming(replyOpen ? 1 : 0, { duration: 320 });
  }, [replyOpen]);

  const replyPanelAnimatedStyle = useAnimatedStyle(() => ({
    maxHeight: interpolate(replyOpenProgress.value, [0, 1], [0, REPLY_PANEL_MAX_H]),
    opacity: interpolate(replyOpenProgress.value, [0, 1], [0, 1]),
    overflow: 'hidden',
  }));

  const { preview, isTruncated } = useMemo(
    () => truncateForPreview(comment.body, textPreviewBudget),
    [comment.body, textPreviewBudget]
  );

  return (
    <View className='w-full items-center gap-3'>
      <View className='w-full justify-start rounded-xl border border-[#E4EBF0] px-4 pb-2 pt-4'>
        <View className='w-full flex-row justify-between'>
          <View className='flex-row items-center gap-2'>
            <Text className='text-sm font-bold'>{comment.authorName}</Text>
            <View className='items-center'>
              <View className='h-[22px] w-[51px] flex-row items-center justify-center gap-1 rounded-3xl bg-[#FFEDD4] px-2'>
                <Text className='text-xs font-extrabold text-[#FF6900]'>
                  کارکشته
                </Text>
              </View>
            </View>
          </View>
          <Text className='text-sm font-bold text-[#90A1B9]'>
            {comment.commentDate}
          </Text>
        </View>

        <View className='mt-3 w-full flex-row justify-between'>
          <View className='flex-row items-center gap-2'>
            <Text
              className='shrink-0 text-sm font-bold text-[#90A1B9]'
              numberOfLines={1}
            >
              {comment.roomTypeLabel}
            </Text>
            <Text className='text-sm font-bold text-[#62748E]'>
              {comment.gameTitle}
            </Text>
          </View>
          <Text className='text-sm font-bold text-[#62748E]'>
            {comment.sessionDateTime}
          </Text>
        </View>

        <View className='my-3 h-px self-stretch bg-[#E4EBF0]' />

        <Animated.View layout={LinearTransition.duration(280)}>
          <Text
            className='text-sm font-medium leading-6'
            style={{ writingDirection: 'rtl' }}
          >
            {bodyExpanded ? comment.body : preview}
            {isTruncated && !bodyExpanded && (
              <Text
                className='text-sm font-medium text-[#90A1B9]'
                onPress={() => setBodyExpanded(true)}
              >
                {'\u00A0'}
                بیشتر
                <Text className='text-xs text-[#90A1B9]'> ▾</Text>
              </Text>
            )}
            {isTruncated && bodyExpanded && (
              <Text
                className='text-sm font-medium text-[#90A1B9]'
                onPress={() => setBodyExpanded(false)}
              >
                {'\u00A0'}
                کمتر
                <Text className='text-xs text-[#90A1B9]'> ▴</Text>
              </Text>
            )}
          </Text>
        </Animated.View>

        <View className='mb-1 mt-3' style={{ marginHorizontal: -16 }}>
          <View
            style={{
              width: '100%',
              height: Math.max(StyleSheet.hairlineWidth, 1.5),
              backgroundColor: '#E4EBF0',
            }}
          />
        </View>

        <CommentMoodRow mood={comment.mood} label={comment.moodLabel} />
      </View>

      <View className='w-full flex-row items-center justify-between'>
        {replyOpen ? (
          <Pressable
            className='flex-row items-center gap-2'
            onPress={() => setReplyOpen(false)}
          >
            <Text className='text-sm font-bold text-[#3F7FF5]'>بستن</Text>
            <CloseBlue size={12} />
          </Pressable>
        ) : (
          <Pressable
            className='flex-row items-center gap-2'
            onPress={() => setReplyOpen(true)}
          >
            <Text className='text-sm font-bold text-[#3F7FF5]'>
              پاسخ به این دیدگاه
            </Text>
            <ArrowBlueLeft size={12} />
          </Pressable>
        )}

        <Pressable
          className='flex-row items-center gap-2 rounded-lg border border-[#E8EDF1] px-2 py-1'
          onPress={() => onReport?.(comment.id)}
        >
          <Text className='text-[10px] font-bold text-[#F21543]'>گزارش</Text>
          <Roport size={12} />
        </Pressable>
      </View>

      <Animated.View
        style={replyPanelAnimatedStyle}
        pointerEvents={replyOpen ? 'auto' : 'none'}
      >
        <View className='w-full flex-row items-stretch gap-4 px-8 pb-3 pt-2'>
          <TextInput
            className='min-h-[58px] max-h-[200px] flex-1 rounded-lg border border-[#E8EDF1] bg-white px-3 py-2 text-sm text-[#09192D]'
            placeholder='پاسخ خود را اینجا بنویسید...'
            placeholderTextColor='#90A1B9'
            textAlign='right'
            multiline
            scrollEnabled
            value={replyDraft}
            onChangeText={setReplyDraft}
            style={replyInputStyles.input}
          />
          <View className='justify-center'>
            <Pressable
              onPress={() => onSubmitReply?.(comment.id, replyDraft)}
              accessibilityRole='button'
            >
              <Forward size={58} />
            </Pressable>
          </View>
        </View>
      </Animated.View>
    </View>
  );
}

export default function WalletScreen() {
  const { width } = useWindowDimensions();

  const textPreviewBudget = useMemo(() => {
    const horizontalGutter = 56 + 32;
    const contentW = Math.max(220, width - horizontalGutter);
    const approxCharsPerLine = Math.max(18, Math.floor(contentW / 9));
    return Math.max(48, approxCharsPerLine * 3 - 12);
  }, [width]);

  const [comments] = useState<OwnerCommentDto[]>(MOCK_OWNER_COMMENTS);

  const handleSubmitReply = (commentId: string, text: string) => {
    // TODO: فراخوانی API، مثلاً POST /owner/comments/:id/replies
    console.warn('submit reply', commentId, text);
  };

  const handleReport = (commentId: string) => {
    // TODO: API گزارش
    console.warn('report', commentId);
  };

  return (
    <View className='mt-8 flex w-full items-center justify-start gap-10 px-7 pb-10'>
      {comments.map((c) => (
        <CommentCard
          key={c.id}
          comment={c}
          textPreviewBudget={textPreviewBudget}
          onSubmitReply={handleSubmitReply}
          onReport={handleReport}
        />
      ))}
    </View>
  );
}

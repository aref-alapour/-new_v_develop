/**
 * روت پیش‌فرض؛ مسیر واقعی توسط RootLayoutNav در app/_layout.tsx
 * (توکن → (owner) ، بدون توکن → (auth)/login) تعیین می‌شود.
 */
import { View } from 'react-native';

export default function Index() {
  return <View className="flex-1 bg-white" />;
}

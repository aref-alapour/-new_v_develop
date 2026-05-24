// src/context/AuthContext.tsx
import React, { createContext, useContext, useEffect, useState } from 'react';
import * as SecureStore from 'expo-secure-store';

type AuthContextType = {
  isLoaded: boolean;
  userToken: string | null;
  signIn: (token: string) => Promise<void>;
  signOut: () => Promise<void>;
};

const AUTH_TOKEN_KEY = 'userToken';

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [userToken, setUserToken] = useState<string | null>(null);
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    // هنگام باز شدن اپلیکیشن، چک می‌کنیم آیا توکنی قبلا ذخیره شده یا خیر
    const loadToken = async () => {
      try {
        const token = await SecureStore.getItemAsync(AUTH_TOKEN_KEY);
        if (token) {
          setUserToken(token);
        }
      } catch (error) {
        console.error('Error loading token', error);
      } finally {
        setIsLoaded(true); // بررسی به اتمام رسید
      }
    };

    loadToken();
  }, []);

  const signIn = async (token: string) => {
    await SecureStore.setItemAsync(AUTH_TOKEN_KEY, token);
    setUserToken(token);
  };

  const signOut = async () => {
    await SecureStore.deleteItemAsync(AUTH_TOKEN_KEY);
    setUserToken(null);
  };

  return (
    <AuthContext.Provider value={{ isLoaded, userToken, signIn, signOut }}>
      {children}
    </AuthContext.Provider>
  );
}

// هوک کاستوم برای استفاده راحت‌تر
export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

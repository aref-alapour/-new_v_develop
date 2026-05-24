// api/authService.ts — لایهٔ تماس با بک‌اند (فعلاً mock)

export type VerifyOtpResult =
  | { success: true; token: string }
  | { success: false; message: string };

/** شبیه‌سازی ورود با رمز؛ در بک‌اند واقعی جایگزین شود */
export const loginWithPassword = async (
  phoneNumber: string,
  password: string,
): Promise<{
  success: true;
  token: string;
  message: string;
}> => {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      if (password === "1234") {
        resolve({
          success: true,
          token: "fake-jwt-token-xyz",
          message: "ورود موفقیت‌آمیز",
        });
      } else {
        reject(new Error("رمز عبور اشتباه است"));
      }
    }, 1500);
  });
};

export const sendOtp = async (phoneNumber: string) => {
  return new Promise<{ success: true; message: string }>((resolve) => {
    setTimeout(() => {
      resolve({ success: true, message: "کد با موفقیت ارسال شد" });
    }, 1000);
  });
};

/** کد تست mock: ۱۲۳۴ (هم‌خوان با رمز نمونهٔ لاگین) */
export const verifyOtp = async (
  phoneNumber: string,
  code: string,
): Promise<VerifyOtpResult> => {
  return new Promise((resolve) => {
    setTimeout(() => {
      if (code === "1234") {
        resolve({ success: true, token: "fake-jwt-token-xyz" });
      } else {
        resolve({ success: false, message: "کد وارد شده نامعتبر است" });
      }
    }, 1500);
  });
};

export const checkUserExists = async (phoneNumber: string) => {
  return new Promise<{ exists: boolean }>((resolve) => {
    setTimeout(() => {
      resolve({ exists: true });
    }, 1000);
  });
};

/** بعد از OTP یا فراموشی رمز؛ در API واقعی phone + رمز جدید ارسال شود */
export const resetPassword = async (
  phoneNumber: string,
  newPassword: string,
): Promise<{ success: true; token: string; message: string }> => {
  return new Promise((resolve) => {
    setTimeout(() => {
      resolve({
        success: true,
        token: "fake-jwt-token-xyz",
        message: "رمز عبور ثبت شد",
      });
    }, 1500);
  });
};

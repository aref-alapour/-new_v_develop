import Svg, { Path } from "react-native-svg";
import { IconProps } from "./IconProps";

/** آیکن گزارش / حباب تعجب (قرمز) */
export function Roport({
  size = 12,
  width,
  height,
  color = "#F21543",
  ...props
}: IconProps & { color?: string }) {
  const w = width ?? size;
  const h = height ?? (size * 11) / 12;

  return (
    <Svg width={w} height={h} viewBox="0 0 12 11" fill="none" {...props}>
      <Path
        d="M5.79412 2.85294V4.61765M5.79412 6.38235V6.38824M9.32353 0.5C9.79156 0.5 10.2404 0.685924 10.5714 1.01687C10.9023 1.34782 11.0882 1.79668 11.0882 2.26471V6.97059C11.0882 7.43862 10.9023 7.88748 10.5714 8.21842C10.2404 8.54937 9.79156 8.73529 9.32353 8.73529H6.38235L3.44118 10.5V8.73529H2.26471C1.79668 8.73529 1.34782 8.54937 1.01687 8.21842C0.685924 7.88748 0.5 7.43862 0.5 6.97059V2.26471C0.5 1.79668 0.685924 1.34782 1.01687 1.01687C1.34782 0.685924 1.79668 0.5 2.26471 0.5H9.32353Z"
        stroke={color}
        strokeWidth={1}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </Svg>
  );
}

import Svg, { Path } from "react-native-svg";
import { IconProps } from "./IconProps";

/** فلش بازگشت آبی (چپ) با خط افقی */
export function ArrowBlueLeft({
  size = 12,
  width,
  height,
  color = "#3F7FF5",
  ...props
}: IconProps & { color?: string }) {
  const w = width ?? size;
  const h = height ?? (size * 9) / 12;

  return (
    <Svg width={w} height={h} viewBox="0 0 12 9" fill="none" {...props}>
      <Path
        d="M4.125 7.25L1 4.125L4.125 1"
        stroke={color}
        strokeWidth={2}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <Path
        d="M11 7.875V6.625C11 5.96196 10.7366 5.32607 10.2678 4.85723C9.79893 4.38839 9.16304 4.125 8.5 4.125H1"
        stroke={color}
        strokeWidth={2}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </Svg>
  );
}

import Svg, { Path, Rect } from "react-native-svg";
import { IconProps } from "./IconProps";

export function ArrowBottom({ size = 18, width, height, ...props }: IconProps) {
  return (
    <Svg
      width={8}
      height={5}
      viewBox="0 0 8 5"
      fill="none"
      {...props}
    >
      <Path
        d="M7 1L4.70711 3.29289C4.31658 3.68342 3.68342 3.68342 3.29289 3.29289L1 1"
        stroke="#09192D"
        strokeWidth={2}
        strokeLinecap="round"
      />
    </Svg>
  );
}

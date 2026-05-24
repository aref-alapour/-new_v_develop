import Svg, { Circle } from "react-native-svg";
import { IconProps } from "./IconProps";

const ORANGE = "#FD7013";

/** یک دایره نارنجی واحد */
export function CircleOrange({ size = 14, width, height, ...props }: IconProps) {
  const w = width ?? size;
  const h = height ?? size;
  return (
    <Svg width={w} height={h} viewBox="0 0 14 14" fill="none" {...props}>
      <Circle cx="7" cy="7" r="5" fill={ORANGE} />
    </Svg>
  );
}

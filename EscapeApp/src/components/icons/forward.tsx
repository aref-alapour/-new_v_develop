import Svg, { ClipPath, Defs, G, Path, Rect } from "react-native-svg";
import { IconProps } from "./IconProps";

/** دکمه / آیکن اشتراک‌گذاری (مربع گرد آبی با فلش) */
export function Forward({
  size = 58,
  width,
  height,
  backgroundColor = "#2B7FFF",
  color = "#FFFFFF",
  ...props
}: IconProps & { backgroundColor?: string; color?: string }) {
  const w = width ?? size;
  const h = height ?? size;

  return (
    <Svg width={w} height={h} viewBox="0 0 58 58" fill="none" {...props}>
      <Defs>
        <ClipPath id="forwardIconClip">
          <Rect x={17} y={17} width={24} height={24} />
        </ClipPath>
      </Defs>
      <Rect width={58} height={58} rx={12} ry={12} fill={backgroundColor} />
      <G clipPath="url(#forwardIconClip)">
        <Path
          fillRule="evenodd"
          clipRule="evenodd"
          d="M38.6585 26.6012C39.8805 24.5957 38.427 22.0268 36.0778 22.0413L22.8617 22.1214C20.3304 22.136 18.9543 25.087 20.5702 27.0354L29.0036 37.212C30.5025 39.021 33.4038 38.4827 34.1555 36.2579L35.5496 32.1325L28.1922 28.7017C27.9518 28.5896 27.7658 28.3867 27.6751 28.1374C27.5844 27.8882 27.5964 27.6132 27.7085 27.3728C27.8206 27.1324 28.0235 26.9464 28.2728 26.8557C28.522 26.765 28.797 26.777 29.0374 26.8891L36.3939 30.3195L38.6585 26.6012Z"
          fill={color}
        />
      </G>
    </Svg>
  );
}

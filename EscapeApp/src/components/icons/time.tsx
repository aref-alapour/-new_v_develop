import Svg, { ClipPath, Defs, G, Path, Rect } from "react-native-svg";
import { IconProps } from "./IconProps";

export function TimeIcon({ size = 20, width, height, ...props }: IconProps) {
  return (
    <Svg
      width={width ?? size}
      height={height ?? size}
      viewBox="0 0 20 20"
      fill="none"
      {...props}
    >
      <G clipPath="url(#clip0_61829_20907)">
        <Path
          d="M4.69667 15.3033C5.92371 16.5303 7.53828 17.2939 9.26524 17.4639C10.9922 17.6339 12.7247 17.1998 14.1674 16.2356C15.6102 15.2714 16.6739 13.8367 17.1774 12.176C17.6809 10.5153 17.5929 8.73147 16.9285 7.1284C16.2641 5.52533 15.0643 4.20225 13.5337 3.38465C12.0031 2.56704 10.2363 2.3055 8.53443 2.6446C6.83258 2.98369 5.30097 3.90244 4.20061 5.24427C3.10025 6.5861 2.49924 8.26797 2.5 10.0033V11.6666"
          stroke="#0F172B"
          strokeWidth={2}
          strokeLinecap="round"
          strokeLinejoin="round"
        />
        <Path
          d="M0.832031 10.0013L2.4987 11.668L4.16536 10.0013M9.16536 6.66797V10.8346H13.332"
          stroke="#0F172B"
          strokeWidth={2}
          strokeLinecap="round"
          strokeLinejoin="round"
        />
      </G>
      <Defs>
        <ClipPath id="clip0_61829_20907">
          <Rect width={20} height={20} fill="white" />
        </ClipPath>
      </Defs>
    </Svg>
  );
}

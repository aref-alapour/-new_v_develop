import Svg, { Path } from 'react-native-svg';
import { IconProps } from './IconProps';

export function ChevronRightIcon({ size = 12, width, height, ...props }: IconProps) {
    const resolvedWidth = width ?? 6;
    const resolvedHeight = height ?? size;
    return (
      <Svg width={resolvedWidth} height={resolvedHeight} viewBox="0 0 5 8" fill="none" {...props}>
        <Path
          d="M3.58594 7L1.29304 4.70711C0.90252 4.31658 0.90252 3.68342 1.29304 3.29289L3.58594 1"
          stroke="#09192D"
          strokeWidth={2}
          strokeLinecap="round"
        />
      </Svg>
    );
}
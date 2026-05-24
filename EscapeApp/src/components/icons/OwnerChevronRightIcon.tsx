import Svg, { Path } from 'react-native-svg';
import { IconProps } from './IconProps';

export function OwnerChevronRightIcon({ size = 12, width, height, ...props }: IconProps) {
  return (
    <Svg width={width ?? size * (7 / 12)} height={height ?? size} viewBox="0 0 7 12" fill="none" {...props}>
      <Path
        d="M1.5 1.5L4.90945 5.33564C5.24624 5.71452 5.24624 6.28548 4.90945 6.66436L1.5 10.5"
        stroke="#4E5C6D"
        strokeWidth={3}
        strokeLinecap="round"
      />
    </Svg>
  );
}

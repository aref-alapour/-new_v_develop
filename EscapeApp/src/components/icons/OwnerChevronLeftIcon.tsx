import Svg, { Path } from 'react-native-svg';
import { IconProps } from './IconProps';

export function OwnerChevronLeftIcon({ size = 12, width, height, ...props }: IconProps) {
  return (
    <Svg width={width ?? size * (7 / 12)} height={height ?? size} viewBox="0 0 7 12" fill="none" {...props}>
      <Path
        d="M5.16016 1.5L1.7507 5.33564C1.41391 5.71452 1.41391 6.28548 1.7507 6.66436L5.16016 10.5"
        stroke="#4E5C6D"
        strokeWidth={3}
        strokeLinecap="round"
      />
    </Svg>
  );
}

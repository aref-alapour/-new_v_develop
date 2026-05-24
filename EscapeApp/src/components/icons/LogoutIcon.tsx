import Svg, { Path } from 'react-native-svg';
import { IconProps } from './IconProps';

export function LogoutIcon({ size = 22, width, height, ...props }: IconProps) {
  return (
    <Svg width={width ?? size} height={height ?? size} viewBox="0 0 22 22" fill="none" {...props}>
      <Path
        d="M13.75 2.75L17.4167 2.75C17.9029 2.75 18.3692 2.94316 18.713 3.28697C19.0568 3.63079 19.25 4.0971 19.25 4.58333L19.25 17.4167C19.25 17.9029 19.0568 18.3692 18.713 18.713C18.3692 19.0568 17.9029 19.25 17.4167 19.25L13.75 19.25M7.33333 6.41667L2.75 11M2.75 11L7.33333 15.5833M2.75 11L13.75 11"
        stroke="#09192D"
        strokeWidth={2}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </Svg>
  );
}

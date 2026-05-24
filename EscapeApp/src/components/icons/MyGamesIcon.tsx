import Svg, { Path } from 'react-native-svg';
import { IconProps } from './IconProps';

export function MyGamesIcon({ size = 22, width, height, ...props }: IconProps) {
  return (
    <Svg width={width ?? size} height={height ?? size} viewBox="0 0 22 22" fill="none" {...props}>
      <Path
        d="M15.5833 14.6667V7.33333C15.5833 5.17275 15.5833 4.092 14.9123 3.421C14.2413 2.75 13.1606 2.75 11 2.75H7.33333C5.17275 2.75 4.092 2.75 3.421 3.421C2.75 4.092 2.75 5.17275 2.75 7.33333V14.6667C2.75 16.8273 2.75 17.9071 3.421 18.579C4.092 19.2509 5.17275 19.25 7.33333 19.25H11C13.1606 19.25 14.2413 19.25 14.9123 18.579C15.5833 17.9071 15.5833 16.8273 15.5833 14.6667Z"
        fill="#09192D"
        stroke="#09192D"
        strokeWidth={1.5}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <Path
        d="M10.084 19.25H15.584C17.3128 19.25 18.1763 19.25 18.7135 18.7128C19.2506 18.1757 19.2506 17.3122 19.2506 15.5833V9.16667C19.2506 7.43783 19.2506 6.57433 18.7135 6.03717C18.1763 5.5 17.3128 5.5 15.584 5.5M11.9173 10.0833V11.9167"
        stroke="#09192D"
        strokeWidth={1.5}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <Path d="M11.918 10.082V11.9154" stroke="white" strokeWidth={1.5} strokeLinecap="round" strokeLinejoin="round" />
    </Svg>
  );
}

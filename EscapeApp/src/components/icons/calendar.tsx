import Svg, { Path, Ellipse } from "react-native-svg";
import { IconProps } from "./IconProps";

export function Calender({
  size = 12,
  width,
  height,
  ...props
}: IconProps) {
  return (
    <Svg width={17} height={19} viewBox="0 0 17 19" fill="none">
      <Path
        d="M12.8721 1.62891C15.0812 1.62891 16.8721 3.41977 16.8721 5.62891V14.501C16.8721 16.7101 15.0812 18.501 12.8721 18.501H4C1.79086 18.501 0 16.7101 0 14.501V5.62891C0 3.41977 1.79086 1.62891 4 1.62891H12.8721ZM4.05469 2.68359C2.39783 2.68359 1.05469 4.02674 1.05469 5.68359V6.90137H15.8174V5.68359C15.8174 4.02674 14.4742 2.68359 12.8174 2.68359H4.05469Z"
        fill="#FC6F13"
      />

      <Path
        d="M5.625 3.875L5.625 0.500555"
        stroke="#FC6F13"
        strokeLinecap="round"
      />

      <Path
        d="M11.25 3.875L11.25 0.500555"
        stroke="#FC6F13"
        strokeLinecap="round"
      />

      <Ellipse
        cx={3.93741}
        cy={10.0624}
        rx={0.562407}
        ry={0.562408}
        fill="white"
      />
      <Ellipse
        cx={3.93741}
        cy={13.4335}
        rx={0.562407}
        ry={0.562408}
        fill="white"
      />
      <Ellipse
        cx={8.43741}
        cy={10.0624}
        rx={0.562407}
        ry={0.562408}
        fill="white"
      />
      <Ellipse
        cx={8.43741}
        cy={13.4335}
        rx={0.562407}
        ry={0.562408}
        fill="white"
      />
      <Ellipse
        cx={12.9374}
        cy={10.0624}
        rx={0.562407}
        ry={0.562408}
        fill="white"
      />
      <Ellipse
        cx={12.9374}
        cy={13.4335}
        rx={0.562407}
        ry={0.562408}
        fill="white"
      />
    </Svg>
  );
}

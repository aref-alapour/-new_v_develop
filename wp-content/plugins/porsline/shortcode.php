<?php
add_shortcode("porsline", function($attrs){
    if(!isset($attrs[0])){ return ""; }
    $data = prs_get_form_data(esc_attr($attrs[0]));
    $url = esc_url(prs_endpoint().'/s/'.$attrs[0]); // Escaping URL
    $icons = prs_geticons();
    $icon = '';
    if($data['type'] == "widget" && isset($data['icon']) && isset($icons[esc_html($data['icon'])])){
      $icon = file_get_contents($icons[esc_html($data['icon'])]);
    }
    if($data['type'] == "full"){
		echo '<script> document.addEventListener("DOMContentLoaded", function() { document.body.innerHTML += `<iframe src="'.$url.'" class="prsline full"></iframe>` }); </script>';
	} else if($data['type'] == "iframe"){
        if(empty($data['ifm_width']) || $data['ifm_width'] == "%"){ $data['ifm_width'] = '100%'; }
        if(empty($data['ifm_height']) || $data['ifm_height'] == "auto"){ $data['ifm_height'] = '480px'; }
        return '<iframe src="'.$url.'" '.($data['type'] == "full" ? ' class="prsline full"' : '').' style="margin:8px;border:'.esc_attr($data['brd_size']).'px solid '.esc_attr($data['brd_clr']).';width:'.esc_attr($data['ifm_width']).';height:'.esc_attr($data['ifm_height']).';"></iframe>';
    } else {
        $code = ' open("'.$url.'","PorsLine", "popup") ';
        if($data['type'] == 'newtab'){ 
            $code = ' open("'.$url.'"," _blank") ';
        }
        if($data['type'] == "popup" || $data['type'] == "slidein" || $data['type'] == "sidetab" || $data['type'] == "widget"){
            $code = ' document.body.insertAdjacentHTML("beforeend",\'<div class="prsline_popup'.($data['type'] == "slidein" ? ' slidein hide' : "").($data['type'] == "sidetab" || $data['type'] == "widget" ? " sidetab" : "").($data['type'] == "full" ? " full" : "").'"><div class="popup-wrapper"><iframe'.($data['type'] == "full" ? ' class="full"' : "").' src="'.$url.'"></iframe><button id="prscls"><span class="dashicons dashicons-no-alt"></span></button></div><div class="loading"></div></div>\') ';
            $code .= "\n".' var iframe = document.querySelector(".prsline_popup iframe");
            iframe.onload = function() { document.querySelector(".prsline_popup .loading").remove(); document.querySelector(".prsline_popup").classList.remove("hide"); }; ';
        }

        return '<div style="text-align:'.esc_attr($data['btn_align']).';margin:8px;">
            <button id="prslbtn" class="'.$data['type'].($data['type'] == "widget" ? " icon ".$data['icon'] : "").'" style="color:'.esc_attr($data[$data['type'] == "widget" ? 'icon_color' : 'btn_txt_clr']).';padding:8px;background:'.esc_attr($data['btn_bg']).';border:'.esc_attr($data['btn_brd']).'px '.esc_attr($data['btn_brd_stl']).' '.esc_attr($data['btn_brd_clr']).'">
              '.($data['type'] != "widget" ? esc_html($data['btn_text']) : "").'
              '.$icon.'
            </button>
        </div>
        <script>
            document.getElementById("prslbtn").addEventListener("click",function(e){
                '.$code.'
            });
        </script>';
    }
});

add_action("wp_footer",function(){
    $pdir = plugin_dir_url(__FILE__);
    echo '<style>
        .prsline_popup {
        position: fixed;
        top:0px;
        left: 0px;
        width:100%;
        height: 100%;
        background:rgba(0,0,0,0.5);
        z-index: 999999;
        display:grid;
        grid-template-columns: repeat(1);
        justify-content:center;
        align-items:center;
      }
.prsline_popup:before {
  position: absolute;
  width: 100%;
  height: 100%;
  background: #333333ab;
  z-index:-1;
  content: "";
}
iframe.prsline.full {
  background:#fff url("'.$pdir.'assets/loading.svg") no-repeat center center;
  background-size:5%;
  width:100vw !important;
  height:100vh !important;
  position: fixed;
  margin:0px !important;;
  left:0px;
  top:0px;
  right: 0px;
  max-width: 100vw;
  bottom:0px;
  z-index: 999999;
}
      .prsline_popup iframe {
        width:50vw;
        height: 65vh;
      }
.prsline_popup button {
	width: max-content;
	margin: 8px auto;
	background: #fff;
	border: none;
	padding: 8px 15px;
	border-radius: 8px;
	position: absolute;
	top: 15px;
	right: 15px;
	z-index: 11111;
}
#prslbtn.sidetab {
    position: fixed;
    right: 0;
    top: 45%;
    transform: rotate(270deg) translateY(-49%) translateX(50%);
    transform-origin: center right;
    margin: 0px;
    z-index: 100;
    box-sizing: border-box;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}

.prsline_popup iframe.full {
    	width: 100vw;
    	height: 100vh;
}	
	.prsline_popup.slidein , .prsline_popup.sidetab {
    background:none;
}
.prsline_popup.slidein iframe {
    width:60vw;
    height:100vh;
    position:absolute;
    right:0px;
    top:0px;
    transition:all 0.2s ease-in;
}
.prsline_popup.slidein.hide iframe {
    right:-60vw;
}

#prslbtn svg {
  width: 70%;
  height: auto;
}
.prsline_popup .popup-wrapper {
    position: relative;
}

.prsline_popup.slidein .popup-wrapper, .prsline_popup.sidetab .popup-wrapper {
    width: 100vw;
    height: 100vh;
}

.prsline_popup.sidetab iframe {
    width:40vw;
    height:70vh;
    position:absolute;
    right:2vw;
    top:15vh;
    border-radius:10px;
}

.prsline_popup .loading {
  width: 60px;
  aspect-ratio: 4;
  background: radial-gradient(circle closest-side,#000 90%,#0000) 0/calc(100%/3) 100% space;
  clip-path: inset(0 100% 0 0);
  animation: l1 1s steps(4) infinite;
}

button#prscls {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: hsla(0, 0%, 100%, .5);
    border-radius: 4px;
    margin: 0;
    top: -2.5rem;
    right: 0;
}

.prsline_popup.slidein button#prscls, .prsline_popup.sidetab button#prscls {
    background-color: #3e434d;
    border-radius: 100%;
    border: 1px solid #fff;
}

.prsline_popup #prscls span.dashicons, .prsline_popup.sidetab #prscls span.dashicons {
    color: #fff;
}

#prscls span.dashicons {
    color: #3e434d;
    font-size: 28px;
    width: auto;
    height: auto;
}

#prslbtn.widget {
  position: fixed;
  bottom:30px;
  right: 30px;
  border-radius: 100%;
  width: 58px;
  height: 58px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index:100;
}

@keyframes l1 {to{clip-path: inset(0 -34% 0 0)}}
@media only screen and (max-width: 950px) {
  .prsline_popup iframe {
    width:90vw;
  }
  .prsline_popup.slidein iframe 
}
@media only screen and (max-width: 800px) {
  .prsline_popup.slidein iframe {
  	width:50vw;
  }
  .prsline_popup.slidein.hide iframe {
    right:-55vw;
	}
	.prsline_popup.sidetab iframe {
		width:50%;
	}
	
}
@media only screen and (max-width: 500px) {
  .prsline_popup.slidein iframe {
  	width:80vw;
  }
  .prsline_popup.slidein.hide iframe {
    right:-85vw;
	}
	.prsline_popup.sidetab iframe {
		width:90%;
	}
}
    </style>
    ';
    echo '
		<script>
		document.addEventListener("click", function(evnt) {
			const target = evnt.target;
			const prsclsElement = document.getElementById("prscls");

			if (
				target.id === "prscls" ||
				(prsclsElement && prsclsElement.contains(target) && target.matches("span.dashicons"))
			) {
				const popup = document.querySelector(".prsline_popup");
				if (popup) {
					if (popup.classList.contains("slidein")) {
						popup.classList.add("hide");
						const iframe = popup.querySelector("iframe");
						iframe.addEventListener("transitionend", function handleTransitionEnd(event) {
							if (event.propertyName === "right") {
								popup.remove();
								iframe.removeEventListener("transitionend", handleTransitionEnd);
							}
						});
					} else {
						popup.remove();
					}
				}
			}
		});

		function updatePrsclsPosition() {
			const popup = document.querySelector(".prsline_popup.slidein, .prsline_popup.sidetab");
			const closeButton = document.getElementById("prscls");

			if (!popup || !closeButton) return;

			const iframe = popup.querySelector("iframe");
			if (!iframe) return;

			const iframeRect = iframe.getBoundingClientRect();
			const popupRect = popup.getBoundingClientRect();

			const iframeTop = iframe.offsetTop;
			const iframeLeft = iframe.offsetLeft;
			const iframeWidth = iframe.offsetWidth;

			if (popup.classList.contains("slidein")) {
				closeButton.style.position = "absolute";
				closeButton.style.top = "0.5rem";
				closeButton.style.right = `${iframeWidth - 18}px`;
			} else if (popup.classList.contains("sidetab")) {
				closeButton.style.position = "absolute";
				closeButton.style.top = `${iframeTop - 18}px`;
				closeButton.style.right = `${iframeWidth - 18}px`;
			}
		}

		// Run initially
		updatePrsclsPosition();

		// Update on window resize
		window.addEventListener("resize", updatePrsclsPosition);

		// Observe iframe resizing
		const iframeObserver = () => {
			const popup = document.querySelector(".prsline_popup.slidein");
			if (!popup) return;
			const iframe = popup.querySelector("iframe");
			if (!iframe) return;

			if ("ResizeObserver" in window) {
				const ro = new ResizeObserver(updatePrsclsPosition);
				ro.observe(iframe);
			}
		};

		// Observe DOM mutations (like class changes .hide → visible)
		const mutationObserver = new MutationObserver(updatePrsclsPosition);
		mutationObserver.observe(document.body, {
			subtree: true,
			attributes: true,
			attributeFilter: ["class"],
			childList: true,
		});

		iframeObserver();
		</script>';
});
?>
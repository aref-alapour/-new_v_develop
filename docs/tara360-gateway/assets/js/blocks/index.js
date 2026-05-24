const { createElement, useEffect } = window.wp.element;
const { decodeEntities } = window.wp.htmlEntities;
const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const taraSettings = window.wc.wcSettings.getSetting("tara360_data", {});
const taraLabel = decodeEntities(taraSettings.title || "");

const Content = (props) => {
  const { eventRegistration, emitResponse } = props;
  const { onPaymentProcessing } = eventRegistration;

  function faToEnDigits(str) {
    return String(str)
      .replace(/[۰-۹]/g, (d) => "۰۱۲۳۴۵۶۷۸۹".indexOf(d)) // Persian
      .replace(/[٠-٩]/g, (d) => "٠١٢٣٤٥٦٧٨٩".indexOf(d)); // Arabic-Indic
  }

  useEffect(() => {
    const unsubscribe = onPaymentProcessing(() => {
      const mobile = faToEnDigits(
        document.getElementById("tara360-mobile")?.value || ""
      );

      if (/^\d{11}$/.test(mobile)) {
        return {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              tara360_MobileNo: mobile,
            },
          },
        };
      }

      return {
        type: emitResponse.responseTypes.ERROR,
        message: "شماره موبایل باید 11 رقم باشد.",
      };
    });

    return () => unsubscribe();
  }, [onPaymentProcessing, emitResponse]);

  return createElement(
    "div",
    null,
    decodeEntities(taraSettings.description || ""),
    createElement(
      "div",
      { style: { marginTop: "10px" } },
      createElement("label", { htmlFor: "tara360-mobile" }, "شماره موبایل"),
      createElement("input", {
        id: "tara360-mobile",
        name: "tara360_MobileNo",
        type: "tel",
        required: true,
        minLength: 11,
        maxLength: 11,
        style: {
          width: "100%",
          padding: "8px",
          marginTop: "4px",
          border: "1px solid #ccc",
          borderRadius: "4px",
          boxSizing: "border-box",
        },
      })
    )
  );
};

const Icon = () => {
  return taraSettings.icon
    ? createElement("img", {
        src: taraSettings.icon,
        style: { marginLeft: "10px" },
      })
    : null;
};

const Label = () => {
  return createElement(
    "span",
    { style: { display: "flex", alignItems: "center", gap: "5px" } },
    taraLabel,
    createElement(Icon)
  );
};

const Tara360BlockGateway = {
  name: "tara360",
  label: createElement(Label),
  content: createElement(Content),
  edit: createElement(Content),
  canMakePayment: () => true,
  ariaLabel: taraLabel,
  supports: {
    features: taraSettings.supports || ["products"],
  },
};

registerPaymentMethod(Tara360BlockGateway);

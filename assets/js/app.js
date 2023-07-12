document.addEventListener("DOMContentLoaded", function () {
  let productField = document.querySelector("#wsk-custom-product"),
    variationField = document.querySelector("#wsk-variation-field"),
    variationFieldWrapper = variationField.closest("tr"),
    preloader = document.querySelector("#preloader"),
    regularPriceField = document.querySelector(
      'input[name="wsk_custom_regular_price"]'
    ),
    salesPriceField = document.querySelector(
      'input[name="wsk_custom_sales_price"]'
    );

  productField.addEventListener("change", function () {
    let selectedProduct = productField.value;
    if (selectedProduct !== "") {
      showVariations(selectedProduct);
      regularPriceField.required = true;
      salesPriceField.required = true;
    } else {
      hideVariations();
      regularPriceField.required = false;
      salesPriceField.required = false;
    }
  });

  if (productField.value !== "")
    productField.dispatchEvent(new Event("change"));

  function showVariations(productID) {
    preloader.classList.remove("hide");
    fetch(ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "action=wsk_load_variations&product_id=" + productID,
    })
      .then((response) => {
        if (response.ok) {
          return response.json();
        } else {
          throw new Error("Error loading variations");
        }
      })
      .then((data) => {
        preloader.classList.add("hide");
        if (data.success) {
          let variationsHTML = "",
            variations = data.data;

          if (variations.length > 0)
            variationFieldWrapper.style.display = "table-row";
          if (variations.length < 1) hideVariations();

          variations.forEach((variation) => {
            variationsHTML +=
              '<label><input type="checkbox" name="wsk_custom_variation[]" value="' +
              variation.variation_id +
              '">' +
              variation.attributes +
              "</label><br>";
          });
          variationField.innerHTML = variationsHTML;
        } else {
          variationField.innerHTML = "<p>" + data.data + "</p>";
        }
      })
      .catch((error) => {
        console.error(error);
      });
  }

  function hideVariations() {
    variationFieldWrapper.style.display = "none";
  }

  // function updatePrices(productID, regularPrice, salesPrice, variations) {
  //   let formData = new FormData();
  //   formData.append("wsk_custom_product", productID);
  //   formData.append("wsk_custom_regular_price", regularPrice);
  //   formData.append("wsk_custom_sales_price", salesPrice);
  //   variations.forEach((variation) => {
  //     formData.append("wsk_custom_variation[]", variation);
  //   });

  //   fetch(window.location.href, {
  //     method: "POST",
  //     body: formData,
  //   })
  //     .then((response) => {
  //       if (response.ok) {
  //         return response.json();
  //       } else {
  //         throw new Error("Error updating prices");
  //       }
  //     })
  //     .then((data) => {
  //       if (data.success) {
  //         alert(data.data);
  //         window.location.reload();
  //       } else {
  //         alert(data.data);
  //       }
  //     })
  //     .catch((error) => {
  //       console.error(error);
  //     });
  // }
});

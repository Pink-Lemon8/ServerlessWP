function ShowSubMenu(ID) {
  jQuery("#" + ID).slideToggle("fast");
}

function sideToggle() {
  jQuery("#nav-mobile-side").toggle("fast");
}

function scrollTop(time = 0) {
  jQuery("html, body").animate(
    {
      scrollTop: 0,
    },
    time
  );
}

function alertRemove(id="alert", sec = 0) {
  setTimeout(function () {
    if (document.getElementById(id))
      document.getElementById(id).remove();
  }, sec);

}
alertRemove('alert', 6000);

function loading(is_show){
  if(is_show)
    jQuery("div#pl_loading").fadeIn("fast");
  else
    jQuery("div#pl_loading").fadeOut("slow");
}

function login_loading(){
  var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
  if(jQuery("input#email").val() != "" && jQuery("input#password").val() != "" && jQuery("input#email").val().match(validRegex))
    loading(true);
}

function copyShippingAddressToBilling() {
  document.getElementById("billingStreet").value =
    document.getElementById("shippingStreet").value;
  document.getElementById("billingApt").value =
    document.getElementById("shippingApt").value;
  document.getElementById("billingCity").value =
    document.getElementById("shippingCity").value;
  document.getElementById("billingState").value =
    document.getElementById("shippingState").value;
  document.getElementById("billingCountry").value =
    document.getElementById("shippingCountry").value;
  document.getElementById("billingZip").value =
    document.getElementById("shippingZip").value;
  document.getElementById("billing_phoneAreaCode").value =
    document.getElementById("shipping_phoneAreaCode").value;
  document.getElementById("billing_phone").value =
    document.getElementById("shipping_phone").value;
}


function displayErrorAfter(inputElement, message) {
  removeError(inputElement);

  var errorHTML = createErrorHTML(message);
  inputElement.insertAdjacentHTML("afterend", errorHTML);
}

function removeError(inputElement) {
  var errorElement = inputElement.nextElementSibling;

  if (errorElement && errorElement.classList.contains("error")) {
    errorElement.remove();
  }
}

function createErrorHTML(message) {
  return `<div class="rounded-md bg-red-50 p-4 error">
              <div class="flex">
                  <div class="flex-shrink-0">
                      <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                      </svg>
                  </div>
                  <div class="ml-3">
                      <h3 class="text-sm font-medium text-red-800">${message}</h3>
                  </div>
              </div>
          </div>`;
}


function setSteps(steps, current) {

  const body = document.getElementsByTagName("body")[0];
  const colors_data = body.getAttribute("color-data");
  // MAIN_COLOR,MAIN_COLOR_HOVER,MAIN_COLOR_FOCUS,MAIN_COLOR_ACTIVE,MAIN_COLOR_MOBILE
  const colors = colors_data.split(",");
  let result = "";
  const lineEmptyclass = "pr-8 sm:pr-20";
  const typeEmpty =
    '<li class="relative {0}"><div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="h-0.5 w-full bg-gray-200"></div></div><a href="#" class="group relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white hover:border-gray-400"><span class="h-2.5 w-2.5 rounded-full bg-transparent group-hover:bg-gray-300" aria-hidden="true"></span></a></li>';
  const finished =
    '<li class="relative {0}"><div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="h-0.5 w-full bg-[' + colors[0] + ']"></div></div><a href="#" class="relative flex h-8 w-8 items-center justify-center rounded-full bg-[' + colors[0] + '] hover:bg-[' + colors[1] + ']"><svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg></a></li>';
  const currentStep =
    ' <li class="relative {0}"><div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="h-0.5 w-full bg-gray-200"></div></div><a href="#" class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-[' + colors[0] + '] bg-white" aria-current="step"><span class="h-2.5 w-2.5 rounded-full bg-[' + colors[0] + ']" aria-hidden="true"></span></a></li>';

  for (let index = 1; index <= steps; index++) {
    if (index < current) {
      result += finished.replace("{0}", lineEmptyclass);
    } else if (index == current) {
      if (current == steps) result += currentStep.replace("{0}", "");
      else result += currentStep.replace("{0}", lineEmptyclass);
    } else if (index != steps) {
      result += typeEmpty.replace("{0}", lineEmptyclass);
    } else {
      result += typeEmpty.replace("{0}", "");
    }
  }
  jQuery("nav#Progress .steps").html(result);
  jQuery("div h3.currentStep").html(
    jQuery("form#multi-step-form div#part" + current + " h2").text()
  );
}

function getErrorHTML(errorMessage) {
  return `<div class="rounded-md bg-red-50 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">${errorMessage}</h3>
              </div>
            </div>
          </div>`;
}

// account create steps
jQuery(document).ready(function () {

  var currentStep = 1;
  if (document.getElementById("back1"))
    document.getElementById("back1").addEventListener("click", function () {
      jQuery("#part1").slideToggle("fast");
      jQuery("#part2").slideToggle("fast");
      scrollTop(100);
      currentStep = 1;
      setSteps(3, currentStep);
    });
  if (document.getElementById("back2"))
    document.getElementById("back2").addEventListener("click", function () {
      jQuery("#part2").slideToggle("fast");
      jQuery("#part3").slideToggle("fast");
      scrollTop(100);
      currentStep = 2;
      setSteps(3, currentStep);
    });

  if (document.getElementById("next1"))
    document.getElementById("next1").addEventListener("click", function (e) {
      const requiredFields = [
        "firstName",
        "lastName",
        "email",
        "confirm_email",
        "password",
        "confirmPassword",
        "Areacode",
        "Phonenumber",
      ];
      let errors = [];

      requiredFields.forEach((field) => {
        const input = document.getElementById(field);
        const existingErrorDiv = document.querySelector(
          "#" + field + " + .errorDiv"
        );
        if (existingErrorDiv) {
          existingErrorDiv.remove();
        }

        // If field is empty, create a new errorDiv
        if (!input.value.trim()) {
          errors.push(input.placeholder + " is not optional");
          const errorDiv = document.createElement("div");
          errorDiv.classList.add("errorDiv"); // Add a class to the error div for easy removal
          errorDiv.innerHTML = getErrorHTML(input.placeholder + " is not optional");
          input.parentNode.insertBefore(errorDiv, input.nextSibling);
        }
      });



      // Extra checks
      const email = document.getElementById("email").value;
      const confirmEmail = document.getElementById("confirm_email").value;
      const password = document.getElementById("password").value;
      const confirmPassword = document.getElementById("confirmPassword").value;
      const areaCode = document.getElementById("Areacode").value;
      const phoneNumber = document.getElementById("Phonenumber").value;

      const fieldChecks = [
        {
          condition: email !== confirmEmail,
          field: "confirm_email",
          message: "Email addresses do not match",
        },
        {
          condition: password !== confirmPassword,
          field: "confirmPassword",
          message: "Passwords do not match",
        },
        {
          condition: areaCode.length !== 3,
          field: "Areacode",
          message: "Area code must be 3 digits",
        },
        {
          condition: phoneNumber.length !== 7,
          field: "Phonenumber",
          message: "Phone number must be 7 digits",
        },
      ];

      fieldChecks.forEach((check) => {
        // Check if errorDiv already exists, if yes remove it
        const existingErrorDiv = document.querySelector(
          "#" + check.field + " + .errorDiv"
        );
        if (existingErrorDiv) {
          existingErrorDiv.remove();
        }

        if (check.condition) {
          errors.push(check.message);

          const errorDiv = document.createElement("div");
          errorDiv.classList.add("errorDiv");
          errorDiv.innerHTML = getErrorHTML(check.message);
          document
            .getElementById(check.field)
            .parentNode.insertBefore(
              errorDiv,
              document.getElementById(check.field).nextSibling
            );
        }
      });

      if (errors.length > 0) {
        e.preventDefault(); // prevent the page from navigating to the next step
        return;
      }

      if (email !== confirmEmail) {
        errors.push("Email addresses do not match");
        const errorDiv = document.createElement("div");
        errorDiv.classList.add("errorDiv");
        errorDiv.innerHTML = getErrorHTML("Email addresses do not match");
        document
          .getElementById("confirm_email")
          .parentNode.insertBefore(
            errorDiv,
            document.getElementById("confirm_email").nextSibling
          );
      }

      if (password !== confirmPassword) {
        errors.push("Passwords do not match");
        const errorDiv = document.createElement("div");
        errorDiv.classList.add("errorDiv");
        errorDiv.innerHTML = getErrorHTML("Passwords do not match");
        document
          .getElementById("confirmPassword")
          .parentNode.insertBefore(
            errorDiv,
            document.getElementById("confirmPassword").nextSibling
          );
      }

      if (areaCode.length !== 3) {
        errors.push("Area code must be 3 digits");
        const errorDiv = document.createElement("div");
        errorDiv.classList.add("errorDiv");
        errorDiv.innerHTML = getErrorHTML("Area code must be 3 digits");
        document
          .getElementById("Areacode")
          .parentNode.insertBefore(
            errorDiv,
            document.getElementById("Areacode").nextSibling
          );
      }

      if (phoneNumber.length !== 7) {
        errors.push("Phone number must be 7 digits");
        const errorDiv = document.createElement("div");
        errorDiv.classList.add("errorDiv");
        errorDiv.innerHTML = getErrorHTML("Phone number must be 7 digits");
        document
          .getElementById("Phonenumber")
          .parentNode.insertBefore(
            errorDiv,
            document.getElementById("Phonenumber").nextSibling
          );
      }

      // If there are no errors, then proceed with the form navigation
      jQuery("#part1").slideToggle("fast");
      jQuery("#part2").slideToggle("fast");
      scrollTop(100);
      currentStep = 2;
      setSteps(3, currentStep);
    });

  if (document.getElementById("next2"))
    document.getElementById("next2").addEventListener("click", function (e) {
      const shippingFields = [
        "shippingStreet",
        "shippingCity",
        "shippingState",
        "shippingCountry",
        "shippingZip",
        "shipping_phoneAreaCode",
        "shipping_phone",
      ];

      const billingFields = [
        "billingStreet",
        "billingCity",
        "billingState",
        "billingCountry",
        "billingZip",
        "billing_phoneAreaCode",
        "billing_phone",
      ];

      let sameAsShipping = document.getElementById("sameAsShipping").checked;
      let errors = [];

      const fieldCheck = (fieldArray) => {
        sameAsShipping = document.getElementById("sameAsShipping").checked;
        fieldArray.forEach((field) => {
          const input = document.getElementById(field);

          // Check if errorDiv already exists, if yes remove it
          const existingErrorDiv = document.querySelector(
            "#" + field + " + .errorDiv"
          );
          if (existingErrorDiv) {
            existingErrorDiv.remove();
          }

          // If field is empty, create a new errorDiv
          if (!input.value.trim()) {
            errors.push(input.placeholder + " is not optional");

            const errorDiv = document.createElement("div");
            errorDiv.classList.add("errorDiv"); // Add a class to the error div for easy removal
            errorDiv.innerHTML = getErrorHTML(input.placeholder + " is not optional");
            input.parentNode.insertBefore(errorDiv, input.nextSibling);
          }
        });
      };

      // Check fields
      fieldCheck(shippingFields);
      if (!sameAsShipping) {
        fieldCheck(billingFields);
      }

      const fieldChecks = [
        {
          fields: sameAsShipping ? ["shippingZip"] :  ["shippingZip", "billingZip"],
          condition: (field) => document.getElementById(field).value.length !== 5,
          message: (field) => "Zip code must be 5 digits",
        },

        {
          fields: sameAsShipping ? ["shipping_phone"] :  ["shipping_phone", "billing_phone"],
          condition: (field) => document.getElementById(field).value.length !== 7,
          message: (field) => "Phone number must be 7 digits",
        },

        {
          fields: sameAsShipping ? ["shipping_phoneAreaCode"] :  ["shipping_phoneAreaCode", "billing_phoneAreaCode"],
          condition: (field) => document.getElementById(field).value.length !== 3,
          message: (field) => "Area code must be 3 digits",
        },
        // more conditions can be added here
      ];

      fieldChecks.forEach((check) => {
        check.fields.forEach((field) => {
        
          const existingErrorDiv = document.querySelector(
            "#" + field + " + .errorDiv"
          );
          if (existingErrorDiv) {
            existingErrorDiv.remove();
          }

          if (check.condition(field)) {
            errors.push(check.message(field));

            const errorDiv = document.createElement("div");
            errorDiv.classList.add("errorDiv");
            errorDiv.innerHTML = getErrorHTML(check.message(field));
            document
              .getElementById(field)
              .parentNode.insertBefore(
                errorDiv,
                document.getElementById(field).nextSibling
              );
          }

        });
      });

      // TODO: Check that zip code matches state, likely require an API call or extensive database

      if (errors.length > 0) {
        e.preventDefault(); // prevent the page from navigating to the next step
        return;
      }

      // If there are no errors, then proceed with the form navigation
      jQuery("#part2").slideToggle("fast");
      jQuery("#part3").slideToggle("fast");
      scrollTop(100);
      currentStep = 3;
      setSteps(3, currentStep);
    });


  setSteps(3, currentStep);

  if (document.getElementById("#multi-step-form"))
    jQuery("#multi-step-form").on("change blur keydown", "#password", (ev) => {
      const pwdInput = jQuery(ev.target);
      const pwd = pwdInput.val();
      const passwordStrength = zxcvbn(pwd);
      const progressBar = pwdInput.siblings(".progress");

      switch (passwordStrength.score) {
        case 0:
          progressBar.removeClass("success warning alert").addClass("secondary");
          progressBar.attr({ "aria-valuenow": 0 });
          progressBar.find(".progress-meter").css({ width: "0%" });
          progressBar.find(".progress-meter-text").text("");
          break;
        case 1:
          progressBar.removeClass("success warning alert").addClass("secondary");
          progressBar.attr({ "aria-valuenow": 25 });
          progressBar.find(".progress-meter").css({ width: "25%" });
          progressBar.find(".progress-meter-text").text("weak");
          break;
        case 2:
          progressBar.removeClass("secondary success warning").addClass("alert");
          progressBar.attr({ "aria-valuenow": 50 });
          progressBar.find(".progress-meter").css({ width: "50%" });
          progressBar.find(".progress-meter-text").text("weak");
          break;
        case 3:
          progressBar.removeClass("secondary success alert").addClass("warning");
          progressBar.attr({ "aria-valuenow": 75 });
          progressBar.find(".progress-meter").css({ width: "75%" });
          progressBar.find(".progress-meter-text").text("good");
          break;
        case 4:
          progressBar.removeClass("secondary warning alert").addClass("success");
          progressBar.attr({ "aria-valuenow": 100 });
          progressBar.find(".progress-meter").css({ width: "100%" });
          progressBar.find(".progress-meter-text").text("strong");
          break;
        default:
          break;
      }
    });

});

document.addEventListener("DOMContentLoaded", (event) => {
  const sameAsShipping = document.getElementById("sameAsShipping");
  if (sameAsShipping)
    sameAsShipping.addEventListener("change", function () {
      copyShippingAddressToBilling();
      if (this.checked) {
        jQuery("#billingAddress").slideToggle('fast');
        document.getElementById("billing_useShippingAddress").value = 'yes';
      } else {
        jQuery("#billingAddress").slideToggle('fast');
        document.getElementById("billing_useShippingAddress").value = 'no';
      }
    });

  let shippingInputs = document.querySelectorAll(
    "#shippingStreet, #shippingApt, #shippingCity, #shippingState, #shippingCountry, #shippingZip"
  );

  shippingInputs.forEach((input) => {
    input.addEventListener("change", function () {
      if (document.getElementById("sameAsShipping").checked) {
        copyShippingAddressToBilling();
      }
    });
  });
});

const multi_step_form = document.querySelector("#multi-step-form");
if (multi_step_form)
  multi_step_form.addEventListener("submit", function (event) {
    event.preventDefault(); // prevent form submission

    var dobDay = document.querySelector("#dobDay");
    var dobMonth = document.querySelector("#dobMonth");
    var heightFeet = document.querySelector("#heightFeet");
    var heightInches = document.querySelector("#heightInches");
    var weight = document.querySelector("#weight");
    var childPackagingYes = document.querySelector("#childPackagingYes");
    var childPackagingNo = document.querySelector("#childPackagingNo");
    var refillsYes = document.querySelector("#refillsYes");
    var refillsNo = document.querySelector("#refillsNo");

    var errorMessages = [];
    var errorsPresent = false;

    // check if day is valid for the selected month
    if (
      (dobMonth.value == 2 && dobDay.value > 29) ||
      ((dobMonth.value == 4 ||
        dobMonth.value == 6 ||
        dobMonth.value == 9 ||
        dobMonth.value == 11) &&
        dobDay.value > 30)
    ) {
      errorMessages.push("Invalid day for selected month.");
      displayErrorAfter(dobDay, "Invalid day for selected month.");
      errorsPresent = true;
    } else {
      removeError(dobDay);
    }

    // check height and weight fields
    if (!heightFeet.value || !heightInches.value) {
      errorMessages.push("Please enter your height.");
      displayErrorAfter(heightInches, "Please enter your height.");
      errorsPresent = true;
    } else {
      removeError(heightInches);
    }

    if (!weight.value) {
      errorMessages.push("Please enter your weight.");
      displayErrorAfter(weight, "Please enter your weight.");
      errorsPresent = true;
    } else {
      removeError(weight);
    }

    // check radio buttons
    if (!childPackagingYes.checked && !childPackagingNo.checked) {
      errorMessages.push("Please select your packaging preference.");
      displayErrorAfter(
        childPackagingNo,
        "Please select your packaging preference."
      );
      errorsPresent = true;
    } else {
      removeError(childPackagingNo);
    }

    if (!refillsYes.checked && !refillsNo.checked) {
      errorMessages.push(
        "Please indicate if you want to be called or emailed for refills."
      );
      displayErrorAfter(
        refillsNo,
        "Please indicate if you want to be called or emailed for refills."
      );
      errorsPresent = true;
    } else {
      removeError(refillsNo);
    }

    //if (!errorsPresent) {
    event.target.submit(); // manually submit the form if no errors
    // }
  });




function show_profile_content(a, color = "blue") {
  var tabs = Array.from(document.getElementById("content-tabs").getElementsByTagName("a"));
  tabs.forEach(element => element.getElementsByTagName("span")[1].classList.remove("bg-[" + color + "]"));
  a.getElementsByTagName("span")[1].classList.add("bg-[" + color + "]");
  toggle_profile_content(a.getAttribute("do"));

}
function show_profile_content_mobile(select) {
  var tabs_mobile = document.getElementById("content-tabs-mobile");
  toggle_profile_content(select.value);
}

function toggle_profile_content(show) {
  jQuery("#profile-content > div").hide();
  jQuery("#profile-content > div" + show).fadeIn("fast");
}




document.addEventListener('DOMContentLoaded', function () {
  const dropdownButtons = document.querySelectorAll('.dropdown-button');
  const dropdownContents = document.querySelectorAll('.dropdown-content');
  const dropdownOptions = document.querySelectorAll('.dropdown-content a');

  dropdownButtons.forEach(button => {
    button.addEventListener('click', function (event) {
      event.stopPropagation();
      const dropdownContent = button.closest('.relative').querySelector('.dropdown-content');
      dropdownContent.classList.toggle('hidden');
    });
  });

  dropdownOptions.forEach(option => {
    option.addEventListener('click', function () {
      const dropdownContent = option.closest('.dropdown-content');
      const dropdownButton = dropdownContent.previousElementSibling;

      // Update the dropdown button's text with the selected option's text
      const label = dropdownButton.querySelector('.dropdown-label');
      label.textContent = option.textContent;

      dropdownContent.classList.add('hidden');
    });
  });

  document.addEventListener('click', function (event) {
    dropdownContents.forEach(dropdown => {
      if (!dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
      }
    });
  });
});


function select_package(id, quantity) {
  const dropdownButtons = document.querySelectorAll('div.packages');
  const package_id = document.querySelectorAll('input#package_id');
  const package_quantity = document.querySelectorAll('input#package_quantity');
  if (package_id.length > 0)
    package_id[0].value = id;
  if (package_quantity.length > 0)
    package_quantity[0].value = quantity;
  dropdownButtons.forEach((element) => {
    if (id != element.getAttribute("id")) {
      element.classList.remove("ring-2");
    }
    else {
      element.classList.add("ring-2");
    }
  });
}

function change_cart_text(count) {
  const cart_text = document.querySelectorAll('span#cart-header');
  if (cart_text.length > 0) {
    cart_text[0].textContent = count;
  }
}

function show_tab(a, id) {

  const titles = document.getElementsByName("tab-select");
  titles.forEach(title => {
    title.classList.remove("bg-white");
    title.classList.remove("rounded-tl-lg");
    title.classList.remove("rounded-tr-lg");
    title.classList.remove("border-l");
    title.classList.remove("border-t");
    title.classList.remove("border-r");
    title.classList.remove("border-gray-100");

  });
  a.classList.add("bg-white");
  a.classList.add("rounded-tl-lg");
  a.classList.add("rounded-tr-lg");
  a.classList.add("border-l");
  a.classList.add("border-t");
  a.classList.add("border-r");
  a.classList.add("border-gray-100");
  jQuery("div[name='tab']").hide();
  jQuery("div#" + id).show();
}

document.addEventListener("DOMContentLoaded", function () {
  const faqToggles = document.querySelectorAll(".faq-toggle");

  // Set unique aria-controls and id values
  faqToggles.forEach((toggle, index) => {
    const uniqueID = "faq-" + index;
    toggle.setAttribute("aria-controls", uniqueID);
    if (toggle.nextElementSibling) {
      toggle.nextElementSibling.setAttribute("id", uniqueID);
    }
  });

  // Handle the click events
  faqToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const contentId = toggle.getAttribute("aria-controls");
      const content = document.getElementById(contentId);

      if (content) {
        if (content.style.maxHeight && content.style.maxHeight !== "0px") {
          content.style.maxHeight = "0px";
          toggle.querySelector(".faq-icon-expand").classList.remove("hidden");
          toggle.querySelector(".faq-icon-collapse").classList.add("hidden");
        } else {
          content.style.maxHeight = content.scrollHeight + "px";
          toggle.querySelector(".faq-icon-expand").classList.add("hidden");
          toggle.querySelector(".faq-icon-collapse").classList.remove("hidden");
        }
      } else {
        
      }
    });
  });
});

document.addEventListener('DOMContentLoaded', function () {
  if (document.getElementById('toggleCouponBtn'))
    document.getElementById('toggleCouponBtn').addEventListener('click', function () {
      jQuery("#toggleCouponBtn").hide();
      jQuery("#couponField").slideToggle("slow");
    });
});

jQuery(document).ready(function () {
  // Reference to the checkbox and the billing address section
  var $checkbox = jQuery('#sameAsShipping');
  var $billingAddress =jQuery('.billing-address');

  // Function to toggle the billing address based on the checkbox's state
  function toggleBillingAddress() {
    if ($checkbox.is(':checked')) {
      $billingAddress.hide();
    } else {
      $billingAddress.show();
    }
  }

  // Call the function initially to set the proper display state on page load
  toggleBillingAddress();

  // Add an event listener to the checkbox to call our function whenever its state changes
  $checkbox.change(toggleBillingAddress);
});

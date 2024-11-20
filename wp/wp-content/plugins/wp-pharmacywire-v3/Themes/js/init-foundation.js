/* eslint-disable camelcase */
// Abide validator required to be defined before $(document).foundation()
// despite what documentation says
function abideCreditCardValidator(el) {
	const creditCardType = el.attr('data-credit-card-type');
	const allowedCreditCards = pwire.pwire_components.payment.methods.creditcard.types;
	if (creditCardType && Object.prototype.hasOwnProperty.call(allowedCreditCards, creditCardType)) {
		return true;
	}
	return false;
}
Foundation.Abide.defaults.validators.abideCreditCardValidator = abideCreditCardValidator;

function abideCreditCard_CVV_Validator(el) {
	const creditCardCVV = el.val();
	const creditCardInput = el.closest('.method-credit-card').find('input[name=billing_creditCard_number]');
	const creditCardType = creditCardInput.attr('data-credit-card-type');
	const cvvError = el.siblings('label[data-form-error-for="billing_creditCard_cvv"]');
	let cvvValid = false;
	if ((creditCardType === 'amex') && (creditCardCVV.length === 4)) {
		cvvValid = true;
	} else if ((creditCardType !== 'amex') && (creditCardCVV.length === 3)) {
		cvvValid = true;
	}
	if (cvvValid === false) {
		if (creditCardType === 'amex') {
			cvvError.text('A 4 digit credit card CVV is required.');
		} else {
			cvvError.text('A 3 digit credit card CVV is required.');
		}
	}
	return cvvValid;
}
Foundation.Abide.defaults.validators.abideCreditCard_CVV_Validator = abideCreditCard_CVV_Validator;

jQuery(document).foundation();

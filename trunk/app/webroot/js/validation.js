/* validation methods - syntax modeled after prototype.js */

var Validation = {
	required: function(element) {
		element = $(element);
		Validation.clearError(element);
		
		if (($F(element) || "").blank()) {
			Validation.showError(element, arguments[1], arguments[2] || "is required.");
			return false;
		}

		return true;
	},
	
	numeric: function(element, allowDecimal) {
		element = $(element);
		Validation.clearError(element);
		
		if (allowDecimal ? !($F(element) || "").isNumeric() : !/^-?[0-9]*$/.test($F(element) || "")) {
			Validation.showError(element, arguments[2], arguments[3] || (allowDecimal ? "must be a number." : "must be a whole number."));
			return false;
		}

		return true;
	},
	
	within: function(element, range, allowDecimal) {
		element = $(element);
		Validation.clearError(element);
		
		if (!$$N(element, allowDecimal, arguments[3], arguments[4]))
			return false;
			
		if (!($F(element) || "").blank() && !range.include($F(element))) {
			Validation.showError(element, arguments[3], arguments[4] || "must be between " + range.start + " and " + range.end + ".");
			return false;
		}

		return true;
	},
	
	date: function(element) {
		element = $(element);
		Validation.clearError(element);
		
		if (!($F(element) || "").isDate()) {
			Validation.showError(element, arguments[1], arguments[2] || "is not a valid date.");			
			return false;
		}
		
		return true;
	},
	
	pattern: function(element, regex) {
		element = $(element);
		Validation.clearError(element);
		
		if (!($F(element) || "").blank() && !regex.test(($F(element)))) {
			Validation.showError(element, arguments[2], arguments[3] || "is not valid.");
			return false;
		}
		
		return true;
	},
	
	custom: function(element, expression) {
		element = $(element);
		Validation.clearError(element);
		
		if (!($F(element) || "").blank() && !expression) {
			Validation.showError(element, arguments[2], arguments[3] || "is not valid.");
			return false;
		}
		
		return true;
	},
	
	showError: function(element, name, predicate) {
		if (!name) {
			var label = element.previous();
			
			if (label && label.tagName && label.tagName.toLowerCase() == "label") {
				name = "\"" + label.innerHTML + "\"";
			}
		}
		
		if (name) {
			var message = "The " + name + " field " + predicate;
			element.addClassName("FieldError");
			element.title = message;
		}
	},
	
	clearError: function(element) {
		if (element.hasClassName("FieldError"))
		{
			element.removeClassName("FieldError");
			element.title = "";
		}
	}
}

/* shortcuts */
var $$R = Validation.required;
var $$N = Validation.numeric;
var $$D = Validation.date;
var $$P = Validation.pattern;
var $$W = Validation.within;
var $$C = Validation.custom;
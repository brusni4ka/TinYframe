'use strict';
//http://stackoverflow.com/questions/14220321/how-to-return-the-response-from-an-asynchronous-call
(function () {
    var ERROR_CLASS = 'has-error';
    var form = document.querySelector('form');
    var allValidators = [];
    var validationStatus = [];
    var submitButton = document.querySelector('button[type="submit"]');
    var emailUrl = 'https://aqueous-reaches-8130.herokuapp.com/check-email/?email=';


    var validators = {
        email: [{
            validator: function (options) {
                options.callback(options.value, this.message);
            },
            message: "Введите свой емейл. Это поле обязательно к заполнению"
        }, {
            validator: function (options) {
                options.callback(/@/.test(options.value), this.message);
            },
            message: "Адрес электронной почты должен содержать символ \"@\" "
        }, {
            validator: function (options) {
                options.callback(/@\w+/.test(options.value), this.message);
            },
            message: "Введите часть адреса после символа\"@\"."
        }, {
            validator: function (options) {
                options.callback(/[^@]+@[^@\.]+\.[^@]+/.test(options.value), this.message);
            },
            message: "Введите данные в указанном формате (petrenko@mail.ru)"
        }, {
            validator: function (options) {
                var massage = this.message;
                $.get(emailUrl + options.value).done(function (result) {
                    options.callback(!result.used, massage);
                }).fail(function () {
                    console.log("ERROR");
                });

            },
            message: "Укажите другой email. Этот уже используется"
        }],

        password: [{
            validator: function (options) {
                options.callback(options.value, this.message);
            },
            message: "Введите пароль. Это поле обязательно к заполнению"
        }, {
            validator: function (options) {
                options.callback(/(?=^.{5,}$)/.test(options.value), this.message);
            },
            message: "Пароль слишком короток (до 5 символов)"

        }],

        rules: [{
            validator: function (options) {
                options.callback(options.node.checked, this.message);
            },
            message: "Надо согласиться"
        }]
    };


    function showError(node, errorMessage) {
        if (node.parentNode.classList.contains(ERROR_CLASS)) {
            return
        }
        node.parentNode.classList.add(ERROR_CLASS);
        var alert = document.createElement('dir');
        alert.className += 'alert alert-danger';
        alert.innerHTML = errorMessage;
        node.parentNode.appendChild(alert);
    }


    function hideError(formElement) {
        if (!formElement.parentNode.classList.contains(ERROR_CLASS)) {
            return;
        }
        formElement.parentNode.classList.remove(ERROR_CLASS)
        var errorContainer = formElement.parentNode.querySelector('.alert.alert-danger')
        errorContainer.parentNode.removeChild(errorContainer)
    }


    function updateSubmitButtonStatus() {
        var everythingIsValid = validationStatus.every(function (validStatus) {
            return validStatus
        });
        if (everythingIsValid) {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }
    }

    function createValidator(id, rules) {
        var node = document.getElementById(id);
        var validationStatusIndex = validationStatus.push(false) - 1;


        function validateCallback(result, errorMessage) {
            if (!result) {
                console.log(result);
                showError(node, errorMessage);
                validationStatus[validationStatusIndex] = false;
                updateSubmitButtonStatus();
            }

        }

        function validate() {
            var nodeValue = node.value.trim();
            var options = {
                value: nodeValue,
                node: node,
                callback: validateCallback
            };
            validationStatus[validationStatusIndex] = true;
            hideError(node);
            updateSubmitButtonStatus();
            for (var i = 0; i < rules.length; i++) {
                if (!validationStatus[validationStatusIndex])
                    break;
                rules[i].validator(options);
            }
        }

        node.addEventListener('keyup', validate, false);
        node.addEventListener('blur', validate, false);
        node.addEventListener('change', validate, false);

        return validate;
    }


    /*
     document.querySelector('form').addEventListener('submit', function () {
     var everythingIsValid = validationStatus.every(function (value) {
     return value == true;
     })
     submitButton.disabled = !everythingIsValid;

     if (!everythingIsValid) {
     event.preventDefault();
     } else {
     var emailValue = document.querySelector('#email').value;
     sendEmail(emailValue);
     alert(emailValue);
     }
     // location.reload();
     /* var everythingIsValid = allValidators.reduce(function (totallyValid, validator) {
     var inputIsValid = validator();
     if (!totallyValid) {
     return false;
     } else {
     return inputIsValid;
     }
     }, true);

     submitButton.disabled = !everythingIsValid;
     if (!everythingIsValid) {
     event.preventDefault();
     }
     else alert("GOOD JOB");
     //else usedEmails.push(document.getElementById(email).value);

     }, false)*/


    for (var inputId in validators) {
        console.log(inputId);
        allValidators.push(createValidator(inputId, validators[inputId]));
    }

}())

